<?php
/**
* 模板解析缓存类文件
*/
final class tpl_cache{
  /**
  * 编译模板
  * @param $module 模板名称
  * @param $tpl 模板文件名
  */
  public function tpl_compile($module, $tpl){
    $module = str_replace('/', DIRECTORY_SEPARATOR, $module);
    $tpl_file = MP_PATH . 'tpls' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $tpl . '.html';
    if (!file_exists($tpl_file)) {
      die($tpl_file . ".html is not exists!");
    }
    $content = @file_get_contents($tpl_file);
    $filepath = CACHE_PATH . 'caches_template' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
    if (!is_dir($filepath)) {
      mkdir($filepath, 0777, true);
    }
    $compiled_tpl_file = $filepath . $tpl . '.php';
    $content = $this->tpl_parse($content);  //解析
    $strlen = file_put_contents($compiled_tpl_file, $content);
    chmod($compiled_tpl_file, 0777);

    return $strlen;
  }


  /**
  * 解析模板
  * @param $str 模板内容
  */
  public function tpl_parse($str){
    $str = preg_replace("/\{template\s+(.+)\}/", "<?php include template(\\1); ?>", $str);
    $str = preg_replace("/\{include\s+(.+)\}/", "<?php include \\1; ?>", $str);
    $str = preg_replace("/\{php\s+(.+)\}/", "<?php \\1; ?>", $str);
    //if 条件
    $str = preg_replace("/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $str);
    $str = preg_replace("/\{else\}/", "<?php } else { ?>", $str);
    $str = preg_replace("/\{elseif\s+(.+?)\}/", "<?php } elseif(\\1) {?>", $str);
    $str = preg_replace("/\{\/if\}/", "<?php } ?>", $str);
    //for 循环
    $str = preg_replace("/\{for\s+(.+?)\}/", "<?php for(\\1) { ?>", $str);
    $str = preg_replace("/\{\/for\}/", "<?php } ?>", $str);
    //++ --
    $str = preg_replace("/\{\+\+(.+?)\}/", "<?php ++\\1; ?>", $str);
    $str = preg_replace("/\{\-\-(.+?)\}/", "<?php --\\1; ?>", $str);
    $str = preg_replace("/\{(.+?)\+\+\}/", "<?php \\1++; ?>", $str);
    $str = preg_replace("/\{(.+?)\-\-\}/", "<?php \\1--; ?>", $str);
    //foreach
    $str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\}/", "<?php \$n=1;if(is_array(\\1)) foreach(\\1 AS \\2) { ?>", $str );
		$str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "<?php \$n=1; if(is_array(\\1)) foreach(\\1 AS \\2 => \\3) { ?>", $str );
		$str = preg_replace ( "/\{\/loop\}/", "<?php \$n++;}unset(\$n); ?>", $str );
    //变量
    $str = preg_replace ( "/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str );
    $str = "<?php defined('IN_MYPC') or exit('No permission resources')" . $str;

    return $str;
  }

}
 ?>
