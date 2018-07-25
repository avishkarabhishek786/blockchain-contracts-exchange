<?php

if(!isset($_SESSION)) {
    session_start();
}

//SITE DOMAIN NAME WITH HTTP
defined("SITE_URL") || define("SITE_URL", "http://".$_SERVER['SERVER_NAME']);

//DIRECTORY SEPARATOR
defined("DS") || define("DS", DIRECTORY_SEPARATOR);

//ROOT PATH
defined("ROOT_PATH") || define("ROOT_PATH", realpath(dirname(__FILE__) . DS . ".." . DS));

//CLASSES DIR
defined("CLASSES_DIR") || define("CLASSES_DIR", "classes");

//INCLUDES DIR
defined("INCLUDES_DIR") || define("INCLUDES_DIR", "includes");

//VIEWS DIR
defined("VIEWS_DIR") || define("VIEWS_DIR", "views");

//CONFIG DIR
defined("CONFIG_DIR") || define("CONFIG_DIR", "config");

//CONFIG DIR
defined("IMG_DIR") || define("IMG_DIR", "images");

if(isset($_SESSION['user_name'])) {
//USER DIR
    defined("USER_DIR") || define("USER_DIR", "user". DS .$_SESSION['user_name']. DS ."uploads". DS);
} else {
//USER DIR
    defined("USER_DIR") || define("USER_DIR", null);
}


//JS DIR
defined("JS_DIR") || define("JS_DIR", "js");

//STYLE DIR
defined("STYLE_DIR") || define("STYLE_DIR", "style");


//add all above directories to the include path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(ROOT_PATH.DS.CLASSES_DIR),
    realpath(ROOT_PATH.DS.INCLUDES_DIR),
    realpath(ROOT_PATH.DS.VIEWS_DIR),
    realpath(ROOT_PATH.DS.CONFIG_DIR),
    realpath(ROOT_PATH.DS.USER_DIR),
    realpath(ROOT_PATH.DS.JS_DIR),
    realpath(ROOT_PATH.DS.STYLE_DIR),
    get_include_path()
)));


