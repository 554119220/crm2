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
define('TIME_LAG', 50400);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
//require(ROOT_PATH . 'includes/lib_payment.php');

$_REQUEST['act'] = $_REQUEST['act'] ? $_REQUEST['act'] : '';

if ($_REQUEST['act'] == 'from_where')
{
    /* 顾客来源统计 */
    $sql = 'SELECT `from_id`, `from` FROM '.$ecs->table('from_where').' WHERE sort>=0';
    $from_where = $db->getAll($sql);

    /* 获取顾客列表 */
    $sql = 'SELECT user_id, from_where  FROM '.$ecs->table('users').' WHERE admin_id>0';
    $user_list = $db->getAll($sql);
    foreach ($user_list as $val)
    {
        foreach ($from_where as $v)
        {
            if($v['from_id'] == $val['from_where'])
            {
                $total[$v['from']]['num']++;
                $total[$v['from']]['from_id'] = $v['from_id'];
            }
        }
    }

    $smarty->assign('total', $total);

    /* 默认获取当天订单数量 */
    $date = $_POST['start_date'] ? $_POST['start_date'] : date('Y-m-01');
    $sql = 'SELECT final_amount, order_source FROM '.$ecs->table('order_info').
        ' WHERE order_source>0 AND add_time>UNIX_TIMESTAMP("'.$date.'") -6*3600';
    $_POST['end_date'] && $sql = 'SELECT final_amount, order_source FROM '.$ecs->table('order_info').
        ' WHERE order_source>0 AND add_time<UNIX_TIMESTAMP("'.$_POST['end_date'].'") +18*3600';

    $_POST['start_date'] && $_POST['end_date'] && $sql = 'SELECT final_amount, order_source FROM '.$ecs->table('order_info').
        ' WHERE order_source>0 AND add_time>UNIX_TIMESTAMP("'.$_POST['start_date'].'") -6*3600 AND add_time<UNIX_TIMESTAMP("'.$_POST['end_date'].'") +18*3600';

    $order_list = $db->getAll($sql);

    /* 统计每个来源的订单数量 */
    foreach ($order_list as $v)
    {
        foreach ($from_where as $val)
        {
            if($val['from_id'] == $v['order_source'])
            {
                $order_total[$val['from']]['order']++;
                $order_total[$val['from']]['amount'] += $v['final_amount'];
            }
        }
    }

    if ($_POST)
    {
        $smarty->assign('hiddened', 1);
        $smarty->assign('order_total', $order_total);
        die($smarty->fetch('customer_stat.htm'));
    }

    $smarty->assign('ur_here', $_LANG['01_from_where']);

    $smarty->assign('order_total', $order_total);
    $smarty->display('customer_stat.htm');
}

/* 销量统计 */
elseif($_REQUEST['act'] == 'sales_volume')
{
    admin_priv('sales_volume');
    /* ===================================== */
    // 初始化时间
    /* ===================================== */
    date_default_timezone_set('Asia/Shanghai');
    $today = strtotime(date('Y-m-d 18:00:00', time()));

    if ($_POST['month']) $today = strtotime($_POST['month'].'-01 18:00:00');

    $monthdays = date('t', $today);

    $last_day        = $today - 24*3600;
    $before_last_day = $last_day - 24*3600;
    $days_7          = $before_last_day -24*3600*6;
    $month1st        = strtotime(date('Y-m-01', $today)) -6*3600;
    $monthlast       = strtotime(date('Y-m-'.$monthdays.' 18:00:00', $today));

    if (date('H', time()) >= 18)
    {
        $today           += 24*3600;
        $last_day        += 24*3600;
        $before_last_day += 24*3600;
        $days_7          += 24*3600;
    }

    if ($days_7 < $month1st)
    {
        $days_7 = $month1st;
    }

    /* ===================================== */
    // 初始化销售平台信息
    /* ===================================== */
    $sql = 'SELECT role_name, role_id FROM '.$GLOBALS['ecs']->table('role')." WHERE role_type=1";
    $platform = $GLOBALS['db']->getAll($sql);
    $role = array();
    foreach ($platform as $val)
    {
        $role[$val['role_id']] = $val['role_name'];
    }

    // 初始化销售人员信息
    $sql = 'SELECT user_name, user_id FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE stats=1";
    $admin = $GLOBALS['db']->getAll($sql);
    $saler = array ();
    foreach ($admin as $val)
    {
        $saler[$val['user_id']] = $val['user_name'];
    }

    // 输出今天的日期
    if (admin_priv('sales_report', '', false) === false)
    {
        // 初始化销售人员数据
        $sale = array(
            'today'    => array ('number' => 0,'amount' => 0),
            'last_day' => array ('number' => 0,'amount' => 0),
            'month'    => array ('number' => 0,'amount' => 0)
        );

        // 初始化平台数据
        $shop = array(
            'today'    => array ('number' => 0,'amount' => 0),
            'last_day' => array ('number' => 0,'amount' => 0),
            'month'    => array ('number' => 0,'amount' => 0)
        );

        // 每位销售人员的销量
        if (array_key_exists($_SESSION['admin_id'], $saler))
        {
            $sql = 'SELECT IF(SUM(final_amount), SUM(final_amount), 0) amount,COUNT(*) number FROM '.
                $GLOBALS['ecs']->table('order_info').' WHERE pay_status=2 AND '.
                " add_time<=$today AND add_time>$last_day AND admin_id={$_SESSION['admin_id']}";
            $sale['today'] = $GLOBALS['db']->getRow($sql);  // 当天销量 

            $sql = 'SELECT IF(SUM(final_amount),SUM(final_amount),0) amount,COUNT(*) number FROM '.
                $GLOBALS['ecs']->table('order_info').' WHERE pay_status=2 AND '.
                " add_time<=$last_day AND add_time>$before_last_day AND admin_id={$_SESSION['admin_id']}";
            $sale['last_day'] = $GLOBALS['db']->getRow($sql);  // 昨天销量 

            $sql = 'SELECT IF(SUM(final_amount),SUM(final_amount),0) amount,COUNT(*) number FROM '.
                $GLOBALS['ecs']->table('order_info').' WHERE pay_status=2 AND '.
                " add_time<=$monthlast AND add_time>$month1st AND admin_id={$_SESSION['admin_id']}";
            $sale['month'] = $GLOBALS['db']->getRow($sql);  // 当月销量 

            $sale['average'] = 0;
            if ($sale['month']['number'])
            {
                $sale['average'] = round($sale['month']['amount']/$sale['month']['number'],2);
            }

            /* 统计当前客服的顾客人数 */
            $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').
                " WHERE admin_id={$_SESSION['admin_id']}";
            $userNum = $GLOBALS['db']->getOne($sql);

            /* 获取有服务记录的顾客数量 */
            $sql = 'SELECT COUNT(user_id) FROM (SELECT DISTINCT user_id FROM '.
                $GLOBALS['ecs']->table('service')." WHERE admin_id={$_SESSION['admin_id']}) tmp";
            $serviceNum = $GLOBALS['db']->getOne($sql);

            /* 获取有服务记录的顾客数量 */
            $sql = 'SELECT COUNT(user_id) FROM (SELECT DISTINCT user_id FROM '.
                $GLOBALS['ecs']->table('service')." WHERE admin_id={$_SESSION['admin_id']} AND service_time>".
                strtotime(date('Y-m-d', time())).") tmp ";
            $serviceNumToday = $GLOBALS['db']->getOne($sql);

            if ($serviceNum < $userNum)
            {
                $quotient = $userNum ? $serviceNum/$userNum*100 : 0;
                $returningRate = sprintf('%.2f', $quotient);
            }
        }

        // 客服所在平台的销量
        if (array_key_exists($_SESSION['role_id'], $role))
        {
            $sql = 'SELECT IF(SUM(final_amount),SUM(final_amount),0) amount,COUNT(*) number FROM '.
                $GLOBALS['ecs']->table('order_info').' WHERE pay_status=2 AND '.
                " add_time>$last_day AND add_time<=$today AND platform={$_SESSION['role_id']}";
            $shop['today'] = $GLOBALS['db']->getRow($sql);  // 当天销量 

            $sql = 'SELECT IF(SUM(final_amount),SUM(final_amount),0) amount,COUNT(*) number FROM '.
                $GLOBALS['ecs']->table('order_info').' WHERE pay_status=2 AND '.
                " add_time>$before_last_day AND add_time<=$last_day AND platform={$_SESSION['role_id']}";
            $shop['last_day'] = $GLOBALS['db']->getRow($sql);  // 昨天销量 

            $sql = 'SELECT IF(SUM(final_amount),SUM(final_amount),0) amount,COUNT(*) number FROM '.
                $GLOBALS['ecs']->table('order_info').' WHERE pay_status=2 AND '.
                "add_time>$month1st AND add_time<=$monthlast AND platform={$_SESSION['role_id']}";
            $shop['month'] = $GLOBALS['db']->getRow($sql);  // 昨天销量 

            // 获取排名
            $sql = 'SELECT admin_id,SUM(final_amount) amount FROM '.$GLOBALS['ecs']->table('order_info').' WHERE pay_status=2 '.
                " AND platform={$_SESSION['role_id']} AND add_time>$month1st AND add_time<=$monthlast GROUP BY admin_id ORDER BY amount DESC";
            $rank = $GLOBALS['db']->getAll($sql);

            $ranking = array ();
            $i = 0;
            foreach ($rank as $val)
            {
                if ($val['amount'] == $tmp)
                {
                    $ranking[$val['admin_id']] = $tmp_rank;
                }
                else
                {
                    $ranking[$val['admin_id']] = ++$i;
                }
                $tmp_amount = $val['amount'];
                $tmp_rank   = $ranking[$val['admin_id']];
            }
        }

        // 客服id
        $smarty->assign('admin_id', $_SESSION['admin_id']);

        // 客服销售数据
        $smarty->assign('sale', $sale);

        // 客服销售排名
        $smarty->assign('ranking', $ranking);

        // 平台销售数据 
        $smarty->assign('shop', $shop);

        // 客服服务数据
        $smarty->assign('userNum', $userNum);
        $smarty->assign('serviceNum', $serviceNum);
        $smarty->assign('serviceNumToday', $serviceNumToday);
        $smarty->assign('returningRate', $returningRate);

        $smarty->assign('adminName', $_SESSION['admin_name']);

        $smarty->display('start.htm');
        exit;
    }
    else 
    {
        // 初始化固定数组
        $sum = array('today'=>'', 'last'=>'','days_7'=>'','month'=>'');
        $sum_every = array('today'=>'', 'last'=>'','days_7'=>'','month'=>'');

        // 初始化销售平台信息
        $sql = 'SELECT role_name, role_id FROM '.$GLOBALS['ecs']->table('role')." WHERE role_type=1";
        $platform = $GLOBALS['db']->getAll($sql);
        $role = array();
        foreach ($platform as $val)
        {
            $role[$val['role_id']] = $val['role_name'];
        }

        // 初始化销售人员信息
        $sql = 'SELECT user_name, user_id FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE stats=1";
        $admin = $GLOBALS['db']->getAll($sql);
        $saler = array ();
        foreach ($admin as $val)
        {
            $saler[$val['user_id']] = $val['user_name'];
        }

        // 获取当天的销量
        $today_total = statistics($last_day, $today, 'platform'); // 团队
        $today_total_every = statistics($last_day, $today, 'admin_id');  // 个人
        $sum['today'] = calc_times($today_total);
        $sum_every['today'] = calc_times($today_total_every);


        if (date('d', $today) != 1)
        {
            // 获取昨天的销量 
            $last_total = statistics($before_last_day, $last_day, 'platform'); // 团队
            $last_total_every = statistics($before_last_day, $last_day, 'admin_id'); // 个人
            $sum['last'] = calc_times($last_total);
            $sum_every['last'] = calc_times($last_total_every);
        }

            // 获取近7天的销量
            $days_7_total = statistics($days_7, $before_last_day, 'platform');  // 团队
            $days_7_total_every = statistics($days_7, $before_last_day, 'admin_id'); // 个人
            $sum['days_7'] = calc_times($days_7_total);
            $sum_every['days_7'] = calc_times($days_7_total_every);

        // 获取当月的销量
        $month_total = statistics($month1st, $monthlast,'platform'); // 团队
        $month_total_every = statistics($month1st, $monthlast,'admin_id'); // 个人
        $sum['month'] = calc_times($month_total);
        $sum_every['month'] = calc_times($month_total_every);

         /* 查看时间划分
         echo date('Y-m-d H:i:s', $today),'- ', $today, '<br>';
         echo date('Y-m-d H:i:s', $last_day),'- ', $last_day, '<br>';
         echo date('Y-m-d H:i:s', $before_last_day), '- ',$before_last_day, '<br>';
         echo date('Y-m-d H:i:s', $days_7), '- ',$days_7, '<br>';
         echo date('Y-m-d H:i:s', $month1st), '- ',$month1st, '<br>';
         echo date('Y-m-d H:i:s', $monthlast), '- ',$monthlast, '<br>';
          */

        $platform_total = array ();
        foreach ($platform as $val)
        {
            if (isset($month_total[$val['role_id']]))
                $platform_total[$val['role_id']] = $month_total[$val['role_id']]['amount'];
            else $platform_total[$val['role_id']] = 0;
        }

        // 对月销量进行降序排列
        $platform_total = array_flip($platform_total);
        krsort($platform_total);
        $platform_total = array_flip($platform_total);

        // 计算当月客单价
        $average = calc_average($month_total);
        $sum['month']['average'] = round($sum['month']['amount']/$sum['month']['number'], 2);
        $sum['month']['forecast'] = round($sum['month']['amount']/date('d', $today)/10000*$monthdays, 2);

        // 汇总平台销量数据
        $final_total = array();
        foreach ($platform_total as $key=>$val)
        {
            $final_total[$key]['today']    = $today_total[$key];
            $final_total[$key]['last_day'] = $last_total[$key];
            $final_total[$key]['days_7']   = $days_7_total[$key];
            $final_total[$key]['month']    = $month_total[$key];

            $final_total[$key]['month']['average']  = $average[$key];
            $final_total[$key]['month']['forecast']  = round($val/date('d', $today)/10000*$monthdays, 2);
        }

        $saler_total = array ();
        foreach ($admin as $val)
        {
            if (isset($month_total_every[$val['user_id']]))
                $saler_total[$val['user_id']] = $month_total_every[$val['user_id']]['amount'];
            else $saler_total[$val['user_id']] = 0;
        }

        // 对月销量进行降序排列
        $saler_total = array_flip($saler_total);
        krsort($saler_total);
        $saler_total = array_flip($saler_total);

        // 汇总平台销量数据
        $final_total_saler = array();
        foreach ($saler_total as $key=>$val)
        {
            $final_total_saler[$key]['today']    = $today_total_every[$key];
            $final_total_saler[$key]['last_day'] = $last_total_every[$key];
            $final_total_saler[$key]['days_7']   = $days_7_total_every[$key];
            $final_total_saler[$key]['month']    = $month_total_every[$key];
        }


        // 各平台数据输出
        $smarty->assign('total', $final_total);
        $smarty->assign('role', $role);
        $smarty->assign('sum', $sum);

        // 各销售人员数据输出
        $smarty->assign('total_saler', $final_total_saler);
        $smarty->assign('saler', $saler);
        $smarty->assign('sum_every', $sum_every);

        $smarty->assign('date', $_POST['month']);

        $smarty->assign('show', 1);
        $smarty->assign('ur_here', $_LANG['02_sales_volume']);
        $smarty->display('order_stat.htm');
    }
}

/* 销售预测 */
elseif ($_REQUEST['act'] == 'forecast')
{
    /* 统计每位客服的销售额 */
     $res = array ('switch_tag' => true, 'id' => isset($_REQUEST['tag']) ? $_REQUEST['tag'] : 0);
     $take = intval($_POST['take']);
     $time = $_POST['time'];
     $now  = time();

     $start = strtotime('today');
     $end   = $time ? $start +3600*24*$time : strtotime('tomorrow') +3600*24;

     $where = '';
     if ($_SESSION['action_list'] != 'all')
     {
          $where .= " AND add_admin_id={$_SESSION['admin_id']}";
     }

     if ($take || $time)
     {
          if ($time != 'over')
          {
               $sql = "SELECT order_id, user_id, user_name, tel home_phone, mobile mobile_phone, goods_name, goods_number, FROM_UNIXTIME(shipping_time+take_days*goods_number/$take, '%m-%d') coming, admin_name FROM ".$ecs->table('forecast_view')." WHERE shipping_time+take_days*goods_number/$take>$start AND shipping_time+take_days*goods_number/$take<$end AND shipping_time+7*3600*24>service_time AND service_time<$start";
               $sql .= $where;
               $res = $db->getAll($sql);

               $smarty->assign('user_list', $res);
               $res['main'] = $smarty->fetch('forecast_list.htm');
               die($json->encode($res));
          }
          else
          {
               $sql = "SELECT order_id, user_id, user_name, tel home_phone, mobile mobile_phone, goods_name, goods_number, FROM_UNIXTIME(shipping_time+take_days*goods_number/$take, '%m-%d') coming, admin_name FROM ".$ecs->table('forecast_view')." WHERE (shipping_time+take_days*goods_number/$take)<$start AND shipping_time+7*3600*24>service_time AND service_time<$start";
               $sql .= $where;
               $res = $db->getAll($sql);

               $smarty->assign('user_list', $res);
               $res['main'] = $smarty->fetch('forecast_list.htm');
               die($json->encode($res));
          }
     }
     else
     {
          $sql = 'SELECT order_id, user_id, user_name, tel home_phone, mobile mobile_phone, goods_name, goods_number, FROM_UNIXTIME(shipping_time+take_days*goods_number, "%m-%d") coming, admin_name FROM '.$ecs->table('forecast_view')." WHERE shipping_time+take_days*goods_number>$start AND shipping_time+take_days*goods_number<$end AND shipping_time+7*3600*24>service_time AND service_time<$start";
          $sql .= $where;
     }

     $res = $db->getAll($sql);

     $smarty->assign('action',    $_SESSION['action_list']);
     $smarty->assign('user_list', $res);

     $res['main'] = $smarty->fetch('forecast_list.htm');

     die($json->encode($res));
}

/* 重复购买 */
elseif ($_REQUEST['act'] == 'rebuy')
{
     admin_priv('rebuy');
     $admin_list = get_admin('session');
     $smarty->assign('admin_list', $admin_list);

     if ($_SESSION['admin_id'] == 1 || $_SESSION['role'] == 8)
     {
          $role_list = get_role();
          $smarty->assign('role_list', $role_list);
     }

     // 初始化查询条件
     date_default_timezone_set('Aisa/Shanghai');
     $today     = time();
     $monthdays = date('t', $today);

     $start = strtotime(date('Y-m-01', $today)) -6*3600;
     $end   = strtotime(date('Y-m-'.$monthdays.' 18:00:00', $today));

     $team     = '';
     $admin_id = '';
     $buy_num  = 2;

     if (!empty($_POST['start_date']))
     {
          $start = strtotime($_POST['start_date']);
     }

     if (!empty($_POST['end_date']))
     {
          $end = strtotime($_POST['end_date']);
     }

     if (!empty($_POST['team']))
     {
          $team = " AND platform=".intval($_POST['team']);
     }

     if (empty($role_list))
     {
          $team = " AND platform={$_SESSION['role_id']} ";
     }

     if (!empty($_POST['admin_id']))
     {
          $admin_id = " AND admin_id=".intval($_POST['admin_id']);
     }

     if (empty($admin_list))
     {
          $admin_id = " AND admin_id={$_SESSION['admin_id']} ";
     }

     if (!empty($_POST['buy_num']))
     {
          $buy_num = intval($_POST['buy_num']);
     }

     $sql = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('order_info').
          " WHERE final_amount>0 AND order_status=5 AND pay_status=2 ";
     $group_by = " GROUP BY user_id HAVING COUNT(1)>=$buy_num";
     $user_res = $GLOBALS['db']->getAll($sql.$group_by);

     $user_id = array ();
     foreach ($user_res as $val)
     {
          $user_id[] = $val['user_id'];
     }

     $uid = implode(',', $user_id);

     $sql = 'SELECT user_id,consignee,final_amount,admin_name,admin_id,FROM_UNIXTIME(add_time,"%Y-%m-%d") add_time FROM '
          .$GLOBALS['ecs']->table('order_info').' WHERE order_status=5 AND shipping_status>0 AND pay_status=2 AND '.
          "final_amount>0 AND user_id IN ($uid) $team $admin_id AND add_time>$start AND add_time<$end ORDER BY add_time DESC";
     $order_res = $GLOBALS['db']->getAll($sql);

     foreach ($order_res as $val)
     {
          if (!isset($order_list[$val['user_id']]))
          {
               $order_list[$val['user_id']] = $val;
               ++$order_list[$val['user_id']]['times'];
          }
          else
          {
               $order_list[$val['user_id']]['final_amount'] = bcadd($order_list[$val['user_id']]['final_amount'],floatval($val['final_amount']),2);
               $order_list[$val['user_id']]['times'] += 1;
          }

          ++$total_order; 
          $total_order_money = bcadd($total_order_money, $val['final_amount'], 2);
     }

     $smarty->assign('order_list',  $order_list);               // 重复购买的用户列表
     $smarty->assign('total_men',   count($order_list));         // 重复购买的人数
     $smarty->assign('total_order', $total_order);             // 当月重复购买总人次
     $smarty->assign('total_order_money', $total_order_money); // 所有订单总金额

     if ($_POST['start_date'] || $_POST['end_date'] || $_POST['admin_id'] || $_POST['team'] || $_POST)
     {
          /* 按条件查询 */
          $smarty->assign('hiddened', 1);
          echo $smarty->fetch('rebuy.htm');
          exit;
     }

     $smarty->assign('ur_here', $_LANG['04_rebuy']);
     $smarty->display('rebuy.htm');
}

/* 获取用户所有有效订单的详细信息 */
elseif ($_REQUEST['act'] == 'get_order_info')
{
     $user_id = intval($_POST['user_id']);

     if ($user_id)
     {
          /* 读取用户的所有有效订单 */
          $sql = 'SELECT order_id, FROM_UNIXTIME(add_time, "%Y-%m-%d %H:%i:%s") add_time FROM '
               .$GLOBALS['ecs']->table('order_info'). 
               " WHERE order_status=5 AND shipping_status>0 AND pay_status=2 AND final_amount>0
               AND user_id=$user_id ORDER BY order_id DESC";
          $order_res = $GLOBALS['db']->getAll($sql);

          foreach ($order_res as $val) 
          {
               $order_list[$val['order_id']] = $val['add_time'];
          }

          $order_id = implode(',', array_keys($order_list));

          /* 读取所有有效订单中的商品及数量 */
          $sql = 'SELECT goods_name, goods_number, order_id FROM '
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
}



/* 函数区 */

/**
 * 统计函数 
 * @param timestamp  $start     开始时间
 * @param timestamp  $end       结束时间
 * @param int        $group_by  平台ID
 */
function statistics($start,$end,$group_by)
{ 
     // 初始化基本查询语句
     $sql = "SELECT SUM(final_amount) amount,$group_by,COUNT(*) number FROM ".$GLOBALS['ecs']->table('order_info').
          " WHERE pay_status=2 AND add_time>$start AND add_time<=$end GROUP BY $group_by";
     $res = $GLOBALS['db']->getAll($sql);

     $final = array ();
     foreach ($res as $val)
     {
          $final[$val[$group_by]]['amount'] = $val['amount'];
          $final[$val[$group_by]]['number'] = $val['number'];
     }
     unset($res);

     return $final;
}

// 计算每个时间段的全部销量
function calc_times($arr)
{
     if (is_array($arr))
     {
          $sum = array ();
          foreach ($arr as $val)
          {
               $sum['amount'] += $val['amount'];
               $sum['number'] += $val['number'];
          }

          return $sum;
     }
}

// 计算客单价
function calc_average ($arr)
{
     if (is_array($arr))
     {
          $average = array ();
          foreach ($arr as $key=>$val)
          {
               if ($val['number'])
               {
                    $average[$key] = round($val['amount']/$val['number'], 2);
               }
          }

          return $average;
     }
}
