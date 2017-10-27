<?php

class Module_side_leftController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);  
        $this->model = new Modul_side_left();
    }

    public function get_categories_all()
    {
        $categories = $this->model->getCategories();
        $categories_line = structure_to_line($categories, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);
        $this->data['categories'] = $categories_line;
        return $categories_line;
    }

    public function get_view()
    { 
        $view_data['categories'] = $this->get_categories_all();
        $view_data['categories_url_base'] = $this->data['categories_url_base'];
        
        $view_path = VIEWS_PATH . DS . 'modules' . DS . 'side_left.html';
        
        $view_object = new View( $view_data, $view_path);
        $content = $view_object->render();
        
        return $content;
    }

}
