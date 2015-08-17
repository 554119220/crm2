<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($_REQUEST['act'] == 'bar_code') {
    $res = array (
        'req_msg' => true,
        'timeout' => 2000,
    );

    $goods_id = intval($_REQUEST['id']);
    $bar_code = addslashes_deep($_REQUEST['value']);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').
        " SET bar_code='$bar_code' WHERE goods_id=$goods_id";
    if ($GLOBALS['db']->query($sql_update)) {
        $res['id']       = $goods_id;
        $res['code']     = 1;
        $res['message']  = '条形码已经保存成功！';
        $res['bar_code'] = $bar_code;
    } else {
        $res['code']    = 2;
        $res['id']      = $goods_id;
        $res['message'] = '条形码保存失败，请稍后再试！';
    }

    die($json->encode($res));
}
