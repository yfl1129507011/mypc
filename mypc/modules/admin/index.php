<?php
defined('IN_MYPC') or die('No permission resources');

/**
 *
 */
class index{

  function __construct()
  {
    # code...
  }


  public function init(){
    $admin_model = mp_base::load_model('admin_model');
    $list = $admin_model->select('userid=1', 'userid, username, email');
    echo "<pre>";
    var_dump($admin_model);
    var_dump($list);
  }
}

 ?>
