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
    echo "<pre>";
    var_dump(mp_base::load_model('admin_model'));
  }
}

 ?>
