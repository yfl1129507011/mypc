<?php
/**
* 文件方式存储session信息 session_files.class.php
*/
class session_files {

  function __construct()
  {
    $path = pc_base::load_config('system', 'session_savepath');  //获取文件位置
    ini_set('session.save_handler', 'files');  //设置文件存储
    session_save_path($path);  //设置session的保存路径
    session_start();
  }
}

 ?>
