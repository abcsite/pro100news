<?php

class HomeController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Home_page();
    }


    public function index()
    {
        /* Задаем параметры и получаем контент для центральной части главной страницы (список статей) */
        $data_module_articles = [];
        $data_module_articles['articles_url_base'] = '/home/';
        $data_module_articles['filter'] = $_GET;
        $data_module_articles['filter']['order_by'][] = '-a.date_published';
        $data_module_articles['filter']['order_by'][] = '-a.title';

        $module_articles_list = new Module_articles_listController($data_module_articles);
        $articles_list_data = $module_articles_list->get_articles_filter();
        $this->data['module_articles_list'] = $module_articles_list->get_view( $articles_list_data );

        /* Если пользователем выбрана категория , передаем ее и родительские категории на страницу (для создания "хлебных крошек") */
        if ( isset($_GET['categ']) && is_array($_GET['categ']) && count($_GET['categ']) === 1) {
            
            $categories_all = $this->model->getCategList();
            $parents_by_category_id = structure_to_line($categories_all, $options = ['begin_id' => (int)$_GET['categ'][0], 'nested_level' => 0, 'field_id' => 'parent_id', 'field_id_parent' => 'id']);

            $hornav_categories = [];
            foreach ($parents_by_category_id as $parent_categ) {
                $hornav_categories[] = $parent_categ;
            }
            $this->data['selected_categ'] = array_reverse($hornav_categories); 
        }

        /* Для списка статей получаем список имен каждого первого изображения в статье (для вывода в слайдере) */
        $data = $articles_list_data;
        $images = [];
        $articles_count = count($data['articles']);
        for ($i = 0; $i < $articles_count && $i < 10 ; $i++ ) {
            $article_images = $module_articles_list->getModel()->getImgsByArticleId( $data['articles'][$i]['art_id'] );
            if ($article_images[0]) {
                $arr = ['img' => $article_images[0]['full_name'], 'article' => $data['articles'][$i] ];
                $images[] = $arr;
            }
        }
        $this->data['images'] = $images;

        /* Задаем параметры и получаем контент для левой части главной страницы (список категорий) */
        $data_module_side_left = [];
        $data_module_side_left['categories_url_base'] = '/home/';

        $module_side_left = new Module_side_leftController($data_module_side_left);
        $this->data['module_side_left'] = $module_side_left->get_view();

        /* Задаем параметры для модуля для получения списка ТОП-новостей за день в правой части главной страницы */
        $data_module_articles_top_day = [];
        $data_module_articles_top_day['filter']['date_min'] = date('Y-m-d h:i:s', time() - 60 * 60 * 24);
        $data_module_articles_top_day['filter']['order_by'][] = '-a.visited';
        $data_module_articles_top_day['filter']['order_by'][] = '-a.date_published';
        $data_module_articles_top_day['filter']['limit_count'] = 10;
        $data_module_articles_top_day['filter']['limit_offset'] = 0;

        $module_articles_top_day = new Module_articles_listController($data_module_articles_top_day);
        $data_top_day = $module_articles_top_day->get_articles_filter();

        /* Задаем параметры для модуля для получения списка ТОП-новостей за неделю в правой части главной страницы */
        $data_module_articles_top_week = [];
        $data_module_articles_top_week['filter']['date_min'] = date('Y-m-d h:i:s', time() - 60 * 60 * 24 * 7);
        $data_module_articles_top_week['filter']['order_by'][] = '-a.visited';
        $data_module_articles_top_week['filter']['order_by'][] = '-a.date_published';
        $data_module_articles_top_week['filter']['limit_count'] = 10;
        $data_module_articles_top_week['filter']['limit_offset'] = 0;

        $module_articles_top_week = new Module_articles_listController($data_module_articles_top_week);
        $data_top_week = $module_articles_top_week->get_articles_filter();

        /* Задаем параметры для модуля для получения списка ТОП-новостей за месяц в правой части главной страницы */
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


        /* Получаем контент для правой части главной страницы */
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
        $this->index();
        return VIEWS_PATH . '/home/index.html';
    }


}
