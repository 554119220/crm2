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

$free_platform = array('1' => '官网', '2' => '天猫商城', '3' => 'QQ商城', '4' => '电话销售', '5' => '京东商场');

//包邮卡列表请求
if($_REQUEST['act'] == 'list'){
    $smarty->assign('full_page',1);
    $smarty->assign('ur_here','包邮卡列表');
    $smarty->assign('action_link', array('href'=>'freecard.php?act=add', 'text' => '添加包邮卡类型'));
    
    //是否显示编辑和删除功能    
    if($_SESSION['action_list'] == 'all'){
        $smarty->assign('show','1'); 
    }

    //获取包邮卡列表信息
    $freecard_list = freecard_list();
    //模板赋值
    $smarty->assign('freecard_list',$freecard_list['freecard_list']);
    //页码赋值
    $smarty->assign('filter',       $freecard_list['filter']);
    $smarty->assign('record_count', $freecard_list['record_count']);
    $smarty->assign('page_count',   $freecard_list['page_count']);   

    $smarty->display('freecard_list.htm');
}

/*------------------------------------------------------ */
//-- ajax列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $freecard_list = freecard_list();
    $smarty->assign('freecard_list',    $freecard_list['freecard_list']);
    $smarty->assign('filter',       $freecard_list['filter']);
    $smarty->assign('record_count', $freecard_list['record_count']);
    $smarty->assign('page_count',   $freecard_list['page_count']);
    
    //是否显示编辑和删除功能
    if($_SESSION['action_list'] == 'all'){
        $smarty->assign('show','1'); 
    }

    $sort_flag  = sort_flag($freecard_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('freecard_list.htm'), '', array('filter' => $freecard_list['filter'], 'page_count' => $freecard_list['page_count']));
}

//包邮卡的更新或添加
elseif($_REQUEST['act'] == 'add'){
    //判断是否有更新请求
    $freecard_id = intval($_REQUEST['freecard_id']);
    if(!empty($freecard_id)){
        $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('freepostalcard_type').' 
            WHERE freecard_id = '.$freecard_id;
        $free_info = $GLOBALS['db']->getRow($sql);
        $free_platform_sel = $free_info['free_platform'];
        $free_platform_sel = explode(',',$free_platform_sel);
        $smarty->assign('free_platform_sel',$free_platform_sel);
        $smarty->assign('free_info',$free_info);
    }            
    $smarty->assign('free_platform',$free_platform);
    $smarty->assign('ur_here',$_LANG['view_questionnaire']);
    $smarty->assign('action_link', array('href'=>'freecard.php?act=list', 'text' => '包邮卡列表'));
    if(!empty($_REQUEST['edit'])){
        $smarty->assign('show_update',1);
    }
    $smarty->display('add_freecard.htm');
}

//包邮卡的更新或添加
elseif($_REQUEST['act'] == 'insert'){
    $freecard_info = array
      (
            'free_goods_sn'   => $_POST['free_goods_sn'],
            'free_limit'      => intval($_POST['free_limit']),
            'effective_date'  => intval($_POST['effective_date']),
            'free_number'     => intval($_POST['free_number']),
            'free_type'       => intval($_POST['free_type']),
            'free_platform'   => empty($_POST['free_platform']) ? '' : implode(',',$_POST['free_platform']),
            'free_remarks'    => mysql_real_escape_string($_POST['free_remarks']),
      ); 
    $fields = array_keys($freecard_info);
    $values = array_values($freecard_info);
    //更新包邮卡信息到数据库
    if($_POST['update'] == 'update'){
         $count = count($fields);
         for($i=0;$i<$count;$i++){
              $insert .= $fields[$i]."='".$values[$i]."',";
         }
         $insert=rtrim($insert,',');
         $sql = 'UPDATE '.$GLOBALS['ecs']->table('freepostalcard_type').' SET '.$insert.' WHERE freecard_id= '.$_POST['freecard_id'];
         $GLOBALS['db']->query($sql);
         $msg="更新成功";
       }
    //插入包邮卡信息到数据库
    else{        
         $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('freepostalcard_type').'('.implode(',',$fields).')VALUES(\''.implode('\',\'',$values).'\')';
         $GLOBALS['db']->query($sql);
         $msg="包邮卡添加成功";
    }

    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'freecard.php?act=list');
    sys_msg($msg, 0, $link);
}

//包邮卡类型的删除
elseif($_REQUEST['act'] == 'delete'){
    $freecard_id = intval($_REQUEST['freecard_id']);
    $sql = 'DELETE FROM '.$GLOBALS['ecs']->table('freepostalcard_type').' WHERE freecard_id = '.$freecard_id;
    $GLOBALS['db']->query($sql);
    $msg="删除成功";

    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href' => 'freecard.php?act=list');
    sys_msg($msg,0,$link);

}

function freecard_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 列表排序 */
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'freecard_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 分页大小 */
        $sql = "SELECT count(*) FROM " .$GLOBALS['ecs']->table('freepostalcard_type');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 查询 */
        $sql = "SELECT 	freecard_id,free_goods_sn,free_limit,effective_date,free_number,free_type,free_platform,free_remarks ".
               " FROM ".$GLOBALS['ecs']->table('freepostalcard_type').
               " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
               " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $freecard_list = $GLOBALS['db']->getAll($sql);

    //获取包邮卡平台名称
    $free_platform = array('1' => '官网', '2' => '天猫商城', '3' => 'QQ商城', '4' => '电话销售', '5' => '京东商场');
    foreach($freecard_list as $key=>$free){
        $j = $free['free_platform'];
        if(strlen($j) >= 2 ){
            $j = explode(',',$j);
            $freecard_list[$key]['free_platform'] = '';
            foreach($j as $arr){
                $freecard_list[$key]['free_platform'] = $freecard_list[$key]['free_platform'].$free_platform[$arr].'；';
            }
        }
        else{
            $freecard_list[$key]['free_platform'] = empty($free_platfrom) ? '' : $free_platform[$j].'；';
        }
    }

    //获取包邮卡销售数量
    foreach($freecard_list as $key=>$free){
        if($free['free_goods_sn']){
            $from = $GLOBALS['ecs']->table('order_info').' oi,'.$GLOBALS['ecs']->table('order_goods').' og';
            $where = ' oi.order_id=og.order_id AND og.goods_sn='.$free['free_goods_sn'].' AND oi.order_status=5 AND oi.shipping_status>0 AND oi.pay_status=2';

            /* START: 这里控制访问权限 */
            if ($_SESSION['action_list'] != 'all' && $_SESSION['role_id'] != 8 && empty($filter['admin_id']))
            {
                 $ex_where .= ' AND oi.add_admin_id='.$_SESSION['admin_id']; 
            }
            elseif ($filter['admin_id'])
            {
                 $ex_where .= " AND oi.add_admin_id=$filter[admin_id] ";
            }
            else
            {
                $ex_where .= ' AND oi.add_admin_id>0 ';
            }
            /* END: 这里控制访问权限 */
            $ex_where .= ' AND oi.add_admin_id<>-1 ';
            
            //查询个人添加包邮卡的数量
            $personal_sql = 'SELECT COUNT(*) FROM '.$from.' WHERE '.$where.$ex_where; 
            $freecard_list[$key]['personal_count'] = $GLOBALS['db']->getOne($personal_sql);

            //查询团队添加包邮卡的数量
/*            $sql = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE role_id='.$_SESSION['role_id'];
            $admin_id = $GLOBALS['db']->getCol($sql);
            $admin_id = implode(',',$admin_id);
            $team_sql = 'SELECT COUNT(*) FROM '.$from.' WHERE '.$where.' AND oi.add_admin_id IN ('.$admin_id.')';
            $freecard_list[$key]['team_count'] = $GLOBALS['db']->getOne($team_sql); */
        }       
    }

    $arr = array('freecard_list' => $freecard_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>
