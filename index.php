<?php
/**
* index.php 网站路口文件
* @author    yangfeilong
* @lastmodify    2015-12-22
*/

//定义网站根目录
define('MYPC_PATH', getcwd() . DIRECTORY_SEPARATOR);

include MYPC_PATH . '/mypc/base.php';

mp_base::create_app();