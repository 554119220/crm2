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
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');

$file = basename($_SERVER['PHP_SELF'], '.php');
date_default_timezone_set('Asia/Shanghai');
$nowtime = time();

if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time']))
{
    $smarty->assign('start_time', stamp2date($_REQUEST['start_time'], 'Y-m-d H:i'));
    $smarty->assign('end_time', stamp2date($_REQUEST['end_time'], 'Y-m-d H:i'));
}

/*-- 财务子菜单 --*/
if ($_REQUEST['act'] == 'menu')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);

    die($smarty->fetch('left.htm'));
}

/* 每日订单核对 */
elseif ($_REQUEST['act'] == 'everyday_order_check')
{
    $res = array ();
    if (!admin_priv('everyday_order_check', '', false)) {
        $res = array (
            'timeout' => 2000,
            'req_msg' => true,
            'code'    => 0,
            'message' => '对不起，该帐号没有权限访问！',
        );

        die($json->encode($res));
    }

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false)
    {
        unset($res['left']);
    }

    if (isset($_REQUEST['month']))
    {
        $month   = $_REQUEST['month'];
        $max_day = date('t', strtotime($_REQUEST['month']));

        $_REQUEST['day'] = $_REQUEST['month'].'-01';
    }
    elseif (isset($_REQUEST['day']))
    {
        $month   = date('Y-m', strtotime($_REQUEST['day']));
        $max_day = date('t', strtotime($_REQUEST['day']));
    } else {
        $month   = date('Y-m', $nowtime);
        $max_day = date('t', $nowtime);
    }

    for ($i = 1; $i <= $max_day; $i++) {
        if ($i < 10) {
            $days_list[$month][] = '0'.$i;
        } else {
            $days_list[$month][] = $i;
        }
    }

    $_REQUEST['day'] = isset($_REQUEST['day']) ? $_REQUEST['day'] : date('Y-m-d', $nowtime);

    $order_list = order_list();  // 读取订单列表

    $smarty->assign('day', $_REQUEST['day']);

    $smarty->assign('platform',   get_role_list(1));
    $smarty->assign('brand_list', brand_list());

    // 支付方式
    $sql = 'SELECT pay_id,pay_name FROM '.$GLOBALS['ecs']->table('payment')." WHERE enabled=1 ORDER BY pay_id";
    $pay_list = $GLOBALS['db']->getAll($sql);
    $smarty->assign('pay_list',   $pay_list);

    // 配送方式
    $smarty->assign('shipping_list', shipping_list(3));

    $sch_condition = preg_replace('/&day=.*.\d$/','',$order_list['condition']);

    $smarty->assign('act',           $_REQUEST['act']);
    $smarty->assign('month',         $month);
    $smarty->assign('days_list',     $days_list);
    $smarty->assign('order_list',    $order_list['orders']);
    $smarty->assign('sch_condition', $sch_condition);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);
    $smarty->assign('filter',        $order_list['filter']);

    if ($order_list['filter']['platform']) {
        $smarty->assign('group_list', get_group_list($order_list['filter']['platform']));
        $smarty->assign('admin_list', get_admin_tmp_list($order_list['filter']['platform']));
    }

    if ($order_list['filter']['group_id']) {
        $smarty->assign('admin_list', get_admin_list_by_group($order_list['filter']['group_id']));
    }

    if ($order_list['filter']['brand']) {
        $smarty->assign('goods_list', get_goods_by_brand($order_list['filter']['brand']));
    }

    if (admin_priv('order_print', '', false)) {
        $smarty->assign('access', 1);
    }

    $smarty->assign('curr_title', '每日订单核对');
    $smarty->assign('num', sprintf('（%d笔订单，共%.2f元）', $order_list['record_count'], $order_list['final_amount']));
    $res['main'] = $smarty->fetch('everyday_order_check.htm');

    if (isset($_REQUEST['day']))
    {
        $res['id'] = $_REQUEST['day'];
        $res['switch_tag'] = true;
    }

    die($json->encode($res));
}

//品牌商品联级
elseif ($_REQUEST['act'] == 'get_brand_goods')
{
    if(isset($_REQUEST['brand_id']) && !empty($_REQUEST['brand_id'])){
        $sql_select = 'SELECT goods_id,goods_name FROM '.$GLOBALS['ecs']->table('goods')
            ." WHERE brand_id={$_REQUEST['brand_id']} AND is_delete=0";
        $res['goods'] = $GLOBALS['db']->getAll($sql_select);

        die($json->encode($res));    
    }
}

/**
 * 订单列表
 */
function order_list() {
    $now_time     = time() +8*3600;

    $temp_fields  = '';
    $table_admin  = '';
    $order_status = '';
    $condition    = '';
    $where        = '';
    switch ($_REQUEST['act'])
    {
    case 'everyday_order_check' :
        $table_order = 'order_info';
        $table_user  = 'users';
        $order_status = " AND a.user_id=o.add_admin_id AND o.order_status IN (1,5) AND o.shipping_status IN (0,1,2) ";
        $table_admin = ','.$GLOBALS['ecs']->table('admin_user').' a ';
        $temp_fields = ',o.review,a.user_name add_admin';
        $sort_by = ' o.platform ASC,o.final_amount ASC';
        if (!admin_priv('order_view', '', false)) $order_status .= " AND o.add_admin_id={$_SESSION['admin_id']} ";
        break;
    }

    // 如果是中老年事业部，只列出本部门订单
    if (!admin_priv('all', '', false) && !admin_priv('order_list_all', '', false) && admin_priv('zhln', '', false))
    {
        $order_status .= " AND o.platform=1 ";
    }
    elseif (admin_priv('member', '', false)){
    }
    elseif (!admin_priv('order_list_all', '', false))
    {
        $order_status .= " AND o.platform={$_SESSION['role_id']} ";
    }

    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1)
        {
            $_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
        }

        $filter['consignee']   = empty($_REQUEST['consignee'])   ? '' : trim($_REQUEST['consignee']);
        $filter['admin_id']    = empty($_REQUEST['admin_id'])    ? '' : intval($_REQUEST['admin_id']);
        $filter['address']     = empty($_REQUEST['address'])     ? '' : trim($_REQUEST['address']);
        $filter['zipcode']     = empty($_REQUEST['zipcode'])     ? '' : trim($_REQUEST['zipcode']);
        $filter['tel']         = empty($_REQUEST['tel'])         ? '' : trim($_REQUEST['tel']);
        $filter['mobile']      = empty($_REQUEST['mobile'])      ? 0  : intval($_REQUEST['mobile']);
        $filter['country']     = empty($_REQUEST['country'])     ? 0  : intval($_REQUEST['country']);
        $filter['province']    = empty($_REQUEST['province'])    ? 0  : intval($_REQUEST['province']);
        $filter['city']        = empty($_REQUEST['city'])        ? 0  : intval($_REQUEST['city']);
        $filter['district']    = empty($_REQUEST['district'])    ? 0  : intval($_REQUEST['district']);
        $filter['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0  : intval($_REQUEST['shipping_id']);
        $filter['pay_id']      = empty($_REQUEST['pay_id'])      ? 0  : intval($_REQUEST['pay_id']);
        $filter['platform']    = empty($_REQUEST['platform'])    ? 0  : intval($_REQUEST['platform']);
        $filter['group_id']    = empty($_REQUEST['group_id'])    ? 0  : intval($_REQUEST['group_id']);
        $filter['tracking_sn'] = empty($_REQUEST['tracking_sn']) ? '' : trim($_REQUEST['tracking_sn']);
        $filter['goods_id']    = empty($_REQUEST['goods_id'])    ? 0  : intval($_REQUEST['goods_id']);
        $filter['min_money']   = empty($_REQUEST['min_money'])   ? 0  : trim($_REQUEST['min_money']);
        $filter['max_money']   = empty($_REQUEST['max_money'])   ? 0  : trim($_REQUEST['max_money']);
        $filter['brand']       = empty($_REQUEST['brand'])       ? 0  : intval($_REQUEST['brand']);

        $timestamp = isset($_REQUEST['day']) ? strtotime($_REQUEST['day']) : $now_time;
        $filter['start_time'] = strtotime(date("Y-m-d 00:00:00", $timestamp));
        $filter['end_time']   = strtotime(date("Y-m-d 23:59:59", $timestamp));

        // 订单搜索
        if (! empty($_REQUEST['keywords']))
        {
            $filter['keyfields'] = mysql_real_escape_string(trim($_REQUEST['keyfields']));
            $filter['keywords']  = urldecode(mysql_real_escape_string(trim($_REQUEST['keywords'])));

            $where .= " AND {$filter['keyfields']} LIKE '%{$filter['keywords']}%' ";
        }

        // 收集查询条件
        foreach ($filter as $key=>$val) {
            if ($key == 'end_time') {
                continue;
            }

            if (!empty($val) && $key=='start_time') {
                $condition .= '&day='.date('Y-m-d', $val);
            } elseif (!empty($val)) {
                $condition .= "&$key=$val";
            }
        }

        $filter['page']         = empty($_REQUEST['page'])         ? 1  : intval($_REQUEST['page']);
        $filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;
        $filter['pay_status']   = isset($_REQUEST['pay_status'])   ? intval($_REQUEST['pay_status'])   : -1;

        $filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? intval($_REQUEST['shipping_status']) : -1;

        $filter['sort_by']    = empty($_REQUEST['sort_by'])    ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC'     : trim($_REQUEST['sort_order']);

        // 根据平台查询订单
        if ($filter['platform'] && is_int($filter['platform']))
        {
            $where .= " AND o.platform={$filter['platform']} ";
        }

        // 设置按配送方式查询的条件
        if ($filter['shipping_id'])
        {
            if (strpos($filter['shipping_id'], ','))
            {
                $where .= " AND o.shipping_id NOT IN ({$filter['shipping_id']}) ";
            }
            else 
            {
                $where .= " AND o.shipping_id={$filter['shipping_id']} ";
            }
        }


        // 订单编号查询订单
        if ($filter['order_sn'])
        {
            $where .= " AND o.order_sn LIKE '%".mysql_like_quote($filter['order_sn'])."%'";
        }

        // 运单号查询订单
        if ($filter['tracking_sn'])
        {
            $where .= " AND o.tracking_sn LIKE '%".mysql_like_quote($filter['tracking_sn'])."%'";
        }

        // 收货人查询订单
        if ($filter['consignee'])
        {
            $where .= " AND o.consignee LIKE '%".mysql_like_quote($filter['consignee'])."%'";
        }

        // 收货地址查询订单
        if ($filter['address'])
        {
            $where .= " AND o.address LIKE '%".mysql_like_quote($filter['address'])."%'";
        }

        // 邮编查询订单
        if ($filter['zipcode'])
        {
            $where .= " AND o.zipcode LIKE '%".mysql_like_quote($filter['zipcode'])."%'";
        }

        // 固话查询订单
        if ($filter['tel'])
        {
            $where .= " AND o.tel LIKE '%".mysql_like_quote($filter['tel'])."%'";
        }

        // 手机号码查询订单
        if ($filter['mobile'])
        {
            //$where .= " AND o.mobile LIKE '%".mysql_like_quote($filter['mobile'])."%'";
        }

        // 国家查询订单
        if ($filter['country'])
        {
            $where .= " AND o.country='$filter[country]'";
        }

        // 省份查询订单
        if ($filter['province'])
        {
            $where .= " AND o.province='$filter[province]'";
        }

        // 城市查询订单
        if ($filter['city'])
        {
            $where .= " AND o.city='$filter[city]'";
        }

        // 区县查询订单
        if ($filter['district'])
        {
            $where .= " AND o.district='$filter[district]'";
        }

        // 快递查询订单
        if ($filter['shipping_id'])
        {
            $where .= " AND o.shipping_id='$filter[shipping_id]'";
        }

        // 支付查询订单
        if ($filter['pay_id'])
        {
            $where .= " AND o.pay_id='$filter[pay_id]'";
        }

        // 订单状态查询订单
        if ($filter['order_status'] != -1)
        {
            $where .= " AND o.order_status='$filter[order_status]'";
        }

        // 配送状态查询订单
        if ($filter['shipping_status'] != -1)
        {
            $where .= " AND o.shipping_status='$filter[shipping_status]'";
        }

        // 支付状态查询订单
        if ($filter['pay_status'] != -1)
        {
            $where .= " AND o.pay_status='$filter[pay_status]'";
        }

        // 下单时间
        if ($filter['start_time'] && $filter['end_time'])
        {
            if ($filter['start_time'] > $filter['end_time'])
            {
                $time_tmp = $filter['end_time'];
                $filter['end_time'] = $filter['start_time'];
                $filter['start_time'] = $time_tmp;
            }

            $where .= " AND o.add_time BETWEEN '{$filter['start_time']}' AND '{$filter['end_time']}'";
        }

        //订单金额
        if($filter['min_money'] && $filter['max_money']){
            if($filter['min_money'] > $filter['max_money']){
                $filter['min_money'] = $filter['min_money'] + $filter['max_money'];
                $filter['max_money'] = $filter['min_money'] - $filter['max_money'];
                $filter['min_money'] = $filter['min_money'] - $filter['max_money'];
            }

            $where .= " AND o.final_amount BETWEEN {$filter['min_money']} AND {$filter['max_money']} ";
        }

        //小组
        if($filter['group_id']){
            $where .= " AND o.group_id={$filter['group_id']} ";
        }

        //健康顾问
        if($filter['admin_id']){
            $where .= " AND o.admin_id={$filter['admin_id']} ";
        }

        //品牌名
        if($filter['brand'] && !$filter['goods_id']){
            $table_admin .= ', '.$GLOBALS['ecs']->table('order_goods').' g ';
            $where = " AND o.order_id=g.order_id AND g.brand_id={$filter['brand']} ".$where;
        //包括套餐中的商品
        }

        //商品名
        if($filter['goods_id']){
            $table_admin .= ', '.$GLOBALS['ecs']->table('order_goods').' g ';
            $where = " AND o.order_id=g.order_id AND g.goods_id={$filter['goods_id']} ".$where;
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        else
        {
            $filter['page_size'] = 20; 
        }

        /* 记录总数 */
        $sql_select = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table($table_order).' o,'.
            $GLOBALS['ecs']->table($table_user).' u, '.$GLOBALS['ecs']->table('role').
            " r $table_admin WHERE o.user_id=u.user_id AND r.role_id=o.platform $where $order_status";

        $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);

        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

        // 设置分页
        $page_set = array (1,2,3,4,5,6,7);
        if ($filter['page'] > 4)
        {
            foreach ($page_set as &$val)
            {
                $val += $filter['page'] -4;
            }
        }

        if (end($page_set) > $filter['page_count'])
        {
            $page_set = array ();
            for ($i = 7; $i >= 0; $i--)
            {
                if ($filter['page_count'] - $i > 0)
                {
                    $page_set[] = $filter['page_count'] - $i;
                }
            }
        }

        /* 查询 */
        $sql_select = 'SELECT SUBSTRING(u.user_name FROM 1 FOR 3) buyer,SUBSTRING(o.consignee FROM 1 FOR 4) consignee,u.aliww,u.qq,'.
            ' r.role_name platform,r.role_describe,o.add_time,o.final_amount,o.remarks,o.admin_name,o.order_amount,o.money_paid,o.pay_id,'.
            ' CONCAT(o.order_status,o.shipping_status,o.pay_status) order_status,o.shipping_status, o.order_id, SUBSTR(o.order_sn,-8,8) '.
            ' order_sn,o.tracking_sn,o.shipping_code,r.role_describe,o.order_type '." $temp_fields FROM ".$GLOBALS['ecs']->table($table_order).' o,'.
            $GLOBALS['ecs']->table($table_user).' u, '.$GLOBALS['ecs']->table('role').
            " r $table_admin WHERE o.user_id=u.user_id AND r.role_id=o.platform $where $order_status ".
            " ORDER BY $sort_by LIMIT ".($filter['page'] -1)*$filter['page_size'].",{$filter['page_size']}";

        set_filter($filter, $sql_select);
    }
    else
    {
        $sql_select = $result['sql'];
        $filter     = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql_select);

    $sql_select = 'SELECT type_id,type_name FROM '.$GLOBALS['ecs']->table('order_type').' WHERE available=1';
    $order_type = $GLOBALS['db']->getAll($sql_select);
    $type_list = array ();
    foreach ($order_type as $v)
    {
        $type_list[$v['type_id']] = $v['type_name'];
    }

    /* 格式化数据 */
    foreach ($row as &$val)
    {
        $val['formated_order_amount'] = price_format($val['order_amount']);
        $val['formated_money_paid']   = price_format($val['money_paid']);
        $val['formated_total_fee']    = price_format($val['final_amount']);
        $val['short_order_time']      = date('Y-m-d H:i', $val['add_time']);

        // 订单状态
        switch (@$value['order_status'])
        {
        case 000 : $value['order_status'] = '待确认'; break;
        case 102 : ;
        case 101 : $value['order_status'] = '待发货'; break;
        case 512 : $value['order_status'] = '已发货'; break;
        case 522 : $value['order_status'] = '已收货'; break;
        case 532 : unset($row[$key]); // 已申请退货订单 不显示在该列表中
        default :  $value['order_status'] = '无效订单'; break;
        }

        // 订单类型
        if ($val['order_type'] != 4 && $val['order_type'])
        {
            $val['admin_name'] = $type_list[$val['order_type']];
        }

        // 宅急送code处理
        $val['shipping_code'] = $val['shipping_code'] == 'sto_express' ? 'zjs' :$val['shipping_code'];
        $val['shipping_code'] = $val['shipping_code'] == 'sto_nopay' ? 'zjs' :$val['shipping_code'];
    }

    $sql_select = 'SELECT SUM(final_amount) FROM '.$GLOBALS['ecs']->table('order_info').
        ' WHERE shipping_status IN (0,1,2) AND order_status IN (1,5) AND add_time BETWEEN '.
        " {$filter['start_time']} AND {$filter['end_time']}";
    $arr= array (
        'orders'       => $row,
        'final_amount' => $GLOBALS['db']->getOne($sql_select),
        'filter'       => $filter,
        'page_count'   => $filter['page_count'],
        'record_count' => $filter['record_count'],
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page'],
        'page_set'     => $page_set,
        'condition'    => $condition,
        'start'        => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'          => $filter['page']*$filter['page_size'],
    );

    return $arr;
}
