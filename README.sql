CREATE DATABASE mypc DEFAULT CHARSET utf8;

CREATE TABLE `mp_admin`(
	`userid` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
	`username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
	`password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
	`lastloginip` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后登录IP',
	`lastlogintime` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后登录时间',
	`email` varchar(40) DEFAULT NULL COMMENT '邮箱',
	`realname` varchar(50) NOT NULL DEFAULT '' COMMENT '真实姓名',
	PRIMARY KEY (`userid`),
	KEY `username` (`username`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `mp_admin` (`username`, `password`, `email`) VALUES ('admin', md5('123456'), '111111@aaa.com');