<?php
/**
* 框架入口文件 base.php
*@author    yangfeilong
*@lastmodify     2015-12-22
*/

//定义入口文件访问标识
define('IN_MYPC', true);

//定义路径分割符
define('DS', DIRECTORY_SEPARATOR);

//定义框架路径
define('MP_PATH', dirname(__FILE__) . DS);

if (!defined('MYPC_PATH')) define('MYPC_PATH', MP_PATH . '..' . DS);

//缓存文件夹地址
define('CACHE_PATH', MYPC_PATH . 'caches' . DS);

//主机协议
define('SITE_PROTOCOL', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']=='443' ? 'https://' : 'http://');

//站点主机名
define('SITE_URL', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');

//访问来源
//只有点击超链接（即<A href=...>）打开的页面才有HTTP_REFERER环境变量
define('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

//定义系统时间
define('SYS_TIME', time());

//加载公共函数库
mp_base::load_sys_func('global');

//错误报告处理
C('system', 'errorlog') ? set_error_handler('my_error_handler') : error_reporting(E_ERROR | E_WARNING | E_PARSE);

//设置本地时差
function_exists('date_default_timezone_set') && date_default_timezone_set(C('system', 'timezone'));

//设置网站字符集
define('CHARSET', C('system', 'charset'));
header('Content-Type:text/html;charset='.CHARSET);

class mp_base{
	/**
	* 初始化应用程序并运行网站
	*/
	public static function create_app(){
		return self::load_sys_class('application');
	}


	/**
	* 加载系统类函数
	* @param string $classname 类名
	* @param string $path 地址
	* @param intger $initialize 是否实例化类, 默认实例化
	*/
	public static function load_sys_class($classname, $path = '', $initialize = 1){
		return self::_load_class($classname, $path, $initialize);
	}


	/**
	* 加载应用类函数
	* @param string $classname 类名
	* @param string $m 模块
	* @param intger $initialize 是否实例化，默认实例化
	*/
	public static function load_app_class($classname, $m = '', $initialize = 1){
		$m = empty($m) && defined('ROUTE_M') ? ROUTE_M : $m;
		if(empty($m)) return false;
		return self::_load_class($classname, 'modules' . DS . $m . DS . 'classes', $initialize);
	}


	/**
	* 加载数据模型
	* @param string $classname 类名
	*/
	public static function load_model($classname){
		return self::_load_class($classname, 'model');
	}


	/**
	* 加载类函数
	* @param string $classname 类名
	* @param string $path 地址
	* @param intger $initialize 是否实例化类, 默认实例化
	*/
	private static function _load_class($classname, $path = '', $initialize = 1){
		static $classes = array();

		if (empty($path)) {  //默认加载框架根目录下libs/classes中的类文件
			$path = 'libs' . DS . 'classes';
		}

		$key = md5($path . $classname);
		if (isset($classes[$key])) {
			if (!empty($classes[$key])) {
				return $classes[$key];
			}else{
				return true;
			}
		}

		if (file_exists(MP_PATH . $path . DS . $classname . '.class.php')) {
			include MP_PATH . $path . DS . $classname . '.class.php';
			if ($initialize) {
				$classes[$key] = new $classname;
			}else{
				$classes[$key] = true;
			}

			return $classes[$key];
		}else{
			return false;
		}
	}


	/**
	* 加载系统的函数库
	* @param string $func 函数库名
	*/
	public static function load_sys_func($func){
		return self::_load_func($func);
	}


	/**
	* 加载函数库
	* @param string $func 函数库名
	* @param string $path 地址
	*/
	private static function _load_func($func, $path = ''){
		static $funcs = array();

		if (empty($path)) {
			$path = 'libs' . DS . 'functions';
		}
		$path .= DS . $func . '.func.php';
		$key = md5($path);
		if (isset($funcs[$key])) {
			return true;
		}

		if (file_exists(MP_PATH . $path)) {
			include MP_PATH . $path;
			$funcs[$key] = true;
			return true;
		}else{
			$funcs[$key] = false;
			return false;
		}
	}


	/**
	* 加载配置文件
	* @param string $file 配置文件
	* @param string $key 要获取的配置值，默认获取所有的值
	*/
	public static function load_config($file, $key = ''){
		static $configs = array();

		if (isset($configs[$file])) {
			if (empty($key)) {
				return $configs[$file];
			}else{
				if (isset($configs[$file][$key])) {
					return $configs[$file][$key];
				}else{
					return '';
				}
			}
		}

		$path = CACHE_PATH . 'configs' . DS . $file . '.php';
		if (file_exists($path)) {
			$configs[$file] = include $path;
		}else{
			return '';
		}

		if (empty($key)) {
			return $configs[$file];
		}else{
			if (isset($configs[$file][$key])) {
				return $configs[$file][$key];
			}else{
				return '';
			}
		}
	}
}
