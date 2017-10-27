<?php

class Module_side_rightController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);  
        $this->model = new Modul_side_right();
    }

    
    
    public function get_view()
    {
        $view_data = null;
        $view_path = VIEWS_PATH . DS .'modules' . DS . 'side_right.html';
        $view_object = new View( $view_data, $view_path);
        $content = $view_object->render();
        return $content;
    }


}
