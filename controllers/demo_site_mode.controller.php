<?php

class demo_site_modeController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Article();
    }

    public function articles_list()
    {
        $articles = $this->model->getList(false);

        $max_date = strtotime('2017-10-01');

        foreach ($articles as $article) {
            $date_published = strtotime($article['date_published']);
            if ($date_published > $max_date) {
                $max_date = $date_published;
            }
        }

        $now = time();
        $count_article = count($articles);
        $time_interval = 60 * 60 * 8 ;

        if ( ($now - $max_date) > (60 * 60 * 12) ) {

            for($i = 0; $i < $count_article; $i++) {
                $new_time_published = (int) ($now - ($time_interval * $i) - rand(1, $time_interval) );
                $new_data_published = date('Y-m-d H:i:s', $new_time_published);

                $this->model->demo_mode_save($new_data_published, $articles[$i]['id']);
            }
        }
    }


}
