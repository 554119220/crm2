<?php

define('IN_ECS', true);

require(dirname(__FILE__).'/includes/init.php');

if ($_REQUEST['act'] == 'save_sales_target') {
    $msg = array ('req_msg' => true, 'timeout'=>2000);

    // 查询是否已经提交了当月的目标销量
    $month  = strtotime(date('Y-m'));
    $sales_target = addslashes_deep($_REQUEST['sales_target']);
    $sales_target = explode(',', $sales_target);

    foreach ($sales_target as $val) {
        list($admin_id,$target) = explode(':', $val);
        if (empty($target)) {
            continue;
        }

        $sql_select = 'SELECT target_id,sales_target,approved FROM '.$GLOBALS['ecs']->table('sales_target').
            " WHERE month_target>=$month AND admin_id=$admin_id";
        $current_target = $GLOBALS['db']->getRow($sql_select);

        if (empty($current_target)) {
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('sales_target').
                '(month_target,sales_target,admin_id)VALUES('."$month,$target,$admin_id)";
            $GLOBALS['db']->query($sql_insert);
        } else {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('sales_target').
                " SET sales_target=$target WHERE target_id={$current_target['target_id']}";
            $GLOBALS['db']->query($sql_update);
        }
    }

    $msg['message'] = '保存成功！';
    die($json->encode($msg));
}
