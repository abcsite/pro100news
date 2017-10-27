<?php

class Home_page extends Model
{

    public  function getCategList($only_published = false){
        $sql = 'select * from categories where 1';
        if ( $only_published) {
            $sql .= ' and displayed = 1';
        }
        $sql .= ' order by category_name ';
        return $this->db->query($sql);
    }

    public function getArticlesByCategId($cat_id)
    {
        $cat_id = (int)$cat_id;
        $artPerCateg = 5;
        $sql = "SELECT DISTINCT  ca.id_category cat_id, a.id art_id,  a.title art_title, a.date_published art_data  FROM categories_of_article ca JOIN articles a  
                ON ca.id_category = '{$cat_id}' AND ca.id_article = a.id ORDER BY a.date_published DESC LIMIT {$artPerCateg}
         ";
        $result = $this->db->query($sql);
        return  $result ;
    }











    

    public function getMaxValue($table, $field, $whereField = '1', $whereValue = '1')
    {
        $sql = "select max({$field}) as max from {$table} where {$whereField} = '{$whereValue}' ";
        $res = $this->db->query($sql);

        return $res[0]['max'];
    }

    public function save($data, $id = null)
    {
        if (!isset($data['author']) || !isset($data['title']) || $data['title'] == '' || !isset($data['description'])
            || !isset($data['text']) || !isset($data['tags'])) {
            return false;
        }

        $id = (int)$id;
        $author = $this->db->escape($data['author']);
        $title = $this->db->escape($data['title']);
        $description = $this->db->escape($data['description']);
        $text = $this->db->escape($data['text']);
        $tags = $this->db->escape($data['tags']);
        $is_published = isset($data['is_published']) ? 1 : 0;

        if (!$id) {  // Add new record
            $date_created = date("Y-m-d H:i:s");

            $sql = "
                insert into articles
                  set author = '{$author}',
                      title = '{$title}',
                      description = '{$description}',
                      text = '{$text}',
                      is_published = '{$is_published}',
                      date_created = '{$date_created}',
                      tags = '{$tags}'
            ";
            $sql = ($is_published) ? $sql . ", date_published = '" . date("Y-m-d H:i:s") . "'" : $sql;

        } else {  // Update existing record

            if (isset($data['date_created'])) {
                $date_created = $data['date_created'];
            } else {
                return false;
            }

            if (isset($data['date_published']) && isset($data['is_published_old'])) {
                if (isset($data['is_published']) && $data['is_published_old'] == 0) {
                    $date_published = date("Y-m-d H:i:s");
                } else {
                    $date_published = $data['date_published'];
                }
            } else {
                return false;
            }

            $sql = "
                update articles
                  set author = '{$author}',
                      title = '{$title}',
                      description = '{$description}',
                      text = '{$text}',
                      is_published = '{$is_published}',
                      date_created = '{$date_created}',
                      date_published = '{$date_published}',
                      tags = '{$tags}'
                  where id = {$id}
            ";
        }

        return $this->db->query($sql);
    }

    public function delete($id)
    {
        $id = (int)$id;
        $result = true;

        $resArr = $this->getImgsByArticleId($id);
        if ($resArr) {
            foreach ($resArr as $img) {
                $uploadfile = ARTICLES_IMG_PATH . DS . $img['full_name'];
                $result = $result && unlink($uploadfile);
            }
        }

        $sql = "delete from articles where id = {$id}";
        $result = $result && $this->db->query($sql);

        $sql = "delete from images_of_article where id_article = {$id}";
        $result = $result && $this->db->query($sql);

        return $result;
    }

    public function getImgsByArticleId($id)
    {
        $id = (int)$id;
        $sql = "select * from images_of_article where id_article = '{$id}' order by id ";
        $result = $this->db->query($sql);
        return $result;
    }

    public function saveImages($data, $id = null)
    {
        $id = (int)$id;
        $maxId = $this->getMaxValue('articles', 'id');

        if ($id) {
            $maxNum = $this->getMaxValue('images_of_article', 'num', 'id_article', $id);
            $maxNum = ($maxNum === null) ? 0 : $maxNum + 1;
        } else {
            $maxNum = 0;
        }

        $maxImgId = $this->getMaxValue('images_of_article', 'id');
        $maxImgId = ($maxImgId === null) ? 0 : $maxImgId;

        $countImgs = count($data['name']);
        $result = true;

        for ($i = 0; $i < $countImgs; $i++) {
            $num = $i + $maxNum + 1;
            $imgId = $maxImgId + $i + 1;

            if ($data['name'][$i] == '') {
                break;
            } else {
                $name = $this->db->escape(basename($data['name'][$i]));
                $full_name = $imgId . '_' . $name;
                $uploadfile = ARTICLES_IMG_PATH . DS . $full_name;
                if (!move_uploaded_file($data['tmp_name'][$i], $uploadfile)) {
                    $result = false;
                    break;
                }
            }
            if ($result) {
                if (!$id) {
                    $sql = "
                        insert into images_of_article
                          set id = '{$imgId}',
                              id_article = '{$maxId}',
                              num = '{$num}',
                              name = '{$name}',
                              full_name = '{$full_name}'
                    ";
                } else {
                    $sql = "
                        insert into images_of_article
                          set id = '{$imgId}',
                              id_article = '{$id}',
                              num = '{$num}',
                              name = '{$name}',
                              full_name = '{$full_name}'
                    ";
                }

                if (!$this->db->query($sql)) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;

    }

    public function replaceImages($data, $id)
    {
        $id = (int)$id;
        $result = true;
//var_dump($data['name']);die;
        
        foreach ($data['name'] as $imgId => $imgName) {
            if ($imgName != '') {
                $name = $this->db->escape($imgName);
                $full_name = $imgId . '_' . $name;

                $sql = "select full_name from images_of_article where id = {$imgId} ";
                $res = $this->db->query($sql);

                if ($res) {
                    $old_full_name = $res[0]['full_name'];
                    $result = $result && true;
                } else $result = false;

                $uploadfile = ARTICLES_IMG_PATH . DS . $full_name;

                if ($result) {
                    $result = $result && unlink(ARTICLES_IMG_PATH . DS . $old_full_name);
                    $result = $result && move_uploaded_file($data['tmp_name'][$imgId], $uploadfile);
                }
                if ($result) {
                    $sql = "
                            update images_of_article
                              set name = '{$imgName}',
                                  full_name = '{$full_name}'
                              where id = {$imgId}
                            ";
                    $result = $result && $this->db->query($sql);
                }
            }
        }
        return $result;
    }

    public function del_image($full_name)
    {
        $sql = "delete from images_of_article where full_name = '{$full_name}' ";
        $result = $this->db->query($sql);

        $uploadfile = ARTICLES_IMG_PATH . DS . $full_name;
        $result = $result && unlink($uploadfile);

        return $result;
    }

    public function add_cat($catId, $id)
    {
        $id = (int)$id;
        $catId = (int)$catId;

        $sql = "
                insert into categories_of_article
                  set id_article = '{$id}',
                      id_category = '{$catId}'
            ";
        return $this->db->query($sql);
    }
    
    public function delete_cat($catId, $id)
    {
        $id = (int)$id;
        $catId = (int)$catId;

        $sql = " delete from categories_of_article  where id_article = '{$id}' and id_category = '{$catId}' ";
        return $this->db->query($sql);
    }




}


