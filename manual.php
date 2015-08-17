<?php
/**
 * ECSHOP 快速购买
 * ============================================================================
 * 版权所有 2010-2020 广州康健人生健康管理有限公司。
 * 网站地址: http://www.kjrs365.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: user.php 17067 2010-03-26 03:59:37Z liuhui $
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

if ($_REQUEST['act'] == 'add')
{
     $today = date('Y年m月d日 H:i:s', time());

     $smarty->assign('today', $today);
     $smarty->assign('ur_here', $_LANG['05_manual']);
     $smarty->display('manual.htm');
}
elseif ($_REQUEST['act'] == 'insert')
{
     unset($_POST['act']);
     $data = $_POST;
     $data['admin_id'] = $_SESSION['admin_id'];
     $data['check_time'] = time();

     $fields = '('.implode(',', array_keys($data)).')';
     $values = '("'.implode('","', array_values($data)).'")';

     $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('check').$fields.'VALUES'.$values;
     if ($GLOBALS['db']->query($sql))
     {
          sys_msg('添加成功！');
     }
}

