<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
date_default_timezone_set('Asia/Shanghai');

if ($_REQUEST['act'] == 'admin_name') {
    $smarty->assign('type',  'select');
    $smarty->assign('field', 'admin_id');

    $seller_list = seller_list();
    if ($_SESSION['admin_id'] == 4 || admin_priv('all', '', false)) {
        array_push($seller_list, array('name'=>'李健均', 'id'=>493));
    }

    $smarty->assign('list',  $seller_list);

    $html = $smarty->fetch('search_builder.htm');
    die($html);
}

/* 订单二次跟进 */
elseif ($_REQUEST['act'] == 'shipping_feed') {
    $smarty->assign('field', 'shipping_feed_id');

    $html = $smarty->fetch('search_builder.htm');
    die($html);
}

/* 顾客来源 */
elseif ($_REQUEST['act'] == 'from_where') {
    $sql_select = 'SELECT `from_id` id,`from` name FROM '.$GLOBALS['ecs']->table('from_where').' ORDER BY sort ASC';
    $source_list = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('field', 'from_where');
    $smarty->assign('type', 'select');
    $smarty->assign('list', $source_list);

    $html = $smarty->fetch('search_builder.htm');
    die($html);
}

/* 电商平台 */
elseif ($_REQUEST['act'] == 'platform') {
    $sql_select = 'SELECT role_id id,role_name name FROM '.
        $GLOBALS['ecs']->table('role').' WHERE role_type=3';
    $source_list = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('field', 'platform');
    $smarty->assign('type', 'select');
    $smarty->assign('list', $source_list);

    $html = $smarty->fetch('search_builder.htm');
    die($html);
}

/* 获取顾客来源ID */
elseif ('obtain_fromwhere' == $_REQUEST['act']) {
    $from = mysql_real_escape_string(trim($_REQUEST['fromWhere']));
    $sql_select = 'SELECT from_id FROM '.
        $GLOBALS['ecs']->table('from_where')." WHERE `from`='$from'";
    $from_id = $GLOBALS['db']->getOne($sql_select);

    if ($from_id) {
        $result = array('from_id'=>$from_id);
    } else {
        $result = array(
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '错误的顾客来源，请重新输入！'
        );
    }
    die(json_encode($result));
}

/**
 * 客服列表
 */
function seller_list ()
{
    $sql = 'SELECT user_name name, user_id id FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE status>0 AND stats>0';
    if (admin_priv('all', '', false) || admin_priv('finance', '', false)) {// 全局 
        $sql .= ' AND role_id IN ('.SALE.')';
    } elseif (admin_priv('user_trans-part_view', '', false)) {
        $trans_role_list = implode(',', trans_part_list());
        $sql .= " AND role_id IN ($trans_role_list) ";
    } elseif(admin_priv('user_part_view', '', false)) { // 部门
        $sql .= " AND role_id={$_SESSION['role_id']}";
    } elseif (admin_priv('user_group_view', '', false)) {
        $sql .= " AND role_id={$_SESSION['role_id']} AND group_id={$_SESSION['group_id']}";
    } else {
        return false;
    }

    return $GLOBALS['db']->getAll($sql);
}
