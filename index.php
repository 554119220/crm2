<?php
/**
 * ECSHOP 控制台首页
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.kjrs365.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: index.php 17217 2011-01-19 06:29:08Z liubo $
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . '/includes/lib_order.php');
include_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_index_info.php');
include(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_wav.php');

date_default_timezone_set('Asia/Shanghai');
/*------------------------------------------------------ */
//-- 框架
/*------------------------------------------------------ */
if ($_REQUEST['act'] == '')
{
    $smarty->assign('admin_id', $_SESSION['admin_id']);
    $smarty->assign('role_id', $_SESSION['role_id']);

    $nav = list_nav();
    $smarty->assign('nav_1st', $nav[0]);
    $smarty->assign('nav_2nd', $nav[1]);
    $smarty->assign('nav_3rd', $nav[2]);

    if (!admin_priv('everyone_sales', '',false)) {
        $role_id = $_SESSION['role_id'];
    } else {
        $role_id = OFFLINE_SALE;
    }
    $hello         = "<b>{$_SESSION['admin_name']}</b>";
    $smarty->assign('hello',$hello);
    /*电话销售或会员部 预约提醒*/
    if($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 9){
        $smarty->assign('appointments','appointments');
    }

    $authority_judge = authority_judge();
    //首页信息显示
    if (empty($authority_judge['ordinary_employee'])) {
        $stock_alarm   = get_index_alarm_stock();    //库存警报
        $stats_info    = get_nature_stats();         //销售情况
        $public_notice = public_notice();
        $pwd_info      = $stats_info['pwd_info'];
        $smarty->assign('pwd_info',$stats_info['pwd_info']);
        $smarty->assign('stats_info',$stats_info);         //销量排行
        $smarty->assign('public_notice',$public_notice);  //公告
        //$smarty->assign('commemoration_list',get_commemoration()); //顾客记念日
        $smarty->assign('sale_alarm_list',get_sale_alarm_list());  //销量警报
    }

    $date_status = intval($_REQUEST['date_status']);
    $smarty->assign('date_status',$date_status);

    if(admin_priv('storage_manage','',false)){
        $smarty->assign('storage_manage',true);
    }

    if($_REQUEST['from_sel']){
        $res = $smarty->fetch('nature_stats_index.htm');
        die($json->encode($res));
    }elseif($_REQUEST['publice_sel']){
        $res = $smarty->fetch('index_publice_notice');
        die($json->encode($res));
    } else{
        $index_role = company_rule($role_id);              //公司规章
        $smarty->assign('nature_stats_index',$smarty->fetch('nature_stats_index.htm'));
        $smarty->assign('index_role',$index_role);
        $smarty->assign('role_list',get_role_list());
        $smarty->assign('left',$smarty->fetch('company_rule.htm'));
        $smarty->assign('index_stock_alarm_div',$smarty->fetch('index_stock_alarm.htm'));
        $smarty->assign('index_public_notice',$smarty->fetch('index_public_notice.htm'));
        //$smarty->assign('commemoration',$smarty->fetch('commemoration.htm'));
        $smarty->assign('index_sale_alarm_div',$smarty->fetch('index_sale_alarm.htm'));
        $smarty->assign('spread_activity',index_spread_activity());
        $smarty->assign('spread_activity_div',$smarty->fetch('index_spread_activity.htm'));
    }

    //拥有epr权限管理员
    if (admin_priv('all-erp','',false) || admin_priv('all','',false)) {
        $smarty->assign('erp',true);
    }
    if (admin_priv('users_list','',false)) {
        $smarty->assign('saler',true);
    }
    $smarty->assign('ordinary',empty($authority_judge['ordinary_employee']));
    $smarty->assign('wav', getSingleWavInfo());
    $data = $smarty->assign('top', $smarty->fetch('top.htm'));
    $smarty->assign('main_info',$smarty->fetch('main_info.htm'));
    $smarty->display('index.htm');        
}
/*------------------------------------------------------ */
//-- 左边的框架
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'menu')
{
    include_once('includes/inc_menu.php');

    // 权限对照表
    include_once('includes/inc_priv.php');

    foreach ($modules AS $key => $value)
    {
        ksort($modules[$key]);
    }
    ksort($modules);

    foreach ($modules AS $key => $val)
    {
        $menus[$key]['label'] = $_LANG[$key];
        if (is_array($val))
        {
            foreach ($val AS $k => $v)
            {
                if ( isset($purview[$k]))
                {
                    if (is_array($purview[$k]))
                    {
                        $boole = false;
                        foreach ($purview[$k] as $action)
                        {
                            $boole = $boole || admin_priv($action, '', false);
                        }
                        if (!$boole)
                        {
                            continue;
                        }
                    }
                    else
                    {
                        if (! admin_priv($purview[$k], '', false))
                        {
                            continue;
                        }
                    }
                }
                if ($k == 'ucenter_setup' && $_CFG['integrate_code'] != 'ucenter')
                {
                    continue;
                }
                $menus[$key]['children'][$k]['label']  = $_LANG[$k];
                $menus[$key]['children'][$k]['action'] = $v;
            }
        }
        else
        {
            $menus[$key]['action'] = $val;
        }

        // 如果children的子元素长度为0则删除该组
        if(empty($menus[$key]['children']))
        {
            unset($menus[$key]);
        }
    }

    $smarty->assign('menus',     $menus);
    $smarty->assign('no_help',   $_LANG['no_help']);
    $smarty->assign('help_lang', $_CFG['lang']);
    $smarty->assign('charset', EC_CHARSET);
    $smarty->assign('admin_id', $_SESSION['admin_id']);
    $smarty->display('menu.htm');
}


/*------------------------------------------------------ */
//-- 清除缓存
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'clear_cache')
{
    clear_all_files();

    sys_msg($_LANG['caches_cleared']);
}


/*------------------------------------------------------ */
//-- 主窗口，起始页
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'main')
{
    //开店向导第一步
    if(isset($_SESSION['shop_guide']) && $_SESSION['shop_guide'] === true)
    {
        unset($_SESSION['shop_guide']);//销毁session

        ecs_header("Location: ./index.php?act=first\n");

        exit();
    }

    $gd = gd_version();

    /* 检查文件目录属性 */
    $warning = array();

    if ($_CFG['shop_closed'])
    {
        $warning[] = $_LANG['shop_closed_tips'];
    }

    if (file_exists('../install'))
    {
        $warning[] = $_LANG['remove_install'];
    }

    if (file_exists('../upgrade'))
    {
        $warning[] = $_LANG['remove_upgrade'];
    }

    if (file_exists('../demo'))
    {
        $warning[] = $_LANG['remove_demo'];
    }

    $open_basedir = ini_get('open_basedir');
    if (!empty($open_basedir))
    {
        /* 如果 open_basedir 不为空，则检查是否包含了 upload_tmp_dir  */
        $open_basedir = str_replace(array("\\", "\\\\"), array("/", "/"), $open_basedir);
        $upload_tmp_dir = ini_get('upload_tmp_dir');

        if (empty($upload_tmp_dir))
        {
            if (stristr(PHP_OS, 'win'))
            {
                $upload_tmp_dir = getenv('TEMP') ? getenv('TEMP') : getenv('TMP');
                $upload_tmp_dir = str_replace(array("\\", "\\\\"), array("/", "/"), $upload_tmp_dir);
            }
            else
            {
                $upload_tmp_dir = getenv('TMPDIR') === false ? '/tmp' : getenv('TMPDIR');
            }
        }

        if (!stristr($open_basedir, $upload_tmp_dir))
        {
            $warning[] = sprintf($_LANG['temp_dir_cannt_read'], $upload_tmp_dir);
        }
    }

    $result = file_mode_info('../cert');
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'cert', $_LANG['cert_cannt_write']);
    }

    $result = file_mode_info('../' . DATA_DIR);
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'data', $_LANG['data_cannt_write']);
    }
    else
    {
        $result = file_mode_info('../' . DATA_DIR . '/afficheimg');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/afficheimg', $_LANG['afficheimg_cannt_write']);
        }

        $result = file_mode_info('../' . DATA_DIR . '/brandlogo');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/brandlogo', $_LANG['brandlogo_cannt_write']);
        }

        $result = file_mode_info('../' . DATA_DIR . '/cardimg');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/cardimg', $_LANG['cardimg_cannt_write']);
        }

        $result = file_mode_info('../' . DATA_DIR . '/feedbackimg');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/feedbackimg', $_LANG['feedbackimg_cannt_write']);
        }

        $result = file_mode_info('../' . DATA_DIR . '/packimg');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/packimg', $_LANG['packimg_cannt_write']);
        }
    }

    $result = file_mode_info('../images');
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'images', $_LANG['images_cannt_write']);
    }
    else
    {
        $result = file_mode_info('../' . IMAGE_DIR . '/upload');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], IMAGE_DIR . '/upload', $_LANG['imagesupload_cannt_write']);
        }
    }

    $result = file_mode_info('../temp');
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'images', $_LANG['tpl_cannt_write']);
    }

    $result = file_mode_info('../temp/backup');
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'images', $_LANG['tpl_backup_cannt_write']);
    }

    if (!is_writeable('../' . DATA_DIR . '/order_print.html'))
    {
        $warning[] = $_LANG['order_print_canntwrite'];
    }
    clearstatcache();

    $smarty->assign('warning_arr', $warning);


    /* 管理员留言信息 */
    $sql = 'SELECT message_id, sender_id, receiver_id, sent_time, readed, deleted, title, message, user_name ' .
        'FROM ' . $ecs->table('admin_message') . ' AS a, ' . $ecs->table('admin_user') . ' AS b ' .
        "WHERE a.sender_id = b.user_id AND a.receiver_id = '$_SESSION[admin_id]' AND ".
        "a.readed = 0 AND deleted = 0 ORDER BY a.sent_time DESC";
    $admin_msg = $db->GetAll($sql);

    $smarty->assign('admin_msg', $admin_msg);

    /* 统计当月销量 */

    /* 计算时间 */
    $today     = strtotime('today') +36000;     // 今天的时间
    $yesterday = strtotime('yesterday') +36000;    // 昨天的时间
    $last7     = strtotime('last '.date('D')) +36000; // 最近7天的时间
    $month     = strtotime(date('Y-m-01')) -50400;    // 当月订单

    $sql = 'SELECT admin_id, final_amount, add_time, add_admin_id FROM '.$ecs->table('order_info').' WHERE pay_status=2 AND add_time>'.$month;
    $order_list = $db->getAll($sql);

    $sql = 'SELECT user_id, user_name, role_id FROM '.$ecs->table('admin_user').' WHERE role_id IN (1,2,6,7) AND transfer>0';
    $admin_list = $db->getAll($sql);

    $cycle = array('1_today', '2_yesterday', '3_week', '4_month');
    foreach ($admin_list as $val)
    {
        foreach ($cycle as $v)
        {
            $total_per[$val['user_name']][$v]['amount']     = 0; //个人订单金额
            $total_per[$val['user_name']][$v]['num']        = 0; //个人订单数量
        }

        $admin_list_name[$val['user_id']] = $val['user_name'];

        switch ($val['role_id'])
        {
        case 2 : $admin_list_k[$val['user_id']] = '官网';
        break;
        case 1 : $admin_list_k[$val['user_id']] = '电话';
        break;
        case 6 : $admin_list_k[$val['user_id']] = '天猫';
        break;
        case 7 : $admin_list_k[$val['user_id']] = 'QQ';
        break;
        }
    }

    foreach ($admin_list_k as $val)
    {
        foreach ($cycle as $v)
        {
            $total[$val][$v]['amount']  = 0;  // 团队订单金额
            $total[$val][$v]['num']     = 0;  // 团队订单数量
            $total_team[$v]['amount']   = 0;  // 所有团队订单总金额
            $total_team[$v]['num']      = 0;  // 所有团队订单总数量
            $total_person[$v]['amount'] = 0;  // 所有个人订单总金额
            $total_person[$v]['num']    = 0;  // 所有个人订单总数量
        }
    }

    foreach ($order_list as $key=>$val)
    {
        /* 获取当月每个客服的订单统计信息 */
        ++$total_per[$admin_list_name[$val['admin_id']]]['4_month']['num'];
        $total_per[$admin_list_name[$val['admin_id']]]['4_month']['amount'] += $val['final_amount'];

        /* 获取当月每个团队的订单统计信息 */
        ++$total[$admin_list_k[$val['add_admin_id']]]['4_month']['num'];
        $total[$admin_list_k[$val['add_admin_id']]]['4_month']['amount'] += $val['final_amount'];

        /* 计算每个团队平均每笔订单的金额 */
        $total[$admin_list_k[$val['add_admin_id']]]['4_month']['average'] = number_format(round($total[$admin_list_k[$val['add_admin_id']]]['4_month']['amount']/$total[$admin_list_k[$val['add_admin_id']]]['4_month']['num']), 0, '.', ',');

        /* 计算每个团队的预计销量 */
        $total[$admin_list_k[$val['add_admin_id']]]['4_month']['forecast'] = number_format(round($total[$admin_list_k[$val['add_admin_id']]]['4_month']['amount']/date('j')*date('t')/10000, 1), 1, '.', ',');

        /* 获取当月所有销售的订单统计信息 */
        ++$total_team['4_month']['num'];
        $total_team['4_month']['amount'] += $val['final_amount'];

        /* 计算当月平均每笔订单的销量 */
        $total_team['4_month']['num'] && $total_team['4_month']['average'] = number_format(round($total_team['4_month']['amount']/$total_team['4_month']['num']), 0, '.', ',');

        /* 预计当月所有团队的总销量 */
        $total_team['4_month']['forecast'] = number_format(round($total_team['4_month']['amount']/date('j')*date('t')/10000, 1), 1, '.', ',');


        /* 获取所有客服最近7天的订单统计信息 */
        ++$total_person['4_month']['num'];
        $total_person['4_month']['amount'] += $val['final_amount'];

        if ($val['add_time'] >= $last7)
        {
            /* 获取最近7天所有销售的订单统计信息 */
            ++$total_team['3_week']['num'];
            $total_team['3_week']['amount'] += $val['final_amount'];

            /* 获取所有客服最近7天的订单统计信息 */
            ++$total_person['3_week']['num'];
            $total_person['3_week']['amount'] += $val['final_amount'];

            ++$total_per[$admin_list_name[$val['admin_id']]]['3_week']['num'];
            $total_per[$admin_list_name[$val['admin_id']]]['3_week']['amount'] += $val['final_amount'];

            ++$total[$admin_list_k[$val['add_admin_id']]]['3_week']['num'];
            $total[$admin_list_k[$val['add_admin_id']]]['3_week']['amount'] += $val['final_amount'];
        }

        if($val['add_time'] > ($yesterday -24*3600) && $val['add_time'] <= $yesterday)
        {
            /* 获取当月所有销售的订单统计信息 */
            ++$total_team['2_yesterday']['num'];
            $total_team['2_yesterday']['amount'] += $val['final_amount'];

            /* 获取每个客服昨天的订单统计信息 */
            ++$total_per[$admin_list_name[$val['admin_id']]]['2_yesterday']['num'];
            $total_per[$admin_list_name[$val['admin_id']]]['2_yesterday']['amount'] += $val['final_amount'];

            /* 获取所有客服昨天的订单统计信息 */
            ++$total_person['2_yesterday']['num'];
            $total_person['2_yesterday']['amount'] += $val['final_amount'];

            /* 获取每个团队昨天的订单统计信息 */
            ++$total[$admin_list_k[$val['add_admin_id']]]['2_yesterday']['num'];
            $total[$admin_list_k[$val['add_admin_id']]]['2_yesterday']['amount'] += $val['final_amount'];
        }
        elseif ($val['add_time'] < $today && $val['add_time'] >= $yesterday)
        {
            /* 获取当月所有销售的订单统计信息 */
            ++$total_team['1_today']['num'];
            $total_team['1_today']['amount'] += $val['final_amount'];

            /* 获取所有客服今天的订单统计信息 */
            ++$total_person['1_today']['num'];
            $total_person['1_today']['amount'] += $val['final_amount'];

            /* 获取每个客服当天的订单统计信息 */
            ++$total_per[$admin_list_name[$val['admin_id']]]['1_today']['num'];
            $total_per[$admin_list_name[$val['admin_id']]]['1_today']['amount'] += $val['final_amount'];

            /* 获取每个团队当天的订单统计信息 */
            ++$total[$admin_list_k[$val['add_admin_id']]]['1_today']['num'];
            $total[$admin_list_k[$val['add_admin_id']]]['1_today']['amount'] += $val['final_amount'];
        }
    }

    foreach ($total_per as $key=>$val)
    {
        is_array($val) && ksort($total_per[$key]);
    }

    $total     = sort($total);
    $total_per = sort($total_per);

    $smarty->assign('total',        $total);
    $smarty->assign('total_per',    $total_per);
    $smarty->assign('total_team',   $total_team);
    $smarty->assign('total_person', $total_person);
    $smarty->assign('stat', $smarty->fetch('order_stat.htm'));

    /* 取得支持货到付款和不支持货到付款的支付方式 */
    $ids = get_pay_ids();

    /* 已完成的订单 */
    $order['finished']     = $db->GetOne('SELECT COUNT(*) FROM ' . $ecs->table('order_info').
        " WHERE 1 " . order_query_sql('finished'));
    $status['finished']    = CS_FINISHED;

    /* 待发货的订单： */
    $order['await_ship']   = $db->GetOne('SELECT COUNT(*)'.
        ' FROM ' .$ecs->table('order_info') .
        " WHERE 1 " . order_query_sql('await_ship'));
    $status['await_ship']  = CS_AWAIT_SHIP;

    /* 待付款的订单： */
    $order['await_pay']    = $db->GetOne('SELECT COUNT(*)'.
        ' FROM ' .$ecs->table('order_info') .
        " WHERE 1 " . order_query_sql('await_pay'));
    $status['await_pay']   = CS_AWAIT_PAY;

    /* “未确认”的订单 */
    $order['unconfirmed']  = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('order_info').
        " WHERE 1 " . order_query_sql('unconfirmed'));
    $status['unconfirmed'] = OS_UNCONFIRMED;

    /* “部分发货”的订单 */
    $order['shipped_part']  = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('order_info').
        " WHERE  shipping_status=" .SS_SHIPPED_PART);
    $status['shipped_part'] = OS_SHIPPED_PART;

    //    $today_start = mktime(0,0,0,date('m'),date('d'),date('Y'));
    $order['stats']        = $db->getRow('SELECT COUNT(*) AS oCount, IFNULL(SUM(order_amount), 0) AS oAmount' .
        ' FROM ' .$ecs->table('order_info'));

    $smarty->assign('order', $order);
    $smarty->assign('status', $status);

    /* 商品信息 */
    $goods['total']   = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND is_alone_sale = 1 AND is_real = 1');
    $virtual_card['total'] = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND is_alone_sale = 1 AND is_real=0 AND extension_code=\'virtual_card\'');

    $goods['new']     = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND is_new = 1 AND is_real = 1');
    $virtual_card['new']     = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND is_new = 1 AND is_real=0 AND extension_code=\'virtual_card\'');

    $goods['best']    = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND is_best = 1 AND is_real = 1');
    $virtual_card['best']    = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND is_best = 1 AND is_real=0 AND extension_code=\'virtual_card\'');

    $goods['hot']     = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND is_hot = 1 AND is_real = 1');
    $virtual_card['hot']     = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND is_hot = 1 AND is_real=0 AND extension_code=\'virtual_card\'');

    $time             = gmtime();
    $goods['promote'] = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND promote_price>0' .
        " AND promote_start_date <= '$time' AND promote_end_date >= '$time' AND is_real = 1");
    $virtual_card['promote'] = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').
        ' WHERE is_delete = 0 AND promote_price>0' .
        " AND promote_start_date <= '$time' AND promote_end_date >= '$time' AND is_real=0 AND extension_code='virtual_card'");

    /* 缺货商品 */
    if ($_CFG['use_storage'])
    {
        $sql = 'SELECT COUNT(*) FROM ' .$ecs->table('goods'). ' WHERE is_delete = 0 AND goods_number <= warn_number AND is_real = 1';
        $goods['warn'] = $db->GetOne($sql);
        $sql = 'SELECT COUNT(*) FROM ' .$ecs->table('goods'). ' WHERE is_delete = 0 AND goods_number <= warn_number AND is_real=0 AND extension_code=\'virtual_card\'';
        $virtual_card['warn'] = $db->GetOne($sql);
    }
    else
    {
        $goods['warn'] = 0;
        $virtual_card['warn'] = 0;
    }
    $smarty->assign('goods', $goods);
    $smarty->assign('virtual_card', $virtual_card);

    /* 访问统计信息 */
    $today  = local_getdate();
    $sql    = 'SELECT COUNT(*) FROM ' .$ecs->table('stats').
        ' WHERE access_time > ' . (mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']) - date('Z'));

    $today_visit = $db->GetOne($sql);
    $smarty->assign('today_visit', $today_visit);

    $online_users = $sess->get_users_count();
    $smarty->assign('online_users', $online_users);

    /* 最近反馈 */
    $sql = "SELECT COUNT(f.msg_id) ".
        "FROM " . $ecs->table('feedback') . " AS f ".
        "LEFT JOIN " . $ecs->table('feedback') . " AS r ON r.parent_id=f.msg_id " .
        'WHERE f.parent_id=0 AND ISNULL(r.msg_id) ' ;
    $smarty->assign('feedback_number', $db->GetOne($sql));

    /* 未审核评论 */
    $smarty->assign('comment_number', $db->getOne('SELECT COUNT(*) FROM ' 
        . $ecs->table('comment') . ' WHERE status = 0 AND parent_id = 0'));

    $mysql_ver = $db->version();   // 获得 MySQL 版本

    /* 系统信息 */
    $sys_info['os']            = PHP_OS;
    $sys_info['ip']            = @$_SERVER['SERVER_ADDR'];
    $sys_info['web_server']    = $_SERVER['SERVER_SOFTWARE'];
    $sys_info['php_ver']       = PHP_VERSION;
    $sys_info['mysql_ver']     = $mysql_ver;
    $sys_info['zlib']          = function_exists('gzclose') ? $_LANG['yes']:$_LANG['no'];
    $sys_info['safe_mode']     = (boolean) ini_get('safe_mode') ?  $_LANG['yes']:$_LANG['no'];
    $sys_info['safe_mode_gid'] = (boolean) ini_get('safe_mode_gid') ? $_LANG['yes'] : $_LANG['no'];
    $sys_info['timezone']      = function_exists("date_default_timezone_get") ? date_default_timezone_get() : $_LANG['no_timezone'];
    $sys_info['socket']        = function_exists('fsockopen') ? $_LANG['yes'] : $_LANG['no'];

    if ($gd == 0)
    {
        $sys_info['gd'] = 'N/A';
    }
    else
    {
        if ($gd == 1)
        {
            $sys_info['gd'] = 'GD1';
        }
        else
        {
            $sys_info['gd'] = 'GD2';
        }

        $sys_info['gd'] .= ' (';

        /* 检查系统支持的图片类型 */
        if ($gd && (imagetypes() & IMG_JPG) > 0)
        {
            $sys_info['gd'] .= ' JPEG';
        }

        if ($gd && (imagetypes() & IMG_GIF) > 0)
        {
            $sys_info['gd'] .= ' GIF';
        }

        if ($gd && (imagetypes() & IMG_PNG) > 0)
        {
            $sys_info['gd'] .= ' PNG';
        }

        $sys_info['gd'] .= ')';
    }

    /* IP库版本 */
    $sys_info['ip_version'] = ecs_geoip('255.255.255.0');

    /* 允许上传的最大文件大小 */
    $sys_info['max_filesize'] = ini_get('upload_max_filesize');

    $smarty->assign('sys_info', $sys_info);

    /* 缺货登记 */
    $smarty->assign('booking_goods', $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('booking_goods') . ' WHERE is_dispose = 0'));

    /* 退款申请 */
    $smarty->assign('new_repay', $db->getOne('SELECT COUNT(*) FROM '.$ecs->table('user_account').' WHERE process_type='.SURPLUS_RETURN.' AND is_paid = 0 '));

    assign_query_info();
    $smarty->assign('ecs_version',  VERSION);
    $smarty->assign('ecs_release',  RELEASE);
    $smarty->assign('ecs_lang',     $_CFG['lang']);
    $smarty->assign('ecs_charset',  strtoupper(EC_CHARSET));
    $smarty->assign('install_date', local_date($_CFG['date_format'], $_CFG['install_date']));

    if (admin_priv('report_guest', '', false) === false)
    {
    }
    else
    {
        $total_customer =  $db->getOne('SELECT COUNT(*) FROM '.$ecs->table('users').' WHERE admin_id>0');
        $smarty->assign('total_customer', $total_customer);

        $time = time();// +8*3600;
        $day = strtotime(date('Y-m-d', $time));
        $month = strtotime(date('Y-m', $time).'-01');
        $year = strtotime(date('Y', $time).'-01-01');
        $week = $time - date('w', $time) *24*3600;

        $smarty->assign('today_customer', $db->getOne('SELECT COUNT(*) FROM '.$ecs->table('users').' WHERE add_time>='.$day));
        $smarty->assign('month_customer', $db->getOne('SELECT COUNT(*) FROM '.$ecs->table('users').' WHERE add_time>='.$month));
        $smarty->assign('year_customer', $db->getOne('SELECT COUNT(*) FROM '.$ecs->table('users').' WHERE admin_id>0 AND add_time>='.$year));
        $smarty->assign('week_customer', $db->getOne('SELECT COUNT(*) FROM '.$ecs->table('users').' WHERE add_time>='.$week));

        $sql = 'SELECT user_id,user_name FROM '.$ecs->table('admin_user').' WHERE transfer=1 AND status=1 ORDER BY role_id DESC';
        $admin = $db->getAll($sql);

        $today         = strtotime(date('Y-m-d', time())) -8*3600;
        $time_slot     = array();
        $time_slot[0]  = $today;
        $time_slot[1]  = $today -24*3600;
        $time_slot[3]  = $today -2*24*3600;
        $time_slot[7]  = $today -6*24*3600;
        $time_slot[30] = $today -29*24*3600;

        // END:逐人 逐日期 统计 客服名 下 顾客数量 //
        foreach($admin as $val)
        {
            foreach ($time_slot as $k=>$v)
            {
                $sql = 'SELECT COUNT(*) FROM '.$ecs->table('users').' WHERE admin_id='.$val['user_id'].' AND add_time>'.$v;
                $per_hour_total[$val['user_name']][$k] = $db->getOne($sql);
                if ($k == 1)
                {
                    $per_hour_total[$val['user_name']][$k] -= $per_hour_total[$val['user_name']][0];
                }
            }
            $sql = 'SELECT COUNT(*) FROM '.$ecs->table('users')
                .' WHERE admin_id='.$val['user_id']; 
            $per_hour_total[$val['user_name']]['all'] = $db->getOne($sql);
        }

        $per_slot = array();
        foreach ($time_slot as $k=>$v)
        {
            foreach ($admin as $val)
            {
                @$per_slot[$k] += $per_hour_total[$val['user_name']][$k];
            }
        }

        $smarty->assign('per_slot', $per_slot);

        $smarty->assign('per_hour_total', $per_hour_total);
        // END:逐人 逐日期 统计 客服名 下 顾客数量 //

        // START: 逐人 逐日 统计 客服名下  服务数量 //
        $time_slot[30] = $month;
        foreach($admin as $val)
        {
            foreach ($time_slot as $k=>$v)
            {
                $sql = 'SELECT COUNT(*) FROM '.$ecs->table('service').' WHERE  admin_id='.$val['user_id'].' AND service_time>='.$v;
                $per_s_day_total[$val['user_name']][$k] = $db->getOne($sql);
                if ($k == 1)
                {
                    $per_s_day_total[$val['user_name']][$k] -= $per_s_day_total[$val['user_name']][0];
                }
            }

            $sql = 'SELECT COUNT(DISTINCT user_id) FROM '.$ecs->table('service') ." WHERE admin_id=$val[user_id] AND service_time>=$month"; 
            $per_s_day_total[$val['user_name']]['all'] = $db->getOne($sql);

            $per_s_day_total[$val['user_name']]['rate'] = $per_hour_total[$val['user_name']]['all']?sprintf('%.2f%%', ($per_s_day_total[$val['user_name']]['all']*100/$per_hour_total[$val['user_name']]['all'])):'NG';
            $per_s_day_total[$val['user_name']]['all'] = $per_hour_total[$val['user_name']]['all'];
        }

        $per_service_slot = array();
        foreach ($time_slot as $k=>$v)
        {
            foreach ($admin as $val)
            {
                @$per_service_slot[$k] += $per_s_day_total[$val['user_name']][$k];
            }
        }

        $smarty->assign('per_service_slot', $per_service_slot);
        $smarty->assign('per_service_day_total', $per_s_day_total);

        $total_service = $db->getOne('SELECT COUNT(DISTINCT user_id) FROM '.$ecs->table('service')." WHERE admin_id>0 AND service_time>$month");
        $smarty->assign('total_service', $total_service);
        $smarty->assign('service_rate', sprintf('%.2f%%', ($total_service*100/$total_customer)));
        // END: 逐人 逐日 统计 客服名下  服务数量 //

    }    
    //START: 顾客生日统计 //
    require(dirname(__FILE__) . '/includes/cls_date.php');          
    //当天生日的顾客的数量
    $authority = '';
    if ($_SESSION['action_list'] != 'all')
    {
        $authority = " AND admin_id={$_SESSION['admin_id']}";
    }

    date_default_timezone_set('Asia/Shanghai');
    $arr = explode('-',date('Y-m-d'));
    $ld = new Lunar($arr[0],$arr[1],$arr[2]);
    $nl = $ld->display();
    $sql = 'SELECT COUNT(user_id) FROM ' .$GLOBALS['ecs']->table('users').
        " WHERE SUBSTR(birthday,6)='$nl' $authority";
    $today_count = $GLOBALS['db']->getOne($sql);
    $smarty->assign('today_count',$today_count);
    //未来四天生日的顾客的数量
    $now = '';
    for($i = 1;$i < 5;$i++){
        $time = strtotime("now") + (24*60*60)*$i;
        $date = date('Y-m-d',$time);
        $arr = explode('-',$date);
        $ld = new Lunar($arr[0],$arr[1],$arr[2]);
        $nl = $ld->display();
        $now = $now . ',\'' . $nl . '\'';
    }
    $now = ltrim($now,',');
    $sql = 'SELECT COUNT(user_id) FROM ' .$GLOBALS['ecs']->table('users').
        " WHERE (SUBSTR(birthday,6)) IN ($now) $authority";
    $count_5 = $GLOBALS['db']->getOne($sql);
    $smarty->assign('count_5',$count_5);
    //END: 顾客生日统计 //

    $smarty->display('start.htm');
}

elseif ($_REQUEST['act'] == 'main_api')
{
    require_once(ROOT_PATH . '/includes/lib_base.php');
    $data = read_static_cache('api_str');

    if($data === false || API_TIME < date('Y-m-d H:i:s',time()-43200))
    {
        include_once(ROOT_PATH . 'includes/cls_transport.php');
        $ecs_version = VERSION;
        $ecs_lang = $_CFG['lang'];
        $ecs_release = RELEASE;
        $php_ver = PHP_VERSION;
        $mysql_ver = $db->version();
        $order['stats'] = $db->getRow('SELECT COUNT(*) AS oCount, IFNULL(SUM(order_amount), 0) AS oAmount' . ' FROM ' .$ecs->table('order_info'));
        $ocount = $order['stats']['oCount'];
        $oamount = $order['stats']['oAmount'];
        $goods['total']   = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('goods').' WHERE is_delete = 0 AND is_alone_sale = 1 AND is_real = 1');
        $gcount = $goods['total'];
        $ecs_charset = strtoupper(EC_CHARSET);
        $ecs_user = $db->getOne('SELECT COUNT(*) FROM ' . $ecs->table('users'));
        $ecs_template = $db->getOne('SELECT value FROM ' . $ecs->table('shop_config') . ' WHERE code = \'template\'');
        $style = $db->getOne('SELECT value FROM ' . $ecs->table('shop_config') . ' WHERE code = \'stylename\'');
        if($style == '')
        {
            $style = '0';
        }
        $ecs_style = $style;
        $shop_url = urlencode($ecs->url());

        $patch_file = file_get_contents(ROOT_PATH.ADMIN_PATH."/patch_num");

        $apiget = "ver= $ecs_version &lang= $ecs_lang &release= $ecs_release &php_ver= $php_ver &mysql_ver= $mysql_ver &ocount= $ocount &oamount= $oamount &gcount= $gcount &charset= $ecs_charset &usecount= $ecs_user &template= $ecs_template &style= $ecs_style &url= $shop_url &patch= $patch_file ";

        $t = new transport;
        $api_comment = $t->request('http://api.ecshop.com/checkver.php', $apiget);
        $api_str = $api_comment["body"];
        echo $api_str;

        $f=ROOT_PATH . 'data/config.php'; 
        file_put_contents($f,str_replace("'API_TIME', '".API_TIME."'","'API_TIME', '".date('Y-m-d H:i:s',time())."'",file_get_contents($f)));

        write_static_cache('api_str', $api_str);
    }
    else 
    {
        echo $data;
    }

}


/*------------------------------------------------------ */
//-- 开店向导第一步
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'first')
{
    $smarty->assign('countries',    get_regions());
    $smarty->assign('provinces',    get_regions(1, 1));
    $smarty->assign('cities',    get_regions(2, 2));

    $sql = 'SELECT value from ' . $ecs->table('shop_config') . " WHERE code='shop_name'";
    $shop_name = $db->getOne($sql);

    $smarty->assign('shop_name', $shop_name);

    $sql = 'SELECT value from ' . $ecs->table('shop_config') . " WHERE code='shop_title'";
    $shop_title = $db->getOne($sql);

    $smarty->assign('shop_title', $shop_title);

    //获取配送方式
    //    $modules = read_modules('../includes/modules/shipping');
    $directory = ROOT_PATH . 'includes/modules/shipping';
    $dir         = @opendir($directory);
    $set_modules = true;
    $modules     = array();

    while (false !== ($file = @readdir($dir)))
    {
        if (preg_match("/^.*?\.php$/", $file))
        {
            if ($file != 'express.php')
            {
                include_once($directory. '/' .$file);
            }
        }
    }
    @closedir($dir);
    unset($set_modules);

    foreach ($modules AS $key => $value)
    {
        ksort($modules[$key]);
    }
    ksort($modules);

    for ($i = 0; $i < count($modules); $i++)
    {
        $lang_file = ROOT_PATH.'languages/' .$_CFG['lang']. '/shipping/' .$modules[$i]['code']. '.php';

        if (file_exists($lang_file))
        {
            include_once($lang_file);
        }

        $modules[$i]['name']    = $_LANG[$modules[$i]['code']];
        $modules[$i]['desc']    = $_LANG[$modules[$i]['desc']];
        $modules[$i]['insure_fee']  = empty($modules[$i]['insure'])? 0 : $modules[$i]['insure'];
        $modules[$i]['cod']     = $modules[$i]['cod'];
        $modules[$i]['install'] = 0;
    }
    $smarty->assign('modules', $modules);

    unset($modules);

    //获取支付方式
    $modules = read_modules('../includes/modules/payment');

    for ($i = 0; $i < count($modules); $i++)
    {
        $code = $modules[$i]['code'];
        $modules[$i]['name'] = $_LANG[$modules[$i]['code']];
        if (!isset($modules[$i]['pay_fee']))
        {
            $modules[$i]['pay_fee'] = 0;
        }
        $modules[$i]['desc'] = $_LANG[$modules[$i]['desc']];
    }
    //        $modules[$i]['install'] = '0';
    $smarty->assign('modules_payment', $modules);

    assign_query_info();

    $smarty->assign('ur_here', $_LANG['ur_config']);
    $smarty->display('setting_first.htm');
}

/*------------------------------------------------------ */
//-- 开店向导第二步
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'second')
{
    admin_priv('shop_config');

    $shop_name = empty($_POST['shop_name']) ? '' : $_POST['shop_name'] ;
    $shop_title = empty($_POST['shop_title']) ? '' : $_POST['shop_title'] ;
    $shop_country = empty($_POST['shop_country']) ? '' : intval($_POST['shop_country']);
    $shop_province = empty($_POST['shop_province']) ? '' : intval($_POST['shop_province']);
    $shop_city = empty($_POST['shop_city']) ? '' : intval($_POST['shop_city']);
    $shop_address = empty($_POST['shop_address']) ? '' : $_POST['shop_address'] ;
    $shipping = empty($_POST['shipping']) ? '' : $_POST['shipping'];
    $payment = empty($_POST['payment']) ? '' : $_POST['payment'];

    if(!empty($shop_name))
    {
        $sql = 'UPDATE ' . $ecs->table('shop_config') . " SET value = '$shop_name' WHERE code = 'shop_name'";
        $db->query($sql);
    }

    if(!empty($shop_title))
    {
        $sql = 'UPDATE ' . $ecs->table('shop_config') . " SET value = '$shop_title' WHERE code = 'shop_title'";
        $db->query($sql);
    }

    if(!empty($shop_address))
    {
        $sql = 'UPDATE ' . $ecs->table('shop_config') . " SET value = '$shop_address' WHERE code = 'shop_address'";
        $db->query($sql);
    }

    if(!empty($shop_country))
    {
        $sql = 'UPDATE ' . $ecs->table('shop_config') . "SET value = '$shop_country' WHERE code='shop_country'";
        $db->query($sql);
    }

    if(!empty($shop_province))
    {
        $sql = 'UPDATE ' . $ecs->table('shop_config') . "SET value = '$shop_province' WHERE code='shop_province'";
        $db->query($sql);
    }

    if(!empty($shop_city))
    {
        $sql = 'UPDATE ' . $ecs->table('shop_config') . "SET value = '$shop_city' WHERE code='shop_city'";
        $db->query($sql);
    }

    //设置配送方式
    if(!empty($shipping))
    {
        $shop_add = read_modules('../includes/modules/shipping');

        foreach ($shop_add as $val)
        {
            $mod_shop[] = $val['code'];
        }
        $mod_shop = implode(',',$mod_shop);

        $set_modules = true;
        if(strpos($mod_shop,$shipping) === false)
        {
            exit;   
        }
        else 
        {
            include_once(ROOT_PATH . 'includes/modules/shipping/' . $shipping . '.php');
        }
        $sql = "SELECT shipping_id FROM " .$ecs->table('shipping'). " WHERE shipping_code = '$shipping'";
        $shipping_id = $db->GetOne($sql);

        if($shipping_id <= 0)
        {
            $insure = empty($modules[0]['insure']) ? 0 : $modules[0]['insure'];
            $sql = "INSERT INTO " . $ecs->table('shipping') . " (" .
                "shipping_code, shipping_name, shipping_desc, insure, support_cod, enabled" .
                ") VALUES (" .
                "'" . addslashes($modules[0]['code']). "', '" . addslashes($_LANG[$modules[0]['code']]) . "', '" .
                addslashes($_LANG[$modules[0]['desc']]) . "', '$insure', '" . intval($modules[0]['cod']) . "', 1)";
            $db->query($sql);
            $shipping_id = $db->insert_Id();
        }

        //设置配送区域
        $area_name = empty($_POST['area_name']) ? '' : $_POST['area_name'];
        if(!empty($area_name))
        {
            $sql = "SELECT shipping_area_id FROM " .$ecs->table("shipping_area").
                " WHERE shipping_id='$shipping_id' AND shipping_area_name='$area_name'";
            $area_id = $db->getOne($sql);

            if($area_id <= 0)
            {
                $config = array();
                foreach ($modules[0]['configure'] AS $key => $val)
                {
                    $config[$key]['name']   = $val['name'];
                    $config[$key]['value']  = $val['value'];
                }

                $count = count($config);
                $config[$count]['name']     = 'free_money';
                $config[$count]['value']    = 0;

                /* 如果支持货到付款，则允许设置货到付款支付费用 */
                if ($modules[0]['cod'])
                {
                    $count++;
                    $config[$count]['name']     = 'pay_fee';
                    $config[$count]['value']    = make_semiangle(0);
                }

                $sql = "INSERT INTO " .$ecs->table('shipping_area').
                    " (shipping_area_name, shipping_id, configure) ".
                    "VALUES" . " ('$area_name', '$shipping_id', '" .serialize($config). "')";
                $db->query($sql);
                $area_id = $db->insert_Id();
            }

            $region_id = empty($_POST['shipping_country']) ? 1 : intval($_POST['shipping_country']);
            $region_id = empty($_POST['shipping_province']) ? $region_id : intval($_POST['shipping_province']);
            $region_id = empty($_POST['shipping_city']) ? $region_id : intval($_POST['shipping_city']);
            $region_id = empty($_POST['shipping_district']) ? $region_id : intval($_POST['shipping_district']);

            /* 添加选定的城市和地区 */
            $sql = "REPLACE INTO ".$ecs->table('area_region')." (shipping_area_id, region_id) VALUES ('$area_id', '$region_id')";
            $db->query($sql);
        }
    }

    unset($modules);

    if(!empty($payment))
    {
        /* 取相应插件信息 */
        $set_modules = true;
        include_once(ROOT_PATH.'includes/modules/payment/' . $payment . '.php');

        $pay_config = array();
        if (isset($_REQUEST['cfg_value']) && is_array($_REQUEST['cfg_value']))
        {
            for ($i = 0; $i < count($_POST['cfg_value']); $i++)
            {
                $pay_config[] = array('name'  => trim($_POST['cfg_name'][$i]),
                    'type'  => trim($_POST['cfg_type'][$i]),
                    'value' => trim($_POST['cfg_value'][$i])
                );
            }
        }

        $pay_config = serialize($pay_config);
        /* 安装，检查该支付方式是否曾经安装过 */
        $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$payment'";
        if ($db->GetOne($sql) > 0)
        {
            $sql = "UPDATE " . $ecs->table('payment') .
                " SET pay_config = '$pay_config'," .
                " enabled = '1' " .
                "WHERE pay_code = '$payment' LIMIT 1";
            $db->query($sql);
        }
        else
        {
            //            $modules = read_modules('../includes/modules/payment');
            $payment_info = array();
            $payment_info['name'] = $_LANG[$modules[0]['code']];
            $payment_info['pay_fee'] = empty($modules[0]['pay_fee']) ? 0 : $modules[0]['pay_fee'];
            $payment_info['desc'] = $_LANG[$modules[0]['desc']];

            $sql = "INSERT INTO " . $ecs->table('payment') . " (pay_code, pay_name, pay_desc, pay_config, is_cod, pay_fee, enabled, is_online)" .
                "VALUES ('$payment', '$payment_info[name]', '$payment_info[desc]', '$pay_config', '0', '$payment_info[pay_fee]', '1', '1')";
            $db->query($sql);
        }
    }

    clear_all_files();

    assign_query_info();

    $smarty->assign('ur_here', $_LANG['ur_add']);
    $smarty->display('setting_second.htm');
}

/*------------------------------------------------------ */
//-- 开店向导第三步
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'third')
{
    admin_priv('goods_manage');

    $good_name = empty($_POST['good_name']) ? '' : $_POST['good_name'];
    $good_number = empty($_POST['good_number']) ? '' : $_POST['good_number'];
    $good_category = empty($_POST['good_category']) ? '' : $_POST['good_category'];
    $good_brand = empty($_POST['good_brand']) ? '' : $_POST['good_brand'];
    $good_price = empty($_POST['good_price']) ? 0 : $_POST['good_price'];
    $good_name = empty($_POST['good_name']) ? '' : $_POST['good_name'];
    $is_best = empty($_POST['is_best']) ? 0 : 1;
    $is_new = empty($_POST['is_new']) ? 0 : 1;
    $is_hot = empty($_POST['is_hot']) ? 0 :1;
    $good_brief = empty($_POST['good_brief']) ? '' : $_POST['good_brief'];
    $market_price = $good_price * 1.2;

    if(!empty($good_category))
    {
        if (cat_exists($good_category, 0))
        {
            /* 同级别下不能有重复的分类名称 */
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['catname_exist'], 0, $link);
        }
    }

    if(!empty($good_brand))
    {
        if (brand_exists($good_brand))
        {
            /* 同级别下不能有重复的品牌名称 */
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['brand_name_exist'], 0, $link);
        }
    }

    $brand_id = 0;
    if(!empty($good_brand))
    {
        $sql = 'INSERT INTO ' . $ecs->table('brand') . " (brand_name, is_show)" .
            " values('" . $good_brand . "', '1')";
        $db->query($sql);

        $brand_id = $db->insert_Id();
    }

    if(!empty($good_category))
    {
        $sql = 'INSERT INTO ' . $ecs->table('category') . " (cat_name, parent_id, is_show)" .
            " values('" . $good_category . "', '0', '1')";
        $db->query($sql);

        $cat_id = $db->insert_Id();

        //货号
        require_once(ROOT_PATH . ADMIN_PATH . '/includes/lib_goods.php');
        $max_id     = $db->getOne("SELECT MAX(goods_id) + 1 FROM ".$ecs->table('goods'));
        $goods_sn   = generate_goods_sn($max_id);

        include_once(ROOT_PATH . 'includes/cls_image.php');
        $image = new cls_image($_CFG['bgcolor']);

        if(!empty($good_name))
        {
            /* 检查图片：如果有错误，检查尺寸是否超过最大值；否则，检查文件类型 */
            if (isset($_FILES['goods_img']['error'])) // php 4.2 版本才支持 error
            {
                // 最大上传文件大小
                $php_maxsize = ini_get('upload_max_filesize');
                $htm_maxsize = '2M';

                // 商品图片
                if ($_FILES['goods_img']['error'] == 0)
                {
                    if (!$image->check_img_type($_FILES['goods_img']['type']))
                    {
                        sys_msg($_LANG['invalid_goods_img'], 1, array(), false);
                    }
                }
                elseif ($_FILES['goods_img']['error'] == 1)
                {
                    sys_msg(sprintf($_LANG['goods_img_too_big'], $php_maxsize), 1, array(), false);
                }
                elseif ($_FILES['goods_img']['error'] == 2)
                {
                    sys_msg(sprintf($_LANG['goods_img_too_big'], $htm_maxsize), 1, array(), false);
                }
            }
            /* 4。1版本 */
            else
            {
                // 商品图片
                if ($_FILES['goods_img']['tmp_name'] != 'none')
                {
                    if (!$image->check_img_type($_FILES['goods_img']['type']))
                    {
                        sys_msg($_LANG['invalid_goods_img'], 1, array(), false);
                    }
                }


            }
            $goods_img        = '';  // 初始化商品图片
            $goods_thumb      = '';  // 初始化商品缩略图
            $original_img     = '';  // 初始化原始图片
            $old_original_img = '';  // 初始化原始图片旧图
            // 如果上传了商品图片，相应处理
            if ($_FILES['goods_img']['tmp_name'] != '' && $_FILES['goods_img']['tmp_name'] != 'none')
            {

                $original_img   = $image->upload_image($_FILES['goods_img']); // 原始图片
                if ($original_img === false)
                {
                    sys_msg($image->error_msg(), 1, array(), false);
                }
                $goods_img      = $original_img;   // 商品图片

                /* 复制一份相册图片 */
                $img        = $original_img;   // 相册图片
                $pos        = strpos(basename($img), '.');
                $newname    = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);
                if (!copy('../' . $img, '../' . $newname))
                {
                    sys_msg('fail to copy file: ' . realpath('../' . $img), 1, array(), false);
                }
                $img        = $newname;

                $gallery_img    = $img;
                $gallery_thumb  = $img;

                // 如果系统支持GD，缩放商品图片，且给商品图片和相册图片加水印
                if ($image->gd_version() > 0 && $image->check_img_function($_FILES['goods_img']['type']))
                {
                    // 如果设置大小不为0，缩放图片
                    if ($_CFG['image_width'] != 0 || $_CFG['image_height'] != 0)
                    {
                        $goods_img = $image->make_thumb('../'. $goods_img , $GLOBALS['_CFG']['image_width'],  $GLOBALS['_CFG']['image_height']);
                        if ($goods_img === false)
                        {
                            sys_msg($image->error_msg(), 1, array(), false);
                        }
                    }

                    $newname    = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);
                    if (!copy('../' . $img, '../' . $newname))
                    {
                        sys_msg('fail to copy file: ' . realpath('../' . $img), 1, array(), false);
                    }
                    $gallery_img        = $newname;

                    // 加水印
                    if (intval($_CFG['watermark_place']) > 0 && !empty($GLOBALS['_CFG']['watermark']))
                    {
                        if ($image->add_watermark('../'.$goods_img,'',$GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false)
                        {
                            sys_msg($image->error_msg(), 1, array(), false);
                        }

                        if ($image->add_watermark('../'. $gallery_img,'',$GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false)
                        {
                            sys_msg($image->error_msg(), 1, array(), false);
                        }
                    }

                    // 相册缩略图
                    if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0)
                    {
                        $gallery_thumb = $image->make_thumb('../' . $img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
                        if ($gallery_thumb === false)
                        {
                            sys_msg($image->error_msg(), 1, array(), false);
                        }
                    }
                }
                else
                {
                    /* 复制一份原图 */
                    $pos        = strpos(basename($img), '.');
                    $gallery_img = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);
                    if (!copy('../' . $img, '../' . $gallery_img))
                    {
                        sys_msg('fail to copy file: ' . realpath('../' . $img), 1, array(), false);
                    }
                    $gallery_thumb = '';
                }
            }
            // 未上传，如果自动选择生成，且上传了商品图片，生成所略图
            if (!empty($original_img))
            {
                // 如果设置缩略图大小不为0，生成缩略图
                if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0)
                {
                    $goods_thumb = $image->make_thumb('../' . $original_img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
                    if ($goods_thumb === false)
                    {
                        sys_msg($image->error_msg(), 1, array(), false);
                    }
                }
                else
                {
                    $goods_thumb = $original_img;
                }
            }


            $sql = 'INSERT INTO ' . $ecs->table('goods') . "(goods_name, goods_sn, goods_number, cat_id, brand_id, goods_brief, shop_price, market_price, goods_img, goods_thumb, original_img,add_time, last_update,
                is_best, is_new, is_hot)" .
                "VALUES('$good_name', '$goods_sn', '$good_number', '$cat_id', '$brand_id', '$good_brief', '$good_price'," .
                " '$market_price', '$goods_img', '$goods_thumb', '$original_img','" . gmtime() . "', '". gmtime() . "', '$is_best', '$is_new', '$is_hot')";

            $db->query($sql);
            $good_id = $db->insert_id();
            /* 如果有图片，把商品图片加入图片相册 */
            if (isset($img))
            {
                $sql = "INSERT INTO " . $ecs->table('goods_gallery') . " (goods_id, img_url, img_desc, thumb_url, img_original) " .
                    "VALUES ('$good_id', '$gallery_img', '', '$gallery_thumb', '$img')";
                $db->query($sql);
            }

        }
    }

    assign_query_info();
    //    $smarty->assign('ur_here', '开店向导－添加商品');
    $smarty->display('setting_third.htm');
}

/*------------------------------------------------------ */
//-- 关于 ECSHOP
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'about_us')
{
    assign_query_info();
    $smarty->display('about_us.htm');
}

/*------------------------------------------------------ */
//-- 拖动的帧
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'drag')
{
    $smarty->display('drag.htm');;
}

/*------------------------------------------------------ */
//-- 检查订单
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'check_order')
{
    if (empty($_SESSION['last_check']))
    {
        $_SESSION['last_check'] = gmtime();

        make_json_result('', '', array('new_orders' => 0, 'new_paid' => 0));
    }

    /* 新订单 */
    $sql = 'SELECT COUNT(*) FROM ' . $ecs->table('order_info').
        " WHERE add_time >= '$_SESSION[last_check]'";
    $arr['new_orders'] = $db->getOne($sql);

    /* 新付款的订单 */
    $sql = 'SELECT COUNT(*) FROM '.$ecs->table('order_info').
        ' WHERE pay_time >= ' . $_SESSION['last_check'];
    $arr['new_paid'] = $db->getOne($sql);

    $_SESSION['last_check'] = gmtime();

    if (!(is_numeric($arr['new_orders']) && is_numeric($arr['new_paid'])))
    {
        make_json_error($db->error());
    }
    else
    {
        make_json_result('', '', $arr);
    }
}

/*------------------------------------------------------ */
//-- Totolist操作
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'save_todolist')
{
    $content = json_str_iconv($_POST["content"]);
    $sql = "UPDATE" .$GLOBALS['ecs']->table('admin_user'). " SET todolist='" . $content . "' WHERE user_id = " . $_SESSION['admin_id'];
    $GLOBALS['db']->query($sql);
}

elseif ($_REQUEST['act'] == 'get_todolist')
{
    $sql = "SELECT todolist FROM ".$GLOBALS['ecs']->table('admin_user').
        " WHERE user_id=".$_SESSION['admin_id'];
    $content = $GLOBALS['db']->getOne($sql);
    echo $content;
}
// 邮件群发处理
elseif ($_REQUEST['act'] == 'send_mail')
{
    if ($_CFG['send_mail_on'] == 'off')
    {
        make_json_result('', $_LANG['send_mail_off'], 0);
        exit();
    }
    $sql = "SELECT * FROM " . $ecs->table('email_sendlist') . " ORDER BY pri DESC, last_send ASC LIMIT 1";
    $row = $db->getRow($sql);

    //发送列表为空
    if (empty($row['id']))
    {
        make_json_result('', $_LANG['mailsend_null'], 0);
    }

    //发送列表不为空，邮件地址为空
    if (!empty($row['id']) && empty($row['email']))
    {
        $sql = "DELETE FROM " . $ecs->table('email_sendlist') . " WHERE id = '$row[id]'";
        $db->query($sql);
        $count = $db->getOne("SELECT COUNT(*) FROM " . $ecs->table('email_sendlist'));
        make_json_result('', $_LANG['mailsend_skip'], array('count' => $count, 'goon' => 1));
    }

    //查询相关模板
    $sql = "SELECT * FROM " . $ecs->table('mail_templates') . " WHERE template_id = '$row[template_id]'";
    $rt = $db->getRow($sql);

    //如果是模板，则将已存入email_sendlist的内容作为邮件内容
    //否则即是杂质，将mail_templates调出的内容作为邮件内容
    if ($rt['type'] == 'template')
    {
        $rt['template_content'] = $row['email_content'];
    }

    if ($rt['template_id'] && $rt['template_content'])
    {
        if (send_mail('', $row['email'], $rt['template_subject'], $rt['template_content'], $rt['is_html']))
        {
            //发送成功

            //从列表中删除
            $sql = "DELETE FROM " . $ecs->table('email_sendlist') . " WHERE id = '$row[id]'";
            $db->query($sql);

            //剩余列表数
            $count = $db->getOne("SELECT COUNT(*) FROM " . $ecs->table('email_sendlist'));

            if($count > 0)
            {
                $msg = sprintf($_LANG['mailsend_ok'],$row['email'],$count);
            }
            else
            {
                $msg = sprintf($_LANG['mailsend_finished'],$row['email']);
            }
            make_json_result('', $msg, array('count' => $count));
        }
        else
        {
            //发送出错

            if ($row['error'] < 3)
            {
                $time = time();
                $sql = "UPDATE ".$ecs->table('email_sendlist').
                    " SET error = error + 1, pri = 0, last_send = '$time' WHERE id = '$row[id]'";
            }
            else
            {
                //将出错超次的纪录删除
                $sql = "DELETE FROM " . $ecs->table('email_sendlist') . " WHERE id = '$row[id]'";
            }
            $db->query($sql);

            $count = $db->getOne("SELECT COUNT(*) FROM " . $ecs->table('email_sendlist'));
            make_json_result('', sprintf($_LANG['mailsend_fail'],$row['email']), array('count' => $count));
        }
    }
    else
    {
        //无效的邮件队列
        $sql = "DELETE FROM " . $ecs->table('email_sendlist') . " WHERE id = '$row[id]'";
        $db->query($sql);
        $count = $db->getOne("SELECT COUNT(*) FROM " . $ecs->table('email_sendlist'));
        make_json_result('', sprintf($_LANG['mailsend_fail'],$row['email']), array('count' => $count));
    }
}

/*------------------------------------------------------ */
//-- license操作
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'license')
{
    $is_ajax = $_GET['is_ajax'];

    if (isset($is_ajax) && $is_ajax)
    {
        // license 检查
        include_once(ROOT_PATH . 'includes/cls_transport.php');
        include_once(ROOT_PATH . 'includes/cls_json.php');
        include_once(ROOT_PATH . 'includes/lib_main.php');
        include_once(ROOT_PATH . 'includes/lib_license.php');

        $license = license_check();
        switch ($license['flag'])
        {
        case 'login_succ':
            if (isset($license['request']['info']['service']['ecshop_b2c']['cert_auth']['auth_str']))
            {
                make_json_result(process_login_license($license['request']['info']['service']['ecshop_b2c']['cert_auth']));
            }
            else
            {
                make_json_error(0);
            }
            break;

        case 'login_fail':
        case 'login_ping_fail':
            make_json_error(0);
            break;

        case 'reg_succ':
            $_license = license_check();
            switch ($_license['flag'])
            {
            case 'login_succ':
                if (isset($_license['request']['info']['service']['ecshop_b2c']['cert_auth']['auth_str']) && $_license['request']['info']['service']['ecshop_b2c']['cert_auth']['auth_str'] != '')
                {
                    make_json_result(process_login_license($license['request']['info']['service']['ecshop_b2c']['cert_auth']));
                }
                else
                {
                    make_json_error(0);
                }
                break;

            case 'login_fail':
            case 'login_ping_fail':
                make_json_error(0);
                break;
            }
            break;

        case 'reg_fail':
        case 'reg_ping_fail':
            make_json_error(0);
            break;
        }
    }
    else
    {
        make_json_error(0);
    }
}

//库存首页提醒
elseif($_REQUEST['act'] == 'index_stock_alarm'){
    $stock_alarm = get_index_alarm_stock();

    if(admin_priv('storage_manage','',false)){
        $smarty->assign('storage_manage',true);
    }
    $smarty->assign('stock_alarm',$stock_alarm);
    $res = $smarty->fetch('index_stock_alarm.htm');

    die($json->encode($res));
}

//近一个月顾客记念日
elseif ($_REQUEST['act'] == 'get_commemoration'){
    $commemoration_list = get_commemoration();
    $smarty->assign('commemoration_list',$commemoration_list);
    $res['main'] = $smarty->fetch('commemoration.htm');

    die($json->encode($res));
}

/*获取更多排行信息*/
elseif($_REQUEST['act'] == 'get_more_ranklist'){
    $ranklist_name_en = mysql_real_escape_string($_REQUEST['ranklist_name_en']);
    $ranklist_name_zh = mysql_real_escape_string($_REQUEST['ranklist_name_zh']);

    $ranklist_date = get_ranklist_date();
    extract($ranklist_date);

    switch($ranklist_name_en){
    case 'hide_role_ranklist' :
        $sql_sub = tidysql('sql_role_ranklist');
        $result = get_ranklist($sql_sub,'role_ranklist',$date_start,$date_end);
        $key = 'role_name';
        $value = 'final_amount';
        break;
    case 'hide_group_ranklist' :
        $sql_sub = tidysql('sql_group_ranklist');
        $result  = get_ranklist($sql_sub,'group_ranklist',$date_start,$date_end);
        $key     = 'group_name';
        $value   = 'final_amount';
        break;
    case 'hide_ordersum_ranklist' :
        $sql_sub = tidysql('sql_finish_order_ranklist');
        $result = get_ranklist($sql_sub,'finish_order_ranklist',$date_start,$date_end);
        if($_REQUEST['company_mgr']){
            $key = 'role_name';
        }else{
            $key = 'admin_name';
        }
        $value = 'num';
        break;
    case 'hide_person_ranklist' :
        $sql_sub = tidysql('sql_person_ranklist');
        $result  = get_ranklist($sql_sub,'personal_ranklist',$date_start,$date_end);
        $key     = 'admin_name';
        $value   = 'final_amount';
        break;
    case 'hide_service_ranklist' :
        $result = stats_service($date_start,$date_end);
        $key    = 'user_name';
        $value  = 'num_service';
        break;
    }

    $table = array();
    $rank_count = ceil(count($result)/10);
    $off_width = $rank_count >= 3 ? 282 * 3 : 282 * $rank_count;
    for($j = 0; $j < $rank_count; $j++){
        $table[$j] = '<table style="float:left;margin-right:12px;" width="264px">';
        for($i = $j*10 ; $i < $j*10+10; $i++){
            $table[$j] .= '<tr><td><em>'.($i+1).'</em></td><td>'.$result[$i][$key].'</td><td style="text-align:left;color:#9E2F33;border-right:1px solid #A8A5A5">'.$result[$i][$value].'</td></tr>';
        }
        $table[$j] .= '</table>';
    }

    foreach($table as $val){
        $res['message'] .= $val;
    }

    $res['btncontent'] = false;
    $res['title'] = $ranklist_name_zh;
    $res['req_msg'] = true;
    $res['off_width'] = $off_width.'px';

    die($json->encode($res));
}


//为了库存盘点修改数据
elseif ($_REQUEST['act'] == 'add_date_in'){
    $cur_time = time();
    $sql_insert = 'IUPDATE '.$GLOBALS['ecs']->table('order_info')
        ."confirm_time=$cur_time,shipping_time=0,add_time=$cur_time,order_status=1,shipping_status=0)";

    return;
}

//推广活动
elseif($_REQUEST['act'] == 'get_spread_activity'){
    $spread_activity = index_spread_activity();

    $smarty->assign('spread_activity',$spread_activity);
    $res['main'] = $smarty->fetch('index_spread_activity.htm');
    die($json->encode($res));
}

/*销量警报*/
elseif($_REQUEST['act'] == 'get_sale_alarm'){
    $smarty->assign('sale_alarm_list',get_sale_alarm_list());  //销量警报

    $res['div_id'] = 'index_sale_alarm';
    $res['main']   = $smarty->fetch('index_sale_alarm.htm');

    die($json->encode($res));
}

/**
 * license check
 * @return  bool
 */
function license_check()
{
    // return 返回数组
    $return_array = array();

    // 取出网店 license
    $license = get_shop_license();

    // 检测网店 license
    if (!empty($license['certificate_id']) && !empty($license['token']) && !empty($license['certi']))
    {
        // license（登录）
        $return_array = license_login();
    }
    else
    {
        // license（注册）
        $return_array = license_reg();
    }

    return $return_array;
}

//公司规章制度
function company_rule($role_id)
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('company_system').
        " WHERE role_id IN ($role_id) OR role_id=0 ORDER BY add_time DESC";

    return $GLOBALS['db']->getAll($sql_select);
}
?>
