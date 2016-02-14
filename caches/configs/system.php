<?php
return array(
	'charset' => 'utf-8',  //网站字符集
	'timezone' => 'Etc/GMT-8', //网站时区（只对php 5.1以上版本有效），Etc/GMT-8 实际表示的是 GMT+8
	'errorlog' => 1, //1、保存错误日志到 cache/error_log.php | 0、在页面直接显示
	'auth_key' => 'yangfeilong925',  //密钥
	//Cookie配置
	'cookie_domain' => '', //Cookie 作用域
	'cookie_path' => '', //Cookie 作用路径
	'cookie_pre' => 'eqELX_', //Cookie 前缀，同一域名下安装多套系统时，请修改Cookie前缀
	'cookie_ttl' => 0, //Cookie 生命周期，0 表示随浏览器进程
);
