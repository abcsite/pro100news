<?php

class Article_view extends Model
{

    public function getById($id)
    {
        $id = (int)$id;
        $sql = "select * from articles where id = '{$id}' limit 1";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function visited_counter($id)
    {        
        $id = (int)$id;
        $sql = "
                update articles
                  set visited = visited + 1
                  where id = {$id}
            ";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function getCommentsByArticleId($id_article = 1, $only_published_comments = false)
    {
        $id_article = (int)$id_article;
        $only_published = $only_published_comments ? ' AND c.is_published = 1 ' : '';
        $sql = "select  c.* , u.* , count(like_user) as like_count 
                      FROM comments c JOIN users u ON id = id_user AND id_article = '{$id_article}'  {$only_published}
                      LEFT JOIN likes ON id_comment = like_comment GROUP BY c.id_comment ";

        $result = $this->db->query($sql);
        return $result;
    }

    public function getCommentById($id_comment)
    {
        $id_comment = (int)$id_comment;

        $sql = "select * from comments where id_comment = '{$id_comment}'  ";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function getLike($id_comment, $id_user)
    {
        $id_comment = (int)$id_comment;
        $id_user = (int)$id_user;

        $sql = "select * from likes where like_comment = '{$id_comment}' and  like_user = '{$id_user}' ";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function addLike($id_comment, $id_user)
    {
        $id_comment = (int)$id_comment;
        $id_user = (int)$id_user;

        $sql = " insert into likes
                  set like_comment = '{$id_comment}',
                      like_user = '{$id_user}'
               ";
        $result = $this->db->query($sql);
        return $result;
    }


    public function getImgsByArticleId($id)
    {
        $id = (int)$id;
        $sql = "select * from images_of_article where id_article = '{$id}' order by id ";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getUserByLogin($login)
    {
        $login = $this->db->escape($login);
        $sql = "select * from users where login = '{$login}' limit 1";
        $result = $this->db->query($sql);
        if (isset($result[0])) {
            return $result[0];
        }
        return false;
    }

    public function comment_save($data, $comment_id = null)
    {
        if (!isset($data['text']) || !isset($data['id_comment']) || !isset($data['id_user']) || !isset($data['id_article'])) {
            return false;
        }

        $comment_id = (int)$comment_id;
        $parent_id = (int)$data['id_comment'];
        $user_id = (int)$data['id_user'];
        $article_id = (int)$data['id_article'];
        $text = $this->db->escape($data['text']);
        
        if (!$comment_id) {  
            $date = date("Y-m-d H:i:s");

            $sql = "
                insert into comments
                  set id_parent_comment = '{$parent_id}',
                      id_user = '{$user_id}',
                      id_article = '{$article_id}',
                      text = '{$text}',
                      date = '{$date}',
                      like_ok = ''
            ";

        } else {

            $sql = "
                update comments
                  set text = '{$text}'
                  where id_comment = '{$comment_id}'
            ";
        }

        return $this->db->query($sql);
    }


    public function setPublishComment($comments_id, $is_published = 1)
    {
        $is_published = (int)$is_published;

        if (count($comments_id)) {
            foreach ($comments_id as $key => $id) {
                $comments_id[$key] = (int)$id;
            }
            $comments_id_str = implode(',' , $comments_id );

            $sql = "
                update comments
                  set is_published = '{$is_published}'
                  where id_comment IN ({$comments_id_str})
            ";
            return $this->db->query($sql);
        } else {
            return true;
        }
    }

    public function deleteComments($comments_id)
    {
        if (count($comments_id)) {
            foreach ($comments_id as $key => $id) {
                $comments_id[$key] = (int)$id;
            }
            $comments_id_str = implode(',' , $comments_id );
            
            $sql = "
                delete from  comments
                  where id_comment IN ({$comments_id_str})
            ";
            return $this->db->query($sql);
        } else {
            return true;
        }
    }


}


