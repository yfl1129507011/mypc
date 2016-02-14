<?php
/**
* param.class.php 参数处理类
* @author    yangfeilong
* @lastmodify    2015-12-23
*/

class param{

	//路由配置
	private $route_config = '';

	public function __construct(){
		if (!get_magic_quotes_gpc()) {
			$_POST = new_addslashes($_POST);
			$_GET = new_addslashes($_GET);
			$_REQUEST = new_addslashes($_REQUEST);
			$_COOKIE = new_addslashes($_COOKIE);
		}

		$this->route_config = mp_base::load_config('route', SITE_URL) ? mp_base::load_config('route', SITE_URL) : mp_base::load_config('route', 'default');

		if (isset($_GET['page'])) {  //页数为1-1000000
			$_GET['page'] = max(intval($_GET['page']), 1);
			$_GET['page'] = min(intval($_GET['page']), 1000000);
		}

		return true;
	}


	/**
	* 获取模型
	*/
	public function route_m(){
		$m = isset($_GET['m']) && !empty($_GET['m']) ? $_GET['m'] : (isset($_POST['m']) && !empty($_POST['m']) ? $_POST['m'] : '');
		$m = $this->safe_deal($m);
		if (empty($m)) {
			return $this->route_config['m'];
		}else{
			if(is_string($m)) return $m;
		}
	}


	/**
	 * 获取控制器
	 */
	public function route_c() {
		$c = isset($_GET['c']) && !empty($_GET['c']) ? $_GET['c'] : (isset($_POST['c']) && !empty($_POST['c']) ? $_POST['c'] : '');
		$c = $this->safe_deal($c);
		if (empty($c)) {
			return $this->route_config['c'];
		} else {
			if(is_string($c)) return $c;
		}
	}


	/**
	 * 获取事件
	 */
	public function route_a() {
		$a = isset($_GET['a']) && !empty($_GET['a']) ? $_GET['a'] : (isset($_POST['a']) && !empty($_POST['a']) ? $_POST['a'] : '');
		$a = $this->safe_deal($a);
		if (empty($a)) {
			return $this->route_config['a'];
		} else {
			if(is_string($a)) return $a;
		}
	}


	/**
	* 设置cookie
	* @param string $var   变量
	* @param string $value 变量值
	* @param int $time     过期时间
	*/
	public static function set_cookie($var, $value='', $time=0){return 'yfl';
		$time = $time>0 ? $time : ($value==='' ? SYS_TIME-3600 : 0);  //处理过期时间
		$s = $_SERVER['SERVER_PORT']=='443' ? 1 : 0;
		$var = C('system', 'cookie_pre').$var;
		$_COOKIE[$var] = $value;
		if (is_array($value)) {
			foreach ($value as $k => $v) {
				setcookie($var.'['.$k.']', sys_auth($v, 'ENCODE'), $time, C('system','cookie_path'), C('system','cookie_domain'), $s);
			}
		}else{
			setcookie($var, sys_auth($value, 'ENCODE'), $time, C('system','cookie_path'), C('system','cookie_domain'), $s);
		}
	}


	/**
	 * 安全处理函数
	 * 处理m,a,c
	 */
	private function safe_deal($str) {
		return str_replace(array('/', '.'), '', $str);
	}
}
