<?php 



defined('BASEPATH') OR exit('No direct script access allowed');

function compress_image($tempPath, $originalPath, $imageQuality){
  
    // Get image info 
    $imgInfo = getimagesize($tempPath); 
    $mime = $imgInfo['mime']; 
     
    // Create a new image from file 
    switch($mime){ 
        case 'image/jpeg': 
            $image = imagecreatefromjpeg($tempPath); 
            break; 
        case 'image/png': 
            $image = imagecreatefrompng($tempPath); 
            break; 
        case 'image/gif': 
            $image = imagecreatefromgif($tempPath); 
            break; 
        default: 
            $image = imagecreatefromjpeg($tempPath); 
    } 
     
    // Save image 
    imagejpeg($image, $originalPath, $imageQuality);    
    // Return compressed image 
    return $originalPath; 
}

function uploadAndResize($data)
{
    $CI =& get_instance();

    $image_name = $data['image_name'];
    $image = $_FILES[$image_name]['name'];

    $config['upload_path']          = './assets/'.$data['upload_path'];
    $config['allowed_types']        = 'gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG';
    $config['max_size']             = 5024;

    $CI->load->library('upload', $config);

    $CI->upload->initialize($config);

    if ($CI->upload->do_upload($image_name)) {

        $avatar = $CI->upload->data();

        $config['image_library'] = 'gd2';
        $config['source_image']  = './assets/'.$data['upload_path'].'/'.$avatar['file_name'];
        $config['create_thumb']  = FALSE;
        $config['maintain_ratio']= FALSE;
		// $config['height']        = $data['height'];
		// $config['width']         = $data['width'];
        $config['quality']		 = '60%';
        $config['new_image']	 = './assets/'.$data['upload_path'].'/'.$avatar['file_name'];

        $CI->load->library('image_lib',$config);
        $CI->image_lib->resize();

        $avatar_name = 'assets/'.$data['upload_path'].'/'.$avatar['file_name']; 

        return $avatar_name;
    }
}
function uploadWithoutResize($data)
{
    $CI =& get_instance();

    $image_name = $data['image_name'];
    $image = $_FILES[$image_name]['name'];

    $config['upload_path']          = './assets/'.$data['upload_path'];
    $config['allowed_types']        = 'gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG';
    $config['max_size']             = 5024;

    $CI->load->library('upload', $config);

    $CI->upload->initialize($config);

    if ($CI->upload->do_upload($image_name)) {

        $avatar = $CI->upload->data();

        $config['image_library'] = 'gd2';
        $config['source_image']  = './assets/'.$data['upload_path'].'/'.$avatar['file_name'];
        $config['create_thumb']  = FALSE;
        $config['maintain_ratio']= FALSE;
        $config['quality']       = '60%';
        $config['new_image']     = './assets/'.$data['upload_path'].'/'.$avatar['file_name'];

        $CI->load->library('image_lib',$config);
        $CI->image_lib->resize();

        $avatar_name = 'assets/'.$data['upload_path'].'/'.$avatar['file_name']; 

        return $avatar_name;
    }
}

    function uploadMultipleAndResize($data,$limit = null)
    {
        $CI =& get_instance();

        $imageData = array();

        $image_name = $data['image_name'];

        // step 1: Count total files
        if($limit != null){
            $countFiles = $limit;
        } else {
            $countFiles = count($_FILES[$image_name]['name']);
        }
        $count = 1;
        $j = 0;
        // step 2: loop all files
        for ($i=0; $i < $countFiles; $i++) { 

            // step 3: check if file empty or not 
            if(!empty($_FILES[$image_name]['name'][$i]))
            {
                // step 4: Define temp files
                $_FILES['file']['name']     = $_FILES[$image_name]['name'][$i]; 
                $_FILES['file']['type']     = $_FILES[$image_name]['type'][$i];
                $_FILES['file']['tmp_name'] = $_FILES[$image_name]['tmp_name'][$i];
                $_FILES['file']['error']    = $_FILES[$image_name]['error'][$i];
                $_FILES['file']['size']     = $_FILES[$image_name]['size'][$i];

                // step 5: configuration for the uploading the file
                $config['upload_path']      = './assets/'.$data['upload_path'];
                $config['allowed_types']    = 'gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG';
                $config['max_size']         = 5024;
                $config['file_name']        = $_FILES[$image_name]['name'][$i];  

                $CI->load->library('upload', $config);
                $CI->upload->initialize($config);

                if ($CI->upload->do_upload('file')) {

                    $avatar = $CI->upload->data();

                    // step 6: configuration for resizing image
                    $config['image_library'] = 'gd2';
                    $config['source_image']  = './assets/'.$data['upload_path'].'/'.$avatar['file_name'];
                    $config['create_thumb']  = FALSE;
                    $config['maintain_ratio']= FALSE;
                    $config['height']        = $data['height'];
                    $config['width']         = $data['width'];
                    $config['quality']       = '60%';
                    $config['new_image']     = './assets/'.$data['upload_path'].'/'.$avatar['file_name'];

                    $CI->load->library('image_lib',$config);
                    $CI->image_lib->resize();

                    $avatar_name = 'assets/'.$data['upload_path'].'/'.$avatar['file_name'];

                    $imageData[$j] = $avatar_name;                   
                }
            } 
            $j++;
        }
        $allImages = json_encode($imageData);
        return $allImages;
    }