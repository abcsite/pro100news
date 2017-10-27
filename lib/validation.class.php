<?php

class Validation {

    protected $db;

    public function __construct($db){
        $this->db = $db;
    }


/*     $inp_data - массив с данными из формы (напр. $_POST). Маассив $options определяет, какие поля формы будут обрабатываться, какими методами, с какими параметрами.
     В этом массиве ключи (первого уровня вложенности) соответствуют названиям полей формы, значения из которых нужно обрабатывать ('login', 'email' ...).
     Ключи второго уровня вложенности задают названия методов, с помощью которых будут обрабатываться значения из соответствующих полей формы ('filter_clean', 'valid_str_length' ...).
     Значениями для ключей второго уровня вложенности должны быть массивы с данными, которые будут передаваться в качестве параметров в соответствующий метод.
     Для методов валидации (имеют в названии префикс 'valid_') в массиве параметров может задаваться ключ 'err_message' и его значение (сообщение),
     которое будет выводиться пользователю в случае ошибки при валидации значения соответствующего поля формы.*/
    public function validate($inp_data, $options) {
        /* Массив с результирующими  данными (проверенными и обработанными) из полей формы,
         который будет содержать также и массив с сообщениями об ошибках при проверке данных из полей формы*/
        $result = [];

        if ( !isset( $options) || !is_array($options)) {
            $result['data'] = $inp_data;  /*$result['data'] - массив с данными полей формы, $result['err_messages'] - массив с сообщениями об ошибках при проверке полей формы*/

        } else {
                /* Перебираются названия полей формы и соответсвующие им массивы методов обработки этих полей*/
            foreach ( $options as $field => $arr_methods) {
                if ( !isset( $inp_data[$field])) {
                    throw new Exception('Field "'.$field.'" does not exist in input data (File: "'.__FILE__.'". Line: "'.__LINE__.'". ).');
                }
                if ( $inp_data[$field] === '' ) {
                    $result['err_messages'][$field] = 'Error: Empty field "'.$field.'".' ;
                    $result['data'][$field] = '' ;

                } elseif ( !isset( $options[$field] ) || !is_array( $options[$field]) ) {
                    $result['data'][$field] = $inp_data[$field] ;
                }
                else {
                        /* Перебираются названия методов (функций) для обработки значения из одного поля формы и соответсвующие массивы с параметрами для каждого метода*/
                    foreach ( $arr_methods as $method => $arr_params) {

                        if ( !method_exists( $this, $method) ) {
                            throw new Exception('Method "'.$method.'" of class "'.__CLASS__.'" does not exist.  (File: "'.__FILE__.'". Line: "'.__LINE__.'". ).');
                        }
                        if ( !isset( $options[$field][$method] ) || !is_array( $options[$field][$method] ) ) {
                            throw new Exception('The parameters of "'.$method.'" method of "'.__CLASS__.'"  class are not an array.');
                        }
                             /* Проверка, является ли обработчик (метод) валидатором (проверяет значения полей) или фильтром (изменяет значения полей)*/
                        if ( substr($method, 0, 6) == 'filter' ) {
                            $result['data'][$field] = $this->$method( $inp_data[$field], $arr_params);

                        } elseif ( substr($method, 0, 5) == 'valid' ) {
                            $result['data'][$field] = $inp_data[$field];

                            if ( !$this->$method( $inp_data[$field], $arr_params) ) {
                                $err_massage = $options[$field][$method]['err_message'] ;
                                $result['err_messages'][$field] = $err_massage ? $err_massage : 'Error: Incorrect input data.' ;
                            }
                        } else {
                            throw new Exception('Incorrect method name: "'.$method.'" of class "'.__CLASS__.'" .  (File: "'.__FILE__.'". Line: "'.__LINE__.'". ).');
                        }
                  }
                }
            }
        }
        return $result;

    }
    

    public  function  filter_clean( $str, $options = [] ){
        $str = trim($str, ' ');
        $str = stripslashes($str);
        $str = strip_tags($str);
        $str = htmlspecialchars($str);
        return $str;
    }

    public  function  filter_sql($str, $options){
        return $this->db->escape($str);
    }

    public  function filter_hash_md5($data) {
        return md5( Config::get('salt') . $data ) ;
    }

    public  function valid_str_length($str, $options) {
        return ( mb_strlen($str) >= (int)$options[0] && mb_strlen($str) <= (int)$options[1]) ;
    }

    public  function valid_email($email, $options) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ;
    }

    public function valid_login_is_not_used( $login, $options) {
        $sql = "select * from users where login = '{$login}' limit 1";
        $result = $options[0]->query($sql);
        if ( $result ) {
            return false;
        }
        return true;
    }

}