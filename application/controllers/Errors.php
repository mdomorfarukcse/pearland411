<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Errors extends CI_Controller
{
    public function page_missing()
    {
        $this->load->view('404-page');
    }
}
