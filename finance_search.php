<?php
/**
 * ECSHOP 程序说明
 * ===========================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ==========================================================
 * $Author: liubo $
 * $Id: search_log.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$act = $_REQUEST['act'] ? $_REQUEST['act'] : 'default';

/* 查询视图 */
if ($act == 'search_view')
{
    $smarty->assign('hidden', 1);
    $smarty->assign('ur_here', $_LANG['00_users_search']);
    $res['main'] = $smarty->fetch('search.htm');

    die($json->encode($res));
}

/* 查询数据 */
elseif ($act == 'search')
{
    $keyword    = trim(mysql_real_escape_string($_REQUEST['keyword']));    //关键字
    $condition  = intval($_REQUEST['condition']);                        //检索条件
    $user_type  = array('new' => false,'old' => false);

    $search_key = array(
        'user_name',
        'home_phone',
        'mobile_phone',
        'qq',
        'email',
        'aliww',
        'tracking_sn',
        'order_sn',
        'card_number',
        'user_id',
        'goods_name'
    );

    $condition = $search_key[$condition];

    if (strpos($keyword, '-') === false) {
        if($condition == 'tracking_sn' || $condition == 'order_sn') {
            $where    = " WHERE $condition='$keyword' ";
            $user_old = get_user_id('order_info',$where,$keyword);

            if (!$user_old) {
                $user_new = get_user_id('ordersyn_info',$where,$keyword);
            }
        } else if ($condition == 'card_number') {
            $where    = " WHERE c.$condition=$keyword";
            $user_old = get_user_id('memship_number',$where,$keyword);
        } else if ($condition == 'user_id') {
            $where    = " WHERE user_id={$keyword} ";
            $user_old = get_user_id('users',$where,$keyword);
            $user_new = get_user_id('userssyn',$where,$keyword);
        } else {
            $where    = " WHERE $condition LIKE '%$keyword%'";
            $user_old = get_user_id('users',$where,$keyword);
            $user_new = get_user_id('userssyn',$where,$keyword);
        }

        $tel = $_SESSION['action_list'] == 'all' ? 'CONCAT(home_phone, " ", mobile_phone) tel, ' : "IF(u.admin_id=$_SESSION[admin_id], CONCAT(home_phone,' ', mobile_phone), '-') tel, ";

        if($user_old) {
            foreach($user_old as &$val){
                $user_old_id[] = $val['user_id'];
            }

            $user_old_id = implode(',',$user_old_id);
            $sql = "SELECT DISTINCT u.user_id,u.user_name,u.rank_points, u.admin_name, $tel "
                ." c.card_number, o.order_id,o.admin_name as add_name, CONCAT(o.final_amount, '元') final_amount, FROM_UNIXTIME(o.add_time,'%Y-%m-%d-') order_time, "
                .' s.logbook,FROM_UNIXTIME(s.service_time,"%Y-%m-%d") service_time, o.tracking_sn, o.pay_name, o.shipping_code,o.order_lock, '
                .' r.role_name,o.order_sn,o.platform_order_sn,o.order_status,o.shipping_status,o.shipping_name,o.pay_status '
                .' FROM '.$ecs->table('users')
                .' u LEFT JOIN '.$ecs->table('order_info').' o ON o.user_id=u.user_id LEFT JOIN '.$ecs->table('service')
                .' s ON s.user_id=u.user_id AND u.service_time=s.service_time LEFT JOIN '
                .$ecs->table('role')
                .' r ON o.platform=r.role_id LEFT JOIN '
                .$ecs->table('memship_number')
                ." AS c ON u.user_id=c.user_id WHERE u.user_id IN($user_old_id) ORDER BY o.add_time DESC";

            $old_user_info = $db->getAll($sql); //老顾客
        }

        if($user_new) {
            foreach($user_new as &$val){
                $user_new_id[] = $val['user_id'];
            }

            $user_new_id = implode(',',$user_new_id);
            $sql = "SELECT DISTINCT u.user_id,u.user_name,u.rank_points, u.admin_name, '-' as tel, "
                ." c.card_number,o.order_id,o.admin_name as add_name, CONCAT(o.final_amount, '元') final_amount, FROM_UNIXTIME(o.add_time,'%Y-%m-%d') order_time, "
                .' o.tracking_sn, o.pay_name, o.shipping_code,o.order_lock,'
                .' r.role_name,o.order_sn,o.order_status,o.shipping_status,o.shipping_name,o.pay_status '
                .' FROM '.$ecs->table('userssyn')
                .' u LEFT JOIN '.$ecs->table('ordersyn_info')
                .' o ON o.user_id=u.user_id LEFT JOIN '
                .$ecs->table('role').' r ON o.platform=r.role_id LEFT JOIN '
                .$ecs->table('memship_number')
                ." AS c ON u.user_id=c.user_id WHERE u.user_id IN($user_new_id)"
                .' ORDER BY o.add_time DESC';

            $new_user_info  = $db->getAll($sql); //新顾客
        }
    } else {
        $keyword = substr($keyword, 1);
        if($user_type['old'])
        {
            $sql = 'SELECT u.user_id,u.user_name,u.rank_points, u.admin_name, o.tracking_sn, o.shipping_code FROM '.$ecs->table('order_info').' o, '.$ecs->table('users')." u WHERE o.order_status=5 AND o.pay_status=2 AND o.user_id=u.user_id AND o.$condition='$keyword'";
            $old_user_info = $db->getAll($sql);
            $user_type['old'] = true;
        }

        if($user_type['new']) {
            $sql = 'SELECT u.user_id,u.user_name,u.rank_points, u.admin_name, o.tracking_sn, o.shipping_code FROM '.$ecs->table('ordersyn_info').' o, '.$ecs->table('users')." u WHERE o.order_status=5 AND o.pay_status=2 AND o.user_id=u.user_id AND o.$condition='$keyword'";
            $new_user_info = $db->getAll($sql);
            $user_type['new'] = true;
        }
    }

    if($user_new || $user_old) {
        $user_type = array(
            'new' => $user_new,
            'old' => $user_old
        );

        foreach($user_type as $key=>$type) {
            if($type != 0) {
                if($key == 'old') {
                    $resource = 'old_user_info'; 
                    $res = $old_user_info;
                } else {
                    $resource = 'new_user_info';
                    $res      = $new_user_info;
                }

                $res = $key == 'old' ? $old_user_info : $new_user_info;

                if($res){
                    foreach ($res as &$val) {
                        $shipping_code = $val['shipping_code'];
                        $val['link'] = "<a href='http://www.kuaidi100.com/chaxun?com={$shipping_code}&nu={$val['tracking_sn']}' target='_blank'>{$val['tracking_sn']}</a>";
                        $val['order_status'] = $val['order_status'].$val['shipping_status'];

                        switch($val['order_status']) {
                        case '52' :
                            $val['order_status'] = '已签收';
                            break;
                        case '00' :
                            $val['order_status'] = '待确认';
                            break;
                        case '13' :
                            $val['order_status'] = '已取消';
                            break;
                        case '53' :
                            $val['order_status'] = '已取消';
                            break;
                        case '10' :
                            $val['order_status'] = '待发货';
                            break;
                        case '51' :
                            $val['order_status'] = '已发货';
                            break;
                        case '54' :
                            $val['order_status'] = '已退货';
                            break;
                        case '-20':
                            $sql_select = 'SELECT user_name FROM '.$ecs->table('admin_user')." WHERE user_id={$val['order_lock']}";
                            $val['order_status'] = '已被'.$db->getOne($sql_select).'删除';
                            break;
                        default :
                            break;
                        }
                    }
                }

                if($key == 'old') {
                    $old_user_info = $res;
                } else {
                    $new_user_info = $res;
                }
            }
        }

        if($user_type['old'])
            $smarty->assign('old_user_info',$old_user_info);
        if($user_type['new'])
            $smarty->assign('new_user_info',$new_user_info);

        $smarty->assign('user_type',$user_type);
        $smarty->assign('user_list', $res);
        $res = $smarty->fetch('finance_res_search.htm');
        die($json->encode($res));
    } else {
        die($json->encode(0));
    }
}

elseif($act == 'goods')
{
    $user_type = intval($_REQUEST['user_type']);
    if($user_type)
    {
        $sql = 'SELECT goods_name, goods_number ,goods_price FROM'.$ecs->table('ordersyn_goods')." WHERE order_id=$_POST[id]";
    }
    else
    {
        $sql = 'SELECT goods_name, goods_number ,goods_price FROM'.$ecs->table('order_goods')." WHERE order_id=$_POST[id]";
    }
    $res = $db->getAll($sql);

    $text = "<span><b>订单商品表：</b></span><ul style='margin:5px !important'>";
    foreach ($res as $val) 
    {
        $text .= '<li>'.$val['goods_name'].'×'.$val['goods_number']." 价格:{$val['goods_price']}</li>";
    }
    $text .= '</ul>';
    die($text);
}
else
{
    $keyword = mysql_real_escape_string($_REQUEST['keyword']);

    if (strpos($keyword, '-') === false)
    {
        $sql = 'SELECT * FROM '.$ecs->table('users')." WHERE user_name LIKE '%$keyword%' "
            ." OR home_phone LIKE '%$keyword%' OR mobile_phone LIKE '%$keyword%' "
            ." OR qq LIKE '%$keyword%' OR aliww LIKE '%$keyword%' OR email LIKE '%$keyword%'";
        if (count($db->getAll($sql)))
        {
            $tel = $_SESSION['action_list'] == 'all' ? 'CONCAT(home_phone, " ", mobile_phone) tel, ' : "IF(u.admin_id=$_SESSION[admin_id], CONCAT(home_phone,' ', mobile_phone), '-') tel, ";
            $sql = "SELECT u.user_name, u.admin_name, $tel "
                ." o.order_id, CONCAT(o.final_amount, '元') final_amount, FROM_UNIXTIME(o.add_time, '%Y-%m-%d') order_time, "
                ." s.logbook, FROM_UNIXTIME(s.service_time, '%Y-%m-%d') service_time, o.tracking_sn, o.shipping_name"
                .' FROM '.$ecs->table('users').' u LEFT JOIN '.$ecs->table('order_info').' o ON o.user_id=u.user_id LEFT JOIN '.$ecs->table('service').' s ON s.user_id=u.user_id AND  u.service_time=s.service_time '
                ." WHERE o.order_status=5 AND o.pay_status=2 AND u.user_name LIKE '%$keyword%' "
                ." OR u.home_phone LIKE '%$keyword%' "
                ." OR u.mobile_phone LIKE '%$keyword%' "
                ." OR u.qq LIKE '%$keyword%' "
                ." OR u.aliww LIKE '%$keyword%' "
                ." OR u.email LIKE '%$keyword%' "
                ." ORDER BY o.add_time DESC ";
        }
    }
    else
    {
        $keyword = substr($keyword, 1);
        $sql = 'SELECT u.user_name, u.admin_name, i.tracking_sn, i.shipping_code FROM '.$ecs->table('order_info').' i, '.$ecs->table('users')." u WHERE i.order_status=5 AND i.pay_status=2 AND i.user_id=u.user_id AND i.tracking_sn='$keyword'";
    }

    if ($res = $db->getAll($sql))
    {
        foreach ($res as &$val)
        {
            $shipping_code = $val['shipping_code'] == 'sto_express' ? 'zjs' : $val['shipping_code'];
            $val['link'] = "<a href='http://www.kuaidi100.com/chaxun?com={$shipping_code}&nu={$val['tracking_sn']}' target='_blank'>{$val['tracking_sn']}</a>";
        }

        $smarty->assign('user_list', $res);
        $res = $smarty->fetch('search.htm');
        die($res);
    }
    else
    {
        die(0);
    }
}

// 获取顾客ID
function get_user_id($table_name,$where,$keyword){
    if($table_name != ''){
        $user_id = array();

        if(in_array($table_name,array('userssyn','users'))){
            $where .= ' AND is_black IN(0,4)';

            /*先在顾客表寻找*/
            $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table($table_name)
                ."$where GROUP BY user_id";
            $user_id = $GLOBALS['db']->getAll($sql_select);

            /*到顾客联系表找*/
            if(count($user_id) == 0){
                $array_key = explode(' ',$where);
                $condition = $array_key[2];

                switch($condition){
                case 'mobile_phone':
                    $condition = 'mobile';
                    break;
                case 'home_phone':
                    $condition = 'tel';
                    //if(strpos($keyword,'-')){
                    //    $keyword = substr($keyword,stristr($keyword,'-'));
                    //}
                    break;
                }

                $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('user_contact').
                    " WHERE contact_name='$condition' AND contact_value='$keyword'";
                $user_id = $GLOBALS['db']->getAll($sql_select);
            }
        }else{
            $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table($table_name)
                ."$where GROUP BY user_id";
            $user_id    = $GLOBALS['db']->getAll($sql_select);    
        }
        return $user_id;
    }else{
        return false;
    }
}
