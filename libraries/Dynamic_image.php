<?php  defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * CodeIgniter Dynamic Image Library
 * v 1.0
 *
 * @author Sujeet <sujeetkv90@gmail.com>
 * @link https://github.com/sujeet-kumar/ci-dynamic-image
 */

class Dynamic_image
{
	protected $CI;
	protected $base_path = './';
	protected $cache_dir = 'image_cache';
	protected $create_sub_dir = true;
	protected $thumb_quality = '100%';
	protected $qstr_mode = false;
	
	protected $wm_info = array(
		'text' 			=> '',
		'color' 		=> '000000',
		'font_size' 	=> 12,
		'font_path' 	=> 'system/fonts/texb.ttf',
		'padding' 		=> 4,
		'smart_wm'		=> false,
		'custom_config' => array()
	);
	
	/**
	 * Initialize library
	 * @param	array	$config
	 */
	public function __construct($config = array()){
		$this->CI =& get_instance();
		$this->initialize($config);
	}
	
	/**
	 * Set config
	 * @param	array	$params
	 */
	public function initialize($params){
		if(isset($params['base_path'])){
			$this->base_path = $params['base_path'];
		}elseif(defined('FCPATH')){
			$this->base_path = str_replace('\\', '/', FCPATH);
		}
		
		isset($params['cache_dir']) and $this->cache_dir = trim(strval($params['cache_dir']),'/\\');
		isset($params['create_sub_dir']) and $this->create_sub_dir = (bool) $params['create_sub_dir'];
		isset($params['thumb_quality']) and $this->thumb_quality = $params['thumb_quality'];
		isset($params['qstr_mode']) and $this->qstr_mode = (bool) $params['qstr_mode'];
	}
	
	/**
	 * Set watermark
	 * @param	array	$config
	 */
	public function setWaterMark($config){
		if(is_array($config)){
			foreach($config as $k => $v){
				if(array_key_exists($k, $this->wm_info)){
					$this->wm_info[$k] = $v;
				}
			}
		}
	}
	
	/**
	 * Render image
	 * @param	string	$image_path (optional)
	 */
	public function render($image_path = ''){
		$img_path = NULL; $width = NULL; $height = NULL; $with_ratio = false; $crop = false;
		
		$_directory = $this->CI->router->directory;
		$_class = $this->CI->router->class;
		$_method = $this->CI->router->method;
		$namespace = $_directory . $_class . '/' . $_method . '/';
		
		if(!empty($image_path)){
			$path_array = explode('/', ltrim($image_path,'/'));
		}else{
			$path_array = $this->CI->uri->segment_array();
			
			empty($_directory) or array_shift($path_array); // remove directory name
			array_shift($path_array); // remove controller name
			array_shift($path_array); // remove method name
		}
		
		if($this->qstr_mode){
			
			$width = $this->CI->input->get('w');
			$height = $this->CI->input->get('h');
			$with_ratio = (bool) $this->CI->input->get('r');
			$crop = (bool) $this->CI->input->get('c');
		
		}elseif(count($path_array) > 0 and preg_match('#[0-9]+x[0-9]+(-r|-c|-r-c|-c-r)?#i', $path_array[0])){
			
			$resize_info = explode('-', strtolower(array_shift($path_array)));
			list($width, $height) = explode('x', $resize_info[0]);
			array_shift($resize_info);
			$with_ratio = in_array('r', $resize_info);
			$crop = in_array('c', $resize_info);
			
		}
		
		$img_path = implode('/', $path_array);
		
		if(empty($img_path)){
			show_error('Error: no image specified.', 400);
			return;
		}
		
		if(! is_file($this->base_path . $img_path)){
			show_404();
			return;
		}else{
			$img_info = getimagesize($this->base_path . $img_path);
			$mime = (isset($img_info['mime'])) ? $img_info['mime'] : '';
			
			if(substr($mime, 0, 6) != 'image/'){
				show_error('Error: requested file is not a valid image type: ' . $img_path, 400);
				return;
			}
			
			list($w, $h) = $img_info;
			$width and $w = $width;
			$height and $h = $height;
			
			$cache_path = $this->base_path . $this->cache_dir . '/';
			if(empty($this->cache_dir) or !is_dir($cache_path)){
				show_error('Error: image cache directory not configured.', 500);
				return;
			}
			
			if($this->create_sub_dir){
				if(! $this->_createDirectory($namespace, $cache_path)){
					return;
				}
				$cache_path .= $namespace;
				
				$size_dir = $w . 'x' . $h;
				if($with_ratio and $crop){
					$size_dir .= '/c-r';
				}elseif($with_ratio){
					$size_dir .= '/r';
				}elseif($crop){
					$size_dir .= '/c';
				}
				
				$new_img_name = basename($img_path);
				$new_img_dir = dirname($img_path) . '/' . $size_dir;
				
				if(! $this->_createDirectory($new_img_dir, $cache_path)){
					return;
				}
				
				$new_img_path = $cache_path . $new_img_dir . '/' . $new_img_name;
			}else{
				$size_dir = $w . 'x' . $h . ($crop ? '-c' : '') . ($with_ratio ? '-r' : '');
				$new_img_name = $size_dir . '-' . str_replace(array('/','\\'), '-', $namespace . $img_path);
				$new_img_path = $cache_path . $new_img_name;
			}
			
			if(file_exists($new_img_path)){
				$img_modified = $this->_fileTime($this->base_path . $img_path);
				$new_img_modified = $this->_fileTime($new_img_path);
				$process_image = ($img_modified > $new_img_modified) ? true : false;
			}else{
				$process_image = true;
			}
			
			if($process_image){
				$config = array(
					'source_image' => $this->base_path . $img_path,
					'new_image' => $new_img_path,
					'quality' => $this->thumb_quality,
					'width' => $w,
					'height' => $h,
					'maintain_ratio' => $with_ratio
				);
				
				if($crop){
					$process = 'crop';
					
					$img_width_half = round($img_info[0] / 2);
					$img_height_half = round($img_info[1] / 2);
					
					$crop_width_half  = round($w / 2);
					$crop_height_half = round($h / 2);
					
					$config['x_axis'] = max(0, ($img_width_half - $crop_width_half));
					$config['y_axis'] = max(0, ($img_height_half - $crop_height_half));
				}else{
					$process = 'resize';
				}
				
				$this->CI->load->library('image_lib', $config);
				
				if(! $this->CI->image_lib->$process()){
					show_error('Image '. ucfirst($process) .' Error: ' . $this->CI->image_lib->display_errors(' * ', ''), 500);
					return;
				}elseif(! $this->_processWaterMark($new_img_path, $h, $mime)){
					show_error('Image Watermark Process Error: ' . $this->CI->image_lib->display_errors(' * ', ''), 500);
					return;
				}
				$this->CI->image_lib->clear();
				
				clearstatcache();
				$new_img_modified = $this->_fileTime($new_img_path);
			}
			
			$data = file_get_contents($new_img_path);
			$etag = md5($data . $new_img_modified . $new_img_path);
			
			if($this->_doConditionalGet($etag, $new_img_modified)){
				return;
			}
			
			header('Content-Type: ' . $mime);
			header('Content-Length: ' . strlen($data));
			echo $data;
			return;
		}
	}
	
	protected function _processWaterMark($image_path, $image_hieght, $mime){
		$h_limit = ($this->wm_info['font_size'] + $this->wm_info['padding'] * 2) * 2;
		$invalid_mime = ($this->wm_info['smart_wm'] and in_array($mime, array('image/png','image/x-png','image/gif')));
		
		if(empty($this->wm_info['text']) or ($image_hieght < $h_limit) or $invalid_mime){
			return true;
		}else{
			$wm_config = array(
				'source_image'		=> $image_path,
				'quality'			=> $this->thumb_quality,
				'wm_text'			=> $this->wm_info['text'],
				'wm_type'			=> 'text',
				'wm_font_path'		=> $this->base_path . $this->wm_info['font_path'],
				'wm_font_size'		=> strval($this->wm_info['font_size']),
				'wm_font_color'		=> $this->wm_info['color'],
				'wm_vrt_alignment'	=> 'top',
				'wm_hor_alignment'	=> 'left',
				'wm_padding'		=> strval($this->wm_info['padding'])
			);
			
			if(!empty($this->wm_info['custom_config']) and is_array($this->wm_info['custom_config'])){
				foreach($this->wm_info['custom_config'] as $wm_k => $wm_v){
					preg_match('/^wm_/', $wm_k) and $wm_config[$wm_k] = $wm_v;
				}
			}
			
			$this->CI->image_lib->clear();
			$this->CI->image_lib->initialize($wm_config);
			return $this->CI->image_lib->watermark();
		}
	}
	
	protected function _doConditionalGet($etag, $last_modified){
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $last_modified));
		header('ETag: "' . $etag . '"');
		
		$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : false;
		$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :	false;
		
		//etag and if-modified-since is not present
		if(!$if_modified_since && !$if_none_match){
			return false;
		}
		
		//etag is present but doesn't match
		if($if_none_match && !preg_match('|^"?'.$etag.'"?$|', $if_none_match)){
			return false;
		}
		
		//if-modified-since is present but doesn't match
		if($if_modified_since && strtotime($if_modified_since) != $last_modified){
			return false;
		}
		
		//Nothing has changed since the last request - serve a 304 and exit
		set_status_header(304);
		return true;
	}
	
	protected function _createDirectory($directory_path, $base_path){
		if(! is_dir($base_path . $directory_path)){
			$tmp_path = '';
			foreach(explode('/', $directory_path) as $directory){
				$tmp_path = $tmp_path . '/' . $directory;
				$new_path = rtrim($base_path, '/') . $tmp_path;
				if(! is_dir($new_path)){
					if(! @mkdir($new_path, 0777)){
						show_error('Error: could not create sub-directory for cache.', 500);
						return false;
					}
				}
			}
		}
		return true;
	}
	
	protected function _fileTime($file_path){
		return max(filemtime($file_path), filectime($file_path));
	}
}

/* End of file Dynamic_image.php */