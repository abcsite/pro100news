<?php

class CommentsController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Comment();
    }



    public function admin_index()
    {
        $this->data['articles'] = $this->model->getListArticles();
    }

    public function admin_publish_all_in_article()
    {
        if (isset($this->params[0]) ) {
            $this->model->publishAllCommentsByArticleId($this->params[0]);
        }
        Router::redirect('/admin/comments/');
    }

    public function admin_delete_not_publish_comm_in_article()
    {
        if (isset($this->params[0]) ) {
            $this->model->deleteNotPublishCommentsByArticleId($this->params[0]);
        }
        Router::redirect('/admin/comments/');
    }


}
