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

date_default_timezone_set('Asia/Shanghai');
$file = basename($_SERVER['PHP_SELF'], '.php');

/* 保留搜索关键词 */
if (isset($_REQUEST['keywords']) || isset($_REQUEST['start_time']) || isset($_REQUEST['end_time']))
{
    $smarty->assign('kf', $_REQUEST['keyfields']);
    $smarty->assign('kw', urldecode($_REQUEST['keywords']));

    if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
        $smarty->assign('start_time', stamp2date($_REQUEST['start_time'], 'Y-m-d H:i'));
        $smarty->assign('end_time', stamp2date($_REQUEST['end_time'], 'Y-m-d H:i'));
    }
}

/*-- 订单子菜单 --*/
if ($_REQUEST['act'] == 'menu') {
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);

    die($smarty->fetch('left.htm'));
}

/* 顾客被转移后，原订单依然显示 */
elseif ($_REQUEST['act'] == 'order_before_transfer')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) {
        unset($res['left']);
    }

    $order_list = order_list();       // 读取订单列表
    $platform_list = platform_list(); // 销售平台
    if (admin_priv('order_list_all', '', false)) {
        array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
    }

    if (isset($_REQUEST['platform'])) {
        $smarty->assign('platform', $platform);
    }

    $smarty->assign('order_list',    $order_list['orders']);
    $smarty->assign('platform_list', $platform_list);
    $smarty->assign('act',           $_REQUEST['act']);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('platform',  isset($_REQUEST['platform']) ? $_REQUEST['platform']:0);

    if (admin_priv('order_print', '', false)) {
        $smarty->assign('access', 1);
    }

    $smarty->assign('curr_title', '客服订单列表');
    $smarty->assign('num', sprintf('（共%d条）', $order_list['record_count']));
    $res['main'] = $smarty->fetch('finished_order_list.htm');

    if (isset($_REQUEST['platform']))
    {
        $res['id'] = intval($_REQUEST['platform']);
        $res['switch_tag'] = 'true';
    }

    die($json->encode($res));
}

/* 买家已确认收货 */
elseif ($_REQUEST['act'] == 'finished_order') {
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false)
    {
        unset($res['left']);
    }

    $order_list = order_list();  // 读取订单列表
    $platform_list = platform_list();      // 销售平台
    if (admin_priv('order_list_all', '', false))
    {
        array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
    }

    if (isset($_REQUEST['platform']))
    {
        $smarty->assign('platform', $platform);
    }

    $smarty->assign('order_list',      $order_list['orders']);
    $smarty->assign('platform_list',   $platform_list);
    $smarty->assign('act',             $_REQUEST['act']);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('platform',  isset($_REQUEST['platform']) ? $_REQUEST['platform']:0);

    if (admin_priv('order_print', '', false))
    {
        $smarty->assign('access', 1);
    }

    $smarty->assign('curr_title', '已收货订单');
    $smarty->assign('num', sprintf('（共%d条）', $order_list['record_count']));
    $res['main'] = $smarty->fetch('finished_order_list.htm');

    if (isset($_REQUEST['platform']))
    {
        $res['id'] = intval($_REQUEST['platform']);
        $res['switch_tag'] = 'true';
    }

    die($json->encode($res));
}

/* 列出当前订单列表 */
elseif ($_REQUEST['act'] == 'current_order')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) {
        unset($res['left']);
    }

    $order_list = order_list();  // 读取订单列表
    $shipping_list = get_higher_rate_shipping(); // 订单中使用频率最高的5家快递公司
    if (admin_priv('order_list_all', '', false)) {
        @array_unshift($shipping_list, array('shipping_name'=>'全部','shipping_id'=>0));
    }

    if (admin_priv('shipping_act', '', false)) {
        $smarty->assign('shipping_act', 1);
    }

    if (admin_priv('add_memcard', '', false)) {
        $smarty->assign('add_memcard', 1);
    }

    // 将已经列出的快递公司的id组成字符串
    if ($shipping_list) {
        foreach ($shipping_list as $val)
        {
            $other_shipping[] = $val['shipping_id'];
        }
        $other_shipping = implode('|', $other_shipping);

        $smarty->assign('shipping_list',   $shipping_list);
        $smarty->assign('other_shipping',  $other_shipping);
    }

    // 获取订单中所有的商品
    $sql_select = 'SELECT DISTINCT o.goods_kind, g.goods_name FROM '.$GLOBALS['ecs']->table('order_info').' o LEFT JOIN '.
        $GLOBALS['ecs']->table('goods').' g ON g.goods_id=o.goods_kind WHERE o.order_status=1 AND o.shipping_status=0 AND o.goods_kind>0';
    $beshipped_goods_list = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('beshipped_goods_list', $beshipped_goods_list);

    isset($_REQUEST['goods_kind']) && $smarty->assign('beshipped_goods_id', intval($_REQUEST['goods_kind']));

    $smarty->assign('order_list',      $order_list['orders']);
    $smarty->assign('act',             $_REQUEST['act']);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('shipping_id',  isset($_REQUEST['shipping_id']) ? $_REQUEST['shipping_id']:0);

    if (admin_priv('order_print', '', false))
    {
        $smarty->assign('access', 1);
    }

    $smarty->assign('curr_title', '待发货订单');
    $smarty->assign('num', sprintf('（共%d条）', $order_list['record_count']));
    $res['main'] = $smarty->fetch('current_order_list.htm');

    if (isset($_REQUEST['shipping_id']))
    {
        $res['id'] = intval($_REQUEST['shipping_id']);
        $res['switch_tag'] = 'true';
    }
    die($json->encode($res));
}

/* 已发货订单 */
elseif ($_REQUEST['act'] == 'history_order')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    $platform_list = platform_list(); // 列出各个销售平台

    if (admin_priv('order_list_all', '', false))
    {
        $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').
            " WHERE role_name LIKE '%微店%'";
        $wei_store = $GLOBALS['db']->getRow($sql_select);

        if($wei_store){
            array_push($platform_list,array('role_name'=>$wei_store['role_name'],'role_id'=>$wei_store['role_id']));
        }

        array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
        $smarty->assign('sch_all_order',true);
        $smarty->assign('platform_list', $platform_list);
    }

    if (admin_priv('shipping_act', '', false)){
        $smarty->assign('shipping_act', 1);
    }

    // 是否显示确认收货按钮
    if (admin_priv('shipping_done', '', false)) {
        $smarty->assign('shipping_done', 1);
    }

    $_REQUEST['exp_status'] = isset($_REQUEST['exp_status'])?intval($_REQUEST['exp_status']):1;
    $smarty->assign('exp_status', $_REQUEST['exp_status']);
    $order_list = order_list();   // 读取订单列表

    $exp_list = array(
        array('exp_status'=>1, 'exp_info'=>'已发货'),
        array('exp_status'=>0, 'exp_info'=>'在途中'),
        array('exp_status'=>5, 'exp_info'=>'派件中'),
        //array('exp_status'=>3, 'exp_info'=>'已签收'),
        array('exp_status'=>2, 'exp_info'=>'疑难件'),
        array('exp_status'=>4, 'exp_info'=>'已退回'),
        array('exp_status'=>6, 'exp_info'=>'拒收'),
    );

    $smarty->assign('exp_list', $exp_list);
    $smarty->assign('act', 'history_order');

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('order_list', $order_list['orders']);
    $smarty->assign('curr_title', '已发货订单');
    $smarty->assign('platform_list',$platform_list);
    $smarty->assign('num', sprintf('（共%d条）', $order_list['record_count']));
    $smarty->assign('admin_id', $_SESSION['admin_id']);
    $smarty->assign('platform_id', isset($_REQUEST['platform']) ? $_REQUEST['platform'] : 0);

    $res['main'] = $smarty->fetch('sent_order_list.htm');
    $res['id'] = intval($_REQUEST['exp_status']);
    $res['switch_tag'] = 'true';

    die($json->encode($res));
}

/* 列出同步到的订单  全新顾客订单 */
elseif ($_REQUEST['act'] == 'temp_order')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    $order_list    = order_list();        // 获取订单列表
    $platform_list = platform_list();      // 销售平台
    if (admin_priv('order_list_all', '', false)) {
        array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
    }

    if (isset($_REQUEST['platform'])) {
        $smarty->assign('platform', $platform);
    }

    $smarty->assign('act',           $_REQUEST['act']);
    $smarty->assign('order_list',    $order_list['orders']);

    $smarty->assign('platform_list', $platform_list);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('curr_title',    '新顾客订单');
    $smarty->assign('num',           sprintf('（共%d条）', $order_list['record_count']));
    $smarty->assign('admin_id',      $_SESSION['admin_id']);

    $smarty->assign('platform',  isset($_REQUEST['platform']) ? $_REQUEST['platform']:0);

    $res['main'] = $smarty->fetch('order_list.htm');

    if (isset($_REQUEST['platform']))
    {
        $res['id'] = intval($_REQUEST['platform']);
        $res['switch_tag'] = 'true';
    }

    die($json->encode($res));
}

/* 刷单列表 */
elseif ($_REQUEST['act'] == 'flush_order')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    $order_list    = order_list();        // 获取订单列表
    $platform_list = platform_list();      // 销售平台
    if (admin_priv('order_list_all', '', false)) {
        array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
    }

    if (isset($_REQUEST['platform'])) {
        $smarty->assign('platform', $platform);
    }

    $smarty->assign('act',           $_REQUEST['act']);
    $smarty->assign('order_list',    $order_list['orders']);

    $smarty->assign('platform_list', $platform_list);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('curr_title',    '刷单列表');
    $smarty->assign('num',           sprintf('（共%d条）', $order_list['record_count']));
    $smarty->assign('admin_id',      $_SESSION['admin_id']);

    $smarty->assign('platform',  isset($_REQUEST['platform']) ? $_REQUEST['platform']:0);

    $res['main'] = $smarty->fetch('flush_order_list.htm');
    if (isset($_REQUEST['platform'])) {
        $res['id'] = intval($_REQUEST['platform']);
        $res['switch_tag'] = 'true';
    }
    die($json->encode($res));
}
/* 列出同步到的订单  老顾客订单 */
elseif ($_REQUEST['act'] == 'history_users_order') {
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    $order_list    = order_list();        // 读取订单列表
    $platform_list = platform_list();      // 列出各个销售平台
    if (admin_priv('order_list_all', '', false)) {
        array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
    }

    if (isset($_REQUEST['platform'])) {
        $smarty->assign('platform', $platform);
    }

    $smarty->assign('act', 'history_users_order');
    $smarty->assign('platform_list', $platform_list);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('order_list',    $order_list['orders']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('curr_title', '老顾客订单');
    $smarty->assign('num', sprintf('（共%d条）', $order_list['record_count']));
    $smarty->assign('admin_id', $_SESSION['admin_id']);
    $smarty->assign('platform',  isset($_REQUEST['platform']) ? $_REQUEST['platform']:0);

    $res['main'] = $smarty->fetch('order_list.htm');
    if (isset($_REQUEST['platform'])) {
        $res['id'] = $_REQUEST['platform'];
        $res['switch_tag'] = 'true';
    }

    die($json->encode($res));
}

/* 列出异常订单 */
elseif ($_REQUEST['act'] == 'abnormal_list')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    $order_list = abnormal_order_list ();

    $smarty->assign('act', 'abnormal_list');
    $smarty->assign('tag', isset($_REQUEST['tag']) ? $_REQUEST['tag'] : 0);
    $smarty->assign('order_list', $order_list['orders']);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('order_list',    $order_list['orders']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('curr_title', '异常订单');
    $smarty->assign('num', sprintf('（共%d条）', $order_list['record_count']));
    $smarty->assign('admin_id', $_SESSION['admin_id']);

    $res['main'] = $smarty->fetch('abnormal_order_list.htm');

    if (isset($_REQUEST['tag']))
    {
        $res['id'] = $_REQUEST['tag'];
        $res['switch_tag'] = 'true';
    }

    die($json->encode($res));
}

/* 获取订单的详细信息 */
elseif ($_REQUEST['act'] == 'order_detail') {
    $id = intval($_REQUEST['id']);
    $res = array ('response_action' => 'order_detail');

    // 确定要的订单是在临时订单表还是在正式订单表
    if (isset($_REQUEST['current_order'])||isset($_REQUEST['abnormal_list'])||isset($_REQUEST['returned_list'])||isset($_REQUEST['canceled_list'])||isset($_REQUEST['history_order'])||isset($_REQUEST['finished_order'])||isset($_REQUEST['everyday_order_check'])||isset($_REQUEST['order_before_transfer'])) {
        $order_table_name = 'order_info';
        $goods_table_name = 'order_goods';
    } else {
        $order_table_name = 'ordersyn_info';
        $goods_table_name = 'ordersyn_goods';
        $sql_select = 'SELECT order_lock,lock_timeout,admin_id FROM '.
            $GLOBALS['ecs']->table($order_table_name)." WHERE order_id=$id";
        $lock_status = $GLOBALS['db']->getRow($sql_select);
        if (!admin_priv('all', '', false) && !admin_priv('order_group_view', '', false)) {
            // 如果订单锁定者与当前查看人不同
            if ($lock_status['order_lock'] != $_SESSION['admin_id'] && $lock_status['admin_id'] != $_SESSION['admin_id']) {
                $sql_select = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user').
                    " WHERE user_id={$lock_status['order_lock']}";
                $operator = $GLOBALS['db']->getOne($sql_select);
                $msg['response_action'] = 'order_detail';
                $msg['code'] = 0;
                $msg['req_msg'] = true;
                $msg['message'] = $lock_status['order_lock'] ? "动作有点儿慢了哦！$operator 正在处理该订单，请选择其它订单！" : "该订单还未分配，请先去↗获取订单↗吧！";
                $msg['timeout'] = 2000;
                die($json->encode($msg));
            }
        }
    }

    // 读取订单详细信息
    $order_info     = get_order_detail($id, $order_table_name);

    if(!in_array($_SESSION['admin_id'],array(497,4,277,330,554))){
        $order_info['mobile'] = hideContact($order_info['mobile']);
        $order_info['tel'] = hideContact($order_info['tel']);
    }
    $goods_list     = goods_list($id, $goods_table_name);
    $blacklist_info = in_blacklist($id,$order_table_name);

    $order_info['table'] = $order_table_name;

    if($blacklist_info['is_black'] > 0){
        if(admin_priv('all','',false)||admin_priv('order_group_view','',false)||admin_priv('ignore_blacklist','',false)){
            $smarty->assign('ignore_error',true);
        }
        if($blacklist_info['is_black'] == 1){
            $error_msg = sprintf('查询历史记录得知，%s', $blacklist_info['reason']);
        }elseif($blacklist_info['is_black'] == 3){
            $error_msg = '根据记录，此顾客有风险，请通知上级主管进行排查！';
        }elseif($blacklist_info['is_black'] == 4){
            $error_msg = sprintf("此顾客有风险，但已被%s排除警报，可进行订单操作",$blacklist_info['ignore_admin']);
        }
    }

    $smarty->assign('error_msg',$error_msg);
    $smarty->assign('blacklist_info',$blacklist_info);

    // 获取顾客的旺旺或QQ号码
    if (isset($_REQUEST['temp_order'])) {
        $sql_select = "SELECT aliww,qq FROM ".$GLOBALS['ecs']->table('userssyn')." WHERE user_id={$order_info['user_id']}";
    } else {
        $sql_select = "SELECT aliww,qq FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id={$order_info['user_id']}";
    }

    $im = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('im', $im[0]);

    // 获取老顾客类型和顾客归属
    $sql_select = 'SELECT admin_id,eff_id FROM '.$GLOBALS['ecs']->table('users').
        " WHERE user_id={$order_info['user_id']}";
    $user_info = $GLOBALS['db']->getRow($sql_select);
    $smarty->assign('user_info', $user_info);
    $smarty->assign('admin_id', $_SESSION['admin_id']);

    // 月末禁止关闭订单
    if (date('d') > 27 && $_SESSION['role_id'] != 13 && !admin_priv('all', '', false)) {
        $smarty->assign('hide_close', 1);
    } else {
        $smarty->assign('hide_close', 0);
    }

    if (!empty($order_info)) {
        $res['info']['id'] = $id;
        $smarty->assign('order_info', $order_info);  // 订单信息
        $smarty->assign('goods_list', $goods_list);  // 商品列表
        // 订单归属
        $sql_select = 'SELECT user_name, user_id FROM '.$GLOBALS['ecs']->table('admin_user').
            ' WHERE action_list LIKE "%,order_synchro,%" AND status>0 ORDER BY role_id ASC';
        $admin_list_order = $GLOBALS['db']->getAll($sql_select);
        $smarty->assign('admin_list_order', $admin_list_order);
        // 顾客归属
        $sql_select = 'SELECT user_name, user_id FROM '.$GLOBALS['ecs']->table('admin_user').
            ' WHERE action_list LIKE "%,user_synchro,%" AND status>0 ORDER BY role_id ASC';
        $admin_list_user = $GLOBALS['db']->getAll($sql_select);
        $smarty->assign('admin_list_user', $admin_list_user);
        // 订单类型
        $sql_select = 'SELECT type_id, type_name FROM '.$GLOBALS['ecs']->table('order_type').
            ' WHERE available=1 ORDER BY sort DESC';
        $order_type = $GLOBALS['db']->getAll($sql_select);
        $smarty->assign('order_type', $order_type);
        $reason_list   = order_shut_reason(); // 订单关闭原因
        $smarty->assign('reason',        $reason_list);
        // 顾客功效类型
        $sql_select = 'SELECT eff_name, eff_id FROM '.$GLOBALS['ecs']->table('effects').' WHERE available=1 ORDER BY sort DESC';
        $effect_list = $GLOBALS['db']->getAll($sql_select);
        $smarty->assign('effect_list', $effect_list);
        if (admin_priv('shipping_edit', '', false) && isset($_REQUEST['current_order'])) {
            $smarty->assign('shipping_edit', 1);
        }
        $now_time = time();
        if (isset($_REQUEST['everyday_order_check'])||isset($_REQUEST['order_before_transfer'])) {
            $res['info']['info'] = $smarty->fetch('current_order_detail.htm');
            $res['info']['menu'] = '';
        } elseif (isset($_REQUEST['abnormal_list'])) {
            $res['info']['info'] = $smarty->fetch('current_order_detail.htm');
            $res['info']['menu'] = '异常订单';
        } elseif (isset($_REQUEST['returned_list']) || isset($_REQUEST['canceled_list'])) {
            $res['info']['info'] = $smarty->fetch('current_order_detail.htm');
            $res['info']['menu'] = '该订单无效！';
        } elseif ($_SESSION['admin_id']!=$order_info['order_lock'] && $order_info['order_lock']!=0 && $order_info['lock_timeout']>$now_time) {
            // 获取当前正在处理该订单的客服
            $sql_select = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user').
                " WHERE user_id={$order_info['order_lock']}";
            $admin_name = $GLOBALS['db']->getOne($sql_select);
            $res['info']['info'] = $smarty->fetch('current_order_detail.htm');
            $res['info']['menu'] = '客服：<strong>'.$admin_name.
                '</strong>正在处理该订单……<br>处理截止时间：<br>'.
                date('Y-m-d H:i:s', $order_info['lock_timeout']);
        } elseif (isset($_REQUEST['finished_order'])||isset($_REQUEST['current_order'])||isset($_REQUEST['history_order'])) {
            $res['info']['info'] = $smarty->fetch('current_order_detail.htm');
            $res['info']['menu'] = $smarty->fetch('order_returns_menu.htm'); // 退货操作
        } else {
            $res['info']['info'] = $smarty->fetch('order_detail.htm');
            $res['info']['menu'] = $smarty->fetch('order_menu.htm');
        }
    }
    // 查询该顾客最近一天内是否有订单，或是否有订单还未发货
    //$now_time = time();
    /*$sql_select = 'SELECT CONCAT(FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i:%s"),"&nbsp;&nbsp;收货人：",consignee) FROM '.
    $GLOBALS['ecs']->table('order_info')." WHERE user_id={$order_info['user_id']} AND (add_time>=$now_time -24*3600 OR shipping_status=0)";
    $last_order = $GLOBALS['db']->getCol($sql_select);

    $sql_select = 'SELECT CONCAT(FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i:%s"),"&nbsp;&nbsp;收货人：",consignee) FROM '.
    $GLOBALS['ecs']->table('ordersyn_info')." WHERE user_id={$order_info['user_id']} AND (add_time>=$now_time -24*3600 OR shipping_status=0)";
    $last_order += $GLOBALS['db']->getCol($sql_select);
    if (count($last_order) > 1) {
    $res['req_msg'] = true;
    $res['message'] = '该顾客24小时内还有其它订单或未发货订单。订单信息如下：<br>'.implode('<br>', $last_order).'<br><br>提示：<br>多个有效订单且地址及收货人相同，请手动合并！<br><mark>避免重复添加订单！</mark>';
}
     */
    die($json->encode($res));
}

/* 确认订单，进入发货流程 */
elseif ($_REQUEST['act'] == 'ordersyn_verify') {
    $order_id = intval($_REQUEST['id']);

    $res['response_action'] = $_REQUEST['act'];
    $res['code']    = 0;
    $res['req_msg'] = 'true';
    $res['id']      = $order_id;
    $res['timeout'] = 3000;

    // 检查库存是否满足发货所需
    $sql_select = 'SELECT goods_id,goods_sn,goods_name,goods_price,goods_number FROM '.
        $GLOBALS['ecs']->table('ordersyn_goods')." WHERE order_id=$order_id";
    $goods_list = $GLOBALS['db']->getAll($sql_select);
    $enough_storage = enough_storage($goods_list);
    if (!$enough_storage) {
        $res['message'] = sprintf('商品%s缺货%d件/瓶', $enough_storage['goods_name'], $enough_storage['shortage']);
        $res['timeout'] = 2000;
        die($json->encode($res));
    }
    // 检查价格是否合规
    /*
    foreach ($goods_list as $val){
    if (verify_price($val)) {
    $res['message'] = "商品{$val['goods_name']}的价格低于最低售价，请修改商品价格后再提交订单！";
    echo $json->encode($res);
    return;
}
}
     */


    // 验证该订单是否已进入发货流程
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').' i,'
        .$GLOBALS['ecs']->table('ordersyn_info')." si WHERE i.order_sn=si.order_sn AND si.order_id=$order_id";
    $order_exist = $GLOBALS['db']->getOne($sql_select);
    if ($order_exist) {
        $res['message'] = '该订单已经进入发货流程！';
        die($json->encode($res));
    }

    // 验证订单信息是否正确
    $sql_select = 'SELECT final_amount, goods_amount, shipping_fee FROM '.$GLOBALS['ecs']->table('ordersyn_info').
        " WHERE (final_amount<0 OR goods_amount<0 OR shipping_fee<0) AND order_id=$order_id";
    $ng = $GLOBALS['db']->getRow($sql_select);
    if ($ng) {
        $res['timeout'] = 5000;
        if ($ng['final_amount'] < 0) {
            $res['message'] = '订单总金额为负数，请核对！';
        } elseif ($ng['goods_amount'] < 0) {
            $res['message'] = '商品总金额为负数，请核对！';
        } elseif ($ng['shipping_fee'] < 0) {
            $res['message'] = '运费为负数，请核对！';
        }
        die($json->encode($res));
    }

    // 验证商品信息是否正确
    $sql_select = 'SELECT goods_id,goods_sn,goods_name,goods_price,goods_number FROM '.$GLOBALS['ecs']->table('ordersyn_goods')
        .' WHERE (goods_sn=""'." OR goods_price<0 OR goods_number<=0) AND order_id=$order_id";
    $ng = $GLOBALS['db']->getAll($sql_select);
    if ($ng) {
        $res['timeout'] = 5000;
        foreach ($ng as $val) {
            if (empty($val['goods_sn']) || empty($val['goods_name'])) {
                $res['message'] = "商品{$val['goods_name']}信息不完整，请删除该商品并重新添加！";
            } elseif ($val['goods_price'] < 0) {
                $res['message'] = "商品{$val['goods_name']}单价为负值，请删除该商品并重新添加！";
            } elseif (empty($val['goods_number'])) {
                $res['message'] = "商品{$val['goods_name']}数量为负值，请删除该商品并重新添加！";
            }
            die($json->encode($res));
        }
    }

    // 验证该订单是否已经被锁定
    $now_time = time();
    $sql_select = 'SELECT order_lock FROM '.$GLOBALS['ecs']->table('ordersyn_info').
        " WHERE order_id=$order_id AND lock_timeout>$now_time";
    $lock_admin = $GLOBALS['db']->getOne($sql_select);
    if ($lock_admin && $_SESSION['admin_id'] != $lock_admin) {
        $res['response_action'] = $_REQUEST['act'];
        $res['req_msg'] = 'true';
        $res['id']      = $order_id;
        $res['message'] = '该订单已被其他客服锁定！';
        $res['timeout'] = 3000;
        die($json->encode($res));
    }

    $order_admin_id = intval($_REQUEST['order_admin_id']);
    $eff_id         = intval($_REQUEST['eff_id']);
    $order_type     = intval($_REQUEST['order_type']);
    $secrecy        = intval($_REQUEST['secrecy']);

    // 查询当前订单的user_id
    $sql_select = 'SELECT order_sn,user_id,country,province,city,district,address,zipcode FROM '.
        $GLOBALS['ecs']->table('ordersyn_info')." WHERE order_id=$order_id";
    $order_info = $GLOBALS['db']->getRow($sql_select);

    // 验证相同订单编号的订单是否存在
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_sn='{$order_info['order_sn']}'";
    $isExist = $GLOBALS['db']->getOne($sql_select);
    if ($isExist > 0) {
        $res['response_action'] = $_REQUEST['act'];
        $res['req_msg'] = 'true';
        $res['id']      = $order_id;
        $res['message'] = '订单编号已存在，请立即核实！';
        $res['timeout'] = 5000;
        die($json->encode($res));
    }

    $GLOBALS['db']->insert_id();

    // 判断该顾客是否已经存在于正式的顾客表中
    // 此处保留（判断顾客是否已经存在于正式的顾客表中）

    $sql_select = 'SELECT shipping_id FROM '.$GLOBALS['ecs']->table('ordersyn_info')." WHERE order_id=$order_id";
    $shipping_id = $GLOBALS['db']->getOne($sql_select);
    if (!$shipping_id) {
        $res['response_action'] = $_REQUEST['act'];
        $res['req_msg']         = 'true';
        $res['id']              = $order_id;
        $res['message']         = '请选择配送方式！！！';
        $res['timeout']         = 5000;
        die($json->encode($res));
    }

    $sql_select_order_sn = 'SELECT order_sn FROM '.$GLOBALS['ecs']->table('ordersyn_info')." WHERE order_id=$order_id";
    $link_order_sn = $GLOBALS['db']->getOne($sql_select_order_sn);
    // 修改临时顾客表中的订单归属
    if (isset($_REQUEST['user_admin_id'])) {
        $user_admin_id = intval($_REQUEST['user_admin_id']);
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('userssyn').' us, '.
            $GLOBALS['ecs']->table('admin_user')." a SET us.admin_name=a.user_name, ".
            " us.admin_id=$user_admin_id,us.eff_id=$eff_id,us.customer_type=2 ".
            " WHERE us.user_id={$order_info['user_id']} AND a.user_id=$user_admin_id";
        $GLOBALS['db']->query($sql_update);
        // 将顾客从临时顾客表转存至正式顾客表中
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('users').'(user_name,add_time,aliww,qq,home_phone,mobile_phone,'.
            'team,role_id,platform,admin_id,admin_name,customer_type) SELECT user_name,add_time,aliww,qq,home_phone,'.
            'mobile_phone,team,role_id,platform,admin_id,admin_name,customer_type FROM '.$GLOBALS['ecs']->table('userssyn').
            " WHERE user_id={$order_info['user_id']}";
        $GLOBALS['db']->query($sql_insert);
        $user_id = $GLOBALS['db']->insert_id();
        record_operate($sql_insert, 'users');

        // 保存顾客的收货地址
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_address').
            '(user_id,country,province,city,district,address,zipcode)VALUES('.
            "$user_id,'{$order_info['country']}','{$order_info['province']}','{$order_info['city']}',
            '{$order_info['district']}','{$order_info['address']}','{$order_info['zipcode']}')";
        $GLOBALS['db']->query($sql_insert);
        record_operate($sql_insert, 'users_address');  // 记录SQL

        // 更新临时订单表中的顾客ID user_id 订单归属
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').' os, '.
            $GLOBALS['ecs']->table('admin_user').' a SET os.admin_name=a.user_name,os.order_status=1,'.
            " os.add_admin_id={$_SESSION['admin_id']},os.admin_id=$order_admin_id,".
            " os.user_id=$user_id,os.secrecy=$secrecy,os.order_type=$order_type".
            " WHERE a.user_id=$order_admin_id AND os.order_id=$order_id";
        $GLOBALS['db']->query($sql_update);
        record_operate($sql_update, 'ordersyn_info');   // 记录SQL
    } else {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').' os, '.
            $GLOBALS['ecs']->table('admin_user').' a SET os.admin_name=a.user_name,os.order_status=1,'.
            " os.add_admin_id={$_SESSION['admin_id']},os.secrecy=$secrecy,os.order_type=$order_type".
            " WHERE a.user_id=$order_admin_id AND os.order_id=$order_id";
        $GLOBALS['db']->query($sql_update);
        record_operate($sql_update, 'ordersyn_info');   // 记录SQL
        $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('user_contact')." WHERE order_sn='$link_order_sn'";
        if (!$GLOBALS['db']->getOne($sql_select)) {
            $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('ordersyn_info')."  WHERE order_sn='$link_order_sn'";
            $user_id = $GLOBALS['db']->getOne($sql_select);
        }
    }

    // 更新联系信息(user_contact)表中的user_id
    if ($user_id) {
        $sql_update_user_contact = 'UPDATE '.$GLOBALS['ecs']->table('user_contact').
            " SET user_id=$user_id WHERE order_sn='$link_order_sn'";
        $GLOBALS['db']->query($sql_update_user_contact);
    }

    // 将订单数据从临时订单表中转存至正式订单表中
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('order_info').
        '(order_sn,user_id,order_status,pay_status,consignee,country,province,city,district,address,zipcode,
        tel,mobile,best_time,shipping_id,shipping_name,shipping_code,pay_id,pay_name,remarks,to_seller,inv_payee,
        goods_amount,final_amount,shipping_fee,pay_fee,money_paid,add_time,confirm_time,pay_time,shipping_time,is_package,
        inv_no,inv_type,inv_title,add_admin_id,admin_id,group_id,admin_name,team,syn_time,platform,platform_type,operator,
        confirmor,order_type,discount_amount,discount_explain) SELECT order_sn,user_id,order_status,pay_status,
        consignee,country,province,city,district,address,zipcode,tel,mobile,best_time,shipping_id,shipping_name,
        shipping_code,pay_id,pay_name,remarks,to_seller,inv_payee,goods_amount,final_amount,shipping_fee,pay_fee,
        money_paid,add_time,confirm_time,pay_time,shipping_time,is_package,inv_no,inv_type,inv_title,add_admin_id,admin_id,
        group_id,admin_name,team,syn_time,platform,platform_type,operator,confirmor,order_type,discount_amount,discount_explain
        FROM '.$GLOBALS['ecs']->table('ordersyn_info')." WHERE order_id=$order_id";
    if ($GLOBALS['db']->query($sql_insert)) {
        $id = $GLOBALS['db']->insert_id(); // 正式表中的订单ID
        record_operate($sql_insert, 'order_info');// 记录确认操作

        // 更新订单正式表中的订单状态
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').
            " SET order_status=1, pay_status=1 WHERE order_id=$id";
        $GLOBALS['db']->query($sql_update);
        record_operate($sql_update, 'order_info');// 记录更新

        // 更新user_sync中买家账号
        $sql_select = 'SELECT user_id,order_sn FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$id";
        $link_info = $GLOBALS['db']->getRow($sql_select);

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_sync').
            " SET user_id={$link_info['user_id']} WHERE order_sn='{$link_info['order_sn']}'";
        $GLOBALS['db']->query($sql_update);

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_goods').
            " SET temp_order_id=$id WHERE order_id=$order_id";
        $GLOBALS['db']->query($sql_update);
        record_operate($sql_update, 'ordersyn_goods'); // 记录SQL

        // 将订单商品从临时商品表转存至正式商品表中
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('order_goods').
            '(order_id,goods_id,goods_sn,goods_name,goods_price,goods_number,order_sn,is_package,packing_desc,is_gift)'.
            ' SELECT temp_order_id,goods_id,goods_sn,goods_name,goods_price,goods_number,order_sn,is_package,packing_desc,is_gift FROM '.
            $GLOBALS['ecs']->table('ordersyn_goods')." WHERE order_sn='{$order_info['order_sn']}'";
        $GLOBALS['db']->query($sql_insert);
        record_operate($sql_insert, 'order_goods'); // 记录SQL

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_goods').' o,'.$GLOBALS['ecs']->table('goods').
            " g SET o.is_gift=g.is_gift,o.brand_id=g.brand_id WHERE o.goods_id=g.goods_id AND o.order_sn='{$order_info['order_sn']}'";
        $GLOBALS['db']->query($sql_update);
        record_operate($sql_update, 'order_goods'); // 记录SQL

        // 获取订单中的商品种类及数量
        $sql_select = 'SELECT COUNT(DISTINCT goods_sn) kind, SUM(goods_number) goods_num, goods_id FROM '.
            $GLOBALS['ecs']->table('order_goods')." WHERE order_id=$id GROUP BY order_id,goods_sn";
        $goods_info = $GLOBALS['db']->getRow($sql_select);
        if ($goods_info['kind'] > 1) {
            $goods_info['goods_id'] = -1;
        }

        // 将订单中的商品种类及数量补充到订单信息中
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').
            " SET goods_kind={$goods_info['goods_id']},goods_num={$goods_info['goods_num']} WHERE order_id=$id";
        $GLOBALS['db']->query($sql_update);

        // 将临时订单转为正式订单后
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').
            " SET confirmor={$_SESSION['admin_id']}, order_status='-1' WHERE order_id=$order_id";
        $GLOBALS['db']->query($sql_update);
        record_operate ($sql_update, 'ordersyn_info'); // 记录SQL

        $sql_select = 'SELECT order_sn FROM '.$GLOBALS['ecs']->table('ordersyn_info')." WHERE order_id=$order_id AND team=6";
        $order_sn = $GLOBALS['db']->getOne($sql_select);
        if (!empty($order_sn)) {
            require(dirname(__FILE__).'/taobao/order_synchro.php');
            require(dirname(__FILE__).'/taobao/sk.php');
            $auth = require(dirname(__FILE__).'/taobao/config.php');
            $c = new TopClient;
            $c->appkey    = $auth['appkey'];
            $c->secretKey = $auth['secretKey'];
            $req = new TradeMemoUpdateRequest;
            $req->setTid($order_sn);
            $req->setFlag(4);
            $resp = $c->execute($req, $sk['access_token']);
        }
        //按购买商品分类顾客到数据中心
        assignUserToData($id,$goods_list);
        $sql = ' UPDATE '.$GLOBALS['ecs']->table('users')." SET order_time={$_SERVER['REQUEST_TIME']} WHERE user_id={$order_info['user_id']} LIMIT 1";
        $GLOBALS['db']->query($sql);

        $res['response_action'] = $_REQUEST['act'];
        $res['code']    = 1;
        $res['req_msg'] = 'true';
        $res['id']      = $order_id;
        $res['message'] = '订单确认成功！'.$handle_message;
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
}

/* 删除同步到的订单 */
elseif ($_REQUEST['act'] == 'ordersyn_del')
{
    $res['response_action'] = 'ordersyn_del';

    $order_id     = intval($_REQUEST['id']);
    $close_reason = addslashes_deep($_REQUEST['close_reason']);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').
        " SET order_status=-2,add_admin_id={$_SESSION['admin_id']},close_reason='$close_reason' WHERE order_id=$order_id";
    if ($GLOBALS['db']->query($sql_update)) {
        $res['code']    = 1;
        $res['req_msg'] = 'true';
        $res['id']      = $order_id;
        $res['message'] = '订单关闭成功！';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
}

/* 取消待发货中的订单 */
elseif ($_REQUEST['act'] == 'order_del') {
    $res['response_action'] = 'order_del';
    $res['req_msg'] = 'true';
    $res['timeout'] = 2000;
    if (!admin_priv('shipping_act', '', false)){
        $res['message'] = '对不起，您没有取消订单的权限！';
        die($json->encode($res));
    }

    $res['id'] = $order_id  = intval($_REQUEST['id']);
    $close_reason = intval($_REQUEST['close_reason']);

    $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('repeat_purchase')." WHERE order_id=$order_id";
    if (!$GLOBALS['db']->query($sql_delete)) {
        $res['message'] = '订单取消失败！';
        die($json->encode($res));
    }

    $sql_select = 'SELECT shipping_status FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
    $shipping_status = $GLOBALS['db']->getOne($sql_select);
    if ($shipping_status == 1) {
        // 恢复库存数量
        $result = restore_storage($order_id);
        if ($result) {
            $res['message'] = '取消订单时出错，请检查订单后再试！';
            die($json->encode($res));
        }
    } elseif ($shipping_status > 1) {
        $res['message'] = '当前订单不允许取消！';
        die($json->encode($res));
    }

    //取消电子面单
    $sql = 'SELECT order_sn,shipping_code,tracking_sn FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
    $order = $GLOBALS['db']->getAll($sql);
    if ('ztott' == $order['shipping_code']) {
        include_once('taobao/sk.php');
        include_once('taobao/TopSdk.php');
        include_once('taobao/config.php');
        $req = new WlbWaybillICancelRequest;
        $waybill_apply_cancel_request = new WaybillApplyCancelRequest();
        $waybill_apply_cancel_request->real_user_id= $sk['taobao_user_id'];
        $waybill_apply_cancel_request->trade_order_list= $order['order_sn'];
        $waybill_apply_cancel_request->cp_code= strtoupper($order['shipping_code']);
        $waybill_apply_cancel_request->waybill_code= $order['tracking_sn'];
        $waybill_apply_cancel_request->app_key= $auth['appKey'];
        $req->setWaybillApplyCancelRequest(json_encode($waybill_apply_cancel_request));
        $resp = $c->execute($req, $sk['access_token']);
        $resp = json_decode(json_encode($resp),TRUE);
        if (!$resp['wlb_waybill_i_cancel_response']['cancel_result']) {
            $res['message'] = '当前订单的快递单号未能取消成功，请联系技术';
            die($json->encode($res));
        }
    }
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').
        " SET shipping_status=3,close_reason='$close_reason' WHERE order_id=$order_id";
    if ($GLOBALS['db']->query($sql_update)) {
        $res['code'] = 1;
        $res['message'] = '订单取消成功！';
    } else {
        $res['code'] = 0;
        $res['message'] = '订单不符合取消条件！';
    }

    die($json->encode($res));
}

/* 编辑订单 */
elseif ($_REQUEST['act'] == 'edit') {
    $res['response_action'] = 'edit_order';
    $id = intval($_REQUEST['id']); // 订单ID

    // 获取订单状态、支付方式，在修改当前订单时，须考虑订单状态及顾客支付方式
    $sql = 'SELECT pay_id, address FROM '.$GLOBALS['ecs']->table('ordersyn_info')." WHERE order_id=$id";
    $part_info = $GLOBALS['db']->getRow($sql);
    if ($part_info['pay_id'] == 3) {
        $cod = 1;
    }

    // 编辑配送方式
    if ($_REQUEST['info'] == 'shipping') {
        // 获取配送方式列表
        $shipping_list = shipping_list($cod);
        $smarty->assign('shipping', $shipping_list);
    }

    // 修改地址信息
    if ($_REQUEST['info'] == 'district')
    {
        $smarty->assign('province_list', get_regions(1,1));
    }

    // 修改详细地址
    if ($_REQUEST['info'] == 'address') {
        $smarty->assign('address', trim($part_info['address']));
    }

    if ($_REQUEST['type'] == 'input') {
        $sql_select = "SELECT {$_REQUEST['info']} FROM ".$GLOBALS['ecs']->table('ordersyn_info').
            " WHERE order_id=$id";
        $val = $GLOBALS['db']->getOne($sql_select);

        if (!isset($val)) {
            $val = '';
        }

        $smarty->assign($_REQUEST['info'], $val);
    }

    $res['info'] = $_REQUEST['info'];
    $res['id']   = $id;
    $res['act']  = 'edit';
    $res['type'] = $_REQUEST['type'];
    $res['main'] = $smarty->fetch('deliver.htm');

    die($json->encode($res));
}

/* 保存订单信息 */
elseif ($_REQUEST['act'] == 'save') {
    $order_id = intval($_REQUEST['id']); // 订单ID
    $res['id']      = $order_id;
    $res['info']    = $_REQUEST['info'];
    $res['req_msg'] = true;
    $res['timeout'] = 2000;
    if (admin_priv('all', '', false) && !in_array($_SESSION['admin_id'],array(1,4,359,493))) {
        $res['main'] = '<mark>无权限修改！</mark>';
        $res['message'] = '该账号不能修改订单信息！';
        die($json->encode($res));
    }

    // 保存配送方式
    if ($_REQUEST['info'] == 'shipping') {
        $shipping_id = intval($_REQUEST['shipping_id']);
        if (admin_priv('shipping_act', '', false) && !in_array($_SESSION['admin_id'],array(1,4,359,493))){
            $table_name = 'order_info';
            $where = ' AND shipping_status=0 AND order_status=1';
        } else {
            $table_name = 'ordersyn_info';
        }

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table($table_name).' si, '.
            $GLOBALS['ecs']->table('shipping').' s SET si.shipping_code=s.shipping_code, '.
            " si.shipping_id=$shipping_id, si.shipping_name=s.shipping_name ".
            " WHERE order_id=$order_id AND s.shipping_id=$shipping_id $where";
        $GLOBALS['db']->query($sql_update);

        $sql_select = 'SELECT shipping_name FROM '.$GLOBALS['ecs']->table('ordersyn_info').
            " WHERE order_id=$order_id";
        $res['main'] = $GLOBALS['db']->getOne($sql_select);
    }

    // 保存地址信息
    if ($_REQUEST['info'] == 'district') {
        $province = intval($_REQUEST['province']);
        $city     = intval($_REQUEST['city']);
        $district = intval($_REQUEST['district']);

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').
            " SET province=$province, city=$city, district=$district WHERE order_id=$order_id";
        $GLOBALS['db']->query($sql_update);

        $sql_select = 'SELECT CONCAT(p.region_name, c.region_name, d.region_name) region FROM '.
            $GLOBALS['ecs']->table('ordersyn_info').' si LEFT JOIN '.$GLOBALS['ecs']->table('region').
            ' p ON si.province=p.region_id LEFT JOIN '.$GLOBALS['ecs']->table('region').
            ' c ON si.city=c.region_id LEFT JOIN '.$GLOBALS['ecs']->table('region').
            " d ON si.district=d.region_id WHERE order_id=$order_id";
        $res['main'] = $GLOBALS['db']->getOne($sql_select);
    }

    // 处理input输入框数据
    if (isset($_REQUEST['val'])) {
        //收货地址信息
        $val = mysql_real_escape_string(trim($_REQUEST['val']));

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').
            " SET {$_REQUEST['info']}='$val' WHERE order_id=$order_id";
        $GLOBALS['db']->query($sql_update);

        $sql_select = "SELECT {$_REQUEST['info']} FROM ".$GLOBALS['ecs']->table('ordersyn_info').
            " WHERE order_id=$order_id";
        $res['main'] = $GLOBALS['db']->getOne($sql_select);
    }

    // 记录客服操作
    record_operate($sql_update, 'ordersyn_info');

    $res['type']    = strtolower($_REQUEST['type']);
    $res['message'] = '订单修改成功！';

    die($json->encode($res));
}

/* 删除单个商品 */
elseif ($_REQUEST['act'] == 'delete_goods')
{
    // 此处须设置权限验证
    $rec_id = intval($_REQUEST['rec_id']);

    $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('ordersyn_goods')." WHERE rec_id=$rec_id LIMIT 1";
    if ($GLOBALS['db']->query($sql_delete))
    {
        // 记录客服的操作
        record_operate($sql_delete, 'ordersyn_info');

        $res['response_action'] = 'delete_goods';
        $res['code']            = 1;
        $res['req_msg']         = 'true';  // true 要求显示反馈信息 false 不要求显示反馈信息
        $res['obj']             = 'rec_';
        $res['id']              = $rec_id;
        $res['message']         = '商品删除成功！！';
        $res['timeout']         = 2000;
    }
    else
    {
        $res['response_action'] = 'delete_goods';
        $res['code']    = 0;
        $res['req_msg'] = 'true';  // true 要求显示反馈信息 false 不要求显示反馈信息
        $res['message'] = '商品删除失败！！';
        $res['timeout'] = 2000;
    }

    die($json->encode($res));
}

/* 订单锁定与解锁 */
elseif ($_REQUEST['act'] == 'order_lock')
{
    $order_id = intval($_REQUEST['id']); // 订单ID

    $res['id']   = $order_id;
    $res['info'] = 'order_lock';

    // 获取将被锁定的订单的锁定信息
    $sql_select = 'SELECT order_lock, lock_timeout FROM '.$GLOBALS['ecs']->table('ordersyn_info').
        " WHERE order_id=$order_id";
    $lock_status = $GLOBALS['db']->getRow($sql_select);

    // 统计当前客服已经锁定的订单数量
    $now_time = time() +8*3600;
    $sql_select = 'SELECT COUNT(order_lock) FROM '.$GLOBALS['ecs']->table('ordersyn_info').
        " WHERE order_lock={$_SESSION['admin_id']} AND lock_timeout>$now_time";
    $max_lock = $GLOBALS['db']->getOne($sql_select);

    // 该客服锁定的订单数量小于其限额
    if ($max_lock < 5 || $lock_status['order_lock'] == $_SESSION['admin_id'])
    {
        $timeout = $now_time +1800;

        if ($lock_status['order_lock'] == $_SESSION['admin_id'])
        {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').
                " SET order_lock=0, lock_timeout=0 WHERE order_id=$order_id";
        }
        elseif ($lock_status['order_lock'] != $_SESSION['admin_id'] && $now_time>$lock_status['lock_timeout'])
        {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').
                " SET order_lock={$_SESSION['admin_id']}, lock_timeout=$timeout WHERE order_id=$order_id";
        }

        if ($GLOBALS['db']->query($sql_update))
        {
            // 记录客服操作
            record_operate($sql_update, 'ordersyn_info');

            $sql_select = 'SELECT order_lock FROM '.$GLOBALS['ecs']->table('ordersyn_info').
                " WHERE order_id=$order_id";
            $lock_status = $GLOBALS['db']->getOne($sql_select);

            $res['main'] = $lock_status ? '解锁' : '锁定';
            die($json->encode($res));
        }
    }
}

/* 搜索商品 */
elseif ($_REQUEST['act'] == 'search_goods')
{
    $keyword = mysql_real_escape_string(trim($_REQUEST['keyword']));
    if (isset($_REQUEST['order_id'])) {
        $order_id = intval($_REQUEST['order_id']);
    }

    $sql_select = 'SELECT CONCAT(g.goods_name,"  【库存：",SUM(s.quantity),"】") goods_name,g.goods_sn goods_id,SUM(s.quantity) quantity FROM '.
        $GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('stock_goods').
        " s WHERE g.goods_name LIKE '%$keyword%' AND g.is_on_sale=1 AND g.is_delete=0 AND g.goods_sn=s.goods_sn GROUP BY s.goods_sn";
    $goods_list = $GLOBALS['db']->getAll($sql_select);

    if ($order_id) {
        // 获取订单的平台信息
        $sql_select = 'SELECT team FROM '.$GLOBALS['ecs']->table('ordersyn_info')." WHERE order_id=$order_id";
        $platform = $GLOBALS['db']->getOne($sql_select);

        $where = " AND platform=$platform";
    }

    $sql_select = 'SELECT CONCAT(packing_name,"[套餐:", packing_desc,"]") goods_name, packing_desc goods_id FROM '.
        $GLOBALS['ecs']->table('packing')." WHERE packing_name LIKE '%$keyword%' AND is_delete=0";
    $packing_list = $GLOBALS['db']->getAll($sql_select);

    $res['main'] = array_merge($goods_list, $packing_list);

    $res['info'] = 'searchGoods';
    $res['obj']  = 'goods_id';
    die($json->encode($res));
}

/* 添加新订单 */
elseif ($_REQUEST['act'] == 'add_new_order') {
    $res['code']            = 0;
    $res['req_msg']         = 'true';
    $res['response_action'] = $_REQUEST['act'];

    $order_info = stripslashes($_REQUEST['JSON']);  // 过滤转义字符
    $order_info = json_decode($order_info,true);  // 转JSON为Array
    $order_info = addslashes_deep($order_info);

    //收货人的联系方式
    $contact_where = " WHERE user_id={$order_info['user_id']}";
    $sql = 'SELECT mobile_phone,home_phone FROM '.$GLOBALS['ecs']->table('users').$contact_where;
    $default_contact = $GLOBALS['db']->getRow($sql);
    if (strpos($order_info['tel'],'*')>2) {
        $tel = preg_replace("/\*+/",'%',$order_info['tel']);
        $sql = 'SELECT contact_value FROM '.$GLOBALS['ecs']->table('user_contact')."$contact_where AND contact_value LIKE '$tel'";
        $order_info['tel'] = $GLOBALS['db']->getOne($sql);
        if (empty($order_info['tel'])) {
            $order_info['tel'] = $default_contact['home_phone'];
        }
    }
    if (strpos($order_info['mobile'],'*')>2) {
        $phone = preg_replace("/\*+/",'%',$order_info['mobile']);
        $sql = 'SELECT contact_value FROM '.$GLOBALS['ecs']->table('user_contact')."$contact_where AND contact_value LIKE '$phone'";
        $order_info['mobile'] = $GLOBALS['db']->getOne($sql);
        if (empty($order_info['mobile'])) {
            $order_info['mobile'] = $default_contact['mobile_phone'];
        }
    }

    if ($order_info['goods_amount'] < 0) {
        $res['message'] = '商品总金额不能是负数，请修改！';
        $res['timeout'] = 5000;
        die($json->encode($res));
    }
    foreach ($order_info['goods_list'] as $val) {
        if (empty($val['goods_sn'])) {
            $res['message'] = '商品信息不完整，请重新添加该商品！';
            die($json->encode($res));
        } elseif ($val['goods_price'] < 0) {
            $res['message'] = '商品单价不能为负数，请重新添加该商品！';
            die($json->encode($res));
        } elseif (empty($val['goods_number'])) {
            $res['message'] = '商品数量无效，请重新添加该商品！';
            die($json->encode($res));
        } elseif ($order_info['order_type'] != 100 || !admin_priv('employee_order', '', false)) {
            if (verify_price($val, $order_info['team'])) {
                $mem = new Memcache;
                $mem->connect('127.0.0.1', 11211);
                $right_goods = $mem->get("goods_{$val['goods_sn']}");
                $res['message'] = "商品<strong style='color:red'>{$right_goods['goods_name']}</strong>的价格不得低于最低价格<strong style='color:red'>『{$right_goods['cost_price']}』</strong>，请修改商品价格后再提交订单！";
                echo $json->encode($res);
                return;
            }
        }
    }

    if (!$order_info['goods_list']) {
        $res['message'] = '订单中没有任何商品！';
        $res['timeout'] = 2000;
        die($json->encode($res));
    }

    $goods_list = $order_info['goods_list'];
    unset($order_info['goods_list']);

    $enough_storage = enough_storage($goods_list);
    if (!$enough_storage) {
        $res['message'] = sprintf('商品%s缺货%d件/瓶', $enough_storage['goods_name'], $enough_storage['shortage']);
        $res['timeout'] = 2000;
        die($json->encode($res));
    }

    $order_info['order_sn'] = date('ymdHis', time()).mt_rand(00,99);
    $user_id = intval($order_info['user_id']);
    if ($order_info['order_type'] == 99) {
        $shipping_status = " shipping_status=99 AND user_id=$user_id ";
    } else {
        $shipping_status = ' shipping_status IN (0,1,2) ';
    }

    if (!empty($order_info['platform_order_sn'])) {
        $order_info['platform_order_sn'] = trim($order_info['platform_order_sn']);
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('order_info').
            " WHERE $shipping_status AND (order_sn='{$order_info['platform_order_sn']}' OR platform_order_sn='{$order_info['platform_order_sn']}')";
        $order_result = $GLOBALS['db']->getRow($sql_select);
        if ($order_info['order_type'] != 99 && $order_result) {
            $res['message'] = '该订单编号已存在！';
            $res['timeout'] = 2000;
            die($json->encode($res));
        } else if ($order_info['order_type'] == 99 && empty($order_result)) {
            $res['message'] = '顾客信息不一致，无法添加部分退货的订单！';
            $res['timeout'] = 2000;
            die($json->encode($res));
        } elseif ($order_info['order_type'] == 99 && !empty($order_result)) {
            $order_result['goods_amount'] = $order_info['goods_amount'];
            $order_result['shipping_fee'] = $order_info['shipping_fee'];
            $order_result['user_id']      = $order_info['user_id'];
            $order_info = $order_result;
        }
    } else {
        $order_info['platform_order_sn'] = NULL;
    }
    foreach ($goods_list as $val) {
        $value = array_values($val);
        if (is_numeric($val['goods_sn'])) {
            $goods_values[] = '0","'.implode('","', $value);
        } else {
            $goods_values[] = '1","'.implode('","', $value);
        }
    }

    // 计算订单总金额
    $order_info['final_amount'] = bcadd($order_info['goods_amount'], $order_info['shipping_fee'], 2);

    $order_info['order_status'] = 1;
    $order_info['pay_status']   = 1;
    $order_info['add_time']     = time();
    $order_info['confirm_time'] = time();
    $order_info['add_admin_id'] = $_SESSION['admin_id'];
    $order_info['group_id']     = $_SESSION['group_id'];

    // 获取顾客所属平台
    $sql_select = 'SELECT role_id FROM '.$GLOBALS['ecs']->table('users')." WHERE user_id={$order_info['user_id']}";
    $order_info['platform'] = $GLOBALS['db']->getOne($sql_select);
    if (in_array($order_info['platform'], array(13,8))) {
        $order_info['platform'] = $order_info['team'];
    }
    $order_fields = array_keys($order_info);
    $order_fields = '('.implode(',', $order_fields).')VALUES';

    $order_values = array_values($order_info);
    $order_values = '("'.implode('","', $order_values).'")';
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('order_info').$order_fields.$order_values;
    $GLOBALS['db']->query($sql_insert);
    $order_id = $GLOBALS['db']->insert_id();

    if ($goods_values && $order_id) {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').' i,'.$GLOBALS['ecs']->table('admin_user').
            ' u, '.$GLOBALS['ecs']->table('shipping').' s,'.$GLOBALS['ecs']->table('payment').
            ' p SET i.admin_name=u.user_name,i.shipping_code=s.shipping_code,i.shipping_name=s.shipping_name,'.
            'i.pay_name=p.pay_name,i.group_id=u.group_id WHERE i.admin_id=u.user_id AND i.shipping_id=s.shipping_id AND '.
            " i.pay_id=p.pay_id AND i.order_id=$order_id";
        $GLOBALS['db']->query($sql_update);
        $goods_fields = array_keys($goods_list[0]);
        $goods_fields = '(order_id,is_package,'.implode(',', $goods_fields).')VALUES';
        $goods_values = "($order_id,\"".implode("\"),($order_id,\"", $goods_values).'")';
    } else {
        $res['message'] = '订单提交出错，请检查订单中是否存在商品！';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('order_goods').$goods_fields.$goods_values;
    if ($GLOBALS['db']->query($sql_insert))
    {
        foreach ($goods_list as $val)
        {
            if (is_numeric($val['goods_sn']))
            {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_goods').' o, '.
                    $GLOBALS['ecs']->table('goods').' g SET o.goods_name=g.goods_name,o.goods_id=g.goods_id,'.
                    "o.brand_id=g.brand_id WHERE o.goods_sn=g.goods_sn AND o.goods_sn='{$val['goods_sn']}' AND o.order_id=$order_id";
            }
            else
            {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_goods').' o, '.
                    $GLOBALS['ecs']->table('packing').' p SET o.goods_name=p.packing_name'.
                    " WHERE o.goods_sn=p.packing_desc AND o.goods_sn='{$val['goods_sn']}' AND o.order_id=$order_id";
            }
            $GLOBALS['db']->query($sql_update);
        }

        // 统计订单中的商品种类及数量
        $sql_select = 'SELECT COUNT(DISTINCT goods_sn) kind, SUM(goods_number) goods_num, goods_id FROM '.
            $GLOBALS['ecs']->table('order_goods')." WHERE order_id=$order_id GROUP BY order_id,goods_sn";
        $goods_info = $GLOBALS['db']->getRow($sql_select);
        if ($goods_info['kind'] > 1) {
            $goods_info['goods_id'] = -1;
        }

        // 将订单中的商品种类及数量补充到订单信息中
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').
            " SET goods_kind={$goods_info['goods_id']},goods_num={$goods_info['goods_num']} WHERE order_id=$order_id";
        $GLOBALS['db']->query($sql_update);

        /*
        // 处理库存
        $handle_message = '';
        $handle_res = handle_storage($order_id);
        if (!$handle_res['update_status']) {
        $handle_message = sprintf("但【%s】库存不足，暂时无法发货！", $handle_res['error_msg']);
    }
         */

        //大单奖提醒
        if (31<$order_info['team'] && !in_array($order_info['type'],array(1,3))) {
            $sql = ' SELECT r.role_name,o.admin_name,r.role_id FROM '.$GLOBALS['ecs']->table('order_info')
                .' o LEFT JOIN '.$GLOBALS['ecs']->table('role').' r ON o.team=r.role_id'
                ." WHERE order_id=$order_id";
            $info = $GLOBALS['db']->getRow($sql);
            $now = date('H:i');
            if(1500 <= $order_info['final_amount']){
                $notice_type = 4;
                $notice = '爆大单了';
            }elseif(800<$order_info['final_amount']){
                $notice_type = 5;
                $notice = '又出单了';
            }
            if($notice_type){
                $final_amount = intval($order_info['final_amount']).'元';
                $content = <<<EOF
            <P><font class="bao_big_order"></font><font class="final_amount">{$final_amount}</font><p>
            <p>{$now}{$info['role_name']}{$info['admin_name']} {$notice}！</p>
EOF;
                $sql = 'INSERT INTO'.$GLOBALS['ecs']->table('public_notice').
                    '(content,admin_id,title,notice_type,status,remind_time,issue_time,role_id)VALUES('.
                    "'$content',{$_SESSION['admin_id']},'订单贺讯',$notice_type,1,{$_SERVER['REQUEST_TIME']},{$_SERVER['REQUEST_TIME']},{$info['role_id']})";
                $GLOBALS['db']->query($sql);
            }
        }

        $sql = ' UPDATE '.$GLOBALS['ecs']->table('users')." SET order_time={$order_info['add_time']} WHERE user_id={$order_info['user_id']}";
        $GLOBALS['db']->query($sql);

        $res['code'] = 1;
        $res['message'] = '订单添加成功，将进入发货流程！'.$handle_message;
        $res['timeout'] = 3000;

        die($json->encode($res));
    }
}

/* 添加商品到数据库 */
elseif ($_REQUEST['act'] == 'add_goods')
{
    $request = addslashes_deep($_REQUEST);
    $goods_sn = $request['goods_id'];
    $number   = intval($request['number']);
    $price    = floatval($request['price']);
    $order_id = intval($request['order_id']);
    $is_gift  = intval($request['is_gift']);

    $sql_select = 'SELECT COUNT(*) is_exists FROM '.$GLOBALS['ecs']->table('ordersyn_info').
        " WHERE order_id=$order_id AND order_status=0";
    $order_exist = $GLOBALS['db']->getOne($sql_select);
    if ($order_exist)
    {
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('ordersyn_goods').
            '(order_id,goods_sn,goods_number,goods_price,is_gift)VALUES('."$order_id,'$goods_sn',$number,$price,$is_gift)";
        if ($GLOBALS['db']->query($sql_insert))
        {
            // 记录客服操作
            record_operate($sql_insert, 'ordersyn_goods');

            if (is_numeric($goods_sn))
            {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_goods').' sg, '.
                    $GLOBALS['ecs']->table('goods').' g, '.$GLOBALS['ecs']->table('ordersyn_info').
                    ' si SET sg.goods_id=g.goods_id, sg.goods_name=g.goods_name, sg.order_sn=si.order_sn '.
                    " WHERE sg.goods_sn=g.goods_sn AND sg.order_id=si.order_id AND sg.order_id=$order_id";
            }
            else
            {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_goods').' sg, '.
                    $GLOBALS['ecs']->table('packing').' p, '.$GLOBALS['ecs']->table('ordersyn_info').
                    ' si SET sg.goods_name=p.packing_name, sg.order_sn=si.order_sn,sg.is_package=1 '.
                    " WHERE sg.goods_sn=p.packing_desc AND sg.order_id=si.order_id AND sg.order_id=$order_id";
            }

            if ($GLOBALS['db']->query($sql_update))
            {
                // 记录商品信息完善操作
                record_operate($sql_update, 'ordersyn_goods');
            }

            // 重新计算订单商品金额
            $sql_select = 'SELECT SUM(sg.goods_number*sg.goods_price) FROM '.
                $GLOBALS['ecs']->table('ordersyn_info').' si, '.$GLOBALS['ecs']->table('ordersyn_goods').
                " sg WHERE si.order_id=sg.order_id AND sg.order_id=$order_id";
            $goods_amount = $GLOBALS['db']->getOne($sql_select);

            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').' si, '.
                $GLOBALS['ecs']->table('ordersyn_goods').' sg SET '.
                " si.goods_amount=$goods_amount WHERE sg.order_id=$order_id AND si.order_id=sg.order_id";
            if ($GLOBALS['db']->query($sql_update))
            {
                record_operate($sql_update, 'ordersyn_goods'); // 记录操作

                // 重新计算订单总金额
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').
                    " SET final_amount=goods_amount+shipping_fee-discount_amount WHERE order_id=$order_id";
                if ($GLOBALS['db']->query($sql_update))
                {
                    record_operate ($sql_update, 'ordersyn_info');
                }
            }

            // 查询添加后的订单商品列表
            $sql_select = 'SELECT rec_id,goods_name,goods_sn,goods_number,goods_price,is_gift FROM '.
                $GLOBALS['ecs']->table('ordersyn_goods')." WHERE order_id=$order_id";
            $goods_list = $GLOBALS['db']->getAll($sql_select);

            foreach ($goods_list as &$val){
                if (!is_numeric($val['goods_sn'])) {
                    $sql_select = 'SELECT goods_name,num FROM '.$GLOBALS['ecs']->table('packing_goods').' g, '.
                        $GLOBALS['ecs']->table('packing')." p WHERE g.packing_id=p.packing_id AND p.packing_desc='{$val['goods_sn']}'";
                    $val['packing_goods'] = $GLOBALS['db']->getAll($sql_select);
                }
            }

            $smarty->assign('goods_list', $goods_list);
            $res['main'] = $smarty->fetch('deliver.htm');

            // 查询新的订单金额
            $sql_select = 'SELECT final_amount, shipping_fee, goods_amount FROM '.
                $GLOBALS['ecs']->table('ordersyn_info')." WHERE order_id=$order_id";
            $order_info = $GLOBALS['db']->getRow($sql_select);

            $res['goods_amount'] = $order_info['goods_amount'];
            $res['shipping_fee'] = $order_info['shipping_fee'];
            $res['final_amount'] = $order_info['final_amount'];
        }
    }

    die($json->encode($res));
}

/* 审核订单 */
elseif ($_REQUEST['act'] == 'review')
{
    $res['response_action'] = $_REQUEST['act'];

    $res['code']    = 0;
    $res['req_msg'] = 'true';  // true 要求显示反馈信息 false 不要求显示反馈信息
    $res['timeout'] = 2000;

    if (!admin_priv($_REQUEST['act'], '', false))
    {
        $res['message'] = '对不起，您没有进行该操作的权限权限！';
        die($json->encode($res));
    }

    $now_time = time();
    $order_id = intval($_REQUEST['id']);

    if ($order_id)
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').
            " SET review=IF(review,0,1),reviewer={$_SESSION['admin_id']},review_time=$now_time".
            " WHERE order_id=$order_id";
        if ($GLOBALS['db']->query($sql_update))
        {
            $sql_select = 'SELECT review FROM '.$GLOBALS['ecs']->table('order_info').
                " WHERE order_id=$order_id";
            $res['main'] = $GLOBALS['db']->getOne($sql_select);

            $res['id']      = $order_id;
            $res['code']    = 1;
            $res['req_msg'] = false;  // true 要求显示反馈信息 false 不要求显示反馈信息

            die($json->encode($res));
        }
    }
}

/* 感谢信 */
elseif($_REQUEST['act'] == 'thanks_note')
{
    $smarty->assign('user_name', $db->getOne('SELECT consignee FROM '.$ecs->table('order_info').' WHERE order_id='.intval($_GET['order_id'])));
    $smarty->assign('date', date('Y年m月d日', (time())));
    $smarty->display('thanks_note.htm');
}

/* 提交运单号 */
elseif ($_REQUEST['act'] == 'edit_tracking_sn')
{
    //if (!admin_priv('shipments')) die($json->encode(array ('errMsg'=>'非法操作！！！')));
    $order_id = intval($_GET['order_id']);

    $res['response_action'] = $_REQUEST['act'];
    $res['req_msg'] = true;

    // 如果运单号为空，或已存在
    if(!empty($_GET['tracking_sn'])) {
        $tracking_sn = mysql_real_escape_string(trim($_GET['tracking_sn']));
        $sql = 'SELECT order_id FROM '.$ecs->table('order_info').
            " WHERE order_id<>$order_id AND shipping_status NOT IN (3,4) AND tracking_sn='$tracking_sn'";

        if ($db->getOne($sql)) {
            $res['message'] = '该运单号已被使用！！！';
            $res['errMsg']  = 3;
            die($json->encode($res));
        }

        // 验证运单号与快递公司是否一致
        $sql_select = 'SELECT code_regexp FROM '.$GLOBALS['ecs']->table('shipping').' s, '.
            $GLOBALS['ecs']->table('order_info')." i WHERE i.shipping_id=s.shipping_id AND i.order_id=$order_id";
        $regexp = $GLOBALS['db']->getOne($sql_select);

        $sql_select = 'SELECT shipping_id FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
        $shipping_id = $GLOBALS['db']->getOne($sql_select);
        if($shipping_id==39){
            $tracking_sn = "$tracking_sn";
        }

        if ($regexp && !preg_match($regexp, $tracking_sn)) {
            $res['message'] = '您当前输入的运单号与配送公司不相符，请选择相符的运单！';
            $res['errMsg']  = 3;
            die($json->encode($res));
        }
    } else {
        $res['message'] = '请输入正确的运单号！！！';
        $res['errMsg']  = 3;
        die($json->encode($res));
    }

    // 更新订单中的运单号及发货时间
    $sql = "UPDATE ".$ecs->table('order_info')." SET tracking_sn='$tracking_sn',".
        " shipping_time=CURRENT_TIMESTAMP() WHERE order_id=$order_id AND tracking_sn<>'$tracking_sn'";
    if($db->query($sql)) {
        // 将发货信息同步到第三方平台
        $syn_info = shipping_synchro($order_id);
        if (!empty($syn_info['message'])) {
            $res['message'] = $syn_info['message'];
            $res['errMsg'] = 3;
            die($json->encode($res));
        }

        $res['tracking_sn']   = $tracking_sn;
        $res['message']       = '运单号保存成功，发货已同步至网店！';
        $res['timeout']       = 2000;
        $res['errMsg']        = 2;

        die($json->encode($res));
    }
}

/* 打印快递单 */
elseif ($_REQUEST['act'] == 'expressprint') {
    $order_id = intval($_REQUEST['order_id']);
    $sql = 'SELECT IFNULL(platform_order_sn,order_sn) order_sn,user_id,consignee,tel,mobile,LEFT(r.region_name COLLATE utf8_general_ci,2) prov,e.region_name city,g.region_name district,CONCAT(LEFT(r.region_name COLLATE utf8_general_ci,2),"<br>",e.region_name,"<br>",g.region_name) province, CONCAT(LEFT(e.region_name COLLATE utf8_general_ci,2),"<br>",g.region_name) zhixiashi, CONCAT(IFNULL(r.region_name, ""), IFNULL(e.region_name,""), IFNULL(g.region_name,"")) addr, address, zipcode, best_time, shipping_code, shipping_status, shipping_name, tracking_sn, final_amount, remarks, secrecy, team, platform, r.region_id pro,admin_id,add_time FROM '.$GLOBALS['ecs']->table('order_info').' i LEFT JOIN '.$GLOBALS['ecs']->table('region').' r ON r.region_id=i.province LEFT JOIN '.$GLOBALS['ecs']->table('region').' e ON i.city=e.region_id LEFT JOIN '.$GLOBALS['ecs']->table('region').' g ON i.district=g.region_id '."WHERE i.order_id=$order_id";
    $order = $GLOBALS['db']->getRow($sql);

    if (in_array($order['shipping_code'], array('sto_express', 'sto_yhd', 'emssn', 'ems', 'ems2', 'sf', 'sf2', 'yto_no_pay'))) {
        $order['big5'] = nature2zh($order['final_amount']);
    }

    $order['goods_amount'] = price_format($order['final_amount']);
    $res = handle_storage($order_id);
    if (!$res['update_status']) {
        $notice = sprintf("【%s】库存不足，无法发货！", $res['error_msg']);
        sys_msg($notice);
    }

    $now_time = time();
    //中通快递
    //if ($order['shipping_code'] == 'zto' && in_array($order['team'], array(6,21,22,26))) {
    if ($order['shipping_code'] == 'ztott') {
        $zto      = shipping_zto($order);;
        $get_resp = $zto['get_resp'];
        $smarty->assign('now_time',date('Y-m-d',$now_time));
        $smarty->assign('get_resp',$get_resp);
        $smarty->display('zto.htm');
        exit;
    }


    /* 判断用户的联系方式是否为手机 */
    /*{
    if (is_mobile($order['mobile']) && $order['shipping_status'] === 0)
    {
    // 发送短信通知顾客商品已送出
    $msg = sprintf('尊敬的%s，您在康健人生订购的产品已通过%s发出，单号为%s，预计2~5天送达，请注意查收！', $order['consignee'], $order['shipping_name'], $order['tracking_sn']);
    require_once(ROOT_PATH . 'includes/cls_sms.php');
    $sms = new sms();
    $sms->send($order['mobile'], $msg);
}
     */
    // 将发货信息同步到第三方平台
/*
$res = shipping_synchro($order_id);
file_put_contents('shipping_message.txt', $res['message']."\r\n", FILE_APPEND);
if (!empty($res['message'])) {
exit($res['message']);
}
}
 */

    $db->query('UPDATE '.$ecs->table('order_info')." SET order_status=5,shipping_status=1,pay_status=2,shipping_time=UNIX_TIMESTAMP(),shipping_print_times=shipping_print_times+1 WHERE order_id='$order_id'");

/* {
// 获取会员部所有客服的user_id及user_name
$sql_select = 'SELECT user_id FROM '.$ecs->table('admin_user').
' WHERE role_id=9 AND status=1 AND stats=1 AND password<>1 AND counter>=0 ORDER BY counter ASC LIMIT 1';
$admin_id = $db->getOne($sql_select);

$sql_select = 'SELECT GROUP_CONCAT(user_id) FROM '.$ecs->table('admin_user').
' WHERE role_id IN (13) AND status=1 AND stats=1 AND password<>1';
$admin_list = $db->getOne($sql_select);

$sql_update = 'UPDATE '.$ecs->table('users').' u,'.$GLOBALS['ecs']->table('admin_user').
" a SET u.admin_id=$admin_id,u.admin_name=a.user_name,u.assign_time=".time().
', u.role_id=a.role_id, a.counter=a.counter+1 WHERE u.admin_id IN ('.
"$admin_list) AND u.user_id={$order['user_id']} AND a.user_id=$admin_id";
file_put_contents('shipping_done.txt', $sql_update."\r\n", FILE_APPEND);
$db->query($sql_update);
} */

    // 查询该次购买是否已经添加了记录
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('repeat_purchase')." WHERE order_id={$order_id}";
    $isExistRep = $GLOBALS['db']->getRow($sql_select);
    if (empty($isExistRep)) {
        // 统计当前订单是该顾客第几次购买
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').
            " WHERE order_status=5 AND shipping_status IN (1,2) AND user_id={$order['user_id']}";
        $ordinal_number = $GLOBALS['db']->getOne($sql_select);

        // 将购买记录添加至重复购买表中
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('repeat_purchase').
            '(user_id, order_id, ordinal_number, final_amount, admin_id, buy_time, team, platform)VALUES('.
            "{$order['user_id']},{$order_id},$ordinal_number,{$order['final_amount']},{$order['admin_id']},{$order['add_time']},{$order['team']},{$order['platform']})";
        $GLOBALS['db']->query($sql_insert);
    }

    list($date['year'], $date['month'], $date['day']) = explode('-', date('Y-m-d', $now_time));
    $smarty->assign('date', $date);
    $smarty->assign('order', $order);

    if (!isset($_REQUEST['h'])) {
        $smarty->assign('sender', "康健人生{$_SESSION['admin_id']}");
        $smarty->assign('kjrs_tel', '020-66212023');
        $smarty->assign('kjrs_region', '广东省 广州市 白云区');
        $smarty->assign('kjrs_address', '梅花园 麦华路24号大院');
        $smarty->assign('company_name', '广州康健人生健康商城');

        $prov = preg_replace('/^黑龙/', '黑龙江', $order['prov']);
        $prov = preg_replace('/^内蒙/', '内蒙古', $order['prov']);

        $smarty->assign('region',  "$prov<wbr>{$order['city']}<wbr>{$order['district']}");
        $smarty->assign('prov',    $prov);
        $smarty->assign('city',    $order['city']);
        $smarty->assign('dist',    $order['district']);
    }

    $smarty->assign('notice',         $order['best_time']);
    $smarty->assign('message',        $res['message']);
    $smarty->assign('shipping_code',  $order['shipping_code']);

    $smarty->display('shipping_'.strtolower($order['shipping_code']).'.htm');
}

/*------------------------------------------------------ */
//-- 订单详情页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'info') {
    /* 检查权限 */
    /* 根据订单id或订单号查询订单信息 */
    if (isset($_REQUEST['order_id'])) {
        $order_id = intval($_REQUEST['order_id']);
        $order = order_info($order_id);
    } elseif (isset($_REQUEST['order_sn'])) {
        $order_sn = trim($_REQUEST['order_sn']);
        $order = order_info(0, $order_sn);
    } else {
        /* 如果参数不存在，退出 */
        die('invalid parameter');
    }

    /* 如果订单不存在，退出 */
    if (empty($order)) {
        die('order does not exist');
    }

    /* 根据订单是否完成检查权限 */
    /*if (order_finished($order))
    {
    admin_priv('order_view_finished');
}
else
{
admin_priv('order_view');
    }*/

    /* 取得上一个、下一个订单号 */
    if (!empty($_COOKIE['ECSCP']['lastfilter'])) {
        $filter = unserialize(urldecode($_COOKIE['ECSCP']['lastfilter']));
        if (!empty($filter['composite_status'])) {
            $where = '';
            //综合状态
            switch($filter['composite_status']) {
            case CS_AWAIT_PAY :
                $where .= order_query_sql('await_pay');
                break;

            case CS_AWAIT_SHIP :
                $where .= order_query_sql('await_ship');
                break;

            case CS_FINISHED :
                $where .= order_query_sql('finished');
                break;

            default:
                if ($filter['composite_status'] != -1) {
                    $where .= " AND o.order_status = '$filter[composite_status]' ";
                }
            }
        }
    }

    $sql = "SELECT MAX(order_id) FROM ".$ecs->table('order_info')." as o WHERE order_id<'$order[order_id]'";
    if ($agency_id > 0) {
        $sql .= " AND agency_id='$agency_id'";
    }

    if (!empty($where)) {
        $sql .= $where;
    }

    $smarty->assign('prev_id', $db->getOne($sql));
    $sql = "SELECT MIN(order_id) FROM ".$ecs->table('order_info')." as o WHERE order_id>'$order[order_id]'";
    if ($agency_id > 0) {
        $sql .= " AND agency_id='$agency_id'";
    }

    if (!empty($where)) {
        $sql .= $where;
    }

    $smarty->assign('next_id', $db->getOne($sql));

    /* 取得用户名 */
    if ($order['user_id'] > 0) {
        $user = user_info($order['user_id']);
        if (!empty($user)) {
            $order['user_name'] = $user['user_name'];
        }
    }

    /* 取得所有办事处 */
    $sql = "SELECT agency_id, agency_name FROM ".$ecs->table('agency');
    $smarty->assign('agency_list', $db->getAll($sql));

    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(p.region_name,''), "
        . " IFNULL(t.region_name,''), IFNULL(d.region_name,'')) AS region ".
        " FROM " . $ecs->table('order_info') . " AS o "  .
        "LEFT JOIN ".$ecs->table('region') . " AS p ON o.province = p.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS t ON o.city = t.region_id " .
        "LEFT JOIN ".$ecs->table('region') . " AS d ON o.district = d.region_id " .
        "WHERE o.order_id = '$order[order_id]'";
    $order['region'] = $db->getOne($sql);

    /* 格式化金额 */
    if ($order['order_amount'] < 0) {
        $order['money_refund']          = abs($order['order_amount']);
        $order['formated_money_refund'] = price_format(abs($order['order_amount']));
    }

    /* 其他处理 */
    $order['order_time']    = date($_CFG['time_format'], $order['add_time']);
    $order['pay_time']      = $order['pay_time'] > 0 ?
        date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][PS_UNPAYED];
    $order['shipping_time'] = $order['shipping_time'] > 0 ?
        date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][SS_UNSHIPPED];
    $order['status']        = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
    $order['invoice_no']    = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];

    /* 取得订单的来源 */
    $order['referer'] = $_SESSION['admin_name'];

    /* 此订单的发货备注(此订单的最后一条操作记录) */
    $sql = "SELECT action_note FROM " . $ecs->table('order_action').
        " WHERE order_id = '$order[order_id]' AND shipping_status = 1 ORDER BY log_time DESC";
    $order['invoice_note'] = $db->getOne($sql);

    /* 取得订单商品总重量 */
    $weight_price = order_weight_price($order['order_id']);
    $order['total_weight'] = $weight_price['formated_weight'];

    /* 取得顾客的旺旺号 */
    $sql = 'SELECT aliww FROM '.$GLOBALS['ecs']->table('users')." WHERE user_id={$order['user_id']}";
    $order['aliww'] = $GLOBALS['db']->getOne($sql);

    /* 参数赋值：订单 */
    $smarty->assign('order', $order);

    /* 取得用户信息 */
    if ($order['user_id'] > 0) {
        /* 用户等级 */
        if ($user['user_rank'] > 0) {
            $where = " WHERE rank_id = '$user[user_rank]' ";
        } else {
            $where = " WHERE min_points<=" . intval($user['rank_points']) . " ORDER BY min_points DESC ";
        }

        $sql = "SELECT rank_name FROM " . $ecs->table('user_rank') . $where;
        $user['rank_name'] = $db->getOne($sql);

        // 用户红包数量
        $day   = getdate();
        $today = mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        $sql = "SELECT COUNT(*) FROM ".$ecs->table('bonus_type')." AS bt, ".$ecs->table('user_bonus')." AS ub ".
            "WHERE bt.type_id = ub.bonus_type_id " .
            "AND ub.user_id = '$order[user_id]' " .
            "AND ub.order_id = 0 " .
            "AND bt.use_start_date <= '$today' " .
            "AND bt.use_end_date >= '$today'";
        $user['bonus_count'] = $db->getOne($sql);
        $smarty->assign('user', $user);

        // 地址信息
        $sql = "SELECT * FROM ".$ecs->table('user_address')." WHERE user_id='$order[user_id]'";
        $smarty->assign('address_list', $db->getAll($sql));
    }

    /* 取得订单商品及货品 */
    $goods_attr = array();
    $goods_list = array();
    $sql = "SELECT * FROM ".$ecs->table('order_goods').
        " WHERE order_id='{$order['order_id']}' ORDER BY is_package ASC";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res)) {
        /* 虚拟商品支持 */
        if ($row['is_real'] == 0) {
            /* 取得语言项 */
            $filename = ROOT_PATH.'plugins/'.$row['extension_code'].'/languages/common_'.$_CFG['lang'].'.php';
            if (file_exists($filename)) {
                include_once($filename);
                if (!empty($_LANG[$row['extension_code'].'_link'])) {
                    $row['goods_name'] = $row['goods_name'].sprintf($_LANG[$row['extension_code'].'_link'],$row['goods_id'],$order['order_sn']);
                }
            }
        }

        $row['formated_subtotal'] = sprintf('%.2f', $row['goods_price']*$row['goods_number']);
        $row['formated_goods_price'] = intval($row['goods_price']) ? $row['goods_price'] : '赠品';

        $goods_attr[] = explode(' ', trim($row['goods_attr']));//将商品属性拆分为一个数组

        if ($row['is_package'] == 1) {
            $row['package_goods_list'] = get_packing($row['goods_sn']);
            $sql_select = 'SELECT packing_price shop_price FROM '.$ecs->table('packing').
                " WHERE packing_desc='{$row['goods_sn']}'";
            $row['formated_shop_price'] = sprintf('%.2f', $db->getOne($sql_select));
        } else {
            $sql_select = 'SELECT shop_price FROM '.$ecs->table('goods')." WHERE goods_sn='{$row['goods_sn']}'";
            $row['formated_shop_price'] = $db->getOne($sql_select);
            $row['formated_goods_price'] = intval($row['goods_price'])?$row['goods_price']:'赠送';
            $row['formated_subtotal'] = sprintf('%.2f', $row['goods_price']*$row['goods_number']);
        }
        $goods_list[] = $row;
    }

    $attr = array();
    $arr  = array();
    foreach ($goods_attr AS $index => $array_val) {
        foreach ($array_val AS $value) {
            $arr = explode(':', $value);//以 : 号将属性拆开
            $attr[$index][] = @array('name' => $arr[0], 'value' => $arr[1]);
        }
    }

    $smarty->assign('goods_attr', $attr);
    foreach ($goods_list as &$val) {
        $sql ='SELECT * FROM '.$GLOBALS['ecs']->table('freepostalcard_type').
            " WHERE free_goods_sn='{$val['goods_sn']}'";
        $postal = $GLOBALS['db']->getRow($sql);

        // 获取今天的日期
        date_default_timezone_set('Asia/Shanghai');
        $nowdate = date('Y-m-d');

        if (is_array($postal) && count($postal)) {
            //获取$admin_id所属平台
            $sql = 'SELECT r.role_describe role, i.user_id FROM '.
                $GLOBALS['ecs']->table('order_info').' i, '.
                $GLOBALS['ecs']->table('users').' u, '.
                $GLOBALS['ecs']->table('admin_user').' a, '.
                $GLOBALS['ecs']->table('role').
                " r WHERE i.user_id=u.user_id AND u.admin_id=a.user_id AND a.role_id=r.role_id AND i.order_id={$val['order_id']}";
            $role = $GLOBALS['db']->getRow($sql);

            // 判断该顾客的包邮卡是否已经生成
            $sql = 'SELECT freecard_num, free_limit, buy_date, effective_date effective FROM '.
                $GLOBALS['ecs']->table('free_postal_card').
                " WHERE user_id={$role['user_id']} AND free_goods_sn='{$val['goods_sn']}' ".
                " AND free_platform IN ({$postal['free_platform']})";
            $isExist = $GLOBALS['db']->getRow($sql);

            // 计算包邮卡的有效期
            $effective = get_deadline($postal['effective_date'], $nowdate);

            // 如果该顾客没有包邮卡存在，
            // 或者包邮卡剩余使用次数为0
            // 或者包邮卡已过期
            // 则直接添加包邮卡到顾客列表
            if (empty($isExist)) {
                // 生成包邮卡卡号
                $freecard_num = generate_card_number($role);

                $val['goods_name'] .= "（卡号：$freecard_num ）";

                $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('free_postal_card').
                    '(user_id, freecard_num, effective_date, free_limit, free_platform, free_goods_sn, buy_date, order_id)VALUES('.
                    "{$role['user_id']}, '".strtr($freecard_num, array(' '=>'')).
                    "', '$effective', {$postal['free_limit']}, '{$postal['free_platform']}', '{$postal['free_goods_sn']}', '$nowdate', {$order['order_id']})";
                $GLOBALS['db']->query($sql);
                $smarty->assign('effective', $effective.'前有效 ');
            }
            // 如果已经有包邮卡存在
            else {
                $sql = 'UPDATE '.$GLOBALS['ecs']->table('free_postal_card').
                    " SET effective_date='$effective', order_id={$order['order_id']} ".
                    " WHERE user_id={$role['user_id']} AND free_goods_sn='{$postal['free_goods_sn']}'".
                    " AND order_id<>{$order['order_id']}";
                $GLOBALS['db']->query($sql);

                if ($GLOBALS['db']->affected_rows()) {
                    $smarty->assign('effective', '有效期延长至<font color="red">'.$effective.'</font>&nbsp;');
                }
                $freecard_num = trim(chunk_split(' '.$isExist['freecard_num'], 4));
                $val['goods_name'] .= "（卡号：$freecard_num ）";
            }
        }
    }

    $smarty->assign('goods_list', $goods_list);

    /* 取得能执行的操作列表 */
    $operable_list = operable_list($order);
    $smarty->assign('operable_list', $operable_list);

    /* 取得订单操作记录 */
    $act_list = array();
    $sql = "SELECT * FROM ".$ecs->table('order_action')." WHERE order_id='$order[order_id]' ORDER BY log_time DESC,action_id DESC";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res)) {
        $row['order_status']    = $_LANG['os'][$row['order_status']];
        $row['pay_status']      = $_LANG['ps'][$row['pay_status']];
        $row['shipping_status'] = $_LANG['ss'][$row['shipping_status']];
        $row['action_time']     = date($_CFG['time_format'], $row['log_time']);
        $act_list[] = $row;
    }
    $smarty->assign('action_list', $act_list);

    /* 取得是否存在实体商品 */
    $smarty->assign('exist_real_goods', exist_real_goods($order['order_id']));

    $sql = 'SELECT COUNT(user_id) FROM '.$ecs->table('order_info')." WHERE user_id={$order['user_id']} GROUP BY user_id HAVING COUNT(user_id)";
    if($db->getOne($sql) > 1) {
        $smarty->assign('old_buyer', '◆');
    }

    $sql_select = 'SELECT card_number FROM '.$GLOBALS['ecs']->table('memship_number')." WHERE user_id={$order['user_id']}";
    $card_number = $GLOBALS['db']->getOne($sql_select);

    /* 是否打印订单，分别赋值 */
    if (isset($_GET['print'])) {
        $smarty->assign('shop_name',    $_CFG['shop_name']);
        $smarty->assign('shop_url',     $ecs->url());
        $smarty->assign('shop_address', $_CFG['shop_address']);
        $smarty->assign('service_phone',$_CFG['service_phone']);
        $smarty->assign('print_time',   strstr($order['order_time'], ' ', true));
        $smarty->assign('action_user',  $_SESSION['admin_name']);
        $smarty->assign('card_number',  $card_number);

        //$sql = 'SELECT r.role_describe,a.user_name,u.phone FROM '.$ecs->table('role').
        $sql = 'SELECT r.role_describe,a.user_name,a.phone FROM '.$ecs->table('role').
            ' r, '.$ecs->table('order_info').' i,'.$ecs->table('admin_user').
            " a WHERE a.user_id=i.admin_id AND r.role_id=i.platform AND i.order_id={$order['order_id']}";
        $dietitian = $db->getRow($sql);
        $smarty->assign('role', $dietitian['role_describe']);
        $smarty->assign('dietitian', $dietitian);

        $sql = 'UPDATE '.$ecs->table('order_info').' SET order_print_times=order_print_times+1'.
            " WHERE order_id={$order['order_id']}";
        $db->query($sql);

        $smarty->template_dir = '../' . DATA_DIR;
        if (isset($_GET['print_A4'])) {
            $stamp = '';
            for ($i = 0; $i < 15; $i++) {
                $stamp .= mt_rand(0,9);
            }
            $smarty->assign('stamp', $stamp.time());
            $smarty->assign('operator', $_SESSION['admin_id']);
            $smarty->display('order_print_A4.htm');
            exit;
        }
        if (in_array($role, array('K1','K2','K3','K4'))) {
            $smarty->assign('notice', false);
        }

        $smarty->display('order_print.htm');
    }

    /* 打印快递单 */
    elseif (isset($_GET['shipping_print'])) {
        //$smarty->assign('print_time',   date($_CFG['time_format']));
        //发货地址所在地
        $region_array = array();
        $region_id = !empty($_CFG['shop_country']) ? $_CFG['shop_country'] . ',' : '';
        $region_id .= !empty($_CFG['shop_province']) ? $_CFG['shop_province'] . ',' : '';
        $region_id .= !empty($_CFG['shop_city']) ? $_CFG['shop_city'] . ',' : '';
        $region_id = substr($region_id, 0, -1);
        $region = $db->getAll("SELECT region_id, region_name FROM " . $ecs->table("region") . " WHERE region_id IN ($region_id)");
        if (!empty($region)) {
            foreach($region as $region_data) {
                $region_array[$region_data['region_id']] = $region_data['region_name'];
            }
        }

        $smarty->assign('shop_name', $_CFG['shop_name']);
        $smarty->assign('order_id',  $order_id);
        $smarty->assign('province',  $region_array[$_CFG['shop_province']]);
        $smarty->assign('city',      $region_array[$_CFG['shop_city']]);
        $smarty->assign('shop_address', $_CFG['shop_address']);
        $smarty->assign('service_phone',$_CFG['service_phone']);
        $shipping = $db->getRow("SELECT * FROM " . $ecs->table("shipping") . " WHERE shipping_id = " . $order['shipping_id']);

        //打印订单模式
        if ($shipping['print_model'] == 2) {
            /* 可视化 */
            /* 快递单 */
            $shipping['print_bg'] = empty($shipping['print_bg']) ? '' : get_site_root_url() . $shipping['print_bg'];

            /* 取快递单背景宽高 */
            if (!empty($shipping['print_bg'])) {
                $_size = @getimagesize($shipping['print_bg']);

                if ($_size != false) {
                    $shipping['print_bg_size'] = array('width' => $_size[0], 'height' => $_size[1]);
                }
            }

            if (empty($shipping['print_bg_size'])) {
                $shipping['print_bg_size'] = array('width' => '1024', 'height' => '600');
            }

            /* 标签信息 */
            $lable_box                        = array();
            $lable_box['t_shop_country']      = $region_array[$_CFG['shop_country']]; //网店-国家
            $lable_box['t_shop_city']         = $region_array[$_CFG['shop_city']]; //网店-城市
            $lable_box['t_shop_province']     = $region_array[$_CFG['shop_province']]; //网店-省份
            $lable_box['t_shop_name']         = $_CFG['shop_name']; //网店-名称
            $lable_box['t_shop_district']     = ''; //网店-区/县
            $lable_box['t_shop_tel']          = $_CFG['service_phone']; //网店-联系电话
            $lable_box['t_shop_address']      = $_CFG['shop_address']; //网店-地址
            $lable_box['t_customer_country']  = $region_array[$order['country']]; //收件人-国家
            $lable_box['t_customer_province'] = $region_array[$order['province']]; //收件人-省份
            $lable_box['t_customer_city']     = $region_array[$order['city']]; //收件人-城市
            $lable_box['t_customer_district'] = $region_array[$order['district']]; //收件人-区/县
            $lable_box['t_customer_tel']      = $order['tel']; //收件人-电话
            $lable_box['t_customer_mobel']    = $order['mobile']; //收件人-手机
            $lable_box['t_customer_post']     = $order['zipcode']; //收件人-邮编
            $lable_box['t_customer_address']  = $order['address']; //收件人-详细地址
            $lable_box['t_customer_name']     = $order['consignee']; //收件人-姓名

            $gmtime_utc_temp       = gmtime(); //获取 UTC 时间戳
            $lable_box['t_year']   = date('Y', $gmtime_utc_temp); //年-当日日期
            $lable_box['t_months'] = date('m', $gmtime_utc_temp); //月-当日日期
            $lable_box['t_day']    = date('d', $gmtime_utc_temp); //日-当日日期

            $lable_box['t_order_no']         = $order['order_sn']; //订单号-订单
            $lable_box['t_order_postscript'] = $order['postscript']; //备注-订单
            $lable_box['t_order_best_time']  = $order['best_time']; //送货时间-订单
            $lable_box['t_pigeon']           = '√'; //√-对号
            $lable_box['t_custom_content']   = ''; //自定义内容

            //标签替换
            $temp_config_lable = explode('||,||', $shipping['config_lable']);
            if (!is_array($temp_config_lable)) {
                $temp_config_lable[] = $shipping['config_lable'];
            }
            foreach ($temp_config_lable as $temp_key => $temp_lable) {
                $temp_info = explode(',', $temp_lable);
                if (is_array($temp_info)) {
                    $temp_info[1] = $lable_box[$temp_info[0]];
                }
                $temp_config_lable[$temp_key] = implode(',', $temp_info);
            }
            $shipping['config_lable'] = implode('||,||',  $temp_config_lable);

            $smarty->assign('shipping', $shipping);

            $smarty->display('print.htm');
        } elseif (!empty($shipping['shipping_print'])) {
            /* 代码 */
            echo $smarty->fetch("str:" . $shipping['shipping_print']);
        } else {
            $shipping_code = $db->getOne("SELECT shipping_code FROM " . $ecs->table('shipping') . " WHERE shipping_id=" . $order['shipping_id']);
            if ($shipping_code) {
                include_once(ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php');
            }

            if (!empty($_LANG['shipping_print'])) {
                echo $smarty->fetch("str:$_LANG[shipping_print]");
            } else {
                echo $_LANG['no_print_shipping'];
            }
        }
    } else {
        /* 模板赋值 */
        $smarty->assign('ur_here', $_LANG['order_info']);
        $smarty->assign('action_link', array('href' => 'order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['02_order_list']));

        /* 显示模板 */
        assign_query_info();
        $smarty->display('order_info.htm');
    }
}

elseif ($_REQUEST['act'] == 'receive_goods')
{
    $res['req_msg'] = true;
    $res['btncontent'] = false;
    if (!admin_priv('shipping_done', '', false)) {
        $res['message'] = '对不起，您还未被授权确认收货！';
        die($json->encode($res));
    }

    $_REQUEST = addslashes_deep($_REQUEST);

    $sql_select = 'SELECT receive_status,others,FROM_UNIXTIME(add_time, "【%Y-%m-%d %H:%i:%s】") add_time FROM '.
        $GLOBALS['ecs']->table('shipping_feed')." WHERE order_id={$_REQUEST['id']} ORDER BY add_time DESC";
    $info = $GLOBALS['db']->getRow($sql_select);

    $smarty->assign('id', $_REQUEST['id']);
    $smarty->assign('info', $info);
    $res['message'] = $smarty->fetch('date.htm');
    $res['title']   = '请选择顾客的收货时间：';

    die($json->encode($res));
}

/* 确认收货 */
elseif ($_REQUEST['act'] == 'shipping_done') {
    $order_id     = intval($_REQUEST['id']);
    $receive_time = strtotime($_REQUEST['receive_time']);

    $res = array('id' => $order_id, 'req_msg' => 'true', 'timeout' => 2000);
    if (!admin_priv('shipping_done', '', false)) {
        $res['message'] = '对不起，您还未被授权确认收货！';
        die($json->encode($res));
    }

    if (!empty($receive_time)) {
        $sql = 'UPDATE '.$ecs->table('order_info').' SET shipping_status=IF(shipping_status=2, 1, 2), '.
            " receive_time=$receive_time,shipping_feed_id=0,finish_admin_id={$_SESSION['admin_id']} WHERE order_id=$order_id";
        if ($db->query($sql)) {
            $sql_select = 'SELECT order_id,user_id,consignee,goods_amount,final_amount,add_time,admin_id,admin_name,platform FROM '
                .$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
            $order      = $GLOBALS['db']->getRow($sql_select);   //订单详情
            $order_time = $order['add_time'];

            $sql_select = 'SELECT user_id,user_name,rank_points,is_black,customer_type FROM '.
                $GLOBALS['ecs']->table('users')." WHERE user_id={$order['user_id']}";
            $user = $GLOBALS['db']->getRow($sql_select);

            //是否已经存在该订单的积分
            $sql_select = 'SELECT source_id FROM '.$GLOBALS['ecs']->table('user_integral')." WHERE source_id={$order['order_id']}";
            if(!$GLOBALS['db']->getOne($sql_select)){
                //购买平台适用的消费获积分是否启用  全品牌
                $sql = 'SELECT integral_id,scale,present_end,present_start,platform,min_consume,max_consume,available FROM '.$GLOBALS['ecs']->table('integral').' WHERE available=1 AND suit_brand=0';
                $sql_session = $sql." AND platform={$order['platform']} ";
                $inte_pur = $GLOBALS['db']->getRow($sql_session);

                if($inte_pur) {
                    //消费启用
                    $points = ceil($order['goods_amount'] * $inte_pur['scale']);

                    //生日获积分
                    $sql_bir  = $sql_session.' AND integral_way=4';
                    $inte_bir = $GLOBALS['db']->getRow($sql_bir);
                    if(count($inte_bir))    //生日启用
                    {
                        if (date('%M-%d') == date('%M-%d',strtotime($user['birthday']))) {
                            $bir_points = ceil($inte_bir['scale'] * $order['goods_amount']);
                        }
                    }

                    //所获积分
                    if($points < $bir_points) {
                        $points   = $bir_points;
                        $integral = $inte_bir;
                    } else {
                        $integral = $inte_pur;
                    }

                    $is_enable_pur_all = true;
                } else {//是否有存在全平台可用消费积分 全品牌
                    $sql_global = $sql." AND platform=0 AND integral_way=1";
                    $inte_global = $GLOBALS['db']->getRow($sql_global);
                    $points = ceil($order['goods_amount'] * $inte_global['scale']);

                    //生日获积分
                    $sql_bir = $sql.' AND platform=0 AND integral_way=4';

                    $inte_bir  = $GLOBALS['db']->getRow($sql_bir);
                    $bir_point = 0;

                    //生日启用
                    if($inte_bir) {
                        if(date('m-d') == substr($user['birthday'],5)) {
                            $bir_points = ceil($inte_bir['scale'] * $order['goods_amount']);
                        }
                    }

                    //所获积分
                    if($points<$bir_points) {
                        $points = $bir_points;
                        $integral = $inte_bir;
                    } else {
                        $integral = $inte_global;
                    }

                    $is_enable_pur_all = true;
                }


                if($is_enable_pur_all) {
                    //添加到未确认表
                    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_integral').
                        '(integral_id,points,haduse,source,source_id,receive_time,validity,integral_info,user_id,admin_id,exchange_points,increase_reduce,pre_points,confirm)'.
                        "VALUES({$integral['integral_id']},$points,0,'order',{$order['order_id']},UNIX_TIMESTAMP(NOW()),{$integral['present_end']},0,{$order['user_id']},{$_SESSION['admin_id']},$points,1,{$user['rank_points']},0)";

                    $result = $GLOBALS['db']->query($sql_insert);
                }

                //是否通过转介绍购买
                $sql_select = 'SELECT parent_id FROM '.$GLOBALS['ecs']->table('users')." WHERE user_id={$order['user_id']} AND payed_parent=0";
                $parent_id = $GLOBALS['db']->getOne($sql_select);
                if($parent_id > 0)
                {
                    //推荐送积分规则是否开启
                    $sql_select = 'SELECT integral_id,scale FROM '.$GLOBALS['ecs']->table('integral').
                        ' WHERE integral_way=2 AND available=1 AND present_end>'.time()." AND {$order['final_amount']}=>min_consume";
                    $integral_info = $GLOBALS['db']->getRow($sql_select);
                    //加入未确认表
                    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_integral').
                        '(integral_id,points,source_id,user_id,admin_id,exchange_points)VALUES'.
                        "({$integral_info['integral_id']},{$integral_info['scale']},{$order['order_id']},{$_SESSION['admin_id']},{$integral_info['scale']})";
                    $GLOBALS['db']->query($sql_insert);
                }
            }

            assign_user($order_id);
            update_taking_time();  // 更新商品可服用时间

            if (in_array($user['customer_type'], array(1,12,6,7,8,13,14,15,16,17))) {
                // 将成交的顾客转到已购买顾客中
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').
                    " SET customer_type=2 WHERE user_id={$user['user_iduser_id']}";
                $GLOBALS['db']->query($sql_update);
            }

            $res['timeout'] = 2000;
            $res['message'] = '确认收货已完成！';
            die($json->encode($res));
        }
    } else {
        $receive_status = intval($_REQUEST['receive_status']);
        $others         = '';
        if (!empty($_REQUEST['others'])) {
            $others = addslashes_deep($_REQUEST['others']);
        }

        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('shipping_feed').'(order_id,receive_status,add_time,others)VALUES('.
            "$order_id, $receive_status, UNIX_TIMESTAMP(), '$others')";
        $GLOBALS['db']->query($sql_insert);
        $shipping_feed_id = $GLOBALS['db']->insert_id();

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info')." SET shipping_feed_id=$receive_status WHERE order_id=$order_id";
        $GLOBALS['db']->query($sql_update);

        $res['message'] = '信息已保存，请再次及时跟进该订单！';
    }

    die($json->encode($res));
}

/* 退货处理 */
elseif ($_REQUEST['act'] == 'do_returns') {
    $res['response_action'] = $_REQUEST['act'];
    $res['code']    = 0;
    $res['req_msg'] = 'true';  // true 要求显示反馈信息 false 不要求显示反馈信息
    $res['timeout'] = 2000;
    if (!admin_priv('shipping_act', '', false)) {
        $res['message'] = '对不起，您没有退货的权限！';
        die($json->encode($res));
    }

    if (!admin_priv('do_returns', '', false)) {
        $res['message'] = '对不起，没有对该订单进行退货操作的权限';
        die($json->encode($res));
    }

    $order_id = intval($_REQUEST['order_id']);

    $sql_select = 'SELECT shipping_status,exp_info,verify_finance FROM '.
        $GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
    $shipping_info = $GLOBALS['db']->getRow($sql_select);
    if ($shipping_info['shipping_status'] > 2 || $shipping_info['verify_finance'] > 0) {
        $res['message'] = '该订单已不符合退货条件';
        die($json->encode($res));
    }

    // 恢复库存
    $result = restore_storage($order_id);
    if ($result) {
        $res['message'] = '退货失败，请检查订单信息后，再次尝试！';
        die($json->encode($res));
    }

    $return = array (
        'order_id'          => intval($_REQUEST['order_id']),
        'express_number'    => mysql_real_escape_string(trim($_REQUEST['express_number'])),
        'return_reason'     => mysql_real_escape_string(trim($_REQUEST['return_reason'])),
        'return_time'       => time(),
        'initiate_admin_id' => $_SESSION['admin_id'],
        //'return_way'      => intval($_REQUEST['return_way'])
    );

    $fields = implode(',', array_keys($return));
    $values = implode("','", array_values($return));

    // 保存退货信息
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('returns_order')."($fields)VALUES('$values')";
    if ($GLOBALS['db']->query($sql_insert)) {
        record_operate($sql_insert, 'order_info'); // 记录退货操作

        // 修改订单状态为退货状态
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info')." SET shipping_status=4 WHERE order_id=$order_id";
        if ($GLOBALS['db']->query($sql_update)) {
            $res['code']    = 1;
            $res['message'] = '系统已受理您的退货请求！';

            $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('repeat_purchase')." WHERE order_id=$order_id";
            $GLOBALS['db']->query($sql_delete);

            $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
            $user_id = $GLOBALS['db']->getOne($sql_select);

            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').
                " WHERE user_id=$user_id AND order_status=5 AND shipping_status=2";
            $deal_order_number = $GLOBALS['db']->getOne($sql_select);
            if ($deal_order_number <= 0) {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET customer_type=1 WHERE user_id=$user_id";
                $GLOBALS['db']->query($sql_update);
            }
        } else {
            $res['message'] = '申请退货失败，请稍后再试！';
        }
    } else {
        $res['message'] = '申请退货失败，请稍后再试！';
    }

    die($json->encode($res));
}

/* 退货列表 */
elseif ($_REQUEST['act'] == 'returned_list')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false)
    {
        unset($res['left']);
    }

    $order_list = order_list();  // 读取订单列表
    $shipping_list = get_higher_rate_shipping(); // 订单中使用频率最高的5家快递公司
    if (admin_priv('order_list_all', '', false))
    {
        @array_unshift($shipping_list, array('shipping_name'=>'全部','shipping_id'=>0));
    }

    if (admin_priv('shipping_act', '', false))
    {
        $smarty->assign('shipping_act', 1);
    }

    // 将已经列出的快递公司的id组成字符串
    if ($shipping_list)
    {
        foreach ($shipping_list as $val)
        {
            $other_shipping[] = $val['shipping_id'];
        }
        $other_shipping = implode('|', $other_shipping);
    }

    $smarty->assign('order_list',      $order_list['orders']);
    $smarty->assign('act',             $_REQUEST['act']);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    if (admin_priv('order_print', '', false))
    {
        $smarty->assign('access', 1);
    }

    $smarty->assign('curr_title', '退货订单列表');
    $smarty->assign('num', sprintf('（共%d条）', $order_list['record_count']));
    $res['main'] = $smarty->fetch('return_order_list.htm');

    if (isset($_REQUEST['shipping_id']))
    {
        $res['id'] = intval($_REQUEST['shipping_id']);
        $res['switch_tag'] = 'true';
    }

    die($json->encode($res));
}

/* 确认退回的订单 */
/*
elseif ($_REQUEST['act'] == 'return_goods_sign')
{
$res['response_action'] = $_REQUEST['act'];
$res['code']    = 0;
$res['req_msg'] = 'true';  // true 要求显示反馈信息 false 不要求显示反馈信息
$res['timeout'] = 2000;

if (!admin_priv('return_goods_sign', '', false))
{
$res['message'] = '对不起，你没有权限确认退货！';
die($json->encode($res));
}

$order_id = intval($_REQUEST['id']);

$goods_list = array();
$sql = "SELECT * FROM ".$ecs->table('order_goods')." WHERE order_id='$order_id'";
$result = $db->query($sql);
while ($row = $db->fetchRow($result))
{
// 虚拟商品支持
if ($row['is_real'] == 0)
{
// 取得语言项
$filename = ROOT_PATH.'plugins/'.$row['extension_code'].'/languages/common_'.$_CFG['lang'].'.php';
if (file_exists($filename))
{
include_once($filename);
if (!empty($_LANG[$row['extension_code'].'_link']))
{
$row['goods_name'] = $row['goods_name'].sprintf($_LANG[$row['extension_code'].'_link'], $row['goods_id'], $order['order_sn']);
}
}
}

$row['formated_subtotal'] = price_format($row['goods_price'] * $row['goods_number']);
$row['formated_goods_price'] = intval($row['goods_price']) ? price_format($row['goods_price']) : '赠品';

$goods_attr[] = explode(' ', trim($row['goods_attr']));//将商品属性拆分为一个数组

if ($row['is_package'] == 1)
{
$row['package_goods_list'] = get_packing($row['goods_sn']);
}

$goods_list[] = $row;
}

$smarty->assign('id', $order_id);
$smarty->assign('goods_list', $goods_list);

$res['timeout'] = 1000000;
$res['message'] = $smarty->fetch('goods_back.htm');
die($json->encode($res));
}
 */

/* 提交退货详情 */
/*
elseif ($_REQUEST['act'] == 'handle_storage_back')
{
$res['response_action'] = $_REQUEST['act'];
$res['code']            = 0;
$res['req_msg']         = 'true';  // true 要求显示反馈信息 false 不要求显示反馈信息
$res['timeout']         = 2000;
$res['order_id']        = $_REQUEST['id'];

if (!admin_priv('handle_storage_back', '', false))
{
$res['message'] = '对不起，你没有权限确认退货！';
die($json->encode($res));
}

$request = addslashes_deep($_REQUEST);

// 计算提交的商品数量
foreach ($request['intact_number'] as $key=>&$val)
{
$val = array_sum($val);
$request['loss_number'][$key] = array_sum($request['loss_number'][$key]);
}

// 获取订单中商品数量
$sql_select = 'SELECT stock FROM '.$GLOBALS['ecs']->table('order_goods').' g, '.
$GLOBALS['ecs']->table('order_info').' i WHERE i.order_status=5 AND '.
" i.shipping_status=4 AND g.order_id=i.order_id AND i.order_id={$request['id']}";
$result = $GLOBALS['db']->getCol($sql_select);
if (empty($result))
{
$res['message'] = '请确认订单状态是否符合退货要求，订单商品是否无误？！';
die($json->encode($res));
}

$goods_list = array ();
foreach ($result as $val)
{
$val = unserialize($val);
foreach ($val as $v)
{
$goods_list[$v['rec_id']] += $v['quantity'];
}
}

$rec_list = implode(',', array_keys($goods_list));
$sql_select = 'SELECT rec_id,goods_sn FROM '.$GLOBALS['ecs']->table('stock_goods')." WHERE rec_id IN ($rec_list)";
$goods_sn = $GLOBALS['db']->getAll($sql_select);
$goods_list_sn = array ();
foreach ($goods_list as $k=>$v)
{
foreach ($goods_sn as $e)
{
if ($k == $e['rec_id']) $goods_list_sn[$e['goods_sn']] = $v;
}
}

foreach ($request['intact_number'] as $key=>$val)
{
if ($val+$request['loss_number'][$key] != $goods_list_sn[$key])
{
$res['message'] = "商品 $key 提交的数量与订单中的实际数量不一致，请仔细核对！";
die($json->encode($res));
}
}

$time = time();
$GLOBALS['db']->query('START TRANSACTION');

// 将可以二次销售的商品退回至仓库
foreach ($request['intact_number'] as $key=>$val)
{
$sql_select = 'SELECT g.rec_id FROM '.$GLOBALS['ecs']->table('stock_goods').' g,'.
$GLOBALS['ecs']->table('stock')." s WHERE g.goods_sn='$key' AND g.stock_id=s.stock_id ".
' ORDER BY s.add_time DESC,g.quantity ASC';
$rec_id = $GLOBALS['db']->getOne($sql_select);

$sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods')." SET quantity=quantity+$val".
" WHERE rec_id=$rec_id AND goods_sn='$key'";
$update_res = $GLOBALS['db']->query($sql_update);
if (!$update_res)
{
$GLOBALS['db']->query('ROLLBACK');
$res['message'] = '操作退货仓时出错，请稍后再试！';
die($json->encode($res));
}
}
 */
// 将可二次销售的商品暂存至退货仓
/*
foreach ($request['intact_number'] as $key=>$val)
{
if ($val > 0) $back[] = "({$request['id']}, '$key', '$val',{$_SESSION['admin_id']},$time)";
}

$sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('backgoods').
'(order_id,goods_sn,goods_number,admin_id,add_time)VALUES'.implode(',', $back);
$insert_res = $GLOBALS['db']->query($sql_insert);
if (!$insert_res)
{
$GLOBALS['db']->query('ROLLBACK');
$req['message'] = '操作退货仓时出错，请稍后再试！';
die($json->encode($res));
}
 */

/*
// 将不能二次销售的商品，添加至废品仓
foreach ($request['loss_number'] as $key=>$val)
{
if ($val > 0) $waste[] = "({$request['id']}, '$key', '$val',{$_SESSION['admin_id']},$time)";
}

$sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('warehouse').
'(order_id,goods_sn,goods_number,admin_id,add_time)VALUES'.implode(',', $waste);
$insert_res = $GLOBALS['db']->query($sql_insert);

if (!$insert_res)
{
$GLOBALS['db']->query('ROLLBACK');
$req['message'] = '操作废品仓时出错，请稍后再试！';
die($json->encode($res));
}

$sql_update = 'UPDATE '.$GLOBALS['ecs']->table('returns_order').
" SET arrive_time=$time,confirmed_admin_id={$_SESSION['admin_id']} WHERE order_id={$request['id']}";
$GLOBALS['db']->query($sql_update);

$sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').' SET shipping_status=5 '.
" WHERE shipping_status=4 AND order_id={$request['id']}";
$GLOBALS['db']->query($sql_update);

$GLOBALS['db']->query('COMMIT');

$res['code'] = 1;
$res['message'] = '该订单退货完成！';
die($json->encode($res));
}
 */

/* 已取消订单 */
elseif ($_REQUEST['act'] == 'canceled_list')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false)
    {
        unset($res['left']);
    }

    $order_list = order_list();  // 读取订单列表
    $shipping_list = get_higher_rate_shipping(); // 订单中使用频率最高的5家快递公司
    if (admin_priv('order_list_all', '', false) && is_array($shipping_list))
    {
        array_unshift($shipping_list, array('shipping_name'=>'全部','shipping_id'=>0));
    }

    if (admin_priv('shipping_act', '', false))
    {
        $smarty->assign('shipping_act', 1);
    }

    // 将已经列出的快递公司的id组成字符串
    if ($shipping_list)
    {
        foreach ($shipping_list as $val)
        {
            $other_shipping[] = $val['shipping_id'];
        }
        $other_shipping = implode('|', $other_shipping);

        //$smarty->assign('shipping_list',   $shipping_list);
        //$smarty->assign('other_shipping',  $other_shipping);
    }

    $smarty->assign('order_list',      $order_list['orders']);
    $smarty->assign('act',             $_REQUEST['act']);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    if (admin_priv('order_print', '', false))
    {
        $smarty->assign('access', 1);
    }

    $smarty->assign('curr_title', '取消的订单');
    $smarty->assign('num', sprintf('（共%d条）', $order_list['record_count']));
    $res['main'] = $smarty->fetch('canceled_list.htm');

    if (isset($_REQUEST['shipping_id']))
    {
        $res['id'] = intval($_REQUEST['shipping_id']);
        $res['switch_tag'] = 'true';
    }

    die($json->encode($res));
}


/* 获取地区选项 */
elseif ($_REQUEST['act'] == 'get_regions')
{
    // 设置初始省份
    $smarty->assign('province_list', get_regions(1,1));
    $res = $smarty->fetch('deliver.htm');

    die($res);
}

/* 获取日期插件 */
elseif ($_REQUEST['act'] == 'add_time')
{
    $smarty->assign('act', 'add_time');
    $res = $smarty->fetch('deliver.htm');

    die($res);
}

//白单和快递单确认
elseif ($_REQUEST['act'] == 'get_order_info')
{
    if (!admin_priv('get_order_info', '', false)){
        $res['req_msg'] = 'true';
        $res['id'] = $order_id;
        $res['message'] = '该订单已经进入发货流程！';
        $res['timeout'] = 2000;

        die($res);
    }

    $type = mysql_real_escape_string($_REQUEST['type']);         //判断是白单还是快递
    $order_id = intval($_REQUEST['order_id']);

    $sql_select = 'SELECT final_amount,shipping_name,shipping_fee,confirm_print,confirm_shipping,confirm_spdata,confirm_ordata FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
    $result = $GLOBALS['db']->getRow($sql_select);
    $result['confirm_spdata'] = $result['confirm_spdata'] ? date($_CFG['time_format'],$result['confirm_spdata']) : '：尚未确认';
    $result['confirm_ordata'] = $result['confirm_ordata'] ? date($_CFG['time_format'],$result['confirm_ordata']) : '：尚未确认';

    if($type == 'express')               //快递单
    {
        $value = '确 认';
        if($result['confirm_shipping'])
        {
            $value = "修 改";
        }

        $res['html'] = '<p>上次确认时间'.$result['confirm_spdata'].'</P>'.'<p>'.$result['shipping_name'].'<input type="text" id="shipping_fee" value='.$result['shipping_fee'].'></p><p>其它说明</p>&nbsp;<textarea style="resize:none" cols="32px" rows="5px" id="other"></textarea></center><p><input type="button" id="b_value" class="input_submit" onclick="confirmExpress('.$order_id.')" value="'.$value.'"/><input type="button" onclick="cancel()" class="input_submit" style="margin-left:30px" value="取  消"/></p>';
    }
    else
    {
        if($result['confirm_print'])
        {
            $value = '第'.$result['confirm_print'].'次确认';
        }

        $res['html'] = '<p>上次确认时间'.$result['confirm_ordata'].'</P>'.'<p>共计'.$result['final_amount'].'元</p><p>其它说明</p>&nbsp;<textarea style="resize:none" cols="32px" rows="5px" id="other"></textarea></center><p><input id="b_value" type="button" class="input_submit" onclick="confirmInfo('.$order_id.')" value="'.$value.'"/><input type="button" onclick="cancel()" class="input_submit" style="margin-left:30px" value="取  消"/></p>';
    }
    $res['type'] = $type;
    die($json->encode($res));
}

elseif ($_REQUEST['act'] == 'confirm_info')         //发货白单确认
{
    $order_id = intval($_REQUEST['order_id']);
    $shipping_fee = floatval($_REQUEST['shipping_fee']);        //快递费用
    $b_value = mysql_real_escape_string('b_value');             //确认次数
    $type = mysql_real_escape_string($_REQUEST['type']);        //确认类型
    $other = mysql_real_escape_string($_REQUEST['other']);        //其实说明
    $confirm_admin = $_SESSION['admin_name'];                   //确认人
    $confirm_data = strtotime(date('Y-m-d H:i:s'));             //确认时间

    if($type == 'express')                                      //快递单确认
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info')." SET shipping_fee=$shipping_fee,confirm_shipping=confirm_shipping+1,confirm_spdata=$confirm_data,confirm_spadmin='$confirm_admin',confirm_content='$other' WHERE order_id=$order_id";
    }
    else
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info')." SET confirm_print=confirm_print+1,confirm_content='$other',confirm_oradmin='$confirm_admin',confirm_ordata=$confirm_data WHERE order_id=$order_id";
    }

    $result = $GLOBALS['db']->query($sql_update);
    die($result);
}

/* 动态获取配送方式 */
elseif ($_REQUEST['act'] == 'get_shipping_list')
{
    $pay_id = intval($_REQUEST['pay_id']);
    $sql_select = 'SELECT is_cod FROM '.$GLOBALS['ecs']->table('payment')." WHERE pay_id=$pay_id";
    $is_cod = $GLOBALS['db']->getOne($sql_select);
    $shipping_list = shipping_list($is_cod);

    die($json->encode($shipping_list));
}

/* 获取套餐中的商品详情 */
elseif ($_REQUEST['act'] == 'packing_goods') {
    $packing_sn = addslashes_deep($_REQUEST['id']);
    $packing_sn = trim($packing_sn);

    $sql_select = 'SELECT g.goods_name, g.num, p.packing_name FROM '.$GLOBALS['ecs']->table('packing_goods').' g, '.
        $GLOBALS['ecs']->table('packing')." p WHERE g.packing_id=p.packing_id AND p.packing_desc='$packing_sn'";
    $packing_goods = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('packing_goods', $packing_goods);
    die($smarty->fetch('deliver.htm'));
}

/* 淘宝订单补充同步 */
elseif ($_REQUEST['act'] == 'addsyn') {
    $start = time() -24*3600*5;

    // 获取标记发货所需的相关订单参数
    $sql = 'SELECT IF(platform_order_sn,platform_order_sn,order_sn) order_sn,team,'.
        'tracking_sn,shipping_id,shipping_name,shipping_code,province,shipping_time FROM '.
        $GLOBALS['ecs']->table('order_info')." WHERE team=6 AND order_status=5 AND shipping_status=1 AND add_time>$start";
    $order_info = $GLOBALS['db']->getAll($sql);

    require(dirname(__FILE__).'/taobao/order_synchro.php');
    require(dirname(__FILE__).'/taobao/sk.php');
    $auth = require(dirname(__FILE__).'/taobao/config.php');

    // 配置淘宝签权参数
    $c = new TopClient;

    foreach ($order_info as $val){
        $tracking_sn = $val['tracking_sn'];

        // 获取快递公司编码
        $sql = 'SELECT company_code, company_name FROM '.$GLOBALS['ecs']->table('shipping').
            " WHERE shipping_code='{$val['shipping_code']}'";
        $logistics = $GLOBALS['db']->getRow($sql);

        $c->appkey    = $auth['appkey'];
        $c->secretKey = $auth['secretKey'];

        // 查询订单当前状态是否符合发货条件
        $req = new TradeFullinfoGetRequest;
        $req->setFields("status");
        $req->setTid($val['order_sn']);
        $shipping_able = $c->execute($req, $sk['access_token']);
        $shipping_able = $json->decode($json->encode($shipping_able), true);

        // 订单状态符合发货条件
        if ($shipping_able['trade']['status'] == 'WAIT_SELLER_SEND_GOODS')
        {
            // 构建标记发货的数据格式
            $req = new LogisticsOfflineSendRequest;

            if ($logistics['company_code'] == 'zjs')
            {
                $req->setOutSid($tracking_sn);
                $req->setTid($val['order_sn']);
                $req->setCompanyCode(strtoupper($logistics['company_code']));
            }
            else
            {
                $req->setOutSid($tracking_sn);
                $req->setTid($val['order_sn']);
                $req->setCompanyCode(strtoupper($logistics['company_code']));
            }

            // 发送发货请求
            $shipping_resp = $c->execute($req, $sk['access_token']);
        }

        // 订单符合修改运单号条件
        elseif ($shipping_able['trade']['status'] == 'WAIT_BUYER_CONFIRM_GOODS')
        {
            $req = new LogisticsConsignResendRequest;
            $req->setOutSid($tracking_sn);
            $req->setTid(number_format($val['order_sn'], 0, '', ''));
            $req->setCompanyCode(strtoupper($logistics['company_code']));

            $shipping_resp = $c->execute($req, $sk['access_token']);
        }
        else
        {
            $res['message'] = '订单状态已改变，不符合发货条件！！！';
            $res['shipping_name'] = $val['shipping_name'];
            $res['errMsg'] = 1;
        }
    }
}

/* 解锁订单 */
elseif ($_REQUEST['act'] == 'unlock') { die(''); }

/* 随机获取订单 */
elseif ($_REQUEST['act'] == 'rand_order') {
    $now_time = time();
    if (isset($_REQUEST['order_type']) && $_REQUEST['order_type'] == 1) {
        $order_type = " AND order_type=1 ";
    } else {
        $order_type = " AND order_type<>1 ";
    }

    // 计算在线客服总数
    $online_admin = $sess->get_admin_count();

    $online_admin_str = implode(',', $online_admin);

    // 获取可以分配订单的客服数量
    $action = 'rand_order_'.$_REQUEST['a'];

    $sql_select = 'SELECT COUNT(*) num,CONCAT(user_id,",") admin_list FROM '.$GLOBALS['ecs']->table('admin_user').
        " WHERE action_list LIKE '%,$action,%' AND user_id IN ($online_admin_str)";
    $sql_res = $GLOBALS['db']->getRow($sql_select);
    $online_admin_count = $sql_res['num'];
    $admin_list = $sql_res['admin_list'];
    unset($sql_res);

    if (!$online_admin_count || !admin_priv($action, '', false)){// || strpos($admin_list, "'{$_SESSION['admin_id']}'") === false) {
        $msg['response_action'] = 'rand_order';
        $msg['code'] = 0;
        $msg['req_msg'] = true;
        $msg['message'] = '请确认下您是否在可分配订单列表中……';
        $msg['timeout'] = 1000;
        die($json->encode($msg));
    }

    // 统计未处理的新订单总数
    if ($_REQUEST['a'] == 'verify') {
        $table = 'ordersyn_info';
        $where_select = ' WHERE order_status=0 AND shipping_status=0 AND admin_id=0 '.$order_type;
        $where_update = " WHERE order_status=0 AND shipping_status=0 AND operator=0 $order_type AND admin_id=0 AND (order_lock IN (0,{$_SESSION['admin_id']}) OR lock_timeout<$now_time)";
        if (intval($_REQUEST['platform'])) {
            $where_select .= ' AND platform='.intval($_REQUEST['platform']);
            $where_update .= ' AND platform='.intval($_REQUEST['platform']);
        }

        $platform_list = platform_list();      // 销售平台
        $template = 'order_list.htm';

        $smarty->assign('act', 'temp_order');
        $smarty->assign('curr_title',    isset($_REQUEST['order_type']) ? '刷单列表' : '新顾客订单');

        if (admin_priv('order_list_all', '', false)) {
            array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
        }

        if (isset($_REQUEST['platform'])) {
            $smarty->assign('platform', $platform);
        }
        $smarty->assign('platform_list', $platform_list);
    } elseif ($_REQUEST['a'] == 'print') {
        $table = 'order_info';
        $where_select = " WHERE order_status=1 AND shipping_status=0 AND pay_status=1 ";
        $where_update = " WHERE order_status=1 AND shipping_status=0 AND pay_status=1 AND (order_lock IN (0,{$_SESSION['admin_id']}) OR lock_timeout<$now_time)";
        $template = 'current_order_list.htm';
        $smarty->assign('curr_title',    '待发货订单');
        $smarty->assign('act', 'current_order');
        $shipping_list = get_higher_rate_shipping(); // 订单中使用频率最高的5家快递公司
        if (admin_priv('order_list_all', '', false)) {
            @array_unshift($shipping_list, array('shipping_name'=>'全部','shipping_id'=>0));
        }
        if (admin_priv('shipping_act', '', false)) {
            $smarty->assign('shipping_act', 1);
        }
        // 将已经列出的快递公司的id组成字符串
        if ($shipping_list) {
            foreach ($shipping_list as $val) {
                $other_shipping[] = $val['shipping_id'];
            }
            $other_shipping = implode('|', $other_shipping);
            $smarty->assign('shipping_list',   $shipping_list);
            $smarty->assign('other_shipping',  $other_shipping);
        }
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table($table).$where_select;
    $order_total = $GLOBALS['db']->getOne($sql_select);

    // 计算平均每人有多少订单
    $order_limit = 10; // 该处留一变量 方便日后可以设定各客服分配订单数量
    $per_admin_order = ceil($order_total/$online_admin_count) ?: 1;
    $per_admin_order = $per_admin_order > $order_limit ? $order_limit : $per_admin_order; // 如果平均每人订单数量超过20则每人只分配20个订单

    $limit_time = $now_time + $per_admin_order * 120; // 每个订单2分钟的处理时间

    // 清除订单分配信息后 再重新分配订单
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table($table)." SET order_lock={$_SESSION['admin_id']}, ".
        " lock_timeout=$limit_time $where_update ORDER BY add_time ASC LIMIT $per_admin_order";
    $GLOBALS['db']->query($sql_update);

    // 开始进入订单列表流程
    $res = array ();

    $order_list = order_list();        // 获取订单列表

    $smarty->assign('order_list',    $order_list['orders']);

    // 分页参数
    $smarty->assign('page_link',     $order_list['condition']);
    $smarty->assign('page_set',      $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('num',           sprintf('（共%d条）', $order_list['record_count']));
    $smarty->assign('admin_id',      $_SESSION['admin_id']);

    $smarty->assign('platform',  isset($_REQUEST['platform']) ? $_REQUEST['platform']:0);

    $res['main'] = $smarty->fetch($template);

    if (isset($_REQUEST['platform'])) {
        $res['id'] = intval($_REQUEST['platform']);
        $res['switch_tag'] = 'true';
    }

    die($json->encode($res));
}

//存货订单管理
elseif ($_REQUEST['act'] == 'inventory_order_list')
{
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

    $sql_select = 'SELECT COUNT(*) FROM '
        .$GLOBALS['ecs']->table('inventory_order')
        .' AS iv LEFT JOIN '.$GLOBALS['ecs']->table('users')
        .' AS u ON iv.user_id=u.user_id '
        .' GROUP BY iv.user_id';

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

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

    $filter = array (
        'filter'        => $filter,
        'page_count'    => $filter['page_count'],
        'record_count'  => $filter['record_count'],
        'page_size'     => $filter['page_size'],
        'page'          => $filter['page'],
        'page_set'      => $page_set,
        'condition'     => $condition,
        'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'           => $filter['page']*$filter['page_size'],
        'act'           => 'inventory_order_list',
    );

    //拥有存货订单的顾客
    $sql_select = 'SELECT iv.user_id,u.user_name,u.mobile_phone,u.admin_name FROM '
        .$GLOBALS['ecs']->table('inventory_order')
        .' AS iv LEFT JOIN '.$GLOBALS['ecs']->table('users')
        .' AS u ON iv.user_id=u.user_id '
        .' GROUP BY iv.user_id'
        .' LIMIT '.($filter['page'] - 1)*$filter['page_size'].','.$filter['page_size'];

    $user_list = $GLOBALS['db']->getAll($sql_select);

    //获取存货订单
    foreach($user_list AS $val)
    {
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('inventory_order')
            .' WHERE user_id='.$val['user_id'];

        $inventory_order = $GLOBALS['db']->getAll($sql_select);
        $val['inventory_order'] = $inventory_order;
    }

    $smarty->assign('inventory_order',$inventory_order);
    $smarty->assign('filter',$filter);

    $res['main'] = $smarty->fetch('inventory_order_list.htm');

    die($json->encode($res));
}

//搜索存货订单
elseif ($_REQUEST['act'] == 'sch_inventory')
{
    $user_name = mysql_real_escape_string(trim($_REQUEST['user_name']));
    $phone = mysql_real_escape_string(trim($_REQUEST['phone']));
    $store_time = mysql_real_escape_string(trim($_REQUEST['store_time']));
    $order_sn = mysql_real_escape_string(trim($_REQUEST['order_sn']));

    $where = ' WHERE 1';
    $condition = '';

    if($user_name != '')
    {
        $where .= " AND user_name LIKE '%$user_name%' ";
        $condition .= "&user_name=$user_name";
    }

    if($phone != '')
    {
        $where .= " AND phone LIKE '%$phone%' ";
        $condition .= "&phone=$phone";
    }

    if($store_time != '')
    {
        $where .= " AND store_time=$store_time ";
        $condition .= "&store_time=$store_time";
    }

    if($order_sn != '')
    {
        $where .= " AND order_sn=$order_sn ";
        $condition .= "&order_sn=$order_sn";
    }

    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20;
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users');

    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('sch_inventory_order.htm');

    die($json->encode($res));
}

/* 批量打印发货单 */
elseif ($_REQUEST['act'] == 'batch_print') {
    if (!admin_priv('batch_print', '', false)) {
        $msg = array ('req_msg'=>true, 'timeout'=>2000, 'message'=>'对不起，您无权批量打印订单');
    }

    // 用一条SQL读取订单数据
    $sql_select = 'SELECT i.order_id,i.consignee,i.tracking_sn,i.mobile,i.pay_id,i.order_type,i.pay_name,i.shipping_name,'.
        'm.card_number,i.admin_id,i.final_amount,r.role_describe platform,i.shipping_fee,i.discount_amount,i.discount_explain,'.
        'i.address,i.user_id,IFNULL(i.order_sn,i.platform_order_sn)order_sn,FROM_UNIXTIME(i.add_time,"%Y-%m-%d")order_time,'.
        'a.user_name,a.phone,CONCAT(p.region_name,c.region_name,d.region_name) region FROM '.$GLOBALS['ecs']->table('order_info').
        ' i LEFT JOIN '.$GLOBALS['ecs']->table('role').' r ON r.role_id=i.platform '.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('region').' p ON p.region_id=i.province '.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('region').' c ON c.region_id=i.city '.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('region').' d ON d.region_id=i.district '.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('memship_number').' m ON m.user_id=i.user_id '.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' a ON a.user_id=i.admin_id '.
        ' WHERE i.review=1 AND'.' i.order_status=1 AND i.shipping_status=0 ORDER BY i.goods_kind DESC,i.goods_num ASC';
    $order_result = $GLOBALS['db']->getAll($sql_select.$selected_orders);

    if (empty($order_result)) {
        $msg = array('req_msg'=>true, 'timeout'=>2000, 'message'=>'请先选择需要批量打印的订单！');
        die($json->encode($msg));
    }

    // 收集订单ID
    $order_id_list = array();
    $order_list = array();
    foreach ($order_result as &$val){
        $val['formated_final_amount'] = price_format($val['final_amount']);
        $val['formated_shipping_fee'] = price_format($val['shipping_fee']);
        $val['formated_discount_amount'] = price_format($val['discount_amount']);
        // $val['official_phone'] = $val['platform'] == 'D' ? '400-616-9817或020-66212345' : '400-616-9717或020-66210088';
        $val['notice'] = in_array($val['platform'], array('K1','K2','K3','K4'));

        $order_id_list[] = $val['order_id'];
        $order_list[$val['order_id']] = $val;
    }

    // 拼接字符串
    $order_id_list = implode(',', $order_id_list);

    // 获取订单中的商品单品数据
    $sql_select = 'SELECT g.order_id, g.goods_name, g.goods_sn, g.goods_number, g.is_gift, g.goods_price, %s FROM '.
        $GLOBALS['ecs']->table('order_goods').' g %s WHERE g.order_id IN (%s) AND g.goods_sn REGEXP "%s"';
    $goods_result = $GLOBALS['db']->getAll(sprintf($sql_select, 's.shop_price shop_price', ' LEFT JOIN '.$GLOBALS['ecs']->table('goods').' s ON s.goods_sn=g.goods_sn ', $order_id_list, '^[0-9]+$'));
    foreach ($goods_result as &$val){
        $val['formated_shop_price']  = price_format($val['shop_price']);
        $val['formated_goods_price'] = price_format($val['goods_price']);
        $val['formated_subtotal'] = price_format(bcmul($val['goods_price'],$val['goods_number'],2));
        $order_list[$val['order_id']]['goods_list'][] = $val;
    }

    // 获取订单中的商品套餐数据
    $package_result = $GLOBALS['db']->getAll(sprintf($sql_select, ' p.packing_price shop_price', 'LEFT JOIN '.$GLOBALS['ecs']->table('packing').' p ON p.packing_desc=g.goods_sn ', $order_id_list, '^[a-z]'));
    if (!empty($package_result)) {
        $package_sn_list = array();
        foreach ($package_result as $val){
            $package_sn_list[] = $val['goods_sn'];
        }

        $package_sn_list = implode("','", array_unique($package_sn_list));

        // 获取套餐中的商品列表
        $sql_select = 'SELECT p.package_sn, p.goods_sn, p.goods_name, p.num goods_number, g.shop_price FROM '.
            $GLOBALS['ecs']->table('packing_goods').' p LEFT JOIN '.$GLOBALS['ecs']->table('goods').
            " g ON g.goods_sn=p.goods_sn WHERE p.package_sn IN ('$package_sn_list')";
        $package_goods_result = $GLOBALS['db']->getAll($sql_select);
        $package_list = array();
        foreach ($package_goods_result as &$val){
            $val['formated_shop_price']  = price_format($val['shop_price']);
            $package_list[$val['package_sn']][] = $val;
        }
        foreach ($package_result as &$val){
            $val['formated_shop_price']  = price_format($val['shop_price']);
            $val['formated_goods_price'] = price_format($val['goods_price']);
            $val['formated_subtotal'] = price_format(bcmul($val['goods_price'],$val['goods_number'],2));
            $val['goods_list'] = $package_list[$val['goods_sn']];
        }
        foreach ($package_result as &$val){
            $order_list[$val['order_id']]['package_list'][] = $val;
        }
    }

    $smarty->assign('order_list', $order_list);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info')." SET review=2 WHERE review=1 AND order_id IN ($order_id_list)";
    $GLOBALS['db']->query($sql_update);

    $stamp = '';
    for ($i = 0; $i < 15; $i++) {
        $stamp .= mt_rand(0,9);
    }

    $smarty->assign('stamp', $stamp.time());
    $smarty->assign('operator', $_SESSION['admin_id']);
    $smarty->assign('print_time', date('Y-m-d'));

    $smarty->template_dir = '../'.DATA_DIR;
    $smarty->display('multi_order_print_A4.htm');
}

/* 手动添加订单时查询该订单是否已存在 */
elseif ($_REQUEST['act'] == 'check_order') {
    $order_sn = mysql_real_escape_string($_REQUEST['order_sn']);

    $msg = array('req_msg'=>true, 'message'=>'该订单已存在，请勿重复下单！');

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').
        " WHERE order_status IN (1,5) AND shipping_status<3 AND (order_sn='$order_sn' OR platform_order_sn='$order_sn')";
    $result = $GLOBALS['db']->getOne($sql_select);
    if ((boolean)$result) {
        die($json->encode($msg));
    }

    $sql_select = 'SELECT order_status FROM '.$GLOBALS['ecs']->table('ordersyn_info')." WHERE order_status>-2 AND order_sn='$order_sn'";
    $result = $GLOBALS['db']->getOne($sql_select);
    switch ($result) {
    case '0' :
        die($json->encode($msg));
    case '-1' :
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').
            " WHERE order_status IN (1,5) AND shipping_status<3 AND (order_sn='$order_sn' OR platform_order_sn='$order_sn')";
        $result = $GLOBALS['db']->getOne($sql_select);
        if (empty($result)) {
            die($json->encode(array()));
        }
        die($json->encode($msg));
    }

    die($json->encode(array()));
}

/* 刷单地址列表 */
elseif ($_REQUEST['act'] == 'false_order') {

    $order_list = false_order_list();

    $smarty->assign('order_list',      $order_list['orders']);
    $smarty->assign('act',             $_REQUEST['act']);

    // 分页参数
    $smarty->assign('condition',     $order_list['condition']);
    $smarty->assign('page_list',     $order_list['page_set']);
    $smarty->assign('record_count',  $order_list['record_count']);
    $smarty->assign('page_size',     $order_list['page_size']);
    $smarty->assign('page',          $order_list['page_no']);
    $smarty->assign('page_count',    $order_list['page_count']);
    $smarty->assign('page_start',    $order_list['start']);
    $smarty->assign('page_end',      $order_list['end']);

    $smarty->assign('dst_script',  'order');
    $smarty->assign('act',         $_REQUEST['act']);

    $res['main'] = $smarty->fetch('false_order_data.htm');
    $res['page'] = $smarty->fetch('page_fragment.htm');

    if (!(isset($_REQUEST['start_time']) || isset($_REQUEST['page_no']) || isset($_REQUEST['platform']) || isset($_REQUEST['admin_id']))) {
        $smarty->assign('data', $res['main']);
        $smarty->assign('page', $res['page']);
        $res['main'] = $smarty->fetch('false_order.htm');

        unset($res['page']);
    }

    echo $json->encode($res);
    return;
}

/* 保存刷单的订单编号 */
elseif ($_REQUEST['act'] == 'save_brush_order_sn') {

    $msg = array(
        'req_msg' => true,
        'timeout' => 2000,
    );

    $brush_order_sn  = mysql_real_escape_string($_REQUEST['order_sn']);
    $brush_platform  = intval($_REQUEST['brush_platform']);
    $source_order_id = intval($_REQUEST['order_id']);

    if (!$brush_platform || !$brush_order_sn || !$source_order_id) {
        $msg['message'] = '提交的数据有误，请检查后再提交！';
        echo $json->encode($msg);
        return;
    }

    $sql_replace = 'REPLACE INTO '.$GLOBALS['ecs']->table('brush_order').
        '(source_order_id,brush_order_sn,brusher_id,brush_platform)VALUES('.
        "$source_order_id,'$brush_order_sn',{$_SESSION['admin_id']},$brush_platform)";
    if ($GLOBALS['db']->query($sql_replace)) {
        $msg['message'] = '刷单信息保存成功！系统将自动标记发货并过滤！';
        $msg['id'] = $source_order_id;
    } else {
        $msg['message'] = '刷单信息保存失败！请联系技术人员！';
    }

    echo $json->encode($msg);
    return;
}

/* 显示顾客联系信息 */
elseif ($_REQUEST['act'] == 'show_single_info') {
    $order_id = intval($_REQUEST['order_id']);
    $table = mysql_real_escape_string($_REQUEST['table']);

    $order_info = get_order_detail($order_id, $table);

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('view_user_info').
        '(order_id,admin_id)VALUES('."$order_id,{$_SESSION['admin_id']})";
    $GLOBALS['db']->query($sql_insert);

    $msg = array(
        'req_msg'=>true,
        'message'=>"{$order_info['mobile']}<br>{$order_info['tel']}<br>{$order_info['province']}{$order_info['city']}{$order_info['district']}<br>{$order_info['address']}"
    );

    echo $json->encode($msg);
    return;
}

/* 打印收据 */
elseif ($_REQUEST['act'] == 'order_receipt') {
    if (!admin_priv('order_receipt', '', false)) {
        echo '对不起，您没有权限打印收据！';
        return;
    }

    $order_id = intval($_REQUEST['order_id']);
    $sql_select = 'SELECT g.goods_name,g.goods_price,g.goods_number,FROM_UNIXTIME(i.shipping_time, "%Y-%m-%d") shipping_date,'.
        'i.final_amount,i.consignee FROM '.$GLOBALS['ecs']->table('order_goods').' g, '.$GLOBALS['ecs']->table('order_info').
        " i WHERE i.order_id=g.order_id AND g.goods_price>0 AND g.is_gift<>1 AND i.shipping_status=1 AND i.order_id=$order_id";
    $goods_list = $GLOBALS['db']->getAll($sql_select);
    if (empty($goods_list)) {
        echo '订单尚未发货，或商品列表为空，请查证后再打印收据！';
        return;
    }

    $order_info = array();
    foreach ($goods_list as $val){
        $order_info['goods_list'][] = $val;

        if (!isset($order_info['big5'])) {
            $order_info['big5'] = nature2zh($val['final_amount']);
        }

        if (!isset($order_info['final_amount'])) {
            $order_info['final_amount'] = $val['final_amount'];
        }

        if (!isset($order_info['shipping_date'])) {
            $order_info['shipping_date'] = $val['shipping_date'];
        }

        if (!isset($order_info['consignee'])) {
            $order_info['consignee'] = $val['consignee'];
        }
    }

    $order_info['printer'] = $_SESSION['admin_name'];

    $smarty->assign('order_info', $order_info);
    $smarty->display('receipt.htm');
}

/**
 * 订单列表
 */
function order_list()
{
    if (!admin_priv('all', '', false) && admin_priv('order_part_view', '', false)) {
        $role_list_str = order_cross_platform();
    }
    $now_time     = time();
    $temp_fields  = '';
    $table_admin  = '';
    $order_status = '';
    $condition    = '';
    $where        = '';
    switch ($_REQUEST['act'])
    {
    case 'rand_order' :
        if (isset($_REQUEST['order_type']) && $_REQUEST['order_type'] == 1) {
            $order_type = " AND order_type=1 ";
        } else {
            $order_type = " AND order_type<>1 ";
        }
        if ($_REQUEST['a'] == 'verify') {
            $table_order = 'ordersyn_info';
            $table_user  = 'userssyn';
            $where       = " AND order_status=0 AND o.admin_id=0 AND o.order_lock={$_SESSION['admin_id']} AND o.lock_timeout>$now_time $order_type";
            $temp_fields = " ,o.order_lock, IF(lock_timeout<$now_time,'锁定','已锁定') lock_status";
            $sort_by = ' o.pay_id DESC, o.add_time ASC ';
        } elseif ($_REQUEST['a'] == 'print') {
            $table_order = 'order_info';
            $table_user  = 'users';
            $order_status = ' AND order_status=1 AND shipping_status=0 AND a.user_id=o.add_admin_id AND '.
                " s.shipping_id=o.shipping_id AND o.order_lock={$_SESSION['admin_id']} AND o.lock_timeout>$now_time $order_type";
            $table_admin = ','.$GLOBALS['ecs']->table('admin_user').' a, '.$GLOBALS['ecs']->table('shipping').' s ';
            $temp_fields = ',o.review,a.user_name add_admin,s.shipping_name ';
            $sort_by = ' o.add_time ASC ';
            if (admin_priv('order_view', '', false)) {
            } elseif (admin_priv('order_part_view', '', false)) {
                $order_status .= " AND o.platform IN ($role_list_str) ";
            } else {
                $order_status .= " AND o.add_admin_id={$_SESSION['admin_id']} ";
            }
        }
        break;
    case 'temp_order' : // 新顾客未确认订单
        $table_order = 'ordersyn_info';
        $table_user  = 'userssyn';
        $where       = ' AND o.order_status=0 AND o.admin_id=0 AND o.order_type<>1';
        $temp_fields = " ,o.order_lock, IF(lock_timeout<$now_time,'锁定','已锁定') lock_status";
        $sort_by = ' o.add_time ASC ';
        break;
    case 'history_users_order' : // 老顾客未确认订单
        $table_order  = 'ordersyn_info';
        $table_user   = 'users';
        if (admin_priv('order_view', '', false)) {
            $order_status = ' AND order_status=0 AND o.admin_id>0 ';
        } elseif (admin_priv('order_part_view', '', false)) {
            $order_status = " AND order_status=0 AND o.platform IN ($role_list_str) ";
        } elseif (admin_priv('order_group_view', '', false)) {
            $order_status = ' AND order_status=0 AND o.admin_id>0 AND o.group_id='.$_SESSION['group_id'];
            if (isset($_REQUEST['admin_id']) && intval($_REQUEST['admin_id'])) {
                $order_status = ' AND order_status=0 AND o.admin_id='.intval($_REQUEST['admin_id']);
            }
        } else {
            $order_status = ' AND order_status=0 AND o.admin_id='.$_SESSION['admin_id'];
        }
        $temp_fields  = " ,o.order_lock, IF(lock_timeout<$now_time,'锁定','已锁定') lock_status";
        $sort_by = ' o.add_time ASC ';
        break;
    case 'current_order' : // 待发货订单
        $table_order = 'order_info';
        $table_user  = 'users';
        $order_status = ' AND order_status=1 AND shipping_status=0 AND a.user_id=o.add_admin_id AND s.shipping_id=o.shipping_id ';
        $table_admin = ' LEFT JOIN '.$GLOBALS['ecs']->table('memship_number').' m ON o.user_id=m.user_id,'.$GLOBALS['ecs']->table('admin_user').' a, '.$GLOBALS['ecs']->table('shipping').' s ';
        $temp_fields = ',o.review,a.user_name add_admin,s.shipping_name,m.card_number ';
        $sort_by = ' o.goods_kind DESC, o.goods_num ASC ';
        if (isset($_REQUEST['goods_kind']) && intval($_REQUEST['goods_kind'])) {
            $order_status .= 'AND o.goods_kind='.intval($_REQUEST['goods_kind']);
        }
        if (admin_priv('order_view', '', false)) {
            $auth = '';
        } elseif (admin_priv('order_part_view', '', false)) {
            $auth = " AND o.platform IN ($role_list_str) ";
        } elseif (admin_priv('order_group_view', '', false)) {
            $auth = " AND o.group_id={$_SESSION['group_id']} ";
            if (isset($_REQUEST['admin_id']) && intval($_REQUEST['admin_id'])) {
                $auth = ' AND o.admin_id='.intval($_REQUEST['admin_id']);
            }
        } else {
            $auth = " AND o.add_admin_id={$_SESSION['admin_id']} ";
        }
        $order_status .= $auth;
        break;
    case 'history_order' : // 已发货订单
        $table_order = 'order_info';
        $table_user  = 'users';
        $order_status = " AND a.user_id=o.add_admin_id AND o.order_status=5 AND o.shipping_status=1 ";
        $table_admin = ','.$GLOBALS['ecs']->table('admin_user').' a ';
        $temp_fields = ',o.exp_info,o.review,a.user_name add_admin';
        $sort_by = ' o.shipping_time DESC';
        if (admin_priv('order_view', '', false)) {
            $auth = '';
        } elseif (admin_priv('order_part_view', '', false)) {
            $auth = " AND u.role_id IN ($role_list_str) "; //如果以顾客所属部门检索就是u.role_id如果以订单平台检索就是o.platform
        } elseif (admin_priv('order_group_view', '', false)) {
            $auth = " AND u.group_id={$_SESSION['group_id']} ";
            if (isset($_REQUEST['admin_id']) && intval($_REQUEST['admin_id'])) {
                $auth = ' AND u.admin_id='.intval($_REQUEST['admin_id']);
            }
        } else {
            $auth = " AND u.admin_id={$_SESSION['admin_id']} ";
        }
        $order_status .= $auth;
        break;
    case 'finished_order' : // 已签收的订单
        $table_order = 'order_info';
        $table_user  = 'users';
        $order_status = " AND a.user_id=o.add_admin_id AND o.order_status=5 AND o.shipping_status=2 ";
        $table_admin = ','.$GLOBALS['ecs']->table('admin_user').' a ';
        $temp_fields = ',o.review,a.user_name add_admin';
        $sort_by = ' o.shipping_time DESC';
        if (admin_priv('order_view', '', false)) {
            $auth = '';
        } elseif (admin_priv('order_part_view', '', false)) {
            $auth = " AND u.role_id IN ($role_list_str) ";  //如果以顾客所属部门检索就是u.role_id如果以订单平台检索就是o.platform
        } elseif (admin_priv('order_group_view', '', false)) {
            $auth = " AND u.group_id={$_SESSION['group_id']} ";
            if (isset($_REQUEST['admin_id']) && intval($_REQUEST['admin_id'])) {
                $auth = ' AND u.admin_id='.intval($_REQUEST['admin_id']);
            }
        } else {
            $auth = " AND u.admin_id={$_SESSION['admin_id']} ";
        }
        $order_status .= $auth;
        break;
    case 'returned_list' : // 退货的订单
        $table_order = 'order_info';
        $table_user  = 'users';
        $order_status = " AND a.user_id=o.add_admin_id AND o.order_status=5 AND o.shipping_status=4 AND re.order_id=o.order_id ";
        $table_admin = ','.$GLOBALS['ecs']->table('admin_user').' a, '.$GLOBALS['ecs']->table('returns_order').' re';
        $temp_fields = ',o.review,a.user_name add_admin,FROM_UNIXTIME(re.return_time, "%Y-%m-%d %H:%i") return_time';
        $sort_by = ' o.shipping_time DESC';
        if (admin_priv('order_view', '', false)) {
            $auth = '';
        } elseif (admin_priv('order_part_view', '', false)) {
            $auth = " AND u.role_id IN ($role_list_str) ";  //如果以顾客所属部门检索就是u.role_id如果以订单平台检索就是o.platform
        } elseif (admin_priv('order_group_view', '', false)) {
            $auth = " AND u.group_id={$_SESSION['group_id']} ";
            if (isset($_REQUEST['admin_id']) && intval($_REQUEST['admin_id'])) {
                $auth = ' AND u.admin_id='.intval($_REQUEST['admin_id']);
            }
        } else {
            $auth = " AND u.admin_id={$_SESSION['admin_id']} ";
        }
        $order_status .= $auth;
        break;
    case 'canceled_list' : // 取消的订单
        $table_order = 'order_info';
        $table_user  = 'users';
        $order_status = " AND a.user_id=o.add_admin_id AND o.shipping_status=3 ";
        $table_admin = ','.$GLOBALS['ecs']->table('admin_user').' a ';
        $temp_fields = ',o.review,a.user_name add_admin';
        $sort_by = ' o.shipping_time DESC';
        if (admin_priv('order_view', '', false)) {
            $auth = '';
        } elseif (admin_priv('order_part_view', '', false)) {
            $auth = " AND u.role_id IN ($role_list_str) ";  //如果以顾客所属部门检索就是u.role_id如果以订单平台检索就是o.platform
        } elseif (admin_priv('order_group_view', '', false)) {
            $auth = " AND u.group_id={$_SESSION['group_id']} ";
            if (isset($_REQUEST['admin_id']) && intval($_REQUEST['admin_id'])) {
                $auth = ' AND u.admin_id='.intval($_REQUEST['admin_id']);
            }
        } else {
            $auth = " AND u.admin_id={$_SESSION['admin_id']} ";
        }
        $order_status .= $auth;
        break;
    case 'order_before_transfer' :
        $table_order = 'order_info';
        $table_user  = 'users';
        $order_status = " AND u.admin_id=a.user_id AND o.order_status=5 AND o.shipping_status IN (1,2) AND u.admin_id={$_SESSION['admin_id']}";
        $table_admin = ','.$GLOBALS['ecs']->table('admin_user').' a ';
        $temp_fields = ',o.review,a.user_name add_admin';
        $sort_by = ' o.shipping_time DESC';
        break;
    case 'flush_order' :
        $table_order = 'ordersyn_info';
        $table_user  = 'userssyn';
        $where       = ' AND o.order_status=0 AND o.order_type=1 AND o.admin_id=0';
        $temp_fields = " ,o.order_lock, IF(lock_timeout<$now_time,'锁定','已锁定') lock_status";
        $sort_by = ' o.add_time ASC ';
        break;
    }

    // 如果是中老年事业部，只列出本部门订单
    if (!admin_priv('all', '', false) && !admin_priv('order_list_all', '', false) && admin_priv('zhln', '', false)) {
        if (admin_priv('order_part_view', '', false)) {
            $order_status .= " AND o.platform IN ($role_list_str) ";
        } else {
            $order_status .= " AND o.platform={$_SESSION['role_id']} ";
        }
    } elseif (admin_priv('member', '', false)){
    } elseif (!admin_priv('order_list_all', '', false)) {
        $order_status .= " AND o.platform IN ($role_list_str) ";
    }

    $result = get_filter();
    if ($result === false) {
        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
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
        $filter['tracking_sn'] = empty($_REQUEST['tracking_sn']) ? '' : trim($_REQUEST['tracking_sn']);

        $filter['shipping_feed_id'] = empty($_REQUEST['shipping_feed_id']) ? 0 : intval($_REQUEST['shipping_feed_id']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : strtotime($_REQUEST['start_time']);
        $filter['end_time']   = empty($_REQUEST['end_time']) ? '' : strtotime($_REQUEST['end_time']);

        $filter['exp_status'] = !isset($_REQUEST['exp_status'])?0:intval($_REQUEST['exp_status']);

        // 订单搜索

        if (! empty($_REQUEST['keywords']))
        {
            $filter['keyfields'] = mysql_real_escape_string(trim($_REQUEST['keyfields']));
            $filter['keywords']  = urldecode(mysql_real_escape_string(trim($_REQUEST['keywords'])));

            $act = array(
                'current_order',
                'history_order',
                'order_before_transfer',
                'finished_order',
            );
            if ($filter['keyfields'] == 'order_sn' && in_array($_REQUEST['act'],$act)) {
                $where .= " AND ({$filter['keyfields']} LIKE '%".mysql_like_quote($filter['keywords']).
                    "%' OR platform_order_sn LIKE '%".mysql_like_quote($filter['keywords'])."%') ";
            } elseif ($filter['keyfields'] == 'aliww') {
                $where .= " AND u.{$filter['keyfields']} LIKE '%".mysql_like_quote($filter['keywords'])."%' ";
            } else {
                $where .= " AND o.{$filter['keyfields']} LIKE '%".mysql_like_quote($filter['keywords'])."%' ";
            }
        }

        // 收集查询条件
        foreach ($filter as $key=>$val)
        {
            if ($key == 'exp_status')
            {
                $condition .= "&$key=$val";
            }

            if (!empty($val))
            {
                $condition .= "&$key=$val";
            }
        }

        isset($_REQUEST['goods_kind']) && $condition .= '&goods_kind='.intval($_REQUEST['goods_kind']);

        $filter['page']         = empty($_REQUEST['page'])         ? 1  : intval($_REQUEST['page']);
        $filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;
        $filter['pay_status']   = isset($_REQUEST['pay_status'])   ? intval($_REQUEST['pay_status'])   : -1;

        $filter['shipping_status'] = isset($_REQUEST['shipping_status'])?intval($_REQUEST['shipping_status']):-1;

        $filter['sort_by']    = empty($_REQUEST['sort_by'])    ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC'     : trim($_REQUEST['sort_order']);

        // 根据平台查询订单
        if ($filter['platform'] && is_int($filter['platform'])) {
            if ($_REQUEST['act'] == 'history_users_order') {
                $where .= " AND o.team={$filter['platform']} ";
            } else {
                $where .= " AND o.platform={$filter['platform']} ";
            }
        }

        // 设置按配送方式查询的条件
        if ($filter['shipping_id']) {
            if (strpos($filter['shipping_id'], ',')) {
                $where .= " AND o.shipping_id NOT IN ({$filter['shipping_id']}) ";
            } else {
                $where .= " AND o.shipping_id={$filter['shipping_id']} ";
            }
        }

        // 客服
        if (admin_priv('all', '', false)) {
            if ($filter['admin_id']) {
                $where .= " AND o.admin_id={$filter['admin_id']} ";
            }
        } elseif (admin_priv('order_part_view', '', false)) {
            if ($filter['admin_id']) {
                $where .= " AND o.admin_id={$filter['admin_id']} ";
            }
        } elseif (admin_priv('section', '', false)) {
            $sql_select_admin = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').
                " WHERE role_id={$_SESSION['role_id']}";
            $admin_id = $GLOBALS['db']->getCol($sql_select_admin);
            if (!empty($admin_id)) {
                $admin_id = implode(',', $admin_id);
                $where .= " AND o.admin_id IN ($admin_id) ";
            }
            if ($filter['admin_id']) {
                $where .= " AND o.admin_id={$filter['admin_id']} ";
            }
        }

        // 订单编号查询订单
        if ($filter['order_sn']) {
            $where .= " AND (o.order_sn LIKE '%".mysql_like_quote($filter['order_sn'])."%'";
        }

        // 运单号查询订单
        if ($filter['tracking_sn']) {
            $where .= " AND o.tracking_sn LIKE '%".mysql_like_quote($filter['tracking_sn'])."%'";
        }

        // 收货人查询订单
        if ($filter['consignee']) {
            $where .= " AND o.consignee LIKE '%".mysql_like_quote($filter['consignee'])."%'";
        }

        // 收货地址查询订单
        if ($filter['address']) {
            $where .= " AND o.address LIKE '%".mysql_like_quote($filter['address'])."%'";
        }

        // 邮编查询订单
        if ($filter['zipcode']) {
            $where .= " AND o.zipcode LIKE '%".mysql_like_quote($filter['zipcode'])."%'";
        }

        // 固话查询订单
        if ($filter['tel']) {
            $where .= " AND o.tel LIKE '%".mysql_like_quote($filter['tel'])."%'";
        }

        // 手机号码查询订单
        if ($filter['mobile']) {
            //$where .= " AND o.mobile LIKE '%".mysql_like_quote($filter['mobile'])."%'";
        }

        // 省份查询订单
        if ($filter['province']) {
            $where .= " AND o.province='$filter[province]'";
        }

        // 城市查询订单
        if ($filter['city']) {
            $where .= " AND o.city='$filter[city]'";
        }

        // 区县查询订单
        if ($filter['district']) {
            $where .= " AND o.district='$filter[district]'";
        }

        // 快递查询订单
        if ($filter['shipping_id']) {
            $where .= " AND o.shipping_id='$filter[shipping_id]'";
        }

        // 支付查询订单
        if ($filter['pay_id']) {
            $where .= " AND o.pay_id='$filter[pay_id]'";
        }

        // 快递追踪
        if ($_REQUEST['act'] == 'history_order' && $filter['exp_status'] !== false) {
            $where .= " AND o.exp_status={$filter['exp_status']} ";
        }

        // 订单状态查询订单
        elseif ($filter['order_status'] != -1 && $filter['shipping_status'] != -1) {
            $where .= " AND o.order_status='{$filter['order_status']}'";
            $where .= " AND o.shipping_status='{$filter['shipping_status']}'";
        }

        // 下单时间
        if ($filter['start_time'] && $filter['end_time']) {
            if ($filter['start_time'] > $filter['end_time']) {
                $time_tmp = $filter['end_time'];
                $filter['end_time'] = $filter['start_time'];
                $filter['start_time'] = $time_tmp;
            }

            $where .= " AND o.add_time BETWEEN '{$filter['start_time']}' AND '{$filter['end_time']}'";
        }

        // 二次跟单
        if ($filter['shipping_feed_id']) {
            $where .= " AND o.shipping_feed_id={$filter['shipping_feed_id']} ";
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } else {
            $filter['page_size'] = 20;
        }

        /* 记录总数 */
        $sql_select = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table($table_order)." o $table_admin,".
            $GLOBALS['ecs']->table($table_user).' u, '.$GLOBALS['ecs']->table('role').
            " r WHERE o.user_id=u.user_id AND r.role_id=o.platform $where $order_status";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);

        $page_set = break_pages($filter['record_count'], $filter['page_size'], $filter['page']);

        /* 查询 */
        $sql_select = 'SELECT SUBSTRING(u.user_name FROM 1 FOR 3) buyer,SUBSTRING(o.consignee FROM 1 FOR 4) consignee,u.aliww,u.qq,u.is_black,'.
            ' r.role_name platform,r.role_describe,o.add_time,o.final_amount,o.remarks,o.admin_name,o.order_amount,o.money_paid,o.pay_id,'.
            ' CONCAT(o.order_status,o.shipping_status,o.pay_status) order_status,o.shipping_status, o.order_id, SUBSTR(o.order_sn,-8,8) '.
            ' order_sn,o.tracking_sn,o.shipping_code,r.role_describe,o.order_type,o.order_lock,o.shipping_feed_id '." $temp_fields FROM ".
            $GLOBALS['ecs']->table($table_order)." o $table_admin,".$GLOBALS['ecs']->table($table_user).' u, '.$GLOBALS['ecs']->table('role').
            " r WHERE o.user_id=u.user_id AND r.role_id=o.platform $where $order_status ".
            " ORDER BY $sort_by LIMIT ".($filter['page'] -1)*$filter['page_size'].",{$filter['page_size']}";
        set_filter($filter, $sql_select);
    } else {
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

        if($val['is_black'] == 1){
            $val['buyer'] = '<font class="font_blacklist">'.$val['buyer'].'</font>';
        }

        // 订单状态
        switch (@$val['order_status'])
        {
        case 000 : $value['order_status'] = '待确认'; break;
        case 102 : ;
        case 101 : $value['order_status'] = '待发货'; break;
        case 512 : $value['order_status'] = '已发货'; break;
        case 522 : $value['order_status'] = '已收货'; break;
        case 532 : unset($row[$key]); // 已申请退货订单 不显示在该列表中
        default :  $value['order_status'] = '无效订单'; break;
        }

        // 跟单情况
        if ($val['shipping_feed_id']) {
            switch ($val['shipping_feed_id']) {
            case 1 :
                $val['consignee'] = '<font style="color:#00a0ff"><strong>'.$val['consignee'].'</strong></font>';
                $val['receive_notice'] = '其它';
                break;
            case 2 :
                $val['consignee'] = '<font style="color:indianred"><strong>'.$val['consignee'].'</strong></font>';
                $val['receive_notice'] = '尚未收到';
                break;
            case 3 :
                $val['consignee'] = '<font style="color:#ff00ff"><strong>'.$val['consignee'].'</strong></font>';
                $val['receive_notice'] = '超区派送';
                break;
            case 4 :
                $val['consignee'] = '<font style="color:#ff0000"><strong>'.$val['consignee'].'</strong></font>';
                $val['receive_notice'] = '拒收';
                break;
            }
        }

        // 订单类型
        if ($val['order_type'] != 4 && $val['order_type'])
        {
            $val['admin_name'] = $type_list[$val['order_type']];
        }

        // 宅急送code处理
        $shipping_code = array (
            'ems'         => 'ems',
            'ems2'        => 'ems',
            'sto_express' => 'zjs',
            'sto_nopay'   => 'zjs',
            'zto'         => 'zhongtong',
            'sto'         => 'shentong',
            'yto'         => 'yuantong',
            'sf'          => 'shunfeng',
            'emssn'       => 'ems',
            'sf2'         => 'shunfeng',
            'yunda'       => 'yunda',
        );

        if (isset($shipping_code[$val['shipping_code']])) {
            $val['shipping_code'] = @$shipping_code[$val['shipping_code']];
        }
    }

    return array (
        'orders'       => $row,
        'filter'       => $filter,
        'page_count'   => $page_set['page_count'],
        'record_count' => $filter['record_count'],
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page'],
        'page_set'     => $page_set['page_set'],
        'condition'    => $condition,
        'start'        => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'          => $filter['page']*$filter['page_size'],
    );
}

/**
 * 异常订单列表
 */
function abnormal_order_list ()
{
    // 获取条件数据
    $now_time = time();
    $condition = '';
    $filter['o']             = isset($_REQUEST['o'])          ? intval($_REQUEST['o'])        : 0;
    $filter['free_shipping'] = isset($_REQUEST['free_shipping'])   ? intval($_REQUEST['free_shipping']) : 0;
    $filter['free_ems']      = isset($_REQUEST['free_ems'])   ? intval($_REQUEST['free_ems']) : 0;
    $filter['gift']          = isset($_REQUEST['gift'])       ? intval($_REQUEST['gift']) : 0;
    $filter['start_time']    = isset($_REQUEST['start_time']) ? strtotime(stamp2date($_REQUEST['start_time'], 'Y-m-d H:i:s')) : strtotime(date('Y-m-01 00:00', $now_time)) -6*3600;
    $filter['end_time']      = isset($_REQUEST['end_time'])   ? strtotime(stamp2date($_REQUEST['end_time'], 'Y-m-d H:i:s')) : $now_time;

    // 收集查询条件
    foreach ($filter as $key=>$val) {
        if (!empty($val)) {
            $condition .= "&$key=$val";
        }
    }

    // 设置查询的条件
    $query = array (
        'o' => ' final_amount=0 ',
        'f' => ' (shipping_fee=0 AND final_amount<=50) ',
        'e' => ' (shipping_id=19 AND shipping_fee=0) ',
        'g' => ' ( is_gift=0 AND goods_price=0 ) ',
    );

    // 0元订单
    if (isset($_REQUEST['o']) && $_REQUEST['o'])
    {
        $sql_where[] = $query['o'];
    }

    // 50元包邮
    elseif (isset($_REQUEST['free_shipping']) && $_REQUEST['free_shipping'])
    {
        $sql_where[] = $query['f'];
    }

    // EMS到付 免运费
    elseif (isset($_REQUEST['free_ems']) && $_REQUEST['free_ems'])
    {
        $sql_where[] = $query['e'];
    }

    // 送非赠品订单
    elseif (isset($_REQUEST['gift']) && $_REQUEST['gift'])
    {
        $sql_where[] = $query['g'];
    }
    else {
        $sql_where = $query;
    }

    // 构造基础查询条件
    if (count($sql_where))
    {
        $sql_where = ' AND ('.implode(' OR ', $sql_where).') ';
    }

    // 设置查询权限
    if (!admin_priv('abnormal_list_all', '', false)){
        $sql_where .= " AND add_admin_id={$_SESSION['admin_id']} ";
    }

    // 统计时间
    $sql_where .= " AND o.add_time BETWEEN {$filter['start_time']} AND {$filter['end_time']} AND order_status IN (1,5) AND shipping_status IN (0,1,2) ";

    // 统计记录总数
    $sql_select_count = 'SELECT DISTINCT o.order_id FROM '.$GLOBALS['ecs']->table('order_info').' o, '.
        $GLOBALS['ecs']->table('users').' u, '.$GLOBALS['ecs']->table('admin_user').' a, '.$GLOBALS['ecs']->table('order_goods').
        " g WHERE o.admin_id=a.user_id AND o.user_id=u.user_id AND o.order_id=g.order_id $sql_where";
    $order_id = $GLOBALS['db']->getCol($sql_select_count);
    $filter['record_count'] = count($order_id);

    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    else
    {
        $filter['page_size'] = 15;
    }

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

    $sql_select = 'SELECT o.*,a.user_name add_admin,u.user_name buyer,u.aliww,u.qq,r.role_describe FROM '.
        $GLOBALS['ecs']->table('order_info').' o, '.$GLOBALS['ecs']->table('users').' u, '.
        $GLOBALS['ecs']->table('order_goods').' g, '.$GLOBALS['ecs']->table('admin_user').' a, '.
        $GLOBALS['ecs']->table('role').' r WHERE o.admin_id=a.user_id AND o.user_id=u.user_id '.
        ' AND o.order_id=g.order_id AND r.role_id=o.platform '."$sql_where GROUP BY g.order_id ORDER BY o.add_time DESC LIMIT ".
        ($filter['page'] -1)*$filter['page_size'].",{$filter['page_size']}";
    $res = $GLOBALS['db']->getAll($sql_select);


    foreach ($res as &$val)
    {
        $val['formated_order_amount'] = price_format($val['order_amount']);
        $val['formated_money_paid']   = price_format($val['money_paid']);
        $val['formated_total_fee']    = price_format($val['final_amount']);
        $val['short_order_time']      = date('Y-m-d H:i', $val['add_time']);
    }

    $arr= array (
        'orders'       => $res,
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


/**
 * 获取订单详细信息
 * @param $id int   订单ID
 */
function get_order_detail($id, $table) {
    if (!is_numeric($id)) {
        return false;
    }

    if ($table == 'order_info') {
        $sql='SELECT t.*,IFNULL(t.platform_order_sn,t.order_sn)order_sn,p.region_name province,c.region_name city,d.region_name district, r.role_name sale_platform FROM ';
    } else {
        $sql = 'SELECT t.*, p.region_name province, c.region_name city, d.region_name district, r.role_name sale_platform FROM ';
    }

    $sql .= $GLOBALS['ecs']->table($table).' t LEFT JOIN '.$GLOBALS['ecs']->table('region').
        ' p ON p.region_id=t.province LEFT JOIN '.$GLOBALS['ecs']->table('region').' c ON c.region_id=t.city LEFT JOIN '.
        $GLOBALS['ecs']->table('region').' d ON d.region_id=t.district LEFT JOIN '.$GLOBALS['ecs']->table('role').
        " r ON t.team=r.role_id WHERE order_id=$id";
    $order_info = $GLOBALS['db']->getAll($sql);

    $order_info[0]['address'] = mb_chunk_split($order_info[0]['address'], 20, '<br/>');

    return $order_info[0];
}

/*查看是否是黑名单*/
function in_blacklist($id,$order_table){
    $order_table = empty($order_table) ? 'order_info' : $order_table;
    $user_table  = $order_table == 'order_info' ? 'users' : 'userssyn';
    $is_black    = 0;

    $sql_select = 'SELECT u.user_id,u.is_black,u.user_name,o.consignee,u.mobile_phone FROM '.
        $GLOBALS['ecs']->table($user_table).' u LEFT JOIN '.$GLOBALS['ecs']->table($order_table).
        " o ON o.user_id=u.user_id WHERE o.order_id=$id ";

    $blacklist_info = $GLOBALS['db']->getRow($sql_select);

    /*如果已经疑是黑名单顾客 */
    if($blacklist_info['is_black']){
        $field = '';
        /*该顾客黑名单已警报解除*/
        if($blacklist_info['is_black'] == 4){
            $field = ',u.mod_reason,a.user_name as ignore_admin';
        }

        $sql_select = "SELECT u.user_id,u.reason,IFNULL(u.from_table,'users') from_table,t.type_name $field FROM ".$GLOBALS['ecs']->table('user_blacklist').
            ' u LEFT JOIN '.$GLOBALS['ecs']->table('blacklist_type').' t ON u.type_id=t.type_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
            ' a ON a.user_id=u.ignore_admin '.
            " WHERE u.status NOT IN (0,1) AND u.user_id={$blacklist_info['user_id']} AND u.from_table='$user_table'";

        $blacklist = $GLOBALS['db']->getRow($sql_select);
        if(!$blacklist){
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_blacklist').
                '(user_id,user_name,status,in_time,type_id,reason,from_table)VALUES('.
                sprintf("%d,'%s',2,%d,4,'%s','%s'",$blacklist_info['user_id'],$blacklist_info['user_name'],$_SERVER['REQUEST_TIME'],'疑是网络骗子',$user_table).')';
            $GLOBALS['db']->query($sql_insert);

            $blacklist_info = $GLOBALS['db']->getRow($sql_select." AND blacklist_id=".$GLOBALS['db']->insert_id());
        }

        $blacklist['is_black'] = $blacklist_info['is_black'];
        return $blacklist;
    }elseif($blacklist_info && (!$blacklist_info['is_black'])){
        //是否疑似网络诈骗
        foreach($blacklist_info as $key=>&$val){
            if(empty($val) && $key != 'is_black'){
                $blacklist_info[$key] = 'error_null';
            }
        }

        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('network_blacklist').
            " WHERE mobile LIKE '%{$blacklist_info['mobile_phone']}%'";
        //" WHERE user_name LIKE '%{$blacklist_info['user_name']}%' OR user_name LIKE '%{$blacklist_info['consignee']}%' OR mobile LIKE '%{$blacklist_info['mobile_phone']}%'";
        $result   = $GLOBALS['db']->getOne($sql_select);
        $is_black = $result>0 ? 3 : 0;

        /*疑似网络骗子*/
        if($is_black){
            $sql_select = 'SELECT status FROM '.$GLOBALS['ecs']->table('user_blacklist').
                " WHERE user_id={$blacklist_info['user_id']} AND from_table='$user_table'";
            $status = $GLOBALS['db']->getOne($sql_select);

            if(!$status){
                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_blacklist').
                    '(user_id,user_name,status,in_time,type_id,reason,from_table)VALUES('.
                    sprintf("%d,'%s',2,%d,4,'%s','%s'",$blacklist_info['user_id'],$blacklist_info['user_name'],$_SERVER['REQUEST_TIME'],'疑是网络骗子',$user_table).')';
                $GLOBALS['db']->query($sql_insert);
            }else{
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_blacklist').
                    " SET status=2 WHERE user_id={$blacklist_info['user_id']}";
                $GLOBALS['db']->query($sql_update);
            }

            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table($user_table)." SET is_black=3 WHERE user_id={$blacklist_info['user_id']}";
            $GLOBALS['db']->query($sql_update);

            $blacklist_info['reason']     = '疑是网络骗子';
            $blacklist_info['type_name']  = '网络黑名单';
            $blacklist_info['is_black']   = 3;
            $blacklist_info['from_table'] = $user_table;
        }
    }

    return $blacklist_info;
}

/**
 * 获取订单中的商品列表
 * @param $id          int     订单ID
 * @param $table_name  string  表名
 */
function goods_list ($id, $table_name)
{
    $is_gift = array(
        1 => '赠品',
        2 => '活动',
        3 => '补发',
        4 => '员工'
    );

    if (!is_numeric($id)) {
        return false;
    }

    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table($table_name)." WHERE order_id=$id";
    $goods_list = $GLOBALS['db']->getAll($sql);
    foreach ($goods_list as &$val)
    {
        if ($val['is_package'] == 1)
        {
            $sql_select = 'SELECT goods_name,num FROM '.$GLOBALS['ecs']->table('packing_goods').' pg, '.
                $GLOBALS['ecs']->table('packing')." p WHERE p.packing_id=pg.packing_id AND p.packing_desc='{$val['goods_sn']}'";
            $val['packing_goods_list'] = $GLOBALS['db']->getAll($sql_select);
        }

        $val['is_gift'] = $is_gift[$val['is_gift']];
    }

    return $goods_list;
}

/**
 * 将字符串转成数组
 * @param  $str  将被转成数组的字符串
 */
function mbStringToArray ($str)
{
    if (empty($str)) return false;
    $len = mb_strlen($str, 'utf-8');
    $array = array();

    for ($i = 0; $i < $len; $i++)
    {
        $array[] = mb_substr($str, $i, 1, 'utf-8');
    }

    return $array;
}

/**
 * 在中文字符串中插入字符
 * @param $str  字符串
 * @param $len  长度
 * @param $glue 要填充的字符
 */
function mb_chunk_split($str, $len, $glue)
{
    if (empty($str)) return false;
    $array = mbStringToArray ($str);
    $n = -1;
    $new = '';

    foreach ($array as $char)
    {
        $n++;
        if ($n < $len) $new .= $char;
        elseif ($n == $len)
        {
            $new .= $glue . $char;
            $n = 0;
        }
    }

    return $new;
}

/**
 * 确认库存是否满足当前提交的订单商品
 */
function enough_storage($goods_info)
{
    $goods = array();
    foreach ($goods_info as $val_merge){
        $goods[$val_merge['goods_sn']] += $val['goods_number'];
    }

    $sql_select = 'SELECT SUM(quantity) quantity,goods_name FROM '.
        $GLOBALS['ecs']->table('stock_goods')." WHERE goods_sn IN ('%s') GROUP BY goods_sn";
    foreach ($goods as $key=>$val) {
        if (!is_numeric($key)) {
            $package_goods_list = explode_package($key);

            $package = array();
            foreach ($package_goods_list as $merge_goods) {
                $package[$merge_goods['goods_sn']] += $merge_goods['goods_number']*$val['goods_number'];
            }

            $storage = $GLOBALS['db']->getAll(sprintf($sql_select, implode(',', array_keys($package))));
            foreach ($package as $k_need=>$v_need){
                $shortage = $storage[$k_need]['quantity'] - $v_need;
                if ($shortage < 0) {
                    return array('goods_name' => $storage['goods_name'], 'shortage' => abs($shortage));
                }
            }
        } else {
            $storage = $GLOBALS['db']->getRow(sprintf($sql_select, $key));

            $shortage = $storage['quantity'] - $val['goods_number'];
            if ($shortage < 0) {
                return array('goods_name' => $storage['goods_name'], 'shortage' => abs($shortage));
            }
        }
    }

    return true;
}


/**
 * 发货时减去库存数量
 * @param   int       $order_id   订单号
 * return   boolean   执行结果
 */
function handle_storage ($order_id) {
    /* 获取订单商品表中的商品货号及数量 */
    $sql = 'SELECT goods_sn,goods_number,goods_name,rec_id,is_package,stock FROM '.
        $GLOBALS['ecs']->table('order_goods')." WHERE order_id=$order_id AND stock=''";
    $goods_list = $GLOBALS['db']->getAll($sql);
    if (empty($goods_list)) {
        return array('update_status' => true);
    }

    $stock_record = array();  // 用到的库存记录
    foreach ($goods_list as $val) {
        // 如果商品是套餐 则分解套餐为单一商品
        if ($val['is_package'] == 1 || !is_numeric($val['goods_sn'])) {
            $package_goods_list = explode_package($val['goods_sn']);
            foreach ($package_goods_list as $v){
                $handle_step = handle_storage_step($v, $val);
                if ($handle_step['update_status']) {
                    if (isset($stock_record[$val['rec_id']])) {
                        $stock_record[$val['rec_id']] += $handle_step['stock_record'];
                    } else {
                        $stock_record[$val['rec_id']] = $handle_step['stock_record'];
                    }
                } else {
                    return $handle_step;
                }
            }
        } else {
            $handle_step = handle_storage_step($val);
            if ($handle_step['update_status']) {
                $stock_record[$val['rec_id']] = $handle_step['stock_record'];
            } else {
                return $handle_step;
            }
        }
    }
    unset($val);

    // 将使用到的库存记录保存到相应订单的商品记录中
    foreach ($stock_record as $key=>$val){
        $single_goods = array();
        foreach ($val as $k=>$v){
            $single_goods[] = array ('rec_id' => $k, 'quantity' => $v);
        }

        $single_goods_string = serialize($single_goods);
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_goods')." SET stock='$single_goods_string' WHERE rec_id=$key";
        $GLOBALS['db']->query($sql_update);
    }

    return array('update_status' => true);
}

/**
 * 判断订单是否已发货（含部分发货）
 * @param   int     $order_id  订单 id
 * @return  int     1，已发货；0，未发货
 */
function order_deliveryed($order_id)
{
    $return_res = 0;

    if (empty($order_id))
    {
        return $return_res;
    }

    $sql = 'SELECT COUNT(delivery_id) FROM '.$GLOBALS['ecs']->table('delivery_order').
        ' WHERE order_id=\''.$order_id.'\' AND status = 0';
    $sum = $GLOBALS['db']->getOne($sql);

    if ($sum) {
        $return_res = 1;
    }

    return $return_res;
}

/**
 * 返回某个订单可执行的操作列表，包括权限判断
 * @param   array   $order      订单信息 order_status, shipping_status, pay_status
 * @param   bool    $is_cod     支付方式是否货到付款
 * @return  array   可执行的操作  confirm, pay, unpay, prepare, ship, unship, receive, cancel, invalid, return, drop
 * 格式 array('confirm' => true, 'pay' => true)
 */
function operable_list($order)
{
    /* 取得订单状态、发货状态、付款状态 */
    $os = $order['order_status'];
    $ss = $order['shipping_status'];
    $ps = $order['pay_status'];
    /* 取得订单操作权限 */
    $actions = $_SESSION['action_list'];
    if ($actions == 'all')
    {
        $priv_list  = array('os' => true, 'ss' => true, 'ps' => true, 'edit' => true);
    }
    else
    {
        $actions    = ',' . $actions . ',';
        $priv_list  = array(
            'os'    => strpos($actions, ',order_edit,') !== false,
            'ss'    => strpos($actions, ',order_edit,') !== false,
            'ps'    => strpos($actions, ',order_edit,') !== false,
            'edit'  => strpos($actions, ',order_edit,') !== false
        );
    }

    /* 取得订单支付方式是否货到付款 */
    $payment = payment_info($order['pay_id']);
    $is_cod  = $payment['is_cod'] == 1;

    /* 根据状态返回可执行操作 */
    $list = array();
    if (OS_UNCONFIRMED == $os)
    {
        /* 状态：未确认 => 未付款、未发货 */
        if ($priv_list['os'])
        {
            $list['confirm']    = true; // 确认
            $list['invalid']    = true; // 无效
            $list['cancel']     = true; // 取消
            if ($is_cod)
            {
                /* 货到付款 */
                if ($priv_list['ss'])
                {
                    $list['prepare'] = true; // 配货
                    $list['split'] = true; // 分单
                }
            }
            else
            {
                /* 不是货到付款 */
                if ($priv_list['ps'])
                {
                    $list['pay'] = true;  // 付款
                }
            }
        }
    }
    elseif (OS_CONFIRMED == $os || OS_SPLITED == $os || OS_SPLITING_PART == $os)
    {
        /* 状态：已确认 */
        if (PS_UNPAYED == $ps)
        {
            /* 状态：已确认、未付款 */
            if (SS_UNSHIPPED == $ss || SS_PREPARING == $ss)
            {
                /* 状态：已确认、未付款、未发货（或配货中） */
                if ($priv_list['os'])
                {
                    $list['cancel'] = true; // 取消
                    $list['invalid'] = true; // 无效
                }
                if ($is_cod)
                {
                    /* 货到付款 */
                    if ($priv_list['ss'])
                    {
                        if (SS_UNSHIPPED == $ss)
                        {
                            $list['prepare'] = true; // 配货
                        }
                        $list['split'] = true; // 分单
                    }
                }
                else
                {
                    /* 不是货到付款 */
                    if ($priv_list['ps'])
                    {
                        $list['pay'] = true; // 付款
                    }
                }
            }
            /* 状态：已确认、未付款、发货中 */
            elseif (SS_SHIPPED_ING == $ss || SS_SHIPPED_PART == $ss)
            {
                // 部分分单
                if (OS_SPLITING_PART == $os)
                {
                    $list['split'] = true; // 分单
                }
                $list['to_delivery'] = true; // 去发货
            }
            else
            {
                /* 状态：已确认、未付款、已发货或已收货 => 货到付款 */
                if ($priv_list['ps'])
                {
                    $list['pay'] = true; // 付款
                }
                if ($priv_list['ss'])
                {
                    if (SS_SHIPPED == $ss)
                    {
                        $list['receive'] = true; // 收货确认
                    }
                    $list['unship'] = true; // 设为未发货
                    if ($priv_list['os'])
                    {
                        $list['return'] = true; // 退货
                    }
                }
            }
        }
        else
        {
            /* 状态：已确认、已付款和付款中 */
            if (SS_UNSHIPPED == $ss || SS_PREPARING == $ss)
            {
                /* 状态：已确认、已付款和付款中、未发货（配货中） => 不是货到付款 */
                if ($priv_list['ss'])
                {
                    if (SS_UNSHIPPED == $ss)
                    {
                        $list['prepare'] = true; // 配货
                    }
                    $list['split'] = true; // 分单
                }
                if ($priv_list['ps'])
                {
                    $list['unpay'] = true; // 设为未付款
                    if ($priv_list['os'])
                    {
                        $list['cancel'] = true; // 取消
                    }
                }
            }
            /* 状态：已确认、未付款、发货中 */
            elseif (SS_SHIPPED_ING == $ss || SS_SHIPPED_PART == $ss)
            {
                // 部分分单
                if (OS_SPLITING_PART == $os)
                {
                    $list['split'] = true; // 分单
                }
                $list['to_delivery'] = true; // 去发货
            }
            else
            {
                /* 状态：已确认、已付款和付款中、已发货或已收货 */
                if ($priv_list['ss'])
                {
                    if (SS_SHIPPED == $ss)
                    {
                        $list['receive'] = true; // 收货确认
                    }
                    if (!$is_cod)
                    {
                        $list['unship'] = true; // 设为未发货
                    }
                }
                if ($priv_list['ps'] && $is_cod)
                {
                    $list['unpay']  = true; // 设为未付款
                }
                if ($priv_list['os'] && $priv_list['ss'] && $priv_list['ps'])
                {
                    $list['return'] = true; // 退货（包括退款）
                }
            }
        }
    }
    elseif (OS_CANCELED == $os)
    {
        /* 状态：取消 */
        if ($priv_list['os'])
        {
            $list['confirm'] = true;
        }
        if ($priv_list['edit'])
        {
            $list['remove'] = true;
        }
    }
    elseif (OS_INVALID == $os)
    {
        /* 状态：无效 */
        if ($priv_list['os'])
        {
            $list['confirm'] = true;
        }
        if ($priv_list['edit'])
        {
            $list['remove'] = true;
        }
    }
    elseif (OS_RETURNED == $os)
    {
        /* 状态：退货 */
        if ($priv_list['os'])
        {
            $list['confirm'] = true;
        }
    }

    /* 修正发货操作 */
    if (!empty($list['split']))
    {
        /* 如果是团购活动且未处理成功，不能发货 */
        if ($order['extension_code'] == 'group_buy')
        {
            include_once(ROOT_PATH . 'includes/lib_goods.php');
            $group_buy = group_buy_info(intval($order['extension_id']));
            if ($group_buy['status'] != GBS_SUCCEED)
            {
                unset($list['split']);
                unset($list['to_delivery']);
            }
        }

        /* 如果部分发货 不允许 取消 订单 */
        if (order_deliveryed($order['order_id']))
        {
            $list['return'] = true; // 退货（包括退款）
            unset($list['cancel']); // 取消
        }
    }

    /* 售后 */
    $list['after_service'] = true;
    unset($list['return']);

    return $list;
}

/**
 * 金额转中文大写
 * @param  $price float  订单金额
 */
function nature2zh($price)
{
    $arr_big5 = array(
        0 => '零',
        1 => '壹',
        2 => '贰',
        3 => '叁',
        4 => '肆',
        5 => '伍',
        6 => '陆',
        7 => '柒',
        8 => '捌',
        9 => '玖'
    );

    $arr_unit = array(
        2 => '拾',
        3 => '佰',
        4 => '仟',
        5 => '万'
    );

    $temp = explode('.', $price);

    $j = $k = strlen($temp[0]);
    $str = '';
    for ($i = 0; $i < $j; $i++)
    {
        if ($temp[0][$i] > 0)
        {
            $str .= $arr_big5[$temp[0][$i]];
            $k >= 2 && $str .= $arr_unit[$k--];
            $m = 0;
        }
        else if ($m == 0 && $j-$i>1)
        {
            $str .= $arr_big5[$temp[0][$i]];
            $k--;
            $m++;
        }
        else
        {
            $k--;
            continue;
        }
    }

    if (isset($temp[1]) && $temp[1] > 0)
    {
        $str .= '元';
        $temp[1][0] > 0 && $str .= $arr_big5[$temp[1][0]].'角';
        isset($temp[1][1]) && $temp[1][1] > 0 && $str .= $arr_big5[$temp[1][1]].'分';
    }
    else {
        $str .= '元整';
    }

    return $str;
}

/**
 * 获取订单中使用率高的配送方式
 */
function get_higher_rate_shipping ()
{
    // 按订单中配送方式的使用频率获取配送方式列表
    $end_time = strtotime(date('Y-m-d 23:59:59', time()));
    $start_time = $end_time - 30*3600*24;
    $sql_select = 'SELECT shipping_id FROM '.$GLOBALS['ecs']->table('order_info').
        " WHERE add_time>=$start_time AND add_time<$end_time AND order_status IN (1,5) ".
        ' AND shipping_status=0 GROUP BY shipping_id ORDER BY COUNT(*) DESC';
    $res = $GLOBALS['db']->getAll($sql_select);

    $shipping_list = array();
    foreach ($res as $val)
    {
        $shipping_list[] = $val['shipping_id'];
    }

    // 如果订单中用到的快递公司不多于5家
    if (count($shipping_list)>0)
    {
        if (count($shipping_list) < 7)
        {
            $shipping_list = implode(',', $shipping_list);
        }
        else
        {
            $shipping_list = implode(',', array_slice($shipping_list, 0, 7));
        }

        $sql_select = 'SELECT shipping_name,shipping_id FROM '.
            $GLOBALS['ecs']->table('shipping')." WHERE shipping_id IN ($shipping_list)";
        return $GLOBALS['db']->getAll($sql_select);
    }

    return false;
}

/**
 * 订单关闭原因
 */
function order_shut_reason ()
{
    $sql_select = 'SELECT reason_id,reason_name FROM '.$GLOBALS['ecs']->table('order_shutreason').
        ' WHERE enable=1 ORDER BY sort DESC';
    $reason_list = $GLOBALS['db']->getAll($sql_select);

    return $reason_list;
}

/**
 * 获取套餐中的商品列表
 */
function get_packing ($packing_desc)
{
    $sql_select = 'SELECT g.goods_sn,g.goods_name,g.shop_price,pg.num goods_number FROM '.
        $GLOBALS['ecs']->table('packing').' p,'.$GLOBALS['ecs']->table('packing_goods').' pg, '.
        $GLOBALS['ecs']->table('goods')." g WHERE g.goods_id=pg.goods_id AND p.packing_id=pg.packing_id AND p.packing_desc='$packing_desc'";
    $goods_list = $GLOBALS['db']->getAll($sql_select);

    foreach ($goods_list as &$val)
    {
        $val['formated_shop_price'] = $val['shop_price'];
    }

    return $goods_list;
}

/**
 * 确认签收退回的订单
 */
function handle_storage_back()
{
    $order_id = intval($_REQUEST['id']);

    $GLOBALS['db']->query('START TRANSACTION');
    foreach ($goods_list as $val)
    {
        $stock = unserialize($val);
        if (isset($stock['rec_id']))
        {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').
                " SET quantity=quantity+{$stock['quantity']} WHERE rec_id={$stock['rec_id']}";
            $GLOBALS['db']->query($sql_update);
            if (!$GLOBALS['db']->affected_rows())
            {
                $GLOBALS['db']->query('ROLLBACK');
                return '操作失败，请联系技术人员！';
            }
        }
        else
        {
            foreach ($stock as $v)
            {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').
                    " SET quantity=quantity+{$v['quantity']} WHERE rec_id={$v['rec_id']}";
                $GLOBALS['db']->query($sql_update);
                if (!$GLOBALS['db']->affected_rows())
                {
                    $GLOBALS['db']->query('ROLLBACK');
                    return '操作失败，请联系技术人员！';
                }
            }
        }
    }

    $time = time();
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('returns_order').
        " SET arrive_time=$time,confirmed_admin_id={$_SESSION['admin_id']} WHERE order_id=$order_id";
    $GLOBALS['db']->query($sql_update);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').' SET shipping_status=5 '.
        " WHERE shipping_status=4 AND order_id=$order_id";
    $GLOBALS['db']->query($sql_update);

    $GLOBALS['db']->query('COMMIT');
    return true;
}

/**
 * 更新当前商品服用结束的日期
 */
function update_taking_time ()
{
    $sql_select = 'SELECT rec_id,is_package FROM '.$GLOBALS['ecs']->table('order_goods').
        " WHERE order_id={$_REQUEST['id']}";
    $goods_list = $GLOBALS['db']->getAll($sql_select);

    foreach ($goods_list as $val)
    {
        if ($val['is_package'])
        {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('packing').' p,'.$GLOBALS['ecs']->table('order_goods')
                ." o SET o.taking_time=o.goods_number*p.take_days*24*3600 WHERE o.rec_id={$val['rec_id']}";
            $GLOBALS['db']->query($sql_update);
        }
        else
        {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('order_goods').
                " o SET o.taking_time=o.goods_number*g.take_days WHERE o.rec_id={$val['rec_id']}";
            $GLOBALS['db']->query($sql_update);
        }
    }
}

/**
 * 同步标记发货
 */
function shipping_synchro($order_id)
{
    global $json;
    // 获取标记发货所需的相关订单参数
    $sql = 'SELECT IF(platform_order_sn,platform_order_sn,order_sn) order_sn,team,'.
        'tracking_sn,shipping_id,shipping_name,shipping_code,province,shipping_time FROM '.
        $GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
    $order_info = $GLOBALS['db']->getRow($sql);
    $tracking_sn = $order_info['tracking_sn'];

    // 获取快递公司编码
    $sql = 'SELECT company_code, company_name FROM '.$GLOBALS['ecs']->table('shipping').
        " WHERE shipping_id='{$order_info['shipping_id']}'";
    $logistics = $GLOBALS['db']->getRow($sql);

    // 同步发货（淘宝）
    if ($order_info['shipping_time'] && in_array($order_info['team'], array(6,21,22,26))) {
        $platform_path = array (6 => 'taobao', 21 => 'taobao01', 22 => 'taobao02', 26 => 'taobao03');
        require(dirname(__FILE__)."/taobao/order_synchro.php");
        require(dirname(__FILE__)."/{$platform_path[$order_info['team']]}/sk.php");
        $auth = require(dirname(__FILE__)."/{$platform_path[$order_info['team']]}/config.php");

        // 配置淘宝签权参数
        $c = new TopClient;
        $c->appkey    = $auth['appkey'];
        $c->secretKey = $auth['secretKey'];

        // 查询订单当前状态是否符合发货条件
        $req = new TradeFullinfoGetRequest;
        $req->setFields("status");
        $req->setTid($order_info['order_sn']);
        $shipping_able = $c->execute($req, $sk['access_token']);
        $shipping_able = $json->decode($json->encode($shipping_able), true);

        // 订单状态符合发货条件
        if ($shipping_able['trade']['status'] == 'WAIT_SELLER_SEND_GOODS') {
            // 构建标记发货的数据格式
            $req = new LogisticsOfflineSendRequest;
            if ($logistics['company_code'] == 'zjs') {
                $req->setOutSid($tracking_sn);
                $req->setTid($order_info['order_sn']);
                $req->setCompanyCode(strtoupper($logistics['company_code']));
            } elseif ('lbex' == $logistics['company_code']) {
                return true;
                $req->setOutSid($tracking_sn);
                $req->setTid($order_info['order_sn']);
                $req->setCompanyCode('龙邦速递');
            } else {
                $req->setOutSid($tracking_sn);
                $req->setTid($order_info['order_sn']);
                $req->setCompanyCode(strtoupper($logistics['company_code']));
            }
            // 发送发货请求
            $shipping_resp = $c->execute($req, $sk['access_token']);
        }
        // 订单符合修改运单号条件
        elseif ($shipping_able['trade']['status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
            $req = new LogisticsConsignResendRequest;
            $req->setOutSid($tracking_sn);
            $req->setTid(number_format($order_info['order_sn'], 0, '', ''));
            $req->setCompanyCode(strtoupper($logistics['company_code']));
            $shipping_resp = $c->execute($req, $sk['access_token']);
        } elseif ($shipping_able['code'] == 27) {
            $res['message'] = '淘宝授权到期，请联系天猫推广，进行授权后再发货！';
            file_put_contents('taobao.txt', 27);
        } else {
            $res['message'] = '订单状态已改变，不符合发货条件！【天猫商城提示您】';
            $res['shipping_name'] = $order_info['shipping_name'];
            $res['errMsg'] = 1;
        }
        if (!isset($shipping_resp['shipping']['is_success']) || !$shipping_resp['shipping']['is_success']) {
            $res['message'] = $shipping_resp['sub_msg'];
        }
    }

    // 同步发货（拍拍）
    if ($order_info['shipping_time'] && $order_info['team'] == 7)
    {
        if (in_array($order_info['shipping_id'], array (29,12))) {
            return false;
        }

        require_once dirname(__FILE__).'/paipai/PaiPaiOpenApiOauth.php';

        // 加载参数
        $cfg_paipai = require_once dirname(__FILE__).'/paipai/config.php';

        // 配置四项签权参数
        $uin = $cfg_paipai['account'];
        $appOAuthID = $cfg_paipai['appkey'];
        $appOAuthkey = $cfg_paipai['secretKey'];
        $accessToken = $cfg_paipai['sessionKey'];  // 2013-09-17更新，下次更新2014-09-17

        // 实例化拍拍接口
        $sdk = new PaiPaiOpenApiOauth($appOAuthID, $appOAuthkey, $accessToken, $uin);

        // 关闭调试模式
        $sdk->setDebugOn(false);

        // 查询订单当前状态是否符合发货条件
        $sdk->setApiPath("/deal/getDealDetail.xhtml");
        $sdk->setMethod("get");//post
        $sdk->setCharset("utf-8");//gbk
        $sdk->setFormat('json');

        $params = &$sdk->getParams();  //注意，这里使用的是引用，故可以直接使用
        $params['sellerUin'] = $uin;
        $params['dealCode']  = trim($order_info['order_sn']);
        $params['pureData']  = 1;

        $shipping_able = $json->decode($sdk->invoke(), true);

        // 货到付款不需要标记发货
        if ($shipping_able['dealState'] == 'STATE_COD_WAIT_SHIP') {
            return true;
        }

        $propertymask = explode('_', $shipping_able['propertymask']);

        $allow_shipping = array ('DS_WAIT_SELLER_DELIVERY');
        $need_pay = array ('ems','sto_express','sf','emssn');

        if (!in_array($shipping_able['dealState'], $allow_shipping))
        {
            $res['message'] = '【QQ商城提示您】订单状态已改变，不符合发货条件！';
            $res['shipping_name'] = $order_info['shipping_name'];
            $res['errMsg'] = 1;
        }

        if (($shipping_able['dealState']=='STATE_COD_WAIT_SHIP' || (end($propertymask)==2048 && $shipping_able['dealState']=='DS_WAIT_BUYER_PAY')) && !in_array($order_info['shipping_code'], $need_pay))
        {
            $res['message'] = '该订单须使用货到付款的配送方式！！！';
            $res['shipping_name'] = $order_info['shipping_name'];
            $res['errMsg'] = 1;
        }

        // 实例化拍拍接口
        $sdk = new PaiPaiOpenApiOauth($appOAuthID, $appOAuthkey, $accessToken, $uin);

        // 关闭调试模式
        $sdk->setDebugOn(false);

        //需要调用的 接口函数
        $sdk->setApiPath("/deal/sellerConsignDealItem.xhtml");
        $sdk->setMethod("get");//post
        $sdk->setCharset("utf-8");//gbk
        $sdk->setFormat('json');

        // 处理发货相关数据
        if (in_array($order_info['province'], array(6, 3465))) {
            $params['arriveDays'] = 3;
        } elseif (in_array($order_info['shipping_code'], array('sto_express', 'ems2', 'sf'))) {
            $params['arriveDays'] = 7;
        } else {
            $params['arriveDays'] = 5;
        }

        // 配置标记发货所需的参数
        $params = &$sdk->getParams();  //注意，这里使用的是引用，故可以直接使用
        $params['sellerUin']     = $uin;
        $params['dealCode']      = $order_info['order_sn'];
        $params['pureData']      = 1;
        $params['logisticsName'] = $logistics['company_name'];
        $params['logisticsCode'] = $tracking_sn;

        $response = json_decode($sdk->invoke(), true);
        if ($response['errorCode']) {
            $res['message'] = $response['errorMessage'];
        }
    }

    // 同步发货（京东）
    if ($order_info['shipping_time'] && $order_info['team'] == 10) {
        include dirname(__FILE__).'/jingdong/JdClient.php';
        include dirname(__FILE__).'/jingdong/JdException.php';
        include dirname(__FILE__).'/jingdong/request/order/OrderSopOutstorageRequest.php';

        include dirname(__FILE__).'/jingdong/sk.php';
        $auth = include dirname(__FILE__).'/jingdong/config.php';

        $req = new OrderSopOutstorageRequest;
        $req->setOrderId($order_info['order_sn']);
        $req->setWaybill($tracking_sn);

        $sql_select = 'SELECT jd_code FROM '.$GLOBALS['ecs']->table('shipping').
            " WHERE shipping_id={$order_info['shipping_id']}";
        $req->setLogisticsId($GLOBALS['db']->getOne($sql_select));

        $jd = new JdClient;

        $jd->appKey      = $auth['appkey'];     // 京东AppKey
        $jd->appSecret   = $auth['secretKey'];  // 京东AppSecret
        $jd->accessToken = $sk['access_token']; // 京东sessionkey(access_token)
        $jd->timestamp   = date('Y-m-d H:i:s');
        $jd->v = '2.0';

        $resp = $jd->execute($req);
        $resp = json_decode(json_encode($resp), true);

        if ($resp['error_response']['code'])// && $resp['error_response']['code'] != 10400001)
        {
            $res['message'] = $resp['error_response']['zh_desc'].'【京东商城提示您】';
            $res['tracking_sn'] = $order_info['tracking_sn'];
        }
    }

    // 同步发货（1号店）
    if ($order_info['shipping_time'] && $order_info['team'] == 14) {
        include dirname(__FILE__).'/yhd/YhdClient.php';
        include dirname(__FILE__).'/yhd/sk.php';
        include dirname(__FILE__).'/yhd/request/order/OrderLogisticsPushRequest.php';
        include dirname(__FILE__).'/yhd/request/logistics/LogisticsOrderShipmentsUpdateRequest.php';

        $auth = include dirname(__FILE__).'/yhd/config.php';

        $req = new LogisticsOrderShipmentsUpdateRequest;

        // 应用级参数
        $req->setOrderCode($order_info['order_sn']);
        $req->setExpressNbr($tracking_sn);

        $sql_select = 'SELECT 1mall_code FROM '.$GLOBALS['ecs']->table('shipping').
            " WHERE shipping_id={$order_info['shipping_id']}";
        $req->setDeliverySupplierId($GLOBALS['db']->getOne($sql_select));

        $yhdClient = new YhdClient();

        // 系统级参数
        $yhdClient->appkey    = $auth['appkey'];
        $yhdClient->secretKey = $auth['secretKey'];
        $yhdClient->format    = 'json';

        $result = $yhdClient->execute($req, $sk['accessToken']);
        $result = objectsIntoArray($result);

        if ($result['response']['errInfoList']) {
            $req = new OrderDetailGetRequest;

            $req->setOrderCode($order_info['order_sn']);

            $yhdClient = new YhdClient();

            // 系统级参数
            $yhdClient->appkey    = $auth['appkey'];
            $yhdClient->secretKey = $auth['secretKey'];
            $yhdClient->format    = 'json';

            $express = $yhdClient->execute($req, $sk['accessToken']);
            $express = json_decode($express, true);

            if (isset($express['response']['orderInfo']['orderDetail']['merchantExpressNbr']) && $express['response']['orderInfo']['orderDetail']['merchantExpressNbr'] == $tracking_sn) {
            } elseif (isset($express['response']['orderInfo']['orderDetail']['merchantExpressNbr']) && $express['response']['orderInfo']['orderDetail']['merchantExpressNbr'] != $tracking_sn) {
                $res['message'] = '该订单已经在一号店标记发货，所使用的运单号为【'.
                    $express['response']['orderInfo']['orderDetail']['merchantExpressNbr'].'】';
            } else {
                $res['message']     = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
                $res['tracking_sn'] = $order_info['tracking_sn'];
            }
        }
    }

    // 当当同步发货
    if ($order_info['shipping_time'] && $order_info['team'] == 16) {
        require_once('dangdang/ddClient.php');
        $dd = new ddClient(2100001198);

        // 获取商品列表
        $sql_select = 'SELECT IF(platform_order_sn,platform_order_sn,order_sn) order_sn FROM '.
            $GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
        $order_sn = $GLOBALS['db']->getOne($sql_select);
        $params['o'] = $order_sn;
        $goods_info = $dd->execute('POST', $params, 'dangdang.order.details.get');
        $goods_list = array();
        if (isset($goods_info['ItemsList']['ItemInfo']['itemID'])) {
            $goods_list[] = array (
                'goods_sn'     => $goods_info['ItemsList']['ItemInfo']['outerItemID'],
                'goods_number' => $goods_info['ItemsList']['ItemInfo']['orderCount'],
            );
        } else {
            foreach ($goods_info['ItemsList']['ItemInfo'] as $v) {
                $goods_list[] = array (
                    'goods_sn'     => $goods_info['ItemsList']['ItemInfo']['outerItemID'],
                    'goods_number' => $goods_info['ItemsList']['ItemInfo']['orderCount'],
                );
            }
        }
        unset($val);
        foreach ($goods_list as &$val){
            $params['oit'] = $val['goods_sn'];
            $resp = $dd->execute('POST', $params, 'dangdang.item.itemid.get');
            $val['goods_sn'] = $resp['Result']['itemID'];
        }
        unset($val);

        $order_info['shipping_tel']  = 1234567;
        $order_info['shipping_name'] = mb_strcut($order_info['shipping_name'], 0, 6).'快递';
        $order_info['shipping_name'] = mb_convert_encoding($order_info['shipping_name'], 'GBK', 'UTF-8');
        $order_info['tracking_sn']   = trim($order_info['tracking_sn']);

        global $smarty;
        $smarty->assign('time',       date('Y-m-d H:i:s'));
        $smarty->assign('method',     'dangdang.order.goods.send');
        $smarty->assign('order_info', $order_info);
        $smarty->assign('goods_list', $goods_list);
        $send_goods = $smarty->fetch('dangdang_XML.htm');
        if (file_exists('ddXML.xml')) {
            unlink('ddXML.xml');
        }

        $bytes = file_put_contents('ddXML.xml', $send_goods);
        $params['sendGoods'] = '/var/www/html/crm2/admin/ddXML.xml';

        //$dd = new ddClient(2100001198);
        $resp = $dd->execute('POSTXML', $params, 'dangdang.order.goods.send');

        if (isset($resp['Result']['OrdersList']['OrderInfo']['orderOperCode'])) {
            $res['message'] = '当当网提示您：订单'.$resp['Result']['OrdersList']['OrderInfo']['orderID'].
                '，'.$resp['Result']['OrdersList']['OrderInfo']['orderOperation'];
        } else {
            $res = true;
        }

        $resp = var_export($resp, true);
        file_put_contents('ddInfo.htm', $resp, FILE_APPEND);
    }

    // 国美同步发货
    if ($order_info['shipping_time'] && $order_info['team'] == 12) {
        $auth = include 'gome/config.php';

        $url = 'http://api.coo8.com/ApiControl';
        $sys_param = array (
            'venderId'   => $auth['appkey'],
            'timestamp'  => date('Y-m-d H:i:s'),
            'v'          => '2.0',
            'signMethod' => 'md5',
            'format'     => 'json',
        );

        // 应用级参数
        $sys_param['method']          = 'coo8.orders.send';
        $sys_param['orderid']         = $order['order_sn'];
        $sys_param['logisticsNumber'] = $tracking_sn;

        // 配送公司编号
        $sql_select = 'SELECT gemo_code FROM '.$GLOBALS['ecs']->table('shipping').' s, '.
            $GLOBALS['ecs']->table('order_info')." i WHERE i.shipping_id=s.shipping_id AND i.order_id=$order_id";
        $sys_param['carriersName'] = $GLOBALS['db']->getOne($sql_select);

        $sys_param['sign'] = makeSign($sys_param, $auth['secretKey']);

        //$url = $url.'?'.http_build_query($sys_param);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sys_param));

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch),0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new Exception($data, $httpStatusCode);
            }
        }
        curl_close($ch);
        $res = true;
    }

    // 苏宁同步发货
    if ($order_info['shipping_time'] && $order_info['team'] == 17) {
        require('suning/SuningSdk.php');
        $auth = require('suning/config.php');

        // 配送公司编号
        $sql_select = 'SELECT suning_code,company_name FROM '.$GLOBALS['ecs']->table('shipping').' s, '.
            $GLOBALS['ecs']->table('order_info')." i WHERE i.shipping_id=s.shipping_id AND i.order_id=$order_id";
        $logistics = $GLOBALS['db']->getRow($sql_select);

        $shipping_code = $logistics['suning_code'];

        $req = new SuningCustomOrderGet;

        echo $order_info['order_sn'],PHP_EOL;
        $req->setOrderCode($order_info['order_sn']);

        $reqParam = $req->makeReqObject();

        $suning = new SuningClient();
        $suning->setAppKey($auth['appkey']);
        $suning->setAppSecret($auth['secretKey']);
        $suning->setMethod('suning.custom.order.get');
        $suning->setAppRequestTime(date('Y-m-d H:i:s'));
        $resp = $suning->execute($req, $reqParam);

        $resp = json_decode($resp, true);
        $orderLineNumbers = array ();
        foreach ($resp['sn_responseContent']['sn_body']['orderGet']['orderDetail'] as $suningOrderLineNumber){
            $orderLineNumbers = $suningOrderLineNumber['orderLineNumber'];
        }

        $req = new SuningCustomOrderDeliveryAdd;

        $req->setOrderCode($order_info['order_sn']);
        $req->setExpressNo($tracking_sn);
        $req->setExpressCompanyCode($shipping_code);
        $req->setDeliveryTime(date('Y-m-d H:i:s'));

        $sql_select = 'SELECT goods_sn FROM '.$GLOBALS['ecs']->table('order_goods')." WHERE order_sn='{$order_info['order_sn']}'";
        $productCode = $GLOBALS['db']->getCol($sql_select);
        $req->setProductCode($productCode);

        $req->setOrderLineNumber($orderLineNumbers);

        $reqParam = $req->makeReqObject();
        if ('param_is_null' == $reqParam['error_code']) {
            $msg = array (
                'req_msg' => true,
                'timeout' => 2000,
                'message' => $reqParam['error_msg']
            );
            die($json->encode($msg));
        }
        $suning = new SuningClient();
        $suning->setAppKey($auth['appkey']);
        $suning->setAppSecret($auth['secretKey']);
        $suning->setMethod('suning.custom.orderdelivery.add');
        $suning->setAppRequestTime(date('Y-m-d H:i:s'));
        $resp = $suning->execute($req, $reqParam);
        $resp = json_decode($resp, true);
        $res = true;
    }

    return $res;
}

/**
 * 退货或关闭订单后恢复库存
 */
function restore_storage($order_id) {
    $sql_select = 'SELECT g.rec_id, g.stock FROM '.$GLOBALS['ecs']->table('order_goods').' g,'.$GLOBALS['ecs']->table('order_info').
        " i WHERE g.order_id=$order_id AND g.order_id=i.order_id AND i.restored=0";
    $result = $GLOBALS['db']->getAll($sql_select);

    $tmp_stock = array();
    if (!empty($result)) {
        $GLOBALS['db']->query('START TRANSACTION');
        foreach ($result as $val) {
            if (empty($val['stock'])) {
                foreach ($tmp_stock as $value){
                    $sql_update_order = 'UPDATE '.$GLOBALS['ecs']->table('order_goods').
                        " SET restorage=0 WHERE rec_id={$value} AND restorage=1";
                    $GLOBALS['db']->query($sql_update_order);
                }

                $GLOBALS['db']->query('ROLLBACK'); // 执行失败
                return true;
            }

            foreach (unserialize($val['stock']) as $v) {
                $sql_update_stock = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').
                    " SET quantity=quantity+{$v['quantity']} WHERE rec_id={$v['rec_id']}";
                $sql_update_order = 'UPDATE '.$GLOBALS['ecs']->table('order_goods').
                    " SET restorage=1 WHERE rec_id={$val['rec_id']} AND restorage=0";
                if ($GLOBALS['db']->query($sql_update_stock) && $GLOBALS['db']->query($sql_update_order)) {
                    $tmp_stock[] = $val['rec_id'];
                } else {
                    foreach ($tmp_stock as $value){
                        $sql_update_order = 'UPDATE '.$GLOBALS['ecs']->table('order_goods').
                            " SET restorage=0 WHERE rec_id={$value} AND restorage=1";
                        $GLOBALS['db']->query($sql_update_order);
                    }

                    $GLOBALS['db']->query('ROLLBACK'); // 执行失败
                    return true;
                }
            }
        }
        $GLOBALS['db']->query('COMMIT');
    }

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info')." SET restored=1 WHERE order_id=$order_id";
    $GLOBALS['db']->query($sql_update);
    unset($tmp_stock);
    return false;
}

/**
 * 计算包邮卡使用截至时间
 */
function get_deadline ($effective, $nowdate)
{
    list($cur_year, $cur_month, $cur_day) = explode('-', $nowdate);
    $cur_month += 12;
    if ($cur_month > 12)
    {
        $cur_year += floor($cur_month/12);
        $cur_month = $cur_month%12;
    }

    return $cur_year.'-'.$cur_month.'-'.$cur_day;
}

/**
 * 生成包邮卡卡号
 */
function generate_card_number ($role)
{
    date_default_timezone_set('Asia/Chongqing');
    $mon = date(m);
    $str_rand = "12356789";
    $rand = "";
    $num = 7 - strlen($role['user_id']);
    for($i=0;$i<$num;$i++)
    {
        $rand .= substr($str_rand,mt_rand(0,7),1);
    }
    $freecard_num = trim($role['role']).$mon.' '.chunk_split($rand.'0'.$role['user_id'],"4"," ");

    return $freecard_num;
}

/**
 * 分解套餐
 */
function explode_package($package_sn) {
    $sql_select = 'SELECT pg.num goods_number, pg.goods_id, g.goods_sn, g.goods_name FROM '.
        $GLOBALS['ecs']->table('packing').' p LEFT JOIN '.$GLOBALS['ecs']->table('packing_goods').
        ' pg ON p.packing_id=pg.packing_id LEFT JOIN '.$GLOBALS['ecs']->table('goods').
        " g ON g.goods_id=pg.goods_id WHERE p.packing_desc='$package_sn'";
    return $GLOBALS['db']->getAll($sql_select);
}

/**
 * 处理每个商品的库存数量
 */
function handle_storage_step($goods_info, $package_info = '') {
    // 计算库存记录总和是否满足订单发货需要的商品数量
    $sql_select = 'SELECT SUM(quantity) FROM '.$GLOBALS['ecs']->table('stock_goods').
        " WHERE goods_sn='{$goods_info['goods_sn']}' GROUP BY goods_sn";
    $stock_total = $GLOBALS['db']->getOne($sql_select);

    // 不满足发货需要的商品数量则终止发货流程
    $need_number = empty($package_info) ? $goods_info['goods_number'] : $goods_info['goods_number'] * $package_info['goods_number'];
    if ($need_number > $stock_total) {
        return array ('update_status' => false, 'error_msg' => $goods_info['goods_name']);
    }

    // 满足发货数量 读取库存记录
    $sql_select = 'SELECT rec_id, quantity FROM '.$GLOBALS['ecs']->table('stock_goods').
        " WHERE goods_sn='{$goods_info['goods_sn']}' AND quantity>0";
    $stock_list = $GLOBALS['db']->getAll($sql_select);

    $stock_record = array (); // 被用到的库存记录
    $sql_update   = array (); // 更新库存数据
    foreach ($stock_list as $val) {
        $need_number = $val['quantity'] - $need_number;

        // 单条库存记录满足发货需要
        if ($need_number >= 0) {
            $stock_record[$val['rec_id']] = $val['quantity'] - $need_number;
            $sql_update[] = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').
                " SET quantity=quantity-{$stock_record[$val['rec_id']]} WHERE rec_id={$val['rec_id']}";
            break;
        } else {    // 单条库存记录不满足发货需要
            $stock_record[$val['rec_id']] = $val['quantity'];

            $sql_update[] = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').
                " SET quantity=quantity-{$val['quantity']} WHERE rec_id={$val['rec_id']}";

            $need_number = abs($need_number);
        }
    }
    unset($val);

    // 启用事务处理
    $GLOBALS['db']->query('START TRANSACTION');
    foreach ($sql_update as $val) {
        if (!$GLOBALS['db']->query($val)) {
            $GLOBALS['db']->query('ROLLBACK'); // 执行失败
            return array ('update_status' => false, 'error_msg'=> $goods_info['goods_name']);
        }
    }

    $GLOBALS['db']->query('COMMIT');

    return array ('update_status' => true, 'stock_record' => $stock_record);
}

/**
 * 处理每个商品的库存数量
 */
function handle_storage_2($order_id)
{
    $sql = 'SELECT goods_sn,goods_number,goods_name,rec_id,is_package,stock FROM '.
        $GLOBALS['ecs']->table('order_goods')." WHERE order_id=$order_id AND stock=''";
    $goods_list = $GLOBALS['db']->getAll($sql);

    $stock_record = array();
    foreach ($goods_list as $val){
        if ($val['is_package'] == 1 || !is_numeric($val['goods_sn'])) {
            $package_goods_list = explode_package($val['goods_sn']);
            foreach ($package_goods_list as $v){
                $stock_record[$val['rec_id']][] = handle_storage_step_2($v, $val['goods_number']);
            }
        } else {
            $stock_record[$val['rec_id']][] = handle_storage_step_2($val);
        }
    }

    // 开启事务处理
    $GLOBALS['db']->query('START TRANSACTION');
    unset($val, $v);
    foreach ($stock_record as $key=>$val){
        foreach ($val as $v) {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').
                " SET quantity=quantity-{$v['quantity']} WHERE rec_id={$v['rec_id']}";
            if (!$GLOBALS['db']->query($sql_update)) {
                $GLOBALS['db']->query('ROLLBACK');

                return array('update_status' => false, 'error_msg' => '库存处理失败！');
            }
        }

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_goods').' SET stock=\'%s\' WHERE rec_id=%s';
        if (!$GLOBALS['db']->query(sprintf($sql_update, serialize($val), $key))) {
            $GLOBALS['db']->query('ROLLBACK'); // 执行失败

            return array('update_status' => false, 'error_msg' => '库存处理失败！');
        }
    }

    $GLOBALS['db']->query('COMMIT');

    return array('update_status' => true, 'stock_record' => $rec_id);
}

function handle_storage_step_2 ($goods_info, $package_number = '')
{
    $need_number = empty($package_number) ? $goods_info['goods_number'] : $goods_info['goods_number'] * $package_number;
    $sql_select = 'SELECT rec_id FROM '.$GLOBALS['ecs']->table('stock_goods').
        " WHERE goods_sn='{$goods_info['goods_sn']}' AND quantity>0 ORDER BY production_day ASC";
    $rec_id = $GLOBALS['db']->getOne($sql_select);
    if (empty($rec_id)) {
        $sql_select = 'SELECT rec_id FROM '.$GLOBALS['ecs']->table('stock_goods').
            " WHERE goods_sn='{$goods_info['goods_sn']}' ORDER BY production_day DESC";
        $rec_id = $GLOBALS['db']->getOne($sql_select);
    }

    //$sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods')." SET quantity=quantity-$need_number WHERE goods_sn={$goods_info['goods_sn']} AND rec_id=$rec_id";
    return array('rec_id' => $rec_id, 'quantity' => $need_number);
}

function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();

    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }

    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }

            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }

    return $arrData;
}

/**
 * 国美订单同步:生成签名
 */
function makeSign($sys_param, $secret)
{
    ksort($sys_param);
    $sign_str = $secret;
    foreach ($sys_param as $key=>$val){
        if (empty($val)) {
            continue;
        }

        $sign_str .= $key.$val;
    }

    $sign_str .= $secret;

    $sign = strtoupper(md5($sign_str));

    return $sign;
}

/**
 * 跨平台查看订单
 */
function order_cross_platform()
{
    $platform_list_arr = explode(',', $_SESSION['action_list']);
    $platform_list_arr = array_unique(array_filter($platform_list_arr));
    $platform_list_str = implode('","', $platform_list_arr);
    $sql_select = 'SELECT role_id FROM '.$GLOBALS['ecs']->table('role')." WHERE action IN (\"$platform_list_str\")";
    $role_list = $GLOBALS['db']->getCol($sql_select);

    $role_list_str = implode(',', $role_list);

    return $role_list_str;
}

/**
 * verify_price
 * @return boolean
 * @author Nixus
 **/
function verify_price($goods_info, $platform)
{
    $mem = new Memcache;
    $mem->connect('127.0.0.1', 11211);
    if (2 == $goods_info['is_gift']) {
        $promote = $mem->get("promote_{$goods_info['goods_sn']}");
        if (empty($promote)) {
            $sql_select = 'SELECT promote_price,platform,end_time FROM '.$GLOBALS['ecs']->table('promote').
                " WHERE goods_sn='{$goods_info['goods_sn']}' AND platform=$platform AND start_time<UNIX_TIMESTAMP() ORDER BY promote_price DESC";
            $promote = $GLOBALS['db']->getRow($sql_select);
            $mem->add("promote_{$promote['goods_sn']}", $promote, false, $promote['end_time']-time());
        }
        if ($promote['platform'] == $platform && $promote['promote_price'] > 0) {
            if ($goods_info['goods_price']>=$promote['promote_price']) {
                $mem->close();
                return false;
            }
        }
    }
    $right_goods = $mem->get("goods_{$goods_info['goods_sn']}");
    if (empty($right_goods) || !isset($right_goods['cost_price'])) {
        $sql_select = 'SELECT goods_name,goods_sn,shop_price,cost_price,min_price FROM '.
            $GLOBALS['ecs']->table('goods')." WHERE goods_sn='{$goods_info['goods_sn']}'";
        $right_goods = $GLOBALS['db']->getRow($sql_select);
        $mem->add("goods_{$right_goods['goods_sn']}", $right_goods, false, 20*3600);
        $mem->close();
    }
    $final = true;
    if ($right_goods['cost_price'] <= 0) {
        $final = false;
    } elseif ($goods_info['is_gift'] == 1) {
        $final = false;
    } elseif ($goods_info['is_gift'] == 3) {
        $final = false;
    } elseif ($goods_info['goods_price'] >= $right_goods['cost_price']) {
        $final = false;
    }
    return $final;
}

/**
 * false_order_list
 * @return array
 * @author Nixus
 **/
function false_order_list()
{
    $filter['end_time']   = time();
    $filter['begin_time'] = $filter['end_time'] -3600*30;

    // 收集查询条件
    foreach ($filter as $key=>$val) {
        if (!empty($val)) {
            $condition .= "&$key=$val";
        }
    }

    // $platform;
    // $shipping_way;

    // 总记录数
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').
        " WHERE add_time BETWEEN {$filter['begin_time']} AND {$filter['end_time']} AND team NOT IN ({$_SESSION['role_id']},25)".
        ' AND platform>1 AND order_status=5 AND shipping_status=1 AND pay_id<>3 ';
    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);

    // 页码
    $filter['page_no'] = intval($_REQUEST['page_no']) ?: 1;

    // 页数
    $filter['page_size'] = intval($_REQUEST['page_size']) ?: 20;

    $page_set = break_pages($filter['record_count'], $filter['page_size'], $filter['page_no']);

    $sql_select = 'SELECT i.consignee,FROM_UNIXTIME(i.shipping_time,"%Y-%m-%d %H:%i:%s") add_time,i.shipping_name,i.tracking_sn,'.
        'i.order_id,p.region_name prov, c.region_name city, d.region_name dist, i.address addr, r.role_name platform,t.brush_order_sn tmall_sn,
        j.brush_order_sn jd_sn, dd.brush_order_sn dd_sn, m.brush_order_sn 1m_sn FROM '.
        $GLOBALS['ecs']->table('order_info').' i LEFT JOIN '.$GLOBALS['ecs']->table('region').
        ' p ON i.province=p.region_id LEFT JOIN '.$GLOBALS['ecs']->table('region').' c ON c.region_id=i.city LEFT JOIN '.
        $GLOBALS['ecs']->table('region').' d ON d.region_id=i.district LEFT JOIN '.$GLOBALS['ecs']->table('role').
        ' r ON i.team=r.role_id LEFT JOIN '.
        $GLOBALS['ecs']->table('brush_order').' t ON t.source_order_id=i.order_id AND t.brush_platform=6 LEFT JOIN '.
        $GLOBALS['ecs']->table('brush_order').' j ON j.source_order_id=i.order_id AND j.brush_platform=10 LEFT JOIN '.
        $GLOBALS['ecs']->table('brush_order').' dd ON dd.source_order_id=i.order_id AND dd.brush_platform=16 LEFT JOIN '.
        $GLOBALS['ecs']->table('brush_order').' m ON m.source_order_id=i.order_id AND m.brush_platform=14 '.
        ' WHERE i.pay_id<>3 AND i.order_status=5 AND i.shipping_status=1 AND i.add_time BETWEEN '.
        " {$filter['begin_time']} AND {$filter['end_time']} AND i.team NOT IN ({$_SESSION['role_id']},25) AND i.platform>1 ORDER BY shipping_time DESC".
        ' LIMIT '.($filter['page_no']-1)*$filter['page_size'].','.$filter['page_size'];
    $row = $GLOBALS['db']->getAll($sql_select);

    return array (
        'orders'       => $row,
        'filter'       => $filter,
        'page_count'   => $page_set['page_count'],
        'record_count' => $filter['record_count'],
        'page_size'    => $filter['page_size'],
        'page_no'      => $filter['page_no'],
        'page_set'     => $page_set['page_set'],
        'condition'    => $condition,
        'start'        => ($filter['page_no'] - 1)*$filter['page_size'] +1,
        'end'          => $filter['page_no']*$filter['page_size'],
    );
}

/**
 * 确认收货分顾客
 */
function assign_user($order_id) {
    // 顾客重分配
    $sql_select = 'SELECT final_amount,add_time,user_id FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
    $order_amount = $GLOBALS['db']->getRow($sql_select);

    // 获取会员部所有客服的user_id及user_name
    $mem = new Memcache;
    $mem->connect('127.0.0.1', 11211);
    $user_list = $mem->get('users_counter');

    $tomorrow = mktime(0, 0, 0, date("m"), date("d")+1, date("Y"));
    $expire   = $tomorrow - time();

    if (empty($user_list)) {
        $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').
            ' WHERE role_id IN ('.MEMBER_SALE.') AND status=1 AND stats=1 AND password<>1 AND assign=1';
        $user_list = $GLOBALS['db']->getCol($sql_select);
        $user_list = array_fill_keys($user_list, 0);
        $mem->set('users_counter', $user_list, false, $expire);
    }

    natsort($user_list);
    $alternative = array_keys($user_list, reset($user_list));
    $admin_id = $alternative[array_rand($alternative)];

    $sql = 'SELECT customer_type FROM'.$GLOBALS['ecs']->table('users')." WHERE user_id={$order_amount['user_id']}";
    $customer_type = $GLOBALS['db']->getOne($sql);
    if ($customer_type != 21) {
        if ($order_amount['final_amount'] < 800) {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET admin_id=493 WHERE user_id={$order_amount['user_id']} ".
                ' AND role_id NOT IN ('.OFFLINE_SALE.',8,23) LIMIT 1';
        } else {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET admin_id=493 WHERE user_id={$order_amount['user_id']}".
                ' AND role_id NOT IN ('.OFFLINE_SALE.',8,23) LIMIT 1';
        }
    }

    $GLOBALS['db']->query($sql_update);
    if ($GLOBALS['db']->affected_rows() > 0) {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').' u,'.$GLOBALS['ecs']->table('admin_user').
            " a SET u.order_time={$order_amount['add_time']},u.admin_name=a.user_name,u.role_id=a.role_id,".
            'u.group_id=a.group_id,u.assign_time=UNIX_TIMESTAMP() WHERE u.role_id NOT IN ('.OFFLINE_SALE.
            ",8) AND u.user_id={$order_amount['user_id']} AND a.user_id=u.admin_id";
        $GLOBALS['db']->query($sql_update);
        $user_list[$admin_id]++;
        $mem->replace('users_counter', $user_list, false, $expire);
    }
}

function assign_user2($order_id) {
    // 获取顾客及订单信息
    $sql_select = 'SELECT user_id,final_amount FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
    $order_info = $GLOBALS['db']->getRow($sql_select);
    // 获取商品分类
    $sql_select = 'SELECT o.goods_id,o.goods_sn,SUM(o.goods_number) goods_number,g.goods_effect FROM '
        .$GLOBALS['ecs']->table('order_goods').' o LEFT JOIN '.$GLOBALS['ecs']->table('goods').
        " g ON g.goods_sn=o.goods_sn WHERE o.order_id=$order_id GROUP BY o.goods_sn";
    $goods_info = $GLOBALS['db']->getAll($sql_select);
    // 统计同类商品总数
    $goods_number = array();
    foreach ($goods_info as $val) {
        if (isset($goods_number[$val['goods_effect']])) {
            $goods_number[$val['goods_effect']] += $val['goods_number'];
        } else {
            $goods_number[$val['goods_effect']] = $val['goods_number'];
        }
    }
    $goods_total = array_sum($goods_number);
    // 计算商品在各分类中的比重 并找出最大的goods_effect
    $percentage = array();
    foreach ($goods_number as $key=>$val) {
        $percentage[$key] = $val/$goods_total;
    }
    $goods_effect = array_search(max($percentage), $percentage);
    // 修改顾客所属的effects分类及归属信息
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').' SET role_id=0,last_admin=admin_id,'
        ."admin_id=0,admin_name='',eff_id=$goods_effect WHERE user_id={$order_info['user_id']}";
    return $GLOBALS['db']->query($sql_update);
}

//json转码问题
function encode_json($str) {
    return urldecode(json_encode(url_encode($str)));
}
/** **/
function url_encode($str) {
    if(is_array($str)) {
        foreach($str as $key=>$value) {
            $str[urlencode($key)] = url_encode($value);
        }
    } else {
        $str = urlencode($str);
    }
    return $str;
}
//淘宝中通电子面单
function shipping_zto($order){
    //require('taobao/order_synchro.php');
    include_once('taobao/sk.php');
    include_once('taobao/TopSdk.php');
    $auth = include_once('taobao/config.php');

    //中通，加盟型
    $c                     = new TopClient;
    $c->appkey             = $auth['appkey'];
    $c->secretKey          = $auth['secretKey'];

    $req                   = new WlbWaybillISearchRequest;
    $waybill_apply_request = new WaybillApplyRequest();
    $waybill_apply_request->seller_id= $sk['taobao_user_id'];
    $waybill_apply_request->cp_code= "ZTO";
    $req->setWaybillApplyRequest(json_encode($waybill_apply_request));
    $resp = $c->execute($req,$sk['access_token']);   //查看面单可用数量
    $resp = json_decode(json_encode($resp),TRUE);
    $branch_account_cols =$resp['subscribtions']['waybill_apply_subscription_info']['branch_account_cols'];
    $quantity = ($branch_account_cols['waybill_branch_account']['quantity']);
    $seller_id = ($branch_account_cols['waybill_branch_account']['seller_id']);
    //发货地址
    $address = ($branch_account_cols['waybill_branch_account']['shipp_address_cols']['waybill_address']);
    $shipping_address = array(
        "province"       => $address['province'],
        "area"           => $address['area'],
        "city"           => $address['city'],
        "town"           => '',
        "address_detail" => $address['address_detail']
    );
    $consignee_address = array(
        "province"          => $order['prov'],
        "city"              => $order['city'],
        "area"              => $order['city'],
        "town"              => $order['district'],
        "address_detail"    => $order['address']
    );
    $package_items = array(
        "count"            => 1,
        "item_name"        => '保健食品',
    );
    $trade_order_info_cols = array(
        "send_phone"       => '15989155172',
        "trade_order_list" => $order['order_sn'],
        "product_type"     => "STANDARD_EXPRESS",
        "consignee_phone"  => $order['mobile'],
        "send_name"        => "韦雪婷",
        "consignee_name"   => $order['consignee'],
        "package_items"    => $package_items,
        "real_user_id"      => $sk['taobao_user_id'],
        "package_id"        => $order['sn'],
        "consignee_address" => $consignee_address,
        "order_channels_type"=> "TB"
    );
    //面单申请
    $req = new WlbWaybillIGetRequest;
    $waybill_apply_new_request = new WaybillApplyNewRequest();
    $waybill_apply_new_request->seller_id= $seller_id;
    $waybill_apply_new_request->cp_code= "ZTO";
    $waybill_apply_new_request->shipping_address= $shipping_address;
    $waybill_apply_new_request->app_key= $auth['appkey'];
    $waybill_apply_new_request->trade_order_info_cols= $trade_order_info_cols;
    $req->setWaybillApplyNewRequest(json_encode($waybill_apply_new_request));
    $resp = $c->execute($req, $sk['access_token']); //获取面单号
    $get_resp = json_decode(json_encode($resp),TRUE);
    if (!$get_resp['waybill_apply_new_cols']) {
        echo $get_resp['sub_msg'];
        exit;
    }
    $waybill_code = $get_resp['waybill_apply_new_cols']['waybill_apply_new_info']['waybill_code'];

    if(file_exists('code.jpg'))unlink('code.jpg');
    if(file_exists('code1.jgp'))unlink('code1.jpg');
    include('./includes/bcode128.class.php');
    $options=array(
        'type'=>'B', //设定编码类型 不传递为自动判断
        'dpi'=>300, //设定DPI 72-300
        'width'=>350, //设定生成图片的宽度
        'height'=>100, //设定生成图片的高度
        'margin'=>'10', //边距 传递 1个参数时同时设定四周  传递 2个参数时 为左右,上下 传递4个参数时，为 左,下,右,上
        'img_type'=>'jpg', //输出图片类型 png jpg gif
        'color'=>'#000', //输出颜色
        'out_file'=>'code.png', //输出的文件路径
        //'top_text'=>'php生成图形条形码演示',
        'top_size'=>20,
        'top_margin'=>10,
        'top_font'=>'font/microhei.ttc',
        //'top_color'=>'#000', //顶部文字颜色
        'bottom_text'=>$waybill_code,
        'bottom_size'=>20,
        'bottom_margin'=>10,
        'bottom_font'=>'font/Arial.ttf',
        //'bottom_color'=>'#330000', //底部文字颜色
    );
    $font = new bcode128($waybill_code,$options);
    $font->img();

    $options['out_file'] = 'code1.png';
    $options['width'] = '240';
    $options['height'] = '80';
    $font = new bcode128($waybill_code,$options);
    $font->img();

    //打印中通快递
    $req                               = new WlbWaybillIPrintRequest;
    $waybill_apply_print_check_request = new WaybillApplyPrintCheckRequest();
    $other_info = $get_resp['waybill_apply_new_cols']['waybill_apply_new_info'];
    $print_check_info_cols =
        array_merge($get_resp['waybill_apply_new_cols']['waybill_apply_new_info']['trade_order_info'],array('shipping_address'=>$shipping_address));

    $print_check_info_cols['shipping_branch_code'] = $other_info['shipping_branch_code'];
    $print_check_info_cols['send_phone'] = $trade_order_info_cols['send_phone'];
    $print_check_info_cols['send_name'] = $trade_order_info_cols['send_name'];
    $print_check_info_cols['waybill_code'] = $other_info['waybill_code'];
    $print_check_info_cols['short_address'] = $other_info['short_address'];
    $print_check_info_cols = json_decode(json_encode($print_check_info_cols));

    $waybill_apply_print_check_request->seller_id             = $seller_id;
    $waybill_apply_print_check_request->print_check_info_cols = $print_check_info_cols;
    $waybill_apply_print_check_request->cp_code               = "ZTO";
    $waybill_apply_print_check_request->app_key               = $auth['appkey'];
    $req->setWaybillApplyPrintCheckRequest(json_encode($waybill_apply_print_check_request));
    $resp = $c->execute($req, $sk['access_token']);
    $resp = json_decode(json_encode($resp),TRUE);
    if (!$resp['waybill_apply_print_check_infos']) {
        echo '<pre>';
        print_r($resp);
        exit;
    }

    $order_id = intval($_REQUEST['order_id']);
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('order_info').' SET shipping_time=UNIX_TIMESTAMP(),shipping_status=1, '.
        "order_status=5,pay_status=2,tracking_sn='$waybill_code' WHERE order_id=$order_id";
    $res = $GLOBALS['db']->query($sql);
    $syn_info = shipping_synchro($order_id);

    $get_resp['waybill_apply_new_cols']['waybill_apply_new_info']['trade_order_info']['consignee_address']['address_detail'] = $order['address'];
    return array(
        'get_resp'=>$get_resp['waybill_apply_new_cols']['waybill_apply_new_info'],
        'shipping_address' => $address,
    );
}

//确认订单后将按顾客需求分类到数据中心
function assignUserToData($order_id,$goods_list=array()){
    $sql = 'SELECT u.user_id,u.eff_id FROM '.$GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('order_info')
        ." o ON u.user_id=o.user_id WHERE o.order_id=$order_id";
    $user_info = $GLOBALS['db']->getRow($sql);
    if (!$user_info['eff_id']) {
        //分析订单的商品 
        $sql = 'SELECT og.goods_number,g.goods_effect FROM '.$GLOBALS['ecs']->table('order_goods').' og LEFT JOIN '
            .$GLOBALS['ecs']->table('goods')
            ." g ON g.goods_sn=og.goods_sn WHERE og.order_id=$order_id AND og.goods_price>0 AND og.is_gift=0 ORDER BY og.goods_number ASC";
        $goods_list = $GLOBALS['db']->getAll($sql);
        if ($goods_list) {
            $info = array_shift($goods_list);
            $eff_id = $info['goods_effect'] ? $info['goods_effect'] : intval($_REQUEST['eff_id']);
            $sql = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET eff_id=$eff_id WHERE user_id={$user_info['user_id']}";
            $GLOBALS['db']->query($sql);
        }
    }else return false;
}
