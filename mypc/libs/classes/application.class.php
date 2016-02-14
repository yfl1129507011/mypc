<?php
/**
* application.class.php 应用程序创建类
* @author    yangfeilong
* @lastmodify    2015-12-23
*/

class application{
	/**
	* 构造函数
	*/
	public function __construct(){
		$param = mp_base::load_sys_class('param');
		define('ROUTE_M', $param->route_m());
		define('ROUTE_C', $param->route_c());
		define('ROUTE_A', $param->route_a());

		$this->start();
	}


	/**
	* 事件调用
	*/
	private function start(){
		$controller = $this->load_controller();
		if (method_exists($controller, ROUTE_A)) {
			if (preg_match('/^[_]/i', ROUTE_A)) {
				exit('You are visiting the action is to protect the private action');
			}else{
				call_user_func(array($controller, ROUTE_A));
			}
		}else{
			exit('Action does not exist.');
		}
	}


	/**
	* 加载控制器
	*/
	private function load_controller(){
		$filename = ROUTE_C;
		$m = ROUTE_M;

		$path = MP_PATH . 'modules' . DS . $m . DS . $filename . '.php';
		if (file_exists($path)) {
			include $path;
			if (class_exists($filename)) {
				return new $filename;
			}else{
				exit('Controller does not exist.');
			}
		}else{
			exit('Controller does not exist.');
		}
	}
}
