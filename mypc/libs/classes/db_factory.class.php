<?php
/**
* db_factory.class.php 数据库工厂类
*/

//最终类，禁止继承
final class db_factory{
  //当前数据库工厂类静态实例
  private static $db_factory;

  //数据库配置列表
  protected $db_config = array();

  //数据库操作实例化列表
  protected $db_list = array();


  //构造函数
  public function __construct(){

  }


  /**
  * 返回当前终级类对象的实例
  * @param $db_config 数据库配置
  * @return object
  */
  static public function get_instance($db_config=''){
    if ($db_config == '') {
      $db_config = pc_base::load_config('database');
    }
    if (db_factory::$db_factory == '') {
      db_factory::$db_factory = new db_factory();
    }
    if ($db_config != '' && $db_config != db_factory::$db_factory->db_config) {
      db_factory::$db_factory->db_config = array_merge($db_config, db_factory::$db_factory->db_config);
    }

    return db_factory::$db_factory;
  }


  /**
  * 获取指定数据库操作实例
  * @param $db_name 数据库配置名称
  */
  public function get_database($db_name){
    if (!isset($this->db_list[$db_name]) || !is_object($this->db_list[$db_name])) {
      $this->db_list[$db_name] = $this->connect($db_name);
    }

    return $this->db_list[$db_name];
  }


  /**
  * 加载数据库的操作模型
  * @param $db_name 数据库配置
  * @return object
  */
  public function connect($db_name){
    $object = null;
    switch ($this->db_config[$db_name]['type']) {
      case 'mysql':
        mp_base::load_sys_class('mysql', '', 0);
        $object = new mysql;
        break;

      default:
        mp_base::load_sys_class('mysql', '', 0);
        $object = new mysql;
    }

    $object->open($this->db_config[$db_name]);  //连接数据

    return $object;
  }


  //关闭数据库连接
  protected function close(){
    foreach ($this->db_list as $db) {
      $db->close();
    }
  }


  //析构函数
  public function __destruct(){
    $this->close();
  }
}
 ?>
