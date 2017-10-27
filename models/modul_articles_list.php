<?php

class Modul_articles_list extends Model
{
    
    public function getCategoryByIds($ids)
    { 
        if ( isset($ids) && is_array($ids)) {
            $in_ids = '';
            foreach ($ids as $id) {
                $in_ids = $in_ids . ',' . $id;
            }
            $in_ids = substr($in_ids, 1);
        } else {
            return false;
        }

        $sql = "select * from categories where id IN ({$in_ids}) ";
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

    public function getArticlesFilter($filter, $only_published = false)
    {
        if (isset($filter['categ']) && is_array($filter['categ'])) {
            $in_cat = '';
            foreach ($filter['categ'] as $cat) {
                $cat = $this->db->escape($cat);
                $in_cat = $in_cat . ',' . $cat;
            }
            $in_cat = substr($in_cat, 1);
            $in_cat = " ca.id_category IN ('{$in_cat}')";
            $join_right = '';
        } else {
            $in_cat = '1 = 1';
            $join_right = 'RIGHT';
        }

        if ( isset($filter['tags']) && is_array($filter['tags'])) {
            $in_tag = '';
            foreach ($filter['tags'] as $tag) {
                $tag = $this->db->escape($tag);
                $str = " AND a.tags LIKE '%" . $tag . "%' ";
                $in_tag = $in_tag . $str;
            }
            $in_tag = substr($in_tag, 4);
        } else {
            $in_tag = '1 = 1';
        }

        if ( isset($filter['date_min']) && $filter['date_min']) {
            $date_min = $this->db->escape($filter['date_min']);
            $date_min_str = " a.date_published >= '{$date_min}' ";
        } else {
            $date_min_str = " 1 ";
        }

        if ( isset($filter['date_max']) && $filter['date_max']) {
            $date_max = $this->db->escape($filter['date_max']);
            $date_max_str = " a.date_published <= '{$date_max}' ";
        } else {
            $date_max_str = " 1 ";
        }

        if ( isset($filter['order_by']) && $filter['order_by']) {

            if (is_array($filter['order_by'])) {
                $order_by_arr = $filter['order_by'];
            } else {
                $order_by_arr[0] = $filter['order_by'];
            }
            $order_by_list = [];
            foreach ($order_by_arr as $order_by) {
                if (substr($order_by, 0, 1) == '-') {
                    $order_by = substr($order_by, 1);
                    $order_by_list[] = "  {$order_by} DESC  ";
                } else {
                    $order_by_list[] = " {$order_by} ";
                }
            }
            $order_by_str = ' ORDER BY ' . implode(',' , $order_by_list);


        } else {
            $order_by_str = '';
        }

        if ( isset($filter['limit_count']) && $filter['limit_count']) {
            $limit_count = " LIMIT {$filter['limit_count']} ";

            if ( isset($filter['limit_offset']) && $filter['limit_offset']) {
                $limit_offset = " OFFSET {$filter['limit_offset']} ";
            } else {
                $limit_offset = '';
            }

        } else {
            $limit_count = '';
            $limit_offset = '';
        }

        $is_published = ($only_published) ? ' AND a.is_published = 1 ' : '';

        $sql = "SELECT DISTINCT  GROUP_CONCAT(ca.id_category) cat_id,  
                a.id art_id,  a.title art_title, a.description art_desc,  a.author art_author, a.text art_text,  
                a.is_published art_publish, a.date_created art_creat, a.date_published art_date, a.category art_categ, 
                a.tags art_tags, a.visited art_visit
                FROM categories_of_article ca {$join_right} JOIN  articles a  ON  ca.id_article = a.id  
                WHERE {$in_cat}  AND  {$in_tag} AND {$date_min_str} AND {$date_max_str}  {$is_published}
                GROUP BY a.id
                {$order_by_str}  {$limit_count} {$limit_offset} 
             ";

        $result = $this->db->query($sql);
        return $result;
    }

}


