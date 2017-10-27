<?php

class Article_pageController extends Controller
{
    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Article_view();
    }


    public function view()
    {
        if (isset($this->params[0])) {

            if (Session::get('role') != 'admin') {
                $this->model->visited_counter($this->params[0]);
            }
            
            $result = $this->model->getById($this->params[0]);

            if ($result) {
                if (Session::get('role') != 'admin' && $result['is_published'] == '0') {
                    Session::setFlash('Такой страницы не существует.');
                } else {
                    $this->data['article_page'] = $result;
                    $tags = explode(',', $result['tags']);
                    $this->data['article_page']['tags'] = $tags;

                    $this->data['article_images'] = $this->model->getImgsByArticleId($this->params[0]);

                    if (Session::get('login')) {
                        $login = Session::get('login');
                        $user = $this->model->getUserByLogin($login);
                        $this->data['user'] = $user;
                    } else {
                        $this->data['user'] = null;
                    }
                    
                }

            }

            /* Задаем параметры и получаем контент для левой части страницы статьи  */
            $data_module_side_left = [];
            $data_module_side_left['categories_url_base'] = '/home/';

            $module_side_left = new Module_side_leftController( $data_module_side_left );
            $this->data['module_side_left'] = $module_side_left->get_view();

            /* Задаем параметры для модуля для получения списка ТОП-новостей за день в правой части страницы статьи  */
            $data_module_articles_top_day = [];
            $data_module_articles_top_day['filter']['date_min'] =  date('Y-m-d h:i:s', time() - 60 * 60 * 24);
            $data_module_articles_top_day['filter']['order_by'][] = '-a.visited';
            $data_module_articles_top_day['filter']['order_by'][] = '-a.date_published';
            $data_module_articles_top_day['filter']['limit_count'] = 10;
            $data_module_articles_top_day['filter']['limit_offset'] = 0;

            $module_articles_top_day = new Module_articles_listController( $data_module_articles_top_day );
            $data_top_day = $module_articles_top_day->get_articles_filter();

            /* Задаем параметры для модуля для получения списка ТОП-новостей за неделю в правой части страницы статьи  */
            $data_module_articles_top_week = [];
            $data_module_articles_top_week['filter']['date_min'] =  date('Y-m-d h:i:s', time() - 60 * 60 * 24 * 7 );
            $data_module_articles_top_week['filter']['order_by'][] = '-a.visited';
            $data_module_articles_top_week['filter']['order_by'][] = '-a.date_published';
            $data_module_articles_top_week['filter']['limit_count'] = 10;
            $data_module_articles_top_week['filter']['limit_offset'] = 0;

            $module_articles_top_week = new Module_articles_listController( $data_module_articles_top_week );
            $data_top_week = $module_articles_top_week->get_articles_filter();

            /* Задаем параметры для модуля для получения списка ТОП-новостей за месяц в правой части страницы статьи  */
            $data_module_articles_top_month = [];
            $data_module_articles_top_month['filter']['date_min'] =  date('Y-m-d h:i:s', time() - 60 * 60 * 24 * 30 );
            $data_module_articles_top_month['filter']['order_by'][] = '-a.visited';
            $data_module_articles_top_month['filter']['order_by'][] = '-a.date_published';
            $data_module_articles_top_month['filter']['limit_count'] = 10;
            $data_module_articles_top_month['filter']['limit_offset'] = 0;

            $module_articles_top_month = new Module_articles_listController( $data_module_articles_top_month );
            $data_top_month = $module_articles_top_month->get_articles_filter();

            /* Задаем параметры для модуля для получения списка ТОП-новостей за все время в правой части страницы статьи  */
            $data_module_articles_top_all = [];
            $data_module_articles_top_all['filter']['order_by'][] = '-a.visited';
            $data_module_articles_top_all['filter']['order_by'][] = '-a.date_published';
            $data_module_articles_top_all['filter']['limit_count'] = 10;
            $data_module_articles_top_all['filter']['limit_offset'] = 0;

            $module_articles_top_all = new Module_articles_listController( $data_module_articles_top_all );
            $data_top_all = $module_articles_top_all->get_articles_filter();

            /* Получаем контент для правой части страницы статьи  */
            $view_data_side_right = [];
            $view_data_side_right['top_day'] = $data_top_day;
            $view_data_side_right['top_week'] = $data_top_week;
            $view_data_side_right['top_month'] = $data_top_month;
            $view_data_side_right['top_all'] = $data_top_all;

            $path_side_right = VIEWS_PATH . DS .'modules' . DS . 'side_right.html';
            $view_object_side_right = new View( $view_data_side_right, $path_side_right);
            $this->data['module_side_right'] = $view_object_side_right->render();

        } else {
            Session::setFlash('Такая страница не существует.');
        }
    }


    public function admin_view()
    {
        $this->view();
        
        return VIEWS_PATH . DS . 'article_page' . DS . 'view.html' ;
    }



    public function comments_get_ajax()
    {
        if (Session::get('role') === 'admin') {
            $comments = $this->model->getCommentsByArticleId($this->params[0], false);
        } else {
            $comments = $this->model->getCommentsByArticleId($this->params[0], true);
        }

        $comments_line = structure_to_line($comments, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id_comment', 'field_id_parent' => 'id_parent_comment' ]);
        if ($comments_line) {
            foreach ($comments_line as $key => $row) {
                if (!$row['date']) {
                    $comments_line[$key]['date'] = '';
                } else {
                    $comments_line[$key]['date'] = my_format_date( $row['date'] );
                }
            }
        }

        echo(json_encode($comments_line));
        die;
    }

    
    public function comment_add_ajax()
    {
        if (Session::get('role')) {
            if (isset($_POST['text']) && isset($_POST['id_comment']) && isset($_POST['id_user']) && isset($_POST['id_article'])) {
                $result = $this->model->comment_save($_POST);

                $this->comments_get_ajax();
            }
            die;
        }
    }

    public function admin_comment_edit_ajax()
    {
        if (Session::get('role')) {
            if ( isset($_POST['text']) && isset($_POST['id_comment']) ) {
                $result = $this->model->comment_save($_POST, $_POST['id_comment'] );

                $this->comments_get_ajax();
            }
            die;
        }
    }

    public function comment_like_ajax()
    {
        if (Session::get('role')) {
            if (isset($this->params[0]) && isset($this->params[1])) {
                $like = $this->model->getLike($this->params[0], $this->params[1]);

                if (!$like) {
                    $result = $this->model->addLike($this->params[0], $this->params[1]);

                    if ($result) {
                        echo 1;
                        die;
                    }
                }
            }
            die;
        }
    }

    public function admin_comment_publish_ajax()
    {
        if (isset($this->params[1]) ) {

            $comments = $this->model->getCommentsByArticleId($this->params[0], false);
            $comments_line = structure_to_line($comments, $options = ['begin_id' => $this->params[1], 'nested_level' => 0, 'field_id' => 'id_comment', 'field_id_parent' => 'id_parent_comment' ]);
            $comments_id = [];
            $comments_id[] = $this->params[1];
            if (count($comments_line)) {
                foreach ($comments_line as $key => $row) {
                    $comments_id[] = $row['id_comment'];
                }
            }

            $this->model->setPublishComment($comments_id, 1);
            $this->comments_get_ajax();
        }
        die;
    }

    public function admin_comment_hide_ajax()
    {
        if (isset($this->params[1]) ) {
            $comments = $this->model->getCommentsByArticleId($this->params[0], false);
            $comments_line = structure_to_line($comments, $options = ['begin_id' => $this->params[1], 'nested_level' => 0, 'field_id' => 'id_comment', 'field_id_parent' => 'id_parent_comment' ]);
            $comments_id = [];
            $comments_id[] = $this->params[1];
            if (count($comments_line)) {
                foreach ($comments_line as $key => $row) {
                    $comments_id[] = $row['id_comment'];
                }
            }

            $this->model->setPublishComment($comments_id, 0);
            $this->comments_get_ajax();
        }
        die;
    }

    public function admin_comment_delete_ajax()
    {
        if (isset($this->params[1]) ) {

            $comments = $this->model->getCommentsByArticleId($this->params[0], false);
            $comments_line = structure_to_line($comments, $options = ['begin_id' => $this->params[1], 'nested_level' => 0, 'field_id' => 'id_comment', 'field_id_parent' => 'id_parent_comment' ]);
            $comments_id = [];
            $comments_id[] = $this->params[1];
            if (count($comments_line)) {
                foreach ($comments_line as $key => $row) {
                    $comments_id[] = $row['id_comment'];
                }
            }

            $this->model->deleteComments($comments_id);
            $this->comments_get_ajax();
        }
        die;
    }

}



