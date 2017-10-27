<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

define('DS',DIRECTORY_SEPARATOR);
define('ROOT',dirname(dirname(__FILE__)));
define('VIEWS_PATH',ROOT.DS.'views');
define('ARTICLES_IMG_PATH',ROOT.DS.'webroot'.DS.'img'.DS.'articles');
define('BACKGROUNDS_IMG_PATH',ROOT.DS.'webroot'.DS.'img'.DS.'background');
define('CSS_PATH',ROOT.DS.'webroot'.DS.'css');

try {
    require_once(ROOT.DS.'lib'.DS.'init.php');

    session_start();

    App::run($_SERVER['REQUEST_URI']);

} catch (Exception $e) {
//    echo $e->getMessage();
    echo "Error!";
}





