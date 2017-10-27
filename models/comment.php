<?php

class Comment extends Model
{

    public function getListArticles()
    {
        $sql = "SELECT  a.* , count(c.is_published) as comments_count 
                  FROM articles a LEFT JOIN comments c ON a.id = c.id_article AND c.is_published = 0 
                  GROUP BY a.id  ORDER BY comments_count DESC , a.title ";

        $result = $this->db->query($sql);
        return $result ;
    }
    
    public function publishAllCommentsByArticleId($id_article)
    {
        $id_article = (int)$id_article;
        $sql = "
                update comments
                  set is_published = '1'
                  where id_article = '{$id_article}'
            ";
        return $this->db->query($sql);
    }

    public function deleteNotPublishCommentsByArticleId($id_article)
    {
        $id_article = (int)$id_article;
        $sql = "
                delete from comments
                  where id_article = '{$id_article}' AND is_published = '0'
            ";
        return $this->db->query($sql);
    }


    
}


