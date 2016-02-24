<?php
/**
* cache_memcache.class.php    memcache内存缓存操作类
*/

class cache_memcache{
  //memcache缓存实例
  private $memcache = null;

  /**
  * 构造函数
  */
  public function __construct(){
    $this->memcache = new Memcache;
    $this->memcache->connect(MEMCACHE_HOST, MEMCACHE_PORT, MEMCACHE_TIMEOUT);
  }

  /**
  * 获取memcache缓存数据
  * @param $name 键名
  */
  public function get($name){
    $value = $this->memcache->get($name);
    return $value;
  }

  /**
  * 缓存memcache数据库
  * @param $name 键名
  * @param $value 键值
  * @param $ttl   有效期
  */
  public function set($name, $value, $ttl=0, $ext1='', $ext2=''){
    return $this->memcache->set($name, $value, false, $ttl);
  }

  /**
  * 删除缓存
  */
  public function delete($name){
    return $this->memcache->delete($name);
  }


  /**
  * 刷新缓存
  */
  public function flush(){
    return $this->memcache->flush();
  }
}
 ?>
