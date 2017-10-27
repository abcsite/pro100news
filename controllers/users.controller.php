<?php

class UsersController extends  Controller {

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->model = new User();
    }

    
    public  function  login() {
        if ( Session::get('login') ) {
            Session::destroy();
            Router::redirect('/users/login');
        }
        if ( $_POST && isset( $_POST['login']) && isset( $_POST['password']) ) {
            $user = $this->model->getByLogin( $_POST['login']);
            $hash = md5(Config::get('salt').$_POST['password']);
            if ( $user && $user['is_active'] && $hash == $user['password'] ) {
                Session::set('login', $user['login']);
                Session::set('role', $user['role']);

                if ( Session::get('role') == 'admin') {
                    Router::redirect('/admin/articles/');
                } else {
                    Router::redirect('/home/');
                }
            } else {
                Session::setFlash('Неверный логин или пароль!');
            }
        }
    }

    
    public  function  logout() {
        Session::destroy();
        Router::redirect('/');
    }

    
    public function admin_index() {
        
        if ( $_POST ) {
            if (isset($_POST['is_active_hide'])) {
                $changed_data = [];

                foreach ( $_POST['is_active_hide'] as $id => $is_active_old ) {
                    if ( isset($_POST['is_active_checkbox'][$id]) && ($is_active_old == '0') ) {
                        $changed_data[$id] = 1 ;
                    } elseif ( !isset($_POST['is_active_checkbox'][$id]) && $is_active_old == '1' ) {
                        $changed_data[$id] = 0 ;
                    }
                }
                $result = $this->model->activate($changed_data);
                if (!$result) {
                    Session::setFlash('Ошибка!');
                }
            }
        }
        $this->data = $this->model->getList();
    }

    
    public  function  register() {

        if ( $_POST ) {
            
            /* В этом массиве ключи (первого уровня вложенности) соответствуют названиям полей формы, значения из которых нужно обрабатывать ('login', 'email' ...).
             Ключи второго уровня вложенности задают названия методов, с помощью которых будут обрабатываться значения из соответствующих полей формы ('filter_clean', 'valid_str_length' ...).
             Значениями для ключей второго уровня вложенности должны быть массивы с данными, которые будут передаваться в качестве параметров в соответствующий метод.
             Для методов валидации (имеют в названии префикс 'valid_')  в массиве параметров может задаваться ключ 'err_message' и его значение (сообщение),
             которое будет выводиться пользователю в случае ошибки при валидации значения соответствующего поля формы.*/
            $options = ['login' => ['filter_clean' => [],
                                    'filter_sql' => [],
                                    'valid_str_length' => [ $login_min = 4,
                                                            $login_max = 35,
                                                           'err_message' => 'Количество символов должно быть в пределах '. $login_min.' - '.$login_max.' .' ],
                                    'valid_login_is_not_used' => [ App::$db ,
                                                              'err_message' => 'Этот логин уже используется'] ],
                        'email' => ['filter_clean' => [],
                                    'filter_sql' => [],
                                    'valid_str_length' => [ $email_min = 4,
                                                            $email_max = 100,
                                                            'err_message' => 'Некорректный email'. $email_min.' - '.$email_max.' .' ],
                                    'valid_email' => [ 'err_message' => 'Некорректный email' ] ],
                        'password' => ['filter_clean' => [],
                                       'valid_str_length' => [ $password_min = 5,
                                                               $password_max = 32,
                                                               'err_message' => 'Количество символов должно быть в пределах '. $password_min.' - '.$password_max.' .' ],
                                       'filter_hash_md5' => []]
                         ] ;
            
            $validation = new Validation( App::$db );
            $valid_data = $validation->validate( $_POST, $options);

            if ( !isset($valid_data['err_messages']) || !count( $valid_data['err_messages'] )) {
                
                $result_check = $this->model->register( $valid_data['data'] );

                if ($result_check) {
                    Session::setFlash('Вы успешно зарегистрированы.');
                    $this->data['hide_html_element'] = 'hide';

                    return;
                }
            }

            Session::setFlash('Ошибка при регистрации.');
            $this->data['valid_errors'] = $valid_data['err_messages'];
            $this->data['hide_html_element'] = '';
            $this->data['form_data'] = $_POST;

        } else {
            $this->data['hide_html_element'] = '';
            $this->data['form_data']['login'] = '';
            $this->data['form_data']['email'] = '';
            $this->data['form_data']['password'] = '';
        }
    }

}