<?php
/**
 * ECSHOP 订单管理
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: yehuaixiao $
 * $Id: order.php 17157 2010-05-13 06:02:31Z yehuaixiao $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');
define(VIP, "0002");
$nowtime = time() +28800;

/*-- 售后子菜单 --*/
if ($_REQUEST['act'] == 'menu')
{
     $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
     $nav = list_nav();
     $smarty->assign('nav_2nd', $nav[1][$file]);
     $smarty->assign('nav_3rd', $nav[2]);
     $smarty->assign('file_name', $file);

     die($smarty->fetch('left.htm'));
}
if ($_REQUEST['act'] == 'menu')
{
     die($smarty->fetch('left.htm'));
}
elseif ($_REQUEST['act'] == 'wait_for_shipping')
{
     die($_SESSION['action_list']);
}

