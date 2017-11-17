<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_images extends CI_Controller
{
    public function index() {
        // load library and initialize with config
        $this->load->library('dynamic_image', array(
            'cache_dir' => 'cache',// relative to APPPATH (application directory path)
            'create_cache_tree' => true,
            'image_quality' => '100%',
            'qstr_mode' => false,
            'browser_cache_time' => 31536000, // in seconds
            'browser_cache_type' => 'public', // public or private
        ));

        // set watermark
        $this->dynamic_image->setWaterMark(array(
            'text' => 'Sujeet',
            'color' => '0066FF',
            'font_path' => 'fonts/Bullpen3D.ttf',// relative to BASEPATH (system directory path)
            'smart_wm' => true
        ));

        // or set custom watermark config complying with CodeIgniter Image_Lib watermark
        /*$this->dynamic_image->setWaterMark(array(
            'smart_wm' => true,
            'custom_config' => array(
                'wm_text' => 'Sujeet',
                'wm_type' => 'text',
                'wm_font_path' => BASEPATH . 'fonts/Bullpen3D.ttf',
                'wm_font_size' => '12',
                'wm_font_color' => '0066FF',
                'wm_vrt_alignment' => 'top',
                'wm_hor_alignment' => 'left',
                'wm_padding' => '4'
            )
        ));*/

        // render image
        $this->dynamic_image->render();
    }
}
