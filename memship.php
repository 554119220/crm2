<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

/* 查询会员卡号 */
if ($_REQUEST['act'] == 'card_list') {
        $res = array ('req_msg'=>true, 'timeout'=>2000);

    if (is_numeric($_REQUEST['card_number'])) {
        $card_number = $_REQUEST['card_number'];
    } else {
        $res['message'] = '您输入的卡号有误，请重新输入！';
        $res['code']    = 2;
        die($json->encode($res));
    }

    $order_id = intval($_REQUEST['order_id']);
    if (empty($order_id)) {
        $res['message'] = '缺少订单信息，请稍后再试或告知技术人员！';
        $res['code']    = 2;
        die($json->encode($res));
    }

    // 查询该会员是否已绑定了会员卡
    $sql_select = 'SELECT m.card_number,m.bind_time FROM '.$GLOBALS['ecs']->table('order_info').' i, '.
        $GLOBALS['ecs']->table('memship_number')." m WHERE m.user_id=i.user_id AND i.order_id=$order_id";
    $binded_card_info = $GLOBALS['db']->getRow($sql_select);
    if (!empty($binded_card_info)) {
        $res['message']     = '该顾客已于'.date('Y-m-d', $binded_card_info['bind_time']).'绑定过会员卡！';
        $res['code']        = 2;
        $res['card_number'] = $binded_card_info['card_number'];
        die($json->encode($res));
    }

    // 查询会员卡的类型
    $sql_select = 'SELECT team FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
    $team = $GLOBALS['db']->getOne($sql_select);

    $mem_mark = in_array($team, explode(',', ZHONGLAONIAN_SALE)) ? 1 : 2;

    $sql_select = 'SELECT card_number FROM '.$GLOBALS['ecs']->table('memship_number').
        " WHERE user_id=0 AND card_number REGEXP '^{$mem_mark}[0-9]{0,7}$card_number$' ORDER BY card_id ASC";
    $card_number  = $GLOBALS['db']->getCol($sql_select);

    die($json->encode($card_number));
}

/* 绑定会员卡号 */
elseif ($_REQUEST['act'] == 'bind_card_number') {
    $res = array ('req_msg'=>true, 'timeout'=>2000,);
    $order_id = intval($_REQUEST['order_id']);
    if (is_numeric($_REQUEST['card_number'])) {
        $card_number = $_REQUEST['card_number'];
    } else {
        $res['message'] = '您输入的卡号有误，请重新输入！';
        die($json->encode($res));
    }

    // 验证将绑定的会员卡是否已被绑定
    $sql_select = 'SELECT user_id,bind_time FROM '.$GLOBALS['ecs']->table('memship_number').
        " WHERE card_number=$card_number";
    $mem_bind_info = $GLOBALS['db']->getOne($sql_select);
    if (!empty($mem_bind_info)) {
        $res['message'] = '该会员卡已为其他会员绑定，请尝试其它会员卡！';
        die($json->encode($res));
    } else {
        $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
        $user_id = $GLOBALS['db']->getOne($sql_select);
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('memship_number').
            " SET user_id=$user_id,binder={$_SESSION['admin_id']},bind_time=UNIX_TIMESTAMP() WHERE card_number=$card_number";
        if ($GLOBALS['db']->query($sql_update)) {
            $res['message'] = '会员卡绑定成功！';
            $res['code']        = 1;
            $res['card_number'] = $card_number;
            die($json->encode($res));
        } else {
            $res['message']     = '会员卡绑定失败！';
            die($json->encode($res));
        }
    }
}
