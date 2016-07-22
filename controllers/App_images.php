<?php defined('BASEPATH') OR exit('No direct script access allowed');

class App_images extends CI_Controller
{	
	public function index(){
		$this->load->library('dynamic_image', array(
			'cache_dir' => APPPATH.'cache/image_cache',
			'create_sub_dir' => true,
			'thumb_quality' => '100%',
			'qstr_mode' => false
		));
		
		$this->dynamic_image->setWaterMark(array(
			'text' => 'Sujeet',
			'color' => '0066FF',
			'font_path' => 'system/fonts/Bullpen3D.ttf',
			'smart_wm' => true
		));
		
		$this->dynamic_image->render();
	}
}

/* End of file App_images.php */