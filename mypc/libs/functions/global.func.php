<?php
/**
* global.func.php 公共函数库
* @author    yangfeilong
* @lastmodify    2015-12-23
*/


/**
* 设置，获取指定配置文件的配置参数
* @param string $file 配置文件
* @param string $key  要获取的配置参数。默认是获取所有的参数信息
*/
function C($file, $key=''){
	return mp_base::load_config($file, $key);
}


/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string){
	if (!is_array($string)) {
		return addslashes($string);
	}

	foreach ($string as $key => $value) {
		$string[$key] = new_addslashes($value);
	}

	return $string;
}


/**
* 返回一个去除转义反斜线后的字符串
* @param $string 需要处理的字符串或数组
* @return mixed
*/
function new_stripslashes($string){
	if (!is_array($string)) {
		return stripslashes($string);
	}
	foreach($string as $key=>$val) $string[$key] = new_stripslashes($val);

	return $string;
}


/**
* 将特殊的字符转为HTML实体
* @param $string 需要处理的字符串或数组
* @return mixed
*/
function new_html_special_chars($string){
	$encoding = 'utf-8';
	if(strtolower(CHARSET)=='gbk') $encoding = 'ISO-8859-15';
	if(!is_array($string)) return htmlspecialchars($string, ENT_QUOTES, $encoding);
	foreach($string as $key=>$val) $string[$key] = new_html_special_chars($val);

	return $string;
}


/**
* 将HTML实体转为对应特殊的字符
* @param $string 需要处理的字符串或数组
* @return mixed
*/
function new_html_entity_decode($string){
	$encoding = 'utf-8';
	if(strtolower(CHARSET) == 'gbk') $encoding = 'ISO-8859-15';
	return new_html_entity_decode($string, ENT_QUOTES, $encoding);
}


/**
* 字符串加密、解密函数
*
*
* @param	string	$txt		字符串
* @param	string	$operation	ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
* @param	string	$key		密钥：数字、字母、下划线
* @param	string	$expiry		过期时间
* @return	string
*/
// function sys_auth($string, $operation='ENCODE', $key='', $expiry=0){
// 	$ckey_length = 4;
// 	$key = md5($key != '' ? $key : pc_base::load_config('system', 'auth_key'));
// 	$keya = md5(substr($key, 0, 16));
// 	$keyb = md5(substr($key, 16, 16));
// 	$keyc = $ckey_length ? ($operation=='DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
//
// 	$cryptkey = $keya . md5($keya.$keyc);
// 	$key_length = strlen($cryptkey);
//
// 	$string = $operation=='DECODE' ? base64_decode(strstr(substr($string, $ckey_length)))
// }
function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
	$key_length = 4;
	$key = md5($key != '' ? $key : pc_base::load_config('system', 'auth_key'));
	$fixedkey = md5($key);
	$egiskeys = md5(substr($fixedkey, 16, 16));
	$runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
	$keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
	$string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$egiskeys), 0, 16) . $string : base64_decode(substr($string, $key_length));

	$i = 0; $result = '';
	$string_length = strlen($string);
	for ($i = 0; $i < $string_length; $i++){
		$result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
	}
	if($operation == 'ENCODE') {
		return $runtokey . str_replace('=', '', base64_encode($result));
	} else {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$egiskeys), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	}
}


/**
* 模板调用
*
* @param $module 模板所属模块名称
* @param $template 调用模板名称
*/
function template($module='content', $tpl='index'){
	$module = str_replace('/', DIRECTORY_SEPARATOR, $module);
	//获取编译后的缓存模板文件
	$compiled_tpl_file = MYPC_PATH . 'caches' . DIRECTORY_SEPARATOR . 'caches_tpl' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $tpl . '.php';
	//获取模板文件
	$tpl_file = MP_PATH . 'tpls' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $tpl . '.html';
	if (file_exists($tpl_file)) {  //是否模板文件存在
		if(!file_exists($compiled_tpl_file) || (@filemtime($tpl_file) > @filemtime($compiled_tpl_file))){
			//编译缓存文件不存在或者模板文件已修改，则重新进行模板编译
			$tpl_cache = mp_base::load_sys_class('tpl_cache');
			$tpl_cache->tpl_compile($module, $tpl);
		}
	}else {
		die('模板不存在！');
	}

	return $compiled_tpl_file;
}


/**
* 输出自定义错误，并将错误信息写入CACHE_PATH.'error_log.php'的错误日志文件中
*
* @param $errno 错误号
* @param $errstr 错误描述
* @param $errfile 报错文件地址
* @param $errline 错误行号
* @return string 错误提示
*/
function my_error_handler($errno, $errstr, $errfile, $errline, $string){
	//if ($errno == 8) {return;} //E_NOTICE 错误级别
	$errfile = str_replace(MYPC_PATH, '', $errfile);
	if (C('system', 'errorlog')) {
		$msg = '<?php exit;?>'.date('Y-m-d H:i:s', SYS_TIME).' | '.$errno.' | '.str_pad($errstr, 30).' | '.$errfile.' | '.$errline."\r\n";
		error_log($msg, 3/*信息叠加*/, CACHE_PATH.'error_log.php');
	}else{
		$str = '<div style="font-size:12px;text-align:left; border-bottom:1px solid #9cc9e0; border-right:1px solid #9cc9e0;padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;">';
		$str .= '<span>errorno:' . $errno . ',str:' . $errstr . ',file:';
		$str .= '<font color="blue">' . $errfile . '</font>,line' . $errline .'<br />';
		$str .= '</span></div>';
		echo $str;
	}
}


/**
* 写入缓存，默认为文件缓存，不加载缓存配置
* @param $name 缓存名称 必要
* @param $data 缓存数据 必要
* @param $filepath 数据路径（模块名称） caches/cache_$filepath
* @param $type 缓存类型[file, memcache, redis]
* @param $config 配置名称
* @param $timeout 过期时间
*/
function setcache($name, $data, $filepath='', $type='file', $config='', $timeout=0){
	if (!preg_match("/^[a-zA-Z0-9_-]+$/", $name)) { //过滤非法缓存名称
		return false;
	}
	if($filepath != "" && !preg_match("/^[a-zA-Z0-9_-]+$/", $filepath)){  //数据路径不为空，进行过滤
		return false;
	}
	//加载缓存工厂类文件
	mp_base::load_sys_class('cache_factory', '', 0);
	if ($config) {
		$cacheconfig = mp_base::load_config('cache');
		$cache = cache_factory::get_instance($cacheconfig)->get_cache($config);
	}else{
		$cache = cache_factory::get_instance()->get_cache($type);
	}

	return $cache->set($name, $data, $timeout, '', $filepath);
}


/**
* 读取缓存，默认为文件缓存，不加载缓存配置
* @param string $name 缓存名称
* @param $filepath 数据路径（模块名称） caches/cache_$filepath/
* @param $type 缓存类型[file, memcache, redis]
* @param string $config 配置名称
*/
function getcache($name, $filepath='', $type='file', $config=''){
	if (!preg_match("/^[a-zA-Z0-9_-]+$/", $name)) {
		return false;
	}
	if ($filepath != '' && !preg_match("/^[a-zA-Z0-9_-]+$/", $filepath)) {
		return false;
	}
	mp_base::load_sys_class('cache_factory', '', 0);
	if ($config) {
		$cacheconfig = mp_base::load_config('cache');
		$cache = cache_factory::get_instance($cacheconfig)->get_cache($config);
	}else {
		$cache = cache_factory::get_instance()->get_cache($type);
	}

	return $cache->get($name, '', '', $filepath);
}


/**
 * 删除缓存，默认为文件缓存，不加载缓存配置。
 * @param $name 缓存名称
 * @param $filepath 数据路径（模块名称） caches/cache_$filepath/
 * @param $type 缓存类型[file,memcache,apc]
 * @param $config 配置名称
 */
function delcache($name, $filepath='', $type='file', $config='') {
	if(!preg_match("/^[a-zA-Z0-9_-]+$/", $name)) return false;
	if($filepath!="" && !preg_match("/^[a-zA-Z0-9_-]+$/", $filepath)) return false;
	pc_base::load_sys_class('cache_factory','',0);
	if($config) {
		$cacheconfig = pc_base::load_config('cache');
		$cache = cache_factory::get_instance($cacheconfig)->get_cache($config);
	} else {
		$cache = cache_factory::get_instance()->get_cache($type);
	}
	return $cache->delete($name, '', '', $filepath);
}



/**
* 获取请求IP
*/
function ip(){
	if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$ip = getenv('HTTP_CLIENT_IP');
	}elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp('HTTP_X_FORWARDED_FOR'), 'unknown') {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	}elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$ip = getenv('REMOTE_ADDR');
	}elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
}




/**
* 获取当前页面完整URL地址
*/
function get_url(){
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '433' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? safe_replace($_SERVER['PHP_SELF']) : safe_replace($_SERVER['SCRIPT_NAME']);
	$path_info = isset($_SERVER['PATH_INFO']) ? safe_replace($_SERVER['PATH_INFO']) : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? safe_replace($_SERVER['REQUEST_URI']) : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . safe_replace($_SERVER['QUERY_STRING']) : $path_info);
	return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
}
