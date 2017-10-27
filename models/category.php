<?php

class Category extends Model{

    public  function getList($only_published = false){
        $sql = 'select * from categories where 1';
        if ( $only_published) {
            $sql .= ' and displayed = 1';
        }
        $sql .= ' order by category_name ';
        return $this->db->query($sql);
    }

    public  function  save($data, $id = null){
        if ( !isset($data['category_name']) ) {
            return false;
        }

        $id = (int)$id;
        $parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : '0';
        $category_name = $this->db->escape($data['category_name']);

        if ( !$id) {  
            $sql = "
                insert into categories
                  set parent_id = '{$parent_id}',
                     category_name = '{$category_name}'
            ";
        } else {  
            $sql = "
                update categories
                  set category_name = '{$category_name}'
                  where id = {$id} 
            ";
        }

        return $this->db->query($sql);
    }

    public function delete($id_arr) {
        foreach ($id_arr as $key => $id) {
            $id_arr[$key] = (int) $id;
        }
        $id_str = implode(',', $id_arr);
        $sql = "delete from categories where id IN ({$id_str}) ";
        return $this->db->query($sql);

    }

}