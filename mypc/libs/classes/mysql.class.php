<?php
/**
* mysql.class.php  MySQL数据库操作类
*/
//最终类
final class mysql{
  //数据库配置信息
  private $config = null;

  //数据库连接资源句柄
  public $link = null;

  //最近一次查询资源句柄
  public $lastqueryid = null;

  //统计数据库查询次数
  public $querycount = 0;

  public function __construct(){

  }


  /**
  * 打开数据库连接。 有可能不真实连接数据库（根据autoconnect配置）
  * @param $config 数据库连接参数信息
  */
  public function open($config){
    $this->config = $config;
    if ($config['autoconnect'] == 1) {
      $this->connect();
    }
  }


  //连接数据库
  public function connect(){
    $func = $this->config['pconnect']==1 ? 'mysql_pconnect' : 'mysql_connect';
    if (!$this->link = @$func($this->config['hostname'], $this->config['username'], $this->config['password'], 1)) {
      $this->halt('Can not connect to MySQL server');
      return false;
    }

    if ($this->version() > '4.1') {
      $charset = isset($this->config['charset']) ? $this->config['charset'] : '';
      $serverset = $charset ? "character_set_connection='$charset', character_set_results='$charset', character_set_client=binary" : '';
      $serverset .= $this->version() > '5.0.1' ((empty($serverset) ? '' : ',')." sql_mode='' ") : '';
      $serverset && mysql_query("SET $serverset", $this->link);
    }

    if ($this->config['database'] && !@mysql_select_db($this->config['database'], $this->link)) {
      $this->halt('Cannot use database '.$this->config['database']);
      return false;
    }
    $this->database = $this->config['database'];
    return $this->link;
  }


  //执行sql语句
  private function query($sql){
    if (!is_resource($this->link)) {
      $this->connect();
    }

    $this->lastqueryid = mysql_query($sql, $this->link) or $this->half(mysql_error(), $sql);

    $this->querycount++;
    return $this->lastqueryid;
  }

  /**
	 * 执行sql查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件[例`name`='$name']
	 * @param $limit 		返回结果范围[例：10或10,10 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key 			返回数组按键名排序
	 * @return array		查询结果集数组
	 */
   public function select($data, $table, $where='', $order='', $limit='', $group='', $key=''){
     $where = $where == '' ? '' : ' WHERE ' . $where;
     $limit = $limit == '' ? '' : ' LIMIT ' . $limit;
     $order = $order == '' ? '' : ' ORDER BY ' . $order;
     $group = $group == '' ? '' : ' GROUP BY ' . $group;
     $field = explode(',', $data);
     array_walk($field, array($this, 'add_special_char'));  //格式化字段
     $data = implode(',', $field);

     $sql = 'SELECT ' . $data . ' FROM `' . $this->config['database'] . '`.`' . $table . '`' . $where . $group . $order . $limit;
     $this->query($sql);
     if (!is_resource($this->lastqueryid)) {
       return $this->lastqueryid;
     }

     $datalist = array();
     while (($rs = $this->fetch_next()) != false) {
       if ($key) {
         $datalist[$rs[$key]] = $rs;
       }else{
         $datalist[] = $rs;
       }
     }

     $this->free_result();
     return $datalist;
   }


   /**
   * 获取单条记录查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @return array/null	数据查询结果集,如果不存在，则返回空
   */
   public function get_one($data, $table, $where='', $order='', $group=''){
     $where = $where == '' ? '' : ' WHERE ' . $where;
     $order = $order == '' ? '' : ' ORDER BY ' . $order;
     $group = $group == '' ? '' : ' GROUP BY ' . $group;
     $limit = ' LIMIT 1';
     $field = explode(',', $data);
     array_walk($field, array($this, 'add_special_char'));
     $data = implode(',', $field);

     $sql = 'SELECT ' . $data . ' FROM `' . $this->config['database'] . '`.`' . $table . '`' . $where . $group . $order . $limit;
     $this->query($sql);
     $res = $this->fetch_next();
     $this->free_result();
     return $res;
   }


   /**
   * 执行添加记录操作
   * @param $data	  要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
   * @param $table  数据表
   * @return boolean
   */
   public function insert($data, $table, $return_insert_id=false, $replace=false){
     if (!is_array($data) || $table == '' || count($data) == 0) {
       return false;
     }

     $fielddata = array_keys($data);
     $valuedata = array_values($data);
     array_walk($fielddata, array($this, 'add_special_char'));
     array_walk($valuedata, array($this, 'escape_string'));

     $field = implode(',', $fielddata);
     $value = implode(',', $valuedata);

     $cmd = $replace ? 'REPLACE INFO' : 'INSERT INFO';
     $sql = $cmd . ' `' . $this->config['database'] . '`.`' . $table . '`('.$field.') VALUES ('.$value.')';
     $return = $this->query($sql);
     return $return_insert_id ? $this->insert_id() : $return;
   }


   //获取最后一次添加记录的主键号
   public function insert_id(){
     return mysql_insert_id($this->link);
   }


	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='phpcms',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'phpcms','password'=>'123456')]
	 *						数组可使用array('name'=>'+=1', 'base'=>'-=1');程序会自动解析为`name` = `name` + 1, `base` = `base` - 1
	 * @param $table 		数据表
	 * @param $where 		更新数据时的条件
	 * @return boolean
	 */
   public function update($data, $table, $where=''){
     if ($table == '' or $where == '') {
       return false;
     }

     $where = ' WHERE ' . $where;
     $field = '';
     if (is_string($data) && $data != '') {
       $fields = $data;
     }elseif (is_array($data) && count($data) > 0) {
       $fields = array();
       foreach ($data as $k => $v) {
         switch (substr($v, 0, 2)) {
           case '+=':
             $v = substr($v, 2);
             if (is_numeric($v)) {
               $fields[] = $this->add_special_char($k) . '=' . $this->add_special_char($k) . '+' . $this->escape_string($v, false);
             }else {
               continue;
             }
             break;
           case '-=':
             $v = substr($v, 2);
             if (is_numeric($v)) {
               $fields[] = $this->add_special_char($k) . '=' . $this->add_special_char($k) . '-' . $this->escape_string($v, false);
             }else {
               continue;
             }
             break;
           default:
             $fields[] = $this->add_special_char($k) . '=' . $this->escape_string($v);
             break;
         }
       }
       $field = implode(',', $fields);
     }else {
       return false;
     }

     $sql = 'UPDATE `' . $this->config['database'] . '`.`' . $table . '` SET ' . $field . $where;
     return $this->query($sql);
   }


   /**
   * 执行删除记录操作
   * @param $table   数据表
   * @param $where   删除数据条件，不允许为空。如果要清空表，使用empty方法
   */
   public function delete($table, $where){
     if ($table=='' || $where=='') {
       return false;
     }

     $where = ' WHERE ' . $where;
     $sql = 'DELETE FROM `' . $this->config['database'] . '`.`' . $table . '`' . $where;
     return $this->query($sql);
   }


   //遍历查询结果集
   public function fetch_next($type=MYSQL_ASSOC){
     $res = mysql_fetch_array($this->lastqueryid, $type);
     if (!$res) {
       $this->free_result();
     }

     return $res;
   }


  //释放查询资源
  public function free_result(){
    if (is_resource($this->lastqueryid)) {
      mysql_free_result($this->lastqueryid);
      $this->lastqueryid = null;
    }
  }


  //获取错误信息
  private function error(){
    return @mysql_error($this->link);
  }


  //获取错误号
  private function errno(){
    return intval(@mysql_errno($this->link));
  }


  //获取mysql版本
  public function version(){
    if (!is_resource($this->link)) {
      $this->connect();
    }

    return mysql_get_server_info($this->link);
  }


  //关闭资源
  private function close(){
    if (is_resource($this->link)) {
      @mysql_close($this->link);
    }
  }


  //mysql错误信息的显示
  private function half($message='', $sql=''){
    if ($this->config['debug']) {
      $msg = "<b>MySQL Query : </b> $sql <br /><b> MySQL Error : </b>".$this->error()." <br /> <b>MySQL Errno : </b>".$this->errno()." <br /><b> Message : </b> $message <br />
      echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
      exit;
    }else {
      return false;
    }
  }


  //对字段两边加反引号，以保证数据库安全
  private function add_special_char(&$key){
    if ('*' == $key || false !== strpos($key, ')') || false !== strpos($key, '.') || false !== strpos($key, '`')) {
      //不处理
    }else{
      $key = '`'.trim($key).'`';
    }

    if (preg_match("/\b(select|insert|update|delete)\b/i", $key)) {
      $key = preg_replace("/\b(select|insert|update|delete)\b/i", '', $key);
    }

    return $key;
  }


  //对字段值加入引号，以保证数据库安全
  private function escape_string(&$value, $quotation=true){
    if ($quotation) {
			$q = '\'';
		} else {
			$q = '';
		}
    return $value = $q . $value . $q;
  }

}
 ?>
