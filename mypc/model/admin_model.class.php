<?php
defined('IN_MYPC') or die('No permission resources');

mp_base::load_sys_class('model', '', 0);

/**
 * 后台管理员操作模型
 */
class admin_model extends model{

  public function __construct()
  {
    $this->db_config = C('database');
    $this->db_setting = 'default';
    $this->table_name = 'admin';
    parent::__construct();
  }
}

?>
