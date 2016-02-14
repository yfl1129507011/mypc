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
