<?php
/**
 * ECSHOP 生日提醒页
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.kjrs365.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: birth.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . '/includes/lib_order.php');
require(dirname(__FILE__) . '/includes/cls_date.php');
$res = array ('response_action' => 'list_service','switch_tag' => true, 'id' => $_REQUEST['tag']);

if($_REQUEST['act'] == 'list')
{
    /* START：获取当天、前五天、未来五天生日的顾客名单 */
    $range = empty($_REQUEST['range']) ? 0 : $_REQUEST['range'];
    $now = '';
    //未来五天生日的顾客
    if($range == 5)
    {
         for($i = 2;$i < $range;$i++)
         {
              $time = strtotime("now") + (24*60*60)*$i;
              $date = date('Y-m-d',$time);
              $arr = explode('-',$date);
              $ld = new Lunar($arr[0],$arr[1],$arr[2]);
              $nl = $ld->display();
              $now = $now . ',\'' . $nl . '\'';
         }
         $now = ltrim($now,',');
         $where = ' SUBSTR(u.birthday,"6") IN ('.$now.') ';           
    }
    //五天前生日的顾客
    elseif($range == -5)
    {
         for($i = 2;$i < abs($range);$i++)
         {
              $time = strtotime("now") - (24*60*60)*$i;
              $date = date('Y-m-d',$time);
              $arr = explode('-',$date);
              $ld = new Lunar($arr[0],$arr[1],$arr[2]);
              $nl = $ld->display();
              $now = $now . ',\'' . $nl . '\'';      
         }
         $now = ltrim($now,',');
         $where = ' SUBSTR(u.birthday,"6") IN ('.$now.') ';            
    }
    //获取当月生日的顾客
    elseif($range == 30)
    {
         $date = date('Y-m-d');
         $arr = explode('-',$date);
         $ld = new Lunar($arr[0],$arr[1],$arr[2]);
         $nl = $ld->display();
         $nl = explode('-',$nl);
         $mon = $nl[0];
         $where = ' MONTH(u.birthday) = '.$nl[0];
    } 
    //当天生日的顾客
    else
    {
         $time = strtotime("now") + (24*60*60)*$range;
         $date = date('Y-m-d',$time);
         $arr = explode('-',$date);
         $ld = new Lunar($arr[0],$arr[1],$arr[2]);
         $nl = $ld->display();
         $now = '\''.$nl.'\'';
         $where = ' SUBSTR(u.birthday,"6") IN ('.$now.') ';           
    }

    if($_SESSION['action_list'] == 'all')
    {
         $admin = ' AND u.admin_id>0';
    }
    else
    {
         $admin= ' AND u.admin_id='.$_SESSION['admin_id'];
    }

    //获取年份，用来计算年龄的
    $year = date('Y');

    //查询生日的顾客的信息
    $sel_user = 'SELECT u.user_id,u.user_name,u.sex,u.birthday,(' .$year. '-YEAR(u.birthday)) AS age,u.mobile_phone,u.home_phone,u.qq,u.aliww,u.address_id,r.region_name as province FROM '.$GLOBALS['ecs']->table('users').' u ,'.$GLOBALS['ecs']->table('user_address').' a,'.$GLOBALS['ecs']->table('region').' r WHERE u.user_id = a.user_id AND r.region_id=a.province '.$admin.' AND ';

    //查询生日的顾客的所属的市
    $sel_city = 'SELECT u.user_id,r.region_name as city FROM '.$GLOBALS['ecs']->table('users').
         ' u ,'.$GLOBALS['ecs']->table('user_address').' a,'.$GLOBALS['ecs']->table('region').
         ' r WHERE u.user_id = a.user_id AND r.region_id=a.city '.$admin.' AND ';

    $sql = $sel_user. $where. ' ORDER BY SUBSTR(u.birthday,"6"),u.user_id ASC';
    $user = $GLOBALS['db']->getAll($sql);
    $sql = $sel_city. $where. ' ORDER BY SUBSTR(u.birthday,"6"),u.user_id ASC';
    $city = $GLOBALS['db']->getAll($sql);
    $j = 0;
    for($i=0;$i<count($city);$i++)
    {
         if($user[$i]['user_id'] == $city[$j]['user_id'])
         {
              $user[$i]['city'] = $city[$j]['city'];
              $j++;
         }
         else
         {
              $user[$i]['city'] = '';
         }        
    }

    $smarty->assign('user',$user);
    //判断是不是Ajax请求
    if(!empty($_REQUEST['show_birth']))
    {
         $result = $smarty->fetch('show_birth.htm');
         die($result);  
    }
    else
    {
         $smarty->display('birth_list.htm');        
    }
}

/* END：获取当天、前五天、未来五天生日的顾客名单 */

/* 获取用户所有有效订单的详细信息 */
//elseif ($_REQUEST['act'] == 'get_order_info')
//{
//    $user_id = intval($_POST['user_id']);
//    if ($user_id)
//    { 
/* 读取用户的所有有效订单 */
/*        $sql = 'SELECT order_id, FROM_UNIXTIME(add_time, "%Y-%m-%d %H:%i:%s") add_time FROM '
            .$GLOBALS['ecs']->table('order_info'). 
            " WHERE order_status=5 AND shipping_status>0 AND pay_status=2 AND final_amount>0
            AND user_id=$user_id ORDER BY order_id DESC";
        $order_res = $GLOBALS['db']->getAll($sql);

        foreach ($order_res as $val) 
        {
            $order_list[$val['order_id']] = $val['add_time'];
        }

        $order_id = implode(',', array_keys($order_list));
 */
/* 读取所有有效订单中的商品及数量 */
/*        $sql = 'SELECT goods_name, goods_number, order_id FROM '
            .$GLOBALS['ecs']->table('order_goods')
            ." WHERE order_id IN ($order_id) ORDER BY order_id DESC";
        $goods_res = $GLOBALS['db']->getAll($sql);

        foreach ($goods_res as $val)
        {
            $goods_list[$val['order_id']][] = array_splice($val, 0, 2);
        }

        foreach ($goods_list as $key=>$val)
        {
            $order_info[$order_list[$key]] = $val;
        }

        $smarty->assign('goods_list', $order_info);
        $smarty->assign('order_num', count($order_list));
        echo $smarty->fetch('rebuy_goods_list.htm');
        exit; 
    }
} */

/* 获取用户所有有效订单的详细信息 */
elseif($_REQUEST['act'] == 'get_order_info')
{
     $user_id = intval($_POST['user_id']);
     if($user_id){
          $sql = 'SELECT og.goods_name,og.goods_number,FROM_UNIXTIME(add_time, "%Y-%m-%d %H:%i:%s") add_time 
               FROM '.$GLOBALS['ecs']->table('order_info').' oi,'.$GLOBALS['ecs']->table('order_goods').' og 
               WHERE oi.order_id = og.order_id AND oi.order_status = 5 AND oi.shipping_status>0 AND oi.pay_status = 2
               AND final_amount>0 AND oi.user_id = '.$user_id.' ORDER BY oi.order_id DESC';
          $order_list = $GLOBALS['db']->getAll($sql);
          $result = '<strong>当前顾客一共有'.count($order_list).'个订单</strong>';
          foreach($order_list as $key=>$order_list){
               $result = $result .'<dl><dt>'.$order_list['add_time'].'</dt><dd>'.$order_list['goods_name'].'×'.$order_list['goods_number'].'</dd></dl>';
          }
          die($result);
          exit;
     }

/*    $result = '<table border="1"><tr><th style="font-weight:700;width:190px;">商品名称</th><th style="font-weight:700;width:40px;">数量</th><th style="font-weight:700;width:70px;">购买总金额</th></tr>';
    foreach($order as $key=>$order){
       $key = $key+1;
       $result = $result .'<tr><td>'.$order['goods_name'].'</td><td>'.$order['goods_number'].'</td><td>'.$order['goods_amount'].'</td></tr>';
    }
die($result); */
} 

/* 判断用户的联系方式是否为手机 */
elseif($_REQUEST['act'] == 'send_sms'){
     //    $user_name = $_REQUEST['user_name'];
     $mobile = $_REQUEST['mobile'];
     //    $mobile = explode(',',$mobile);
     $msg = $_REQUEST['sms'];
     //发送生日祝福信息到顾客手机
     require_once(ROOT_PATH . 'includes/cls_sms.php');
     $sms = new sms();
     $send = $sms->send($mobile, $msg);
     if($send === true){ die('1'); }
     else { die('0'); } 
}

?>
