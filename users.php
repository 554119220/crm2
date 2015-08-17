<?php

/**
 * ECSHOP 会员管理程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: users.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_goods.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
date_default_timezone_set('Asia/Shanghai');

$file = basename($_SERVER['PHP_SELF'], '.php');
$smarty->assign('file', $file);

$smarty->assign('by_send_group_list', get_group_list($_SESSION['role_id']));

if(isset($_REQUEST['group_id']) && !empty($_REQUEST['group_id'])){
    $smarty->assign('by_send_to_admin', get_admin_tmp_list(0,intval($_REQUEST['group_id'])));
}else{
    /*未选择组*/
    //$smarty->assign('by_send_to_admin', get_admin_tmp_list('session'));
}

/* 保留搜索关键词 */
if (isset($_REQUEST['keywords']) || isset($_REQUEST['start_time']) || isset($_REQUEST['end_time'])) {
    $smarty->assign('kf', $_REQUEST['keyfields']);
    $smarty->assign('kw', urldecode($_REQUEST['keywords']));

    if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
        $smarty->assign('start_time', stamp2date($_REQUEST['start_time'], 'Y-m-d H:i'));
        $smarty->assign('end_time',   stamp2date($_REQUEST['end_time'], 'Y-m-d H:i'));
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

/* 意向顾客列表 */
if ($_REQUEST['act'] == 'intention') {
    /* 检查权限 */
    //admin_priv('intention');
    $res = array ('switch_tag' => true, 'id' => 4);

    $sql = "SELECT rank_id, rank_name, min_points FROM ".$ecs->table('user_rank')." ORDER BY min_points ASC ";
    $rs = $db->query($sql);

    $ranks = array();
    while ($row = $db->FetchRow($rs)) {
        $ranks[$row['rank_id']] = $row['rank_name'];
    }

    if (!isset($_REQUEST['type'])) {
        $_REQUEST['type'] = '1';
    } else {
        $_REQUEST['type'] = urldecode($_REQUEST['type']);
    }

    $intention = get_customer_type($_REQUEST['type']);
    foreach ($intention as $val) {
        $intente[] = $val['type_name'];
    }

    $smarty->assign('action',        $_SESSION['action_list']);
    $smarty->assign('user_ranks',    $ranks);
    $smarty->assign('action_link',   array('text' => $_LANG['02_users_add'], 'href'=>'users.php?act=add'));
    $smarty->assign('country_list',  get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    $user_list = user_list();

    /* 获取顾客来源、购买力、客服 */
    $smarty->assign('from_where', get_from_where());
    //$smarty->assign('nav_list',get_customer_type('1,2,3'));
    $smarty->assign('type_list',  get_customer_type());

    $admin_list = get_admin('session');
    $smarty->assign('admin_list', $admin_list);
    $smarty->assign('eff_list',   getEffectTypes());

    $smarty->assign('ur_here',       $_LANG['06_intention'].'(包括：'.@implode(',', $intente).')');
    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));

    $smarty->assign('user_list',     $user_list['user_list']);
    $smarty->assign('page_link',     $user_list['condition']);
    $smarty->assign('filter',        $user_list['filter']);
    $smarty->assign('record_count',  $user_list['record_count']);
    $smarty->assign('page_count',    $user_list['page_count']);
    $smarty->assign('page_size',     $user_list['page_size']);
    $smarty->assign('page_start',    $user_list['start']);
    $smarty->assign('page_end',      $user_list['end']);
    $smarty->assign('full_page',     1);
    $smarty->assign('page_set',      $user_list['page_set']);
    $smarty->assign('page',          $user_list['page']);
    $smarty->assign('act',           $_REQUEST['act']);
    $smarty->assign('tag',           $_REQUEST['tag'] ? $_REQUEST['tag'] :0);
    $smarty->assign('type',          $_REQUEST['type']);
    $smarty->assign('sort_user_id',  '<img src="images/sort_desc.gif">');

    //判断客服的权限，是否显示团队搜索
    if($_SESSION['action_list'] == 'all') {
        $smarty->assign('admin_show_team',1);
        $smarty->assign('role_list', get_role());
    } else {
        $sql = 'SELECT manager,role_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id={$_SESSION['admin_id']}";
        $user = $GLOBALS['db']->getRow($sql);
        if($user['manager'] === '0') {
            $smarty->assign('role_id',   $user['role_id']);
            $smarty->assign('show_team', 1);
        }
    }

    if (admin_priv('user_group_view', '', false) || admin_priv('user_part_view', '', false)) {
        $smarty->assign('section', 1);
    }

    // 是否显示转顾客
    if (admin_priv('reassign_user', '', false)) {
        $smarty->assign('reassign_user', 1);
    }

    assign_query_info();
    $res['main'] = $smarty->fetch('users_list.htm');

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    die($json->encode($res));
}

/* 已购买顾客列表 */
elseif ($_REQUEST['act'] == 'users_list') {
    /* 检查权限 */
    //admin_priv('users_list');
    $res = array ('switch_tag' => true, 'id' => intval($_REQUEST['tag']));

    if($_SESSION['admin_id'] == 78) {
        $_REQUEST['type'] = '13';
    } elseif (!$_REQUEST['type']) {
        $_REQUEST['type'] = '2';
    }

    $smarty->assign('admin_id',$_SESSION['admin_id']);
    $smarty->assign('role_id',$_SESSION['role_id']);
    if (admin_priv('all','',false)) {
        $smarty->assign('all',true);
    }

    $smarty->assign('user_ranks',    $ranks);
    $smarty->assign('ur_here',       $_LANG['01_users_list']);
    $smarty->assign('country_list',  get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    $user_list = user_list();

    /* 获取顾客来源、购买力、客服 */
    $smarty->assign('from_where', get_from_where());
    $smarty->assign('type_list',  get_customer_type());
    $smarty->assign('admin_list', get_admin('session'));
    $smarty->assign('eff_list',   getEffectTypes());

    if (admin_priv('user_group_view', '', false) || admin_priv('user_part_view', '', false)) {
        $smarty->assign('section', 1);
    }

    $smarty->assign('is_intention', $_REQUEST['act']);  // 意向顾客查询字段，为分页提供区分支持
    $smarty->assign('user_list',    $user_list['user_list']);

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('tag',          $_REQUEST['tag'] ? $_REQUEST['tag'] : 0);
    $smarty->assign('type',         $_REQUEST['type'] ? $_REQUEST['type'] : 2);

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');

    // 是否显示转顾客
    if (admin_priv('reassign_user', '', false)) {
        $smarty->assign('reassign_user', 1);
    }

    //判断客服的权限，是否显示团队搜索
    if($_SESSION['action_list'] == 'all') {
        $smarty->assign('admin_show_team',1);
        $smarty->assign('role_list', get_role());
    } else {
        $sql = 'SELECT manager,role_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id={$_SESSION['admin_id']}";
        $user = $GLOBALS['db']->getRow($sql);
        if($user['manager'] === '0') {
            $smarty->assign('role_id',$user['role_id']);
            $smarty->assign('show_team',1);
        }
    }

    if (isset($_REQUEST['a'])) {
        $smarty->assign('content', 'users_list');
        $res['main'] = $smarty->fetch('list_tpl.htm');
        $res['page'] = $smarty->fetch('page.htm');
        $res['a'] = $_REQUEST['a'];
    } else {
        $res['main'] = $smarty->fetch('users_list.htm');
    }

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    die($json->encode($res));
}

/* 顾客分类显示 */
elseif ($_REQUEST['act'] == 'users_list_group')
{
    /* 检查权限 */
    admin_priv('users_list_group');
    $res = array (
        'switch_tag' => true,
        'id' => !isset($_REQUEST['tag']) ? 0 : intval($_REQUEST['tag'])
    );

    if (!$_REQUEST['type'])
    {
        $_REQUEST['type'] = '2, 3, 4, 5, 11';
    }

    $smarty->assign('user_ranks',   $ranks);
    $smarty->assign('ur_here',      $_LANG['01_users_list']);
    $smarty->assign('country_list',  get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    $user_list = user_list();

    /* 获取顾客来源、购买力、客服 */
    $smarty->assign('from_where', get_from_where());
    $smarty->assign('type_list',  get_customer_type());
    $smarty->assign('admin_list', get_admin('session'));
    $smarty->assign('eff_list',   getEffectTypes());

    $smarty->assign('section', admin_priv('section', '', false));

    $smarty->assign('is_intention', $_REQUEST['act']);  // 意向顾客查询字段，为分页提供区分支持
    $smarty->assign('user_list',    $user_list['user_list']);

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('tag',          $_REQUEST['tag'] ? $_REQUEST['tag'] :0);

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));

    //判断客服的权限，是否显示团队搜索
    if($_SESSION['action_list'] == 'all')
    {
        $smarty->assign('admin_show_team',1);
        $smarty->assign('role_list', get_role());
    }
    else
    {
        $sql = 'SELECT manager,role_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id={$_SESSION['admin_id']}";
        $user = $GLOBALS['db']->getRow($sql);
        if($user['manager'] === '0')
        {
            $smarty->assign('role_id',$user['role_id']);
            $smarty->assign('show_team',1);
        }
    }

    $res['main'] = $smarty->fetch('users_list_group.htm');

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    die($json->encode($res));
}

/* 顾客自定义分类 */
elseif ($_REQUEST['act'] == 'user_cat_list') {
    /* 检查权限 */
    //if (!admin_priv('users_list', '', false)) {
    if (!admin_priv('all', '', false)) {
        $res = array (
            'req_msg'=>true,
            'timeout'=>2000,
            'message'=>'对不起，该账号暂时无法访问此页面！',
        );

        die($json->encode($res));
    }

    $res = array ('switch_tag' => true, 'id' => $_REQUEST['tag'] ? $_REQUEST['tag'] : 0);

    if($_SESSION['admin_id'] == 78){
        $_REQUEST['type'] = '13';
    } elseif (!$_REQUEST['type']){
        $_REQUEST['type'] = '2, 3, 4, 5, 11';
    }

    $smarty->assign('user_ranks',   $ranks);
    $smarty->assign('ur_here',      $_LANG['01_users_list']);
    $smarty->assign('country_list',  get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    $user_list = user_list();

    /* 获取顾客来源、购买力、客服 */
    $smarty->assign('from_where', get_from_where());
    $smarty->assign('type_list',  get_customer_type());
    $smarty->assign('admin_list', get_admin('session'));
    $smarty->assign('eff_list',   getEffectTypes());

    $smarty->assign('section', admin_priv('section', '', false));

    $smarty->assign('is_intention', $_REQUEST['act']);  // 意向顾客查询字段，为分页提供区分支持
    $smarty->assign('user_list',    $user_list['user_list']);

    $smarty->assign('cat_list', user_cat_list(1));

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('tag',          $_REQUEST['tag'] ? $_REQUEST['tag'] :0);
    $smarty->assign('cat_tag',      $_REQUEST['cat_tag'] ? $_REQUEST['cat_tag'] :0);

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');

    //判断客服的权限，是否显示团队搜索
    if($_SESSION['action_list'] == 'all')
    {
        $smarty->assign('admin_show_team',1);
        $smarty->assign('role_list', get_role());
    }
    else
    {
        $sql = 'SELECT manager,role_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id={$_SESSION['admin_id']}";
        $user = $GLOBALS['db']->getRow($sql);
        if($user['manager'] === '0')
        {
            $smarty->assign('role_id',$user['role_id']);
            $smarty->assign('show_team',1);
        }
    }

    $res['main'] = $smarty->fetch('user_cat_list.htm');

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    die($json->encode($res));
}

/* 更改顾客分类 */
elseif ($_REQUEST['act'] == 'change_cat')
{
    $cat_list = user_cat_list(1);
    if (empty($cat_list)){
        $res['req_msg']    = true;
        $res['message']    = '请先添加分类！';
        $res['title']      = '顾客分类';
        $res['btncontent'] = '放弃分类该顾客';

        die($json->encode($res));
    }

    $smarty->assign('cat_list', $cat_list);
    $smarty->assign('user_id', $_REQUEST['user_id']);

    $res['message'] = $smarty->fetch('change_cat.htm');
    $res['req_msg'] = true;
    $res['swt']  = true;
    $res['form_id'] = 'change_cat';
    $res['type'] = 'submit';

    $res['title'] = '自定义顾客分类';

    die($json->encode($res));
}

/* 修改数据 */
elseif ($_REQUEST['act'] == 'update_cat')
{
    $cat = addslashes_deep($_REQUEST);
    if (!is_numeric($cat['cat'])) {
        $res['req_msg'] = true;
        $res['timeout'] = 2000;
        $res['message'] = '请选择正确的选项！';

        die($json->encode($res));
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').
        " WHERE user_cat='{$_SESSION['admin_id']}-{$cat['cat']}' AND user_id={$cat['user_id']}";
    if ($GLOBALS['db']->getOne($sql_select)){
        $res['req_msg'] = true;
        $res['timeout'] = 2000;
        $res['message'] = '该顾客无需修改分类！';
        die($json->encode($res));
    }

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').
        " SET user_cat='{$_SESSION['admin_id']}-{$cat['cat']}' WHERE user_id={$cat['user_id']}";
    $GLOBALS['db']->query($sql_update);
    if ($GLOBALS['db']->affected_rows()){
        $res['req_msg'] = true;
        $res['timeout'] = 2000;
        $res['message'] = '修改成功！';
        $res['tr_id'] = $cat['user_id'];

        die($json->encode($res));
    }
}

/* 添加顾客分类 */
elseif ($_REQUEST['act'] == 'add_user_cat')
{
    if (!admin_priv('add_user_cat', '', false)){
        $res['req_msg'] = true;
        $res['timeout'] = 2000;
        $res['message'] = '当前帐号无法创建新分类！';
        die($json->encode($res));
    }

    $smarty->assign('cat_list', user_cat_list());

    $res['main'] = $smarty->fetch('add_user_cat.htm');
    die($json->encode($res));
}

/* 保存分类到数据库 */
elseif ($_REQUEST['act'] == 'insert_user_cat')
{
    $res['req_msg'] = true;
    $res['timeout'] = 2000;
    $res['code'] = 0;
    if (!admin_priv('add_user_cat', '', false)){
        $res['message'] = '当前帐号无法创建新分类！';
        die($json->encode($res));
    }

    $cat = addslashes_deep($_REQUEST);
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_cat').
        " WHERE cat_name='{$cat['cat_name']}' AND admin_id={$_SESSION['admin_id']}";
    $is_exist = $GLOBALS['db']->getOne($sql_select);
    if ($is_exist){
        $res['message'] = '您提交的顾客分类名称已经存在！';
        die($json->encode($res));
    }

    $sql_select = 'SELECT cat_tag FROM '.$GLOBALS['ecs']->table('user_cat').
        " WHERE admin_id='{$_SESSION['admin_id']}' AND available=1 ORDER BY cat_tag DESC";
    $cat_tag = $GLOBALS['db']->getOne($sql_select) +1;

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_cat').'(cat_name,cat_desc,cat_tag,admin_id)VALUES('.
        "'{$cat['cat_name']}','{$cat['cat_desc']}',$cat_tag,{$_SESSION['admin_id']})";
    $GLOBALS['db']->query($sql_insert);
    if ($cat['cat_id'] = $GLOBALS['db']->insert_id()){
        $cat['cat_tag'] = $cat_tag;
        $cat['req_msg'] = true;
        $cat['timeout'] = 2000;
        $cat['message'] = '添加成功';
        $cat['code'] = 1;
        die($json->encode($cat));
    }
}

/* 第一回访 */
elseif ($_REQUEST['act'] == 'first_trace')
{
    if(!admin_priv('first_trace', '', false)){
        $res['req_msg'] = true;
        $res['timeout'] = 2000;
        $res['message'] = '当前帐号无访问权限！';

        die($json->encode($res));
    }

    $res = array ('switch_tag' => true, 'id' => 2);
    if (isset($_REQUEST['admin_id']) && intval($_REQUEST['admin_id'])) {
        $admin_id = intval($_REQUEST['admin_id']);
    }

    $days_three = time() -3*24*3600; // 三天前
    //$days_five  = $three_days -2*24*3600; // 五天前

    $sql = 'SELECT i.user_id,i.consignee,i.mobile,i.tel,i.receive_time,o.goods_name, '.
        ' i.receive_time+g.take_days*o.goods_number take_time,i.order_id,i.add_time,a.user_name add_admin, '.
        "IF(u.service_time>$days_three,u.service_time,'-') recently FROM ".$GLOBALS['ecs']->table('order_info').
        ' i,'.$GLOBALS['ecs']->table('admin_user').' a,'.$GLOBALS['ecs']->table('order_goods').' o,'.
        $GLOBALS['ecs']->table('goods').' g,'. $GLOBALS['ecs']->table('users').
        ' u WHERE i.add_admin_id=a.user_id AND u.customer_type<>5 AND i.user_id=u.user_id AND o.goods_id=g.goods_id AND i.order_id=o.order_id';

    if (!admin_priv('all', '', false)) {
        $sql .= " AND u.admin_id={$_SESSION['admin_id']} ";
    } elseif ($admin_id) {
        $sql .= " AND u.admin_id=$admin_id ";
    }

    // 最近三天确认收货的顾客
    $res_three = $GLOBALS['db']->getAll($sql." AND i.receive_time>$days_three GROUP BY i.order_id ORDER BY u.service_time ASC");

    foreach ($res_three as &$val)
    {
        $val['take_timetable'] = $val['goods_name'].date('Y-m-d',$val['take_time']);
        $val['receive_time']   = date('Y-m-d',$val['receive_time']);
        $val['add_time']       = date('Y-m-d', $val['add_time']);

        if ($val['recently'] != '-')
        {
            $val['recently'] = date('Y-m-d', $val['recently']);
        }
    }

    // 最近五天确认收货的顾客
    //$res_five = $GLOBALS['db']->getAll($sql." AND i.receive_time>$days_five");

     /*foreach ($res_three as &$val)
     {
         $sql = 'SELECT o.goods_name, FROM_UNIXTIME(i.receive_time+g.take_days,"%Y-%m-%d") take_date FROM '.
             $GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('order_goods').' o,'.
             $GLOBALS['ecs']->table('order_info').' i '.
             " WHERE o.goods_id=g.goods_id AND i.order_id=o.order_id AND o.order_id={$val['order_id']}";
         $val['time_table'] = $GLOBALS['db']->getAll($sql);
     }
      */

    $smarty->assign('user_list', $res_three);
    $smarty->assign('full_page', 1);

    // 是否显示转顾客
    if (admin_priv('reassign_user', '', false)) {
        $smarty->assign('reassign_user', 1);
    }

    assign_query_info();
    $res['main'] = $smarty->fetch('first_trace.htm');

    die($json->encode($res));
}

/* 预约服务 */
elseif ($_REQUEST['act'] == 'check')
{
    $res = array ('switch_tag' => true, 'id' => 3);

    $smarty->assign('user_ranks',   $ranks);
    $smarty->assign('ur_here',      $_LANG['02_serve_check']);

    $sql = 'SELECT u.user_id, u.user_name, u.mobile_phone, u.home_phone, u.age_group, u.sex, '.
        's.handler,s.service_time,u.admin_name,s.logbook FROM '.$GLOBALS['ecs']->table('users').
        ' u, '.$GLOBALS['ecs']->table('service').' s WHERE u.user_id=s.user_id AND s.handler<>0 ';
    $sql .= " AND u.admin_id={$_SESSION['admin_id']} ";

    $now_time = time();
    $sql .= " AND s.service_time=u.service_time AND s.handler>$now_time ORDER BY s.handler DESC";
    $user_list = $GLOBALS['db']->getAll($sql);
    foreach ($user_list as &$val)
    {
        $val['handler']      = date('Y-m-d H:i', $val['handler']);
        $val['service_time'] = date('Y-m-d', $val['service_time']);
    }

    $smarty->assign('user_list',    $user_list);
    $smarty->assign('action',       $_SESSION['action_list']);
    $smarty->assign('full_page',    1);
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');

    // 是否显示转顾客
    if (admin_priv('reassign_user', '', false)) {
        $smarty->assign('reassign_user', 1);
    }

    assign_query_info();
    $res['main'] = $smarty->fetch('check_list.htm');
    die($json->encode($res));
}

/* 重复购买的顾客 */
elseif ($_REQUEST['act'] == 'repeat')
{
    /* 检查权限 */
    admin_priv('users_list');
    $res = array ('switch_tag' => true, 'id' => 5);

    $smarty->assign('user_ranks',   $ranks);
    $smarty->assign('country_list',  get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    if (!isset($_REQUEST['number_purchased'])){
        $_REQUEST['number_purchased'] = 2;
    }

    $user_list = user_list();

    /* 获取顾客来源、购买力、客服 */
    $smarty->assign('from_where', get_from_where());
    $smarty->assign('type_list',  get_customer_type());
    $smarty->assign('admin_list', get_admin('session'));
    $smarty->assign('eff_list',   getEffectTypes());

    $smarty->assign('is_intention', $_REQUEST['act']);  // 意向顾客查询字段，为分页提供区分支持
    $smarty->assign('user_list',    $user_list['user_list']);

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('tag',          $_REQUEST['tag'] ? $_REQUEST['tag'] :0);
    $smarty->assign('user_id',$_REQUEST['user_id']);

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');

    //判断客服的权限，是否显示团队搜索
    if($_SESSION['action_list'] == 'all')
    {
        $smarty->assign('admin_show_team',1);
        $smarty->assign('role_list', get_role());
    }
    else
    {
        $sql = 'SELECT manager,role_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id={$_SESSION['admin_id']}";
        $user = $GLOBALS['db']->getRow($sql);
        if($user['manager'] === '0')
        {
            $smarty->assign('role_id',$user['role_id']);
            $smarty->assign('show_team',1);
        }
    }

    // 是否显示转顾客
    if (admin_priv('reassign_user', '', false)) {
        $smarty->assign('reassign_user', 1);
    }

    $res['main'] = $smarty->fetch('repeat_list.htm');

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    die($json->encode($res));
}

/* 顾客详细信息 */
elseif ($_REQUEST['act'] == 'user_detail') {
    $user_id = intval($_REQUEST['id']);
    $res['id'] = $user_id;
    $res['response_action'] = 'detail';
    $smarty->assign('user_id',$res['id']);

    $order_list   = access_purchase_records($user_id); // 获取顾客购买记录（订单记录）
    $service_list = get_user_services($user_id);       // 获取顾客服务记录
    $addr_list    = get_addr_list($user_id);           // 顾客地址列表
    $contact_list = get_contact_list($user_id);        // 顾客联系方式列表
    $user_info    = get_user_info($user_id);           // 获取顾客基本资料

    $mem = new Memcache;
    $mem->connect('127.0.0.1',11211);

    if(!$mem->get("freeze_{$_SESSION['admin_id']}")){
        $contact_list = get_contact_list($user_id);        // 顾客联系方式列表
    }
    //$return_list = get_return_list($user_id); // 获取顾客的退货记录
    $user_friends = get_user_friends($user_id);

    $case = get_before_case();         //既往病例

    //获取家长成员
    if($user_info['family_id'] != 0)
    {
        $sql_select = 'SELECT u.user_id,u.user_name,u.mobile_phone,u.home_phone,g.grade_name,g.grade_id,f.family_name,m.input_time,m.real_parent FROM '.$GLOBALS['ecs']->table('user_family_member')
            .' AS m LEFT JOIN '.$GLOBALS['ecs']->table('users')
            .' AS u ON u.user_id=m.user_id LEFT JOIN '
            .$GLOBALS['ecs']->table('user_family_grade')
            .' AS g ON m.grade_id=g.grade_id LEFT JOIN '
            .$GLOBALS['ecs']->table('user_family').' AS f ON u.family_id=f.family_id '
            .'WHERE m.family_id='.$user_info['family_id'].' AND m.status=0 AND g.type=0';

        $family_users = $GLOBALS['db']->getAll($sql_select);
        $family = array('family_name'=>$family_users[0]['family_name'],'family_total'=>count($family_users));
        $smarty->assign('family',$family);

        for($i = 0; $i < count($family_users); $i++)
        {
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('other_examination_test').
                " WHERE user_id={$family_users[$i]['user_id']}";
            $family_users[$i]['healthy_file'] = $GLOBALS['db']->getOne($sql_select);
            $family_users[$i]['input_time'] = date('Y-m-d',$family_users[$i]['input_time']);
        }

        $smarty->assign('family_users',$family_users);
    }

    /* 会员充值和提现申请记录 */
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20;
    }

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM ".$ecs->table('user_account')." WHERE user_id='$user_id'"
        ." AND process_type ".db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
    $record_count = $db->getOne($sql);

    $filter['record_count'] = $record_count;
    $filter['page_count']   = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页
    $page_set = array (1,2,3,4,5,6,7);
    if ($filter['page'] > 4) {
        foreach ($page_set as &$val) {
            $val += $filter['page'] -4;
        }
    }

    if (end($page_set) > $filter['page_count']) {
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--) {
            if ($filter['page_count'] - $i > 0) {
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
        'act'           => 'user_detail',
    );

    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    $sql = "SELECT SUM(user_money) FROM ".$GLOBALS['ecs']->table('account_log')." WHERE user_id='$user_id'";

    if (empty($surplus_amount)) {
        $surplus_amount = 0;
    }

    //获取会员帐号明细
    $account_log = array();    //申请记录
    $account_detail = array(); //账号明细

    $sql_select = "SELECT * FROM ".$ecs->table('account_log')." WHERE user_id='$user_id'".' ORDER BY log_id DESC LIMIT '
        .($filter['start']-1)*$filter['page_size'].",{$filter['page_size']}";
    $account_detail = $GLOBALS['db']->getAll($sql_select);

    foreach($account_detail AS &$val) {
        $val['change_time']      = date('Y-m-d H:i:s',$val['change_time']);
        $val['admin_note']       = nl2br(htmlspecialchars($val['admin_note']));
        $val['short_admin_note'] = ($val['admin_note'] > '') ? sub_str($val['admin_note'], 30) : 'N/A';
        $val['pay_status']       = ($val['is_paid'] == 0) ? $GLOBALS['_LANG']['un_confirm'] : $GLOBALS['_LANG']['is_confirm'];
        $val['amount']           = price_format(abs($val['amount']), false);
        $val['user_money']       = price_format(abs($val['user_money']), false);

        if ($val['process_type'] == 0) {
            $val['type'] = $GLOBALS['_LANG']['surplus_type_0'];
        } else {
            $val['type'] = $GLOBALS['_LANG']['surplus_type_1'];
        }
    }

    $sql_select = "SELECT * FROM ".$GLOBALS['ecs']->table('user_account')." WHERE user_id=$user_id LIMIT ".
        ($filter['start']-1) * $filter['page_size'].",{$filter['page_size']}";

    $account_log = $GLOBALS['db']->getAll($sql_select);

    lange_account($account_log);

    //体检项目
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('examination');
    $smarty->assign('examination',$GLOBALS['db']->getAll($sql_select));

    //模板赋值
    $smarty->assign('count_log',      $record_count);
    $smarty->assign('account_detail', $account_detail);
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('lang',           $_LANG);
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('action',         'account_log');
    $smarty->assign('filter',         $filter);

    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('user_rank').' WHERE role_id=(SELECT role_id FROM '.
        $GLOBALS['ecs']->table('users')." WHERE user_id=$user_id) OR role_id = 0";
    $smarty->assign('user_rank',$GLOBALS['db']->getAll($sql_select));

    // 订单类型
    $sql_select = 'SELECT type_id,type_name FROM '.$GLOBALS['ecs']->table('order_type');
    // 验证是否是组长
    if (admin_priv('employee_order', '', false)) {
        $sql_select .= ' WHERE available=1 ORDER BY sort DESC';
        $smarty->assign('delivery_plus', 1);
    } else {
        $sql_select .= ' WHERE available=1 AND type_id<100 ORDER BY sort DESC';
    }

    $order_type = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('order_type', $order_type);

    $healthy_file = get_healthy($user_id);
    $isexistHF    = $healthy_file['baseInfo']['total'];

    $smarty->assign('healthy_file', $healthy_file);
    $smarty->assign('case_list',    $case['case_list']);  //idsease
    $smarty->assign('before_case',  $case['before_case']);  //idsease
    $smarty->assign('isexistHF',    $isexistHF);

    $smarty->assign('disease',        get_disease());              // diseases
    $smarty->assign('characters',     get_characters());           // characters
    $smarty->assign('payment',        payment_list());             // payment
    $smarty->assign('surplus_payment', payment_list()); // 充值提现支付方式
    $smarty->assign('shipping',       shipping_list(3));           // shipping
    $smarty->assign('service_class',  get_service_class());        // servcie class
    $smarty->assign('service_manner', get_service_manner());       // sercie manner
    $smarty->assign('integral_log',   get_integral_log($user_id)); // integral log
    $smarty->assign('bmi',get_user_bmi($user_id));


    if (in_array($_SESSION['admin_id'],array(1,493,2,4))) {
        $admin_list = get_admin_tmp_list();
        $smarty->assign('all_power',true);
    }elseif(in_array($_SESSION['admin_id'],array(520,359))){
        $sql = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE role_id={$_SESSION['role_id']}";
        $admin_list = $GLOBALS['db']->getAll($sql);
        if ($admin_list === false) {
            $admin_list = get_admin_tmp_list($_SESSION['role_id']);
        }
        $smarty->assign('all_power',true);
    }else{
        $admin_list = get_admin_tmp_list(1);
    }
    $smarty->assign('admin_list',     $admin_list);
    $smarty->assign('platform_list',  get_role_list(1));

    $smarty->assign('province_list',  get_regions(1, 1));
    $smarty->assign('city_list',      get_regions(2, $user_info['province_id']));
    $smarty->assign('district_list',  get_regions(3, $user_info['city_id']));

    $smarty->assign('order_source',   order_source_list());
    $sql= 'SELECT ext FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id={$_SESSION['admin_id']}";
    $ext = $GLOBALS['db']->getOne($sql);

    $smarty->assign('ext',$ext);
    $smarty->assign('user',            $user_info);    // 顾客信息
    $smarty->assign('order_list',      $order_list);   // 购买记录
    $smarty->assign('service',         $service_list); // 服务记录
    $smarty->assign('return',          $return_list);  // 退货记录
    $smarty->assign('user_friends',    $user_friends);

    $smarty->assign('contact_list', $contact_list);
    $smarty->assign('addr_list',    $addr_list);

    $smarty->assign('role_id', $_SESSION['role_id']?$_SESSION['role_id']:0);
    // 营销方式优选
    //$smarty->assign('marketing_list',    marketing_list());

    // 顾客喜欢的联系方式
    //$smarty->assign('marketing_checked_list', marketing_checked_list($user_id, 'marketing_name'));

    $healthy_lifestyle = $smarty->fetch('healthy_file_part.htm');
    $smarty->assign('healthy_lifestyle',$healthy_lifestyle);
    $smarty->assign('service_time', date('Y-m-d H:i'));

    $smarty->assign('order_source',   order_source_list());
    $sql= 'SELECT ext FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id={$_SESSION['admin_id']}";
    $ext = $GLOBALS['db']->getOne($sql);
    $smarty->assign('ext',$ext);

    $res['info'] = $smarty->fetch('users_detail.htm');
    die($json->encode($res));
}

/* 添加顾客 */
elseif($_REQUEST['act'] == 'add_users')
{
    /* 检查权限 */
    $user = array(
        'rank_points' => $_CFG['register_points'],
        'pay_points'  => $_CFG['register_points'],
        'sex'         => 0,
        'credit_line' => 0
    );

    // 取出注册扩展字段
    $sql = 'SELECT * FROM '.$ecs->table('reg_fields').
        ' WHERE type<2 AND display=1 AND id<>6 ORDER BY dis_order, id';
    $extend_info_list = $db->getAll($sql);

    //是否是添加家庭成员
    if(isset($_REQUEST['family_id']))
    {
        $family_id = intval($_REQUEST['family_id']);
        $user_id = intval($_REQUEST['user_id']);
        if($family_id == 0)
        {
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_family_member')
                .'(user_id,family_id,add_time)VALUES('
                ."$user_id,$user_id,".time().')';
            $result = $GLOBALS['db']->query($sql_insert);
        }
        $smarty->assign('family_id',$user_id);
    }

    // 给模板赋值
    $smarty->assign('extend_info_list', $extend_info_list);
    $smarty->assign('ur_here',          $_LANG['04_users_add']);
    $smarty->assign('action_link',      array('text' => $_LANG['01_users_list'], 'href'=>'users.php?act=list'));
    $smarty->assign('form_action',      'insert');
    $smarty->assign('start_index',      0);
    $smarty->assign('user',             $user);
    $smarty->assign('special_ranks',    get_rank_list(true));

    $smarty->assign('country_list', get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    //获取顾客类型
    $smarty->assign('customer_type', get_customer_type($customer_type));
    //获取顾客来源
    $smarty->assign('from_where', get_from_where());
    //获取顾客经济来源
    $smarty->assign('income', get_income());
    //获取疾病列表
    $smarty->assign('disease', get_disease());
    // 添加顾客的客服所属部门
    $smarty->assign('role_id', $_SESSION['role_id']);
    // 获取性格列表
    $sql = 'SELECT character_id, characters FROM '.$ecs->table('character').' ORDER BY sort ASC';
    $smarty->assign('character', $db->getAll($sql));
    // 获取顾客分类
    $smarty->assign('effects',getEffectTypes());
    // 获取销售平台列表
    $smarty->assign('role_list', get_role_list(1));
    $smarty->assign('row_number',       2);
    assign_query_info();
    $smarty->assign('ur_here', $_LANG['02_users_add']);
    //$smarty->display('user_info.htm');

    $res['main']=$smarty->fetch('add_custom.htm');

    die($json->encode($res));
}

/* 添加客户 */
elseif($_REQUEST['act'] == 'add_custom') {
    $area_code = mysql_real_escape_string(trim($_POST['area_code']));
    $hphone    = mysql_real_escape_string(trim($_POST['hphone']));
    $mphone    = mysql_real_escape_string(trim($_POST['mphone']));

    //查询条件为家庭号码
    $hphone = "$area_code-$hphone";
    if ('-' != $hphone) {
        $where = " u.home_phone='$hphone'";
    }

    //查询条件为手机号码
    if(isset($where) && $mphone) {
        $where .= " OR u.mobile_phone='$mphone'";
    } elseif ($mphone) {
        $where = " u.mobile_phone='$mphone'";
    }

    // 查询顾客表中的信息
    $sql = 'SELECT u.admin_name FROM '.$ecs->table('users')." u WHERE $where";
    $admin_name = $db->getOne($sql);
    if (!empty($admin_name)) {
        die("该客户已经是{$admin_name}的顾客了");
    }

    // 查询临时顾客表中的信息
    $sql = 'SELECT u.admin_name FROM '.$ecs->table('userssyn')." u WHERE $where";
    $admin_name = $db->getOne($sql);
    if(!empty($admin_name)) {
        die("该顾客已经同步到本地，请勿重复添加！");
    }

    echo 1;
    exit;
}

/* 修改顾客信息 */
elseif ($_REQUEST['act'] == 'edit')
{
    $res['response_action'] = 'edit_user';
    $id = intval($_REQUEST['id']); // 顾客ID
    $request = addslashes_deep($_REQUEST);

    if (!in_array($request['info'], array ('district','address','marketing')))
    {
        $sql_select = "SELECT add_time,{$request['info']} FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id=$id";
        $user_info = $GLOBALS['db']->getRow($sql_select);
        if ($user_info['add_time'] < time() -3*24*3600 && !empty($user_info[$request['info']]) && in_array($request['info'], array ('home_phone', 'mobile_phone','qq','aliww')))
        {
            $res['code']    = 2;
            $res['req_msg'] = true;
            $res['timeout'] = 2000;
            $res['message'] = '该顾客资料已不允许修改';

            die($json->encode($res));
        }
    }

    // 修改地址信息
    if ($_REQUEST['info'] == 'district')
    {
        $smarty->assign('province_list', get_regions(1,1));
    }

    // 修改详细地址
    if ($_REQUEST['info'] == 'address')
    {
        $sql_select = 'SELECT address FROM '.$GLOBALS['ecs']->table('user_address')." WHERE user_id=$id";
        $val = $GLOBALS['db']->getOne($sql_select);
    }

    if ($_REQUEST['type'] == 'text' && $_REQUEST['info'] != 'address') {
        $sql_select = "SELECT {$request['info']} FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id=$id";
        $val = $GLOBALS['db']->getOne($sql_select);

        if (!isset($val)) {
            $val = '';
        }
    }

    if ($_REQUEST['type'] == 'select' && $_REQUEST['info'] != 'district')
    {
        $sql_select = "SELECT {$request['info']} FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id=$id";
        $val = $GLOBALS['db']->getOne($sql_select);

        switch ($_REQUEST['info'])
        {
        case 'role_id' :
            $list = list_role_common();
            break;
        case 'eff_id' :
            $list = list_effects_common();
            break;
        case 'customer_type' :
            if (in_array($_SESSION['role_id'],array(34,35,36))) {
                $customer_where = ' type_id NOT IN (21) AND ';
            }
            $list = list_customer_type($customer_where);
            break;
        case 'from_where' :
            $list = list_from_where();
        }

        $smarty->assign('list', $list);
    }

    if ($_REQUEST['type'] == 'checkbox') {
        if ($_REQUEST['info'] == 'marketing') {
            $val = marketing_list();
            $smarty->assign('marketing_checked_list', marketing_checked_list($id, 'marketing_id'));
        }
    }

    $res['info'] = $_REQUEST['info'];
    $res['id']   = $id;
    $res['act']  = 'edit';
    $res['type'] = $_REQUEST['type'];

    $smarty->assign('type',  $_REQUEST['type']);
    $smarty->assign('field', $_REQUEST['info']);
    $smarty->assign('value', $val);
    $res['main'] = $smarty->fetch('detail.htm');

    die($json->encode($res));
}

/* 保存顾客信息 */
elseif ($_REQUEST['act'] == 'save')
{
    $user_id = intval($_REQUEST['id']); // 订单ID

    // 转义所有数据 包括数组的key
    $request = addslashes_deep($_REQUEST);
    $request['type'] = strtolower($request['type']);

    $res['id']      = $user_id;
    $res['info']    = $request['info'];
    $res['type']    = $request['type'];
    $res['req_msg'] = true;
    $res['message'] = '顾客信息修改成功！';
    $res['timeout'] = 2000;

    if ($res['info'] == 'address' && empty($request['value'])){
        $res['message'] = '顾客地址不能为空！';
        die($json->encode($res));
    }

    // 修改疾病与性格
    if (in_array($_REQUEST['info'], array('disease', 'characters')))
    {
        $sql_select = "SELECT {$request['info']} FROM ".$GLOBALS['ecs']->table('users').
            " WHERE user_id=$user_id";
        $val_exist = $GLOBALS['db']->getOne($sql_select);
        $exist = strpos(':'.$val_exist.':', ':'.$request['value'].':');
        if ($exist !== false)
        {
            $val_exist = str_replace(":{$request['value']}", '', $val_exist);
        }
        else
        {
            $val_exist .= ':'.$request['value'];
        }

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').
            " SET {$request['info']}='$val_exist' WHERE user_id=$user_id";
        $GLOBALS['db']->query($sql_update);
    }

    // 验证联系方式是否已存在
    if ('text' == $request['type'] && !in_array($_REQUEST['info'], array('district','user_name','address'))) {
        $sql_select = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('users')." WHERE {$request['info']}='{$request['value']}'";
        $is_exist = $GLOBALS['db']->getOne($sql_select);
        if ($is_exist) {
            $msg = array ('req_msg'=>true,'timeout'=>2000,'message'=>'已有相同信息存在，请认真核对！');
            die($json->encode($msg));
        }
    }

    // 修改信息
    if (in_array($request['type'], array('text','select','radio')) && !in_array($_REQUEST['info'], array('district','address')))
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET {$request['info']}='{$request['value']}' WHERE user_id=$user_id";
        if ($GLOBALS['db']->query($sql_update))
        {
            $sql_select = "SELECT {$request['info']} FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id=$user_id";
            $res['main'] = $GLOBALS['db']->getOne($sql_select);

            switch ($request['info'])
            {
            case 'eff_id' : $sql_select = 'SELECT eff_name FROM '.
                $GLOBALS['ecs']->table('effects')." WHERE eff_id={$res['main']}";
                $res['main'] = $GLOBALS['db']->getOne($sql_select);
                break;
            case 'role_id' : $sql_select = 'SELECT role_name FROM '.
                $GLOBALS['ecs']->table('role')." WHERE role_id={$res['main']}";
                $res['main'] = $GLOBALS['db']->getOne($sql_select);
                break;
            case 'sex' : $res['main'] = $res['main'] ? $res['main'] == 1 ? '男' : '女' : '不详';
                break;
            case 'from_where' :
                $sql_select = 'SELECT `from` FROM '.
                    $GLOBALS['ecs']->table('from_where')." WHERE from_id={$res['main']}";
                $res['main'] = $GLOBALS['db']->getOne($sql_select);
                break;
            case 'customer_type':
                $sql_select = 'SELECT type_name FROM '.$GLOBALS['ecs']->table('customer_type')." WHERE type_id={$res['main']}";
                $res['main'] = $GLOBALS['db']->getOne($sql_select);
                break;
            }
        }
        else
        {
            $res['message'] = '修改失败！';
        }
    }

    // 保存地址信息
    if ($_REQUEST['info'] == 'district')
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_address').
            " SET province={$request['province']},city={$request['city']},".
            " district={$request['district']} WHERE user_id=$user_id";
        if ($GLOBALS['db']->query($sql_update))
        {
            $sql_select = 'SELECT p.region_name province,c.region_name city,d.region_name district FROM '.
                $GLOBALS['ecs']->table('user_address').' u LEFT JOIN '.$GLOBALS['ecs']->table('region').
                ' p ON u.province=p.region_id LEFT JOIN '.$GLOBALS['ecs']->table('region').
                ' c ON u.city=c.region_id LEFT JOIN '.$GLOBALS['ecs']->table('region').
                ' d ON d.region_id=u.district'." WHERE u.user_id=$user_id";
            $region = $GLOBALS['db']->getAll($sql_select);

            $res['main'] = implode('', $region[0]);
        }
    }

    // 保存详细地址信息
    if ($_REQUEST['info'] == 'address')
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_address').
            " SET address='{$request['value']}' WHERE user_id=$user_id";
        $GLOBALS['db']->query($sql_update);

        $sql_select = 'SELECT address FROM '.$GLOBALS['ecs']->table('user_address').
            " WHERE user_id=$user_id";
        $res['main'] = $GLOBALS['db']->getOne($sql_select);
    }

    // 保存顾客愿意使用的联系方式
    if ($_REQUEST['info'] == 'marketing') {
        $marketing_list = $json->decode($_REQUEST['value']);
        if (!empty($marketing_list)) {
            // 先确定哪些选项是已经存在的
            $sql_select = 'SELECT marketing_id FROM '.$GLOBALS['ecs']->table('user_marketing')." WHERE user_id=$user_id";
            $tmp_marketing = $GLOBALS['db']->getCol($sql_select);

            $delete_marketing_list = array_diff($tmp_marketing, $marketing_list); // 要删除的优先联系方式
            $marketing_list        = array_diff($marketing_list, $tmp_marketing); // 要添加的优先联系方式

            $change = 0;
            $failure_msg = array(
                '0'=> '修改优先联系方式成功！',
                '1'=> '添加新联系方式失败，请查看是否需要再次修改！',
                '2'=> '删除新联系方式失败，请查看是否需要再次修改！',
                '3'=> '添加和删除方式都失败了，请稍后再试！',
            );
            if (!empty($marketing_list) && !new_marketing($marketing_list, $user_id)) {
                $change++;
            }

            if (!empty($delete_marketing_list) && !new_marketing($delete_marketing_list, $user_id, 'del')) {
                $change += 2;
            }

            $sql_select = 'SELECT marketing_name FROM '.$GLOBALS['ecs']->table('marketing').' m LEFT JOIN '.
                $GLOBALS['ecs']->table('user_marketing')." u ON u.marketing_id=m.marketing_id WHERE u.user_id=$user_id";
            $now_marketing = $GLOBALS['db']->getCol($sql_select);

            $res['main'] = implode('、', $now_marketing);
            $res['message'] = $failure_msg[$change];
        } else {
            $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('user_marketing')." WHERE user_id=$user_id";
            if ($GLOBALS['db']->query($sql_delete)) {
                $res['main'] = '';
                $res['type'] = 'checkbox';
            }
        }

        $res['type'] = 'checkbox';
    }

    // 记录客服操作
    record_operate($sql_update, 'ordersyn_info');

    // 需要修改的数据是否是顾客出生日期
    if (isset($request['lunar'])) {// && $request['lunar'] == 2) {

         /* 阳历转为阴历
         require_once 'includes/cls_lunar.php';
         $lunar = new Lunar;

         list($year, $month, $date) = explode('-', $request['value']);

         $lunar_date = $lunar->convertSolarToLunar($year, $month, $date);

         $request['value'] = "$lunar_date[0]-$lunar_date[4]-$lunar_date[5]";
          */

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET calendar={$request['lunar']} WHERE user_id=$user_id";
        $GLOBALS['db']->query($sql_update);
        $res['main'] .= $request['lunar'] == 1 ? '【阴历】' : '【阳历】';
    }

    die($json->encode($res));
}

// 添加包邮卡
elseif ($_REQUEST['act'] == 'add_freecard')
{
    $user_id = intval($_GET['user_id']);
    $user_name = mysql_real_escape_string(trim($_GET['user_name']));
    $admin_id = $_SESSION['admin_id'];

    /* START 包邮卡编号生成 */
    //获取$admin_id所属平台
    $sql = 'SELECT r.role_describe FROM ' .$GLOBALS['ecs']->table('admin_user').' a, '
        .$GLOBALS['ecs']->table('role'). " r WHERE a.role_id=r.role_id AND a.user_id=$admin_id";
    $admin = $GLOBALS['db']->getOne($sql);
    $mon = date(m);
    $str_rand = "012356789";
    $rand = "";
    $num = 7 - strlen($user_id);
    for($i=0;$i<$num;$i++){
        $rand .= substr($str_rand,rand(0,8),1);
    }
    $freecard_num = str_replace(' ','',$admin.$mon).' '.chunk_split($rand.'0'.$user_id,"4"," ");
    //    $freecard_str = str_replace(' ','',$freecard_num);
    //    $freecard_num = chunk_split($freecard_str,"4"," ");
    /* END 包邮卡编号生成 */

    $smarty->assign('freecard_num',$freecard_num);
    $smarty->assign('user_id',$user_id);
    $smarty->assign('user_name',$user_name);
    //     $smarty->assign('free_type',$free_type);
    $smarty->assign('free_platform',$free_platform);

    //编辑包邮卡信息初始化页面
    if($_REQUEST['handle'] == 'edit')
    {
        $smarty->assign('show',1);
        $sql = 'SELECT free_limit,effective_date,free_type,free_platform,free_remarks FROM ' .$GLOBALS['ecs']->table('free_postal_card'). ' WHERE user_id = '.$user_id;
        $res = $GLOBALS['db']->query($sql);

        $free = mysql_fetch_assoc($res);
        $smarty->assign('free',$free);
        die($smarty->fetch('free_postal_card.htm'));
    }

    //添加包邮卡信息初始化页面
    else{
        $smarty->assign('show',0);
        die($smarty->fetch('free_postal_card.htm'));
    }
}

//把包邮卡信息插入数据库中
elseif ($_REQUEST['act'] == 'insert_free')
{
    $relat_info = array
        (
            'user_id'   => $_POST['user_id'],
            'freecard_num'   => str_replace(' ','',$_POST['freecard_num']),
            'free_limit'    => $_POST['free_limit'],
            'effective_date'     => $_POST['effective_date'],
            'free_type'   => $_POST['free_type'],
            'free_platform' => $_POST['free_platform'],
            'free_remarks'   => $_POST['free_remarks'],
        );
    $fields = array_keys($relat_info);
    $values = array_values($relat_info);
    //更新包邮卡信息到数据库
    if($_REQUEST['update'] == 'update'){
        $count = count($fields);
        for($i=0;$i<$count;$i++){
            $insert .= $fields[$i]."='".$values[$i]."',";
        }
        $insert=rtrim($insert,',');
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('free_postal_card').' SET '.$insert.' WHERE user_id= '.$_POST['user_id'];
        $GLOBALS['db']->query($sql);
        die("更新成功！！");
    }
    //插入包邮卡信息到数据库
    else{
        $sql = 'SELECT freecard_id FROM '.$GLOBALS['ecs']->table('free_postal_card'). ' WHERE user_id = '.$_POST['user_id'];
        $res = $GLOBALS['db']->query($sql);
        if(mysql_fetch_assoc($res)){ die('顾客包邮卡已经添加了！！'); }
        else{
            $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('free_postal_card').'('.implode(',',$fields).')VALUES(\''.implode('\',\'',$values).'\')';
            $GLOBALS['db']->query($sql);
            die("顾客包邮卡添加成功！！");
        }
    }
}

//高级转移顾客
elseif ($_REQUEST['act'] == 'advance_batch')
{
    $from_admin    = intval($_REQUEST['from_admin']);
    $to_admin      = intval($_REQUEST['to_admin']);
    $customer_type = intval($_REQUEST['customer_type']);
    $ser_startTime = strtotime($_REQUEST['ser_startTime']);
    $ser_endTime   = strtotime($_REQUEST['ser_endTime']);
    $buy_startTime = strtotime($_REQUEST['buy_startTime']);
    $buy_endTime   = strtotime($_REQUEST['buy_endTime']);
    $add_startTime = strtotime($_REQUEST['add_startTime']);
    $add_endTime   = strtotime($_REQUEST['add_startTime']);

    $where = ' where 1 ';
    if(empty($to_admin)) {
        $res = crm_msg('操作失败');
        die($json->encode($res));
    }elseif(!empty($from_admin)){
        $where .= " AND admin_id=$from_admin ";
    }

    if(!empty($ser_startTime) && !empty($ser_endTime)) {
        $where .= " AND service_time BETWEEN $ser_startTime AND $ser_endTime";
    }

    if(!empty($add_startTime) && !empty($add_endTime)) {
        $where .= " AND add_time BETWEEN $buy_startTime AND $add_endTime";
    }

    if(!empty($buy_startTime) && !empty($buy_endTime)) {
        $where .= " AND order_time BETWEEN $buy_startTime AND $buy_endTime";
    }
    $sql = ' SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').$where;
    $count = $GLOBALS['db']->getOne($sql);
    $admin_name = $GLOBALS['db']->getOne("SELECT user_name FROM".$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$to_admin");
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET customer_type=$customer_type,admin_id=$to_admin,admin_name='$admin_name',assign_time={$_SERVER['REQUEST_TIME']}"
        .$where;
    $code = $GLOBALS['db']->query($sql);
    if ($code) {
        $res = crm_msg("成功转了{$count}个顾客。",$code);
    }else{
        $res = crm_msg('操作失败');
    }
    die($json->encode($res));
}


/*------------------------------------------------------ */
//-- ajax返回用户列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{

    $user_list = user_list();

    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('action',       $_SESSION['action_list']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);

    $sort_flag  = sort_flag($user_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 会员部处理重新分配顾客 */
    if ($_SESSION['role_id'] == 9)
    {
        $smarty->assign('effects', getEffectTypes());
    }

    //显示一个月内转移的顾客
    if($user_list['filter']['transfer_time']){
        $smarty->assign('transfer',1);
    }

    make_json_result($smarty->fetch('users_list.htm'), '', array('filter' => $user_list['filter'], 'page_count' => $user_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加会员帐号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 检查权限 */
    admin_priv('users_add');

    $user = array(
        'rank_points' => $_CFG['register_points'],
        'pay_points'  => $_CFG['register_points'],
        'sex'         => 0,
        'credit_line' => 0
    );

    /* 取出注册扩展字段 */
    $sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 AND id != 6 ORDER BY dis_order, id';
    $extend_info_list = $db->getAll($sql);
    $smarty->assign('extend_info_list', $extend_info_list);
    $smarty->assign('ur_here',          $_LANG['04_users_add']);
    $smarty->assign('action_link',      array('text' => $_LANG['01_users_list'], 'href'=>'users.php?act=list'));
    $smarty->assign('form_action',      'insert');
    $smarty->assign('start_index',      0);
    $smarty->assign('user',             $user);
    $smarty->assign('special_ranks',    get_rank_list(true));

    $smarty->assign('country_list', get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    //获取顾客类型
    $smarty->assign('customer_type', get_customer_type());

    //获取顾客来源
    $smarty->assign('from_where', get_from_where());

    //获取顾客经济来源
    $smarty->assign('income', get_income());

    //获取疾病列表
    $smarty->assign('disease', get_disease());

    // 获取性格列表
    $sql = 'SELECT character_id, characters FROM '.$ecs->table('character').' ORDER BY sort ASC';
    $smarty->assign('character', $db->getAll($sql));

    /* 获取顾客分类 */
    $sql = 'SELECT eff_id, eff_name FROM '.$ecs->table('effects').' WHERE available=1 ORDER BY sort ASC';
    $smarty->assign('effects', $db->getAll($sql));

    /* 获取销售平台列表 */
    $smarty->assign('role_list', get_role_list(1));

    $smarty->assign('row_number',       2);
    assign_query_info();
    $smarty->assign('ur_here', $_LANG['02_users_add']);

    $smarty->display('user_info.htm');
}

/*------------------------------------------------------ */
//-- 添加会员帐号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert') {
    /* 检查权限 */
    //admin_priv('users_add');
    $res['response_action'] = $_REQUEST['act'];

    $res['code']    = 0;
    $res['req_msg'] = 'true';
    $res['timeout'] = 2000;

    $area_code    = mysql_real_escape_string(trim($_POST['area_code']));
    $home_phone   = mysql_real_escape_string(trim($_POST['home_phone']));
    $mobile_phone = mysql_real_escape_string(trim($_POST['mobile_phone']));

    if (!empty($area_code))
        $home_phone = $area_code.'-'.$home_phone;

    if ($_POST['home_phone'] && $_POST['mobile_phone'])
        $repeat_where = " home_phone='$home_phone' OR mobile_phone=$mobile_phone" ;
    elseif($_POST['home_phone'])
        $repeat_where = " home_phone='$home_phone' ";
    elseif($_POST['mobile_phone'])
        $repeat_where = " mobile_phone='$mobile_phone'";

    $sql = 'SELECT COUNT(*) FROM '.$ecs->table('users')." WHERE $repeat_where";
    if ($repeat_where && $db->getOne($sql)) {
        $res['message'] = '该顾客已存在！';
        die($json->encode($res));
    }

    // 顾客基本信息
    $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
    $sex = in_array($sex, array(0, 1, 2)) ? $sex : 0;
    $userinfo = array (
        'user_name'     => mysql_real_escape_string(trim($_POST['username'])),     // 顾客姓名
        'eff_id'        => intval($_POST['eff_id']),                               // 功效分类
        'sex'           => $sex,                                                   // 性别
        'birthday'      => trim($_POST['birthday']),                               // 出生日期
        'age_group'     => mysql_real_escape_string(trim($_POST['age_group'])),    // 年龄段
        'from_where'    => intval($_POST['from_where']),                           // 顾客来源
        'customer_type' => intval($_POST['customer_type']),                        // 顾客类型
        'mobile_phone'  => mysql_real_escape_string(trim($_POST['mobile_phone'])), // 手机号码
        'id_card'       => mysql_real_escape_string(trim($_POST['id_card'])),      // 身份证号码
        'member_cid'    => mysql_real_escape_string(trim($_POST['member_cid'])),   // 会员卡号
        'qq'            => mysql_real_escape_string($_POST['qq']),                 // 腾讯QQ
        'aliww'         => mysql_real_escape_string($_POST['aliww']),              // 阿里旺旺
        'habby'         => mysql_real_escape_string($_POST['habby']),              // 兴趣爱好
        'email'         => mysql_real_escape_string($_POST['email']),              // 电子邮箱
        'occupat'       => mysql_real_escape_string($_POST['occupat']),            // 顾客职业
        'income'        => mysql_real_escape_string($_POST['income']),             // 经济来源

        'disease'    => isset($_POST['disease'])   &&is_array($_POST['disease'])?implode(',',$_POST['disease']):'', // 疾病
        'characters' => isset($_POST['characters'])&&is_array($_POST['characters'])?','.implode(',',$_POST['characters']).',':'', // 性格

        'disease_2'   => mysql_real_escape_string($_POST['disease_2']),   // 其他疾病
        'remarks'     => mysql_real_escape_string($_POST['remarks']),     // 备注
        'admin_id'    => $_SESSION['admin_id'],                           // 顾客归属
        'first_admin' => $_SESSION['admin_id'],                           // 添加顾客客服
        'add_time'    => time(),                                          // 添加时间
        'snail'       => mysql_real_escape_string($_POST['snail']),       // 平邮地址
        'team'        => intval($_POST['team']),                          // 所属团队
        'admin_name'  => $_SESSION['admin_name'],                         // 客服姓名
        'lang'        => intval($_POST['lang']),                          // 常用语言
        'parent_id'   => intval($_POST['parent_id']),                     // 推荐人
        'role_id'     => intval($_POST['role_id']) ? intval($_POST['role_id']) : $_SESSION['role_id'],
        'adder'       => $_SESSION['admin_id'],
    );

    $userinfo['email']      = empty($userinfo['email']) ? empty($userinfo['qq']) ? '' : $userinfo['qq'].'@qq.com' :$userinfo['email'];
    $userinfo['home_phone'] = empty($_REQUEST['area_code']) ? $_REQUEST['home_phone'] : $_REQUEST['area_code'].'-'.$_REQUEST['home_phone'];

    $userinfo['email']      = mysql_real_escape_string($userinfo['email']);
    $userinfo['home_phone'] = mysql_real_escape_string($userinfo['home_phone']);

    $ex_where = array();
    if ($userinfo['home_phone']) {
        $ex_where[] = "contact_name='tel' AND contact_value='{$userinfo['home_phone']}'";
    }

    if ($userinfo['mobile_phone']) {
        $ex_where[] = "contact_name='mobile' AND contact_value='{$userinfo['mobile_phone']}'";
    }

    if ($userinfo['qq']) {
        $ex_where[] = "contact_name='qq' AND contact_value='{$userinfo['qq']}'";
    }

    if ($userinfo['aliww']) {
        $ex_where[] = "contact_name='aliww' AND contact_value='{$userinfo['aliww']}'";
    }

    $ex_where = implode(') OR (', $ex_where);
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_contact')." WHERE ($ex_where)";
    if ($GLOBALS['db']->getOne($sql_select)) {
        $res['message'] = '该顾客已存在啦！';
        die($json->encode($res));
    }

    if ($_POST['calendar'] == 1) {
        $userinfo['birthday'] = $userinfo['birthday'];
    } else {
        require(dirname(__FILE__) . '/includes/lunar.php');
        $lunar = new Lunar();
        $userinfo['birthday'] = date('Y-m-d', $lunar->S2L($userinfo['birthday']));
    }

    // 顾客地址信息
    $addr = array(
        'country'   => intval($_POST['country']),  // 国家
        'province'  => intval($_POST['province']), // 省份
        'city'      => intval($_POST['city']),     // 城市
        'address'   => mysql_real_escape_string($_POST['address']),  // 详细地址
        'zipcode'   => intval($_POST['zipcode'])   // 邮编
    );

    if (!empty($_POST['district'])) {
        $addr['district'] = intval($_POST['district']); // 区县
    }

    if($_SESSION['role_id'] == 2) {
        $userinfo['dm'] = 1;
        $userinfo['mag_no'] = 1;
    }

    $users =& init_users();

    $user_id = $users->add_user($userinfo, $addr);

    if (!$user_id) {
        /* 插入会员数据失败 */
        if ($users->error == ERR_INVALID_USERNAME) {
            $msg = $_LANG['username_invalid'];
        } elseif ($users->error == ERR_NULL_PHONE) {
            $msg = $_LANG['null_phone'];
        } elseif ($users->error == ERR_INVALID_AREA) {
            $msg = $_LANG['invalid_area'];
        } elseif ($users->error == ERR_NULL_ADDR) {
            $msg = $_LANG['null_addr'];
        } else {
            //die('Error:'.$users->error_msg());
        }
        $res['message'] = $msg;
        die($json->encode($res));
    }

    if (empty($addr['zipcode'])) unset($addr['zipcode']);

    $addr['user_id'] = $user_id;
    $fields = array_keys($addr);
    $values = array_values($addr);
    $sql = 'INSERT INTO '.$ecs->table('user_address').'('.implode(',',$fields).')VALUES(\''.implode('\',\'',$values).'\')';
    $db->query($sql);

    $sql = 'UPDATE '.$ecs->table('users').' u, '.$ecs->table('user_address').' a '
        .' SET u.address_id=a.address_id WHERE u.user_id=a.user_id AND u.user_id='.$user_id;
    $db->query($sql);

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_contact').'(user_id,contact_name,contact_value,is_default) SELECT '
        ."%d,'%s','%s',%d FROM dual WHERE NOT EXISTS (SELECT * FROM ".$GLOBALS['ecs']->table('user_contact').
        " WHERE contact_name='%s' AND contact_value='%s')";

    // 固话
    $sql_insert_assign = sprintf($sql_insert, $user_id, 'tel', $userinfo['home_phone'], 1, 'tel', $userinfo['home_phone']);
    $db->query($sql_insert_assign);

    // 手机号码
    $sql_insert_assign = sprintf($sql_insert,$user_id,'mobile',$userinfo['mobile_phone'],1,'mobile',$userinfo['mobile_phone']);
    $db->query($sql_insert_assign);

    // QQ
    $sql_insert_assign = sprintf($sql_insert, $user_id, 'qq', $userinfo['qq'], 1, 'qq', $userinfo['qq']);
    $db->query($sql_insert_assign);

    // aliww
    $sql_insert_assign = sprintf($sql_insert, $user_id, 'aliww', $userinfo['aliww'], 1, 'aliww', $userinfo['aliww']);
    $db->query($sql_insert_assign);

    /* 推荐顾客赠送积分 */
    if (!empty($userinfo['parent_id'])) {
        include_once './includes/cls_integral.php';
        $integ = new integral($ecs->table('integral'), $db);
        $integral = $integ->countIntegral($userinfo['role_id'], 1);
        $validity = strtotime(date('Ym', time())+intval($integral['validity']));

        if ($integral['integral_way'] == 1) {
            $sql = 'INSERT INTO '.$ecs->table('user_integral').
                '(integral_id,exchange_points,source,source_id,receive_time,validity,user_id,admin_id)'.
                "VALUES('{$integral['integral_id']}','{$integral['scale']}','users','$user_id',".
                " UNIX_TIMESTAMP(),$validity,'{$userinfo['parent_id']}',{$_SESSION['admin_id']})";
            $db->query($sql);
        }
    }

    /* 记录管理员操作 */
    admin_log($_POST['username'], 'add', 'users');
    $uname = implode('', $_REQUEST['uname']);
    if (!empty($uname))
    {
        insertSocial();
    }

    $res['code']    = 1;
    $res['message'] = '顾客添加成功！';
    die($json->encode($res));
}

/*------------------------------------------------------ */
//-- 编辑顾客档案
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'file')
{
    //由于需要设置部分字段的次数，所以需要修改条件中的数字
    //该SQL需要进行深度修改
    $nowtime = time();
    $sql = 'SELECT user_id,sex,user_name,id_card,mobile_phone,home_phone,address,region, remarks FROM '.$GLOBALS['ecs']->table('files').' WHERE nullnum=0 AND noman<10 AND cannot<10 AND ok<>1 AND admin_id='.$_SESSION['admin_id']." AND add_time<$nowtime ORDER BY add_time ASC";
    $user = $db->getRow($sql);

    $smarty->assign('userinfo', $user);

    $smarty->display('users_file.htm');
}
elseif ($_REQUEST['act'] == 'checkfiles')
{
    if (array_key_exists('nullnum', $_POST))
    {
        //空号
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('files').' SET nullnum=1 WHERE user_id='.$_POST['user_id'];
        if($db->query($sql))
        {
            header('location:?act=file');
        }
    }
    elseif(array_key_exists('noman', $_POST))
    {
        //无人接听
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('files').' SET noman=noman+1, add_time=UNIX_TIMESTAMP() WHERE user_id='.$_POST['user_id'];
        if($db->query($sql))
        {
            header('location:?act=file');
        }
    }
    elseif (array_key_exists('cannot', $_POST))
    {
        //打不通
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('files').' SET cannot=cannot+1, add_time=UNIX_TIMESTAMP() WHERE user_id='.$_POST['user_id'];
        if($db->query($sql))
        {
            header('location:?act=file');
        }
    }
    elseif (array_key_exists('night', $_POST))
    {
        //晚上再打
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('files').' SET night=night+1, add_time=UNIX_TIMESTAMP() WHERE user_id='.$_POST['user_id'];
        if($db->query($sql))
        {
            header('location:?act=file');
        }
    }
    elseif (array_key_exists('ok', $_POST))
    {
        //有效
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('files').' SET ok=1 WHERE user_id='.$_POST['user_id'];
        if($db->query($sql))
        {
            $user = $_POST;

            $sql = 'SELECT * FROM '.$ecs->table('customer_type');
            $smarty->assign('customer_type', $db->getAll($sql));

            //获取顾客来源
            $sql = 'SELECT * FROM '.$ecs->table('from_where').' ORDER BY sort ASC';
            $smarty->assign('from_where', $db->getAll($sql));

            //获取顾客经济来源
            $sql = 'SELECT * FROM '.$ecs->table('income');
            $smarty->assign('income', $db->getAll($sql));

            //获取疾病列表
            $sql = 'SELECT * FROM '.$ecs->table('disease');
            $smarty->assign('disease', $db->getAll($sql));

            assign_query_info();
            $smarty->assign('ur_here',          $_LANG['users_edit']);
            $smarty->assign('action_link',      array('text' => $_LANG['03_users_list'], 'href'=>'users.php?act=list&' . list_link_postfix()));
            $smarty->assign('user',             $user);
            $smarty->assign('form_action',      'insert');
            $smarty->assign('special_ranks',    get_rank_list(true));
            $smarty->assign('country_list',     get_regions());
            $smarty->assign('province_list',    get_regions(1, 1));
            $smarty->assign('city_list',        get_regions(2, $user_region['province']));
            $smarty->assign('district_list',    get_regions(3, $user_region['city']));
            $smarty->assign('user_region',      $user_region);
            $smarty->display('user_info.htm');
        }
    }
}

/*------------------------------------------------------ */
//-- 更新用户帐号
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'update')
{
    /* 检查权限 */
    admin_priv('users_edit');
    $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
    $sex = in_array($sex, array(0, 1, 2)) ? $sex : 0;
    $userinfo = array(
        'user_id'       => $_POST['user_id'],
        'user_name'     => trim($_POST['username']),
        'eff_id'        => intval($_POST['eff_id']),
        'sex'           => $sex,
        'age_group'     => $_POST['age_group'],
        'from_where'    => $_POST['from_where'],
        'customer_type' => $_POST['customer_type'],
        'mobile_phone'  => trim($_POST['mobile_phone']),
        'id_card'       => trim($_POST['id_card']),
        'member_cid'    => trim($_POST['member_cid']),
        'qq'            => trim($_POST['qq']),
        'aliww'         => trim($_POST['aliww']),
        'habby'         => trim($_POST['habby']),
        'email'         => trim($_POST['email']),
        'occupat'       => trim($_POST['occupat']),
        'income'        => $_POST['income'],
        'disease'       => isset($_POST['disease']) && is_array($_POST['disease']) ? implode(',', $_POST['disease']) : '',
        'characters'    => isset($_POST['characters']) && is_array($_POST['characters']) ? ','.implode(',', $_POST['characters']).',' : '',
        'disease_2'     => trim($_POST['disease_2']),
        'remarks'       => trim($_POST['remarks']),
        //'parent_id'   => $_POST['recommender'],  // 推荐人信息
        'edit_time'     => time(),
        'snail'         => trim($_POST['snail']),
        'team'          => intval($_POST['team']),
        'admin_name'    => $_SESSION['admin_name'],
        'lang'          => intval($_POST['lang']), // 常用语言
        'parent_id'     => intval($_POST['parent_id']),
        'role_id'       => intval($_POST['role_id'])
    );

    if ($_POST['calendar'] == 1)
    {
        $userinfo['birthday'] = trim($_POST['birthday']);
    }
    else
    {
        require(dirname(__FILE__) . '/includes/lunar.php');
        $lunar = new Lunar();
        $userinfo['birthday'] = date('Y-m-d', $lunar->S2L(trim($_POST['birthday'])));
    }
    $userinfo['email'] = empty($userinfo['email']) ? empty($userinfo['qq']) ? '' : $userinfo['qq'].'@qq.com' :$userinfo['email'];

    if (empty($_POST['area_code']))
    {
        $userinfo['home_phone'] = $_POST['home_phone'];
    }
    else
    {
        $userinfo['home_phone'] = $_POST['area_code'].'-'.$_POST['home_phone'];
    }

    $addr = array(
        'user_id'   => $_POST['user_id'],
        'country'   => $_POST['country'],
        'province'  => $_POST['province'],
        'city'      => $_POST['city'],
        'district'  => $_POST['district'],
        'address'   => $_POST['address'],
        'zipcode'   => $_POST['zipcode']
    );

    $users  =& init_users();
    if($users->edit_user($userinfo, $addr))
    {
        foreach ($addr as $key=>$val)
        {
            if($key != 'user_id' || !empty($val))
                $addr2db[] = $key."='$val'";
        }

        $addr2db = implode(',', $addr2db);
        $sql = 'UPDATE '.$ecs->table('user_address')." SET $addr2db WHERE user_id=$addr[user_id]";
        $db->query($sql);
    }
    else
    {
        if ($users->error == ERR_EMAIL_EXISTS)
        {
            $msg = $_LANG['email_exists'];
        }
        else
        {
            $msg = $_LANG['edit_user_failed'];
        }
        sys_msg($msg, 1);
    }

    /* 更新用户扩展字段的数据 */
    //$sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有扩展字段的id
    //$fields_arr = $db->getAll($sql);
      /*$user_id_arr = $users->get_profile_by_name($username);
      $user_id = $user_id_arr['user_id'];

      foreach ($fields_arr AS $val)       //循环更新扩展用户信息
      {
            $extend_field_index = 'extend_field' . $val['id'];
            if(isset($_POST[$extend_field_index]))
            {
                  $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];

                  $sql = 'SELECT * FROM ' . $ecs->table('reg_extend_info') . "  WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
                  if ($db->getOne($sql))      //如果之前没有记录，则插入
                  {
                        $sql = 'UPDATE ' . $ecs->table('reg_extend_info') . " SET content = '$temp_field_content' WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
                  }
                  else
                  {
                        $sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . " (`user_id`, `reg_field_id`, `content`) VALUES ('$user_id', '$val[id]', '$temp_field_content')";
                  }
                  $db->query($sql);
            }
      }
       */

    /* 更新会员的其它信息 */
      /*$other =  array();
      $other['credit_line'] = $credit_line;
      $other['user_rank'] = $rank;

      $other['aliww'] = isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
      $other['qq'] = isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
      $other['office_phone'] = isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
      $other['home_phone'] = isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
      $other['mobile_phone'] = isset($_POST['extend_field5']) ? htmlspecialchars(trim($_POST['extend_field5'])) : '';

      $db->autoExecute($ecs->table('users'), $other, 'UPDATE', "user_name = '$username'");
       */
    /* 记录管理员操作 */
    admin_log($username, 'edit', 'users');

    /* 更新顾客社会关系 */
    $uname = implode('', $_REQUEST['uname']);
    if (!empty($uname))
    {
        updateSocial();
    }

    /* 提示信息 */
    $links[0]['text']    = $_LANG['goto_list'];
    $links[0]['href']    = 'users.php?act=list&' . list_link_postfix();
    $links[1]['text']    = $_LANG['go_back'];
    $links[1]['href']    = 'javascript:history.back()';

    sys_msg($_LANG['update_success'], 0, $links);

}

/*------------------------------------------------------ */
//-- 批量删除会员帐号
/*------------------------------------------------------ */

elseif($_REQUEST['act'] == 'del_rela')
{
    $sql = 'DELETE FROM '.$GLOBALS['ecs']->table('user_relation').
        " WHERE user_id={$_REQUEST['user_id']} AND rela_id={$_REQUEST['rela_id']}";
    if (1)//$GLOBALS['db']->query($sql))
    {
        echo 1;
    } else {
        echo 0;
    }

    exit;
}
/*------------------------------------------------------ */
//-- 批量删除会员帐号
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'batch_remove')
{
    /* 检查权限 */
    admin_priv('users_drop');

    if (isset($_POST['checkboxes']))
    {
        $sql = 'UPDATE '.$ecs->table('users').' SET admin_id=-1 WHERE user_id '.
            db_create_in($_POST['checkboxes']);
        $db->query($sql);
        //$sql = "SELECT user_name FROM " . $ecs->table('users') . " WHERE user_id " . db_create_in($_POST['checkboxes']);
        /*
        $col = $db->getCol($sql);
        $usernames = implode(',',addslashes_deep($col));
        $count = count($col);
         */
        /* 通过插件来删除用户 */
        //$users =& init_users();
        //$users->remove_user($col);

        admin_log($usernames, 'batch_remove', 'users');

        $lnk[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=list');
        sys_msg(sprintf($_LANG['batch_remove_success'], $count), 0, $lnk);
    }
    else
    {
        $lnk[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=list');
        sys_msg($_LANG['no_select_user'], 0, $lnk);
    }
}

/* 编辑用户名 */
elseif ($_REQUEST['act'] == 'edit_username')
{
    /* 检查权限 */
    check_authz_json('users_manage');

    $username = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));
    $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

    if ($id == 0)
    {
        make_json_error('NO USER ID');
        return;
    }

    if ($username == '')
    {
        make_json_error($GLOBALS['_LANG']['username_empty']);
        return;
    }

    $users =& init_users();

    if ($users->edit_user($id, $username))
    {
        if ($_CFG['integrate_code'] != 'ecshop')
        {
            /* 更新商城会员表 */
            $db->query('UPDATE ' .$ecs->table('users'). " SET user_name = '$username' WHERE user_id = '$id'");
        }

        admin_log(addslashes($username), 'edit', 'users');
        make_json_result(stripcslashes($username));
    }
    else
    {
        $msg = ($users->error == ERR_USERNAME_EXISTS) ? $GLOBALS['_LANG']['username_exists'] : $GLOBALS['_LANG']['edit_user_failed'];
        make_json_error($msg);
    }
}

/*------------------------------------------------------ */
//-- 编辑email
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_email')
{
    /* 检查权限 */
    check_authz_json('users_manage');

    $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $email = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));

    $users =& init_users();

    $sql = "SELECT user_name FROM " . $ecs->table('users') . " WHERE user_id = '$id'";
    $username = $db->getOne($sql);


    if (is_email($email))
    {
        if ($users->edit_user(array('username'=>$username, 'email'=>$email)))
        {
            admin_log(addslashes($username), 'edit', 'users');

            make_json_result(stripcslashes($email));
        }
        else
        {
            $msg = ($users->error == ERR_EMAIL_EXISTS) ? $GLOBALS['_LANG']['email_exists'] : $GLOBALS['_LANG']['edit_user_failed'];
            make_json_error($msg);
        }
    }
    else
    {
        make_json_error($GLOBALS['_LANG']['invalid_email']);
    }
}

/*------------------------------------------------------ */
//-- 删除会员帐号
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove')
{
    /* 检查权限 */
    admin_priv('users_drop');
    $sql = 'UPDATE '.$ecs->table('users')." SET admin_id=-1 WHERE user_id=$_GET[id]";
    $db->query($sql);


    //$sql = "SELECT user_name FROM " . $ecs->table('users') . " WHERE user_id = '" . $_GET['id'] . "'";
    //$username = $db->getOne($sql);
    /* 通过插件来删除用户 */
    //$users =& init_users();
    //$users->remove_user($username); //已经删除用户所有数据

    /* 记录管理员操作 */
    admin_log(addslashes($username), 'remove', 'users');

    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=list');
    sys_msg(sprintf($_LANG['remove_success'], $username), 0, $link);
}

/*------------------------------------------------------ */
//--  收货地址查看
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'address_list')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $sql = "SELECT a.*, c.region_name AS country_name, p.region_name AS province, ct.region_name AS city_name, d.region_name AS district_name ".
        " FROM " .$ecs->table('user_address'). " as a ".
        " LEFT JOIN " . $ecs->table('region') . " AS c ON c.region_id = a.country " .
        " LEFT JOIN " . $ecs->table('region') . " AS p ON p.region_id = a.province " .
        " LEFT JOIN " . $ecs->table('region') . " AS ct ON ct.region_id = a.city " .
        " LEFT JOIN " . $ecs->table('region') . " AS d ON d.region_id = a.district " .
        " WHERE user_id='$id'";
    $address = $db->getAll($sql);
    $smarty->assign('address',          $address);
    assign_query_info();
    $smarty->assign('ur_here',          $_LANG['address_list']);
    $smarty->assign('action_link',      array('text' => $_LANG['03_users_list'], 'href'=>'users.php?act=list&' . list_link_postfix()));
    $smarty->display('user_address_list.htm');
}

/*------------------------------------------------------ */
//-- 脱离推荐关系
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove_parent')
{
    /* 检查权限 */
    admin_priv('users_manage');

    $sql = "UPDATE " . $ecs->table('users') . " SET parent_id = 0 WHERE user_id = '" . $_GET['id'] . "'";
    $db->query($sql);

    /* 记录管理员操作 */
    $sql = "SELECT user_name FROM " . $ecs->table('users') . " WHERE user_id = '" . $_GET['id'] . "'";
    $username = $db->getOne($sql);
    admin_log(addslashes($username), 'edit', 'users');

    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=list');
    sys_msg(sprintf($_LANG['update_success'], $username), 0, $link);
}

/*------------------------------------------------------ */
//-- 查看用户推荐会员列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'aff_list')
{
    /* 检查权限 */
    admin_priv('users_manage');
    $smarty->assign('ur_here',      $_LANG['03_users_list']);

    $auid = $_GET['auid'];
    $user_list['user_list'] = array();

    $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    $smarty->assign('affiliate', $affiliate);

    empty($affiliate) && $affiliate = array();

    $num = count($affiliate['item']);
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
    {
        $count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;

        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, add_time ".
                " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)" .
                " ORDER by level, user_id";
            $user_list['user_list'] = array_merge($user_list['user_list'], $db->getAll($sql));
        }
    }

    $temp_count = count($user_list['user_list']);
    for ($i=0; $i<$temp_count; $i++)
    {
        $user_list['user_list'][$i]['add_time'] = date($_CFG['date_format'], $user_list['user_list'][$i]['add_time']);
    }

    $user_list['record_count'] = $all_count;

    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('full_page',    1);
    $smarty->assign('action_link',  array('text' => $_LANG['back_note'], 'href'=>"users.php?act=edit&id=$auid"));

    assign_query_info();
    $smarty->display('affiliate_list.htm');
}

/* 单个顾客转移-页面 */
elseif ($_REQUEST['act'] == 'transfer')
{
    $user['user_id'] = intval($_GET['user_id']);
    $user['user_name'] = mysql_real_escape_string(trim($_GET['user_name']));
    $sql = 'SELECT user_id, user_name FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE transfer=1';
    $result = $db->getAll($sql);

    $smarty->assign('user', $user);
    $smarty->assign('admin', $result);
    $smarty->display('users_transfer.htm');
}

/* 单个顾客转移-执行转移 */
elseif ($_REQUEST['act'] == 'give_up')
{
    $to_id = intval($_REQUEST['to_id']); // 目标客服id
    $user_id = intval($_REQUEST['uid']); // 要转赠的顾客id

    // 将返回的信息参数
    $res['id']      = $user_id;
    $res['info']    = $request['info'];
    $res['type']    = $request['type'];
    $res['req_msg'] = true;
    $res['timeout'] = 4000;

    // 查询目标客服与该顾客当前所属客服是否为同一人
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').
        " WHERE user_id=$user_id AND admin_id=$to_id";
    $is_same_admin = $GLOBALS['db']->getOne($sql_select);
    if ($is_same_admin){
        $res['message'] = '所属客服与目标客服一致，无需转赠！';
        die($json->encode($res));
    }

    // 查询目标客服是否可以接收顾客
    $sql_select = 'SELECT max_customer FROM '.$GLOBALS['ecs']->table('admin_user').
        " WHERE max_customer>0 AND user_id=$to_id";
    $max_customer = $GLOBALS['db']->getOne($sql_select);
    if (!empty($max_customer)){
        // 统计目标客服已经拥有的顾客数量
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users')." WHERE admin_id=$to_id";
        $had_users_number = $GLOBALS['db']->getOne($sql_select);
    } else {
        // 目标客服不允许接收顾客
        $res['message'] = '目标客服不适合接收顾客！';
        die($json->encode($res));
    }

    if ($max_customer > $had_users_number){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET last_admin=admin_id WHERE user_id=$user_id";
        $GLOBALS['db']->query($sql_update);

        $now_time = time();
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').' u, '.$GLOBALS['ecs']->table('admin_user').
            " a SET u.admin_id=$to_id,u.assign_time=$now_time,u.user_cat='',u.admin_name=a.user_name,u.role_id=a.role_id,".
            "u.group_id=a.group_id WHERE u.user_id=$user_id AND a.user_id=$to_id";
        $GLOBALS['db']->query($sql_update);

        $res['message'] = '顾客转赠成功！';
        $res['code'] = 1;
        die($json->encode($res));
    }
    else{
        // 目标客服的顾客数量已达到上限
        $res['message'] = '目标客服的顾客数量已达到上线！';
        die($json->encode($res));
    }
}

//查看顾客详细信息
elseif ($_REQUEST['act'] == 'get_detail')
{
    $sql = 'SELECT user_id,user_name,sex,IF(birthday="1952-01-01",age_group,CONCAT(LEFT(NOW(),4) - LEFT(birthday,4),"岁")) birthday,parent_id,from_where,home_phone,mobile_phone,id_card,email,qq,aliww,income,habby,disease,characters,remarks FROM '.
        $GLOBALS['ecs']->table('users').' WHERE user_id='.$_GET['user_id'];
    $result = $GLOBALS['db']->getRow($sql);
    extract($result);

    //获取客户来源
    $sql = 'SELECT `from` FROM '.$GLOBALS['ecs']->table('from_where').' WHERE from_id='.$from_where;
    $from_where = $GLOBALS['db']->getOne($sql);

    //获取经济来源
    $sql = 'SELECT income FROM '.$GLOBALS['ecs']->table('income').' WHERE income_id='.$income;
    $income = $GLOBALS['db']->getOne($sql);

    $sql = 'SELECT zipcode, address, province, city, district FROM '.
        $GLOBALS['ecs']->table('user_address').' WHERE user_id='.$user_id;
    $address = $GLOBALS['db']->getRow($sql);

    if($address['province'] && $address['city'])
    {
        extract($address);
        $province = get_address($province);
        $city     = get_address($city);
        if (!empty($district))
        {
            $district = get_address($district);
        }
        else
        {
            $district = '';
        }

        $address = $province.$city.$district.$address;
    }
    else
    {
        $address = "请到点击<a href='users.php?act=edit&id=$user_id' title='编辑'><img src='images/icon_edit.gif' alt='编辑' /></a>完善顾客地址信息！";
    }

    switch($sex)
    {
    case 1 : $sex = '男'; break;
    case 2 : $sex = '女'; break;
    case 0 : $sex = '未知'; break;
    }

    if (!empty($disease))
    {
        $sql = 'SELECT disease FROM '.$GLOBALS['ecs']->table('disease').' WHERE disease_id IN ('.$disease.')';
        $disease = $GLOBALS['db']->getAll($sql);

        foreach ($disease as $val)
        {
            $temp[] = $val['disease'];
        }

        $disease = implode(',', $temp);
        unset($temp);
    }

    if (!empty($characters) && $characters != ',')
    {
        $sql = 'SELECT characters FROM '.$GLOBALS['ecs']->table('character').
            ' WHERE character_id IN ('.substr($characters, 1, -1).')';
        $characters = $GLOBALS['db']->getAll($sql);

        foreach ($characters as $val)
        {
            $temp[] = $val['characters'];
        }

        $characters = implode('，', $temp);
        unset($temp);
    }

    // 获取服务信息
    $ex_where = " WHERE s.user_id={$_GET['user_id']} AND s.service_class=c.class_id AND s.service_manner=m.manner_id ";
    $sql = "SELECT s.admin_name,s.service_id,c.class service_class,m.manner service_manner,FROM_UNIXTIME(service_time,'%m月%d日 %H:%i') servicetime,user_name,service_status,logbook,admin_id FROM ".$GLOBALS['ecs']->table('service').' s,'.$GLOBALS['ecs']->table('service_class').' c,'.$GLOBALS['ecs']->table('service_manner').' m '.$ex_where." ORDER by service_time ASC";
    $list = $GLOBALS['db']->getAll($sql);

    $no = 0;
    foreach($list as $val)
    {
        //++$no;
        extract($val);
        $final .= '【'.$servicetime.'】<font color="olive">'.$admin_name.'</font>通过<font color="olive">'.$service_manner.'</font>进行<font color="olive">'.$service_class.'</font>：'.$logbook.'<br>';
    }

    /* 获取订单信息 */
    $sql = 'SELECT i.order_id,FROM_UNIXTIME(i.add_time,"%Y-%m-%d") add_time,i.final_amount,i.shipping_fee,r.role_name,i.admin_id,i.add_admin_id,i.platform FROM '.
        $GLOBALS['ecs']->table('order_info').' i LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' u ON i.add_admin_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('role').
        " r ON u.role_id=r.role_id WHERE i.pay_status=2 AND i.order_status=5 AND i.user_id={$_GET['user_id']} ORDER BY i.add_time DESC";
    $order_list = $GLOBALS['db']->getAll($sql);
    if(!empty($order_list))
    {
        foreach ($order_list as $val)
        {
            $sql = 'SELECT goods_id,goods_name, goods_number, goods_price FROM '.
                $GLOBALS['ecs']->table('order_goods')." WHERE order_id={$val['order_id']}";
            $goods_list = $GLOBALS['db']->getAll($sql);

            //$sql = 'SELECT r.role_name FROM '.$GLOBALS['ecs']->table('admin_user')." au, ".
            //$GLOBALS['ecs']->table('role').' r '." WHERE au.role_id=r.role_id AND au.user_id={$val['add_admin_id']}";
            $sql = 'SELECT role_name FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id={$val['platform']}";
            $platform = $GLOBALS['db']->getOne($sql);

            $order_record[$val['order_id']] = '【'.$val['add_time'].'】在<font color="red">&nbsp;&nbsp;'.
                $platform.'&nbsp;&nbsp;</font>购买<font color="olive"> ';
            foreach ($goods_list as $v)
            {
                $order_record[$val['order_id']] .= $v['goods_name'].' ('.$v['goods_number'].') ，&nbsp;';
            }

            $order_record[$val['order_id']] .= '</font>共'.$val['final_amount'].'元';
        }

        $order_record = implode('<br />', $order_record);
        $sql = 'SELECT SUM(goods_amount) FROM '.$GLOBALS['ecs']->table('order_info').
            " WHERE order_status=5 AND pay_status=2 AND user_id=".intval($_GET['user_id']);
        $all_money = '共'.$GLOBALS['db']->getOne($sql).'元';
    }

    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('user_photos')." WHERE user_id=".intval($_GET['user_id']);
    $photos = $GLOBALS['db']->getAll($sql);

    $smarty->assign('photos', $photos);

    $smarty->assign('user_id', intval($_GET['user_id']));
    $smarty->assign('user_name', $user_name);
    $smarty->assign('member_cid', $member_cid);
    $smarty->assign('id_card', $id_card);
    $smarty->assign('sex', $sex);
    $smarty->assign('zipcode', $zipcode);
    $smarty->assign('home_phone', $home_phone);
    $smarty->assign('mobile_phone', $mobile_phone);
    $smarty->assign('qq', $qq);
    $smarty->assign('birthday', $birthday);
    $smarty->assign('from_where', $from_where);
    $smarty->assign('aliww', $aliww);
    $smarty->assign('characters', $characters);
    $smarty->assign('address', $address);
    $smarty->assign('email', $email);
    $smarty->assign('remarks', $remarks);
    $smarty->assign('income', $income);
    $smarty->assign('disease', $disease);
    $smarty->assign('final', $final);
    $smarty->assign('all_money', $all_money);
    $smarty->assign('order_record', $order_record);

    die($smarty->fetch('detail.htm'));
}

// 验证顾客是否已经存在
elseif ($_REQUEST['act'] == 'is_repeat')
{
    $area_code = trim($_POST['area_code']);
    $hphone    = trim($_POST['hphone']);
    $mphone    = trim($_POST['mphone']);

    if(!empty($area_code))
    {
        $hphone = "$area_code-$hphone";
    }

    if($hphone)
    {
        $where = " home_phone='$hphone'";
    }

    if($where && $mphone)
    {
        $where .= " OR mobile_phone='$mphone'";
    }
    elseif($mphone)
    {
        $where = " mobile_phone='$mphone'";
    }

    $sql = 'SELECT admin_name FROM '.$ecs->table('users')." WHERE $where";
    $admin_name = $db->getOne($sql);

    if(!empty($admin_name))
    {
        die($admin_name);
    }
    else
    {
        die(0);
    }
}

// 快速添加服务
elseif ($_REQUEST['act'] == 'add_service')
{
    $smarty->assign('user_id', $_GET['user_id']);
    $smarty->assign('username', $_GET['username']);
    $smarty->assign('service_class', get_service_class());
    $smarty->assign('service_manner', get_service_manner());

    // 获取用户的生日信息
    $sql = 'SELECT birthday FROM '.$GLOBALS['ecs']->table('users').
        ' WHERE user_id='.intval($_GET['user_id']);
    $birthday = $GLOBALS['db']->getOne($sql);

    $invalid_birth = array ('0000-00-00', '1970-01-01', '1952-01-01');
    if (empty($birthday) || in_array($birthday, $invalid_birth))
    {
        $smarty->assign('birthday', $birthday);
    }

    // 获取性格列表
    $sql = 'SELECT character_id, characters FROM '.$ecs->table('character').' ORDER BY sort ASC';
    $smarty->assign('character', $db->getAll($sql));

    // 判断用户性格、杂志、专项服务、购买意向是否已经存在
    $sql = 'SELECT characters, dm, mag_no, purchase FROM '.$ecs->table('users')." WHERE user_id=$_GET[user_id]";
    $cdmp = $db->getRow($sql);
    $smarty->assign('has_character', $cdmp['characters']);
    $smarty->assign('dm', $cdmp['dm']);
    $smarty->assign('purchase', $cdmp['purchase']);

    // 获取评分类型
    $sql = 'SELECT grade_type_id,grade_type_name FROM '.$GLOBALS['ecs']->table('grade_type').'
        WHERE available = 1 ORDER BY sort';
    $grade_type = $GLOBALS['db']->getAll($sql);
    $smarty->assign('grade_type',$grade_type);

    // 初始化时间
    $smarty->assign('default_time', date('Y-m-d H:i', (time())));

    $smarty->assign('form', 1);

    die($smarty->fetch('fast_service.htm'));
}

// 快速添加服务----提交至数据库
elseif ($_REQUEST['act'] == 'fast_add')
{
    $service_info = array (
        'service_manner'   => intval($_POST['service_manner']),
        'service_class'    => intval($_POST['service_class']),
        'service_time'     => strtotime($_POST['service_time']),
        'service_status'   => intval($_POST['service_status']),
        //'special_feedback' => $_POST['special_feedback'],
        'logbook'          => mysql_real_escape_string(trim($_POST['logbook'])),
        'admin_id'         => $_SESSION['admin_id'],
        'admin_name'       => $_SESSION['admin_name'],
        'user_id'          => intval($_POST['user_id']),
        'user_name'        => mysql_real_escape_string(trim($_POST['user_name']))
    );

    // 优先保存顾客生日
    $birthday = mysql_real_escape_string(trim($_POST['birthday']));
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('users').
        " SET birthday='$birthday' WHERE user_id={$service_info['user_id']}";
    $GLOBALS['db']->query($sql);

    if(isset($_POST['handler']) && strlen($_POST['handler']) > 0)
    {
        $service_info['handler'] = strtotime($_POST['handler']);
    }

    $characters = substr($_POST['characters'], 1);
    $purchase = $_POST['purchase'];
    $service_info['service_time'] = $service_info['service_time'] ? $service_info['service_time'] : time();

    foreach ($service_info as $key=>$val)
    {
        if ($val)
        {
            if($key == 'special_feedback' && $val == 1)
                continue;
            $fields[] = $key;
            $values[] = $val;
        }
    }

    $sql = 'INSERT INTO '.$ecs->table('service').'('.implode(',', $fields)
        .')VALUES("'.implode('","', $values).'")';
    if ($db->query($sql))
    {
        if($characters)
        {
            $characters = ", u.characters=',$characters,'";
        }

        if ($purchase && $purchase != 1)
        {
            $dm = ", u.purchase='$purchase', u.dm=0";
        }

        $sql = 'UPDATE '.$ecs->table('users').' u, '.$ecs->table('service')." s SET u.service_time=$service_info[service_time] $characters $dm WHERE u.user_id=$service_info[user_id]";
        $db->query($sql);

        //获取service表关联service_id
        $sql = 'SELECT service_id FROM '.$GLOBALS['ecs']->table('service').
            "WHERE service_time = $service_info[service_time] AND user_id = $service_info[user_id]";
        $service_id = $GLOBALS['db']->getOne($sql);
        // 获取评分类型
        $sql = 'SELECT grade_type_id FROM '.$GLOBALS['ecs']->table('grade_type').'
            WHERE available = 1 ORDER BY sort';
        $grade_type = $GLOBALS['db']->getCol($sql);
        $insert = '';
        foreach($grade_type as $grade){
            $i = grade.$grade;
            if($_REQUEST[$i]){
                $insert .= "('".$grade."','".$_REQUEST[$i]."','.$service_id.'),";
            }
        }
        $insert = rtrim($insert,',');
        if($insert){
            $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('grade').' (grade_type_id,grade_type_value,service_id)'.'
                VALUES '.$insert;
            $GLOBALS['db']->query($sql);
        }

        die($smarty->fetch('fast_service.htm'));
    }

}

/*批量分配顾客 */
elseif ($_REQUEST['act'] == 'batch')
{
    // 这里改成相应的权限，也需要改的： line 533
    if (admin_priv('all', '', false)) {
        $admin_list = get_admin_by_all();
        $role_info = get_role_by_all();
    } elseif (admin_priv('batch_transfer', '', false)) {
        $sql = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE role_id='.$_SESSION['role_id'];
        $admin_list = admin_list_by_role($role_list);
    } else {
        $res = crm_msg('对不起，您还不能访问该页面！');
        die($json->encode($res));
    }
    $smarty->assign('customer_type',get_customer_type('1,2,3,4,21,22'));
    $smarty->assign('effect',list_effects_common());
    $smarty->assign('admin_list', $admin_list);
    $smarty->assign('role_info',$role_info);
    $res['main']=$smarty->fetch('batch_transfer.htm');
    die($json->encode($res));
}

/* 转顾客操作 */
elseif($_REQUEST['act'] == 'from_to')
{
    if (!admin_priv('batch_transfer', '', false)) {
        $res = array (
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '对不起，当前账号无法进行批量操作',
        );

        echo $json->encode($res);
        return;
    }

    $to_admin           = intval($_REQUEST['to_admin']);
    $from_admin         = intval($_REQUEST['from_admin']);
    $where = "WHERE admin_id=$from_admin AND is_black<>1 AND is_black<>3";

    if (isset($_REQUEST['from_customer_type'])) {
        $from_customer_type = intval($_REQUEST['from_customer_type']);
        $to_customer_type   = intval($_REQUEST['to_customer_type']);
        if ($from_customer_type) {
            $where .= " AND customer_type=$from_customer_type";
        }
        if ($to_customer_type) {
            $sql_update .= ",customer_type=$to_customer_type";
        }
    }elseif(isset($_REQUEST['from_effect'])){
        $from_effect = intval($_REQUEST['from_effect']);
        if ($from_effect) {
            $where .= " AND eff_id=$from_effect";
        }
    }
    $transfer_num       = intval($_REQUEST['transfer_num']);

    // 获取目标客服的信息
    $sql = 'SELECT user_name,role_id,group_id,max_customer FROM '.
        $GLOBALS['ecs']->table('admin_user')." WHERE user_id=$to_admin";
    $res = $GLOBALS['db']->getRow($sql);
    if (empty($res)) {
        $res = array (
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '未找到目标客服！',
        );
        echo $json->encode($res);
        return;
    }

    $to_admin_name = $res['user_name'];
    $to_role_id    = $res['role_id'];
    $to_group_id   = $res['group_id'];

    // 统计目标客服当前的顾客数量
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users')." WHERE admin_id=%d";
    $to_admin_counter = $GLOBALS['db']->getOne(sprintf($sql_select, $to_admin));

    if (($to_admin_counter + $transfer_num) > $res['max_customer']) {
        $res = array (
            'req_msg' => true,
            'timeout' => 3000,
            'message' => "目标客服顾客数量已达上限【{$res['max_customer']}】或转入后超出数量上限，禁止转入！",
        );

        echo $json->encode($res);
        return;
    }

    // 统计将转入的顾客数量
    if (!$transfer_num) {
        $source_admin_counter = $GLOBALS['db']->getOne(sprintf($sql_select, $from_admin));
        if ($to_admin_counter + $source_admin_counter > $res['max_customer']) {
            $res = array (
                'req_msg' => true,
                'timeout' => 2000,
                'message' => "目标客服顾客数量已达上限【{$res['max_customer']}】或转入后超出数量上限，禁止转入！",
            );
            echo $json->encode($res);
            return;
        }
    }


    $sql = 'UPDATE '.$ecs->table('users')." SET admin_id=$to_admin,admin_name='$to_admin_name',role_id=$to_role_id,".
        "group_id=$to_group_id,last_admin=$from_admin,assign_time=UNIX_TIMESTAMP()$sql_update $where";
    if ($transfer_num) {
        $sql .= " LIMIT $transfer_num";
    }

    if($db->query($sql)) {
        $res = array (
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '顾客转入成功！',
        );
        echo $json->encode($res);
        return;
    }
}

/* 转移部分顾客归属 */
elseif ($_REQUEST['act'] == 'part_transfer')
{
    if (!admin_priv('part_transfer', '', false)) {
        $res = array(
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '对不起，您还不能访问该页面！',
        );

        die($json->encode($res));
    }

    //获取存在客户或者临时客户的名字
    $admin_list = get_admin_tmp_list('session');
    $smarty->assign('admin_list', $admin_list);
    $res['main'] = $smarty->fetch('part_transfer.htm');

    die($json->encode($res));
}

/* 顾客转移 */
elseif($_REQUEST['act'] == 'transfer_submit')
{
    $from_phone = htmlspecialchars($_POST['from_phone']); //联系电话
    $from_phone = preg_split('/[^0-9\-]+/',$from_phone);  //从非数字和-中分割字符串
    $from_phone = array_filter($from_phone);   //去除值为空的元素
    $from_phone = array_slice($from_phone,0);    //数组键值从0开始排序
    $phone = array();
    for($i=0;$i<count($from_phone);$i++){     //获取格式正确的电话号码或手机号码
        if(preg_match('/^(\d{3}-)(\d{8})$|^(\d{4}-)(\d{7,8})$|^(\d{11})$/',$from_phone[$i])){
            $phone[] = $from_phone[$i];
        }
    }

    $from_phone = implode('\',\'',$phone);
    if(empty($from_phone) || $from_phone==0){
        $link[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=part_transfer');
        sys_msg('顾客联系方式有误', 1, $link);
        exit;
    }

    //获取转移目标客服
    $to_admin = intval($_POST['to_admin']);  //目标客服id
    $sql = 'SELECT user_name, role_id, group_id FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE user_id='.$to_admin;
    $res = $GLOBALS['db']->getRow($sql);  //目标客服name

    $to_admin_name = $res['user_name'];
    $role_id       = $res['role_id'];
    $group_id      = $res['group_id'];

    $to_admin_2 = $_POST['to_admin_2'];  //再次输入的目标客服的name

    //判断转移目标客服是否一致
    if(!empty($to_admin_name) && !empty($to_admin_2) && $to_admin_name == $to_admin_2){
        //检查客服的权限
        if($_SESSION['action_list'] == 'all'){
            $sql = 'SELECT admin_id FROM '.$GLOBALS['ecs']->table('users')."
                WHERE mobile_phone IN ('".$from_phone."') or home_phone IN ('".$from_phone."')";
            $from_admin = $GLOBALS['db']->getOne($sql);
            if(empty($from_admin)){
                $link[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=part_transfer');
                sys_msg('顾客联系方式有误', 1, $link);
            }
            $where = 'admin_id>0';
        } else {
            $from_admin = $_SESSION['admin_id'];  //转移客服
            $where = 'admin_id='.$_SESSION['admin_id'];
        }
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('users').' SET admin_id='.$to_admin.",admin_name='".
            $to_admin_name."', role_id=$role_id,group_id=$group_id WHERE ".$where." AND mobile_phone IN ('".$from_phone.
            "') OR home_phone IN ('".$from_phone."')";
        $result = $GLOBALS['db']->query($sql);
        $transfer_num = mysql_affected_rows();
        if($transfer_num == 0 || empty($transfer_num)){
            $link[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=part_transfer');
            sys_msg('转移出错', 1, $link);
        }

        if($result){
            //获取转移的顾客的user_id
            $sql = ' SELECT user_id FROM '.$GLOBALS['ecs']->table('users').
                " WHERE mobile_phone IN ('".$from_phone."') OR home_phone IN ('".$from_phone."')";
            $from_userid = $GLOBALS['db']->getCol($sql);
            $user_id = implode(',',$from_userid);

            //获取转移的时间戳
            $transfer_time = strtotime("now");

            //转移记录插入数据库
            $sql = ' INSERT INTO '.$GLOBALS['ecs']->table('transfer_record').
                "(from_admin,to_admin,handler_admin,transfer_time,transfer_user,transfer_num)VALUES('".
                $from_admin."','".$to_admin."','".$_SESSION['admin_id']."','".$transfer_time."','".
                $user_id."','".$transfer_num."')";
            $GLOBALS['db']->query($sql);
            //转移的时间戳插入到user表中
            $user_id = implode('\',\'',$from_userid);
            $sql = ' UPDATE '.$GLOBALS['ecs']->table('users').' SET transfer_time='.$transfer_time."
                WHERE user_id IN ('".$user_id."')";
            $GLOBALS['db']->query($sql);

            $transfer_time=strtotime("now")-30*24*3600;
            $link[] = array('text'=>$_LANG['go_back'], 'href'=>"users.php?act=list&transfer_time=$transfer_time");
            sys_msg('转移成功，返回一个月内转移顾客列表',0,$link);
        }
    } else {
        $link[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=part_transfer');
        sys_msg('转移目标客服输入不一致', 1, $link);
    }
}

/* 重新分配 */
elseif ($_REQUEST['act'] == 'setEffect') {
    //admin_priv('user_list');

    include_once(ROOT_PATH.'includes/cls_json.php');
    $json = new JSON;

    $userId = intval($_REQUEST['userId']);
    $effId  = intval($_REQUEST['effId']);

    /* 重置功效分配，以便可以修改 */
    if (isset($_REQUEST['req']) && $_REQUEST['req'] == 'showEdit')
    {
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('users').
            " SET eff_id=0 WHERE user_id=$userId";
        if ($GLOBALS['db']->query($sql))
        {
            $res = array(
                'code' => 2,
                'ele'  => $userId,
                'msg'  => '请刷新页面'
            );
            echo $json->encode($res);
            exit;
        }
    }


    if (isset($_REQUEST['req']) && $_REQUEST['req'] == 'showEdit')
    {
        $res = array(
            'code' => 2,
            'ele'  => $userId,
            'msg'  => getEffectTypes()
        );
        echo $json->encode($res);
        exit;
    }

    if (empty($userId))
    {
        echo $json->encode(array('code'=>0, 'msg'=>'用户ID丢失，请联系管理员！'));
        exit;
    }

    $sql = 'UPDATE '.$GLOBALS['ecs']->table('users').
        " SET eff_id=$effId WHERE user_id=$userId";
    if ($GLOBALS['db']->query($sql)) {
        $sql = 'SELECT eff_name FROM '.$GLOBALS['ecs']->table('effects').
            " WHERE eff_id=$effId";
        echo $json->encode(array('code'=> 1, 'msg'=> $GLOBALS['db']->getOne($sql), 'ele'=>$userId));
        exit;
    } else {
        echo $json->encode(array('code'=>0, 'msg'=> '出错啦，请稍后再试'));
        exit;
    }

}

/* 日期切换 */
elseif ($_REQUEST['act'] == 'calendar')
{
    require '..\includes\cls_json.php';
    require 'includes\lunar.php';

    $json  = new JSON;
    $lunar = new Lunar;

    if ($_REQUEST['type'] == 2) {
        $nl = date("Y-m-d",$lunar->S2L($_REQUEST['birthday']));
        echo $temp = $json->encode(array('type' => '农历：', 'date' =>$nl));
    } else {
        //$date = $lunar->convertLunarToSolar($date[0], $date[1], $date[2]);
        $gl = date("Y-m-d",$lunar->L2S($_REQUEST['birthday']));
        die($json->encode(array('type' => '公历：', 'date' => $gl)));
    }
}

/* 查找顾客 */
elseif ($_REQUEST['act'] == 'find_referrer')
{
    $user_id = intval($_REQUEST['keywords']);
    $sql = 'SELECT DISTINCT user_id,user_name FROM '.$GLOBALS['ecs']->table('users')." WHERE user_id=$user_id";
    if ($_SESSION['role_id'] > 0) {
        $sql .= " AND role_id={$_SESSION['role_id']} ";
    }
    $res = $GLOBALS['db']->getAll($sql);
    die($json->encode($res));
}

/* 售后服用时间预测 */
elseif ($_REQUEST['act'] == 'forecast')
{
    /* 统计每位客服的销售额 */
    $res = array ('switch_tag' => true, 'id' => 1);
    $end   = $time ? $start +3600*24*$time : strtotime('tomorrow') +3600*24;

    $forecast_list = forecast_list();
    $smarty->assign('orders', $forecast_list['forecast_list']);

    // 分页设置
    $smarty->assign('filter',       $forecast_list['filter']);
    $smarty->assign('record_count', $forecast_list['record_count']);
    $smarty->assign('page_count',   $forecast_list['page_count']);
    $smarty->assign('page_size',    $forecast_list['page_size']);
    $smarty->assign('page_start',   $forecast_list['start']);
    $smarty->assign('page_end',     $forecast_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $forecast_list['page_set']);
    $smarty->assign('page',         $forecast_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);

    // 是否显示转顾客
    if (admin_priv('reassign_user', '', false)) {
        $smarty->assign('reassign_user', 1);
    }

    $res['main'] = $smarty->fetch('forecast_list.htm');

    die($json->encode($res));
}

//会员等级列表
elseif ($_REQUEST['act'] == 'vip_list')
{
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0){
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }else{
        $filter['page_size'] = 20;
    }

    $sql_select = 'SELECT rank_id,rank_name FROM '.$GLOBALS['ecs']->table('user_rank').' ORDER BY level ASC';

    $user_rank  = $GLOBALS['db']->getAll($sql_select);

    $source     = isset($_REQUEST['source']) ? mysql_real_escape_string($_REQUEST['source']) : 'users';
    $by_upgrade = mysql_real_escape_string($_REQUEST['by_upgrade']);
    $sort_type  = empty($_REQUEST['sort_type']) ? 'DESC' : $_REQUEST['sort_type'];


    if($source == 'users' || empty($_REQUEST['sort'])){
        $user_sort = empty($_REQUEST['sort']) ? 'rank_points' : $_REQUEST['sort'];//默认按积分大小排
        $user_sort = ' ORDER BY '.$user_sort.' '.$sort_type;
    }elseif($source == 'order'){
        $order_sort = ' ORDER BY '.$_REQUEST['sort'].' '.$sort_type;
    }

    $rank_id     = isset($_REQUEST['rank_id']) ? $_REQUEST['rank_id'] : $user_rank[0]['rank_id'];
    $select_item = intval($_REQUEST['select_item']);
    $key_word    = mysql_real_escape_string($_REQUEST['key_word']);
    $role_id     = intval($_REQUEST['platform']);
    $group_id    = intval($_REQUEST['group_id']);
    $min_points  = intval($_REQUEST['min_points']);
    $max_points  = intval($_REQUEST['max_points']);
    $where       = $sel_condition =  '';

    if($select_item && $key_word != ''){
        if($select_item == 1){
            $where .= " AND card_number='$key_word'";
        }elseif($select_item == 2){
            $where .= " AND user_name LIKE '%$key_word%'";
        }
        $sel_condition = "&select_item=$select_item&key_word=$key_word";
    }

    if($role_id){
        $where         .= " AND role_id=$role_id ";
        $sel_condition .= "&platform=$role_id";
    }

    if($group_id){
        $where         .= " AND group_id=$group_id";
        $sel_condition .= "&group_id=$group_id";
    }

    if($min_points || $max_points){
        $where         .= " AND rank_points BETWEEN $min_points AND $max_points";
        $sel_condition .= '&min_points=$group_id';
    }

    $condition = "&rank_id=$rank_id&source=$source&sort=$sort&sort_type=$sort_type&from_sel=".$_REQUEST['from_sel'].$sel_condition;

    //会员列表：
    $sql_select = 'SELECT count(*) FROM '.$GLOBALS['ecs']->table('users').' u LEFT JOIN '
        .$GLOBALS['ecs']->table('memship_number').' m ON u.user_id=m.user_id AND m.user_id<>0 '
        ." WHERE user_rank=$rank_id AND customer_type IN('2, 3, 4, 5, 11') $where";

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
    $filter['page_count']   = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    $page_set = array (1,2,3,4,5,6,7);
    if ($filter['page'] > 4){
        foreach ($page_set as &$val){
            $val += $filter['page'] -4;
        }
    }

    if (end($page_set) > $filter['page_count']){
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--){
            if ($filter['page_count'] - $i > 0){
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
        'act'           => 'vip_list',
    );

    $limit = ' LIMIT '.($filter['page']-1)*$filter['page_size'].','.$filter['page_size'];

    $sql_select = 'SELECT u.user_id,user_name,rank_points,user_rank,m.card_number FROM '.$GLOBALS['ecs']->table('users')
        .' u LEFT JOIN '.$GLOBALS['ecs']->table('memship_number')
        .' m ON u.user_id=m.user_id AND m.user_id<>0 '
        ." WHERE user_rank=$rank_id AND customer_type IN('2, 3, 4, 5, 11') $where $user_sort $limit ";

    $user_list = $GLOBALS['db']->getAll($sql_select);

    if($user_list){
        foreach($user_list as $val){
            if($val['user_id']){
                $user_id_list[] = $val['user_id'];
            }
        }

        $user_id_list = implode("','",$user_id_list);

        $sql_select   = 'SELECT user_id,min(add_time) AS earliest_pur,COUNT(*) AS total,sum(final_amount) AS final_amount FROM '.
            $GLOBALS['ecs']->table('order_info').
            " WHERE user_id IN('$user_id_list')".
            ' GROUP BY user_id'.$order_sort;
        $order_list = $GLOBALS['db']->getAll($sql_select);

        for($i = 0; $i < count($user_list); $i++){
            for($j = 0; $j < count($order_list);$j++){
                if($user_list[$i]['user_id'] == $order_list[$j]['user_id']){
                    $user_list[$i] = array_merge($user_list[$i],$order_list[$j]);
                    unset($order_list[$j]);
                }
            }
        }

        $vip_list = $user_list;

        foreach($vip_list as &$val) {
            $val['earliest_pur'] = !$val['earliest_pur'] ? '-' : date('Y-m-d',$val['earliest_pur']);
            $val['recently_pur'] = !$val['recently_pur'] ? '-' :date('Y-m-d',$val['recently_put']);
            foreach($user_rank as $rank){
                if($val['user_rank'] + 1 == $rank['rank_id']) {
                    $val['upgrade_gap'] = $rank['min_points'] - $val['rank_points'];
                    $val['up_rank']     = $rank['rank_name'];
                    $val['up_rank_id']  = $rank['rank_id'];
                    break;
                }
            }
        }
        //是否按距上一等级积分值排序
        if(!empty($by_upgrade) && $by_upgrade == 'by_upgrade') {
            $sort_type == 'ASC' ? SORT_ASC : SORT_DESC;
            $vip_list  = array_sort($vip_list,'upgrade_gap',$sort_type);
        }
    }
    $sort_type = $sort_type == 'DESC' ? 'ASC' : 'DESC';

    $smarty->assign('sort_type',   $sort_type);
    $smarty->assign('rank_id',     $rank_id);
    $smarty->assign('filter',      $filter);
    $smarty->assign('condition',   $condition);
    $smarty->assign('section',     'by_rank');
    $smarty->assign('rank_list',   $vip_list);

    if($_REQUEST['from_sel']){
        $res['record_count']    = $filter['record_count'];
        $res['response_action'] = 'search_service';
        $res['main']            = $smarty->fetch('vip_part.htm');
    }else{
        $smarty->assign('platform',platform_list());
        $smarty->assign('user_rank',$user_rank);
        $res['main'] = $smarty->fetch('vip_list.htm');
    }

    die($json->encode($res));
}


/* 购买力顾客列表 */
elseif ($_REQUEST['act'] == 'user_buy_list') {
    /* 检查权限 */
    admin_priv('users_list');
    $res = array ('switch_tag' => true, 'id' => $_REQUEST['tag'] ? $_REQUEST['tag'] : 0);

    if($_SESSION['admin_id'] == 78){
        $_REQUEST['type'] = '13';
    }
    elseif (!$_REQUEST['type']){
        $_REQUEST['type'] = '2, 3, 4, 5, 11';
    }

    $smarty->assign('user_ranks',   $ranks);
    $smarty->assign('ur_here',      $_LANG['01_users_list']);
    $smarty->assign('country_list',  get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    $user_list = user_buy_list();

    /* 获取顾客来源、购买力、客服 */
    $smarty->assign('from_where', get_from_where());
    $smarty->assign('type_list',  get_customer_type());
    $smarty->assign('admin_list', get_admin('session'));
    $smarty->assign('eff_list',   getEffectTypes());

    $smarty->assign('is_intention', $_REQUEST['act']);  // 意向顾客查询字段，为分页提供区分支持
    $smarty->assign('user_list',    $user_list['user_list']);

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('tag',          $_REQUEST['tag'] ? $_REQUEST['tag'] :0);

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');

    //判断客服的权限，是否显示团队搜索
    if($_SESSION['action_list'] == 'all')
    {
        $smarty->assign('admin_show_team',1);
        $smarty->assign('role_list', get_role());
    }
    else
    {
        $sql = 'SELECT manager,role_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id={$_SESSION['admin_id']}";
        $user = $GLOBALS['db']->getRow($sql);
        if($user['manager'] === '0')
        {
            $smarty->assign('role_id',$user['role_id']);
            $smarty->assign('show_team',1);
        }
    }

    $res['main'] = $smarty->fetch('user_buy_list.htm');

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    die($json->encode($res));
}

//黑名单顾客
elseif ($_REQUEST['act'] == 'user_blacklist')
{
    $blacklist_user_status = intval($_REQUEST['status']);
    $user_name        = mysql_real_escape_string(trim($_REQUEST['user_name']));
    $phone            = mysql_real_escape_string(trim($_REQUEST['phone']));
    $operator_in      = intval($_REQUEST['operator_in']);

    $where = " WHERE b.status=$blacklist_user_status ";

    $condition = '';
    if($user_name != '')
    {
        $where .= " AND b.user_name LIKE '%$user_name%'";
        $condition .= "&user_name=$user_name";
    }

    if($phone != '')
    {
        $where .= " AND u.mobile_phone LIKE '%$phone%' ";
        $codition .= "&phone='$phone'";
    }

    if($operator_in != 0)
    {
        $where .= " AND b.operator_in=$operator_in ";
        $condtion .= "&operator_in=$operator_in";
    }

    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    else{
        $filter['page_size'] = 20;
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_blacklist').' b '.$where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页
    $page_set = array (1,2,3,4,5,6,7);
    if ($filter['page'] > 4)
    {
        foreach ($page_set as &$val){
            $val += $filter['page'] -4;
        }
    }

    if (end($page_set) > $filter['page_count'])
    {
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--)
        {
            if ($filter['page_count'] - $i > 0){
                $page_set[] = $filter['page_count'] - $i;
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
            'act'           => 'user_blacklist',
        );

        $sql_select = 'SELECT b.user_id,b.user_name,b.operator_in,b.reason,b.in_time,b.status AS blackstatus,bt.type_name,a.user_name AS admin_name,r.role_name FROM '
            .$GLOBALS['ecs']->table('user_blacklist').
            ' AS b LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' AS a ON b.admin_id=a.user_id'
            .' LEFT JOIN '.$GLOBALS['ecs']->table('role').' AS r ON b.role_id=r.role_id'
            .' LEFT JOIN '.$GLOBALS['ecs']->table('blacklist_type').' AS bt ON b.type_id=bt.type_id '
            .$where .' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

        $user_blacklist = $GLOBALS['db']->getAll($sql_select);

        foreach($user_blacklist AS &$val) {
            $val['in_time'] = date('Y-m-d',$val['in_time']);
        }

        $role = get_role();

        $sql_select   = 'SELECT type_id,type_name FROM '.$GLOBALS['ecs']->table('account_type');
        $account_type = $GLOBALS['db']->getAll($sql_select);

        $blacklist_type_list    = get_blacklist_type();

        $smarty->assign('role',$role);
        $smarty->assign('filter',$filter);
        $smarty->assign('user_blacklist',$user_blacklist);
        $smarty->assign('account_type',$account_type);
        $smarty->assign('blackstatus',$_REQUEST['status']);
        $smarty->assign('blacklist_type_list',$blacklist_type_list);


        if($_REQUEST['from_tab'] == 'from_tab'){
            $smarty->assign('condition',"&status={$_REQUEST['status']}");
            $res['response_action'] = 'search_service';
            $res['blackstatus'] = $_REQUEST['status'];
            $res['main'] = $smarty->fetch('sch_blacklist.htm');
        }else{
            $res['main'] = $smarty->fetch('user_blacklist.htm');
        }

        die($json->encode($res));
    }
}

//判断是否在黑名单
elseif ($_REQUEST['act'] == 'in_blacklist')
{
    $home_phone = trim(mysql_real_escape_string($_REQUEST['home_phone']));
    $mobile_phone = trim(mysql_real_escape_string($_REQUEST['mobile_phone']));
    $qq = trim(mysql_real_escape_string($_REQUEST['qq']));
    $aliww = trim(mysql_real_escape_string($_REQUEST['aliww']));

    $res['code'] = false;
    $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('account_blacklist');

    if(!empty($home_phone))
    {
        $where = " WHERE account_type=3 AND account_value='$home_phone'";
        if($GLOBALS['db']->getOne($sql.$where) > 0)
        {
            $res['code'] = true;
            $res['account_type'] = 'infos';
            die($json->encode($res));
        }
    }

    if(!empty($mobile_phone))
    {
        $where = " WHERE account_type=2 AND account_value='$mobile_phone'";
        if($GLOBALS['db']->getOne($sql.$where) > 0)
        {
            $res['code'] = true;
            $res['account_type'] = 'infos';
            die($json->encode($res));
        }
    }

    if(!empty($qq))
    {
        $where = " WHERE account_type=1 AND account_value='$qq'";
        if($GLOBALS['db']->getOne($sql.$where) > 0)
        {
            $res['code'] = true;
            $res['account_type'] = 'qq_alarm';
            die($json->encode($res));
        }
    }

    if(!empty($aliww))
    {
        $where = " WHERE account_type=4 AND account_value='$aliww'";
        if($GLOBALS['db']->getOne($sql.$where) > 0)
        {
            $res['code'] = true;
            $res['account_type'] = 'aliww_alarm';
            die($json->encode($res));
        }
    }
}

/* 顾客流失列表 */
elseif ($_REQUEST['act'] == 'outflow_list') {
    // 条件选项
    $tag_list = array(
        1  => '超过1个月',
        2  => '超过2个月',
        3  => '超过3个月',
        6  => '超过半年',
        12 => '超过1年',
        24 => '超过2年'
    );

    $tmp = array_keys($tag_list);
    if (!empty($_REQUEST['tag'])) {
        $start = calc_time($_REQUEST['tag']);

        $_REQUEST['start_time'] = $start['year'].'-'.$start['month'].'-01 00:00:00';
        $_REQUEST['end_time']   = date('Y-m-t 23:59:59');

        if (end($tag_list) != $tag_list[$_REQUEST['tag']]) {
            $key = array_search($_REQUEST['tag'], $tmp);
            $limit = calc_time($tmp[$key+1]);
            $_REQUEST['time_limit'] = $limit['year'].'-'.$limit['month'].'-01 00:00:00';
        }

        $res['tag'] = $_REQUEST['tag'];
        $condition = '&tag='.$_REQUEST['tag'];
    } else {
        $limit = calc_time(next($tmp));
        $_REQUEST['time_limit'] = $limit['year'].'-'.$limit['month'].'-01 00:00:00';
    }

    if (admin_priv('outflow_list_all', '', false)) {
        $platform = get_role_list(1);
        $platform_list = array ();
        foreach ($platform as $val) {
            $platform_list[$val['role_id']] = $val['role_name'];
        }

        $smarty->assign('platform',    $platform_list);

        $admin_list = get_admin_tmp_list();
    } elseif (admin_priv('outflow_list_part', '', false)) {
        $admin_list = get_admin_tmp_list($_SESSION['role_id']);
    } else {
        $_REQUEST['admin_id'] = $_SESSION['admin_id'];
    }

    if (!empty($admin_list)) {
        $admin = array ();
        foreach ($admin_list as $val) {
            $admin[$val['user_id']] = $val['user_name'];
        }

        $smarty->assign('admin_list',  $admin);
    }

    $outflow = outflow_list('order_time');

    if (!admin_priv('outflow_list_part', '', false)) {
        unset($_REQUEST['admin_id']);
    }

    $smarty->assign('tag_list', $tag_list);

    $smarty->assign('outflow_list', $outflow['outflow_list']);

    $smarty->assign('dst_script',  'users');
    $smarty->assign('act',         $_REQUEST['act']);

    $smarty->assign('condition',    $outflow['condition'].$condition);
    $smarty->assign('page',         $outflow['page']);
    $smarty->assign('page_count',   $outflow['page_count']);
    $smarty->assign('record_count', $outflow['record_count']);
    $smarty->assign('page_list',    $outflow['page_set']);
    $smarty->assign('page_start',   $outflow['start']);
    $smarty->assign('page_end',     $outflow['end']);
    $smarty->assign('page_size',    $outflow['page_size']);

    // 最终时间
    $smarty->assign('end_time', date('Y-m-t 23:59:59'));

    $res['main'] = $smarty->fetch('outflow_data.htm');
    $res['page'] = $smarty->fetch('page_fragment.htm');

    if (!(isset($_REQUEST['start_time']) || isset($_REQUEST['page_no']) || isset($_REQUEST['platform']) || isset($_REQUEST['admin_id']))) {
        $smarty->assign('data', $res['main']);
        $smarty->assign('page', $res['page']);
        $res['main'] = $smarty->fetch('outflow.htm');

        unset($res['page']);
    }

    die($json->encode($res));
}

/* 仿顾客流失列表 服务流失 */
elseif ($_REQUEST['act'] == 'outflow_list_service') {
    // 条件选项
    $tag_list = array(
        1  => '超过1个月',
        2  => '超过2个月',
        3  => '超过3个月',
        6  => '超过半年',
        12 => '超过1年',
        24 => '超过2年'
    );

    $tmp = array_keys($tag_list);
    if (!empty($_REQUEST['tag'])) {
        $start = calc_time($_REQUEST['tag']);

        $_REQUEST['start_time'] = $start['year'].'-'.$start['month'].'-01 00:00:00';
        $_REQUEST['end_time']   = date('Y-m-t 23:59:59');

        if (end($tag_list) != $tag_list[$_REQUEST['tag']]) {
            $key = array_search($_REQUEST['tag'], $tmp);
            $limit = calc_time($tmp[$key+1]);
            $_REQUEST['time_limit'] = $limit['year'].'-'.$limit['month'].'-01 00:00:00';
        }

        $res['tag'] = $_REQUEST['tag'];
        $condition = '&tag='.$_REQUEST['tag'];
    } else {
        $limit = calc_time(next($tmp));
        $_REQUEST['time_limit'] = $limit['year'].'-'.$limit['month'].'-01 00:00:00';
    }

    if (admin_priv('outflow_list_all', '', false)) {
        $platform = get_role_list(1);
        $platform_list = array ();
        foreach ($platform as $val) {
            $platform_list[$val['role_id']] = $val['role_name'];
        }

        $smarty->assign('platform',    $platform_list);

        $admin_list = get_admin_tmp_list();
    } elseif (admin_priv('outflow_list_part', '', false)) {
        $admin_list = get_admin_tmp_list($_SESSION['role_id']);
    } else {
        $_REQUEST['admin_id'] = $_SESSION['admin_id'];
    }

    if (!empty($admin_list)) {
        $admin = array ();
        foreach ($admin_list as $val) {
            $admin[$val['user_id']] = $val['user_name'];
        }
        $smarty->assign('admin_list',  $admin);
    }

    $outflow = outflow_list('service_time');

    if (!admin_priv('outflow_list_part', '', false)) {
        unset($_REQUEST['admin_id']);
    }

    $smarty->assign('tag_list', $tag_list);

    $smarty->assign('outflow_list', $outflow['outflow_list']);

    $smarty->assign('dst_script',  'users');
    $smarty->assign('act',         $_REQUEST['act']);

    $smarty->assign('condition',    $outflow['condition'].$condition);
    $smarty->assign('page',         $outflow['page']);
    $smarty->assign('page_count',   $outflow['page_count']);
    $smarty->assign('record_count', $outflow['record_count']);
    $smarty->assign('page_list',    $outflow['page_set']);
    $smarty->assign('page_start',   $outflow['start']);
    $smarty->assign('page_end',     $outflow['end']);
    $smarty->assign('page_size',    $outflow['page_size']);

    // 最终时间
    $smarty->assign('end_time', date('Y-m-t 23:59:59'));

    $res['main'] = $smarty->fetch('outflow_service_data.htm');
    $res['page'] = $smarty->fetch('page_fragment.htm');

    if (!(isset($_REQUEST['start_time']) || isset($_REQUEST['page_no']) || isset($_REQUEST['platform']) || isset($_REQUEST['admin_id']))) {
        $smarty->assign('data', $res['main']);
        $smarty->assign('page', $res['page']);
        $res['main'] = $smarty->fetch('outflow_service.htm');

        unset($res['page']);
    }

    die($json->encode($res));
}

/* 介绍人信息 */
elseif ($_REQUEST['act'] == 'referrals_detail') {
    $user_id = intval($_REQUEST['id']);

    $user_info  = get_user_info($user_id);
    $order_list = access_purchase_records($user_id); // 获取顾客购买记录（订单记录）

    $smarty->assign('user', $user_info);
    $res['message'] = $smarty->fetch('referrals_detail.htm');
    $res['req_msg'] = true;
    $res['title']   = '介绍人详细信息';

    die($json->encode($res));
}

/* 转介绍列表 */
elseif ($_REQUEST['act'] == 'referrals_list') {
    // 查询权限
    if (!admin_priv('referrals_list', '', false)) {
        $res['message'] = '对不起，您没有权限访问该页面';
        $res['req_msg'] = true;
        die($json->encode($res));
    }

    // 整理条件
    if (admin_priv('referrals_list_all', '', false)) {
        // 管理员权限
        $platform = get_role_list(' WHERE role_type IN (1,2) ');
        $platform_list = array ();
        foreach ($platform as $val) {
            $platform_list[$val['role_id']] = $val['role_name'];
        }

        $smarty->assign('platform',    $platform_list);

        $amdin_list = get_admin('role_id IN (1,9)');
    } elseif (admin_priv('referrals_list_part', '', false)) {
        // 部门权限
        $admin_list = get_admin($_SESSION['admin_id']);
    } elseif (admin_priv('referrals_list_row', '', false)) {
        // 小组权限
        $admin_list = get_admin();
    } else {
        // 个人权限
        $admin_list = '';
    }

    if (!empty($admin_list)) {
        $admin = array ();
        foreach ($admin_list as $val) {
            $admin[$val['user_id']] = $val['user_name'];
        }
        $smarty->assign('admin_list',  $admin);
    }

    // 查询数据
    $referrals_list = referrals_list();

    // 输出数据
    $smarty->assign('outflow_list', $referrals_list['user_list']);

    $smarty->assign('dst_script',   'users');
    $smarty->assign('act',          $_REQUEST['act']);

    $smarty->assign('condition',    '&'.implode('&',$referrals_list['condition']));
    $smarty->assign('page',         $referrals_list['page']);
    $smarty->assign('page_count',   $referrals_list['page_count']);
    $smarty->assign('record_count', $referrals_list['record_count']);
    $smarty->assign('page_list',    $referrals_list['page_set']);
    $smarty->assign('page_start',   $referrals_list['start']);
    $smarty->assign('page_end',     $referrals_list['end']);
    $smarty->assign('page_size',    $referrals_list['page_size']);

    $res['main'] = $smarty->fetch('referrals_data.htm');
    $res['page'] = $smarty->fetch('page_fragment.htm');

    if (!(isset($_REQUEST['start_time']) || isset($_REQUEST['page_no']) || isset($_REQUEST['platform']) || isset($_REQUEST['admin_id']))) {
        $smarty->assign('data', $res['main']);
        $smarty->assign('page', $res['page']);
        $res['main'] = $smarty->fetch('referrals_list.htm');

        unset($res['page']);
    }

    die($json->encode($res));
}

/* 转顾客 */
elseif ($_REQUEST['act'] == 'send_users') {
    $send_to   = intval($_REQUEST['send_to']);

    // 该客服允许维护的顾客数量
    $sql_select = 'SELECT max_customer,user_name,role_id,group_id FROM '.
        $GLOBALS['ecs']->table('admin_user')." WHERE user_id=$send_to";
    $admin_info = $GLOBALS['db']->getRow($sql_select);

    // 该客服已在维护的顾客数量
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users')." WHERE admin_id=$send_to";
    $users_count = $GLOBALS['db']->getOne($sql_select);
    if ($admin_info['max_customer'] <= $users_count) {
        $res = array (
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '该客服的顾客数量已经到达上限了！',
        );

        die($json->encode($res));
    }

    $user_list = addslashes_deep($json->decode($_REQUEST['user_list']));
    $user_number = count($user_list);
    $user_list = implode(',', $user_list);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET last_admin=admin_id WHERE user_id IN ($user_list) LIMIT $user_number";
    $GLOBALS['db']->query($sql_update);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET admin_id=$send_to,admin_name='{$admin_info['user_name']}',".
        "group_id={$admin_info['group_id']}, role_id={$admin_info['role_id']}, assign_time=UNIX_TIMESTAMP() WHERE user_id IN ($user_list) LIMIT $user_number";
    $GLOBALS['db']->query($sql_update);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_user')." SET counter=counter+$user_number WHERE user_id=$send_to LIMIT 1";
    $GLOBALS['db']->query($sql_update);

    $res = array (
        'req_msg'   => true,
        'timeout'   => 2000,
        'message'   => '顾客转移成功！',
        'user_list' => $_REQUEST['user_list'],
    );

    die($json->encode($res));
}

//审核黑名单
//@do_what  2 审核 、1 撤销
elseif ($_REQUEST['act'] == 'check_blacklist')
{
    extract($_REQUEST);
    $res = array(
        'req_msg'=>true,
        'code'=>false,
        'message'=>'',
        'timeout'=>2000
    );

    if(empty($user_id) && $user_id == 0){
        $res['message'] = '审核失败';
        die($res);
    }

    if($do_what == 2){
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('user_blacklist')." SET status=2 WHERE user_id=$user_id";
        if($GLOBALS['db']->query($sql)){
            $sql = 'UPDATE '.$GLOBALS['ecs']->table('users')
                .' SET is_black=1'." WHERE user_id=$user_id";
        }
        $res_msg = '审核';
    }elseif($do_what == 1){
        $sql = 'DELETE FROM '.$GLOBALS['ecs']->table('user_blacklist')." WHERE user_id=$user_id";
        $res_msg = '撤销';
    }

    if($GLOBALS['db']->query($sql)){
        $res['code'] = true;
        $res['row_id'] = $row_id;
        $res['message'] = $res_msg.'成功';
    }else{
        $res['message'] = $res_msg.'失败';
    }

    die($json->encode($res));
}

/* 新增联系方式 */
elseif ($_REQUEST['act'] == 'add_contact') {
    $res = array (
        'req_msg'=>true,
        'timeout'=>2000,
    );

    $data = addslashes_deep($_REQUEST['data']);
    $user_id = intval($_REQUEST['user_id']);
    list($field,$value) = explode(':', $data);
    if (isset($_REQUEST['prov'])) {
        $prov = intval($_REQUEST['prov']);
        $city = intval($_REQUEST['city']);
        $dist = intval($_REQUEST['dist']);
        $addr = addslashes_deep($_REQUEST['addr']);

        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_addr').'(prov,city,dist,addr,user_id) SELECT '.
            "$prov,$city,$dist,'$value',$user_id FROM dual WHERE NOT EXISTS (SELECT * FROM ".$GLOBALS['ecs']->table('user_addr').
            " WHERE prov=$prov AND city=$city AND dist=$dist AND addr='$value' AND user_id=$user_id)";
        $GLOBALS['db']->query($sql_insert);
    } else {
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_contact').'(user_id,contact_name,contact_value) SELECT '.
            "$user_id,'$field','$value' FROM dual WHERE NOT EXISTS (SELECT * FROM ".$GLOBALS['ecs']->table('user_contact').
            " WHERE contact_name='$field' AND contact_value='$value')";
        $result = $GLOBALS['db']->query($sql_insert);
    }

    if (!$GLOBALS['db']->insert_id()) {
        $res['message'] = '相关记录已存在！';
    } else {
        if($result){
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_contact')." SET add_time={$_SERVER['REQUEST_TIME']},add_admin={$_SESSION['admin_id']} WHERE contact_id=".$GLOBALS['db']->insert_id();
            $GLOBALS['db']->query($sql_update);
        }

        $res['message']   = '添加成功！';
        $res['field']     = $field;
        $res['value']     = $value;
        $res['code']      = 1;
        $res['user_id']   = $user_id;
        $res['insert_id'] = $GLOBALS['db']->insert_id();
    }

    die($json->encode($res));
}

/* 为订单中收货人更换收货地址 */
elseif ($_REQUEST['act'] == 'get_addr') {
    $addr_id = intval($_REQUEST['addr_id']);

    $sql_select = 'SELECT prov,city,dist,addr FROM '.$GLOBALS['ecs']->table('user_addr')." WHERE addr_id=$addr_id";
    $result = $GLOBALS['db']->getRow($sql_select);

    die($json->encode($result));
}

/* 设置默认的联系方式 */
elseif ($_REQUEST['act'] == 'set_default') {
    $contact_id = intval($_REQUEST['cid']);

    // 判断该联系方式的类型
    $sql_select = 'SELECT contact_name,user_id FROM '.$GLOBALS['ecs']->table('user_contact')." WHERE contact_id=$contact_id";
    $contact_info = $GLOBALS['db']->getRow($sql_select);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_contact')." SET is_default=IF(contact_id=$contact_id, 1, 0)".
        " WHERE user_id={$contact_info['user_id']} AND contact_name='{$contact_info['contact_name']}'";
    if ($GLOBALS['db']->query($sql_update)) {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').' u, '.$GLOBALS['ecs']->table('user_contact').
            " c SET %s=contact_value WHERE c.contact_id=$contact_id AND u.user_id=c.user_id";
        switch ($contact_info['contact_name']) {
        case 'tel':
            $sql_update = sprintf($sql_update, 'home_phone');
            break;
        case 'mobile' :
            $sql_update = sprintf($sql_update, 'mobile_phone');
            break;
        default:
            $sql_update = sprintf($sql_update, $contact_info['contact_name']);
            break;
        }

        $GLOBALS['db']->query($sql_update);

        $res = array (
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '默认项已修改成功！',
            'cid'     => $contact_id,
            'vid'     => $contact_info['contact_name'],
        );

        die($json->encode($res));
    }
}

/* 资料顾客 */
elseif ($_REQUEST['act'] == 'data_users') {
    $data_batch = 2; // 资料批次
    $sql_select = 'SELECT MIN(visits) FROM '.$GLOBALS['ecs']->table('users_data').
        " WHERE user_type NOT IN (4,6) AND admin_id=0 AND data_batch=$data_batch";
    $min_visits = $GLOBALS['db']->getOne($sql_select);
    // 获取顾客资料
    $sql_select = 'SELECT age,sex,remark,rec_id,user_name,contact,address,user_type,phone_outage,mobile_space,no_answer,died,substitution FROM '.$GLOBALS['ecs']->table('users_data').
        " WHERE user_type IN (0,3) AND admin_id=0 AND user_id=0 AND visits=$min_visits AND data_batch=$data_batch LIMIT 1";
    $user_list = $GLOBALS['db']->getAll($sql_select);
    if (empty($user_list)) {
        $msg = array ('req_msg' => true,'message'=>'对不起，系统中已无多余资料');
        die($json->encode($msg));
    }

    //$rec_id_list = array();
    //foreach ($user_list as $val){
    //    $rec_id_list[] = $val['rec_id'];
    //}

    //$rec_id_list = implode(',', $rec_id_list);
    //$sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users_data')." SET admin_id={$_SESSION['admin_id']} WHERE rec_id IN ($rec_id_list)";
    //$GLOBALS['db']->query($sql_update);

    // 验证顾客是否存在
    while ($user_info = array_shift($user_list)) {
        preg_match('/([-\d]+)/', $user_info['contact'], $contact_list);
        $contact_list = array_unique($contact_list);

        foreach ($contact_list as &$val){
            $val = str_replace('-', '', $val);
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').
                " WHERE REPLACE(home_phone,'-', '')='$val' OR REPLACE(mobile_phone,'-','')='$val'";
            $exist = $GLOBALS['db']->getOne($sql_select);
            if ((boolean)$exist) {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users_data').
                    " SET user_type=4 WHERE rec_id={$user_info['rec_id']}";
                $GLOBALS['db']->query($sql_update);
                break;
            } else {
                $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_contact').
                    " WHERE (REPLACE(contact_value,'-','')='$val' AND contact_name='tel') OR (REPLACE(contact_value,'-','')='$val' AND contact_name='tel')";
                $exist = $GLOBALS['db']->getOne($sql_select);
                if ((boolean)$exist) {
                    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users_data')." SET user_type=4 WHERE rec_id={$user_info['rec_id']}";
                    $GLOBALS['db']->query($sql_update);
                    break;
                }
            }
        }

        if (!$exist) {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users_data').
                " SET admin_id={$_SESSION['admin_id']},visits=visits+1 WHERE rec_id={$user_info['rec_id']}";
            $GLOBALS['db']->query($sql_update);
            break;
        }
    }

    // 输出顾客数据
    if (isset($_REQUEST['is_ajax'])) {
        $smarty->assign('is_ajax', 1);
    }

    $user_info['address'] = mb_substr($user_info['address'],0, 20, 'utf8').'<wbr/>'.mb_substr($user_info['address'],20, mb_strlen($user_info['address'], 'utf8'),'utf8');

    $smarty->assign('user_info',     $user_info);
    $smarty->assign('province_list', get_regions(1, 1));
    $smarty->assign('customer_type', get_customer_type());
    $res['main'] = $smarty->fetch('data_users.htm');

    die($json->encode($res));
}

/* 处理资料中的顾客 */
elseif ($_REQUEST['act'] == 'handle_users') {
    $res = array ('req_msg' => true, 'timeout' => 2000);
    if (!admin_priv('handle_users', '', false)) {
        $res['message'] = '对不起，该账号无此权限';
        die($json->encode($res));
    }

    $req = addslashes_deep($_REQUEST);

    $rec_id = intval($req['rec_id']);
    $feed   = intval($req['feed']);

    if ($feed == 4) {
        $is_exist = 0;
        if (!empty($req['mobile_phone'])) {
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').
                " WHERE mobile_phone='{$req['mobile_phone']}'";
            $is_exist += $GLOBALS['db']->getOne($sql_select);
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_contact').
                " WHERE contact_value='{$req['mobile_phone']}' AND contact_name='mobile'";
            $is_exist += $GLOBALS['db']->getOne($sql_select);
        }
        if (!empty($req['home_phone'])) {
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').
                " WHERE home_phone='{$req['home_phone']}'";
            $is_exist += $GLOBALS['db']->getOne($sql_select);
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_contact').
                " WHERE contact_value='{$req['home_phone']}' AND contact_name='tel'";
            $is_exist += $GLOBALS['db']->getOne($sql_select);
        }
        if ($is_exist > 0) {
            $res['message'] = '很抱歉，该顾客已经存在！';
            die($json->encode($res));
        }
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('users').
            '(user_name,sex,mobile_phone,home_phone,customer_type,add_time,admin_id,admin_name,role_id)VALUES('.
            "'{$req['user_name']}', {$req['sex']}, '{$req['mobile_phone']}', '{$req['home_phone']}', '{$req['customer_type']}', UNIX_TIMESTAMP(),{$_SESSION['admin_id']},'{$_SESSION['admin_name']}',{$_SESSION['role_id']})";
        $GLOBALS['db']->query($sql_insert);
        $user_id = $GLOBALS['db']->insert_id();

        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_address').'(user_id,province,city,district,address)VALUES('.
            "$user_id, {$req['province']}, {$req['city']}, {$req['district']}, '{$req['address']}')";
        $GLOBALS['db']->query($sql_insert);

        $home_phone = explode(',', $req['home_phone']);
        $valuse = array();

        $h = 1;
        foreach ($home_phone as $v){
            $values[] = "($user_id,'tel','{$req['home_phone']}',$h)";
            $h--;
        }

        $home_phone = explode(',', $req['home_phone']);
        $valuse = array();

        $m = 1;
        foreach ($home_phone as $v){
            $values[] = "($user_id,'mobile','{$req['mobile_phone']}',$m)";
            $m--;
        }

        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_contact').'(user_id,contact_name,contact_value,is_default)VALUES';
        $GLOBALS['db']->query($sql_insert.implode(',', $values));

        if (isset($req['service_content'])) {
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('service').
                '(service_class,service_status,service_manner,service_time,user_id,user_name,logbook,admin_id,admin_name)VALUES('.
                "10,1,1,UNIX_TIMESTAMP(),$user_id,'{$req['user_name']}','{$req['service_content']}',{$_SESSION['admin_id']},'{$_SESSION['admin_name']}')";
            if (! $GLOBALS['db']->query($sql_insert)) {
                $res['message'] = '备注信息添加失败！';
                die($json->encode($res));
            }
        }

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users_data').' SET visits=visits+1,handle_time=UNIX_TIMESTAMP(),'.
            "user_type=$feed,admin_id={$_SESSION['admin_id']},user_id=$user_id WHERE rec_id=$rec_id";
        $GLOBALS['db']->query($sql_update);

        $res['message'] = '顾客添加成功！！';
    } else {
        // phone_outage,mobile_space,no_answer,died,substitution
        $feed_info = array(
            '1' => 'phone_outage',
            '2' => 'mobile_space',
            '3' => 'no_answer',
            '5' => 'died',
            '6' => 'substitution'
        );
        try {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users_data').' SET visits=visits+1,handle_time=UNIX_TIMESTAMP(),'.
                "admin_id=0,user_type=$feed,$feed_info[$feed]=$feed_info[$feed]+1 WHERE rec_id=$rec_id";
            $GLOBALS['db']->query($sql_update);
            $res['message'] = '该顾客的资料状况已更新！';
        } catch (Exception $ex) {
            $res['message'] = $ex;
        }
    }

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('users_data_stats').'(data_id,admin_id,user_type,handle_time)VALUES('.
        "$rec_id,{$_SESSION['admin_id']},$feed,UNIX_TIMESTAMP())";
    $GLOBALS['db']->query($sql_insert);

    die($json->encode($res));
}

/* 查看客服自定义列表 */
elseif ($_REQUEST['act'] == 'view_admin_cate') {
    // 权限控制
    if (!admin_priv('everyone_sales', '',false)) {
        $admin_list = get_admin_tmp_list($_SESSION['role_id']);
        $group_list = get_group_list($_SESSION['role_id']);

        $smarty->assign('group_list', $group_list);
    } else {
        $role_id = '1,9';
        $admin_list = get_admin_tmp_list($role_id);
    }

    // 部门列表
    $smarty->assign('role_list', get_role_list(' WHERE role_type IN (1,2) '));

    // 客服列表
    $smarty->assign('admin_list', $admin_list);

    $smarty->assign('curr_title', '查看自定义列表');

    $res['main'] = $smarty->fetch('view_admin_cate.htm');

    die($json->encode($res));
}

/* 显示顾客分类 */
elseif ($_REQUEST['act'] == 'view_user_list') {
    $admin_id = intval($_REQUEST['admin_id']);

    $smarty->assign('cat_list', user_cat_list(1, $admin_id));

    $user_list = user_list();

    $smarty->assign('user_list', $user_list['user_list']);

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition'].'&id='.intval($_REQUEST['tag']));
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('tag',          $_REQUEST['tag'] ? $_REQUEST['tag'] :0);
    $smarty->assign('cat_tag',      $_REQUEST['cat_tag'] ? $_REQUEST['cat_tag'] :0);

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));
    $smarty->assign('admin_id', $admin_id);

    if (isset($_REQUEST['cat_tag']) && 0 < $_REQUEST['cat_tag']) {
        $smarty->assign('transfer', 1);
        $res['switch_tag'] = true;
        $res['id'] = intval($_REQUEST['tag']);
    }

    $res['main'] = $smarty->fetch('view_user_list.htm');
    $res['page'] = $smarty->fetch('page.htm');

    die($json->encode($res));
}

//合并顾客
elseif($_REQUEST['act'] == 'user_combine'){
    if(empty($_REQUEST['behave'] )){
        if(admin_priv('user_combine','',false)){
            $sql_select = 'SELECT contact_way_label,contact_way_name FROM '.$GLOBALS['ecs']->table('contact_way').' ORDER BY contact_way_id ASC';
            $contact_way = $GLOBALS['db']->getAll($sql_select);
            $smarty->assign('contact_way',$contact_way);
            $smarty->assign('repeat_user_div',$smarty->fetch('repeat_user_div.htm'));
            $res['main'] = $smarty->fetch('user_combine_search.htm');
        }
        die($json->encode($res));
    }elseif($_REQUEST['behave'] == 'sch_repeat_user'){
        //搜索重复顾客
        $condition = '&behave=sch_repeat_user';
        $where = ' WHERE role_id<>13 ';
        $power = true;
        if(admin_priv('all','',false)){
            $smarty->assign('user_part_view',true);
        }elseif(admin_priv('user_part_view','',false)){
            $smarty->assign('user_part_view',true);
            $where .= " AND u.role_id={$_SESSION['role_id']} ";
        }else{
            $power = false;
            $where .= " AND u.admin_id={$_SESSION['admin_id']}";
        }
        $_REQUEST = array_filter($_REQUEST);
        extract($_REQUEST);
        if(!empty($keyword)){
            if ($contact_way == -1) {
                $user_list = str_replace('||',',',$keyword);
                $where .= ' AND u.user_id IN('.$user_list.')';
            }else{
                $where .= " AND u.$contact_way='$keyword'";
            }
        }

        $sql_count = 'SELECT count(*) FROM '.$GLOBALS['ecs']->table('users').'u '.$where;
        $filter = admin_page_size($sql_count,'user_combine',$condition);
        $start = $filter['page_start'] - 1;
        $sql_select = 'SELECT u.user_id FROM '.$GLOBALS['ecs']->table('users').' u LEFT JOIN '.
            $GLOBALS['ecs']->table('user_address')." ud ON u.user_id=ud.user_id $where  GROUP BY u.user_id LIMIT $start,{$filter['page_end']}";
        $user_id_list = $GLOBALS['db']->getCol($sql_select);
        $total = count($user_id_list);
        if($total <= 1){
            $smarty->assign('repeat_user_report','没有搜索到重复顾客');
        }else{
            $id_list = implode(',',$user_id_list);
            $where = " where u.user_id IN($id_list)";
            $ap = "SELECT u.user_id,u.user_name,u.mobile_phone,u.home_phone,ud.address,u.admin_name,u.order_time,u.service_time,c.type_name";
            $sql = " FROM ".$GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('user_address').' ud ON u.user_id=ud.user_id '
                .' LEFT JOIN '.$GLOBALS['ecs']->table('customer_type').' c ON u.customer_type=c.type_id';
            if(!$power){
                $sql = "$ap$sql$where";
            }else{
                $sql = "$ap,r.role_name$sql".' LEFT JOIN '.$GLOBALS['ecs']->table('role')
                    ." r ON u.role_id=r.role_id $where GROUP BY u.user_id ORDER BY u.mobile_phone,u.home_phone";
            }
            $repeat_user_list = $GLOBALS['db']->getAll($sql);
            if ($repeat_user_list) {
                foreach ($repeat_user_list as $k=>&$v) {
                    $v['order_time'] = $v['order_time'] ? date('Y-m-d',$v['order_time']) : '-';
                    $v['service_time'] = $v['service_time'] ? date('Y-m-d',$v['service_time']) : '-';
                    $id[] = $v['user_id'];
                }
            }
            $res['user_id_list']    = $id;
            $res['response_action'] = 'search_service';
            $smarty->assign('repeat_user_list',$repeat_user_list);
        }
    }elseif($_REQUEST['behave'] == 'combine_user'){
        $original_user_id = mysql_real_escape_string($_REQUEST['original_user_id']);
        $to_user_id       = intval($_REQUEST['to_user_id']);
        if($original_user_id && $to_user_id){
            $original_user_id = explode(',',$original_user_id);
            foreach ($original_user_id as $k=>&$v) {
                if ($v == $to_user_id) {
                    unset($original_user_id[$k]);
                }
            }
            $original_user_id = implode(',',$original_user_id);
            /*修改顾客联系表信息*/
            $where = "WHERE user_id IN($original_user_id)";
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_contact')." SET user_id=$to_user_id $where";
            $result = $GLOBALS['db']->query($sql_update);

            /*修改顾客地址列表*/
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_address').
                " SET user_id=$to_user_id $where";
            $result = $GLOBALS['db']->query($sql_update);

            /*合并顾客的积分并修改等级*/
            $sql_select = 'SELECT rank_points FROM '.$GLOBALS['ecs']->table('users').
                " WHERE user_id IN($original_user_id,$to_user_id)";
            $rank_points = array_sum($GLOBALS['db']->getCol($sql_select));
            $rank_list = get_rank_list_info();

            if($rank_list){
                foreach($rank_list as $val){
                    if($rank_points >= $val['min_points'] && $rank_ponts <= $val['max_points']){
                        $rank_id = $val['rank_id'];
                        break;
                    }
                }

                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET user_rank=$rank_id,rank_points=$rank_points WHERE user_id=$to_user_id";

                $result = $GLOBALS['db']->query($sql_update);
            }

            /*合并顾客订单*/
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info')." SET user_id=$to_user_id $where";
            $GLOBALS['db']->query($sql_update);

            /*合并服务信息*/
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('service')." SET user_id=$to_user_id $where";
            $GLOBALS['db']->query($sql_update);

            /*删除顾客并记录删除顾客的信息*/
            $sql_insert = 'REPLACE INTO '.$GLOBALS['ecs']->table('del_users')
                .' SELECT * FROM '.$GLOBALS['ecs']->table('users').$where;
            $GLOBALS['db']->query($sql_insert);
            $sql_del = 'DELETE FROM  '.$GLOBALS['ecs']->table('users').$where;
            $GLOBALS['db']->query($sql_del);

            $res = crm_msg('合并成功',true);
            die($json->encode($res));
        }
    }

    $res['main'] = $smarty->fetch('repeat_user_div.htm');
    die($json->encode($res));
}

//批量合并顾客
elseif($_REQUEST['act'] == 'batch_combine_user'){
    $rule = intval($_REQUEST['rule']);
    $res = array('message'=>'','timeout'=>2000,'code'=>false);
    if (!empty($rule)) {
        //查找拥有相同联系方式的顾客
        //拥有相同手机
        //$sql = 'SELECT u.user_id FROM'
        switch($rule){
        case 1 :
            break;
        case 2 :
            break;
        case 3 :
            break;
        }
        die($json->encode($res));
    }
}

/*比较顾客*/
elseif($_REQUEST['act'] == 'compare_user'){
    $user_id_list = mysql_real_escape_string($_REQUEST['user_id_list']);

    if($user_id_list != ''){
        //基本信息
        $where      = " WHERE user_id IN($user_id_list)";
        $sql_select = 'SELECT user_id,user_name,order_time,add_time FROM '.$GLOBALS['ecs']->table('users').$where;
        $users_info = $GLOBALS['db']->getAll($sql_select);
        foreach ($users_info as &$v) {
            $v['order_time'] = date('Y-m-d H:i',$v['order_time']);
            $v['add_time'] = date('Y-m-d H:i',$v['add_time']);
        }

        //地址
        $sql_select = 'SELECT user_id,address FROM '.$GLOBALS['ecs']->table('user_address').$where;
        $users_address = $GLOBALS['db']->getAll($sql_select);

        //联系方式
        $sql_select = 'SELECT user_id,contact_name,contact_value FROM '.$GLOBALS['ecs']->table('user_contact').$where;
        $user_contact_way = $GLOBALS['db']->getAll($sql_select);

        //重组顾客信息
        foreach($users_info as &$user){
            foreach($users_address as $ud_key=>$user_ads){
                if($user['user_id'] == $user_ads['user_id']){
                    $user['address_list'][] = $user_ads['address'];
                    unset($user_ads[$ud_key]);
                }
            }
        }

        foreach($users_info as &$user){
            foreach($user_contact_way as $ucw_key=>$ucw){
                if($user['user_id'] == $ucw['user_id']){
                    $user[$ucw['contact_name']][] = $ucw['contact_value'];
                    unset($ucw[$ucw_key]);
                }
            }
        }


        $smarty->assign('first_user',$users_info[0]);
        $smarty->assign('second_user',$users_info[1]);
        $res['main'] = $smarty->fetch('compare_user_div.htm');
    }else{
        $res = array(
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '对照失败'
        );
    }

    die($json->encode($res));
}

//检查是否来自网络黑名单
elseif($_REQUEST['act'] == 'check_netword_liar'){
    $area_code   = trim(mysql_real_escape_string($_REQUEST['area_code']));
    $home_phone  = trim(mysql_real_escape_string($_REQUEST['home_phone']));
    $mobie_phone = trim(mysql_real_escape_string($_REQUEST['mobile_phone']));

    $sql_select = 'SELECT account_type FROM '.$GLOBALS['account_type'].
        " WHERE label IN('home_phone','moblie_phone')";
    $account_type = $GLOBALS['db']->getCol($sql_select);
    $account_type = implode(',',$account_type);

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table("account_blacklist").
        " WHERE account_type IN ($account_type) AND account_value IN($home_phone,$mobile_phone)";

    $total = $GLOBALS['db']->getOne($sql_select);
}

/*选择接收顾客员工*/
elseif($_REQUEST['act'] == 'get_by_send_admin'){
    $admin_name = mysql_real_escape_string($_REQUEST['admin_name']);

    if($admin_name != ''){
        $sql_select = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_name LIKE '%$admin_name%' AND status=1";
        $res = $GLOBALS['db']->getAll($sql_select);
    }

    die($json->encode($res));
}

/*网络黑名单顾客*/
elseif ($_REQUEST['act'] == 'get_network_blacklist'){
    if(admin_priv('all','',false) || admin_priv('order_part_view','',false) || admin_priv('order_group_view','',false) || admin_priv('ignore_blacklist','',false)){

        $user_name = isset($_REQUEST['user_name']) ? mysql_real_escape_string($_REQUEST['user_name']) : '';
        $keyword = isset($_REQUEST['keyword']) ? mysql_real_escape_string($_REQUEST['keyword']) : '';
        $address = isset($_REQUEST['address']) ? mysql_real_escape_string($_REQUEST['address']) : '';

        $where = ' WHERE 1 ';
        if($user_name){
            $where .= " AND user_name LIKE '%$user_name%'";
        }

        if($keyword){
            $where .= " AND (mobile LIKE '%$keyword%')";
        }

        if($address){
            $where .= " AND address LIKE '%$address%'";
        }

        $network_user_blacklist = get_network_blacklist($where);
        $smarty->assign('network_user_blacklist',$network_user_blacklist);

        $res['response_action'] = 'search_service';
        $res['main']            = $smarty->fetch('network_user_blacklist.htm');
    }

    die($json->encode($res));
}

/*加入网络黑名单*/
elseif($_REQUEST['act'] == 'putin_network_blacklist'){

    $user_name = isset($_REQUEST['user_name']) ?  trim(mysql_real_escape_string($_REQUEST['user_name'])) : '';
    $mobile    = isset($_REQUEST['number']) ? trim(mysql_real_escape_string($_REQUEST['number'])) : '';
    $type_id   = isset($_REQUEST['type_id']) ? intval(($_REQUEST['type_id'])) : '';
    $res = array(
        'req_msg' => true,
        'message' => '',
        'code'    => false,
        'timeout' => 2000,
    );

    if(empty($user_name) && empty($mobile)){
        $res['message'] = '添加黑名单失败';
    }else{
        if(empty($mobile)){
            $where = " AND mobile='$mobile'";
        }
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('network_blacklist').
            " WHERE user_name='$user_name' $where";

        if($GLOBALS['db']->getOne($sql_select)){
            $res['message'] = '已经存在相同的姓名';
        }else{
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('network_blacklist').
                '(user_name,mobile,type_id)VALUES('."'$user_name','$mobile',$type_id)";
            $res['code'] = $GLOBALS['db']->query($sql_insert);
            if($res['code']){
                $res['message'] = '添加成功';
            }
        }
    }

    die($json->encode($res));
}

/*修改网络黑名单*/
elseif($_REQUEST['act'] == 'mod_del_network_blacklsit'){
    $res = array(
        'req_msg' => true,
        'message' => '',
        'code'    => false,
        'timeout' => 2000,
    );

    $blacklist_user_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    if(empty($blacklist_user_id)){
        $res['message'] = '删除失败';
    }else{
        $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('network_blacklist').
            " WHERE blacklist_user_id=$blacklist_user_id";

        $res['code']    = $GLOBALS['db']->query($sql_del);
        $res['message'] = $res['code'] ? '删除成功' : '删除失败';
    }

    die($json->encode($res));
}

/*解除黑名单警报*/
elseif ($_REQUEST['act'] == 'ignore_blacklist'){
    if(admin_priv('order_group_view','',false) || admin_priv('all','',false) || admin_priv('ignore_blacklist','',false)){
        $user_id    = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
        $from_table = isset($_REQUEST['from_table']) ?mysql_real_escape_string($_REQUEST['from_table']) : '';

        if(empty($user_id) && empty($from_table)){
            $res['code'] = false;
            die($json->encode($res));
        }

        $sql_select = 'SELECT is_black FROM '.$GLOBALS['ecs']->table($from_table).
            " WHERE user_id=$user_id";
        $is_black = $GLOBALS['db']->getOne($sql_select);

        if($is_black == 3){
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table($from_table)
                ." SET is_black=4 WHERE user_id=$user_id";
            $GLOBALS['db']->query($sql_update);

            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_blacklist').
                " SET ignore_admin={$_SESSION['admin_id']} WHERE user_id=$user_id AND from_table='$from_table'";
            $GLOBALS['db']->query($sql_update);

            $res['error_msg'] = sprintf("此顾客有风险，但已被%s排除警报，可进行订单操作",$_SESSION['admin_name']);

        }elseif($is_black == 4){
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table($from_table)
                ." SET is_black=3 WHERE user_id=$user_id";
            $GLOBALS['db']->query($sql_update);
            $res['error_msg'] = '根据记录，此顾客有风险，请通知上级主管进行排查！';
        }

        $res['code'] = true;
    }

    $res['is_black'] = $is_black;
    die($json->encode($res));
}

/* 按商品查询顾客 */
elseif ($_REQUEST['act'] == 'search_by_goods') {
    if (!admin_priv('search_by_goods', '', false)) {
        $msg = array(
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '访问未经授权！'
        );
    }
    if (admin_priv('all','',false)) {
        $smarty->assign('all',true);
        $smarty->assign('role_list', get_role_customer(' AND role_id IN(33,34,35,36,37)'));
    }

    // 品牌列表
    $brand_list = get_brand_list(' WHERE is_show=1 ');
    $smarty->assign('brand_list', $brand_list);

    // 商品列表
    $goods_list = goods_list(0);
    $smarty->assign('goods_list', $goods_list['goods']);

    $smarty->assign('full_page', 1);
    $res['main'] = $smarty->fetch('search_by_goods.htm');

    echo $json->encode($res);
    return;
}

/* 按品牌显示商品 */
elseif ($_REQUEST['act'] == 'list_goods_by_brand') {
    $brand_id = intval($_REQUEST['brand_id']);

    $sql_select = 'SELECT goods_id,goods_name FROM '.$GLOBALS['ecs']->table('goods')." WHERE brand_id=$brand_id AND is_on_sale=1";
    $goods_list = $GLOBALS['db']->getAll($sql_select);

    echo $json->encode($goods_list);
    return;
}

/* 查询产品相关的顾客  */
elseif ($_REQUEST['act'] == 'show_goods_users') {
    $user_list = search_by_goods();
    $smarty->assign('user_list', $user_list['user_list']);

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);

    $smarty->assign('act', 'show_goods_users');

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));

    $smarty->assign('full_page', 0);

    $res['main'] = $smarty->fetch('search_by_goods.htm');
    $res['page'] = $smarty->fetch('page.htm');

    if (isset($_REQUEST['a'])) {
        $res['a'] = $_REQUEST['a'];
    }

    echo $json->encode($res);
    return;
}

/* 电商平台 */
elseif ($_REQUEST['act'] == 'list_users_by_platform') {
    /* 检查权限 */
    $res = array ('switch_tag' => true, 'id' => intval($_REQUEST['tag']));

    if($_SESSION['admin_id'] == 78) {
        $_REQUEST['type'] = '13';
    } elseif (!$_REQUEST['type']) {
        $_REQUEST['type'] = '2';
    }

    $smarty->assign('user_ranks',    $ranks);
    $smarty->assign('ur_here',       $_LANG['01_users_list']);
    $smarty->assign('country_list',  get_regions());
    $smarty->assign('province_list', get_regions(1,1));

    $user_list = user_list();

    /* 获取顾客来源、购买力、客服 */
    $smarty->assign('from_where', get_from_where());
    $smarty->assign('type_list',  get_customer_type());
    $smarty->assign('admin_list', get_admin('session'));
    $smarty->assign('eff_list',   getEffectTypes());

    if (admin_priv('user_group_view', '', false) || admin_priv('user_part_view', '', false)) {
        $smarty->assign('section', 1);
    }

    $smarty->assign('is_intention', $_REQUEST['act']);  // 意向顾客查询字段，为分页提供区分支持
    $smarty->assign('user_list',    $user_list['user_list']);

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('tag',          $_REQUEST['tag'] ? $_REQUEST['tag'] : 0);
    $smarty->assign('type',          $_REQUEST['type'] ? $_REQUEST['type'] : 2);

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');

    // 是否显示转顾客
    if (admin_priv('reassign_user', '', false)) {
        $smarty->assign('reassign_user', 1);
    }

    //判断客服的权限，是否显示团队搜索
    if($_SESSION['action_list'] == 'all') {
        $smarty->assign('admin_show_team',1);
        $smarty->assign('role_list', get_role());
    } else {
        $sql = 'SELECT manager,role_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id={$_SESSION['admin_id']}";
        $user = $GLOBALS['db']->getRow($sql);
        if($user['manager'] === '0') {
            $smarty->assign('role_id',$user['role_id']);
            $smarty->assign('show_team',1);
        }
    }

    // 获取电商平台列表
    $smarty->assign('platform_list', ECP_list());

    if (isset($_REQUEST['a'])) {
        $smarty->assign('content', 'users_list');
        $res['main'] = $smarty->fetch('list_tpl.htm');
        $res['page'] = $smarty->fetch('page.htm');
        $res['a'] = $_REQUEST['a'];
    } else {
        $res['main'] = $smarty->fetch('list_users_by_platform.htm');
    }

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    die($json->encode($res));
}

/* 拨号通话 */
elseif ('dial' == $_REQUEST['act']) {
    $phone = trim($_REQUEST['phone']);
    $phone = isLocalPhone($phone);
    $result = curlPhone($phone, $_SESSION['ext']);
    $msg = array('message' => '', 'timeout' => 1000);
    if (1 == $result) {
        $msg['message'] = '正在拨号……';
    } else {
        $msg['message'] = '系统繁忙，请稍后再试！';
    }
    die($json->encode($msg));
}

/* 数据中心 */
elseif ('data_center' == $_REQUEST['act']) {
    $msg = array('req_msg' => true, 'timeout' => 2000);
    if (!admin_priv('data_center', '', false)) {
        $msg['message'] = '对不起，无法访问该页面！';
        die($json->encode($msg));
    }

    $_REQUEST['eff_id'] = isset($_REQUEST['eff_id']) ? intval($_REQUEST['eff_id']) : 3;
    $_REQUEST['admin_id'] = 493;
    $user_list = data_center_user_list();

    /* 获取顾客来源、购买力、客服 */
    $smarty->assign('from_where', get_from_where());
    $smarty->assign('type_list',  get_customer_type());
    $smarty->assign('admin_list', get_admin('session'));
    $smarty->assign('eff_list',   getEffectTypes('-1'));

    $smarty->assign('user_list',  $user_list['user_list']);

    // 分页设置
    $smarty->assign('page_link',    $user_list['condition']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('page_size',    $user_list['page_size']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $user_list['page_set']);
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('tag',          $_REQUEST['tag'] ? $_REQUEST['tag'] : 0);
    $smarty->assign('eff_id',       isset($_REQUEST['eff_id']) ? $_REQUEST['eff_id'] : 2);

    $smarty->assign('num', sprintf('（共%d条）', $user_list['record_count']));
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');

    if (isset($_REQUEST['a'])) {
        $smarty->assign('content', 'users_list');
        $res['main'] = $smarty->fetch('list_tpl.htm');
        $res['page'] = $smarty->fetch('page.htm');
        $res['a'] = $_REQUEST['a'];
    } else {
        $res['main'] = $smarty->fetch('data_center.htm');
    }

    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);
    die($json->encode($res));
}

//删除顾客
elseif($_REQUEST['act'] == 'del_user'){
    $user_id = intval($_REQUEST['user_id']);
    $res = array(
        'message'   => '删除失败',
        'timeout'   => 2000,
        'code'      => false,
        'row_index' => $_REQUEST['row_index'],
    );
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET customer_type=4 WHERE user_id=$user_id";
    $res['code'] = $GLOBALS['db']->query($sql);
    if ($res['code']) {
        $res['message'] = '删除成功';
    }
    die($json->encode($res));
}

//计算顾客数量
elseif($_REQUEST['act'] == 'get_user_count'){
    $admin_id = intval($_REQUEST['admin_id']);
    $where = " WHERE admin_id=$admin_id ";
    if (isset($_REQUEST['customer_type'])) {
        $customer_type = intval($_REQUEST['customer_type']);
        $where .= "  AND customer_type=$customer_type";
    }elseif(isset($_REQUEST['eff_id']) && !empty($_REQUEST['eff_id'])){
        $eff_id = intval($_REQUEST['eff_id']);
        $where .= "  AND eff_id=$eff_id";
    }
    $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').$where;
    $res['count'] = $GLOBALS['db']->getOne($sql);
    die($json->encode($res));
}
//回收顾客
elseif($_REQUEST['act'] == 'recyle_user'){
    $role_id       = intval($_REQUEST['role_id']);
    $admin_id      = intval($_REQUEST['recyle_admin']);
    $customer_type = intval($_REQUEST['customer_type']);
    if (in_array($role_id,array(33,34,35,36,37))) {
        $sql = 'SELECT role_id FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$admin_id";
        $to_role_id = $GLOBALS['db']->getOne($sql);
        $where = " WHERE role_id=$role_id AND customer_type=4";
        $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').$where;
        $count = $GLOBALS['db']->getOne($sql);
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET admin_id=$admin_id,role_id=$to_role_id,customer_type=$customer_type"
            .",assign_time={$_SERVER['REQUEST_TIME']} $where";
        $code = $GLOBALS['db']->query($sql);
        if ($code) {
            $res = crm_msg("成功回收{$count}个顾客",$code);
        }else{
            $res = crm_msg('回收失败');
        }
    }else{
        $res = crm_msg('回收失败');
    }
    die($json->encode($res));
}

//顾客流失列表
elseif($_REQUEST['act'] == 'user_lose'){
    $options = array(2=>'两个月', 3=>'三个月', 4=>'更长时间');
    $smarty->assign('options',$options);
    $smarty->assign('service_time',$_REQUEST['service_time']);
    $smarty->assign('order_time',$_REQUEST['order_time']);
    $smarty->assign('manner',$_REQUEST['manner']);

    if (admin_priv('referrals_list_all','',false)) {
        $platform = get_role_list(' WHERE role_id>32 ');
        $platform_list = array ();
        foreach ($platform as $val) {
            $platform_list[$val['role_id']] = $val['role_name'];
        }
        $smarty->assign('platform',    $platform_list);   
    }

    $user_list = user_lose();
    // 输出数据
    $smarty->assign('user_list', $user_list['user_list']);

    $smarty->assign('act',          $_REQUEST['act']);
    $smarty->assign('condition',    '&'.implode('&',$user_list['condition']));
    $smarty->assign('page',         $user_list['page']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_set',    $user_list['page_set']);
    $smarty->assign('page_start',   $user_list['start']);
    $smarty->assign('page_end',     $user_list['end']);
    $smarty->assign('page_size',    $user_list['page_size']);

    //$smarty->assign('page', $res['page']);
    $smarty->assign('page',$smarty->fetch('page.htm'));
    $res['main'] = $smarty->fetch('user_lose.htm');
    die($json->encode($res));
}

/* 函数区 */

//陶顾客列表函数
function ask_customer($table,$filter)
{
    if(!$filter['role_id']) {
        $sqlstrs['count'] = 'SELECT count(*) AS count FROM '.$GLOBALS['ecs']->table($table);
        $sqlstrs['select'] = 'SELECT * FROM '.$GLOBALS['ecs']->table($table);
    } else {
        $sqlstrs['count'] = 'SELECT count(*) AS count FROM '.$GLOBALS['ecs']->table($table).' WHERE role_id='.$filter['role_id'];
        $sqlstrs['select'] = 'SELECT * FROM '.$GLOBALS['ecs']->table($table).' WHERE role_id='.$filter['role_id'];
    }

    $result = filter_page($filter,$sqlstrs);

    foreach($result['result'] as &$val)
    {
        $val['add_time'] = date('Y-m-d H:i',$val['add_time']);
        if($val['service_time'])
        {
            $val['service_time'] = date('Y-m-d H:i',$val['servcie_time']);
        }
    }

    return $result;
}

/**
 *  返回用户列表数据
 *
 * @access  public
 * @param
 *
 * @return void
 */
function user_list() {
    $result = get_filter();
    $filter = array();
    if ($result === false) {
        /* 过滤条件 */
        $filter['user_name']    = empty($_REQUEST['user_name'])    ? '' : trim(urldecode($_REQUEST['user_name']));
        $filter['admin_id']     = empty($_REQUEST['admin_id'])     ? 0  : intval($_REQUEST['admin_id']);
        $filter['address']      = empty($_REQUEST['address'])      ? '' : trim($_REQUEST['address']);
        $filter['zipcode']      = empty($_REQUEST['zipcode'])      ? '' : trim($_REQUEST['zipcode']);
        $filter['home_phone']   = empty($_REQUEST['tel'])          ? '' : trim($_REQUEST['tel']);
        $filter['mobile_phone'] = empty($_REQUEST['mobile'])       ? 0  : intval($_REQUEST['mobile']);
        $filter['country']      = empty($_REQUEST['country'])      ? 0  : intval($_REQUEST['country']);
        $filter['province']     = empty($_REQUEST['province'])     ? 0  : intval($_REQUEST['province']);
        $filter['city']         = empty($_REQUEST['city'])         ? 0  : intval($_REQUEST['city']);
        $filter['district']     = empty($_REQUEST['district'])     ? 0  : intval($_REQUEST['district']);
        $filter['platform']     = empty($_REQUEST['platform'])     ? 0  : intval($_REQUEST['platform']);
        $filter['from_where']   = !intval($_REQUEST['from_where']) ? 0  : intval($_REQUEST['from_where']);
        $filter['eff_id']       = empty($_REQUEST['eff_id'])       ? 0  : intval($_REQUEST['eff_id']);
        $filter['start_time']   = empty($_REQUEST['start_time'])   ? 0  : $_REQUEST['start_time'];
        $filter['end_time']     = empty($_REQUEST['end_time'])     ? 0  : $_REQUEST['end_time'];
        $filter['time_select']  = empty($_REQUEST['time_select'])     ? 4  : $_REQUEST['time_select'];
        $filter['purchase']     = empty($_REQUEST['purchase'])     ? 0  : trim($_REQUEST['purchase']);
        $filter['district']     = empty($_REQUEST['district'])     ? 0  : intval($_REQUEST['district']);
        $filter['city']         = empty($_REQUEST['city'])         ? 0  : $_REQUEST['city'];
        $filter['province']     = empty($_REQUEST['province'])     ? 0  : $_REQUEST['province'];
        $filter['address']      = empty($_REQUEST['address'])      ? 0  : trim($_REQUEST['address']);

        $filter['number_purchased'] = empty($_REQUEST['number_purchased'])?0:intval($_REQUEST['number_purchased']);

        $filter['start_time'] = strtotime(stamp2date($_REQUEST['start_time'], 'Y-m-d H:i:s'));
        $filter['end_time']   = strtotime(stamp2date($_REQUEST['end_time'], 'Y-m-d H:i:s'));

        //李健均的有无服务顾客分类
        if (isset($_REQUEST['type'])) {
            $customer_type = urldecode($_REQUEST['type']);
            if (!in_array($customer_type,array(77,66))) {
                $filter['type'] = empty($_REQUEST['type']) ? 0 : $customer_type;
            }else{
                $filter['type'] = 2;
                $service_where = $customer_type == 66 ? ' AND service_time IS NOT NULL ' : ' AND service_time IS NULL ';
            }
        }

        $filter['cat_tag']      = empty($_REQUEST['cat_tag']) ? 0 : intval($_REQUEST['cat_tag']);

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $ex_where = ' WHERE 1 ';

        // 顾客搜索
        if (! empty($_REQUEST['keywords']) && isset($_REQUEST['keywords'])) {
            $filter['keyfields'] = mysql_real_escape_string(trim($_REQUEST['keyfields']));
            $filter['keywords']  = mysql_real_escape_string(trim(urldecode($_REQUEST['keywords'])));

            $ex_where .= " AND {$filter['keyfields']} LIKE '%{$filter['keywords']}%' ";
        }

        foreach ($filter as $key=>$val) {
            if (!empty($val)) {
                if ($key == 'type') {
                    $condition .= "&$key=".urlencode($val);
                    continue;
                }
                $condition .= "&$key=$val";
            }
        }

        if ($filter['platform']) {
            $ex_where .= " AND platform='{$filter['platform']}' ";
        }

        if ($filter['purchase']) {
            $ex_where .= " AND purchase='{$filter['purchase']}' ";
        }

        if ($filter['number_purchased']) {
            $ex_where .= " AND number_purchased>={$filter['number_purchased']} ";
        }

        /* 按顾客来源显示 顾客列表 */
        if ($filter['from_where']) {
            $ex_where .= " AND u.from_where='{$filter['from_where']}' ";
        }

        // 顾客姓名
        if ($filter['user_name']) {
            $ex_where .= " AND u.user_name LIKE '%{$filter['user_name']}%' ";
        }

        $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').' u ';

        // 客服
        if (admin_priv('all', '', false) && empty($filter['admin_id'])) {
            $ex_where .= ' AND u.admin_id>0';
        } elseif ($filter['admin_id']) {
            if (admin_priv('all', '',false)) {
                $ex_where .= " AND u.admin_id={$filter['admin_id']} ";

            } elseif (admin_priv('user_trans-part_view', '', false)) {
                $trans_role_list = implode(',', trans_part_list());
                $sql_select_admin = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').
                    " WHERE user_id={$filter['admin_id']} AND role_id IN ($trans_role_list)";
                $admin_id = $GLOBALS['db']->getOne($sql_select_admin);

                if ($admin_id) {
                    $ex_where .= " AND u.admin_id={$filter['admin_id']} ";
                }
            } elseif (admin_priv('user_part_view', '', false)) {
                $sql_select_admin = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').
                    " WHERE user_id={$filter['admin_id']} AND role_id={$_SESSION['role_id']}";
                $admin_id = $GLOBALS['db']->getOne($sql_select_admin);

                if ($admin_id) {
                    $ex_where .= " AND u.admin_id={$filter['admin_id']} ";
                }
            } elseif (admin_priv('user_group_view', '', false)) {
                $ex_where .= " AND u.group_id={$_SESSION['group_id']}";
                if ($filter['admin_id']) {
                    $ex_where .= " AND u.admin_id={$filter['admin_id']} ";
                }
            }
        } else {
            $ex_where .= " AND u.admin_id={$_SESSION['admin_id']}";
        }

        // 区
        if ($filter['district']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.district=r.region_id AND a.district=$filter[district]";
        }

        //判断所属省，市的顾客
        elseif ($filter['city']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.city=r.region_id AND a.city=$filter[city]";
        }

        //判断所属省份的顾客
        elseif ($filter['province']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.province=r.region_id AND a.province=$filter[province]";
        }

        // 详细地址
        if ($filter['saddress']) {
            $sql .= ', '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND a.address LIKE '%$filter[saddress]%' AND a.user_id=u.user_id";
        }

        // 自定义分类
        $admin_id = $filter['admin_id'] == 0 ? $_SESSION['admin_id'] : $filter['admin_id'];
        if ($filter['cat_tag']) {
            $ex_where .= " AND user_cat='$admin_id-{$filter['cat_tag']}' ";
        } elseif ($_REQUEST['act'] == 'user_cat_list') {
            $ex_where .= " AND user_cat NOT LIKE '$admin_id-%' ";
        }

        if ($filter['start_time'] && $filter['end_time']) {
            if ($filter['start_time'] > $filter['end_time']) {
                $time_tmp = $filter['end_time'];

                $filter['end_time']   = $filter['start_time'];
                $filter['start_time'] = $time_tmp;
            }
            switch ($filter['time_select']) {
                case 1 :
                    $time_where = 'service_time';
                    break;
                case 2 :
                    $time_where = 'order_time';
                    break;
                case 3 :
                    $time_where = 'service_time';
                    break;
                case 4 :
                    $time_where = 'add_time';
                    break;
            }
            $ex_where .= " AND u.$time_where BETWEEN '{$filter['start_time']}' AND '{$filter['end_time']}'";
        }

        if($filter['type']) {
            $ex_where .= " AND u.customer_type IN ({$filter['type']}) ";
        }

        // 功效分类
        if ($filter['eff_id'] && $filter['eff_id'] > 0) {
            $ex_where .= " AND u.eff_id={$filter['eff_id']}";
        } elseif ($filter['eff_id'] && $filter['eff_id'] < 0) {
            $ex_where .= ' AND u.eff_id=0 ';
        }

        $sql .= $ex_where.$service_where;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } else {
            $filter['page_size'] = 20;
        }

        $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

        // 设置分页
        $page_set = array (1,2,3,4,5,6,7);
        if ($filter['page'] > 4) {
            foreach ($page_set as &$val) {
                $val += $filter['page'] -4;
            }
        }

        if (end($page_set) > $filter['page_count']) {
            $page_set = array ();
            for ($i = 7; $i >= 0; $i--) {
                if ($filter['page_count'] - $i > 0) {
                    $page_set[] = $filter['page_count'] - $i;
                }
            }
        }

        $sql = 'SELECT u.age_group,u.admin_name,u.user_id,u.is_black,e.eff_name,c.bind_time,u.user_name,u.sex,'.
            'IF(u.birthday="2012-01-01",u.age_group,(YEAR(NOW())-YEAR(u.birthday))) birthday,u.is_validated,u.user_money,'.
            'u.add_time,u.remarks,u.service_time FROM '.$GLOBALS['ecs']->table('users').' u LEFT JOIN '.
            $GLOBALS['ecs']->table('memship_number').' c ON c.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('effects').
            ' e ON e.eff_id=u.eff_id';

        //判断一个月内转移的顾客
        $_REQUEST['transfer_time'] && $filter['transfer_time'] = $_REQUEST['transfer_time'];
        if($filter['transfer_time']) {
            $ex_where .= ' AND u.transfer_time>'.$filter['transfer_time'];
        }

        if ($filter['district']) {
            $sql .= ', '. $GLOBALS['ecs']->table('region').' r, '. $GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.district=r.region_id AND a.district=$filter[district]";
        }

        //判断所属省，市的顾客
        elseif ($filter['city']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.city=r.region_id AND a.city=$filter[city]";
        }

        //判断所属省份的顾客
        elseif ($filter['province']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.province=r.region_id AND a.province=$filter[province]";
        }

        $ex_where .= $service_where.' ORDER by service_time ASC LIMIT '.($filter['page'] -1)*$filter['page_size'].', '.$filter['page_size'];
        $sql .= $ex_where;

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
        if (497 == $_SESSION['admin_id']) {
            //echo $sql,PHP_EOL;
        }
    } else {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $user_list = $GLOBALS['db']->getAll($sql); // 正常查询

    if (in_array($filter['keyfields'], array('home_phone', 'mobile_phone', 'qq', 'wechat', 'aliww'))) {
        // 客服
        if (admin_priv('all', '', false) && empty($filter['admin_id'])) {
            $where = ' u.admin_id>0';
        } elseif ($filter['admin_id']) {
            if (admin_priv('all', '',false)) {
                $where = " u.admin_id={$filter['admin_id']} ";
            } elseif (admin_priv('user_part_view', '', false)) {
                $sql_select_admin = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').
                    " WHERE user_id={$filter['admin_id']} AND role_id={$_SESSION['role_id']}";
                $admin_id = $GLOBALS['db']->getOne($sql_select_admin);

                if ($admin_id) {
                    $where = " u.admin_id={$filter['admin_id']} ";
                }
            } elseif (admin_priv('user_group_view', '', false)) {
                $where = " u.group_id={$_SESSION['group_id']}";
                if ($filter['admin_id']) {
                    $where .= " AND u.admin_id={$filter['admin_id']} ";
                }
            }
        } else {
            $where = " u.admin_id={$_SESSION['admin_id']}";
        }

        $sql_select = 'SELECT u.age_group,u.admin_name,u.user_id,u.is_black,c.card_number,c.bind_time,u.user_name,u.sex,'.
            'IF(u.birthday="2012-01-01",u.age_group,(YEAR(NOW())-YEAR(u.birthday))) birthday,u.is_validated,u.add_time,'.
            'u.remarks,u.service_time FROM '.$GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('memship_number').
            ' c ON c.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('effects').' e ON e.eff_id=u.eff_id LEFT JOIN '.
            $GLOBALS['ecs']->table('user_contact')." uc ON u.user_id=uc.user_id WHERE $where AND u.customer_type IN ({$filter['type']})".
            ' AND uc.contact_name="%s" AND uc.contact_value="%s"';
        switch ($filter['keyfields']) {
        case 'home_phone' :
            $user_info = $GLOBALS['db']->getAll(sprintf($sql_select, 'tel', $filter['keywords']));
            break;
        case 'mobile_phone' :
            $user_info = $GLOBALS['db']->getAll(sprintf($sql_select, 'mobile', $filter['keywords']));
            break;
        default :
            $user_info = $GLOBALS['db']->getAll(sprintf($sql_select, $filter['keyfields'], $filter['keywords']));
        }
        $user_list += $user_info;
    }

    foreach ($user_list as &$val) {
        $val['add_time']      = date('Y-m-d', $val['add_time']);
        $val['transfer_time'] = $val['transfer_time'] ? date('Y-m-d', $val['transfer_time']) : '-';
        $val['service_time']  = date('Y-m-d', $val['service_time']);
        $val['bind_time']     = date('Y-m-d', $val['bind_time']);
        $val['bind_time']  = date('Y-m-d', $val['bind_time']);
        if($val['is_black'] == 1){
            $val['user_name'] = '<font class="font_blacklist" >'.$val['user_name'].'</font>';
        }
    }

    $arr = array(
        'user_list'    => $user_list,
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

function data_center_user_list() {
    $result = get_filter();
    $filter = array();
    if ($result === false) {
        /* 过滤条件 */
        $filter['user_name']    = empty($_REQUEST['user_name'])    ? '' : trim(urldecode($_REQUEST['user_name']));
        $filter['admin_id']     = empty($_REQUEST['admin_id'])     ? 0  : intval($_REQUEST['admin_id']);
        $filter['address']      = empty($_REQUEST['address'])      ? '' : trim($_REQUEST['address']);
        $filter['zipcode']      = empty($_REQUEST['zipcode'])      ? '' : trim($_REQUEST['zipcode']);
        $filter['home_phone']   = empty($_REQUEST['tel'])          ? '' : trim($_REQUEST['tel']);
        $filter['mobile_phone'] = empty($_REQUEST['mobile'])       ? 0  : intval($_REQUEST['mobile']);
        $filter['country']      = empty($_REQUEST['country'])      ? 0  : intval($_REQUEST['country']);
        $filter['province']     = empty($_REQUEST['province'])     ? 0  : intval($_REQUEST['province']);
        $filter['city']         = empty($_REQUEST['city'])         ? 0  : intval($_REQUEST['city']);
        $filter['district']     = empty($_REQUEST['district'])     ? 0  : intval($_REQUEST['district']);
        $filter['platform']     = empty($_REQUEST['platform'])     ? 0  : intval($_REQUEST['platform']);
        $filter['from_where']   = !intval($_REQUEST['from_where']) ? 0  : intval($_REQUEST['from_where']);
        $filter['eff_id']       = empty($_REQUEST['eff_id'])       ? 0  : intval($_REQUEST['eff_id']);
        $filter['start_time']   = empty($_REQUEST['start_time'])   ? 0  : $_REQUEST['start_time'];
        $filter['end_time']     = empty($_REQUEST['end_time'])     ? 0  : $_REQUEST['end_time'];
        $filter['purchase']     = empty($_REQUEST['purchase'])     ? 0  : trim($_REQUEST['purchase']);
        $filter['district']     = empty($_REQUEST['district'])     ? 0  : intval($_REQUEST['district']);
        $filter['city']         = empty($_REQUEST['city'])         ? 0  : $_REQUEST['city'];
        $filter['province']     = empty($_REQUEST['province'])     ? 0  : $_REQUEST['province'];
        $filter['address']      = empty($_REQUEST['address'])      ? 0  : trim($_REQUEST['address']);

        $filter['number_purchased'] = empty($_REQUEST['number_purchased'])?0:intval($_REQUEST['number_purchased']);

        $filter['start_time'] = strtotime(stamp2date($_REQUEST['start_time'], 'Y-m-d H:i:s'));
        $filter['end_time']   = strtotime(stamp2date($_REQUEST['end_time'], 'Y-m-d H:i:s'));

        //李健均的有无服务顾客分类
        if (isset($_REQUEST['type'])) {
            $customer_type = urldecode($_REQUEST['type']);
            if (!in_array($customer_type,array(77,66))) {
                $filter['type'] = empty($_REQUEST['type']) ? 0 : $customer_type;
            }else{
                $filter['type'] = 2;
                $service_where = $customer_type == 66 ? ' AND service_time IS NOT NULL ' : ' AND service_time IS NULL ';
            }
        }

        $filter['cat_tag']      = empty($_REQUEST['cat_tag']) ? 0 : intval($_REQUEST['cat_tag']);

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $ex_where = ' WHERE 1 ';

        // 顾客搜索
        if (! empty($_REQUEST['keywords']) || isset($_REQUEST['keywords'])) {
            $filter['keyfields'] = mysql_real_escape_string(trim($_REQUEST['keyfields']));
            $filter['keywords']  = mysql_real_escape_string(trim(urldecode($_REQUEST['keywords'])));

            $ex_where .= " AND {$filter['keyfields']} LIKE '%{$filter['keywords']}%' ";
        }

        foreach ($filter as $key=>$val) {
            if (!empty($val)) {
                if ($key == 'type') {
                    $condition .= "&$key=".urlencode($val);
                    continue;
                }
                $condition .= "&$key=$val";
            }
        }

        if ($filter['platform']) {
            $ex_where .= " AND platform='{$filter['platform']}' ";
        }

        if ($filter['purchase']) {
            $ex_where .= " AND purchase='{$filter['purchase']}' ";
        }

        if ($filter['number_purchased']) {
            $ex_where .= " AND number_purchased>={$filter['number_purchased']} ";
        }

        /* 按顾客来源显示 顾客列表 */
        if ($filter['from_where']) {
            $ex_where .= " AND u.from_where='{$filter['from_where']}' ";
        }

        // 顾客姓名
        if ($filter['user_name']) {
            $ex_where .= " AND u.user_name LIKE '%{$filter['user_name']}%' ";
        }

        $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').' u ';

        $ex_where .= ' AND u.admin_id IN(0,493) ';

        // 区
        if ($filter['district']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.district=r.region_id AND a.district=$filter[district]";
        }

        //判断所属省，市的顾客
        elseif ($filter['city']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.city=r.region_id AND a.city=$filter[city]";
        }

        //判断所属省份的顾客
        elseif ($filter['province']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.province=r.region_id AND a.province=$filter[province]";
        }

        // 详细地址
        if ($filter['saddress']) {
            $sql .= ', '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND a.address LIKE '%$filter[saddress]%' AND a.user_id=u.user_id";
        }

        // 自定义分类
        $admin_id = $filter['admin_id'] == 0 ? $_SESSION['admin_id'] : $filter['admin_id'];
        if ($filter['cat_tag']) {
            $ex_where .= " AND user_cat='$admin_id-{$filter['cat_tag']}' ";
        } elseif ($_REQUEST['act'] == 'user_cat_list') {
            $ex_where .= " AND user_cat NOT LIKE '$admin_id-%' ";
        }

        if ($filter['start_time'] && $filter['end_time']) {
            if ($filter['start_time'] > $filter['end_time']) {
                $time_tmp = $filter['end_time'];

                $filter['end_time']   = $filter['start_time'];
                $filter['start_time'] = $time_tmp;
            }

            $ex_where .= " AND u.add_time BETWEEN '{$filter['start_time']}' AND '{$filter['end_time']}'";
        }

        if($filter['type']) {
            $ex_where .= " AND u.customer_type IN ({$filter['type']}) ";
        }

        // 功效分类
        if ($filter['eff_id'] && $filter['eff_id'] > 0) {
            $ex_where .= " AND u.eff_id={$filter['eff_id']}";
        } else {
            $ex_where .= ' AND u.eff_id=0 ';
        }

        $sql .= $ex_where.$service_where;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } else {
            $filter['page_size'] = 20;
        }

        $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

        // 设置分页
        $page_set = array (1,2,3,4,5,6,7);
        if ($filter['page'] > 4) {
            foreach ($page_set as &$val) {
                $val += $filter['page'] -4;
            }
        }

        if (end($page_set) > $filter['page_count']) {
            $page_set = array ();
            for ($i = 7; $i >= 0; $i--) {
                if ($filter['page_count'] - $i > 0) {
                    $page_set[] = $filter['page_count'] - $i;
                }
            }
        }

        $sql = 'SELECT u.age_group,u.admin_name,u.user_id,u.is_black,e.eff_name,c.bind_time,u.user_name,u.sex,'.
            'IF(u.birthday="2012-01-01",u.age_group,(YEAR(NOW())-YEAR(u.birthday))) birthday,u.is_validated,u.user_money,'.
            'u.add_time,u.remarks,u.service_time FROM '.$GLOBALS['ecs']->table('users').' u LEFT JOIN '.
            $GLOBALS['ecs']->table('memship_number').' c ON c.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('effects').
            ' e ON e.eff_id=u.eff_id';

        //判断一个月内转移的顾客
        $_REQUEST['transfer_time'] && $filter['transfer_time'] = $_REQUEST['transfer_time'];
        if($filter['transfer_time']) {
            $ex_where .= ' AND u.transfer_time>'.$filter['transfer_time'];
        }

        if ($filter['district']) {
            $sql .= ', '. $GLOBALS['ecs']->table('region').' r, '. $GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.district=r.region_id AND a.district=$filter[district]";
        }

        //判断所属省，市的顾客
        elseif ($filter['city']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.city=r.region_id AND a.city=$filter[city]";
        }

        //判断所属省份的顾客
        elseif ($filter['province']) {
            $sql .= ', '.$GLOBALS['ecs']->table('region').' r, '.$GLOBALS['ecs']->table('user_address').' a ';
            $ex_where .= " AND u.user_id=a.user_id AND a.province=r.region_id AND a.province=$filter[province]";
        }

        $ex_where .= $service_where.' ORDER by service_time ASC LIMIT '.($filter['page'] -1)*$filter['page_size'].', '.$filter['page_size'];
        $sql .= $ex_where;

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    } else {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $user_list = $GLOBALS['db']->getAll($sql); // 正常查询

    if (in_array($filter['keyfields'], array('home_phone', 'mobile_phone', 'qq', 'wechat', 'aliww'))) {
        // 客服
        $where = ' u.admin_id>0';

        $sql_select = 'SELECT u.age_group,u.admin_name,u.user_id,u.is_black,c.card_number,c.bind_time,u.user_name,u.sex,'.
            'IF(u.birthday="2012-01-01",u.age_group,(YEAR(NOW())-YEAR(u.birthday))) birthday,u.is_validated,u.add_time,'.
            'u.remarks,u.service_time FROM '.$GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('memship_number').
            ' c ON c.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('effects').' e ON e.eff_id=u.eff_id LEFT JOIN '.
            $GLOBALS['ecs']->table('user_contact')." uc ON u.user_id=uc.user_id WHERE $where AND u.customer_type IN ({$filter['type']})".
            ' AND uc.contact_name="%s" AND uc.contact_value="%s"';
        switch ($filter['keyfields']) {
        case 'home_phone' :
            $user_info = $GLOBALS['db']->getAll(sprintf($sql_select, 'tel', $filter['keywords']));
            break;
        case 'mobile_phone' :
            $user_info = $GLOBALS['db']->getAll(sprintf($sql_select, 'mobile', $filter['keywords']));
            break;
        default :
            $user_info = $GLOBALS['db']->getAll(sprintf($sql_select, $filter['keyfields'], $filter['keywords']));
        }
        $user_list += $user_info;
    }

    foreach ($user_list as &$val) {
        $val['add_time']      = date('Y-m-d', $val['add_time']);
        $val['transfer_time'] = $val['transfer_time'] ? date('Y-m-d', $val['transfer_time']) : '-';
        $val['service_time']  = date('Y-m-d', $val['service_time']);
        $val['bind_time']     = date('Y-m-d', $val['bind_time']);
        $val['bind_time']  = date('Y-m-d', $val['bind_time']);
        if($val['is_black'] == 1){
            $val['user_name'] = '<font class="font_blacklist" >'.$val['user_name'].'</font>';
        }
    }

    $arr = array(
        'user_list'    => $user_list,
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
 * 获取会员部客服信息
 * return   Array  客服ID、客服姓名
 */
function getMemAdmin()
{
    $sql = 'SELECT user_id, user_name FROM '.$GLOBALS['ecs']->table('admin_user')
        .' WHERE role_id=9';
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取功效分类信息
 * return  Array  人群分类/分类ID
 */
function getEffectTypes($available=0) {
    $sql = 'SELECT eff_id, eff_name FROM '.$GLOBALS['ecs']->table('effects').
        " WHERE available>$available ORDER BY sort ASC ";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 更新用户社会关系信息
 */
function updateSocial()
{
    foreach ($_POST['uname'] as $key=>$val)
    {
        if (empty($val))
        {
            continue;
        }

        $sql = 'SELECT rela_id FROM '.$GLOBALS['ecs']->table('user_relation')." WHERE rela_id=$key AND user_id={$_POST['user_id']}";
        $rela = $GLOBALS['db']->getOne($sql);
        if ($rela)
        {
            $update_tmp = array (
                'uname="'.mysql_real_escape_string($val).'"',
                'mobile="'.mysql_real_escape_string(trim($_POST['mobile'][$key])).'"',
                'relation="'.mysql_real_escape_string(trim($_POST['relation'][$key])).'"',
                'habitancy="'.mysql_real_escape_string(trim($_POST['habitancy'][$key])).'"',
                'age="'.intval($_POST['age'][$key]).'"',
                'rela_sex="'.intval($_POST['relasex'][$key]).'"',
                'profession="'.mysql_real_escape_string(trim($_POST['profession'][$key])).'"',
                'financial="'.mysql_real_escape_string(trim($_POST['financial'][$key])).'"',
                'selfcare="'.mysql_real_escape_string(trim($_POST['selfcare'][$key])).'"',
            );
            $sql = 'UPDATE '.$GLOBALS['ecs']->table('user_relation').' SET '.implode(',', $update_tmp)." WHERE rela_id='$key' AND user_id='{$_POST['user_id']}'";
            unset($_POST['uname'][$key]);
        }
        else
        {
            $newRela = 1;
        }

        $GLOBALS['db']->query($sql);
    }

    $newRela == 1 && insertSocial();
}

/**
 * 插入顾客社会关系
 */
function insertSocial ()
{
    // 添加用户社会关系信息
    $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('user_relation').'(rela_id, user_id, uname, rela_sex, mobile, relation, habitancy, age, add_age_year, profession, financial, selfcare, rela_user_id)VALUES';
    foreach ($_POST['uname'] as $key=>$val)
    {
        // 如果没用姓名 则跳过该条记录
        if (empty($val))
        {
            continue;
        }

        $sql_temp = array (
            'rela_id'      => $key,
            'user_id'      => $user_id ? $user_id : $_POST['user_id'], // 顾客关联ID
            'uname'        => trim($val),                         // 姓名
            'rela_sex'     => intval($_POST['relasex'][$key]),    // 性别
            'mobile'       => mysql_real_escape_string(trim($_POST['mobile'][$key])),       // 联系电话
            'relation'     => mysql_real_escape_string(trim($_POST['relation'][$key])),     // 社会关系
            'habitancy'    => mysql_real_escape_string(trim($_POST['habitancy'][$key])),  // 居住情况
            'age'          => intval($_POST['age'][$key]),        // 年龄
            'add_age_year' => date('Y', time()),            // 添加年份，用于计算当前年龄
            'profession'   => mysql_real_escape_string(trim($_POST['profession'][$key])), // 职业
            'financial'    => intval($_POST['financial'][$key]),  // 经济状况
            'selfcare'     => intval($_POST['selfcare'][$key]),   // 保健意识
            'rela_user_id' => ''
        );

        // 查询该号码的主人是否已成为顾客
        $sql_temp['rela_user_id'] = $GLOBALS['db']->getOne('SELECT user_id FROM '.$GLOBALS['ecs']->table('users')." WHERE home_phone='{$sql_temp['mobile']}' OR mobile_phone='{$sql_temp['mobile']}'");

        $sql_array[] = '("'.implode('","', array_map('mysql_real_escape_string', $sql_temp)).'")';
    }

    $sql .= implode(',', $sql_array);
    $GLOBALS['db']->query($sql);
}

/**
 * 获取地址信息
 * @param   $id   int   地区ID
 */
function get_address ($id)
{
    $sql = 'SELECT region_name FROM '.$GLOBALS['ecs']->table('region').
        " WHERE region_id=$id";
    return $GLOBALS['db']->getOne($sql);
}

/**
 * Details of user
 * @param   $id  int   User's ID
 */
function get_user_info ($id)
{
    $field = 'u.wechat,u.mobile_phone,u.home_phone,u.aliww,';
    $mem = new Memcache();
    $mem->connect('127.0.0.1',11211);

    if ($mem->get("freeze_{$_SESSION['admin_id']}") || is_freeze()) {
        $field = '';
        $mem->set("freeze_{$_SESSION['admin_id']}", 1, false, 3600);
        $mem->close();
    }

    $sql_select = 'SELECT u.age,u.family_id,u.sex,IF(u.calendar=1,CONCAT(u.birthday,"【阴历】"),CONCAT(u.birthday,"【阳历】")) birthday,'.$field.
        'u.user_name,u.role_id,u.characters,u.service_time,u.member_cid,u.number_purchased,u.habby,u.email,u.disease,m.card_number,'.
        'u.disease_2,u.from_where,u.user_id,u.add_time,u.id_card,u.eff_id,u.qq,t.type_name customer_type,u.remarks,prt.user_id referrals_id,'.
        'prt.user_name referrals FROM'.$GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('users').
        ' prt ON u.parent_id=prt.user_id LEFT JOIN '.$GLOBALS['ecs']->table('memship_number').' m ON m.user_id=u.user_id, '.
        $GLOBALS['ecs']->table('customer_type')." t WHERE u.customer_type=t.type_id AND u.user_id=$id";
    $user_info = $GLOBALS['db']->getRow($sql_select);

    if (($_SERVER['REQUEST_TIME']-$user_info['add_time'])/60/60/24>1){
        $user_info['from_where_edit'] = true;
    }
    if(!in_array($_SESSION['admin_id'],array(4,493,554,330,277))){
        $user_info['mobile_phone'] = hideContact($user_info['mobile_phone']);
        $user_info['home_phone'] = hideContact($user_info['home_phone']);
    }

    $sql_select = "SELECT r.rank_name,u.rank_points,u.user_rank FROM ".$GLOBALS['ecs']->table('user_rank').' r,'.
        $GLOBALS['ecs']->table('users')." u WHERE u.user_rank=r.rank_id AND u.user_id=$id";
    $user_rank = array();
    $user_rank[] = $GLOBALS['db']->getRow($sql_select);
    $user_rank = reset($user_rank);
    if(!$user_rank) {
        $user_rank = array('rank_name'=>'未分配','rank_points'=>0);
    }
    $user_info = array_merge($user_info,$user_rank);

    // 获取顾客地址
    $sql_select = 'SELECT p.region_name province,c.region_name city,d.region_name district,'.
        'ua.address,ua.province province_id,ua.city city_id,ua.district district_id FROM '.
        $GLOBALS['ecs']->table('user_address').' ua LEFT JOIN '.$GLOBALS['ecs']->table('region').
        ' p ON p.region_id=ua.province LEFT JOIN '.$GLOBALS['ecs']->table('region').
        ' c ON c.region_id=ua.city LEFT JOIN '.$GLOBALS['ecs']->table('region').
        ' d ON d.region_id=ua.district'." WHERE ua.user_id=$id";
    $user_region = $GLOBALS['db']->getAll($sql_select);

    if (is_array($user_region[0]))
    {
        $user_info = array_merge($user_info, $user_region[0]);
    }

    $sql_select = 'SELECT r.role_name platform FROM '.$GLOBALS['ecs']->table('role').
        ' r, '.$GLOBALS['ecs']->table('users')." u WHERE u.role_id=r.role_id AND u.user_id=$id";
    $user_info['platform'] = $GLOBALS['db']->getOne($sql_select);

    // format time
    $user_info['add_time']     = date('Y-m-d H:i', $user_info['add_time']);     // 添加时间
    $user_info['service_time'] = date('Y-m-d H:i', $user_info['service_time']); // 上次服务时间
    $user_info['birthday']     = mb_strlen($user_info['birthday'])>12 ? $user_info['birthday'] : '';

    $user_info['disease']    = explode(':', $user_info['disease']);    // 疾病
    $user_info['characters'] = explode(':', $user_info['characters']); // 性格

    // 获取顾客需求
    $sql_select = 'SELECT eff_name FROM '.$GLOBALS['ecs']->table('effects').
        " WHERE eff_id='{$user_info['eff_id']}'";
    $user_info['eff_name'] = $GLOBALS['db']->getOne($sql_select);

    // 获取顾客来源
    $sql_select = 'SELECT `from` FROM '.$GLOBALS['ecs']->table('from_where').
        " WHERE from_id='{$user_info['from_where']}'";
    $user_info['from_where'] = $GLOBALS['db']->getOne($sql_select);

    // 获取顾客经济来源
    $sql_select = 'SELECT income FROM '.$GLOBALS['ecs']->table('income').
        " WHERE income_id='{$user_info['income']}'";
    $user_info['income'] = $GLOBALS['db']->getOne($sql_select);

    return $user_info;
}

/**
 * Access to user purchase records
 * @param  $id    int   user_id
 */
function access_purchase_records ($id)
{
    // Get user to buy records 获取顾客购买记录
    $sql_select = 'SELECT o.order_id,o.platform_order_sn,o.order_sn p_order_sn,o.consignee,o.order_status,o.shipping_status,o.add_time,'.
        'o.shipping_name,o.pay_name,o.final_amount,o.tracking_sn express_number,a.user_name operator,o.receive_time,o.shipping_code, '.
        ' r.role_describe platform FROM '.$GLOBALS['ecs']->table('order_info').' o,'.$GLOBALS['ecs']->table('admin_user').' a, '.
        $GLOBALS['ecs']->table('role').' r WHERE  o.add_admin_id=a.user_id AND '.
        " r.role_id=o.team AND o.user_id=$id AND o.shipping_status<>3 GROUP BY o.order_id ORDER BY o.add_time ASC ";

    $order_list = $GLOBALS['db']->getAll($sql_select);

    //o.order_status=5 AND o.shipping_status IN (1,2) AND
    // Format time
    krsort($order_list);
    $order_total = count($order_list);
    foreach ($order_list as &$val)
    {
        $val['order_sn'] = $v['platform_order_sn']||$v['p_order_sn'];
        $val['index'] = $order_total--;
        $val['add_time']     = date('Y-m-d', $val['add_time']);   // Buy time
        $val['service_time'] = date('Y-m-d', $val['service_time']);
        $val['receive_time'] = $val['receive_time'] ? date('Y-m-d', $val['receive_time']) : '-';// Receive time

        // 宅急送code处理
        $val['shipping_code'] = $val['shipping_code'] == 'sto_express' ? 'zjs' :$val['shipping_code'];
        $val['shipping_code'] = $val['shipping_code'] == 'sto_nopay' ? 'zjs' :$val['shipping_code'];

        $sql_select = 'SELECT goods_id,goods_name,goods_number,goods_price,is_package,goods_sn FROM '.
            $GLOBALS['ecs']->table('order_goods')." WHERE order_id={$val['order_id']}";
        $val['goods_list'] = $GLOBALS['db']->getAll($sql_select);
        $val['goods_kind'] = count($val['goods_list']);

        foreach ($val['goods_list'] as &$v)
        {
            if ($v['is_package'])
            {
                $sql_select = 'SELECT goods_name,num goods_number,g.goods_sn FROM '.
                    $GLOBALS['ecs']->table('packing_goods').' g,'.$GLOBALS['ecs']->table('packing').
                    " p WHERE p.packing_id=g.packing_id AND p.packing_desc='{$v['goods_sn']}'";
                $v['goods_list'] = $GLOBALS['db']->getAll($sql_select);
            }
        }

        $temp_status = $val['order_status'].$val['shipping_status'];

        //echo $val['order_status'].'---'.$val['shipping_status'].'---'.$temp_status.'!';
        switch($temp_status)
        {
        case "52" : $val['finaly_order_status'] = '<font>已签收</font>'; $val['tr'] = 'bgcolor=""'; break;
        case "51" : $val['finaly_order_status'] = '<font>已发货</font>'; $val['tr'] = ''; break;
        case "54" : $val['finaly_order_status'] = '<font color="#FF000">已退货</font>';  $val['tr'] = 'style="background:#FFF7FB !important"';break;
        case "13" : $val['finaly_order_status'] = '<font color="#00FF00">已取消</font>'; $val['tr'] = 'style="background:##F0F0F0 !important"'; break;
        case "10" : $val['finaly_order_status'] = '<font>待发货</font>'; $val['tr'] = 'style="background:#D1E9E9 !important"'; break;
        }

    }

    //产品介绍
    goods_introduce($order_list);
    return $order_list;
}

/**
 * 获取顾客服务记录
 */
function get_user_services ($user_id)
{
    $sql_select = 'SELECT service_id,user_name,user_id,logbook,admin_name,service_time FROM '.
        $GLOBALS['ecs']->table('service')." WHERE user_id=$user_id ORDER BY service_time DESC";
    $res = $GLOBALS['db']->getAll($sql_select);
    $total = count($res);
    foreach ($res as &$val) {
        $val['record_date']  = date('Ymd', $val['service_time']);
        $val['service_time'] = date('Y-m-d H:i', $val['service_time']);
        $val['index'] = $total--;
    }

    return $res;
}

/**
 * 获取退货记录
 */
function get_return_list ($user_id)
{
    $sql_select = 'SELECT o.consignee,o.shipping_name,o.other_reason,o.mobile,o.tel,o.return_time FROM '.
        $GLOBALS['ecs']->table('back_order')." o WHERE user_id=$user_id";
    $res = $GLOBALS['db']->getAll($sql_select);

    foreach ($res as &$val)
    {
        $val['return_time'] = local_date('Y-m-d H:i:s', $val['return_time']);
    }

    return $res;
}

/**
 * 健康档案
 */
function get_healthy($user_id)
{
    $sql_select = "SELECT COUNT(*) AS total,born_address,work_address,(CASE is_marry WHEN 0 THEN '未婚' WHEN 1 THEN '已婚' WHEN 2 THEN '单身' END) AS is_marry, (IF(regular_check=0,'否','是')) AS regular_check,(CASE cycle_check WHEN 0 THEN '半年' WHEN 1 THEN '一年' WHEN 2 THEN '两年' WHEN 3 THEN '两年以上' END) AS cyle_check,psychology,allergy,allergy_reason,family_case,before_case,other,tumour FROM ".$GLOBALS['ecs']->table('user_archive').
        " WHERE user_id=$user_id";
    $base_info = $GLOBALS['db']->getRow($sql_select);       //基本信息-过敏史-家庭病历-心理-其它

    if($before_case)
    {
        $before_case = explode(' ',$base_info['before_case']);
        $base_info['before_case'] = $before_case;
    }

    if($family_case)
    {
        $family_case = explode(' ',$base_info['family_case']);
        $base_info['family_case'] = $family_case;
    }

    //$sql_select = " SELECT FROM_UNIXTIME(input_time,'%Y-%m-%d') FROM ".$GLOBALS['ecs']->table('other_examination_test')
    //    ." WHERE user_id=$user_id"
    //    .' GROUP BY input_time ORDER BY input_time DESC';

    //$weight_condition = $GLOBALS['db']->getCol($sql_select);

    //$sql_select = 'SELECT et.*,e.examination_name AS examination_name_zh,a.user_name AS admin_name FROM '.$GLOBALS['ecs']->table('other_examination_test')
    //    .' AS et LEFT JOIN '.$GLOBALS['ecs']->table('examination')
    //    .' AS e ON et.examination_name=e.descript LEFT JOIN '
    //    .$GLOBALS['ecs']->table('admin_user').' AS a ON et.admin_id=a.user_id '
    //    ." WHERE et.user_id=$user_id AND FROM_UNIXTIME(input_time,'%Y-%m-%d')='{$weight_condition[0]}'";

    //$recently_test = $GLOBALS['db']->getAll($sql_select);

    //foreach($recently_test AS &$val)
    //{
    //    $recently_test_temp['examination_name'][] = $val['examination_name_zh'];
    //    $recently_test_temp['examination_test'][$val['examination_name']] = $val['examination_value'];
    //    $recently_test_temp['admin_name'] = $val['admin_name'];
    //    $recently_test_temp['input_time'] = date('Y-m-d',$val['input_time']);
    //}
    //$recently_test = $recently_test_temp;

    //$sql_select = "SELECT COUNT(*) AS total, (IF(work_type=1,'体力劳动','脑力劳动')) AS work_type,(CASE work_time WHEN 0 THEN '6小时' WHEN 1 THEN '6-8小时' WHEN 2 THEN '8-10小时' WHEN 3 THEN '10小时' END) AS work_time,travel_situation,enviroment,(IF(healthy_element=0,'否','是')) AS healthy_element,(CASE blood_type WHEN 0 THEN 'A型'  WHEN 0 THEN 'A型'  WHEN 1 THEN 'B型'  WHEN 2 THEN 'AB型'  WHEN 4 THEN 'O型' END) AS blood_type FROM ".$GLOBALS['ecs']->table('work_condition').
    //    " WHERE user_id=$user_id";                       //工作情况

    //$work_condition = $GLOBALS['db']->getRow($sql_select);

    //$sql_select = "SELECT COUNT(*) AS total,(CASE sleep_habit WHEN 0 THEN '早睡早起' WHEN 1 THEN '早睡晚起' WHEN 2 THEN '晚睡晚起' WHEN 3 THEN '晚睡早起' WHEN 4 THEN '不规律' END) AS sleep_habit,(CASE bedtime_start WHEN 0 THEN '早于22点' WHEN 1 THEN '22点到23点' WHEN 2 THEN '23点到0点' WHEN 3 THEN '0点后' END) AS bedtime_start,(CASE smoke WHEN 0 THEN '否' WHEN 1 THEN '是' WHEN 2 THEN '偶尔' WHEN 3 THEN '已戒' END) AS smoke,(CASE smoke_number WHEN 0 THEN '1-5支' WHEN 1 THEN '6-10支' WHEN 2 THEN '11-20支' WHEN 3 THEN '20支以上' END) AS smoke_number,(CASE smoke_age WHEN 0 THEN '1-2年' WHEN 1 THEN '3-5年' WHEN 2 THEN '5-10年' WHEN 3 THEN '10年以上' END) AS smoke_age,(CASE drink WHEN 0 THEN '无' WHEN 1 THEN '有' WHEN 2 THEN '偶尔' WHEN 3 THEN '已戒' END) AS drink,drink_type,drink_capacity,(CASE sleep_quality WHEN 0 THEN '良好' WHEN 1 THEN '一般' WHEN 2 THEN '入睡困难' WHEN 3 THEN '早醒' WHEN 4 THEN '易梦' WHEN 5 THEN '多梦' END) AS sleep_quality FROM ".$GLOBALS['ecs']->table('lifestyle')
    //    ." WHERE user_id=$user_id";                           //生活习惯

    //$lifestyle = $GLOBALS['db']->getRow($sql_select);

    //$healthy_file = array('baseInfo'=>$base_info,'weight_condition'=>$weight_condition,'work_condition'=>$work_condition,'lifestyle'=>$lifestyle,'date'=>date('Y-m-d'),'recently_test'=>$recently_test);

    return $healthy_file;
}

/**
 * 病例
 */
function get_before_case()
{
    $sql_select = 'SELECT s.sickness_id,s.disease,c.class_id FROM '.$GLOBALS['ecs']->table('sickness').
        ' AS s LEFT JOIN '.$GLOBALS['ecs']->table('sick_class').' AS c ON s.class=c.class_id WHERE s.availble=1';
    $before_case = $GLOBALS['db']->getAll($sql_select);
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('sick_class');
    $case_list = $GLOBALS['db']->getAll($sql_select);
    $result = array('before_case'=>$before_case,'case_list'=>$case_list);
    return $result;
}

/**
 * 增值服务列表
 */
function forecast_list ()
{
    $now_date    = strtotime(date('Y-m-d 00:00', time()));
    $now_time    = time();
    $future_days = $now_date +24*3600*3;

    $filter['admin_id'] = empty($_REQUEST['admin_id']) ? 0 : intval($_REQUEST['admin_id']);

    $sql_select = 'SELECT g.goods_name,g.goods_number,g.taking_time,i.receive_time+g.taking_time over_time,'.
        'i.order_id,i.mobile,i.tel,i.receive_time,u.service_time,u.sex,i.user_id,i.consignee,u.admin_name FROM '
        .$GLOBALS['ecs']->table('order_info').' i,'.$GLOBALS['ecs']->table('users').' u,'.
        $GLOBALS['ecs']->table('order_goods').' g WHERE i.user_id=u.user_id AND i.order_id=g.order_id AND '.
        "i.receive_time+g.taking_time BETWEEN $now_date AND $future_days AND u.service_time<$now_time";

    $sql_where = '';
    if(admin_priv('all','',false)){
    }
    else{
        $sql_where .= " AND u.admin_id={$_SESSION['admin_id']}";
    }

    if($filter['admin_id'] && admin_priv('forecast_view','',false)){
        $sql_where .= " AND u.admin_id={$filter['admin_id']}";
    }

    $forecast = $GLOBALS['db']->getAll($sql_select.$sql_where.' GROUP BY g.order_id ORDER BY over_time ASC');
    if (empty($forecast)){
        return false;
    }

    foreach ($forecast as &$val){
        $val['receive_time'] = date('Y-m-d', $val['receive_time']);
        $val['over_time'] = date('Y-m-d', $val['over_time']);
    }

    $arr = array (
        'forecast_list' => $forecast,
        'filter'        => $filter,
        'page_count'    => $filter['page_count'],
        'record_count'  => $filter['record_count'],
        'page_size'     => $filter['page_size'],
        'page'          => $filter['page'],
        'page_set'      => $page_set,
        'condition'     => $condition,
        'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'           => $filter['page']*$filter['page_size'],
    );

    return $arr;
}

/**
 * 顾客自定义分类
 */
function user_cat_list ($available = false, $admin_id = '')
{
    if (empty($admin_id)) {
        $admin_id = $_SESSION['admin_id'];
    }

    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('user_cat')." WHERE admin_id=$admin_id";
    if ($available) {
        $sql_select .= ' AND available=1';
    }
    $sql_select .= ' ORDER BY sort DESC';

    $cat_list = $GLOBALS['db']->getAll($sql_select);
    return $cat_list;
}

/**
 * 顾客类型
 */
function list_customer_type ($where='')
{
    $sql_select = 'SELECT type_id id, type_name name FROM '.$GLOBALS['ecs']->table('customer_type').
        " WHERE $where available>0 ORDER BY sort ASC";
    $type_list = $GLOBALS['db']->getAll($sql_select);

    return $type_list;
}

/*
 * 积分日志
 * */
function get_integral_log($user_id)
{
    $sql_select = 'SELECT ui.*,u.user_name,r.rank_name,a.user_name as admin_name,i.integral_title,(ui.pre_points+ui.exchange_points) as cur_integral,o.goods_amount FROM '.$GLOBALS['ecs']->table('user_integral').
        ' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('users').
        ' AS u ON ui.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS a ON u.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('integral').
        ' AS i ON ui.integral_id=i.integral_id LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
        ' AS o ON ui.source_id=o.order_id LEFT JOIN '.$GLOBALS['ecs']->table('user_rank').
        " AS r ON u.user_rank=r.rank_id WHERE u.user_id=$user_id ORDER BY confirm_time,receive_time DESC";

    $result = $GLOBALS['db']->getAll($sql_select);
    foreach($result as &$val)
    {
        $val['receive_time'] = date('Y-m-d H:i',$val['receive_time']);
        $val['validity'] = date('Y-m-d H:i',$val['validity']);
        $val['confirm_time']   = date('Y-m-d',$val['confirm_time']);
    }

    return $result;
}
/**
 * 顾客来源
 */
function list_from_where()
{
    $now = time();
    $sql_select = 'SELECT `from` name,from_id id FROM '.$GLOBALS['ecs']->table('from_where').
        " WHERE available=1 AND enddate>=$now OR enddate=0 ORDER BY sort ASC";
    $list = $GLOBALS['db']->getAll($sql_select);

    return $list;
}

//重组会员资金变化记录
function lange_account(&$account_log)
{
    foreach($account_log as &$rows)
    {
        $rows['add_time']         = date('Y-m-d H:i:s',$rows['add_time']);
        $rows['change_time']      = date('Y-m-d H:i:s',$rows['add_time']);
        $rows['admin_note']       = nl2br(htmlspecialchars($rows['admin_note']));
        $rows['short_admin_note'] = ($rows['admin_note'] > '') ? sub_str($rows['admin_note'], 30) : 'N/A';
        $rows['user_note']        = nl2br(htmlspecialchars($rows['user_note']));
        $rows['short_user_note']  = ($rows['user_note'] > '') ? sub_str($rows['user_note'], 30) : 'N/A';
        $rows['pay_status']       = ($rows['is_paid'] == 0) ? $GLOBALS['_LANG']['un_confirm'] : $GLOBALS['_LANG']['is_confirm'];
        $rows['amount']           = price_format(abs($rows['amount']), false);
        $rows['user_money']       = price_format(abs($rows['user_money']), false);

        /* 会员的操作类型： 冲值，提现 */
        if ($rows['process_type'] == 0)
        {
            $rows['type'] = $GLOBALS['_LANG']['surplus_type_0'];
        }
        else
        {
            $rows['type'] = $GLOBALS['_LANG']['surplus_type_1'];
        }
    }
}

/**
 * 顾客购买力分级列表
 */
function user_buy_list()
{
    $request = addslashes_deep($_REQUEST);

    // 默认查询条件
    $filter['amount'] = empty($_REQUEST['amount']) ? 500 : floatval($_REQUEST['amount']);

    // 自定义查询条件
    $filter['user_name']    = empty($request['user_name'])    ? '' : trim($request['user_name']);
    $filter['sex']          = empty($request['sex'])          ? '' : intval($request['sex']);
    $filter['mobile_phone'] = empty($request['mobile_phone']) ? '' : $request['mobile_phone'];
    $filter['home_phone']   = empty($request['home_phone'])   ? '' : $request['home_phone'];
    $filter['admin_id']     = empty($request['admin_id'])     ? '' : intval($request['admin_id']);
    $filter['eff_id']       = empty($request['eff_id'])       ? '' : intval($request['eff_id']);
    $filter['type_id']      = empty($request['type_id'])      ? '' : intval($request['type_id']);
    $filter['from_where']   = empty($request['from_where'])   ? '' : intval($request['from_where']);

    // 查询条件链
    foreach ($filter as $key=>$val) {
        if (!empty($val)) {
            if ($key == 'type') {
                $condition .= "&$key=".urlencode($val);
                continue;
            }

            $condition .= "&$key=$val";
        }
    }

    $filter['page_size'] = empty($request['page_size']) ? 20 : intval($request['page_size']);
    $filter['page'] = empty($request['page']) ? 1 : intval($request['page']);

    $ex_where = ''; // 查询条件

    // 顾客姓名
    if ($filter['user_name']) {
        $ex_where .= " AND u.user_name LIKE '%{$filter['user_name']}%' ";
    }

    // 性别
    if ($filter['sex']) {
        $ex_where .= " AND u.sex={$filter['sex']} ";
    }

    // 手机号码
    if ($filter['mobile_phone']) {
        $ex_where .= " AND u.mobile_phone={$filter['mobile_phone']} ";
    }

    // 固话
    if ($filter['home_phone']) {
        $ex_where .= " AND u.home_phone={$filter['home_phone']} ";
    }

    // QQ
    if ($filter['qq']) {
        $ex_where .= " AND u.qq={$filter['qq']} ";
    }

    // 旺旺
    if ($filter['aliww']) {
        $ex_where .= " AND u.aliww='{$filter['aliww']}' ";
    }

    // 客服
    if (admin_priv('user_buy_list', '', false) && $filter['admin_id']) {
        $ex_where .= " AND u.admin_id={$filter['admin_id']} ";
    } elseif (!admin_priv('all', '', false)) {
        $ex_where .= " AND u.admin_id={$_SESSION['admin_id']} ";
    }

    // 功效
    if ($filter['eff_id']) {
        $ex_where .= " AND u.eff_id={$filter['eff_id']} ";
    }

    // 来源
    if ($filter['from_where']) {
        $ex_where .= " AND u.from_where={$filter['from_where']} ";
    }

    // 累计消费金额
    switch ($filter['amount']) {
    case 500  : $ex_where .= " AND p.final_amount<500 "; break;
    case 1000 : $ex_where .= " AND p.final_amount<1000 AND p.final_amount>=500"; break;
    case 1500 : $ex_where .= " AND p.final_amount<1500 AND p.final_amount>=1000"; break;
    case 5000 : $ex_where .= " AND p.final_amount<5000 AND p.final_amount>=1500"; break;
    case 5001 : $ex_where .= " AND p.final_amount>5000 "; break;
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('purchasing_power').
        " p ON u.user_id=p.user_id WHERE 1 $ex_where ";
    $record_count = $GLOBALS['db']->getOne($sql_select);

    $page = break_pages($record_count, $filter['page_size'], $filter['page']);

    $sql_select = 'SELECT u.user_id,u.user_name,u.sex,u.add_time,u.service_time,u.admin_name,u.remarks FROM '.
        $GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('purchasing_power').
        " p ON p.user_id=u.user_id WHERE 1 $ex_where LIMIT ".($filter['page'] -1)*$filter['page_size'].', '.$filter['page_size'];
    $user_list = $GLOBALS['db']->getAll($sql_select);

    foreach ($user_list as &$val) {
        $val['add_time']      = date('Y-m-d', $val['add_time']);
        $val['service_time']  = date('Y-m-d', $val['service_time']);
    }

    $arr = array(
        'user_list'    => $user_list,
        'filter'       => $filter,
        'page_count'   => $page['page_count'],
        'record_count' => $record_count,
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page'],
        'page_set'     => $page['page_set'],
        'condition'    => $condition,
        'start'        => $page['start'],
        'end'          => $page['end'],
    );

    return $arr;
}

/**
 * 营销方式
 */
function marketing_list()
{
    $sql_select = 'SELECT marketing_id, marketing_name FROM '.$GLOBALS['ecs']->table('marketing').
        ' WHERE available=1 ORDER BY marketing_order DESC';
    $marketing_list = $GLOBALS['db']->getAll($sql_select);

    return $marketing_list;
}

/**
 * 获取顾客的喜欢的服务方式
 */
function marketing_checked_list($uid, $fields)
{
    $sql_select = "SELECT m.$fields FROM ".$GLOBALS['ecs']->table('user_marketing').' u LEFT JOIN '.
        $GLOBALS['ecs']->table('marketing')." m ON u.marketing_id=m.marketing_id WHERE user_id=$uid";
    $checked_list = $GLOBALS['db']->getCol($sql_select);

    if ($fields == 'marketing_name') {
        $checked_list = implode(',', $checked_list);
    }

    return $checked_list;
}

/**
 * 添加新的优先联系方式
 */
function new_marketing($marketing_list, $user_id, $type='')
{
    if (empty($marketing_list)) {
        return false;
    }

    $marketing = array();
    foreach ($marketing_list as $val){
        if ($type == 'del') {
            $marketing[] =  $val;
        } else {
            $marketing[] =  $val.','.$user_id;
        }
    }

    if ($type == 'del') {
        $marketing = implode(',', $marketing);
        $sql = 'DELETE FROM '.$GLOBALS['ecs']->table('user_marketing').
            " WHERE user_id=$user_id AND marketing_id IN ($marketing)";
    } else {
        $marketing = implode('),(', $marketing);
        $sql = 'INSERT INTO'.$GLOBALS['ecs']->table('user_marketing')."(marketing_id, user_id)VALUES($marketing)";
    }

    return $GLOBALS['db']->query($sql);
}

/*获取朋友*/
function get_user_friends($user_id)
{
    $sql_temp = 'SELECT user_id,user_name,mobile_phone,home_phone,add_time FROM '
        .$GLOBALS['ecs']->table('users');

    $sql_select = $sql_temp." WHERE parent_id=$user_id";
    $user_friends = $GLOBALS['db']->getAll($sql_select);    //Ta推荐的人

    $sql_select = $sql_temp.' WHERE user_id IN '.'(SELECT parent_id FROM '
        .$GLOBALS['ecs']->table('users')." WHERE user_id=$user_id"
        .')';   //推荐Ta的人

    $first_friend = $GLOBALS['db']->getRow($sql_select);

    if($first_friend)
    {
        $first_friend['first'] = 1;
        array_unshift($user_friends,$first_friend);
    }

    /*人工手动添加的朋友*/
    $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('user_family_member').
        " WHERE family_id=$user_id AND grade_id=101 ORDER BY input_time ASC";

    $friend_form_admin_add_id_arr = $GLOBALS['db']->getCol($sql_select);
    $friend_form_admin_add_id_str = implode("','",$friend_form_admin_add_id_arr);

    $sql_select        = $sql_temp." WHERE user_id IN ('$friend_form_admin_add_id_str')";
    $friend_form_admin = $GLOBALS['db']->getAll($sql_select);

    if($friend_form_admin){
        $user_friends = array_merge($user_friends,$friend_form_admin);
    }

    //$friends_total = count($user_friends);
    //for($i = 0; $i < $friends_total; $i++)
    //{
    //    $sql_select = 'SELECT COUNT(*) FROM '
    //        .$GLOBALS['ecs']->table('weight_condition')." WHERE user_id={$user_friends[$i]['user_id']}";

    //    $user_friends[$i]['healthy_file'] = $GLOBALS['db']->getOne($sql_select);
    //    $user_friends[$i]['add_time'] = date('Y-m-d',$user_friends[$i]['add_time']);
    //}
    $user_friends = array('friends_total'=>$friends_total,'user_friends'=>$user_friends);

    return $user_friends;
}

/**
 * 顾客流失列表
 */
function outflow_list ($query_field) {
    $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);
    $filter['end_time']   = empty($_REQUEST['end_time'])   ? '' : date('Y-m-t 23:59:59');//trim($_REQUEST['end_time']);
    $filter['time_limit'] = empty($_REQUEST['time_limit']) ? '' : trim($_REQUEST['time_limit']);

    $filter['platform'] = empty($_REQUEST['platform']) ? 0 : intval($_REQUEST['platform']);
    $filter['admin_id'] = empty($_REQUEST['admin_id']) ? 0 : intval($_REQUEST['admin_id']);

    if (empty($filter['start_time']) || empty($filter['end_time'])) {
        $filter['start_time'] = date('Y-m-01 00:00:00');
        $filter['end_time']   = date('Y-m-t 00:00:00');
    }

    $condition = '';
    foreach ($filter as $key=>$val) {
        if (!empty($val)) {
            if ($key == 'type') {
                $condition .= "&$key=".urlencode($val);
                continue;
            }

            $condition .= "&$key=$val";
        }
    }

    $filter['page_size'] = empty($_REQUEST['page_size']) ? 20 : intval($_REQUEST['page_size']);
    $filter['page']      = empty($_REQUEST['page_no'])   ? 1  : intval($_REQUEST['page_no']);

    //$ex_where = " customer_type IN (2, 3, 4, 5, 11) AND u.$query_field NOT BETWEEN ".strtotime($filter['start_time']).
    //' AND '.strtotime($filter['end_time']).' AND u.add_time<'.strtotime($filter['start_time']);
    $ex_where = ' customer_type IN (2, 3, 4, 5, 11) AND '.
        strtotime($filter['end_time']).' AND u.add_time<'.strtotime($filter['start_time']);
    if (!empty($filter['time_limit'])) {
        $ex_where .= " AND u.$query_field<".strtotime($filter['time_limit']);
    }

    if (!empty($filter['platform'])) {
        // 按平台检索顾客
        $ex_where .= " AND u.role_id={$filter['platform']} ";
    }

    if (!empty($filter['admin_id'])) {
        // 按客服检索顾客
        $ex_where .= " AND u.admin_id={$filter['admin_id']} ";
    }

    // 统计顾客数量
    $sql_count = 'SELECT COUNT(DISTINCT u.user_id) FROM '.$GLOBALS['ecs']->table('users').' u WHERE ';
    $record_count = $GLOBALS['db']->getOne($sql_count.$ex_where);
    $page = break_pages($record_count, $filter['page_size'], $filter['page']);

    $sql_select="SELECT u.user_id,u.user_name,u.add_time,u.admin_name,CONCAT(u.home_phone,' ',u.mobile_phone)mobile_phone,u.$query_field FROM ".
        $GLOBALS['ecs']->table('users')." u WHERE $ex_where GROUP BY u.user_id ORDER BY u.$query_field DESC LIMIT ".
        ($filter['page'] -1)*$filter['page_size'].', '.$filter['page_size'];
    $user_list = $GLOBALS['db']->getAll($sql_select);

    // 格式化时间
    foreach ($user_list as &$val){
        $val[$query_field] = $val[$query_field] ? date('Y-m-d', $val[$query_field]) : '-';
        $val['add_time']   = date('Y-m-d', $val['add_time']);
    }

    $result = array(
        'outflow_list' => $user_list,
        'filter'       => $filter,
        'page_count'   => $page['page_count'],
        'record_count' => $record_count,
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page'],
        'page_set'     => $page['page_set'],
        'condition'    => $condition,
        'start'        => $page['start'],
        'end'          => $page['end'],
    );

    return $result;
}


function calc_time ($tag) {
    $curr_month = date('m'); // 当前月份
    $curr_year  = date('Y'); // 当前年份

    $month = $curr_month - $tag;

    if ($month < 0) {
        $year_offset = floor($month/12);
        $year  = $curr_year + $year_offset;
        $month = $month +12*abs($year_offset);
    } elseif ($month == 0) {
        if ($curr_month == 1 && $tag == 1) {
            $year = $curr_year -1;
            $month = 12;
        } else {
            $year  = $curr_year;
            $month = $tag;
        }
    } else {
        $year = date('Y');
    }

    $month = strlen($month) < 2 ? '0'.$month : $month;

    return array('month' => $month, 'year' => $year);
}

/**
 * 转介绍
 */
function referrals_list () {
    $filter['admin_id']   = empty($_REQUEST['admin_id'])   ? 0  : intval($_REQUEST['admin_id']);  // 客服
    $filter['start_time'] = empty($_REQUEST['start_time']) ? 0  : trim($_REQUEST['start_time']);  // 开始时间
    $filter['end_time']   = empty($_REQUEST['end_time'])   ? 0  : trim($_REQUEST['end_time']);    // 结束时间
    $filter['role_id']    = empty($_REQUEST['role_id'])    ? 0  : intval($_REQUEST['role_id']);   // 部门
    $filter['page_size']  = empty($_REQUEST['page_size'])  ? 15 : intval($_REQUEST['page_size']); // 每页显示条数

    // 查询权限
    if (admin_priv('referrals_list_all', '', false)) {
        // 管理员权限
    } elseif (admin_priv('referrals_list_part', '', false)) {
        // 部门权限
        $filter['role_id'] = $_SESSION['role_id'];
    } elseif (admin_priv('referrals_list_group', '', false)) {
        // 小组权限
    } else {
        // 个人权限
        $filter['admin_id'] = $_SESSION['admin_id'];
    }

    // 查询条件
    $condition = array();
    foreach ($filter as $key=>$val){
        $condition[] = $key.'='.$val;
    }
    unset($key,$val);

    $filter['page_no'] = intval($_REQUEST['page_no']) <= 0 ? 1 : intval($_REQUEST['page_no']); // 目标页

    $ex_where = ' WHERE u.parent_id>0 ';
    if ($filter['admin_id']) {
        $ex_where .= " AND u.admin_id={$filter['admin_id']}";
    }

    if ($filter['start_time'] && $filter['end_time']) {
        $ex_where .= ' AND u.add_time BETWEEN '.strtotime($filter['start_time']).' AND '.strtotime($filter['end_time']);
    }

    if ($filter['role_id']) {
        $ex_where .= " AND u.role_id={$filter['role_id']} ";
    }

    // 统计转介绍的顾客数量
    $sql_count = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users')." u $ex_where";
    $record_count = $GLOBALS['db']->getOne($sql_count);

    $page = break_pages($record_count, $filter['page_size'], $filter['page_no']);

    $sql_select = 'SELECT u.user_id,u.user_name,u.sex,u.mobile_phone,u.home_phone,u.add_time,u.admin_name,u.remarks,p.user_name pnt_name FROM '.
        $GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('users')." p ON p.user_id=u.parent_id $ex_where LIMIT ".
        ($filter['page_no']-1)*$filter['page_size'].','.$filter['page_no']*$filter['page_size'];
    $result = $GLOBALS['db']->getAll($sql_select);

    foreach ($result as &$val){
        $val['add_time'] = date('Y-m-d', $val['add_time']);
    }

    $arr = array(
        'user_list'    => $result,
        'filter'       => $filter,
        'page_count'   => $page['page_count'],
        'record_count' => $record_count,
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page_no'],
        'page_set'     => $page['page_set'],
        'condition'    => $condition,
        'start'        => $page['start'],
        'end'          => $page['end'],
    );

    return $arr;
}

/**
 * 顾客联系方式列表
 */
function get_contact_list($user_id)
{
    $sql_select = 'SELECT contact_id,contact_name,contact_value,is_default,add_time FROM '.
        $GLOBALS['ecs']->table('user_contact')." WHERE user_id=$user_id AND is_del=0";
    $result = $GLOBALS['db']->getAll($sql_select);

    $contact_list = array ();
    foreach ($result as $val){
        if ($val['add_time']) {
            $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
        }
        if ($_SESSION['role_id']!=13 || !in_array($_SESSION['admin_id'],array(4,497,467,554,330,277))) {
            if (in_array($val['contact_name'],array('tel','mobile'))) {
                $val['contact_value'] = hideContact($val['contact_value']);
            }
        }
        $contact_list[$val['contact_name']][] = $val;
    }

    return $contact_list;
}

/**
 * 顾客地址列表
 */
function get_addr_list ($user_id)
{
    $sql_select = 'SELECT a.addr_id,a.prov,a.city,a.dist,CONCAT(p.region_name,c.region_name,d.region_name,a.addr) addr FROM '.
        $GLOBALS['ecs']->table('user_addr').'a LEFT JOIN '.$GLOBALS['ecs']->table('region').' p ON a.prov=p.region_id LEFT JOIN'.
        $GLOBALS['ecs']->table('region').' c ON c.region_id=a.city LEFT JOIN '.$GLOBALS['ecs']->table('region').
        " d ON a.dist=d.region_id WHERE user_id=$user_id AND is_del=0";
    return $GLOBALS['db']->getAll($sql_select);
}

function get_rank_list_info(){
    $sql_select = 'SELECT rank_id,max_points,min_points FROM '.$GLOBALS['ecs']->table('user_rank');
    $result = $GLOBALS['db']->getAll($sql_select);
    return $result;
}

/*获取网络黑名单顾客*/
function get_network_blacklist($where = ''){
    $sql_select = "SELECT blacklist_user_id,user_name,mobile,address,identity_card,IF(u.type_id,t.type_name,'网络诈骗') AS type_name FROM ".$GLOBALS['ecs']->table('network_blacklist').
        ' u LEFT JOIN '.$GLOBALS['ecs']->table('blacklist_type').' t ON t.type_id=u.type_id '.$where;
    return $GLOBALS['db']->getAll($sql_select);
}

/*员工账号是否冻结*/
function is_freeze(){
    $sql_select = 'SELECT freeze FROM '.$GLOBALS['ecs']->table('admin_user').
        " WHERE user_id={$_SESSION['admin_id']}";
    $freeze = $GLOBALS['db']->getOne($sql_select);

    return $freeze;
}

/*黑名单顾客类型*/
function get_blacklist_type(){
    $sql_select = 'SELECT type_id,type_name FROM '.$GLOBALS['ecs']->table('blacklist_type');
    return $GLOBALS['db']->getAll($sql_select);
}

/**
 * 根据商品查询顾客
 */
function search_by_goods() {
    $filter['goods_id']   = intval($_REQUEST['goods_id']);
    $filter['role_id']    = intval($_REQUEST['role_id']);
    $filter['start_time'] = $_REQUEST['start_time']?strtotime($_REQUEST['start_time']):strtotime(date('Y-m-1'));
    $filter['end_time']   = $_REQUEST['end_time']?strtotime($_REQUEST['end_time']):strtotime(date('Y-m-d'));
    if (admin_priv('all','',false)) {
        $filter['admin_id']   = intval($_REQUEST['admin_id']);
    }else{
        $filter['admin_id']   = intval($_SESSION['admin_id']);
    }

    $condition = '';
    foreach ($filter as $key=>$val) {
        if (!empty($val)) {
            $condition .= "&$key=$val";
        }
    }
    $where = ' WHERE u.user_id=i.user_id AND i.order_id=g.order_id AND i.order_status IN (1,5) AND i.shipping_status<3 AND g.goods_id='
        .$filter['goods_id']." AND i.order_type NOT IN(1,3)";

    if ($filter['admin_id']) {
        $where .= ' AND i.admin_id='.$filter['admin_id'];
    }

    if ($filter['role_id']) {
        $where .= ' AND u.role_id='.$filter['role_id'];
    }

    if ($filter['end_time'] && $filter['start_time']) {
        $where .= " AND i.add_time BETWEEN {$filter['start_time']} AND {$filter['end_time']}";
    }

    $filter['page_size'] = empty($_REQUEST['page_size']) ? 20 : intval($_REQUEST['page_size']);
    $filter['page']      = empty($_REQUEST['page'])   ? 1  : intval($_REQUEST['page']);

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').' u,'.$GLOBALS['ecs']->table('order_info').' i,'.
        $GLOBALS['ecs']->table('order_goods')." g $where GROUP BY u.user_id";
    $record_count = $GLOBALS['db']->getOne($sql_select);

    $page = break_pages($record_count, $filter['page_size'], $filter['page']);

    // 查询产品相关顾客
    $sql_select = 'SELECT u.user_id,u.user_name,u.sex,u.member_cid,u.add_time,u.service_time,u.admin_name,u.assign_time,u.remarks FROM '.
        $GLOBALS['ecs']->table('users').' u,'.$GLOBALS['ecs']->table('order_info').' i,'.$GLOBALS['ecs']->table('order_goods').' g '.
        $where.' GROUP BY u.user_id LIMIT '.($filter['page'] -1)*$filter['page_size'].', '.$filter['page_size'];
    $result = $GLOBALS['db']->getAll($sql_select);

    foreach ($result as &$val){
        $val['add_time']     = date('Y-m-d', $val['add_time']);
        $val['service_time'] = date('Y-m-d', $val['service_time']);
        $val['assign_time']  = date('Y-m-d', $val['assign_time']);
    }

    return array(
        'user_list'    => $result,
        'filter'       => $filter,
        'page_count'   => $page['page_count'],
        'record_count' => $record_count,
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page'],
        'page_set'     => $page['page_set'],
        'condition'    => $condition,
        'start'        => $page['start'],
        'end'          => $page['end'],
    );
}

//get user's bmi
function get_user_bmi($user_id){
    $sql = 'SELECT weight,height,bmi FROM '.$GLOBALS['ecs']->table('examination_test')." WHERE user_id=$user_id GROUP BY user_id ORDER BY add_time DESC";
    $bmi = $GLOBALS['db']->getRow($sql);
    return $bmi;
}

//产品资料
function goods_introduce(&$order_list){
    $sql_select = 'SELECT id,good_sn,classid FROM cz_ecms_goods';
    $ecms_goods = $GLOBALS['db']->getAll($sql_select);
    foreach ($order_list as &$o) {
        foreach ($o['goods_list'] as $k=>&$g) {
            foreach ($ecms_goods as $eg) {
                if ($eg['good_sn'] == $g['goods_sn']) {
                    $g['classid'] = $eg['classid'];
                    $g['id'] = $eg['id'];
                    $g['knowlage_url'] = "http://192.168.1.217/zhishi/e/action/ShowInfo.php?classid={$eg['classid']}&id={$eg['id']}";
                }
            }
        }
    }
}

//流失顾客
function user_lose(){
    $filter['admin_id']     = intval($_REQUEST['admin_id']);
    $filter['role_id']      = intval($_REQUEST['role_id']);
    $filter['service_time'] = isset($_REQUEST['service_time'])?intval($_REQUEST['service_time']):1;
    $filter['order_time']   = isset($_REQUEST['order_time'])?intval($_REQUEST['order_time']):1;
    $filter['manner']       = empty($_REQUEST['manner'])? ' OR ': ' AND ';
    extract($filter);
    $now_time               = $_SERVER['REQUEST_TIME'];
    $manner = empty($_REQUEST['manner'])? ' OR ': ' AND ';

    $where = ' WHERE is_black=0 AND customer_type NOT IN(4,5,6) AND service_time>0 AND order_time>0 ';
    if ($admin_id) {
        $where .= " AND admin_id=$admin_id";
    }
    if ($role_id) {
        $where .= " AND role_id=$role_id";
    }
    $where .= $service_time<4? " AND TIMESTAMPDIFF(MONTH,FROM_UNIXTIME(service_time,'%Y-%m-%d'),FROM_UNIXTIME($now_time,'%Y-%m-%d'))=$service_time":" AND TIMESTAMPDIFF(MONTH,FROM_UNIXTIME(service_time,'%Y-%m-%d'),FROM_UNIXTIME('%Y-%m-%d',$now_time))>$service_time";

    $where .= $order_time<4?" $manner TIMESTAMPDIFF(MONTH,FROM_UNIXTIME(order_time,'%Y-%m-%d'),FROM_UNIXTIME($now_time,'%Y-%m-%d'))=$order_time":" $manner TIMESTAMPDIFF(MONTH,FROM_UNIXTIME(order_time,'%Y-%m-%d'),FROM_UNIXTIME('%Y-%m-%d',$now_time))>$order_time";

    $filter['page_size']  = empty($_REQUEST['page_size'])  ? 15 : intval($_REQUEST['page_size']);
    $condition = array();
    foreach ($filter as $key=>$val){
        $condition[] = $key.'='.$val;
    }
    unset($key,$val);
    $filter['page'] = intval($_REQUEST['page']) <= 0 ? 1 : intval($_REQUEST['page']);
    $sql_count = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').$where;
    $record_count = $GLOBALS['db']->getOne($sql_count);
    $page = break_pages($record_count, $filter['page_size'], $filter['page']);

    $sql_select = 'SELECT user_id,user_name,sex,admin_name,order_time,service_time FROM '.
        $GLOBALS['ecs']->table('users')." $where LIMIT ".
        ($filter['page']-1)*$filter['page_size'].','.$filter['page']*$filter['page_size'];
    $result = $GLOBALS['db']->getAll($sql_select);

    foreach ($result as &$val){
        $val['add_time']     = date('Y-m-d', $val['add_time']);
        $val['service_time'] = date('Y-m-d', $val['service_time']);
        $val['order_time']   = date('Y-m-d', $val['order_time']);
    }

    $arr = array(
        'user_list'    => $result,
        'filter'       => $filter,
        'page_count'   => $page['page_count'],
        'record_count' => $record_count,
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page'],
        'page_set'     => $page['page_set'],
        'condition'    => $condition,
        'start'        => $page['start'],
        'end'          => $page['end'],
    );
    return $arr;
}
