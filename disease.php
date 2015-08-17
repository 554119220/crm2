<?php
/**
 * 管理常见疾病
 **/
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

/**
 * 常见疾病列表
 **/
if ($_REQUEST['act'] == 'disease_list')
{
      //检查用户权限
      admin_priv('users_manage');
      $sql = 'SELECT * FROM '.$ecs->table('disease');
      $disease_list = $db->getAll($sql);
      $smarty->assign('user_ranks',   $ranks);
      $smarty->assign('ur_here',      $_LANG['15_disease_man']);

      $smarty->assign('disease_list', $disease_list);
      $smarty->assign('full_page',    1);
      $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');

      assign_query_info();
      $smarty->display('disease_list.htm');
}

//添加新的疾病
elseif ($_REQUEST['act'] == 'add')
{
      $sql='INSERT INTO '.$ecs->table('disease').'(disease)VALUES("'.$_GET['disease'].'")';
      if($db->query($sql))
      {
            $sql = 'SELECT * FROM '.$ecs->table('disease')." WHERE disease='$_GET[disease]' OR disease_id=".mysql_insert_id();
            $disease = $db->getRow($sql);
            require_once('../includes/cls_json.php');
            $json = new JSON;
            die($json->encode($disease));
      }
}

elseif ($_REQUEST['act'] == 'del')
{
      $sql = 'DELETE FROM '.$ecs->table('disease').' WHERE disease_id='.$_GET['id'];
      if ($db->query($sql))
      {
            echo $_GET['id'];
      }
}

?>
