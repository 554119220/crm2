<?php
/**
 * ECSHOP 包邮卡管理页
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

//包邮卡销售详细列表
if($_REQUEST['act'] == 'list'){
    //头部显示初始化信息
    $smarty->assign('full_page',1);
    $smarty->assign('ur_here','包邮卡销售列表');
    $smarty->assign('admin_list', get_admin('session'));
    if($_SESSION['action_list'] == 'all'){
        $smarty->assign('role_list', get_role());
    }
    else{
        $sql = ' SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').' WHERE role_id='.$_SESSION['role_id'];
        $role = $GLOBALS['db']->getAll($sql);
        $smarty->assign('role_list',$role);
    }
    
    $freecard_sales_list = freecard_sales_list();

    //模板赋值
    $smarty->assign('freecard_sales_list',$freecard_sales_list['freecard_sales_list']);

    //页码赋值
    $smarty->assign('filter',       $freecard_sales_list['filter']);
    $smarty->assign('record_count', $freecard_sales_list['record_count']);
    $smarty->assign('page_count',   $freecard_sales_list['page_count']);   

    $smarty->display('freecard_sales_list.htm');
}

/*------------------------------------------------------ */
//-- ajax列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $freecard_sales_list = freecard_sales_list();
    $smarty->assign('freecard_sales_list',    $freecard_sales_list['freecard_sales_list']);
    $smarty->assign('filter',       $freecard_sales_list['filter']);
    $smarty->assign('record_count', $freecard_sales_list['record_count']);
    $smarty->assign('page_count',   $freecard_sales_list['page_count']);

    $sort_flag  = sort_flag($freecard_sales_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('freecard_sales_list.htm'), '', array('filter' => $freecard_sales_list['filter'], 'page_count' => $freecard_sales_list['page_count']));
}

//获取包邮卡销量详细列表
function freecard_sales_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 列表排序 */
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'og.goods_sn' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);

        //获取有效的包邮卡商品编号
        $sql = 'SELECT g.goods_sn FROM '.$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('freepostalcard_type').' fc WHERE g.goods_sn = fc.free_goods_sn';
        $goods_sn = $GLOBALS['db']->getCol($sql); 
        $goods_sn = implode(',',$goods_sn);

        //获取搜索条件
        $ex_where = '';
        //过滤条件
        $_REQUEST['keywords'] == '包邮卡商品号' && $_REQUEST['keywords'] = '';            
        $_REQUEST['keywords']  && $filter['keywords']  = trim($_REQUEST['keywords']);

        //截止时间为截止那天的24：00，转换时间戳得加多一天
        $_REQUEST['admin_id']  && $filter['admin_id']  = trim($_REQUEST['admin_id']);
        $_REQUEST['startTime'] && $filter['startTime'] = strtotime(trim($_REQUEST['startTime'])) -28800 + 3600 * 24;
        is_numeric($_REQUEST['startTime']) && $filter['startTime'] = $_REQUEST['startTime'];
        $_REQUEST['endTime']   && $filter['endTime']   = strtotime(trim($_REQUEST['endTime'])) -28800 + 3600 * 24;
        is_numeric($_REQUEST['endTime']) && $filter['endTime'] = $_REQUEST['endTime']; 

        // 增加起止时间、服务类别、服务方式、所属客服的条件
        $filter['keywords']  && $ex_where .= " AND og.goods_sn LIKE '%".mysql_like_quote($filter['keywords'])."%'";
        $filter['startTime'] && $ex_where .= " AND oi.add_time>=$filter[startTime] ";
        $filter['endTime']   && $ex_where .= " AND oi.add_time<=$filter[endTime] ";
        $filter['admin_id']  && $ex_where .= " AND u.admin_id=$filter[admin_id] ";
        
        //团队搜索
        intval($_REQUEST['team']) && $filter['team'] = intval($_REQUEST['team']);
        if($filter['team']){
            $sql = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').'
                WHERE role_id='.intval($filter['team']);
            $admin_id = $GLOBALS['db']->getCol($sql);
            $admin_id = implode(',',$admin_id);
            $ex_where .= ' AND oi.admin_id IN ('.$admin_id.')';
        }

        /* START: 这里控制访问权限 */
        if ($_SESSION['action_list'] != 'all' && $_SESSION['role_id'] != 8 && empty($filter['admin_id']))
        {
            $ex_where .= ' AND u.admin_id='.$_SESSION['admin_id']; 
        }
        elseif ($filter['admin_id'])
        {
            $ex_where .= " AND u.admin_id=$filter[admin_id] ";
        }
        else
        {
            $ex_where .= ' AND u.admin_id>0 ';
        }
        /* END: 这里控制访问权限 */

        $ex_where .= ' AND u.admin_id<>-1 ';

        /* 分页大小 */
        if($_REQUESTP['count']){
            //包邮卡销售总量已存在
            $filter['record_count'] = $_REQUEST['count'];
        }
        else{           
            //获取客服人员中的购买包邮卡顾客总量
            $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').' oi,'
                .$GLOBALS['ecs']->table('order_goods').' og,'.$GLOBALS['ecs']->table('users').' u 
                WHERE oi.order_id=og.order_id AND oi.user_id=u.user_id 
                AND oi.order_status=5 AND oi.shipping_status>0 AND oi.pay_status=2 
                AND og.goods_sn IN ('.$goods_sn.')'.$ex_where; 
            $count = $GLOBALS['db']->getOne($sql);
            $filter['record_count'] = $count;
        }
        $filter = page_and_size($filter);

        //获取用户列表
        /*START 初始化条件 */
        $sel ="SELECT u.user_id,u.user_name,u.sex,u.mobile_phone,u.home_phone,u.admin_name,FROM_UNIXTIME(oi.add_time,'%Y-%m-%d') AS add_time,og.goods_sn,og.goods_name,fc.effective_date,fc.free_number,oi.add_time as fc_add_time ";
        $from = $GLOBALS['ecs']->table('users').' u,'.$GLOBALS['ecs']->table('order_info').' oi,'.$GLOBALS['ecs']->table('order_goods').' og,'.$GLOBALS['ecs']->table('admin_user').' au,'.$GLOBALS['ecs']->table('freepostalcard_type').' fc ';
        $where = ' fc.free_goods_sn = og.goods_sn AND u.user_id = oi.user_id AND oi.order_id = og.order_id AND u.admin_id = au.user_id AND oi.order_status = 5 AND oi.shipping_status > 0 AND oi.pay_status = 2';
        /*END 初始化条件 */
        if($_REQUEST['sn']){
            $where .= ' AND og.goods_sn = '.$_REQUEST['sn'];
        }
        else{
            $where .= ' AND og.goods_sn IN ('.$goods_sn.')';
        }
        
        //获取顾客列表
        $sql = $sel . ' FROM ' .$from . ' WHERE ' . $where . $ex_where. " ORDER BY " . $filter['sort_by'] . ' ' . $filter['sort_order'] . " LIMIT " . $filter['start'] . ',' . $filter['page_size'];  

        set_filter($filter, $sql);

    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $freecard_sales_list = $GLOBALS['db']->getAll($sql);
    
    foreach($freecard_sales_list as $key=>$list){
        //获取包邮卡的结束时间
        $time = explode('-',$list['add_time']);
        $mon = $time[1] + $list['effective_date'];
        if(intval($mon / 12) > 0){
            $time[0] = $time[0] + intval($mon / 12) ;
            $mon = $mon % 12;
            if(strlen($mon) == 1){
                $mon = '0'.$mon;
            }
        }
        $freecard_sales_list[$key]['end_time'] = $time[0].'-'.$mon.'-'.$time[2]; 

        //获取包邮卡的可用次数
        $sql = 'SELECT COUNT(order_id) FROM '.$GLOBALS['ecs']->table('order_info').' 
            WHERE add_time >= '.$list['fc_add_time'].' AND user_id = '.$list['user_id'];
        $freecard_sales_list[$key]['available_num'] = $list['free_number'] - $GLOBALS['db']->getOne($sql);
    } 


    $arr = array('freecard_sales_list' => $freecard_sales_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>
