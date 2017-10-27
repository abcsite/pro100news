<?php

class Background extends Model
{

    public function replaceImages($name, $data = null)
    {
        $uploadfile = BACKGROUNDS_IMG_PATH . DS  . $name;

        $result = true;
        if (file_exists($uploadfile)) {
            $result = unlink($uploadfile);
        }
        if ($data) {
            $result = $result && move_uploaded_file($data['tmp_name'], $uploadfile);
        }
        return $result;
    }

    
    public function set_bg_color($name , $color)
    {
        $uploadfile = BACKGROUNDS_IMG_PATH . DS  . $name . '_color' . '.txt';
        $upload_css = CSS_PATH . DS . 'style_' . $name . '.css';

        $result = true;
        if (file_exists($uploadfile)) {
            $result = unlink($uploadfile);
        }
        if (!$color) $color = ' ';
        if ($color) {
            $result = $result && file_put_contents( $uploadfile, $color);
            $text = '.' . $name . '{
                            background: ' . $color . ';
                            background-image: url(/webroot/img/background/' . $name . ');
                            background-size:  cover;
                            }';
            $result = $result && file_put_contents( $upload_css, $text);
        }
        return $result;
    }

    
     public function get_bg_color($name )
    {
        $uploadfile = BACKGROUNDS_IMG_PATH . DS  . $name . '.txt';
        if (!file_exists($uploadfile)) {
            $content = ' ';
        } else {
            $content = file_get_contents( $uploadfile );
        }

        return $content;
    }

    
    public function deleteImages($name)
    {
        $uploadfile = BACKGROUNDS_IMG_PATH . DS  . $name;
        $result = true;
        if (file_exists($uploadfile)) {
            $result = $result && unlink($uploadfile);
        }
        return $result;
    }
}