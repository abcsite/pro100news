<?php

class CategoriesController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Category();
    }

    public function admin_index()
    {
        /* Список категорий на страницу загружается через AJAX методом admin_categories_get_ajax(). А этот метод необходим для перенаправления роутером на соответствующее представление */
    }

    public function admin_add()
    {
        if ($_POST && $_POST['category_name'] != '') {
            $result = $this->model->save($_POST);
            if ($result) {
                Session::setFlash('Category was saved.');
            } else {
                Session::setFlash('Error.');
            }
        }
        Router::redirect('/admin/categories/');
    }


    public function admin_categories_get_ajax()
    {
        $categories = $this->model->getList();
        $categories_line = structure_to_line($categories, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id' ]);
        echo(json_encode($categories_line));
        die;
    }


    public function admin_category_add_ajax()
    {
        if (isset($_POST['category_name']) && isset($_POST['parent_id'])) {
            $result = $this->model->save($_POST, $_POST['id']);

            $this->admin_categories_get_ajax();
        }
        die;
    }

    public function admin_category_delete_ajax()
    {
        if (isset($this->params[0])) {
            $categories = $this->model->getList();
            $childs_categories_to_delete = structure_to_line($categories, $options = ['begin_id' => $this->params[0], 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id' ]);

            $id_arr = [  (int) $this->params[0] ];
            foreach ($childs_categories_to_delete as $child) {
                $id_arr[] = (int) $child['id'];
            }

            $result = $this->model->delete($id_arr);

            $this->admin_categories_get_ajax();
        }
        die;
    }


}