<?php
error_reporting(7);
date_default_timezone_set('Asia/Shanghai');
header("Content-type:text/html; Charset=utf-8");
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require_once("./includes/image.class.php");

$images = new Images("file");
if (isset($_GET['act']) && $_GET['act'] == 'add')
{
     $user_id = intval($_GET['user_id']);
     $smarty->assign('user_id', $user_id);
     $smarty->display('upload.htm');
}
elseif(isset($_GET['act']) && $_GET['act'] == "upload")
{
     $path = $images->move_uploaded();
     $images->thumb($path,false,0);	//文件比规定的尺寸大则生成缩略图，小则保持原样
     if($path == false)
     {
          $images->get_errMsg();
     }
     else
     {
          $smarty->assign('img_path', $path);
          $smarty->assign('user_id', intval($_REQUEST['user_id']));
          $_SESSION['papp'] = $path;
          die($smarty->fetch('cut_img.htm'));
     }
}

elseif ($_GET['act'] == 'cut')
{
     $image = $_SESSION['papp'];
     if (!file_exists($image)) return;

     $res = $images->thumb($image,true,1);
     if($res == false)
     {
          echo "裁剪失败";
     }
     elseif(is_array($res))
     {
          $explain = mysql_real_escape_string($_REQUEST['photo_explain']);
          $user_id = intval($_REQUEST['user_id']);
          $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('user_photos').
               '(photo_path, photo_thumb, photo_explain, user_id)VALUES('.
               "'{$res['big']}', '{$res['small']}', '{$explain}', $user_id)";
          if ($GLOBALS['db']->query($sql))
               sys_msg('照片已保存，', 0, array (array ('text'=>'将返回顾客列表！', 'href'=>'users.php?act=list')));

          echo '<p><img src="'.$res['small'].'" style="margin:10px;"></p>';
          echo '<p><img src="'.$res['big'].'" style="margin:10px;"></p>';
     }
     elseif(is_string($res))
     {
          echo '<img src="'.$res.'">';
     }
}

?>
