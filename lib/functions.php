<?php

function deb($data, $stop = 1) {
    var_dump($data);
if ($stop) {die;}
}

function my_exists($data) {
    if ( isset($data)) {
        return $data;
    } else {
        return '';
    }
}

function my_format_date($data) {
    $begin_day = date('Y-m-d' , time() );
    $begin_day = strtotime($begin_day);
    $data_stamp = strtotime($data);
    if ($data_stamp < $begin_day )  {
        $new_date = date('Y.m.d' , strtotime( $data ) );
    } else {
        $new_date = date('H:i' , strtotime( $data ) );
    }
    return $new_date;
}




/* Приводит неупорядоченный или неструктурированный список (массив $unstructured_arr) 
с дочерними и родительскими элементами 
к структурированному списку (ОДНОМЕРНОМУ массиву), где все элементы следуют в порядке их отношений родитель-потомок (как список в оглавлении книги).
Название поля в маасиве, указывающего id элемента, задается в параметре 'field_id' .
Название поля в маасиве, указывающего id родительского элемента, задается в параметре  'field_id_parent' .
Параметр 'begin_id' задает id элемента, с которого начнется поиск всех его дочерних элементов (в результирующий массий выведутся только эти дочерние элементы)
В результирующем массиве к каждому элементу добавиться поле 'nested_level', в котором будет указан уровень вложенности этого элемента (как отступ элемента в оглавлении кноги)
Параметр функции 'nested_level' задает начальный уровень вложености (для самых старших родителей)
*/
function structure_to_line($unstructured_arr, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'id_parent' ] )
{
    if ($unstructured_arr) {
        $arr = [];
        foreach ($unstructured_arr as $row) {
            if ($row[$options['field_id_parent']] == $options['begin_id'] ) {
                $row['nested_level'] = $options['nested_level'];
                $arr[] = $row;
                $new_options = $options ;
                $new_options['begin_id'] = $row[$options['field_id']];
                $new_options['nested_level'] = $options['nested_level'] + 1 ;
                $arr_childs = structure_to_line($unstructured_arr, $new_options );
                if ( count($arr_childs) ) {
                    foreach ($arr_childs as $row_child) {
                        $arr[] = $row_child;  
                    }
                }
            }
        }
        return $arr;

    } else {
        return [];
    }
}



//function structure_to_tree($unstructured_arr, $id = 0, $data = null)
//{
//    if ($unstructured_arr) {
//        $arr = [];
//        foreach ($unstructured_arr as $row) {
//            if ($row['id_parent_comment'] == $id) {
//                $arr[$row['id_comment']] = $row;
//                $arr[$row['id_comment']]['childs'] = structure_to_tree($unstructured_arr, $row['id_comment'], $data);
//            }
//        }
//        if (count($arr)) {
//            return $arr;
//        } else {
//            return null;
//        }
//
//    } else {
//        return null;
//    }
//}
