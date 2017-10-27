<?php

class ArticlesController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Article();
    }

    public function index()
    {
        $this->data['pages'] = $this->model->getList(true);
    }



        /* Обработка поиска по статьям  (поиск одновременно и по тегам статей и по заголовкам статей) */
    public function search_ajax()
    {

        if (isset($this->params[0])) {
            
            $inp = $this->params[0];
            $inp_words = explode(' ', $inp);
            /* Максимальное количество выводимых пользователю найденных слов и минимальная длина вводимых пользователем слов, которые будут обрабатываться */
            $max_count = 10;
            $min_length = 1;
            
            if ($inp_words) {

                /* Поиск в тегах статей */
                $res_tags = [];
                foreach ($inp_words as $word) {


                    if (mb_strlen($word) >= $min_length) {
                        $filter['tags'][0] =  $word;
                        $articles_has_word = $this->model->getArticlesFilter($filter, true);

                        foreach ($articles_has_word as $row) {
                            $tags_str = $row['art_tags'];
                            $tags_arr = explode(',', $tags_str);

                            foreach ($tags_arr as $tag) {
                                if (strstr($tag,  $word)) {
                                    $res_tags[$tag] = $tag;
                                }
                            }
                        }
                    }
                }
                $new_res_tags = [];
                foreach ($res_tags as $kay => $val) {
                    $new_res_tags[] = $kay;
                }

                /* Поиск в заголовках статей */
                $articles = $this->model->searchWordsInTitleOfArticles($inp_words, $max_count, $min_length, true);

                $res = [];
                $res['tags'] = $new_res_tags;
                $res['articles'] = $articles;
                $res['max_count'] = $max_count;
                $res['min_length'] = $min_length;

                echo(json_encode($res));
            }
        }
        die;
    }


    public function filter()
    {
        /* Задаем параметры и получаем контент для центральной части страницы поиска (список найденных статей) */
        $data_module_articles = [];
        $data_module_articles['articles_url_base'] = '/articles/filter/';
        $data_module_articles['filter'] = $_GET;
        $data_module_articles['filter']['order_by'][] = '-a.date_published';
        $data_module_articles['filter']['order_by'][] = '-a.title';

        $module_articles_list = new Module_articles_listController($data_module_articles);
        $this->data['module_articles_list'] = $module_articles_list->get_view();

        /* Если выбрана категория , передаем ее название в заголовок страницы */
        if ( isset($_GET['categ']) && is_array($_GET['categ']) && count($_GET['categ']) === 1) {
            $categ = $module_articles_list->getModel()->getCategoryByIds($_GET['categ']);
            $this->data['selected_categ'] = $categ[0]['category_name'];
        }

        /* Задаем параметры и получаем контент для левой части страницы поиска (список категорий) */
        $data_module_side_left = [];
        $data_module_side_left['categories_url_base'] = '/home/';
        $module_side_left = new Module_side_leftController($data_module_side_left);
        $this->data['module_side_left'] = $module_side_left->get_view();

        /* Задаем параметры для модуля для получения списка ТОП-новостей за день в правой части страницы поиска */
        $data_module_articles_top_day = [];
        $data_module_articles_top_day['filter']['date_min'] = date('Y-m-d h:i:s', time() - 60 * 60 * 24);
        $data_module_articles_top_day['filter']['order_by'][] = '-a.visited';
        $data_module_articles_top_day['filter']['order_by'][] = '-a.date_published';
        $data_module_articles_top_day['filter']['limit_count'] = 10;
        $data_module_articles_top_day['filter']['limit_offset'] = 0;

        $module_articles_top_day = new Module_articles_listController($data_module_articles_top_day);
        $data_top_day = $module_articles_top_day->get_articles_filter();

        /* Задаем параметры для модуля для получения списка ТОП-новостей за неделю в правой части страницы поиска  */
        $data_module_articles_top_week = [];
        $data_module_articles_top_week['filter']['date_min'] = date('Y-m-d h:i:s', time() - 60 * 60 * 24 * 7);
        $data_module_articles_top_week['filter']['order_by'][] = '-a.visited';
        $data_module_articles_top_week['filter']['order_by'][] = '-a.date_published';
        $data_module_articles_top_week['filter']['limit_count'] = 10;
        $data_module_articles_top_week['filter']['limit_offset'] = 0;

        $module_articles_top_week = new Module_articles_listController($data_module_articles_top_week);
        $data_top_week = $module_articles_top_week->get_articles_filter();

        /* Задаем параметры для модуля для получения списка ТОП-новостей за месяц в правой части страницы поиска  */
        $data_module_articles_top_month = [];
        $data_module_articles_top_month['filter']['date_min'] = date('Y-m-d h:i:s', time() - 60 * 60 * 24 * 30);
        $data_module_articles_top_month['filter']['order_by'][] = '-a.visited';
        $data_module_articles_top_month['filter']['order_by'][] = '-a.date_published';
        $data_module_articles_top_month['filter']['limit_count'] = 10;
        $data_module_articles_top_month['filter']['limit_offset'] = 0;

        $module_articles_top_month = new Module_articles_listController($data_module_articles_top_month);
        $data_top_month = $module_articles_top_month->get_articles_filter();

        /* Задаем параметры для модуля для получения списка ТОП-новостей за все время в правой части страницы статьи  */
        $data_module_articles_top_all = [];
        $data_module_articles_top_all['filter']['order_by'][] = '-a.visited';
        $data_module_articles_top_all['filter']['order_by'][] = '-a.date_published';
        $data_module_articles_top_all['filter']['limit_count'] = 10;
        $data_module_articles_top_all['filter']['limit_offset'] = 0;

        $module_articles_top_all = new Module_articles_listController( $data_module_articles_top_all );
        $data_top_all = $module_articles_top_all->get_articles_filter();


        /* Получаем контент для правой части страницы поиска  */
        $view_data_side_right = [];
        $view_data_side_right['top_day'] = $data_top_day;
        $view_data_side_right['top_week'] = $data_top_week;
        $view_data_side_right['top_month'] = $data_top_month;
        $view_data_side_right['top_all'] = $data_top_all;

        $path_side_right = VIEWS_PATH . DS . 'modules' . DS . 'side_right.html';
        $view_object_side_right = new View($view_data_side_right, $path_side_right);
        $this->data['module_side_right'] = $view_object_side_right->render();


    }


    public function admin_index()
    {
        $this->data['articles'] = $this->model->getList();
    }


    public function admin_edit()
    {

        if ($_POST) {
            $article_id = ($_POST['id']) ? $_POST['id'] : null;

            /* Сохраняем основные данные статьи */
            $result = $this->model->save($_POST, $article_id);

            /* Если добавлена новая статья, получаем ее id */
            $new_article_id = $this->model->getMaxValue('articles', 'id');
            if (!$article_id) {
                $article_id = $new_article_id;
            }

            /* Удаляем неиспользуемые в статье изображения, обновляем ссылки на изображения, получаем новый текст статьи (HTML-разметку) */
            $new_text = $this->model->checkImagesByHTML($_POST, $article_id);

            $_POST['text'] = $new_text;

            /* Обновляем запись в БД для статьи с обновленным текстом (HTML-разметкой) */
            $result = $result && $this->model->save($_POST, $article_id);

            /* Обновляем в БД категории статьи */
            if ($_POST['categories']) {
                $categories_id = $_POST['categories'];
                $result = $result && $this->model->update_categ_of_article($categories_id, $article_id);
            }

            if (!$result) {
                Session::setFlash('Ошибка сохранения статьи');
            }

            Router::redirect('/admin/articles/edit/' . $article_id . '/');
        }

        if (isset($this->params[0])) {
            /* В случае перехода на данную страницу сайта получаем данные для представления этой страницы */
            $this->data['article'] = $this->model->getById($this->params[0]);
            $this->data['article_images'] = $this->model->getImgsByArticleId($this->params[0]);

        }
    }

    /* Удаляем статью и связанные с ней изображения */
    public function admin_delete()
    {
        if (isset($this->params[0])) {

            $article_images = $this->model->getImgsByArticleId($this->params[0]);

            foreach ($article_images as $image) {
                $this->model->del_image($image['full_name']);
            }

            $result = $this->model->delete($this->params[0]);

            if ($result) {
                Session::setFlash('Статья была удалена');
            } else {
                Session::setFlash('Ошибка.');
            }
        }
        Router::redirect('/admin/articles/');
    }


    public function admin_deleteimage()
    {
        if (isset($this->params[0])) {
            $result = $this->model->del_image($this->params[0]);
            if ($result) {
                Session::setFlash('Изображение было удалено.');
            } else {
                Session::setFlash('Ошибка');
            }
        }
        Router::redirect('/admin/articles/edit/' . $this->params[1]);
    }


    public function admin_categories_get_ajax()
    {
        $categories = $this->model->getCategories();
        $categories_line = structure_to_line($categories, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);

        if (isset($this->params[0])) {
            $article_categories = $this->model->getCategByArticleId($this->params[0]);
            $article_categories_id = array_column($article_categories, 'cat_id');
        } else {
            $article_categories_id = [];
        }

        $data['categories'] = $categories_line;
        $data['article_categories'] = $article_categories_id;   
        echo(json_encode($data));
        die;
    }

    public function admin_update_categ_ajax()
    { 
        $categories_all = $this->model->getCategories();
        $categories_all_line = structure_to_line($categories_all, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);
        $article_categories_id = isset($_POST['article_categories']) ? $_POST['article_categories'] : [];

        if ( isset($_POST['operation']) && $_POST['operation'] == 'add') {
            $article_categories_id[] = $this->params[0];
            $parents_categories_by_id = structure_to_line($categories_all, $options = ['begin_id' => $this->params[0], 'nested_level' => 0, 'field_id' => 'parent_id', 'field_id_parent' => 'id']);
            foreach ($parents_categories_by_id as $parent_categ) {
                $article_categories_id[] = $parent_categ['id'];
            }
        } elseif ( isset($_POST['operation']) && $_POST['operation'] == 'delete') {
            $childs_categories_by_id = structure_to_line($categories_all, $options = ['begin_id' => $this->params[0], 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);
            $childs_categories_by_id = array_column($childs_categories_by_id, 'id');
            $childs_categories_by_id[] = $this->params[0];

            $new_categories_of_article = [];
            foreach ($article_categories_id as $categ) {
                if (!in_array($categ, $childs_categories_by_id))
                    $new_categories_of_article[] = $categ;
            }
            $article_categories_id = $new_categories_of_article;
        }

        $data['categories'] = $categories_all_line;
        $data['article_categories'] = $article_categories_id;

        echo(json_encode($data));
        die;
    }


    /* Сохранение добавленного пользователем в статью изображения как временного файла и возвращение ссылки на него в редактор TinyMCE */
    public function admin_tinymce_upload_image_to_article()
    {
        if ($_FILES['image_tinymce'] && isset($this->params[0])) {

            $temp_images = $this->model->saveTempImages($_FILES['image_tinymce'], $this->params[0]);

            $data = [];
            $data['location'] = '/webroot/img/articles/' . $temp_images[0];
            $data_json = json_encode($data);
            echo $data_json;
        }
        die;
    }


}
