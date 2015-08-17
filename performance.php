<?php
/**
 * ECSHOP 绩效查询
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
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_common.php');

$file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
$smarty->assign('filename', $file);

/*-- 仓库子菜单 --*/
if ($_REQUEST['act'] == 'menu')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);
    die($smarty->fetch('left.htm'));
}

/*--- 显示推广表登记 ---*/
elseif($_REQUEST['act'] == 'spread')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);
    $res['left'] = $smarty->fetch('left.htm');

    //获取所属商城的所有名字
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role');
    $role_list = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('role_list',$role_list);

    //获取广告活动名字
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('advertisement');
    $ad_list = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('ad_list',$ad_list);
    $res['main'] = $smarty->fetch('spread.htm');

    die($json->encode($res));
}

/*---  推广登记列表  ---*/
elseif($_REQUEST['act'] == 'spread_list')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    $spread_list = spread_list();
    $platform_list = platform_list();
    if (admin_priv('spread_list_all', '', false))
    {
        array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
    }

    $smarty->assign('platform_list', $platform_list);

    $smarty->assign('daytime',time()+28800);
    $smarty->assign('list', $spread_list['spread_list']);

    $smarty->assign('curr_title', '推广列表');
    $smarty->assign('num', sprintf('（共%d条记录）', $spread_list['record_count']));

    // 分页设置
    $smarty->assign('filter',       $spread_list['filter']);
    $smarty->assign('record_count', $spread_list['record_count']);
    $smarty->assign('page_count',   $spread_list['page_count']);
    $smarty->assign('page_size',    $spread_list['page_size']);
    $smarty->assign('page_start',   $spread_list['start']);
    $smarty->assign('page_end',     $spread_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_link',    $spread_list['condition']);
    $smarty->assign('page_set',     $spread_list['page_set']);
    $smarty->assign('page',         $spread_list['page']);
    $smarty->assign('act',          trim($_REQUEST['act']));

    if (isset($_REQUEST['platform']))
    {
        $res['id'] = intval($_REQUEST['platform']);
        $res['switch_tag'] = 'true';
    }

    $res['main'] = $smarty->fetch('personal_list.htm');

    die($json->encode($res));
}

/*--- 添加推广表记录 ---*/
elseif($_REQUEST['act'] == 'add_spread')
{
    $spread_pv       = intval($_REQUEST['spread_pv']);
    $spread_uv       = intval($_REQUEST['spread_uv']);
    $orders          = intval($_REQUEST['orders']);
    $sale            = floatval($_REQUEST['sale']);
    $job_content     = mysql_real_escape_string($_REQUEST['job_content']);
    $summary         = mysql_real_escape_string($_REQUEST['summary']);
    $scalping_num    = intval($_REQUEST['scalping_num']);
    $scalping_amount = floatval($_REQUEST['scalping_amount']);

    //加判断防止提交空数据
    if( $job_content == '' || $summary == '' )
    {
        $res['req_msg'] = true;
        $res['message'] = '提交内容不能为空';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    //根据推广人的id找到对应的所属平台
    $sql_select = "SELECT role_id FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE user_id='$_SESSION[admin_id]'";
    $rid = $GLOBALS['db']->getOne($sql_select);

    //重组传过来的参数
    $list = array();
    foreach($_REQUEST['adname_id'] as $key=>$v)
    {
        $list[$key]['adname_id']    = $_REQUEST['adname_id'][$key];
        $list[$key]['ad_costs']     = $_REQUEST['ad_costs'][$key];
        $list[$key]['ad_revenue']   = $_REQUEST['ad_revenue'][$key];
        $list[$key]['favorite_num'] = $_REQUEST['favorite_num'][$key];
        $list[$key]['change_order'] = $_REQUEST['change_order'][$key];
    }

    //插入工作总结表
    $add_time = time()+28800;
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('work_summary').
        "(summary,job_content,admin_id,platform,add_time,belong_time)
        VALUES('$summary','$job_content','{$_SESSION['admin_id']}','{$_SESSION['role_id']}',$add_time','$add_time')";
    $GLOBALS['db']->query($sql_insert);

    //获取工作总结对应的id
    $work_id = $GLOBALS['db']->insert_id();

    if($work_id)
    {   
        //插入推广表
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('spread').'(spread_pv,spread_uv,orders,sale,
            admin_id,add_time,work_id,scalping_amount,scalping_num)'."VALUES('$spread_pv','$spread_uv','$orders',
            '$sale','{$_SESSION['admin_id']}','$add_time','$work_id','$scalping_amount','$scalping_num')";
        $GLOBALS['db']->query($sql_insert);

        //获取推广表对应的id
        $spread_id = $GLOBALS['db']->insert_id();

        if($spread_id)
        {
            foreach($list as $v)
            {
                if(!empty($v))
                {
                    //插入广告费用表
                    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('spread_ad').
                        '(ad_costs,ad_revenue,favorite_num,change_order,adname_id,
                        spread_id,add_time,rid)VALUES'.
                        "('$v[ad_costs]','$v[ad_revenue]','$v[favorite_num]',
                        '$v[change_order]','$v[adname_id]',
                        '$spread_id','$add_time','$rid')";
                    $result = $GLOBALS['db']->query($sql_insert);

                    if($result)
                    {
                        $res['req_msg'] = true;
                        $res['message'] = '添加成功';
                        $res['timeout'] = 2000;
                    }
                    else
                    {
                        $res['req_msg'] = true;
                        $res['message'] = '添加失败';
                        $res['timeout'] = 2000;

                        die($json->encode($res));
                    }
                }
            }
        }
        else
        {
            $res['req_msg'] = true;
            $res['message'] = '添加失败';
            $res['timeout'] = 2000;

            die($json->encode($res));
        }
    }
    else
    {
        $res['req_msg'] = true;
        $res['message'] = '添加失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    die($json->encode($res));
}

/* --- 编辑推广个人登记页面---*/
elseif($_REQUEST['act'] == 'spread_edit')
{
    $sid = isset($_GET['sid']) ? intval($_GET['sid']) : 1;

    //根据传过来的sid查找对应多条记录
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('spread').' AS s,'
        .$GLOBALS['ecs']->table('spread_ad').' d,'.$GLOBALS['ecs']->table('work_summary').
        ' w,'.$GLOBALS['ecs']->table('advertisement')." ad WHERE s.spread_id=$sid AND 
        s.spread_id=d.spread_id AND s.work_id=w.work_id AND d.adname_id=ad.id";
    $row = $GLOBALS['db']->getAll($sql_select);

    //查询推广表的记录
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('spread').' s,'.
        $GLOBALS['ecs']->table('work_summary').
        " w WHERE s.spread_id=$sid AND s.work_id=w.work_id";
    $spread_list = $GLOBALS['db']->getRow($sql_select);

    //获取所属商城的所有名字
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role');
    $role_list = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('role_list',$role_list);
    $smarty->assign('row',$row);
    $smarty->assign('spread_list',$spread_list);

    //获取广告活动名字
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('advertisement');// .' ORDER BY sort ASC';
    $ad_list = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('ad_list',$ad_list);
    $res['main'] = $smarty->fetch('spread_edit.htm');

    die($json->encode($res));
}

/*--- 显示客服登记表 ---*/
elseif($_REQUEST['act'] == 'add_service')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav  = list_nav();

    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);
    $res['left'] = $smarty->fetch('left.htm');

    //查询出所有平台的名字
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').
        ' WHERE role_type>=1';
    $role_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('role_name',$role_name);

    //查询出所有的咨询方式
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('advisory_way').
        ' WHERE is_enable=1';
    $way_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('way_name',$way_name);

    //查询出所有的咨询类型
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('advisory_type').
        ' WHERE is_enable=1';
    $type_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('type_name',$type_name);
    $res['main'] = $smarty->fetch('add_service.htm');

    die($json->encode($res));
}

/*--- 添加客服登记表记录 ---*/
elseif($_REQUEST['act'] == 'add_service_record')
{
    //接受传过来的数据 
    $job_content = mysql_real_escape_string(trim($_REQUEST['job_content']));  
    $add_time = time()+28800; 

    //加判断防止提交空数据
    if($job_content == '')
    {
        $res['req_msg'] = true;
        $res['message'] = '提交内容不能为空';
        $res['timeout'] = 2000;
        die($json->encode($res));
    }

    //插入数据之前先判断是否重复提交
    $post_time = date('Y-m-d',(time()+28800));
    //$sql_select = 'SELECT FROM_UNIXTIME(add_time,"%Y-%m-%d") add_time FROM '.$GLOBALS['ecs']->table('work_summary')." WHERE admin_id=$_SESSION[admin_id]";
    $now_date = strtotime(date('Y-m-d 08:30:00', time()+28800));
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('work_summary').
        " WHERE admin_id={$_SESSION['admin_id']} AND add_time>$now_date";
    $time_list = $GLOBALS['db']->getAll($sql_select);
    if($time_list)
    {
        $res['req_msg'] = true;
        $res['message'] = '重复添加!!';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    //重组传过来的参数
    $list = array();
    foreach($_REQUEST['rid'] as $key=>$val)
    {
        $list[$key]['rid'] = $_REQUEST['rid'][$key];
        $list[$key]['guide_consulting'] = $_REQUEST['guide_consulting'][$key];
        $list[$key]['success_deal'] = $_REQUEST['success_deal'][$key];
        $list[$key]['amount'] = $_REQUEST['amount'][$key];
        $list[$key]['way_id'] = $_REQUEST['way_id'][$key];
        $list[$key]['type_id'] = $_REQUEST['type_id'][$key];
        $list[$key]['package_number'] = $_REQUEST['package_number'][$key];
        $list[$key]['new_user'] = $_REQUEST['new_user'][$key];
        $list[$key]['package_money'] = $_REQUEST['package_money'][$key];
        $list[$key]['old_user'] = $_REQUEST['old_user'][$key];
    }

    //插入工作总结表
    $belong_time = time()+28800;
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('work_summary').'(job_content,admin_id,add_time,belong_time)
        VALUES('."'$job_content','$_SESSION[admin_id]','$add_time','$belong_time')";
    $GLOBALS['db']->query($sql_insert);

    //获取工作总结对应的id
    $work_id = $GLOBALS['db']->insert_id();

    if($work_id)
    {  
        foreach($list as $v)
        {
            if(!empty($v))
            {
                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('service_record').
                    '(rid,way_id,type_id,amount,success_deal,guide_consulting,work_id,
                    package_number,package_money,new_user,old_user)
                    VALUES('."'$v[rid]','$v[way_id]','$v[type_id]','$v[amount]'
                    ,'$v[success_deal]', '$v[guide_consulting]','$work_id',
                    '$v[package_number]','$v[package_money]','$v[new_user]','$v[old_user]')";
                $result = $GLOBALS['db']->query($sql_insert);
                if(!$result)
                {
                    $res['req_msg'] = true;
                    $res['message'] = '添加失败';
                    $res['timeout'] = 2000;

                    die($json->encode($res));
                }
            }
        }
    }
    else
    {
        $res['req_msg'] = true;
        $res['message'] = '添加失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    $res['req_msg'] = true;
    $res['message'] = '添加成功';
    $res['timeout'] = 2000;

    die($json->encode($res));   
}

/*--- 更新推广表记录 ---*/
elseif($_REQUEST['act'] == 'update_spread')
{
    //获得要更新的spread_id,更新前先删除这条记录对应到spread_ad的所有资料
    $spread_id = trim($_REQUEST['spread_id']);

    //根据推广人的id找到对应的所属平台
    $sql_select = 'SELECT role_id FROM '.$GLOBALS['ecs']->table('admin_user').
        " WHERE user_id='$_SESSION[admin_id]'";
    $rid = $GLOBALS['db']->getOne($sql_select);
    $add_time = time()+28800;

    //查找出原来的aid保留起来
    $sql_select = 'SELECT aid FROM '.$GLOBALS['ecs']->table('spread_ad').
        " WHERE spread_id=$spread_id";
    $aid_list = $GLOBALS['db']->getAll($sql_select);
    //print_r($aid_list);die;
    //删除spread_ad对应的记录
    $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('spread_ad').
        " WHERE spread_id=$spread_id";
    $result = $GLOBALS['db']->query($sql_delete);

    if(!$result)
    {
        $res['req_msg'] = true;
        $res['message'] = '更新失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    //获取spread表的数据
    $spread_pv = intval($_REQUEST['spread_pv']);
    $spread_uv = intval($_REQUEST['spread_uv']);
    $orders = intval($_REQUEST['orders']);
    $sale = floatval($_REQUEST['sale']);
    $scalping_num = intval($_REQUEST['scalping_num']);
    $scalping_amount = floatval($_REQUEST['scalping_amount']);

    //更新spread表记录
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('spread').
        " SET spread_pv='$spread_pv',spread_uv='$spread_uv',orders='$orders',sale='$sale',
        scalping_num='$scalping_num',scalping_amount='$scalping_amount' WHERE spread_id=$spread_id";
    $result = $GLOBALS['db']->query($sql_update);

    if(!$result)
    {
        $res['req_msg'] = true;
        $res['message'] = '更新失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    //获取工作总结表的记录
    $job_content = mysql_real_escape_string($_REQUEST['job_content']);
    $summary = mysql_real_escape_string($_REQUEST['summary']);

    //查找对应的工作总结work_id( 一个记录的直接更新对应的记录，多条记录的先删除后插入，保留aid)
    $sql_select = 'SELECT work_id FROM '.$GLOBALS['ecs']->table('spread').
        " WHERE spread_id=$spread_id ";
    $work_id = $GLOBALS['db']->getOne($sql_select);

    //更新work_summary表
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('work_summary').
        " SET job_content='$job_content',summary='$summary' WHERE work_id=$work_id";  
    $result = $GLOBALS['db']->query($sql_update);

    if(!$result)
    {
        $res['req_msg'] = true;
        $res['message'] = '更新失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    //更新spread_ad表的数据
    //重组传过来的参数
    $list = array();
    foreach($_REQUEST['adname_id'] as $key=>$val)
    {
        $list[$key]['adname_id'] = $_REQUEST['adname_id'][$key];
        $list[$key]['ad_costs'] = $_REQUEST['ad_costs'][$key];
        $list[$key]['ad_revenue'] = $_REQUEST['ad_revenue'][$key];
        $list[$key]['favorite_num'] = $_REQUEST['favorite_num'][$key];
        $list[$key]['change_order'] = $_REQUEST['change_order'][$key];
    }

    foreach($list as $key=>$v)
    {
        if(!empty($v))
        {
            //插入新的记录
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('spread_ad').'(adname_id,ad_costs,
                ad_revenue,favorite_num,change_order,spread_id,add_time,rid,aid)VALUES('.
                "'$v[adname_id]','$v[ad_costs]','$v[ad_revenue]','$v[favorite_num]','$v[change_order]',
                '$spread_id','$add_time','$rid','{$aid_list[$key]['aid']}')";
            $result = $GLOBALS['db']->query($sql_insert);

            if(!$result)
            {
                $res['req_msg'] = true;
                $res['message'] = '更新失败';
                $res['timeout'] = 2000;

                die($json->encode($res));
            }
        }
    }

    $res['req_msg'] = true;
    $res['message'] = '更新成功';
    $res['timeout'] = 2000;

    die($json->encode($res));
}

/*--- 客服个人登记列表 ---*/
elseif($_REQUEST['act'] == 'service_personal_list')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);
    $res['left'] = $smarty->fetch('left.htm');

    //查询所有平台的名字
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role')
        .' WHERE is_online=1 ';
    $name_list = $GLOBALS['db']->getAll($sql_select);

    if(!admin_priv('all','',false))
    {
        //根据用户的id查找所属的平台，用来显示一个商城下的所有客服记录
        $sql_select = 'SELECT role_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id=$_SESSION[admin_id]";
        $rid = $GLOBALS['db']->getOne($sql_select);

        //查询对应平台的名字
        $sql_select = 'SELECT role_name FROM '.$GLOBALS['ecs']->table('role').
            " WHERE role_id=$rid";
        $every_name = $GLOBALS['db']->getOne($sql_select);
        $smarty->assign('every_name',$every_name);
    }

    $sql_select = 'SELECT w.work_id,w.add_time,a.user_name,a.role_id FROM '
        .$GLOBALS['ecs']->table('work_summary').' w,'
        .$GLOBALS['ecs']->table('service_record').' s,'
        .$GLOBALS['ecs']->table('admin_user').' a WHERE s.work_id=w.work_id '.
        " AND w.admin_id=a.user_id AND w.is_delete=0 ";

    if(admin_priv('all','',false) || admin_priv('service_personal_list','',false))
    {
        $sql_select .= " ";
        //查看所有的判断
        $smarty->assign('admin_role',1);
    }
    elseif(admin_priv('service_view','',false))
    {
        $sql_select .= " AND a.role_id=$rid ";
    }
    else
    {
        $sql_select .= " AND w.admin_id=$admin_id ";     
        //编辑删除权限判断
        $smarty->assign('role_edit',1);
    }
    $sql_select .= ' ORDER BY(w.add_time) DESC';

    $work_list = $GLOBALS['db']->getAll($sql_select); 

    if(admin_priv('all','',false))
    {
        //给编辑删除加权限
        $smarty->assign('role_edit',1);
        $smarty->assign('role_del',2);
    }

    //查询出所有平台的名字
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').
        ' WHERE is_online=1';
    $role_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('role_name',$role_name);

    //查询出所有的咨询方式
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('advisory_way').
        ' WHERE is_enable=1';
    $way_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('way_name',$way_name);

    //查询出所有的咨询类型
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('advisory_type').
        ' WHERE is_enable=1';
    $type_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('type_name',$type_name);

    $service_list = array();
    foreach($work_list as $val)
    {
        $service_list[$val['work_id']]['add_time'] = $val['add_time'];
        $service_list[$val['work_id']]['work_id'] = $val['work_id'];
        $service_list[$val['work_id']]['user_name'] = $val['user_name'];

        //根据获取到的work_id查询service_record相应的记录
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('service_record')
            ." WHERE work_id=$val[work_id]";
        $service_list[$val['work_id']]['data']= $GLOBALS['db']->getAll($sql_select);

        //查询总的咨询次数
        $sql_select = 'SELECT SUM(guide_consulting) g,SUM(success_deal) s FROM '.
            $GLOBALS['ecs']->table('service_record').
            " WHERE work_id=$val[work_id] GROUP BY work_id";
        $sum = $GLOBALS['db']->getRow($sql_select);

        if($sum['g'] != 0 )
        {
            $service_list[$val['work_id']]['success_all'] =
                (round($sum['s']/$sum['g'],2)) * 100 .'%';
        }

        //查询成功率
        foreach($service_list[$val['work_id']]['data'] as $key=>$v)
        {
            if($v['guide_consulting'] != 0)
            {
                $service_list[$val['work_id']]['data'][$key]['success_rate'] =
                    (round($v['success_deal']/$v['guide_consulting'],2)) * 100 .'%';
            }
        }

    }

    foreach($service_list as &$v)
    {
        $v['add_time'] = date('Y-m-d',$v['add_time']);
    }

    $smarty->assign('service_list',$service_list);

    $res['main'] = $smarty->fetch('service_all_list.htm');

    die($json->encode($res));
}

/*--- 客服登记列表 ---*/
elseif($_REQUEST['act'] == 'service_record_all_list')
{
    $res = array ();
    $res['left'] = sub_menu_list($file);
    if ($res['left'] === false) unset($res['left']);

    $counsel_list  = counsel_list();
    $platform_list = platform_list();
    if (admin_priv('counsel_list_all', '', false))
    {
        array_unshift($platform_list, array('role_name'=>'全部','role_id'=>0));
    }

    foreach ($counsel_list['counsel_list'] as &$val)
    {
        if ($val['success_deal'])
        {
            $val['deal_rate'] = round($val['success_deal']/$val['guide_consulting']*100, 2).'%';
        }
    }

    $smarty->assign('counsel_list',  $counsel_list['counsel_list']);
    $smarty->assign('platform_list', $platform_list);

    $smarty->assign('daytime',time()+28800);

    $smarty->assign('curr_title', '顾客咨询记录');
    $smarty->assign('num', sprintf('（共%d条记录）', $counsel_list['record_count']));

    // 分页设置
    $smarty->assign('filter',       $counsel_list['filter']);
    $smarty->assign('record_count', $counsel_list['record_count']);
    $smarty->assign('page_count',   $counsel_list['page_count']);
    $smarty->assign('page_size',    $counsel_list['page_size']);
    $smarty->assign('page_start',   $counsel_list['start']);
    $smarty->assign('page_end',     $counsel_list['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_link',    $counsel_list['condition']);
    $smarty->assign('page_set',     $counsel_list['page_set']);
    $smarty->assign('page',         $counsel_list['page']);
    $smarty->assign('act',          trim($_REQUEST['act']));

    if (isset($_REQUEST['platform']))
    {
        $res['id'] = intval($_REQUEST['platform']);
        $res['switch_tag'] = 'true';
    }

    $res['main'] = $smarty->fetch('counsel_list.htm');

    die($json->encode($res));
}

/*--- 客服个人登记编辑页面 ---*/
elseif($_REQUEST['act'] == 'service_personal_edit')
{
    $work_id = isset($_GET['work_id']) ? intval($_GET['work_id']) : 1;

    //查询出所有平台的名字
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').
        ' WHERE is_online=1';
    $role_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('role_name',$role_name);

    //查询出所有的咨询方式
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('advisory_way').
        ' WHERE is_enable=1';
    $way_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('way_name',$way_name);

    //查询出所有的咨询类型
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('advisory_type').
        ' WHERE is_enable=1';
    $type_name = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('type_name',$type_name);

    //根据获取到的work_id查询service_record相应的记录
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('service_record')
        ." WHERE work_id=$work_id";
    $service_list = $GLOBALS['db']->getAll($sql_select);

    //根据获取到的work_id查询work_summary相应的记录
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('work_summary')
        ." WHERE work_id=$work_id";
    $work_list = $GLOBALS['db']->getRow($sql_select);

    $smarty->assign('work_list',$work_list);
    $smarty->assign('service_list',$service_list);
    $res['main'] = $smarty->fetch('service_edit.htm');

    die($json->encode($res));
}

/*--- 更新客服个人登记资料 ---*/
elseif($_REQUEST['act'] == 'update_service_record')
{
    //获取传过来的work_id,job_content
    $work_id = intval($_POST['work_id']);
    $job_content = mysql_real_escape_string($_POST['job_content']);

    //获取用户的user_id
    $user_id = $_SESSION['admin_id'];
    $add_time = time()+28800;

    //根据work_id删除对应的service_record的记录，然后再插入新的
    $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('service_record').
        " WHERE work_id=$work_id";
    $result = $GLOBALS['db']->query($sql_delete);

    if(!$result)
    {
        $res['req_msg'] = true;
        $res['message'] = '更新失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    //根据传过来的work_id，更新对应工作总结表
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('work_summary').
        " SET job_content='$job_content',admin_id='$user_id' WHERE work_id=$work_id";
    $result = $GLOBALS['db']->query($sql_update);

    if(!$result)
    {
        $res['req_msg'] = true;
        $res['message'] = '更新失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    //更新service_record表的数据
    //重组传过来的参数
    $list = array();
    foreach($_REQUEST['guide_consulting'] as $key=>$val)
    {
        $list[$key]['guide_consulting'] = $_REQUEST['guide_consulting'][$key];
        $list[$key]['success_deal'] = $_REQUEST['success_deal'][$key];
        $list[$key]['amount'] = $_REQUEST['amount'][$key];
        $list[$key]['rid'] = $_REQUEST['rid'][$key];
        $list[$key]['way_id'] = $_REQUEST['way_id'][$key];
        $list[$key]['type_id'] = $_REQUEST['type_id'][$key];
        $list[$key]['package_number'] = $_REQUEST['package_number'][$key];
        $list[$key]['new_user'] = $_REQUEST['new_user'][$key];
        $list[$key]['package_money'] = $_REQUEST['package_money'][$key];
        $list[$key]['old_user'] = $_REQUEST['old_user'][$key];
    }

    foreach($list as $key=>$v)
    {
        if(!empty($v))
        {
            //插入新的记录
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('service_record').
                '(guide_consulting,success_deal,amount,work_id,rid,way_id,type_id,
                package_number,package_money,new_user,old_user)VALUES('
                ."'$v[guide_consulting]','$v[success_deal]','$v[amount]','$work_id',
                '$v[rid]','$v[way_id]','$v[type_id]','$v[package_number]',
                '$v[package_money]','$v[new_user]','$v[old_user]')";
            $result = $GLOBALS['db']->query($sql_insert);

            if(!$result)
            {
                $res['req_msg'] = true;
                $res['message'] = '更新失败';
                $res['timeout'] = 2000;

                die($json->encode($res));
            }
        }

    }

    $res['req_msg'] = true;
    $res['message'] = '更新成功';
    $res['timeout'] = 2000;

    die($json->encode($res));
}

/*--- 工作总结列表页面 ---*/
elseif($_REQUEST['act'] == 'work_summary_list')
{
    $res['main'] = $smarty->fetch('work_summary_list.htm');

    die($json->encode($res));
}

/*--- 根据传过来的时间进行搜索 ---*/
elseif($_REQUEST['act'] == 'search_time')
{
    //判断传过来的时间是否为空
    if(!empty($_POST['time']))
    {
        $start_time = strtotime($_POST['time']);  
        $end_time = strtotime($_POST['time'].'23:59:59'); 
    }
    else
    {
        $start_time = strtotime(date('Y-m-d H:i',(time()+28800)));
        $end_time = $start_time + 86400; 
    }
    $posttime = $_POST['time'];
    $username = $_POST['username'];

    //判断搜索的条件
    if(!empty($_POST['username']) && $_POST['username'] != '' && $_POST['time'] != '')
    {
        $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_name='$username'";
        $userid = $GLOBALS['db']->getOne($sql_select);
        if(!empty($userid))
        {
            //构造查找的条件
            $where = "add_time>=$start_time AND add_time<=$end_time 
                AND admin_id='$userid'";
        }
        else
        {
            $res['req_msg'] = true;
            $res['message'] = '搜索人姓名不存在';
            $res['timeout'] = 2000;

            die($json->encode($res));
        }
    }

    elseif(!empty($_POST['username']) && $_POST['username'] != '' && $_POST['time'] == '')
    {
        $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_name='$username'";
        $userid = $GLOBALS['db']->getOne($sql_select);
        if(!empty($userid))
        {
            //构造查找的条件
            $where = "admin_id='$userid'";
        }
        else
        {
            $res['req_msg'] = true;
            $res['message'] = '搜索人姓名不存在';
            $res['timeout'] = 2000;

            die($json->encode($res));
        }
    }

    else
    {
        $where = "add_time>=$start_time AND add_time<=$end_time";
    }

    //根据传过来的参数查找对应的记录
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('work_summary').
        "WHERE $where";
    $work_list = $GLOBALS['db']->getAll($sql_select);

    foreach($work_list as &$v)
    {
        $v['add_time'] = date('Y-m-d H:i',$v['add_time']);
        $sql_select = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id=$v[admin_id]";
        $name = $GLOBALS['db']->getOne($sql_select);
        $v['admin_id'] = $name;
    }
    $smarty->assign('work_list',$work_list);
    $smarty->assign('posttime',$posttime);
    $smarty->assign('username',$username);
    $res['main'] = $smarty->fetch('work_summary_list.htm');
    $res['code'] = 1;

    die($json->encode($res));
}

/*--- 删除客服单条记录 ---*/
elseif($_REQUEST['act'] == 'service_personal_delete')
{
    $work_id = intval($_POST['work_id']);

    //根据传过来的work_id更新对应的工作表记录状态
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('work_summary').
        'SET is_delete=1 WHERE work_id='.$work_id;
    $result = $GLOBALS['db']->query($sql_update);

    if($result)
    {
        $res['obj'] = 'work_id';
        $res['code'] = 1;
        $res['id'] = $work_id;
        $res['req_msg'] = true;
        $res['message'] = '删除成功';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
    else
    {
        $res['req_msg'] = true;
        $res['message'] = '删除失败';
        $res['timeout'] = 2000;

        die($json->encode($res));    
    }

}

/*--- 删除个人推广记录 ---*/
elseif($_REQUEST['act'] == 'spread_delete')
{
    $spread_id = intval($_POST['spread_id']);

    //根据传过来的spread_id更新对应的推广表记录状态
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('spread').
        'SET is_delete=1 WHERE spread_id='.$spread_id;
    $result = $GLOBALS['db']->query($sql_update);

    if($result)
    {
        $res['obj'] = 'spread_id';
        $res['code'] = 1;
        $res['id'] = $spread_id;
        $res['req_msg'] = true;
        $res['message'] = '删除成功';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
    else
    {
        $res['req_msg'] = true;
        $res['message'] = '删除失败';
        $res['timeout'] = 2000;

        die($json->encode($res));    
    }
}

/*-- 账号管理子菜单 --*/
elseif ($_REQUEST['act'] == 'menu')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);

    die($smarty->fetch('left.htm'));
}

/*
 * 所有平台账号
 */
elseif ($_REQUEST['act'] == 'accounts')
{
    $sql_condition = ' WHERE is_check=0';
    if(admin_priv('all','',false))
    {
        $admin_id = 1;
    }
    elseif(admin_priv('accounts','',false))
    {
        $admin_id = 2;
    }

    if($admin_id)
    {
        $department_id = $_SESSION['role_id'];

        $sql_select = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('admin_user')
            .' WHERE status=1';

        $admin_list = $GLOBALS['db']->getAll($sql_select);

        $result = accounts_get($sql_condition);                 //所有账号信息 
        $accounts = $result['account_list'];

        $account_type = type_get();                             //帐号类型
        $subject = product_get();

        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('role');
        $smarty->assign('role',$GLOBALS['db']->getAll($sql_select));

        $account_list = array(); 

        for($i = 0; $i<count($account_type);$i++)
        {
            $account_list[$i]['label'] = $account_type[$i]['label'];
            if($accounts)
            {
                foreach($accounts AS $val)
                {
                    if($account_type[$i]['type_id'] == $val['type_id']) 
                    {
                        if($i == 0)
                        {
                            $account_list[$i]['class'] = 'style="display:block"';
                        }
                        else
                        {
                            $account_list[$i]['class'] = 'style="display:none"';
                        }

                        if($admin_id == 1)
                        {
                            if($val['belong'] != '')
                            {
                                $sql_select = 'SELECT user_name FROM '
                                    .$GLOBALS['ecs']->table('admin_user')
                                    ." WHERE user_id IN('".$val['belong']."')";
                                $belong = $GLOBALS['db']->getCol($sql_select);

                                $val['belong'] = implode(' | ',$belong);    
                            }
                            $account_list[$i]['account_list'][] = $val;
                        }
                        elseif($admin_id == 2)      //普通员工
                        {
                            $is_user = $is_empower = false;

                            if($val['belong'] != '')
                            {
                                $belong = explode("','",$val['belong']);
                                array_pop($belong);
                                if(!in_array($_SESSION['admin_id'],$belong))
                                {
                                    $val['password'] = '-';
                                }
                                else
                                {
                                    $is_empower = true;
                                }
                            }

                            if($val['user_id'] == $_SESSION['admin_id'])
                            {
                                $is_user = true;
                            }

                            if($is_user || $is_empower)
                            {
                                $sql_select = 'SELECT user_name FROM '
                                    .$GLOBALS['ecs']->table('admin_user')
                                    ." WHERE user_id IN('".$val['belong']."')";
                                $belong = $GLOBALS['db']->getCol($sql_select);

                                $val['belong'] = implode(' | ',$belong);

                                $account_list[$i]['account_list'][] = $val;
                            }
                        }
                    }
                    else
                    {
                        if($i != 0)
                            $account_list[$i]['class'] = 'style="display:none"';
                    }
                }
            }
            else
            {
                if($i == 0)
                    $account_list[$i]['class'] = 'style="display:block"';
                else
                    $account_list[$i]['class'] = 'style="display:none"';
            }
        }

        for($i = 0;$i<count($account_type);$i++)
        {
            if($i == 0)
                $account_type[$i]['class'] = 'btn_s';
            else
                $account_type[$i]['class'] = 'btn_a';
        }

        $filter = $result['filter'];
        $smarty->assign('admin_list',$admin_list);
        $smarty->assign('subject',$subject);
        $smarty->assign('department',$_SESSION['department']);
        $smarty->assign('admin_name',$_SESSION['admin_name']);
        $smarty->assign('department_id',$department_id);
        $smarty->assign('admin_id',$admin_id);
        $smarty->assign('account_type',$account_type);
        $smarty->assign('account_list',$account_list);
        $smarty->assign('filter',$filter);

        $res['main'] = $smarty->fetch('accounts_list.htm');
        die($json->encode($res));
    }
}

/*
 * 帐号按类型(type_id)分组 
 */
elseif ($_REQUEST['act'] == 'group_accounts')
{
    $type_id = intval($_GET['type_id']);
    $account_type = type_get();     //帐号类型 

    $sql_condition = " WHERE type_id=$type_id".
        ' AND is_check=0 ORDER BY content_updatetime Desc';     //按帐号内容(content_updatetime)排序

    $result = accounts_get($sql_condition);
    $accounts = $result['account_list'];

    if($_SESSION['role_id'] == NULL OR $_SESSION['role_id'] == 5)
    {
        $department = '超级管理员';
    }
    else
    {
        $department = departmentd_get($department_id);
        $department = $departmentd[0]['role_name']; 
    }

    $smarty->assign('admin_name',$_SESSION['admin_name']);
    $smarty->assign('admin_id',$_SESSION['role_id']);
    $smarty->assign('department',$department);
    $smarty->assign('accounts', $accounts);
    $smarty->assign('account_type',$account_type);

    $res['main'] = $smarty->fetch('accounts_list.htm');
    die($json->encode($res));
}

/*
 * 添加账号
 * 返回添加主界面
 */
elseif ($_REQUEST['act'] == 'addaccount')
{
    $type = type_get();                         //获取帐号类型
    $subject = product_get();                   //获取主题
    $passwordprotect = get_passwordprotect();   //密码保护
    $user = user_get();                         //帐号的使用者
    $admin = $_SESSION['admin_name'];           //提交者
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_purpose');
    $smarty->assign('purpose',$GLOBALS['db']->getAll($sql_select));

    $smarty->assign('adminid',$user[0]['user_id']);
    $smarty->assign('admin',$admin);

    $smarty->assign('role_list',get_role());
    $smarty->assign('user',$user);
    $smarty->assign('subject',$subject);
    $smarty->assign('type',$type);
    $smarty->assign('passwordprotect',$passwordprotect);

    $res['info'] = $smarty->fetch('add_account.htm'); 
    die($json->encode($res)); 
}

// 将要添加帐号添入数据库
elseif ($_REQUEST['act'] == 'insert_account')
{
    $account_name = mysql_real_escape_string($_REQUEST['account_name']);        //帐号名称
    $password = mysql_real_escape_string($_REQUEST['password']);          //密码
    $type_id = intval($_REQUEST['type_id']);              //帐号类型
    $email = trim($_REQUEST['email']);
    $department_id = intval($_REQUEST['department']);     //所属部门
    $user_id = intval($_REQUEST['user_id']);              //使用者
    $url = trim($_REQUEST['url']);                        //帐号登陆地址
    $usual_update = intval($_REQUEST['usual_update']);    //是否经常更新(是 1,不是 0);
    $handin = $_SESSION['admin_id'];                   //提交者id
    $inputTime = strtotime(date('Y-m-d H:i')) -8*3600; //提交时间
    $passwordProtectID = intval($_REQUEST['passwordProtect_id']);   //密码保护</p>
    $belong = trim($_REQUEST['belong_list']);              //授权查看者
    $is_vip = intval($_REQUEST['is_vip']);             //是否为会员
    $account_updatetime = strtotime(date('Y-m-d'));
    $phone = mysql_real_escape_string($_REQUEST['phone']);
    $remark = mysql_real_escape_string($_REQUEST['remark']);
    $updater = mysql_real_escape_string($_SESSION['admin_name']);
    $subject = intval($_REQUEST['subject']);
    $purpose = intval($_REQUEST['purpose']);

    $res = array('req_msg'=>true,'timeout'=>2000,'code'=>0);

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('account')
        .'(account_name,password,type_id,subject,email,department_id,user_id,url,usual_update,handin,inputTime,belong,password_protect_id,account_updatetime,updater,is_vip,purpose,phone,remark)'
        ." VALUES('$account_name','$password',$type_id,$subject,'$email',$department_id,$user_id,'$url',$usual_update,$handin,'$inputTime','$belong',$passwordProtectID,$inputTime,'$updater',$is_vip,$purpose,'$phone','$remark')";

    $result = $GLOBALS['db']->query($sql_insert);

    if($result)
    {
        $sql_select = 'SELECT label FROM '.$GLOBALS['ecs']->table('account_type').
            " WHERE type_id=$type_id";
        $type = $GLOBALS['db']->getOne($sql_select);
        $arrange = true;
        switch($type_id)
        {
        case 1 : $type= 'qq'; break;
        case 2 : $type= 'ppcrm'; break;
        case 3 : $type= 'qqcrm'; break;
        case 4 : $type= 'wangwang'; break;
        default : $arrange = false; break;
        }

        //如果未分配此账号
        if($arrange)
        {
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_work_account').
                " WHERE admin_id=$user_id";
            $result_count = $GLOBALS['db']->getOne($sql_select);
            if(!$result_count)
            {
                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('admin_work_account').
                    '(admin_id,'.$type.")VALUES($user_id,'$account_name')";

                $GLOBALS['db']->query($sql_insert);
            }
            elseif($type != '')
            {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_work_account').
                    " SET $type='$account_name' WHERE admin_id=$user_id";
                $GLOBALS['db']->query($sql_update);
            }
        }


        $res['code']=1;
        $res['message'] = '添加成功';    
    }
    else
    {
        $res['message'] = '添加失败';    
    }

    die($json->encode($res));
}

/*
 *账号删除
 */
elseif ($_REQUEST['act'] == 'delAccount')
{
    $account_id = $_GET['account_id'];
    $res = array('response_action' => 'delAccount'); 
    $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('account')." WHERE account_id=$account_id"; 

    $result= $GLOBALS['db']->query($sql_del);
    if($result)
    {
        $res['code'] = 1;       //执行成功
        die($json->encode($res));
    }
    else
    {
        $res['code'] = 0;   //执行失败
    }
}

/*
 *账号修改
 *返回修改界面
 */
elseif ($_REQUEST['act'] == 'updateAccount')
{
    $account_id = $_GET['account_id'];

    $sql_select = 'SELECT a.account_id,a.user_name AS account_name,a.email,u.user_name,a.password,p.passwordProtect,t.type_name,a.subject,a.url,d.role_name,a.content_updatetime,handin,is_check FROM '.
        $GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_type').
        ' AS t ON a.type_id=t.type_id  LEFT JOIN '.$GLOBALS['ecs']->table('role').
        ' AS d ON a.department_id =d.role_id'.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('account_passwordprotect').' AS p ON a.passwordProtect_id=p.passworProtect_id'.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' AS u ON a.user=u.user_id'.
        " WHERE a.account_id=$account_id";

    $account_info = $GLOBALS['db']->getAll($sql_select);    //获取将要修改的帐号信息 
    $account_info = $account_info[0];

    $type = type_get();     //类型
    $user =user_get();      //使用者
    $department = department_get();     //部门 
    $passwordprotect = get_passwordprotect();       //密码保护

    $smarty->assign('account_id',$account_id);
    $smarty->assign('user',$user);
    $smarty->assign('account_info',$account_info);
    $smarty->assign('department',$department);
    $smarty->assign('type',$type);
    $smarty->assign('passwordprotect',$passwordprotect);

    $res['main'] = $smarty->fetch('account_modify.htm');
    die($json->encode($res)); 
}

/*
 * 修改帐号行为
 * 管理员权限
 */
elseif ($_REQUEST['act'] == 'update_account')
{
    admin_priv(update_account);
    $account_id = intval($_POST['account_id']);
    $account_name = trim($_POST['account_name']);               //帐号使用者姓名
    $password = trim($_POST['password']);   //密码
    $type_id = intval($_POST['type_id']);                       //类型
    $email = trim($_POST['email']);
    $department_id = intval($_POST['department']);              //部门
    $user_id = intval($_POST['user']);                             //帐号使用者id
    $url = trim($_POST['url']);                                 //登陆地址
    $usual_update = intval($_POST['usual_update']);             //是否经常更新
    $handin = intval($_POST['admin_id']);                       //提交者id
    $subject = $_POST['subject'];                               //主题
    $passwordProtectID = intval($_POST['PassWordprotectID']);   //密保id
    $account_updatetime = strtotime(date('Y-m-d H:i')) -8*3600;             //帐号修改时间 
    $belong = trim($_REQUEST['belong']);
    $purpose = intval($_REQUEST['purpose']);                    //用途

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account').
        " SET account_name='$account_name',password='$password',type_id=$type_id,email='$email',department_id=$department_id,user_id=$user_id,url='$url',usual_update=$usual_update,updater=$handin,subject='$subject',passwordProtect_id=$passwordProtectID,account_updatetime=$account_updatetime,belong='$belong' WHERE account_id=$account_id"; 

    $result = $GLOBALS['db']->query($sql_update); 
    if($result)
    {
        $res['code'] = 1;   //成功
    }
    else
    {
        $res['code'] = 0;   //失败
    }

    die($json->encode($res));
}

/*
 *帐号搜索
 *超级管理员与部门管理员
 */
elseif ($_REQUEST['act'] == 'account_search')
{
    admin_priv(account_search);

    $type_id = $_GET['type_id'];        //帐号类型
    $subject = $_GET['subject'];        //帐号主题
    $user_name = $_GET['user_name'];    //使用者名称
    $status = $_GET['status'];          //帐号的状态 (有效0 被禁1 被删2 密码错误3 被盗4)

    $base = 0;                          //控件查询条件变量
    $sql_condition = "";                //查询条件

    /*获取查询条件*/
    if($type_id != "")
    {
        $sql_condition .= " WHERE a.type_id=$type_id";
        $base = 1;
    }

    if($user_name != "")
    {
        if($base > 0)
        {
            $sql_condition .= " AND a.user_name LIKE '%$user_name%'";
        }
        else
        {
            $sql_condition .= " WHERE a.user_name LIKE '%$user_name%'";
        }
    }

    if($subject != "")
    {
        if($base > 0)
        {
            $sql_condition .= " AND a.subject LIKE '%$subject%'";
        }
        else
        {
            $sql_condition .= " WHERE a.subject LIKE '%$subject%'";
            $base = 1;
        }
    }

    if($status != "")
    {
        if($base > 0)
        {
            $sql_condition .= ' AND a.usable IN('.$status.')'; 
        } 
        else
        {
            $sql_condition .= ' WHERE a.usable IN('.$status.')';
            $base = 1;
        }
    }

    if($base == 0)          //查询失败
    {
        $res['code'] = 0;
    }
    else
    {
        $accounts = accounts_get($sql_condition);
        $account_type = type_get();

        $smarty->assign('account_type',$account_type);
        $smarty->assign('accounts',$accounts);

        $res['main'] = $smarty->fetch('account_searchList.htm');
        $res['code'] = 1;
        die($json->encode($res));
    }
}

/*
 *核检
 */
elseif ($_REQUEST['act'] == 'checked')
{
    $account_id = intval($_GET['account_id']);
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account')." SET is_check = 1 WHERE account_id=$account_id";

    $result = $GLOBALS['db']->query($sql_update);
}

/*
 * score 0未评分 很差1 差2 合格3 好4 优秀5 
 * 帐号评分
 */
elseif ($_REQUEST['act'] == 'give_score')
{
    admin_priv('give_score');

    $score = intval($_GET['score']);                //帐号分数
    $get_scored_id = intval($_GET['user']);         //被评分人id
    $account_id = intval($_GET['account_id']);      //被评分帐号
    $account_updatetime = strtotime(date('Y-m-d H:i')) -8*3600; //帐号修改时间
    $scored_id = $_SESSION['admin_id'];                                 //评分人默认为部门负责人(登录者) $_SESSION['admin_id']

    //插入评分记录
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('account_score').
        '(account_id,score,scored_id,get_scored_id,score_time)'.
        " VALUES($account_id,$score,$scored_id,$get_scored_id,$account_updatetime)";

    $insResult = $GLOBALS['db']->query($sql_insert);

    //记录当前得分为最近得分
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account')." SET prescore=$score".
        " WHERE account_id=$account_id";

    $result = $GLOBALS['db']->query($sql_update);

    //计算总得分
    $sql_select = 'SELECT SUM(score) AS total_score FROM '.$GLOBALS['ecs']->table('account_score').
        " WHERE account_id=$account_id";
    $total_score = $GLOBALS['db']->getAll($sql_select);

    //修改总得分
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account')."SET score={$total_score[0]['total_score']}".
        " WHERE account_id=$account_id";
    $result = $GLOBALS['db']->query($sql_update);

    if($insResult > 0)
    {
        //评分时间
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account').
            " SET account_updatetime=$account_updatetime WHERE account_id=$account_id";
        $updResult = $GLOBALS['db']->query($sql_update);
        if($updResult > 0)
        {
            $res['code'] = "评分成功";
            die($json->encode($res));
        }
    }
    else
    {
        $res['code'] = "评分失败";
        die($json->encode($res));
    }

}


/*
 * score为0值表示未评分
 * 检查评分
 */
elseif ($_REQUEST['act'] == 'account_score')
{
    //登录者所属部门
    $department_id = $_SESSION['role_id'];

    $employees = get_employees($department_id);     //部门员工列表
    $user_id = $employees[0]["user"];               //第一个员工id
    $department = department_get($department_id);

    //获取每个部门第一个员工ID 并默认显示
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account');
    if($department_id != "")
    {
        $sql_select .= " WHERE department_id=$department_id";
    }
    $result = $GLOBALS['db']->getAll($sql_select); 

    $smarty->assign('employees',$employees);
    $smarty->assign('department',$department);
    $smarty->assign('accounts',$department_account);
    if($department_id == "")
    {
        $admin_name = '超级管理员';
    }
    $smarty->assign('admin_name',$admin_name);

    $res['main'] = $smarty->fetch('account_score.htm');
    die($json->encode($res));
}

/*
 *  帐号评分查看
 *  查看指定员工的帐号评分信息
 */
elseif ($_REQUEST['act'] == 'get_score')
{   
    $user_id = $_GET['user_id'];    // 帐号使用者
    $department_id = $_SESSION['role_id'];
    if($department_id == "")
    {
        $admin_name = "超级管理员";
    }
    $employees = get_employees($department_id);  //  指部门登陆本系统者所属部门id

    $sql_select = 'SELECT a.user,a.account_id,a.user_name,t.type_name,a.subject, a.url,FROM_UNIXTIME(a.account_updatetime,"%Y年%m月%d日%H时") AS account_updatetime,a.prescore,a.score FROM '
        . $GLOBALS['ecs']->table('account')
        .' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_type')
        .'AS t ON a.type_id=t.type_id'
        ." WHERE a.user=$user_id";
    $accounts = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('accounts',$accounts);
    $smarty->assign('admin_name',$admin_name);
    $smarty->assign('employees',$employees);    //部门员工

    $res['main'] = $smarty->fetch('account_score.htm');
    die($json->encode($res));
}

/*
 *  帐号评分明细
 */
elseif ($_REQUEST['act'] == 'score_info')
{
    $account_id = $_GET['account_id'];      //需要查看的帐号id    
    $sql_select = 'SELECT u.user_name AS admin_name,a.user_name FROM '.$GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS u ON a.user=u.user_id '.
        " WHERE a.account_id=$account_id "; //帐号管理员与用户名
    $info = $GLOBALS['db']->getAll($sql_select);

    //查询评分情况详细信息
    $sql_select = 'SELECT a.subject,FROM_UNIXTIME(s.score_time,"%Y年%m月%d日%H时") AS score_time,u.user_name AS give_score,s.score,s.explain FROM '
        .$GLOBALS['ecs']->table('account_score')
        .' AS s LEFT JOIN '.$GLOBALS['ecs']->table('admin_user')
        .' AS u ON s.scored_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('account')
        .' AS a ON s.account_id=a.account_id'
        ." WHERE s.account_id=$account_id ORDER BY s.score_time DESC";
    $score_info = $GLOBALS['db']->getAll($sql_select);

    //统计差评与好评次数
    $sql_select = 'SELECT score,COUNT(score) AS score_total,account_id FROM '.$GLOBALS['ecs']->table('account_score').
        " WHERE account_id=$account_id GROUP BY score";
    /*
    $sql_select = 'SELECT [1] AS much_fail, [5] AS excellent FROM(SELECT score FROM '.
        $GLOBALS['ecs']->table('account_score').
        ')piv Pivot( COUNT(score) FOR score IN(1,5)) AS scoredetail '
        ." WHERE accont_id=$account_id";
     */

    $score = array('much_fail'=>0,'excellent'=>0);
    $score_total = $GLOBALS['db']->getAll($sql_select);
    foreach($score_total AS $key=>$val)
    {
        if($val['score'] < 3)
        {
            $score['much_fail'] +=  $val['score_total']; 
        }
        elseif($val['score'] > 3)
        {
            $score['excellent'] += $val['score_total'];
        }
    }

    $smarty->assign('score',$score);
    $smarty->assign('info',$info);
    $smarty->assign('score_info',$score_info); 

    $res['main'] = $smarty->fetch('score_info.htm');
    die($json->encode($res));
}

/*
 * 添加新的帐号类型
 */
elseif ($_REQUEST['act'] == 'add_newtype')
{
    $type_name = mysql_real_escape_string($_GET['type_name']);      //新类型的名称

    //如果需要添加,判断新类型是否存在
    if($type_name !='')
    {
        $sql_select = 'SELECT count(*) AS exist FROM '.$GLOBALS['ecs']->table('account_type')." WHERE type_name = '$type_name'";

        $result = $GLOBALS['db']->getAll($sql_select);

        if($result[0]['exist'] != 0)       //帐号已经存在
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
    }
    else
    {

    }
}

/*
 * Q群管理
 * 返回Q群管理界面
 */
elseif ($_REQUEST['act'] == 'currentGroup')
{

    $account_qq = qq_get();             //获取 QQ号
    $qqgroupList= qqGroup_get($account_qq[0]['user_name']);     //获取Q群列表

    $smarty->assign('qq_group',$qqgroupList);
    $smarty->assign('account_qq',$account_qq);


    $res['main'] = $smarty->fetch('account_qqgroup.htm');
    die($json->encode($res));
}

//获取Q群信息
elseif ($_REQUEST['act'] == 'getQqGroup')
{
    $account_qq = qq_get();
    $qq = intval($_GET['qq']);

    $qqgroup = qqGroup_get($qq);        //指定qq下的Q群
    $account_qq = qq_get();

    $smarty->assign('account_qq',$account_qq);
    $smarty->assign('qq_group',$qqgroup);

    $res['main'] = $smarty->fetch('account_qqgroup.htm');
    die($json->encode($res));
}

/*
 * 添加Q群
 * 返回添加界面
 */
elseif ($_REQUEST['act'] == 'add_qqgroup')
{
    $account_qq = qq_get();
    $department = product_get();     //获取部门
    $user_name = user_get();            //Q群使用者

    $smarty->assign('account_qq',$account_qq); 
    $smarty->assign('department',$department);

    $res['main'] = $smarty->fetch('add_qqgroup.htm');
    die($json->encode($res));
}

//执行Q群添加
elseif ($_REQUEST['act'] == 'insert_qqgroup')
{
    $qq = mysql_real_escape_string($_REQUEST['qq']);                //所属QQ
    $qqgroup = mysql_real_escape_string($_REQUEST['qqgroup']);      //Q群帐号
    $subject = mysql_real_escape_string($_REQUEST['subject']);      //主题
    $strAdmin = mysql_real_escape_string($_REQUEST['strAdmin']);    //Q管理员列表
    $department_id = intval($_REQUEST['departmentID']);             //部门
    $updateTime = strtotime(date('Y-m-d H:i')) -8*3600;             //更新时间

    $arrAdmin = explode(",",$strAdmin);     
    array_unique($arrAdmin);

    $qqgroup_values = "";           //存放Q群
    $groupadmin_values = "";        //存放Q群管理员
    $total = count($arrAdmin);

    $sql_insert_groupadmin = 'INSERT INTO '.$GLOBALS['ecs']->table('account_groupadmin').
        '(admin_id,department_id,type_id,qqgroup) VALUES';

    for($i = 0; $i < $total-1; $i++)        //遍历管理员并转成字符串格式 (待优化,可用strsub())
    {
        if($i == $total-2)
        {
            $groupadmin_values .= "($arrAdmin[$i],$department_id,1,$qqgroup)";
        }
        else
        {
            $groupadmin_values .= "($arrAdmin[$i],$department_id,1,$qqgroup),"; 
        }
    }
    $groupadmin_values = substr($groupadmin_values,0,strlen($groupadmin_values)-1);
    $sql_insert_groupadmin .= $groupadmin_values; 
    $result = $GLOBALS['db']->query($sql_insert_groupadmin);

    //添加Q群
    $qqgroup_values = 'INSERT INTO '.$GLOBALS['ecs']->table('account_qqgroup').
        '(qq,qqgroup,subject,department_id,updateTime) VALUES'.
        "('$qq','$qqgroup','$subject',$department_id,$updateTime)";

    $result = $GLOBALS['db']->query($qqgroup_values);
    $group_id = $GLOBALS['db']->insert_id();        //获得刚刚添加的Q群Id
    $res['code'] = 1;

    //查找刚刚添加的Q群
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_qqgroup').
        " WHERE group_id=$group_id";
    $qq_group = $GLOBALS['db']->getAll($sql_select);

    //查找新添Q群的管理员
    $sql_select = 'SELECT q.groupadmin_id,q.admin_id,a.user_name FROM '.$GLOBALS['ecs']->table('account_groupadmin').
        ' AS q LEFT JOIN '.$GLOBALS['ecs']->table('account').
        ' AS a ON q.admin_id=a.account_id'.
        " WHERE q.qqgroup=$qqgroup";
    $groupAdmin = $GLOBALS['db']->getAll($sql_select);

    //获取管理名称
    $sql_select = 'SELECT a.user_name FROM '.$GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_groupadmin').
        ' AS q ON a.account_id=q.admin_id'.
        " WHERE q.qqgroup=$qqgroup";
    $result = $GLOBALS['db']->getAll($sql_select);

    $adminTotal = count($result);
    $admin_idlist = "";
    for($i = 0;$i < $adminTotal-1;$i++)
    {
        $admin_idlist .= $result[$i]['user_name'].',';
    }

    //将Q群管理员id加入Q群表 
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account_qqgroup')." SET admin_id='$admin_idlist'".
        " WHERE qqgroup=$qqgroup";
    $result = $GLOBALS['db']->query($sql_update);

    if($res['code'] == 1)
    {
        $smarty->assign('qq_group',$qq_group);
        $smarty->assign('groupAdmin',$groupAdmin);

        $res['main'] = $smarty->fetch('qq_grouplist.htm');
        die($json->encode($res));
    }
}

//验证将添加Q群是否存在
elseif ($_REQUEST['act'] == 'cheQqgroup')
{
    $qq = mysql_real_escape_string($_GET['qq']);
    $qqgroup = mysql_real_escape_string($_GET['qqgroup']);

    $sql_select = 'SELECT count(*) AS total FROM '.$GLOBALS['ecs']->table('account_qqgroup').
        " WHERE qq='$qq' AND qqgroup='$qqgroup'";

    $result = $GLOBALS['db']->getAll($sql_select);
    echo $result[0]['total'];

    if($result[0]['total'] == 1)
    {
        $res['code'] = 1; //不可以添加
    }
    else
    {
        $res['code'] = 0;
    }

}

/*
 * Q群管理员详细信息
 */
elseif ($_REQUEST['act'] == 'getAdminInfo')
{
    $qqgroup = mysql_real_escape_string($_GET['qqgroup']);      //Q群号
    $strAdmin = mysql_real_escape_string($_GET['strAdmin']);    //管理员

    $arAdmin = array();
    //将管理员id所组成的字符串分割
    $arAdmin = explode(',',$strAdmin);
    $length = count($arAdmin);
    $adminlist = "";

    for($i = 0; $i < $length; $i++)
    {
        if($i == $length-1)
        {
            $adminlist .= "'$arAdmin[$i]'"; 
        }
        else
        {
            $adminlist .= "'$arAdmin[$i]',";
        }
    }

    $sql_select = 'SELECT a.email,a.user_name AS qq,u.user_name,d.role_name FROM '.$GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS u ON a.user=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('role').
        ' AS d ON a.department_id=d.role_id '.
        ' WHERE a.user_name IN('.$adminlist.')'.' AND a.type_id=1';

    $result = $GLOBALS['db']->getAll($sql_select);

    if($result)
    {
        $smarty->assign('qqgroup',$qqgroup);
        $smarty->assign('adminInfo',$result);

        $res['code'] = 1;
        $res['main'] = $smarty->fetch('groupAdminInfo.htm');
        die($json->encode($res));
    }
}

/*
 * 删除Q群
 * 删除Q群时同时删除Q群管理员表中对就值
 */
elseif ($_REQUEST['act'] == 'delgroup')
{
    $group_id = intval($_GET['group_id']);
    $qqgroup = mysql_real_escape_string($_GET['qqgroup']);

    $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('account_qqgroup')." WHERE group_id=$group_id";

    $result = $GLOBALS['db']->query($sql_del);

    if($result == 1)
    {
        $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('account_groupadmin')." WHERE qqgroup=$qqgroup";

        $result = $GLOBALS['db']->query($sql_del);

        if($result)
        {
            echo 1;     //删除成功
        }
        else
        {
            echo 0;     //删除失败
        }
    }
}

//修改Q群
elseif ($_REQUEST['act'] == 'updgroup')
{
    $group_id = $_GET['group_id'];
    $qqgroup = $_GET['qqgroup']; 

    //获取Q群信息
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_qqgroup').
        " WHERE group_id=$group_id";
    $qqgroup_info = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('qqgroup_info',$qqgroup_info);
    $smarty->assign('qqgroup',$qqgroup_info[0]['qqgroup']);
    $smarty->assign('admin_list',$qqgtoup_info[0]['admin_id']);
    $res['main'] = $smarty->fetch('modify_group.htm');

    die($json->encode($res));
}

//返回QQ于Q群管理员的下拉控件
elseif ($_REQUEST['act'] == 'getAdminqq')
{
    $user_name = mysql_real_escape_string($_GET['user_name']);
    $id = intval($_GET['id']);

    $sql_select = 'SELECT distinct user_name,account_id FROM '.$GLOBALS['ecs']->table('account').
        " WHERE type_id=1 AND user_name LIKE '%$user_name%'";
    $admin_qq = $GLOBALS['db']->getAll($sql_select);

    $res['code'] = 1;
    if($id == 2)        //Q群管理员列表
    {
        $smarty->assign('qqAdminlist',$admin_qq);
        $res['main'] = $smarty->fetch('qqAdminlist.htm');
    }
    else                //QQ列表
    { 
        $smarty->assign('account_qq',$admin_qq);
        $res['main'] = $smarty->fetch('qqlist.htm');
    }

    die($json->encode($res));
}

/*
 * 查找Q群
 */
elseif ($_REQUEST['act'] == 'search_qqgroup')
{
    $admin =mysql_real_escape_string($_GET['admin']);       //Q群管理员
    $qqgroup =mysql_real_escape_string($_GET['qqgroup']);
    $qq = $_GET['qq'];
    $subject = mysql_real_escape_string($_GET['subject']);

    $sql_condition = "";
    $account_qq = qq_get();
    $base = 0;

    $smarty->assign('account_qq',$account_qq);

    //获取查询条件 
    if($admin != "")
    {
        $sql_condition .= " WHERE q.admin_id LIKE '%$admin%'";
        $base = 1;
    }
    elseif($qqgroup != "")
    {
        if($base > 0)
        {
            $sql_condition .= " AND qqgroup LIKE '%$qqgroup%'";
        }
        else
        {
            $sql_condition .= " WHERE qqgroup LIKE '%$qqgroup%'";
            $base = 1;
        }
    }
    elseif($qq != "")
    {
        if($base > 0) {
            $sql_condition .= " AND qq LIKE '%$qq%'";
        }
        else
        {
            $sql_condition .= " WHERE qq LIKE '%$qq%'";
            $base = 1;
        }
    }
    elseif($subject != "")
    {
        if($base > 0 )
        {
            $sql_condition .= " AND subject LIKE '%$subject%'";
        }
        else
        {
            $sql_condition .= " WHERE subject LIKE '%$subject%'";
            $base = 1;
        }
    }
    if($base != 0)
    {
        $qq_grouplist = qq_group_get($sql_condition);
        $smarty->assign('qq_group',$qq_grouplist);

        $res['main'] = $smarty->fetch('account_qqgroup.htm');
        die($json->encode($res));
    }
    else
    {
        $res['message'] = '请输入查询条件';
    }
}

/*
 * 查看帐号更新
 * 部门管理员只能看到本部门的账号更新
 * 超级管理员可以看到所有
 */
elseif ($_REQUEST['act'] == 'update_view')
{
    if(admin_priv('update_view','',false))
    {
        $behave = intval($_GET['behave']);   //判断查看状态 未更新 已经更新 本周更新)

        $dateNow = strtotime(date('Y-m-d H:i')) -8*3600; 
        $weekarray = array('日','一','二','三','四','五','六'); 
        $nowData .=date('Y年m月d日').'  星期'.$weekarray[date('w')]; 
        $smarty->assign('nowDate',$nowData);
        $smarty->assign('admin_name',$admin_name);
        $weekday = date('w');

        //查看用户所在部门
        $department_id = $_SESSION['role_id'];

        //查询该部门未更新帐号,条件为(strtotime(data('Y-m-d')) - account_updatetime)>7
        if(!$department_id)
        {
            $sql_where = "";
        }
        else
        {
            $sql_where = " AND a.department_id=$department_id";
        }

        /* 如果没有选择则默认显示未更新 */
        switch($behave)
        {
        case 0:         //未更新
            $sql_condition = " WHERE ($dateNow-a.account_updatetime)/86400>=7 AND a.updateTime LIKE '%$weekday%'".$sql_where.
                ' AND a.usable=0';
            break;

        case 1:         //已经更新
            $sql_condition = " WHERE ($dateNow-a.account_updatetime)/86400<=7".$sql_where.
                ' AND a.usable=0';         
            break;

        case 2:         //本周更新
            break;

        default :       //未更新
            $sql_condition = " WHERE ($dateNow-a.account_updatetime)/86400>=7 AND a.updateTime LIKE '%$weekday%'".$sql_where.
                ' AND a.usable=0';
            break;
        }

        /*帐号更新情况 : 0 未更新, 1 已更新, 本周更新*/

        $result = get_accountToupdate($sql_condition);

        $smarty->assign('update_view',$result);
        $smarty->assign('status',$behave);
        $smarty->assign('admin_name',$_SESSION['admin_name']);

        $res['main'] = $smarty->fetch('account_update.htm');
        die($json->encode($res));
    }
}    

/*
 * 更新时间设置:
 */
elseif ($_REQUEST['act'] == 'updatetime_config')
{
    $updateTime = mysql_real_escape_string($_GET['updateTimelist']);
    $account_id = intval($_GET['account_id']);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account').
        " SET updateTime='$updateTime' WHERE account_id=$account_id";

    $result = $GLOBALS['db']->query($sql_update);

    if($result == 1)
    {
        $res['code'] = 1;
    }
    else
    {
        $res['code'] = 0;
    }

    die($json->encode($res));
}

//修改帐号密码
elseif ($_REQUEST['act'] == 'modifyPassword')
{
    $behave = intval($_GET['behave']);
    $account_id = intval($_GET['account_id']);
    $password = mysql_real_escape_string($_GET['password']);
    $user_name = mysql_real_escape_string($_GET['user_name']);

    $account['account_id'] = $account_id;
    $account['password'] = $password;
    $account['user_name'] = $user_name;

    if($behave == 1)     //返回密码修改操作界面 
    {
        $smarty->assign('account',$account);
        $res['main'] = $smarty->fetch('modifyPwd.htm');
    }
    else                //执行密码修改操作
    {
        $newpassword = mysql_real_escape_string($_GET['newPassword']);
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account').
            " SET password=$newpassword";
        $result = $GLOBALS['db']->query($sql_update);
        if($result)
        {
            $res['code'] = 1;
            $res['message'] = '密码修改成功';
        }
        else
        {
            $res['code'] = 0;
            $res['message'] = '密码修改失败';
        }
    }
    //执行修改帐号密码操作
    die($json->encode($res));
}

//检查帐号是否可以添加
elseif ($_REQUEST['act'] == 'add_able')
{
    $type_id = $_GET['type_id'];
    $user_name = $_GET['user_name'];

    $sql_select = 'SELECT COUNT(*) AS exist FROM '.$GLOBALS['ecs']->table('account')
        ." WHERE type_id=$type_id AND user_name=$user_name";
    $result = $GLOBALS['db']->getAll($sql_select);

    if($result[0]['exist'] == 1)     
    {
        echo 1;   //不可以添加
    }
    else
    {
        echo 0;   //可以添加
    }

}
//获取修改账号模块
elseif ($_REQUEST['act'] == 'modify_account')
{
    $account_id = intval($_REQUEST['account_id']); 
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account')." WHERE account_id=$account_id";

    $account_info = $GLOBALS['db']->getAll($sql_select);
    $account_info = $account_info[0];
    if($account_info['belong'] != '')
    {
        $sql_select = 'SELECT user_name,user_id FROM '
            .$GLOBALS['ecs']->table('admin_user')
            ." WHERE user_id IN('".$account_info['belong']."')";
        $arr_belong_name = $GLOBALS['db']->getAll($sql_select);
        $account_info['belong'] = '';
        foreach($arr_belong_name AS $val)
        {
            $account_info['belong'] .= '<span name="'.$val['user_id'].'" id="'.$val['user_id'].'"><img src="images/0.gif" onclick="delBelong(this)">'.$val['user_name'].'</span>';
        }
    }

    $res['code'] = 0;

    if($account_info)
    {
        $res['code'] = 1;
    }

    $smarty->assign('account_info',$account_info);

    $type = type_get();                         //获取帐号类型
    $department = product_get();                //获取部门
    $passwordprotect = get_passwordprotect();   //密码保护
    $user = user_get();                         //帐号的使用者
    $admin = $_SESSION['admin_name'];           //提交者
    $subject = product_get();

    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_purpose');

    $smarty->assign('purpose',$GLOBALS['db']->getAll($sql_select));
    $smarty->assign('user',$user);
    $smarty->assign('department',$department);
    $smarty->assign('type',$type);
    $smarty->assign('passwordprotect',$passwordprotect);
    $smarty->assign('subject',$subject);
    $smarty->assign('role_list',get_role());
    $smarty->assign('modify_do',1);
    $smarty->assign('admin_id',$_SESSION['admin_id']);
    $smarty->assign('subject',$subject);

    $res['info']         = $smarty->fetch('add_account.htm');
    $res['type_id']      = $account_info['type_id'];
    $res['account_name'] = $account_info['account_name'];

    die($json->encode($res));
}

//推广活动
elseif($_REQUEST['act'] == 'activity'){

    $require_time           = $_SERVER['REQUEST_TIME'];
    $current_time           = date('Y-m',$_SERVER['REQUEST_TIME']);
    $_REQUEST['start_time'] = strtotime(date('Y-m-1'),$_SERVER['REQUEST_TIME']);
    $_REQUEST['end_time']   = strtotime(date('Y-m-t'),$_SERVER['REQUEST_TIME']);
    $brand_list             = get_brand_id();
    $result                 = get_activity();
    extract($result);
    $activity_num           = count($activity_list);

    $smarty->assign('brand_list',$brand_list);
    $smarty->assign('activity_list',$activity_list);
    $smarty->assign('current_time',$current_time);
    $smarty->assign('activity_num',$activity_num);
    $smarty->assign('filter',$filter);

    $forms_div        = $smarty->fetch('sch_activity.htm');
    $smarty->assign('forms_div',$forms_div);

    $res['main']      = $smarty->fetch('spread_activity.htm');
    $res['forms_div'] = $forms_div;
    die($json->encode($res));
}

// 获得平台活动列表
elseif ($_REQUEST['act'] == 'sch_activity'){
    $result = get_activity();
    extract($result);

    $smarty->assign('activity_list',$activity_list);
    $smarty->assign('filter',$filter);
    $smarty->assign('activity_list_div',true);

    $res['response_action'] = 'search_service';
    $res['main']            = $smarty->fetch('spread_activity_div.htm');

    die($json->encode($res));
}

// 添加推广活动
elseif($_REQUEST['act'] == 'add_more_activity'){
    if(isset($_REQUEST['view']) && $_REQUEST['view']){

        $sql_select = 'SELECT activity_id FROM '.$GLOBALS['ecs']->table('spread_activity').' WHERE status=2';
        $result     = $GLOBALS['db']->getOne($sql_select);
        $role_id    = intval($_SESSION['role_id']);

        if ($result) {
            $insert_id  = $result;
        }else{
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('spread_activity') 
                .'(role_id,admin_id,status,add_time)VALUES('
                ."$role_id,{$_SESSION['admin_id']},2,{$_SERVER['REQUEST_TIME']})";

            $GLOBALS['db']->query($sql_insert);
            $insert_id   = $GLOBALS['db']->insert_id();
        }

        $smarty->assign('brand_list',get_brand_id());
        $smarty->assign('activity_id',$insert_id);
        $smarty->assign('goods_list_div',true);

        $forms_div = $smarty->fetch('add_activity.htm');

        $res['main']      = $smarty->fetch('spread_activity_div.htm');
        $res['forms_div'] = $forms_div;
        die($json->encode($res));
    }

    $confirm = mysql_real_escape_string($_REQUEST['confirm']);
    $date    = mysql_real_escape_string($_REQUEST['date']);

    if($confirm){
        $admin_id       = $_SESSION['admin_id'];
        $role_id        = intval($_SESSION['role_id']);
        $start_time     = strtotime($_REQUEST['start_time']);
        $end_time       = strtotime($_REQUEST['end_time']);
        $activity_name  = mysql_real_escape_string($_REQUEST['activity_name']);
        $act_describe   = mysql_real_escape_string($_REQUEST['act_describe']);
        $gifts_sn       = mysql_real_escape_string($_REQUEST['gifts_sn']);
        $goods_num      = intval($_REQUEST['number']);
        $gifts_num      = intval($_REQUEST['gifts_num']);
        $order_money    = intval($_REQUEST['money']);
        $res['req_msg'] = true;
        $res['timeout'] = 2000;

        $sql_select     = 'SELECT activity_id FROM '.$GLOBALS['ecs']->table('spread_activity')
            .' WHERE status=2';
        $activity_id    = $GLOBALS['db']->getOne($sql_select);

        if(!$activity_id){
            $res['message'] = '创建活动失败';
            die($json->encode($res));
        }else{
            $sql_select = 'SELECT act_id FROM '.$GLOBALS['ecs']->table('goods_activity')." WHERE act_id=$activity_id";
            if(!$GLOBALS['db']->getOne($sql_select)){
                $res['message'] = '你没有选择参与活动的商品';
                die($json->encode($res));
            }
        }

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('spread_activity')
            ." SET admin_id=$admin_id,role_id=$role_id,start_time=$start_time,end_time=$end_time,activity_name='$activity_name',act_describe='$act_describe',gifts_sn='$gifts_sn',gifts_num=$gifts_num,goods_num=$goods_num,order_money=$order_money,status=1 WHERE activity_id=$activity_id";

        $result = $GLOBALS['db']->query($sql_update);

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods_activity')
            ." SET start_time=$start_time,end_time=$end_time WHERE act_id=$activity_id";
        $GLOBALS['db']->query($sql_update);

        if($result){
            $res['code']    = true;
            $res['message'] = '成功创建活动';
        }
    }

    die($json->encode($res));
}

//所得商品
elseif($_REQUEST['act'] == 'get_goods_list'){

    $goods_sn     = mysql_real_escape_string($_REQUEST['goods_sn']); 
    //临时记录参加活动商品
    if(isset($_REQUEST['join_goods']) && mysql_real_escape_string($_REQUEST['join_goods'])){

        $activity_id  = intval($_REQUEST['activity_id']);
        $is_join      = intval($_REQUEST['is_join']); 
        $tr_index     = intval($_REQUEST['tr_index']); 
        $sql_select   = 'SELECT goods_sn FROM '.$GLOBALS['ecs']->table('goods_activity')." WHERE act_id=$activity_id";
        $goods_sn_list = $GLOBALS['db']->getCol($sql_select);

        if ($is_join) {
            if(!in_array($goods_sn,$goods_sn_list)){
                $sql = "INSERT INTO ".$GLOBALS['ecs']->table('goods_activity').
                    '(act_id,goods_sn)VALUES('."$activity_id,'$goods_sn')";
            }
        }else{
            if($goods_sn_list){
                $sql = 'DELETE FROM '.$GLOBALS['ecs']->table('goods_activity').
                    " WHERE goods_sn='$goods_sn'";
            }
        }

        if($sql){
            $res['code'] = $GLOBALS['db']->query($sql);
        }else{
            $res['code'] = false; 
        }

        $res['tr_index'] = $tr_index;
        $res['is_join']  = $is_join;

        die($json->encode($res));
    }

    $brand_id    = intval($_REQUEST['brand_id']); 
    $keyword     = mysql_real_escape_string($_REQUEST['keyword']);
    $activity_id = intval($_REQUEST['activity_id']);

    $where     = ' WHERE is_delete=0 AND g.goods_sn=s.goods_sn AND g.brand_id=b.brand_id '; 
    $condition = '';

    if($activity_id != 0){
        $condition = "&activity_id=$activity_id";
    }

    if($brand_id){
        $where     .= " AND g.brand_id=$brand_id ";
        $condition .= "&brand_id=$brand_id";
    }

    if($goods_sn){
        $where     .= " AND g.goods_sn='$goods_sn' ";
        $condition .= "&goods_sn=$goods_sn";
    }

    if($keyword != ''){
        $where     .= " AND (brand_name LIKE '%$keyword%' OR g.goods_name LIKE '%$keyword%')";
        $condition .= "&keyword=$keyword";
    }

    $filter['page']      = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $filter['page_size'] = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 15;


    $sql_select   = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('goods')
        .' g, '.$GLOBALS['ecs']->table('stock_goods')
        .' s, '.$GLOBALS['ecs']->table('brand')
        .' b '.$where.' GROUP BY s.goods_sn';

    $result                 = $GLOBALS['db']->getCol($sql_select); 
    $filter['record_count'] = count($result);
    $filter['page_count']   = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

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
        'act'           => 'get_goods_list',
    );

    $limit = ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

    $sql_select = 'SELECT g.goods_id,g.goods_name,g.goods_sn,SUM(s.quantity) AS quantity FROM '.$GLOBALS['ecs']->table('goods').' g, '
        .$GLOBALS['ecs']->table('stock_goods').' s, '.$GLOBALS['ecs']->table('brand')
        .' b '.$where.' GROUP BY s.goods_sn '.$limit;

    $goods_list = $GLOBALS['db']->getAll($sql_select);

    foreach($goods_list as &$val){
        if(!$val['quantity']){
            $val['quantity'] = 0;
        }
    }

    $sql_select   = 'SELECT goods_sn FROM '.$GLOBALS['ecs']->table('goods_activity')." WHERE act_id=$activity_id";

    $joined_goods = $GLOBALS['db']->getCol($sql_select);

    $smarty->assign('joined_goods',$joined_goods);
    $smarty->assign('goods_list',$goods_list);
    $smarty->assign('goods_list_div',true);
    $smarty->assign('filter',$filter);
    $smarty->assign('activity_id',$activity_id);

    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('spread_activity_div.htm');

    die($json->encode($res));
}

// 设置活动优惠内容
elseif($_REQUEST['act'] == 'set_privilege'){
    $privilege = mysql_real_escape_string($_REQUEST['privilege']);

    if($privilege != ''){
        $brand_list = get_brand_id();
    }
}

//查看活动商品
elseif($_REQUEST['act'] == 'get_act_goods'){
    $act_id = intval($_REQUEST['act_id']);
    $res = array(
        'req_msg' => true,
        'code' => false
    );

    if($act_id != 0){
        $sql_select = 'SELECT g.goods_name,g.goods_sn FROM '.$GLOBALS['ecs']->table('goods_activity')
            .' ga,'.$GLOBALS['ecs']->table('goods').'g '
            ." WHERE ga.goods_sn=g.goods_sn AND ga.act_id=$act_id";
        $goods_list = $GLOBALS['db']->getAll($sql_select);

        $sql_select = 'SELECT g.goods_name,gifts_num FROM '.$GLOBALS['ecs']->table('spread_activity')
            .' sa,'.$GLOBALS['ecs']->table('goods')
            ." g WHERE sa.gifts_sn=g.goods_sn AND sa.activity_id=$act_id";

        $gifts_info     = $GLOBALS['db']->getRow($sql_select);
        $res['message'] = '<table width=100% cellpadding=0 cellspacing=0>';

        if($goods_list){
            foreach($goods_list as $val){
                $res['message'] .= "<tr><td>{$val['goods_sn']}</td><td>{$val['goods_name']}</td></tr>";
            }

            $res['message'] .= "<tr><td colspan=2><font color=red>赠品：</font>{$gifts_info['goods_name']}   <font color=red>数量：{$gifts_info['gifts_num']}</font></td></tr></table>";
        }
    }else{
        $res['message'] = '未能找到商品信息'; 
    }

    die($json->encode($res));
}

//撤销活动
elseif($_REQUEST['act'] == 'del_act'){
    $act_id = intval($_REQUEST['val']);
    $res = array(
        'req_msg' => true,
        'code'    => false,
        'message' => '',
        'timeout' => 2000
    );

    if($act_id != 0){
        $sql_update     = 'UPDATE '.$GLOBALS['ecs']->table('spread_activity').' SET status = 0'." WHERE activity_id = $act_id";
        $res['code']    = $GLOBALS['db']->query($sql_update);
    }

    if($res['code']){
        $res['tr_index']   = intval($_REQUEST['tr_index']);
        $res['table_name'] = mysql_real_escape_string($_REQUEST['table_name']);
        $res['message']    = '删除成功';
    }else{
        $res['message'] = '删除失败';
    }

    die($json->encode($res));
}

// 绩效奖励
elseif($_REQUEST['act'] == 'award_manager'){

    $sql_select      = 'SELECT award_item_id,award_item_name FROM '.$GLOBALS['ecs']->table('award_item').' WHERE status=1 ORDER BY level DESC';
    $award_item_list = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('role_list',get_role());
    $smarty->assign('award_item_list',$award_item_list);
    $smarty->assign('current_time',date('Y年m月',$_SERVER['REQUEST_TIME']));
    $smarty->assign('award_list_div',$smarty->fetch('award_list_div.htm'));

    $res['main'] = $smarty->fetch('award_manager.htm');

    die($json->encode($res));

}

//统计绩效成绩
elseif ($_REQUEST['act'] == 'statistics_performance'){
    if(admin_priv('all','',false)){
        $platform      = intval($_REQUEST['platform']);
        $group_id      = intval($_REQUEST['group_id']);
        $award_item_id = intval($_REQUEST['award_item_id']);
        $sql_append    = '';

        //绩效评分类别
        $statistics_info = get_statistics_info($platform,$group_id,$award_item_id );
    }

    $res['main'] = $smarty->fetch('award_list_div.htm');
    die($json->encode($res));
}

/*任务管理*/
elseif($_REQUEST['act'] == 'tasks_site'){
    if(admin_priv('tasks_site','',false)){
        $where = ' WHERE status=1 ';
        if(!admin_priv('all','',false)){
            if($_SESSION['group_id']){
                $where .= " AND (platform={$_SESSION['role_id']} OR group_id={$_SESSION['group_id']})";
            }else{
                $where .= " AND platform={$_SESSION['role_id']}";
            }
        }

        $result           = get_tasks_list($where);
        $tasks_list       = $result['result'];
        $filter           = $result['filter'];

        $period_list = get_period_list();
        $group_list  = get_only_group_list();
        $tasks_list  = format_tasks_list($tasks_list,$period_list,$group_list);

        if(admin_priv('all','',false)){
            $platform_list   = get_role_list(1);
            $role_group_list = get_role_group_list();

            $smarty->assign('platform_list',$platform_list);
            $smarty->assign('role_group_list',$role_group_list);
        }

        $min_date = date('d',$_SERVER['REQUEST_TIME']); //任务期限不能小于今天

        foreach($filter as $key=>$val){
            $smarty->assign($key,$val);
        }

        //$smarty->assign('filter',$filter);
        $smarty->assign('min_date',$min_date);
        $smarty->assign('period_list',$period_list);
        $smarty->assign('tasks_list',$tasks_list);
        $smarty->assign('dst_script','performance');
        $smarty->assign('foot_page',$smarty->fetch('page_fragment.htm'));
        $smarty->assign('tasks_site_form',$smarty->fetch('tasks_site_form.htm'));
        $smarty->assign('tasks_list_div',$smarty->fetch('tasks_list_div.htm'));

        $res['main'] = $smarty->fetch('tasks_site.htm');
        die($json->encode($res));
    }
}

//添加任务
elseif($_REQUEST['act'] == 'add_tasks'){

    $task_name     = mysql_real_escape_string($_REQUEST['task_name']);
    $deadline      = mysql_real_escape_string($_REQUEST['deadline']);
    $period_id     = intval($_REQUEST['period_id']);
    $purpose_value = intval($_REQUEST['purpose_value']);
    $platform      = intval($_REQUEST['platform']);
    $group_id      = intval($_REQUEST['group']);
    $mod_time      = $_SERVER['REQUEST_TIME'];
    $column_name   = '';

    if(stristr($task_name,'销量')){
        $column_name = 'final_amount'; 
    }elseif(stristr($task_name,'服务')){
        $column_name = 'service';
    }

    /*默认每天*/
    if($deadline == '' && $period_id == 0){
        $period_id = 1;
        $deadline  = 0;
    }elseif($period_id != 0){
        $deadline = 0;
    }

    if($deadline != '' && $period_id == 0){
        $deadline = strtotime($deadline);
    }

    $res = array(
        'req_msg' => true,
        'timeout' => 2000,
        'message' => '',
        'code'    => false
    );

    /*查看是否有重复的添加*/
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('tasks').
        " WHERE task_name='$task_name' AND purpose_value=$purpose_value AND (platform=$platform OR group_id=$group_id)";
    $is_exist = $GLOBALS['db']->getOne($sql_select);
    if($is_exist){
        $res['message'] = '已经存在相同规则,请选择修改操作';
        die($json->encode($res));
    }

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('tasks').
        '(task_name,purpose_value,mod_time,platform,group_id,period_id,deadline,column_name)VALUES('.
        "'$task_name',$purpose_value,$mod_time,$platform,$group_id,$period_id,$deadline,'$column_name')";

    $res['code'] = $GLOBALS['db']->query($sql_insert);

    if($res['code'] == true){

        $period_list = get_period_list();
        $group_list  = get_only_group_list();
        $result      = get_tasks_list();
        $tasks_list  = $result['result'];
        $filter      = $result['filter'];
        $tasks_list  = format_tasks_list($tasks_list,$period_list,$group_list);

        foreach($filter as $key=>$val){
            $smarty->assign($key,$val);
        }

        $res['message'] = '添加成功';

        $smarty->assign('tasks_list',$tasks_list);
        $res['main']    = $smarty->fetch('tasks_list_div.htm');
    }else{
        $res['message'] = '添加失败';
    }

    die($json->encode($res));
}

/*修改删除任务管理*/
elseif($_REQUEST['act'] == 'control_task'){
    $behave   = mysql_real_escape_string($_REQUEST['behave']);
    $task_id  = intval($_REQUEST['task_id']);
    $tr_index = intval($_REQUEST['tr_index']);

    switch($behave){
    case 'mod_form':
        $res['tr_index'] = $tr_index;
        $res['task_id']  = $task_id;
        $period_list     = get_period_list();
        $group_list      = get_only_group_list();

        if(admin_priv('all','',false)){
            $platform_list   = get_role_list(1);
            $role_group_list = get_role_group_list();

            $smarty->assign('platform_list',$platform_list);
            $smarty->assign('role_group_list',$role_group_list);
        }

        $sql_select = 'SELECT task_id,task_name,purpose_value,platform,group_id,period_id,deadline FROM '.$GLOBALS['ecs']->table('tasks')." WHERE task_id=$task_id";
        $task_info = $GLOBALS['db']->getRow($sql_select);

        if(!empty($task_info['deadline'])){
            $task_info['deadline'] = date('Y-m-d',$task_info['deadline']);
        }

        $smarty->assign('task_info',$task_info);
        $smarty->assign('behave',$behave);
        $smarty->assign('period_list',$period_list);
        $res['main'] = $smarty->fetch('tasks_site_form.htm');
        break;
    case 'mod_done':
        $task_name          = mysql_real_escape_string($_REQUEST['task_name']);
        $deadline           = mysql_real_escape_string($_REQUEST['deadline']);
        $period_id          = intval($_REQUEST['period_id']);
        $purpose_value      = intval($_REQUEST['purpose_value']);
        $platform           = intval($_REQUEST['platform']);
        $group_id           = intval($_REQUEST['group']);
        $mod_time           = $_SERVER['REQUEST_TIME'];
        $column_name   = '';

        if(stristr($task_name,'销量')){
            $column_name = 'final_amount'; 
        }elseif(stristr($task_name,'服务')){
            $column_name = 'service';
        }

        if($task_id != 0){
            if($deadline == '' && $period_id == 0){
                $period_id = 1;
                $deadline  = 0;
            }elseif($period_id != 0){
                $deadline = 0;
            }

            if($deadline != '' && $period_id == 0){
                $deadline = strtotime($deadline);
            }

            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('tasks').
                " SET task_name='$task_name',deadline=$deadline,period_id=$period_id,".
                "purpose_value=$purpose_value,platform=$platform,group_id=$group_id,".
                "mod_time=$mod_time,column_name='$column_name' WHERE task_id=$task_id";
            $res['code'] = $GLOBALS['db']->query($sql_update);

            $res['timeout'] = 2000;
            if($res['code']){
                $res['message'] = '修改成功';
                $result         = get_tasks_list();
                $tasks_list     = $result['result'];
                $filter         = $result['filter'];

                foreach($filter as $key=>$val){
                    $smarty->assign($key,$val);
                }

                $period_list    = get_period_list();
                $group_list     = get_only_group_list();
                $tasks_list     = format_tasks_list($tasks_list,$period_list,$group_list);

                $smarty->assign('dst_script','performance');
                $smarty->assign('foot_page',$smarty->fetch('page_fragment.htm'));
                $smarty->assign('tasks_list',$tasks_list);
                $res['main'] = $smarty->fetch('tasks_list_div.htm');
            }else{
                $res['message'] = '修改失败';
            }
        }else{
            $res['message'] = '修改失败';
        }
        break;

    case 'del':
        if($task_id != 0){
            $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('tasks')." WHERE task_id=$task_id";
            $res['code']    = $GLOBALS['db']->query($sql_delete);
            $res['timeout'] = 2000;

            if($res['code']){
                $res['message'] = '删除成功';
            }else{
                $res['message'] = '删除失败';
            }
        }
        break;
    }

    $res['behave']   = $behave;
    $res['tr_index'] = $tr_index;

    die($json->encode($res));
}

/*搜索任务*/
elseif ($_REQUEST['act'] == 'sch_tasks'){

    $task_name     = mysql_real_escape_string($_REQUEST['task_name']);
    $deadline      = mysql_real_escape_string($_REQUEST['deadline']);
    $period_id     = intval($_REQUEST['period_id']);
    $purpose_value = intval($_REQUEST['purpose_value']);
    $platform      = intval($_REQUEST['platform']);
    $group_id      = intval($_REQUEST['group']);
    $codition      = '';
    $where         = ' WHERE status=1';

    if($task_name != ''){
        $where      .= " AND task_name LIKE '%$task_name%'";
        $condition  .= "&task_name=$task_name";
    }

    if($deadline != ''){
        $where      .= " AND deadline=".strtotime($deadline);
        $condition  .= "&deadline=$deadline";
    }

    if($period_id != 0){
        $where      .=  " AND period_id=$period_id";
        $condition  .=  "&period_id=$period_id";
    }

    if($platform != 0){
        $where      .=  " AND platform=$platform";
        $condition  .=  "&platform=$platform";
    }

    if($group_id){
        $where      .=  " AND group_id=$group_id";
        $condition  .=  "&group_id=$group_id";
    }

    $result     = get_tasks_list($where);

    $tasks_list = $result['result'];
    $filter     = $result['filter'];

    $period_list = get_period_list();
    $group_list  = get_only_group_list();
    $tasks_list  = format_tasks_list($tasks_list,$period_list,$group_list);

    foreach($filter as $key=>$val){
        $smarty->assign($key,$val);
    }

    $smarty->assign('tasks_list',$tasks_list);
    $smarty->assign('dst_script','performance');
    $foot_page = $smarty->fetch('page_fragment.htm');
    $smarty->assign('foot_page',$foot_page);

    $res['response_action'] = 'search_service';
    $res['main']            = $smarty->fetch('tasks_list_div.htm');

    die($json->encode($res));
}

//刷新任务
elseif ($_REQUEST['act'] == 'refresh_task'){
    if(!admin_priv('all','',false)){
        $data_status = intval($_REQUEST['data_status']);
        switch($data_status){
        case 0:
            $period_id = 1;
            break;
        case 1:
            $period_id = 2;
            break;
        case 2 :
            $period_id = 3;
            break;
        default:
            $period_id = 1;
            break;
        }

        $sql_select_admin_tasks = 'SELECT at.task_id,at.task_value,t.task_name FROM '.$GLOBALS['ecs']->table('admin_task').
            ' at LEFT JOIN '.$GLOBALS['ecs']->table('tasks').' t ON t.task_id=at.task_id'.
            " WHERE admin_id={$_SESSION['admin_id']} AND t.period_id=$period_id";

        $admin_task_info = $GLOBALS['db']->getAll($sql_select_admin_tasks);

        if($admin_task_info){
            $smarty->assign('admin_task_info',$admin_task_info);
            $res['main'] = $smarty->fetch('index_progress_div.htm');
        }else{
            /*分配任务*/
            $where = " WHERE t.period_id=1,t.platform={$_SESSION['role_id']} ";

            if($_SESSION['group_id']){
                $where .= " group_id={$_SESSION['group_id']}";
            }

            $res['req_msg'] = true;
            $res['timeout'] = 2000;
            $res['code']    = true;
            $res['message'] = '很遗憾,您今天没有重要任务';
        }
    }else{
        $res['message'] = '对不起，您没有权限';
    }

    die($json->encode($res));    
}

/*网络黑名单*/
elseif($_REQUEST['act'] == 'network_blacklist'){

    $account_type      = type_get();
    $result            = get_network_blacklist('network_blacklist');
    $network_blacklist = $result['network_blacklist'];
    $filter            = $result['filter'];

    $smarty->assign('is_checked',intval($_REQUEST['is_checked']));
    $smarty->assign('account_type_list',$account_type);
    $smarty->assign('network_blacklist',$network_blacklist);
    $smarty->assign('filter',$filter);
    $smarty->assign('network_blacklist_div',$smarty->fetch('network_blacklist_div.htm'));

    $res['main'] = $smarty->fetch('network_blacklist.htm');
    die($json->encode($res));
}

/*搜索网络黑名单*/
elseif($_REQUEST['act'] == 'sch_network_blacklist'){
    $result = get_network_blacklist('sch_network_blacklist');

    $smarty->assign('is_checked',intval($_REQUEST['is_checked']));
    $smarty->assign('network_blacklist',$result['network_blacklist']);    
    $smarty->assign('filter',$result['filter']);

    $res['response_action'] = 'search_service';
    $res['main']            = $smarty->fetch('network_blacklist_div.htm');

    die($json->encode($res));
}

/*删除黑名单*/
elseif($_REQUEST['act'] == 'del_network_blacklist'){

    $table_name = mysql_real_escape_string($_REQUEST['table_name']);
    $account_id = intval($_REQUEST['val']);
    $tr_index   = intval($_REQUEST['tr_index']);

    $res = array(
        'req_msg' => true,
        'code'    => false,
        'message' => '',
        'timeout' => 2000
    );

    if($account_id != 0){
        $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('account_blacklist').
            " WHERE account_id=$account_id";
        $result = $GLOBALS['db']->query($sql_del);

        if($result){
            $res['code'] = true;
            $res['message'] = '删除成功';
        }else{
            $res['message'] = '删除失败';
        }
    }

    $res['table_name'] = $table_name;
    $res['tr_index']   = $tr_index;

    die($json->encode($res));
}

/*审核网络黑名单*/
elseif($_REQUEST['act'] == 'check_network_blacklist'){
    $account_id = intval($_REQUEST['account_id']);
    $res = array(
        'req_msg' => true,
        'message' => '',
        'timeout' => 2000,
        'code'    => false
    );

    if($account_id != 0){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account_blacklist').
            " SET status=1,checker={$_SESSION['admin_id']} WHERE account_id=$account_id";

        $res['code'] = $GLOBALS['db']->query($sql_update);
        if($res['code']){
            $res['message'] = '审核成功';
        }
    }else{
        $res['message'] = '审核失败';
    }

    die($json->encode($res));
}

/*工作绩效*/
elseif($_REQUEST['act'] == 'work_award'){
    
    $year      = empty($_REQUEST['year']) ? date('Y') : mysql_real_escape_string($_REQUEST['year']);
    $month_day = empty($_REQUEST['month_day']) ? date('m-d') : mysql_real_escape_string($_REQUEST['month_day']);

    $date_time   = $year.'-'.$month_day;
    $admin_id    = isset($_REQUEST['admin_id'])&&empty($_REQUEST['admin_id']) ? 0 :intval($_REQUEST['admin_id']);
    $order_where = ' WHERE order_status IN(1,5) AND shipping_status<>3 ';
    $condition    = '';
    $result       = get_admin_sql();
    $admin_id_str = $result['admin_id_str'];
    $order_rate   = $result['admin_id_arr'];

    if(!empty($date_time)){
        $date_start = strtotime(date('Y-m-01',strtotime($date_time)));
        $date_end   = strtotime(date('Y-m-t',strtotime($date_time)));
    }else{
        $date_start = strtotime(date('Y-m-01',$_SERVER['REQUEST_TIME']));
        $date_end   = strtotime(date('Y-m-t',$_SERVER['REQUEST_TIME']));
        $date_time  = date('Y-m');
    }

    $sql_select = 'SELECT admin_id,COUNT(*) AS service_num FROM '.$GLOBALS['ecs']->table('service').
    " WHERE valid=1 AND service_time>=$date_start AND service_time<=$date_end AND admin_id IN($admin_id_str)".' GROUP BY admin_id';

    $service    = $GLOBALS['db']->getAll($sql_select);
    $admin_list = array();

    if($order_rate != null){
        foreach($order_rate as $val){
            $admin_list[] = $val['user_id'];
        }
    }

    if($admin_list){
        $admin_list = implode(',',$admin_list);
        $sql_select = 'SELECT COUNT(*) AS order_num,admin_id FROM '.$GLOBALS['ecs']->table('order_info').
            $order_where." AND admin_id IN($admin_list) AND add_time>=$date_start AND add_time<$date_end GROUP BY admin_id";
        $order_list = $GLOBALS['db']->getAll($sql_select);

        foreach($order_rate as &$rate){
            foreach($order_list as $key=>$val){
                if($rate['user_id'] == $val['admin_id']){
                    if(empty($val['order_sum'])){
                        $val['order_sum'] = 0;
                    }

                    if($rate['service_num'] == 0){
                        $rate['service_num'] = 1;
                    }

                    $rate['order_rate'] = ceil($val['order_num']/$rate['service_num']*100).'%';

                    $rate['order_num'] = $val['order_num'];
                    unset($val[$key]);
                }
            }
        }
    }

    foreach($order_rate as &$val){
        $val['service_num'] = empty($val['service_num']) ? 0 : $val['service_num'];
        $val['order_num']   = empty($val['order_num']) ? 0 : $val['order_num'];
        $val['order_rate']  = empty($val['order_rate']) ? 0 : $val['order_rate'];

    }

    if(admin_priv('all','',false)){
        $smarty->assign('platform_list',get_platform(SALE));
    }

    $smarty->assign('admin_name',$_REQUEST['admin']);
    $smarty->assign('date',array('year'=>$year,'month_day'=>$month_day));
    $smarty->assign('source','order_rate');
    $smarty->assign('date_time',$date_time);
    $smarty->assign('order_rate',$order_rate);
    $smarty->assign('caption',$year.'年'.date('m').'月成单率排行');
    $smarty->assign('award_list_div',$smarty->fetch('work_award_div.htm'));


    $res['code'] = 1;
    $res['main'] = $smarty->fetch('work_award.htm');
    die($json->encode($res));
}

/*季度销售排行*/
elseif($_REQUEST['act'] == 'quarter_performance'){

    $year    = empty($_REQUEST['year']) ? date('Y') :$_REQUEST['year'] ;
    $quarter = empty($_REQUEST['quarter']) ? 1 : intval($_REQUEST['quarter']);

    //$quarter = $quarter == 0 ? 1 : $quarter;
    //$year = $year == '' ? date('Y') : $year; 

    switch($quarter){
    case 1 :
        $start_time = strtotime("$year-01-01 00:00:00");
        $end_time   = strtotime("$year-03-01 00:00:00");
        break;
    case 2 :
        $start_time = strtotime("$year-03-01 00:00:00");
        $end_time   = strtotime("$year-06-01 00:00:00");
        break;
    case 3 :
        $start_time = strtotime("$year-06-01 00:00:00");
        $end_time   = strtotime("$year-09-01 00:00:00");
        break;
    case 4 :
        $start_time = strtotime("$year-10-01 00:00:00");
        $end_time   = strtotime(($year+1).'-01-01 00:00:00');
        break;
    }

    $sale_performance = get_sale_performance($start_time,$end_time);

    if(admin_priv('all','',false)){
        $smarty->assign('platform_list',get_platform(SALE));
    }

    $smarty->assign('admin_name',$_REQUEST['admin']);
    $smarty->assign('caption',$year.'年第'.$quarter.'季度销售排行');
    $smarty->assign('source','quarter_performance');
    $smarty->assign('date',array('year'=>$year));
    $smarty->assign('quarter',$quarter);
    $smarty->assign('sale_performance',$sale_performance);
    $smarty->assign('award_list_div',$smarty->fetch('work_award_div.htm'));

    $res['code'] = 1;
    $res['main'] = $smarty->fetch('work_award.htm');
    die($json->encode($res));
}

/*年度销售*/
elseif($_REQUEST['act'] == 'year_performance'){
    $year    = isset($_REQUEST['year'])&&empty($_REQUEST['year']) ? date('Y') :$_REQUEST['year'] ;
    //$year   = mysql_real_escape_string($_REQUEST['year']);
    //$year   = $year == '' ? date('Y') : $year; 

    $start_time = strtotime($year.'-01-01 00:00:00');
    $end_time   = strtotime(($year+1).'-01-01 00:00:00');

    $sale_performance = get_sale_performance($start_time,$end_time);

    if(admin_priv('all','',false)){
        $smarty->assign('platform_list',get_platform(SALE));
    }

    $smarty->assign('admin_name',$_REQUEST['admin']);
    $smarty->assign('caption',$year.'年度销售排行');
    $smarty->assign('source','year_performance');
    $smarty->assign('date',array('year'=>$year));
    $smarty->assign('sale_performance',$sale_performance);
    $smarty->assign('award_list_div',$smarty->fetch('work_award_div.htm'));

    $res['code'] = 1;
    $res['main'] = $smarty->fetch('work_award.htm');
    die($json->encode($res));
}

elseif($_REQUEST['act']=='illegal_control'){
    $sql_select = 'SELECT o.order_id,o.consignee,o.add_time oadd_time,c.add_time cadd_time,c.contact_value,c.contact_name,a.user_name FROM '.$GLOBALS['ecs']->table('order_info').' o,'.
        $GLOBALS['ecs']->table('user_contact').' c,'.$GLOBALS['ecs']->table('admin_user').
        ' a WHERE o.add_time>c.add_time AND o.add_time-c.add_time<=86400 AND c.user_id=o.user_id AND a.user_id=c.add_admin AND o.platform<>1  ORDER BY o.add_time DESC';
    $illegal_control_list = $GLOBALS['db']->getAll($sql_select);

    if($illegal_control_list){
       foreach ($illegal_control_list as &$val) {
           $val['oadd_time'] = date('Y-m-d H:i:s',$val['oadd_time']);
           $val['cadd_time'] = date('Y-m-d H:i:s',$val['cadd_time']);
           $d_start    = new DateTime($val['cadd_time']); 
           $d_end      = new DateTime($val['oadd_time']); 
           $diff = $d_start->diff($d_end);
           $val['time_diff'] = $diff->h.' 时'.$diff->i.' 分'.$diff->s.' 秒';
       } 
    }

    $smarty->assign('illegal_control_list',$illegal_control_list);
    $res['main'] = $smarty->fetch('illegal_control.htm');
    die($json->encode($res));
}

/*操作记录*/
elseif($_REQUEST['act'] == 'action_log'){
    $module_list             = get_admin_action();
    $result                  = get_admin_log(0,'','','','','','filter_page');
    $result['filter']['act'] = 'action_log';
    $solution_list           = get_solution_list();

    $smarty->assign('module_list',$module_list);
    $smarty->assign('admin_log',$result['log_list']);
    $smarty->assign('filter',$result['filter']);
    $smarty->assign('solution_list',$solution_list);
    $smarty->assign('action_log_table',$smarty->fetch('action_log_table.htm'));

    $res['main'] = $smarty->fetch('action_log.htm');
    die($json->encode($res));
}

/*搜索管理员操作记录*/
elseif ($_REQUEST['act'] == 'view_action_log'){
    $code       = isset($_REQUEST['code']) ? mysql_real_escape_string($_REQUEST['code']) : '';
    $module     = isset($_REQUEST['module']) ? mysql_real_escape_string($_REQUEST['module']) : 0;
    $admin_id   = isset($_REQUEST['admin_id']) ? intval($_REQUEST['admin_id']) : 0;

    if(empty($_REQUEST['start_time']) && empty($_REQUEST['end_time']) && !empty($_REQUEST['date'])){
        $date = !empty($_REQUEST['date']) ? $_REQUEST['date'] : 'today';
        $date = get_date($date);
        extract($date);
    }else{
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $start_time = strpos($_REQUEST['start_time'],'-') ? strtotime($_REQUEST['start_time']) : $_REQUEST['start_time'];
            $end_time   = strpos($_REQUEST['end_time'],'-') ? strtotime($_REQUEST['end_time']) : $_REQUEST['end_time'];
        }else{
            $date = get_date('today');
            extract($date);
        }
    }

    $result = get_admin_log($admin_id,$code,$module,$start_time,$end_time,'','filter_page');
    $result['filter']['act'] = $_REQUEST['act'];
    $result['filter']['condition'] .= "&start_time=$start_time&end_time=$end_time&module=$module&code=$code";

    $smarty->assign('admin_log',$result['log_list']);
    $smarty->assign('filter',$result['filter']);
    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('action_log_table.htm');
    die($json->encode($res));
}

/*分析操作日志*/
elseif($_REQUEST['act'] == 'analyse_log'){
    //@condition : by_user_action by_order_action
    $module = isset($_REQUEST['module']) ? mysql_real_escape_string($_REQUEST['module']) : '';
    $date   = isset($_REQUEST['date']) ? mysql_real_escape_string($_REQUEST['date']) : 'today';
    $date   = get_date($date);
    extract($date);

    $solution_list = get_solution_list();
    $result        = analyse_log($module,$code,$date,$codition);
    $log_list      = $reslut['log_list'];
    $module = 'users' == $module ? '对顾客信息操作' : '对订单信息操作';

    $smarty->assign('start_time',date('Y-m-d H:i:s',$start_time));
    $smarty->assign('end_time',date('Y-m-d H:i:s',$end_time));
    $smarty->assign('analyse_result',$analyse_result);
    $smarty->assign('module',$module);
    $smarty->assign('solution_list',$solution_list);
    $smarty->assign('analyse_log_table',$smarty->fetch('analyse_log_table.htm'));

    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('analyse_log.htm');

    die($json->encode($res));
}

//设置异常操作记录参数模板
elseif($_REQUEST['act'] == 'analyse_log_config'){
    $config_list = get_solution_list();
    $smarty->assign('config_list',$config_list);
    $res['main'] = $smarty->fetch('analyse_config.htm');

    die($json->encode($res));
}

//设置异常操作记录参数操作
elseif($_REQUEST['act'] == 'save_log_conf'){
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('action_solution');
    foreach($_REQUEST as $key=>$val){
        if(preg_match('/^(by_)\w+/',$key)){
            $GLOBALS['db']->query($sql_update." SET analyse_value=$val WHERE analyse_code='$key'");
        }
    }

    $res = array(
        'req_msg'  => true,
        'time_out' => 2000,
        'message'  => '修改成功',
    );

    die($json->encode($res)); 
}

/*管理员操作日志搜索参数设置*/
elseif($_REQUEST['act'] == 'get_condition'){
    $condition = isset($_REQUEST['condition']) ? mysql_real_escape_string($_REQUEST['condition']) : 0;
    $res['id'] = 'explane_value'; 
    switch($condition){
    case 'role' :
        $sql_select = 'SELECT role_id AS value,role_name AS text FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id IN(".ONLINE_STORE.')';
        $res['text'] = '请选择平台';
        break;
    case 'effect' :
        break;
    case 'other' :
        break;
    case 'rank' :
        $sql_select = 'SELECT rank_id AS value,rank_name AS text FROM '.$GLOBALS['ecs']->table('user_rank').' ORDER BY level ASC';
        $res['text'] = '请选择等级';
        break;
    }

    if($sql_select){
        $res['options'] = $GLOBALS['db']->getAll($sql_select);
        $res['length'] = count($res['options']);
    }else{
        $res['text'] = "请选择条件";
        $res['length'] = 0;
    }

    die($json->encode($res));
}

/*获取更详细的信息*/
elseif ($_REQUEST['act'] == 'get_more_security_info'){
    $table = isset($_REQUEST['table']) ? mysql_real_escape_string($_REQUEST['table']) : '';
    $sn    = isset($_REQUEST['sn']) ? intval($_REQUEST['sn']) : 0;

    if($table && $sn){
        $result_info = get_security_more_info($table,$sn);
    }

    $smarty->assign('info',$result_info);
    $res['message'] = $smarty->fetch('simple_info.htm');
    die($json->encode($res));
}

/*搜索分析操作行为*/
elseif ($_REQUEST['act'] == 'act_analyse_log'){
    $where = ' WHERE 1 ';

    if (isset($_REQUEST['solution']) && 0 != $_REQUEST['solution']) {
        //选择方案
        $condition = act_analyse_from_solution($_REQUEST['solution']);
        $result    = get_analyse_log($condition);
    }else{
        $analyse_code  = isset($_REQUEST['analyse_code']) ? mysql_real_escape_string($_REQUEST['analyse_code']) : '';
        $explane       = isset($_REQUEST['explane']) ? mysql_real_escape_string($_REQUEST['explane']) : '';
        $explane_value = isset($_REQUEST['explane_value']) ? intval($_REQUEST['explane_value']) : 0;
        $symbol        = isset($_REQUEST['symbol']) ? mysql_real_escape_string($_REQUEST['symbol']) : '';
        $analyse_value = isset($_REQUEST['analyse_value']) ? intval($_REQUEST['analyse_value']) : 0;
        $module = isset($_REQUEST['module']) ? mysql_real_escape_string($_REQUEST['module']) : '';
        $behave = isset($_REQUEST['behave']) ? mysql_real_escape_string($_REQUEST['behave']) : '';
        if('add' == $behave){
            $solution_name = isset($_REQUEST['solution_name']) ? mysql_real_escape_string($_REQUEST['solution_name']) : '';
            $res = array(
                'req_msg' => true,
                'timeout' => 2000,
                'code'    => false,
                'message' => ''
            );

            if(!empty($solution_name) && 'select' != $explane){
                $is_exist = is_value_exist('action_solution','solution_name',$solution_name);
                if ($is_exist) {
                    $res['message'] = '已经有相同的方案';
                }else{
                    $sql_insert = 'REPLACE INTO '.$GLOBALS['ecs']->table('action_solution').
                        '(solution_name,analyse_code,analyse_value,module,explane,explane_value,symbol)'.
                        "VALUES('$solution_name','$analyse_code',$analyse_value,'$module','$explane',$explane_value,'$symbol')";

                    $res['code']    = $GLOBALS['db']->query($sql_insert);
                    $res['message'] = $res['code'] ? '添加成功' : '添加失败';
                    $res['id'] = $GLOBALS['db']->insert_id();    
                }
            }else{
                $res['message'] = '添加失败';
            }
        }else{
            $sql_select = 'SELECT COUNT(*) AS ill_value FROM '.$GLOBALS['ecs']->table('action_solution');

            $res['id']   = 'analyse_result';
            $res['main'] = $smarty->fetch('analyse_log_table.htm');
        }
    }

    die($json->encode($res));
}

/*清理操作历史记录*/
elseif($_REQUEST['act'] == 'clear_log'){
    $res = array(
        'req_msg' => true,
        'code'    => false,
        'message' => '',
        'timeout' => 2000,
    );

    $m          = date('m')-1;
    $start_time = strtotime(date("Y-$m-1 00:00:00"));
    $endt_time  = strtotime(date("Y-$m-t 00:00:00"));

    $sql = 'DELETE FROM '.$GLOBALS['ecs']->table('admin_log').
        " WHERE log_time BETWEEN $start_time AND $end_time";

    $res['code']    = $GLOBALS['db']->query($sql);
    $res['message'] = $res['code'] ? '删除成功' : '删除失败';

    die($json->encode($res));
}

/* * 函数 */
// 所有账号
function accounts_get($sql_condition)
{
    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20; 

    }

    //帐号总数
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('account').' AS a LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' AS u ON a.updater=u.user_id'.$sql_condition;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
    $filter['page_count'] = $filter['record_count']> 0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

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
        'act'           => 'accounts',
    );

    $sql_select = 'SELECT a.account_id,a.account_name,a.user_id,a.type_id,a.is_vip,a.purpose,a.password,a.subject,a.url,at.label,FROM_UNIXTIME(a.account_updatetime,"%m月%d日") AS account_updatetime,a.updater AS updater_name,a.belong,a.is_check,a.usable,r.role_name,u.user_name AS admin_name FROM '
        .$GLOBALS['ecs']->table('account').' AS a LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS u ON a.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('role')
        .' AS r ON a.department_id=r.role_id LEFT JOIN '.$GLOBALS['ecs']->table('account_type')
        .' AS at ON at.type_id=a.type_id '.$sql_condition.
        ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

    $accounts_list = $GLOBALS['db']->getAll($sql_select);

    return array('account_list'=>$accounts_list,'filter'=>$filter);
}

/*
//工作检查(部门帐号)
function get_department_account($department_id,$user_id)
{
    $sql_select = 'SELECT a.user, a.account_id, a.account_name, t.type_name, a.subject, a.url,FROM_UNIXTIME(a.account_updatetime,"%m月%d日") AS account_updatetime,a.prescore,a.score FROM '
        .$GLOBALS['ecs']->table('account')
        .' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_type')
        .'AS t ON a.type_id=t.type_id'
        ." WHERE a.department_id=$department_id AND a.user=$user_id";

    $department_account = $GLOBALS['db']->getAll($sql_select);
    return $department_account;
}
//获取部门员工
function get_employees($department_id)
{
    $sql_select = 'SELECT distinct a.user_id,u.user_name FROM '
        .$GLOBALS['ecs']->table('account').' AS a LEFT JOIN '
        .$GLOBALS['ecs']->table('admin_user')
        ." AS u ON a.user=u.user_id";

    if($department_id != "")
    {
        $sql_select .= " WHERE a.department_id=$department_id GROUP BY a.user";   
    }

    $user_list=$GLOBALS['db']->getAll($sql_select);
    return $user_list;
}

 */
//账号类型
function type_get()
{
    $sql_select = "SELECT type_id,type_name,label FROM ".$GLOBALS['ecs']->table('account_type');
    $type = $GLOBALS['db']->getAll($sql_select);
    return $type;
}

//部门
function department_get($department_id = "")
{
    if($department_id == "")
    {
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('role');
        $department =$GLOBALS['db']->getAll($sql_select);
        return $department;
    }
    else
    {
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('role')
            ." WHERE role_id = $department_id";
        $department =$GLOBALS['db']->getAll($sql_select);
        return $department;
    }
}

/*
 * 帐号的使用者
 */
function user_get()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE status=1';
    $user = $GLOBALS['db']->getAll($sql_select);
    return $user;
}

//Q群(通过用户名)
function qqGroup_get($user_name)
{
    $sql_select = 'SELECT distinct q.group_id,q.qq,q.qqgroup,q.subject,a.account_name,q.admin_id,q.category FROM '.
        $GLOBALS['ecs']->table('account_qqgroup').
        ' AS q LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        " AS a ON q.admin_id=a.user_id WHERE qq=$user_name";

    $qqgroup = $GLOBALS['db']->getAll($sql_select);
    return $qqgroup;
}

//Q群(通过搜索条件)
function qq_group_get($sql_condition)
{
    $sql_select = 'SELECT q.qq,q.qqgroup,q.subject,a.user_name,q.admin_id,q.category,q.updateTime FROM '.
        $GLOBALS['ecs']->table('account_qqgroup').
        ' AS q LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS a ON q.admin_id=a.user_id'.$sql_condition;

    $result = $GLOBALS['db']->getAll($sql_select);
    return $result;
}

//获取QQ
function qq_get()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account').' WHERE type_id=1';
    $result = $GLOBALS['db']->getAll($sql_select);
    return $result;
}

//密码保护
function get_passwordprotect()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_passwordprotect');
    $passwordprotect = $GLOBALS['db']->getAll($sql_select);
    return $passwordprotect;
}

function get_accountToupdate($sql_condition)
{
    $sql_select = 'SELECT a.account_name,t.type_name,a.subject,a.url,FROM_UNIXTIME(a.account_updatetime,"%Y年%m月%d日") AS account_updatetime,u.user_name AS updater_name FROM '.
        $GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_type').
        ' AS t ON a.type_id=t.type_id'.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS u ON a.updater=u.user_id'.$sql_condition;

    $result = $GLOBALS['db']->getAll($sql_select);
    return $result;
}

//查询某个商城的函数
function select_list($rid)
{

    $sql_select = 'SELECT s.*,d.*,a.user_name FROM '.$GLOBALS['ecs']->table('spread').
        ' s,'.$GLOBALS['ecs']->table('spread_ad').' d,'
        .$GLOBALS['ecs']->table('admin_user')." a WHERE d.rid='$rid'
        AND s.is_delete=0 AND s.spread_id=d.spread_id AND s.admin_id=a.user_id 
        ORDER BY s.add_time DESC LIMIT 0 , 15";
    $list = $GLOBALS['db']->getAll($sql_select);

    //格式化下时间
    foreach($list as &$v)
    {
        $v['add_time'] = date('Y-m-d H:i',$v['add_time']);
    }

    return $list;
}

//查询某个商城下的对应的客服记录
function service_list($rid)
{
    $sql_select = 'SELECT s.*,w.*,adw.way_name,adt.type_name,au.user_name FROM '
        .$GLOBALS['ecs']->table('service_record').' s,'
        .$GLOBALS['ecs']->table('work_summary').' w,'
        .$GLOBALS['ecs']->table('admin_user').' au,'
        .$GLOBALS['ecs']->table('advisory_way').' adw,'
        .$GLOBALS['ecs']->table('advisory_type').' adt WHERE '.
        " s.rid=$rid AND s.work_id=w.work_id AND au.user_id=w.admin_id AND ".
        ' adw.way_id=s.way_id AND adt.type_id=s.type_id ORDER BY w.add_time DESC LIMIT 0 , 15';
    $list = $GLOBALS['db']->getAll($sql_select);

    //格式化下时间
    foreach($list as &$v)
    {
        $v['add_time'] = date('Y-m-d H:i',$v['add_time']);
    }

    return $list;
}

//查询某个商城下的工作总结
function work_list($rid)
{
    $sql_select = 'SELECT a.user_name,w.job_content,w.summary,s.add_time FROM '
        .$GLOBALS['ecs']->table('spread').' s,'
        .$GLOBALS['ecs']->table('admin_user').' a,'
        .$GLOBALS['ecs']->table('spread_ad').' sa,'
        .$GLOBALS['ecs']->table('work_summary').' w '.
        "WHERE sa.rid=$rid AND s.spread_id=sa.spread_id AND 
        s.admin_id=a.user_id AND s.work_id=w.work_id ".
        ' GROUP BY(sa.spread_id) ORDER BY aid DESC  LIMIT 0 , 15';
    $arr = $GLOBALS['db']->getAll($sql_select);

    //格式化时间   
    foreach($arr as &$v)
    {
        $v['add_time'] = date('Y-m-d H:i',$v['add_time']); 
    }

    return $arr;
}

/**
 * 推广记录
 */
function spread_list ()
{
    $condition = '';
    $sql_select_count = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('spread').' s,'.
        $GLOBALS['ecs']->table('work_summary').' w,'.$GLOBALS['ecs']->table('admin_user').
        ' a WHERE s.is_delete=0 AND s.admin_id=a.user_id AND s.work_id=w.work_id';

    // 权限判断
    $sql_where = '';
    if (admin_priv('all', '', false))
    {
        $filter['platform'] = isset($_REQUEST['platform']) ? intval($_REQUEST['platform']) : 0;
    }
    elseif (admin_priv('spread_view', '', false))
    {
        $sql_select_role = 'SELECT GROUP_CONCAT(user_id) FROM '.$GLOBALS['admin_user'].
            " WHERE status=1 AND role_id={$_SESSION['role_id']}";
        $admin_list = $GLOBALS['db']->getOne($sql_select_role);
        $sql_where .= " AND s.admin_id IN ($admin_list)";
    }
    else
    {
        $sql_where .= " AND s.admin_id={$_SESSION['admin_id']}";
    }

    if (isset($filter['platform']) && $filter['platform'])
    {
        $sql_where .= " AND s.platform={$filter['platform']} ";
    }

    // 收集查询条件
    foreach ($filter as $key=>$val)
    {
        if (!empty($val))
        {
            $condition .= "&$key=$val";
        }
    }

    $sql_select_count .= $sql_where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select_count);

    // 分页
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    else
    {
        $filter['page_size'] = 15; // 默认分页大小
    }

    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页页数
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

    $sql_select = 'SELECT s.spread_id,s.spread_pv,s.spread_uv,s.add_time,s.orders,s.sale,s.scalping_num,'.
        's.scalping_amount,a.user_name,s.platform,w.job_content FROM '.$GLOBALS['ecs']->table('spread').' s,'.
        $GLOBALS['ecs']->table('work_summary').' w,'.$GLOBALS['ecs']->table('admin_user').
        " a WHERE s.is_delete=0 AND s.admin_id=a.user_id AND s.work_id=w.work_id $sql_where ORDER BY ".
        's.add_time DESC LIMIT '.($filter['page'] -1)*$filter['page_size'].', '.$filter['page_size'];
    $res = $GLOBALS['db']->getAll($sql_select);

    // 格式化日期
    foreach ($res as &$val)
    {
        $val['add_time'] = local_date('Y-m-d H:i', $val['add_time']);
    }

    $arr = array (
        'spread_list'  => $res,
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
 * 客服咨询记录
 */
function counsel_list ()
{
    $condition = '';
    $sql_select_count = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('work_summary').' w,'.
        $GLOBALS['ecs']->table('service_record').' s WHERE s.work_id=w.work_id ';

    // 权限判断
    $sql_where = '';
    if (admin_priv('all', '', false))
    {
        $filter['platform'] = isset($_REQUEST['platform']) ? intval($_REQUEST['platform']) : 0;
    }
    elseif (admin_priv('spread_view', '', false))
    {
        $filter['platform'] = $_SESSION['role_id'];
    }
    else
    {
        $sql_where .= " AND w.admin_id={$_SESSION['admin_id']}";
    }

    if (isset($filter['platform']) && $filter['platform'])
    {
        $sql_where .= " AND s.rid={$filter['platform']} ";
    }

    // 收集查询条件
    foreach ($filter as $key=>$val)
    {
        if (!empty($val))
        {
            $condition .= "&$key=$val";
        }
    }

    $sql_select_count .= $sql_where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select_count);

    // 分页
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    else
    {
        $filter['page_size'] = 15; // 默认分页大小
    }

    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页页数
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

    $sql_select = 'SELECT s.*,w.*,a.user_name FROM '.$GLOBALS['ecs']->table('service_record').' s,'.
        $GLOBALS['ecs']->table('work_summary').' w,'.$GLOBALS['ecs']->table('admin_user').
        ' a WHERE w.work_id=s.work_id AND w.admin_id=a.user_id '."$sql_where ORDER BY w.add_time DESC LIMIT ".
        ($filter['page'] -1)*$filter['page_size'].', '.$filter['page_size'];
    $res = $GLOBALS['db']->getAll($sql_select);

    // 格式化日期
    foreach ($res as &$val)
    {
        $val['add_time'] = local_date('Y-m-d H:i', $val['add_time']);
    }

    $arr = array (
        'counsel_list' => $res,
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

// 产品分类 
function product_get()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('effects').
        'WHERE available=1'; 
    return $GLOBALS['db']->getAll($sql_select);
}

//帐号主题分类
function get_subject()
{   
    $sql_select = 'SELECT DISTINCT subject FROM '.$GLOBALS['ecs']->table('account');
    return $GLOBALS['db']->getCol($sql_select);
}

//推广活动列表
function get_activity(){
    $date          = mysql_real_escape_string($_REQUEST['date']);
    $role_id       = intval($_REQUEST['role_id']);
    $activity_name = trim(mysql_real_escape_string($_REQUEST['activity_name']));
    $start_time    = strtotime($_REQUEST['start_time']);
    $end_time      = strtotime($_REQUEST['end_time']);
    $brand_id      = intval($_REQUEST['brand_id']);
    $goods_sn      = mysql_real_escape_string($_REQUEST['goods_id']);
    $keyword       = trim(mysql_real_escape_string($_REQUEST['keyword']));
    $where         = ' WHERE status=1 AND ga.act_id=a.activity_id AND ga.goods_sn=g.goods_sn ';
    $condition     = '';
    $activity_list = $filter = array();

    if(!admin_priv('all','',false)){
        $where     .= ' AND a.role_id='.$_SESSION['role_id'];
        $condition .= "&role_id=$role_id";
    }

    if($activity_name != ''){
        $where     .= " AND a.activity_name LIKE '%$activity_name%'";
        $condition .= "&activity_name=$activity_name";
    }

    if($brand_id != 0){
        $where     .= " AND g.brand_id=$brand_id";
        $condition .= "&brand_id=$brand_id";
    }

    if($goods_sn != '' && $goods_sn != '0'){
        $where     .= " AND g.goods_sn='$goods_sn'";
        $condition .= "&goods_id=$goods_sn";
    }

    if($keyword != ''){
        $where .= " AND g.goods_name LIKE '%$keyword%'";
        $condition .= "&keyword=$keyword";
    }

    if($start_time && $end_time){
        $where     .= " AND a.start_time>=$start_time AND a.end_time<=$end_time";
        $condition .= "&start_time=$start_time&end_time=$end_time";
    }else{
        $start_time = strtotime(date('Y-m-d 0:0:0',$_SERVER['REQUEST_TIME']));
        $where .= " AND a.end_time>=$start_time ";
    }

    $filter['page']      = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $filter['page_size'] = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 15;

    if(!admin_priv('all','',false)){
        $where .= " AND role_id={$_SESSION['role_id']}";
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('spread_activity')
        .' a, '.$GLOBALS['ecs']->table('goods_activity').' ga, '.$GLOBALS['ecs']->table('goods')
        .' g '.$where.' GROUP BY activity_id';

    $result = $GLOBALS['db']->getCOl($sql_select);

    $filter['record_count'] = count($result);
    $filter['page_count']   = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

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
        'act'           => $_REQUEST['act'],
    );

    $limit      = ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";
    $sql_select = 'SELECT activity_id,activity_name,a.start_time,a.end_time,act_describe,a.order_money,a.goods_num FROM '.$GLOBALS['ecs']->table('spread_activity').' a, '.$GLOBALS['ecs']->table('goods_activity')
        .' ga, '.$GLOBALS['ecs']->table('goods').' g '
        .$where.' GROUP BY a.activity_id ORDER BY a.start_time ASC, end_time ASC '.$limit;

    $activity_list = $GLOBALS['db']->getAll($sql_select);

    foreach($activity_list as &$val){
        $val['start_time']    = date('Y-m-d',$val['start_time']);
        $val['end_time']      = date('Y-m-d',$val['end_time']);
        $val['activity_type'] = '-';

        if($val['order_money'] != 0){
            $val['activity_type'] = "消费满{$val['order_money']}元";
        }elseif($val['goods_num'] != 0){
            $val['activity_type'] = "购买满{$val['goods_num']}件";
        }
    }

    $result = array(
        'activity_list' => $activity_list,
        'filter'        => $filter
    );

    return $result;
}

//绩效信息
function get_statistics_info(){
}

//任务列表
function get_tasks_list($where = ''){
    if($where == ''){
        $where = ' WHERE status=1';
    }

    $sql_select = 'SELECT t.task_id,t.task_name,t.purpose_value,t.mod_time,t.period_id,t.deadline,r.role_name AS platform,t.group_id FROM '.
        $GLOBALS['ecs']->table('tasks').' t LEFT JOIN '.$GLOBALS['ecs']->table('role').
        ' r ON t.platform=r.role_id'.
        " $where ORDER BY t.mod_time DESC ";
    $sql_one = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('tasks').$where;

    $result   = filter_page($sql_one,$sql_select,'tasks_site');   

    return $result;
}

/*任务周期*/
function get_period_list(){
    $sql_select    = 'SELECT period_id,period_name FROM '.$GLOBALS['ecs']->table('period');
    $period_list   = $GLOBALS['db']->getAll($sql_select); //日期类型 每天 每周
    return $period_list;
}


/*获取网络黑名单*/
function get_network_blacklist($act){
    $keyword = trim(mysql_real_escape_string($_REQUEST['keyword']));
    $type_id = intval($_REQUEST['account_type']);
    $status  = intval($_REQUEST['is_checked']);
    $where   = " WHERE a.account_type=t.type_id AND u.user_id=a.add_admin AND a.status=$status ";
    $condition = '';

    if($type_id != 0){
        $where .= " AND t.type_id=$type_id ";
        $condition .= "&account_type=$type_id";
    }

    if($keyword != ''){
        $where .= "AND a.account_value LIKE '%$keyword%' ";
        $condition .= "&keyword=$keyword";
    }


    $filter['page']      = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $filter['page_size'] = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 15;

    $sql_select   = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('account_blacklist')
        .' a, '.$GLOBALS['ecs']->table('account_type')
        .' t, '.$GLOBALS['ecs']->table('admin_user')
        .' u '.$where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
    $filter['page_count']   = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

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
        'act'           => $act,
    );

    $limit = ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

    $sql_select   = 'SELECT t.type_name,a.account_id,a.account_value,a.in_time,u.user_name FROM '.$GLOBALS['ecs']->table('account_blacklist')
        .' a, '.$GLOBALS['ecs']->table('account_type')
        .' t, '.$GLOBALS['ecs']->table('admin_user')
        .' u '.$where.$limit;

    $result = $GLOBALS['db']->getAll($sql_select);

    foreach($result as &$val){
        $val['in_time'] = date('Y-m-d',$val['in_time']);
    }

    return array('network_blacklist'=>$result,'filter'=>$filter);
}

/*员工销售排行*/
function get_sale_performance($start_time,$end_time){

    $result           = get_admin_sql();
    $admin_id_str     = $result['admin_id_str'];
    $sale_performance = $result['admin_id_arr'];
    
    $where         = " WHERE order_status IN(1,5) AND shipping_status<>3 AND admin_id IN($admin_id_str) AND add_time>=$start_time AND add_time<=$end_time";

    $sql_select = 'SELECT admin_id,SUM(final_amount) as final_amount,COUNT(*) order_num FROM '
        .$GLOBALS['ecs']->table('order_info').$where.' GROUP BY admin_id ';
    $order_list = $GLOBALS['db']->getAll($sql_select);

    if($order_list != null){
        foreach($sale_performance as &$sale){
            foreach($order_list as $key=>$order){
                if($sale['user_id'] == $order['admin_id']){
                    $sale['final_amount'] = $order['final_amount'];
                    $sale['order_num']    = $order['order_num'];

                    if($order['order_num']){
                        $sale['average_amount'] = sprintf('%0.2f',$order['final_amount']/$order['order_num']);
                    }
                    unset($order[$key]);
                }
            }
        } 
    }

    foreach($sale_performance as &$val){
        $val['final_amount']   = empty($val['final_amount']) ? '0.00' : $val['final_amount'];
        $val['order_num']      = empty($val['order_num']) ? '0' : $val['order_num'];
        $val['average_amount'] = empty($val['average_amount']) ? '0.00' :               $val['average_amount'];
    }

    //print_r($sale_performance);exit;
    $sale_amount = array();
    if($sort_by == '' || $sort_by_final_amount){
        foreach($sale_performance as $key=>&$val){
            $sale_amount[$key] = $val['final_amount'];
        }

        if($sale_amount){
            array_multisort($sale_amount,SORT_DESC,$sale_performance);
        }
    }

    return $sale_performance;
}

/*通过权限判断返回查询员工列表SQL语句*/
function get_admin_sql(){
    $admin_where = ' WHERE status=1 ';
    if(isset($_REQUEST['admin'])){
        $_REQUEST['admin'] = trim($_REQUEST['admin']);
    }

    if(admin_priv('all','',false)){
        $authority      = 'all'; 

        if(isset($_REQUEST['platform']) && !empty($_REQUEST['platform'])){
            $admin_where .= " AND role_id={$_REQUEST['platform']} ";
        }else{
            $admin_where .= ' AND role_id IN('.MEMBER_SALE.')';
        }

        if(isset($_REQUEST['group_id']) && !empty($_REQUEST['group_id'])){
            $admin_where .= " AND group_id={$_REQUEST['group_id']} ";
        }

        if(isset($_REQUEST['admin']) && !empty($_REQUEST['admin'])){
            $admin_where .= " AND user_name LIKE '%{$_REQUEST['admin']}%'";
        }
    }elseif(admin_priv('award_platform_view','',false)){
        $authority      = 'role_view';

        if(isset($_REQUEST['group_id']) && !empty($_REQUEST['group_id'])){
            $admin_where .= " AND group_id={$_REQUEST['group_id']} ";
        }

        if(isset($_REQUEST['admin']) && !empty($_REQUEST['admin'])){
            $admin_where .= " AND user_name LIKE '%{$_REQUEST['admin']}%'";
        }

        $admin_where .= " AND platform={$_SESSION['role_id']} ";
    }elseif(admin_priv('award_group_view','',false)){
        $authority      = 'group_view';

        if(isset($_REQUEST['admin']) && !empty($_REQUEST['admin'])){
            $admin_where .= " AND user_name LIKE '%{$_REQUEST['admin']}%'";
        }

        $admin_where .= " AND platform={$_SESSION['role_id']} AND group_id={$_SESSION['group_id']} ";
    }else{
        $authority      = 'general_view';
        $admin_where .= " AND admin_id={$_SESSION['admin_id']} ";
    }

    $admin_sql_select = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('admin_user').
        $admin_where;
    $result = $GLOBALS['db']->getAll($admin_sql_select); 

    $admin_id_list    = array();

    foreach($result as $val){
        $admin_id_list[] = $val['user_id'];
    }

    if(!count($admin_id_list)){
        $admin_id_list[] = 0;
    }

    $admin_id_str = implode(',',$admin_id_list);

    return array('admin_id_str'=>$admin_id_str,'admin_id_arr'=>$result);
}

function get_platform($role_id = ''){
    $where = empty($role_id) ? '' : " WHERE role_id IN($role_id)";
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').$where;
    return $GLOBALS['db']->getAll($sql_select);
}

/*主权限*/
function get_admin_action(){
    $sql_select = 'SELECT action_id,label FROM '.$GLOBALS['ecs']->table('admin_action').
        ' WHERE action_level=0 ORDER BY action_id ASC'; 
    $admin_action = $GLOBALS['db']->getAll($sql_select);
    return $admin_action;
}

//获取管理员操作记录
function get_admin_log($do_admin=0,$code='',$module='',$start_time='',$end_time='',$order_by='',$filter_page=''){
    $where     = ' WHERE 1';
    $condition = '';

    if($user_id){
        $where .= " AND l.do_admin=$do_admin "; 
        $condition .= "&do_admin=$do_admin";
    }

    if(!empty($code) && $code != 'all'){
        $where .= " AND l.code='$code'";  
        $condition .= "&code=$code";
    }

    if (!$start_time && !$end_time) {
        $start_time = strtotime(date('Y-m-d 00:00:00'));
        $end_time   = strtotime(date('Y-m-d 23:59:59'));
    }

    $condition .= "&start_time=$start_time&end_time=$end_time";
    $where .= " AND log_time BETWEEN $start_time AND $end_time ";

    if($filter_page){
        $sql_select = ' SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_log').
            ' l LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' a ON a.user_id=l.do_admin '.$where;

        $record_count        = $GLOBALS['db']->getOne($sql_select);
        $page_size           = !empty($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 8;
        $page                = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $filter              = break_pages($record_count,$page_size,$page);
        $filter['codition']  = $condition;
        $filter['page']      = $page;
        $filter['page_size'] = $page_size;
        $limit               = ' LIMIT '.($page-1)*$page_size.",$page_size";
    }

    $order_by = $order_by ? $order_by : 'log_time DESC';

    $sql_select = 'SELECT l.log_time,l.log_info,l.sn,l.module,l.do_admin,l.admin_id,l.code,a.user_name,l.do_times FROM '.$GLOBALS['ecs']->table('admin_log').
        ' l LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' a ON a.user_id=l.do_admin '.
        "$where ORDER BY $order_by".$limit;

    $log_list = $GLOBALS['db']->getAll($sql_select);

    if($log_list){
        foreach ($log_list as &$val) {
            $val['log_time'] = date('Y-m-d H:i:s',$val['log_time']);
            if (strpos($val['sn'],',')) {
                $val['sn'] = explode(',',$val['sn']);
            }
        }
    }

    return array('log_list'=>$log_list,'filter'=>$filter);
}

function get_solution_list($where=''){
    $sql_select = 'SELECT solution_id,solution_name,analyse_code,analyse_value,explane,symbol FROM '.$GLOBALS['ecs']->table('action_solution')."$where ORDER BY solution_id ASC";
    return $GLOBALS['db']->getAll($sql_select);
}

/*操作日志分析参数值*/
function get_analyse_value($analyse_code){
    $sql_select = 'SELECT analyse_value FROM '.$GLOBALS['ecs']->table('action_solution').
        " WHERE analyse_code='$analyse_code'";
    return $GLOBALS['db']->getOne($sql_select);
}

/*分析管理员操作*/
function analyse_log($module,$code,$date,$condition){
    //时间 
    if($date){
        extract($date);
    }

    if('user' == $module){
        $module  ='users';
        $content = '顾客';
    }elseif('order' == $module){
        $module='orders';
        $content = '订单';
    }

    if ($code == 'transfer') {
        /*指转移顾客数量*/
    }else{
        $mem = new Memcache;
        $mem->connect('127.0.0.1',11211);

        //统计每个员工的对顾客的操作
        //1、收集管理员id 
        //2、统计操作了非属于自己顾客的操作次数
        //3、某顾客类型次数、某平台、某等级、操作日志中顾客的购买力
        if($mem->get('log_list') && $mem->get('log_condition') == $condition){
            $log_list = $mem->get('log_list');
        }else{
            $result = get_admin_log(0,'',$module,$start_time,$end_time,' do_times DESC','');
            $log_list = $result['log_list'];
            //if ($log_list) {
            //    $admin_list = get_log_admin($log_list);
            //    $un_i_do    = get_un_i_do($admin_list,$log_list); //统计操作非自己顾客次数
            //    print_r($un_i_do);

            //    //$mem->set('log_list',$log_list,0,3600);
            //    //$mem->set('log_condition',$condition,0,3600);
            //}
        }
    }

    return $log_list;
}

/*获得操作类型*/
function get_module_act($condition) {
    switch($condition){
    case 'by_view_user_info'  : $code = 'view';$module = 'user'; break;
    case 'by_edit_user_info'  : $code = 'upd';$module = 'user'; break;
    case 'by_del_user_info'   : $code = 'del';$module  = 'user'; break;
    case 'by_view_order_info' : $code = 'view';$module  = 'order'; break;
    case 'by_edit_order_info' : $code = 'upd';$module  = 'order'; break;
    case 'by_del_order_info'  : $code = 'del';$module  = 'order'; break;
    }

    return array('code' => $code, 'module' => $module);
}

function get_date($date){
    if (!empty($date)) {
        switch($date){
        case 'today':
            $start_time = strtotime(date('Y-m-d 00:00:00'));
            $end_time = strtotime(date('Y-m-d 23:59:59'));
            break;
        case 'week' :
            $start_time = mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y"));
            $end_time   = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
            break;
        case 'month':
            $start_time = strtotime(date('Y-m-1 00:00:00'));
            $end_time = strtotime(date('Y-m-t 23:59:59'));
            break;
        }
    }

    return array('start_time' => $start_time, 'end_time' => $end_time); 
}

//查看异常操作订单或顾客相关信息
function get_security_more_info($table,$value){
    if ('users' == $table) {
        //查看顾客列多的信息
        $field = 'u.wechat,u.mobile_phone,u.home_phone,u.aliww';
        $sql_select = "SELECT u.user_name,a.user_name AS admin_name,u.last_admin,$field,r.role_name FROM ".
            $GLOBALS['ecs']->table('users').' u LEFT JOIN '.$GLOBALS['ecs']->table('role').
            ' r ON u.role_id=r.role_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
            " a on u.admin_id=a.user_id WHERE u.user_id=$value";
        $info = $GLOBALS['db']->getRow($sql_select);
        if($info){
            $sql_select = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user').
                " WHERE user_id={$info['last_admin']}";

            $info['last_admin']   = $GLOBALS['db']->getOne($sql_select);
            $info['mobile_phone'] = substr($info['mobile_phone'],0,6).'···';
            $info['home_phone']   = substr($info['home_phone'],0,4).'···';
            $info['type']         = 'user';
        }

    }else if('order' == $table){
        $sql_select = 'SELECT o.order_sn,o.shipping_name,o.pay_name,o.consignee,o.admin_name,g.goods_name,o.final_amount,r.role_name FROM '.
            $GLOBALS['ecs']->table('order_info').' o LEFT JOIN '.$GLOBALS['ecs']->table('order_goods').
            ' g ON o.order_id=g.order_id LEFT JOIN '.$GLOBALS['ecs']->table('role').
            ' r ON o.platform=r.role_id '.
            " WHERE o.order_id=$value"; 

        $info = $GLOBALS['db']->getRow($sql_select);
        $info['type'] = 'order'; 
    }

    return $info;
}

//查看字段是否在表中存在
function is_value_exist($table,$column,$value){
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table($table).
        "WHERE $column='$value'";

    return $GLOBALS['db']->getOne($sql_select); 
}

//日志分析方案查询
function act_analyse_from_solution($solution_id){
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('action_solution').
        " WHERE solution_id=$solution_id";
    $solution = $GLOBALS['db']->getAll($sql_select);
    return $solution;
}

/*分析操作日志结果*/
function get_analyse_log($condition){
    if ($condition) {
        extract($condition);
    }
}

/*操作记录中的管理员ID列表*/
function get_log_admin($log_list){
    $admin_list = array();
    foreach ($log_list as $val) {
        $admin_list[] = $val['do_admin'];
    }

    $admin_list = array_unique($admin_list);
    return $admin_list;
}

/*统计操作非自己顾客的次数*/
function get_un_i_do($admin_list,$log_list){
    $un_i_do = array();
    foreach ($log_list as &$val) {
        if ($val['admin_id'] != $val['do_admin'] && 'users' == $val['module']) {
            $un_i_do[] = array(
                'sn' => $val['sn'],
                'do_admin' => $val['do_admin']
            );
        }
    }

    foreach ($un_i_do as &$un) {
        foreach ($admin_list as &$ad) {
            if ($un['do_admin'] == $ad['admin_id']) {

            }
        }
    }

    return $un_i_do;
}
