<?php
/**
* cache_file.class.php 文件缓存类文件
*/

class cache_file{
  //缓存默认配置
  protected $_setting = array(
    'suf' => '.cache.php', //缓存文件后缀
    'type' => 'array',  //数据缓存格式：array-数组，serialize-序列化
  );

  //缓存路径
  protected $filepath = '';

  /**
  * 构造函数
  * @param array $setting 缓存配置
  * @return void
  */
  public function __construct($setting = ''){
    $this->get_setting($setting);
  }


  /**
  * 写入缓存
  * @param string $name 缓存名称
  * @param mixed $data 缓存数据
  * @param array $setting 缓存配置
  * @param string $type 缓存类型
  * @param string $module 所属模型
  */
  public function set($name, $data, $setting='', $type='data', $module=ROUTE_M){
    $this->get_setting($setting);
    if(empty($type)) $type = 'data';
    if(empty($module)) $module = ROUTE_M;
    $filepath = CACHE_PATH . 'caches_' . $module . '/caches_' . $type . '/';  //缓存文件路径
    $filename = $name . $this->_setting['suf'];   //缓存文件名
    if (!is_dir($filepath)) {
      mkdir($filepath, 0777, true);
    }

    if ($this->_setting['type'] == 'array') {
      $data = "<?php\nreturn " . var_export($data, true) . ";\n?>";
    }elseif ($this->_setting['type'] == 'serialize') {
      $data = serialize($data);
    }

    if ($module == 'commons') {
      # code...
    }

    //是否开启互斥锁
    if(mp_base::load_config('system', 'lock_ex')){
      $file_size = file_put_contents($filepath . $filename, $data, LOCK_EX);
    }else {
      $file_size = file_put_contents($filepath . $filename, $data);
    }

    return $file_size ? $file_size : 'false';
  }


  /**
  * 获取缓存数据
  * @param string $name 缓存名称
  * @param array $setting 缓存配置
  * @param string $type 缓存类型
  * @param string $module 所属模型
  */
  public function get($name, $setting='', $type='data', $module=ROUTE_M){
    $this->get_setting($setting);
    if(empty($type)) $type = 'data';
    if(empty($module)) $module = ROUTE_M;
    $filepath = CACHE_PATH . 'caches_' . $module . '/caches_' . $type . '/';  //缓存文件路径
    $filename = $name . $this->_setting['suf'];   //缓存文件名
    if (!file_exists($filepath . $filename)) {
      return false;
    }else {
      if ($this->_setting['type'] == 'array') {
        $data = @require($filepath . $filename);
      }elseif ($this->_setting['type'] == 'serialize') {
        $data = unserialize(file_get_contents($filepath . $filename));
      }

      return $data;
    }
  }


  /**
  * 删除缓存
  * @param string $name 缓存名称
  * @param array $setting 缓存配置
  * @param string $type 缓存类型
  * @param string $module 所属模型
  * @return bool
  */
  public function delete($name, $setting='', $type='data', $module=ROUTE_M){
    $this->get_setting($setting);
    if(empty($type)) $type = 'data';
    if(empty($module)) $module = ROUTE_M;
    $filepath = CACHE_PATH . 'caches_' . $module . '/caches_' . $type . '/';  //缓存文件路径
    $filename = $name . $this->_setting['suf'];   //缓存文件名
    if (file_exists($filepath . $filename)) {
      return @unlink($filepath . $filename) ? true : false;
    }else {
      return false;
    }
  }


  /**
	 * 和系统缓存配置对比获取自定义缓存配置
	 * @param	array	$setting	自定义缓存配置
	 * @return  array	$setting	缓存配置
	 */
   public function get_setting($setting = ''){
     if ($setting) {
       $this->_setting = array_merge($this->_setting, $setting);
     }
   }
}
 ?>
