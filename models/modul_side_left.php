<?php

class Modul_side_left extends Model
{

    public function getCategories()
    {
        $sql = "SELECT DISTINCT  *  FROM  categories c WHERE 1 ORDER BY c.category_name ";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getCategoryByName($name)
    {
        $sql = "SELECT DISTINCT  *  FROM  categories c WHERE c.category_name = '{$name}' ORDER BY c.category_name ";
        $result = $this->db->query($sql);
        return $result;
    }

   
}


