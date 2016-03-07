<?php
/**
 * MongoDB 操作类  mongo_db.class.php
 */
class mongo_db{
  //配置信息
  private $config = null;
  //mongo操作实例
  private $mongo;
  //当前操作的mongo数据库名
  private $curr_db_name;
  //错误信息
  private $error;

  function __construct()
  {
    # code...
  }


  public function open($config){
    $this->config = $config;
    if ($config['autoconnect'] == 1) {
      $this->connect();
    }
  }


  /**
  * 连接mongo
  */
  public function connect(){
    try {
        //5.5使用MongoClient
        $this->mongo = new MongoClient($this->config['hostname']);
        $this->selectDb($this->config['database']);
    } catch (MongoConnectionException $e) {
        $this->error = $e->getMessage();
    }
  }


  /**
  * 选择数据库
  */
  public function selectDb($dbname){
    $this->curr_db_name = $dbname;
  }


  /**
  * 插入数据
  */
  public function insert($table_name, $record){
    $dbname = $this->curr_db_name;
    try {
        $this->mongo->$dbname->$table_name->insert($record, array('safe'=>true));
        return true;
    } catch (MongoCursorException $e) {
        $this->error = $e->getMessage();
        return false;
    }

  }


  /**
  * 获取数据条数
  */
  public function count($table_name){
    $dbname = $this->curr_db_name;
    return $this->mongo->$dbname->$table_name->count();
  }


  /**
  * 更新数据
  */
  public function update($table_name, $condition, $newdata, $options=array()){
    $dbname = $this->curr_db_name;
    $options['safe'] = 1;
    if (!isset($options['multiple'])) {
      $options['multiple'] = 0;
    }

    try {
        $this->mongo->$dbname->$table_name->update($condition, $newdata, $options);
        return true;
    } catch (MongoCursorException $e) {
        $this->error = $e->getMessage();
        return false;
    }

  }


  /**
  * 删除数据
  */
  public function remove($table_name, $condition, $options=array()){
    $dbname = $this->curr_db_name;
    $options['safe'] = 1;
    try {
        $this->mongo->$dbname->$table_name->remove($condition, $options);
    } catch (MongoCursorException $e) {
        $this->error = $e->getMessage();
        return false;
    }

  }


  /**
  * 查找数据
  */
  public function find($table_name, $query_condition, $result_condition=array(), $fields=array()){
    $dbname = $this->curr_db_name;
    $cursor = $this->mongo->$dbname->$table_name->find($query_condition, $fields);
    if (!empty($result_condition['start'])) {
      $cursor->skip($result_condition['start']);
    }
    if (!empty($result_condition['limit'])) {
      $cursor->skip($result_condition['limit']);
    }
    if (!empty($result_condition['sort'])) {
      $cursor->skip($result_condition['sort']);
    }
    $result = array();
    try {
        while ($cursor->hasNext()) {
          $result[] = $cursor->getNext();
        }
    } catch (MongoCursorException $e) {
        $this->error = $e->getMessage();
        return false;
    }
    return result;
  }


  /**
  * 查找单个数据
  */
  public function findOne($table_name, $condition, $fields=array()){
      $dbname = $this->curr_db_name;
      return $this->mongo->$dbname->$table_name->findOne($condition, $fields);
  }


  /**
  * 获取错误
  */
  public function getError(){
      return $this->error;
  }


  /**
  * 关闭资源
  */
  public function close(){
      $this->mongo->close();
  }


  /**
  * 表是否存在
  */
  public function table_exists($table_name){
      $table = $this->list_tables();
      return in_array($table_name, $tables) ? 1 : 0;
  }

  /**
  * 获取表列表
  */
  public function list_tables(){
      return $this->mongo->selectDB($this->curr_db_name)->getCollectionNames();
  }


  public function __destruct(){
    $this->close();
  }

}

 ?>
