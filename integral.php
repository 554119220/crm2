<?php

define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require 'includes/cls_integral.php';
date_default_timezone_set('Aisa/Shanghai');
$nowtime = time() +28800;

$allow_action = array('list','add','insert','edit','update','remove');
$_REQUEST['act'] = in_array($_REQUEST['act'], $allow_action)?$_REQUEST['act']:'list';
if ($_REQUEST['act'] == 'list')
{
     $integral = new integral($GLOBALS['ecs']->table('integral'), $GLOBALS['db']);
     $list = $integral->getIntegralList();
     foreach ($list as &$val)
     {
          $sql = 'SELECT SUM(integral) FROM '.$GLOBALS['ecs']->table('user_integral').
               " WHERE validity>$nowtime AND integral_id={$val['integral_id']}";
          $val['hadsent'] = $GLOBALS['db']->getOne($sql);
     }

     /* 模板赋值 */
     $smarty->assign('full_page',   1);
     $smarty->assign('ur_here',     $_LANG['041_marketing']);
     $smarty->assign('action_link', array('href'=>'integral.php?act=add','text'=>$_LANG['add_integral_rule']));

     $smarty->assign('filter',           $list['filter']);
     $smarty->assign('record_count',     $list['record_count']);
     $smarty->assign('page_count',       $list['page_count']);
     $smarty->assign('list', $list);

     $sort_flag  = sort_flag($list['filter']);
     $smarty->assign($sort_flag['tag'], $sort_flag['img']);

     /* 显示商品列表页面 */
     assign_query_info();

     $smarty->display('integral_list.htm');
}

/* 添加积分规则 */
elseif ($_REQUEST['act'] == 'add')
{
     admin_priv('marketing');

     $smarty->assign('role_list', get_role_list(1));
     $smarty->assign('action', 'insert');

     $smarty->display('integral_info.htm');
}
 
/* 添加积分规则到数据库 */
elseif ($_REQUEST['act'] == 'insert')
{
     admin_priv('marketing');

     $integral_title = mysql_real_escape_string(trim($_POST['integral_title']));
     $platform       = intval($_POST['platform']);
     $integral_way   = intval($_POST['integral_way']);

     // 查询是否已经存在相同规则
     $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('integral').
          " WHERE platform=$platform AND integral_way=$integral_way AND available=1";
     $is_exist = $GLOBALS['db']->getOne($sql);
     if ($is_exist>0)
     {
          $links = array(
               array('text'=>'编辑规则', 'href'=>'javascript:history.go(-1)'),
               array('text'=>'积分管理', 'href'=>'integral.php?act=list'),
          );
          sys_msg('不能添加相同的积分规则，如该积分规则已经不能满足要求，请修改！', 0, $links);
     }

     switch ($integral_way)
     {
     case 1 :
     case 3 : $scale = intval($_POST['scale']); break;
     case 2 :
     case 4 : $scale = floatval($_POST['scale']); break;
     }

     $present_start = strtotime($_POST['present_start']);
     $present_end   = strtotime($_POST['present_end']);
     $validity      = intval($_POST['validity']);

     $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('integral').
          '(integral_title, platform, integral_way, scale, present_start, present_end, validity)VALUES'.
          "('$integral_title', $platform, $integral_way, $scale, $present_start, $present_end, $validity)";
     if ($GLOBALS['db']->query($sql))
     {
          $links = array (array('text'=>'积分管理', 'href'=>'integral.php?act=list'));
          sys_msg('添加成功', 0, $links);
     }
}

/* 修改积分规则 */
elseif ($_REQUEST['act'] == 'edit')
{
     admin_priv('marketing');

     $integral_id = intval($_GET['id']);
     $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('integral').
          " WHERE integral_id=$integral_id";
     $integral = $GLOBALS['db']->getRow($sql);

     $integral['present_start'] = date('Y-m-d', $integral['present_start']);
     $integral['present_end']   = date('Y-m-d', $integral['present_end']);

     $smarty->assign('role_list', get_role_list(1));
     $smarty->assign('integral', $integral);

     $smarty->assign('action', 'update');
     $smarty->display('integral_info.htm');
}

elseif ($_REQUEST['act'] == 'update')
{
     admin_priv('marketing');

     $integral_id = intval($_POST['id']);

     $integral_title = mysql_real_escape_string(trim($_POST['integral_title']));
     $platform       = intval($_POST['platform']);
     $integral_way   = intval($_POST['integral_way']);

     switch ($integral_way)
     {
     case 1 :
     case 3 : $scale = intval($_POST['scale']); break;
     case 2 :
     case 4 : $scale = floatval($_POST['scale']); break;
     }

     $present_start = strtotime($_POST['present_start']);
     $present_end   = strtotime($_POST['present_end']);
     $validity      = intval($_POST['validity']);

     $sql = 'UPDATE '.$GLOBALS['ecs']->table('integral').
          " SET integral_title='$integral_title', platform=$platform, integral_way=$integral_way, scale=$scale,".
          "present_start=$present_start,present_end=$present_end,validity=$validity WHERE integral_id=$integral_id";
     if ($GLOBALS['db']->query($sql))
     {
          $links = array (array('text'=>'积分管理', 'href'=>'integral.php?act=list'));
          sys_msg('修改成功', 0, $links);
     }
}

/* 删除积分规则 */
elseif ($_REQUEST['act'] == 'remove')
{
     admin_priv('marketing');

     $integral_id = intval($_GET['id']);
     $sql = 'SELECT integral_title FROM '.$GLOBALS['ecs']->table('integral').
          " WHERE integral_id=$integral_id";
     $integral = $GLOBALS['db']->getOne($sql);
     if (empty($integral))
          sys_msg('删除失败，请稍后重试！');

     $sql = 'UPDATE '.$GLOBALS['ecs']->table('integral').
          " SET available=0 WHERE integral_id=$integral_id";
     if ($GLOBALS['db']->query($sql))
     {
          $links = array (array('text'=>'积分管理', 'href'=>'integral.php?act=list'));
          sys_msg(sprintf('删除 %s 成功', $integral), 0, $links);
     }
}

?>
