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

    if((admin_priv('finance','',false)) && !admin_priv('all','',false)){
        $smarty->assign('finance','finance');
    }

    $smarty->display('search.htm');
}

/* 查询数据 */
elseif ($act == 'search')
{
    $keyword    = trim(mysql_real_escape_string($_REQUEST['keyword']));
    $condition  = intval($_REQUEST['condition']);                        
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
        'platform',
    );

    $condition = $search_key[$condition];
    $order_coidtion = array('tracking_sn','order_sn','platform');

    if (strpos($keyword, '-') === false) {
        if(in_array($condition,$order_coidtion)) {
            if('platform' == $condition){
                $platform_code = $_REQUEST['platform_code'];
                $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('user_contact').
                    " WHERE contact_name='$platform_code' AND contact_value='$keyword'"; 
                $user_old = $GLOBALS['db']->getAll($sql_select);
            }else{
                $where    = " WHERE $condition='$keyword' ";
                $user_old = get_user_id('order_info',$where,$keyword);

                if (!$user_old) {
                    $user_new = get_user_id('ordersyn_info',$where,$keyword);
                }    
            }
        } else if ($condition == 'card_number') {
            $where    = " WHERE $condition=$keyword";
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

        if($user_old) {
            foreach($user_old as $val){
                if($val['user_id']){
                    $user_id[] = $val['user_id'];
                }
            }

            if($user_id){
                $user_id_list  = implode(",",$user_id);
                $old_user_info = get_user_info('users',$user_id_list);    
                $old_user_info = get_admin_qq($old_user_info);
            }else{
                $user_old = ''; 
            }
        }

        if($user_new) {
            foreach($user_new as $val){
                if($val['user_id'] && $val['user_id'] != ''){
                    $user_id[] = $val['user_id'];
                }
            }

            if($user_id){
                $user_id_list  = implode(',',$user_id);
                $new_user_info = get_user_info('userssyn',$user_id_list);    
                $new_user_info = get_admin_qq($new_user_info);
            }else{
                $user_new = ''; 
            }
        }
    } else {
        //固定电话
        $where    = " WHERE $condition = '$keyword'";
        $user_old = get_user_id('users',$where,$keyword);
        if ($user_old) {
            foreach($user_old as $val){
                if($val['user_id']){
                    $user_id[] = $val['user_id'];
                }
            }
            if($user_id){
                $user_id_list  = implode(",",$user_id);
                $old_user_info = get_user_info('users',$user_id_list);    
                $old_user_info = get_admin_qq($old_user_info);
            }else{
                $user_old = ''; 
            }
        }
    }

    if($user_new || $user_old)
    {
        if($user_old){
            $smarty->assign('old_user_info',$old_user_info);
        }
        if($user_new){
            $smarty->assign('new_user_info',$new_user_info);
        }

        $smarty->assign('user_type',$user_type);
        $smarty->assign('user_list', $res);
        $res = $smarty->fetch('res_search.htm');

        die($json->encode($res));
    }
    else
    {
        die($json->encode(0));
    }
}


// 公共查询更多信息
elseif ($_REQUEST['act'] == 'get_more_info'){
    $user_id   = intval($_REQUEST['user_id']);
    $condition = mysql_real_escape_string($_REQUEST['condition']);
    $type      = intval($_REQUEST['type']);

    if($user_id){
        if($condition == 'order'){
            $table_name  = $type == 0 ? 'ordersyn_info' : 'order_info';
            $goods_table =  $type == 0 ? 'ordersyn_goods' : 'order_goods';

            if($table_name == 'ordersyn_info'){
                $order_sql = ' r.role_describe,o.order_sn,o.order_status,o.shipping_status,o.shipping_name,o.pay_status FROM ';
            }else{
                $order_sql = ' r.role_describe,o.order_sn,o.platform_order_sn,o.order_status,o.shipping_status,o.shipping_name,o.pay_status FROM ';
            }

            $sql_select  = 'SELECT o.order_id,o.admin_name as add_name,o.admin_id,'
                ." CONCAT(o.final_amount, '元') final_amount, FROM_UNIXTIME(o.add_time,'%Y-%m-%d') order_time, "
                .' o.tracking_sn, o.pay_name, o.shipping_code,o.order_lock, '.$order_sql
                .$GLOBALS['ecs']->table($table_name)
                .' o LEFT JOIN '.$GLOBALS['ecs']->table('role')
                .' r ON o.platform=r.role_id'
                ." WHERE o.user_id=$user_id ORDER BY o.add_time DESC LIMIT 5";

            try{
                $order_info = $GLOBALS['db']->getAll($sql_select);
            }catch(Exception $e){
                $order_info = null;
            }

            if($order_info){
                $res = '<div style="text-align:right"><span onclick="document.getElementById('.
                    "'goods_search_res').style.display='none'".'" style="cursor:pointer">关闭<span></div>';
                //进行权限控制
                $authority = search_authority();
                $role_id = $_SESSION['role'] ? $_SESSION['role'] : 0;
                $sql_select = 'SELECT action FROM '.$GLOBALS['ecs']->table('role').
                    " WHERE role_id=$role_id";
                $action = $GLOBALS['db']->getOne($sql_select);
                foreach($order_info as &$val){
                    $val['order_sn'] = $val['platform_order_sn'] ? $val['platform_order_sn'] : $val['order_sn'];
                    $shipping_code = $val['shipping_code'];
                    $val['order_status'] = $val['order_status'].$val['shipping_status'];
                    //进行权限判断
                    $link = "<a href='http://www.kuaidi100.com/chaxun?com=$shipping_code&nu={$val['tracking_sn']}' target='_blank'}>{$val['tracking_sn']}</a>";
                    if ('member' == $action) {
                        if ($val['admin_id'] == $_SESSION['admin_id']) {
                            $val['link'] = $link;
                        }else{
                            if ('52' == $val['order_status']) {
                                $val['link'] = $link;
                            }else{
                                $val['link'] = '<font style="font-size:13px"> （处于保护未显示）</font>';
                            }
                        }
                    }else{
                        $val['link'] = $link;
                    }

                    $order_status = array(
                        '52' => '已签收',
                        '00' => '待确认',   
                        '13' => '已取消',
                        '53' => '已取消',
                        '10' => '待发货',
                        '51' => '已发货',
                        '54' => '已退货'
                    );

                    if($val['order_status'] == '-20'){
                        $sql_select = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user')
                            ." WHERE user_id={$val['order_lock']}";
                        $val['order_status'] = '已被'.$GLOBALS['db']->getOne($sql_select).'删除';                           
                    }else{
                        $val['order_status'] = $order_status[$val['order_status']];
                    }

                    if (empty($val['link'])) {
                        $val['link'] = '上门自提';
                    }

                    $order_text         = "{$val['order_time']}消费{$val['final_amount']}【{$val['role_describe']}-{$val['order_sn']}-{$val['order_status']}】{$val['link']}";
                    $goods_text         = get_search_goods($goods_table,$val['order_id']);

                    $res .= '<details><summary>'.$order_text.'</summary>'.$goods_text.'</details><hr/><br/>';
                }
            }else{
                $res = 'CRM没有这个顾客的订单';
            }

        }elseif($condition == 'vip_card'){
            $sql_select = 'SELECT u.rank_points,m.card_number FROM '.$GLOBALS['ecs']->table('users')
                .' u LEFT JOIN '.$GLOBALS['ecs']->table('memship_number')
                ." m ON m.user_id=u.user_id AND m.user_id<>0 WHERE u.user_id=$user_id";

            $vip_info = $GLOBALS['db']->getRow($sql_select);
            if($vip_info){
                $res = "【会员卡号】{$vip_info['card_number']} 【积分】{$vip_info['rank_points']}";
            }else{
                $res = '该顾客还没有绑定会员卡';
            }
        }

        die($json->encode($res));
    }
}

// 获取顾客ID
function get_user_id($table_name,$where,$keyword){
    if($table_name != ''){
        $user_id = array();

        if(in_array($table_name,array('userssyn','users'))){

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
                    if(strpos($keyword,'-')){
                        $keyword = substr($keyword,stristr($keyword,'-')+1);
                    }
                    break;
                }

                $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('user_contact').
                    " WHERE contact_name='$condition' AND contact_value LIKE '%$keyword%'";
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


// 顾客信息
function get_user_info($table_name,$user_id){
    $tel = $_SESSION['action_list'] == 'all' ? 'CONCAT(home_phone, " ", mobile_phone) tel ' : "IF(admin_id=$_SESSION[admin_id], CONCAT(home_phone,' ', mobile_phone), '-') tel ";

    $sql = "SELECT u.user_id,u.user_name,u.admin_name,u.home_phone,u.mobile_phone,u.admin_id,u.is_black,c.type_name customer_type FROM "
        .$GLOBALS['ecs']->table($table_name).' u LEFT JOIN '.$GLOBALS['ecs']->table('customer_type')
        .' c ON u.customer_type=c.type_id'  
        ." WHERE u.user_id IN ($user_id)";

    $user_info = $GLOBALS['db']->getAll($sql);
    if ($user_info) {
        if ($_SESSION['action_list'] != 'all') {
            foreach ($user_info as &$v) {
                if ($v['admin_id'] != $_SESSION['admin_id']) {
                    $mobile_phone = hideContact($v['mobile_phone']);
                    $home_phone = hideContact($v['home_phone']);
                }else{
                    $mobile_phone = $v['mobile_phone'];
                    $home_phone = $v['home_phone'];
                }
                $v['tel'] = "$mobile_phone $home_phone";
            }
        }else{
            foreach ($user_info as &$v) {
                $v['tel'] = "{$v['mobile_phone']} {$v['home_phone']}";
            }
        }
    }

    return $user_info;
}

/*商品信息*/
function get_search_goods($table_name,$order_id)
{
    $sql = 'SELECT goods_name, goods_number ,goods_price FROM'.$GLOBALS['ecs']->table($table_name)." WHERE order_id=$order_id";
    $res = $GLOBALS['db']->getAll($sql);

    $text = "<hr style='margin-top:3px;'/><ul style='margin:5px !important'>";
    foreach ($res as $val) 
    {
        $text .= '<li>'.$val['goods_name'].'×'.$val['goods_number']." 价格:{$val['goods_price']}</li>";
    }
    $text .= '</ul>';

    return $text;
}

/*客服的QQ*/
function get_admin_qq($user_info){
    $admin_list = $qq_list = array();
    foreach($user_info as $val){
        $admin_list[] = $val['admin_id'];
    }

    if($admin_list != null && count($admin_list) > 0){
        $admin_list = implode("','",$admin_list);

        $sql_select = 'SELECT user_id,account_name FROM '.$GLOBALS['ecs']->table('account').
            " WHERE type_id=1 AND user_id IN('$admin_list')";
        $qq_list = $GLOBALS['db']->getAll($sql_select);
    }

    foreach($user_info as &$user){
        foreach($qq_list as $qq){
            if($user['admin_id'] == $qq['user_id']){
                $user['qq'] = $qq['account_name'];
            } 
        }
    }

    return $user_info;
}

//公共查询权限
function search_authority(){
    if (admin_priv('search_all','',false) || admin_priv('all','',false)) {
        return 'all';
    }else if (admin_priv('search_role','',false)){
        return 'role';
    }else if (admin_priv('search_group','',false)) {
        return 'group';
    }else{
        return 'general';
    }

}
