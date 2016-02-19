<?php
/**
*
*/
defined('IN_MYPC') or exit('No permission access');

class index{

	public function __construct(){
	}


	public function init(){
		//echo "yfl";
		var_dump(setcache('cache','d'));
	}
}
