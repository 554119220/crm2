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
 * $Id:  serve.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$res = array ('response_action' => 'list_service','switch_tag' => true, 'id' => $_REQUEST['tag']);

date_default_timezone_set('Asia/Shanghai');
/* 第一回访 */
if ($_REQUEST['act'] == 'first_trace')
{
     admin_priv('first_trace');

     $days_three = time() -3*24*3600; // 三天前
     $days_five  = $three_days -2*24*3600; // 五天前

     $sql = 'SELECT i.user_id,i.consignee,i.mobile,i.tel,i.receive_time,o.goods_name, '.
          ' i.receive_time+g.take_days*o.goods_number take_time,i.order_id,i.add_time,a.user_name add_admin, '.
          "IF(u.service_time>$days_three,u.service_time,'-') recently FROM ".$GLOBALS['ecs']->table('order_info').
          ' i,'.$GLOBALS['ecs']->table('admin_user').' a,'.$GLOBALS['ecs']->table('order_goods').' o,'.
          $GLOBALS['ecs']->table('goods').' g,'. $GLOBALS['ecs']->table('users').
          ' u WHERE i.add_admin_id=a.user_id AND i.user_id=u.user_id AND '.
          ' o.goods_id=g.goods_id AND i.order_id=o.order_id';

     if (! admin_priv('all', '', false))
     {
          $sql .= " AND u.admin_id={$_SESSION['admin_id']} ";
     }

     // 最近三天确认收货的顾客
     $res_three = $GLOBALS['db']->getAll($sql." AND i.receive_time>$days_three GROUP BY i.order_id");

     foreach ($res_three as &$val)
     {
          $val['add_time']       = date('Y-m-d', $val['add_time']);
          $val['take_timetable'] = $val['goods_name'].date('Y-m-d',$val['take_time']);
          $val['receive_time']   = date('Y-m-d',$val['receive_time']);

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

     $smarty->assign('ur_here', '最近三天确认收货的顾客');
     $smarty->assign('user_list_3', $res_three);
     //$smarty->assign('user_list_5', $res_five);

     assign_query_info();
     $res['main'] = $smarty->fetch('first_trace.htm');

     die($json->encode($res));
}

/*------------------------------------------------------ */
//-- 用户帐号列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
     /* 检查权限 */
     admin_priv('serve_list');
     $smarty->assign('user_ranks',   $ranks);
     $smarty->assign('ur_here',      $_LANG['01_serve_list']);

     $smarty->assign('service_class', get_serviceClass());
     $smarty->assign('service_manner', get_serviceManner());

     $smarty->assign('admin_list', get_admin('session'));

     $service_list = service_list();

     $smarty->assign('service_list', $service_list['service_list']);
     $smarty->assign('filter',       $service_list['filter']);
     $smarty->assign('record_count', $service_list['record_count']);
     $smarty->assign('page_count',   $service_list['page_count']);
     $smarty->assign('action',       $_SESSION['action_list']);
     $smarty->assign('full_page',    1);
     $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
     $smarty->assign('country_list', get_regions());
     $smarty->assign('province_list', get_regions(1,1));

     assign_query_info();
     $res['main'] = $smarty->fetch('serve_list.htm');

     die($json->encode($res));
}

/*------------------------------------------------------ */
//-- ajax返回用户列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
     $service_list = service_list();

     $smarty->assign('service_list',    $service_list['service_list']);
     $smarty->assign('filter',          $service_list['filter']);

     $smarty->assign('record_count',    $service_list['record_count']);
     $smarty->assign('page_count',      $service_list['page_count']);

     $sort_flag  = sort_flag($service_list['filter']);
     $smarty->assign($sort_flag['tag'], $sort_flag['img']);

     make_json_result($smarty->fetch('serve_list.htm'), '', array('filter' => $service_list['filter'], 'page_count' => $service_list['page_count']));
}

/*------------------------------------------------------ */
//-- 预约服务
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'check')
{
     $smarty->assign('user_ranks',   $ranks);
     $smarty->assign('ur_here',      $_LANG['02_serve_check']);

     $sql = 'SELECT u.user_id, u.user_name, u.mobile_phone, u.home_phone, u.age_group, u.sex, '.
          's.handler,s.service_time,u.admin_name,s.logbook FROM '.$GLOBALS['ecs']->table('users').
          ' u, '.$GLOBALS['ecs']->table('service').' s WHERE u.user_id=s.user_id AND s.handler<>0 ';
     //if (!admin_priv('all', '', 'false'))
     //{
          $sql .= " AND u.admin_id={$_SESSION['admin_id']} ";
     //}

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

     assign_query_info();
     $res['main'] = $smarty->fetch('serve_schedule.htm');

     die($json->encode($res));
}

else if ($_REQUEST['act'] == 'search_chr')
{
     $chr_id = $_POST['chr_id'];

     $_POST['chr_id'] && $where = " AND characters LIKE '%,$chr_id,%'";
     $sql = 'SELECT u.user_id, u.user_name, FROM_UNIXTIME(s.handler, "%Y-%m-%d %H:%m") add_time, u.mobile_phone, u.home_phone, u.sex, u.admin_name, FROM_UNIXTIME(s.service_time, "%Y-%m-%d") service_time, u.age_group FROM '.$GLOBALS['ecs']->table('users').' u,'.$GLOBALS['ecs']->table('service'). " s WHERE s.service_time=u.service_time AND u.admin_id=$_SESSION[admin_id] $where";

     $_POST['subscribe'] == 'yes' && $sql = 'SELECT u.user_id, u.user_name, FROM_UNIXTIME(s.handler, "%Y-%m-%d %H:%m") add_time, u.mobile_phone, u.home_phone, u.sex, u.admin_name, FROM_UNIXTIME(s.service_time, "%Y-%m-%d") service_time, u.age_group FROM '.$GLOBALS['ecs']->table('users').' u, '.$GLOBALS['ecs']->table('service')." s WHERE u.user_id=s.user_id AND s.handler<>0 AND u.admin_id=$_SESSION[admin_id] $where ORDER BY s.handler ASC";

     $user_list = $GLOBALS['db']->getAll($sql);

     if (empty($user_list))
     {
          die(0);
     }

     $smarty->assign('user_list', $user_list);
     die($smarty->fetch('serve_schedule.htm'));
}


/**
 *  返回用户列表数据
 *
 * @access  public
 * @param
 *
 * @return void
 */
function service_list()
{
     $result = get_filter();
     if ($result === false)
     {
          /* 过滤条件 */
          $_REQUEST['keywords'] == '姓名' && $_REQUEST['keywords'] = '';

          $_REQUEST['keywords']  && $filter['keywords']  = trim($_REQUEST['keywords']);
          $_REQUEST['class']     && $filter['class']     = trim($_REQUEST['class']);
          $_REQUEST['manner']    && $filter['manner']    = trim($_REQUEST['manner']);
          $_REQUEST['admin_id']  && $filter['admin_id']  = trim($_REQUEST['admin_id']);
          $_REQUEST['startTime'] && $filter['startTime'] = strtotime(trim($_REQUEST['startTime']));
          is_numeric($_REQUEST['startTime']) && $filter['startTime'] = $_REQUEST['startTime'];
          $_REQUEST['endTime']   && $filter['endTime']   = strtotime(trim($_REQUEST['endTime']));
          is_numeric($_REQUEST['endTime']) && $filter['endTime'] = $_REQUEST['endTime'];

          if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
          {
               $filter['keywords'] = json_str_iconv($filter['keywords']);
          }

          $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'service_id' : trim($_REQUEST['sort_by']);
          $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

          $ex_where = ' WHERE 1 ';
          if ($filter['keywords'])
          {
               $ex_where .= " AND user_name LIKE '%".mysql_like_quote($filter['keywords'])."%'";
          }

          $ex_where .= ' AND c.class_id=s.service_class AND m.manner_id=s.service_manner ';

          // 增加起止时间、服务类别、服务方式、所属客服
          $filter['startTime'] && $ex_where .= " AND s.service_time>$filter[startTime] ";
          $filter['endTime']   && $ex_where .= " AND s.service_time<$filter[endTime] ";
          $filter['class']     && $ex_where .= " AND s.service_class=$filter[class] ";
          $filter['manner']    && $ex_where .= " AND s.service_manner=$filter[manner] ";
          $filter['admin_id']  && $ex_where .= " AND s.admin_id=$filter[admin_id] ";

          $filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".
               $GLOBALS['ecs']->table('service').' s,'.$GLOBALS['ecs']->table('service_class').
               ' c,'.$GLOBALS['ecs']->table('service_manner').' m '.$ex_where);

          if (admin_priv('all', '', false))
          {
               $ex_where .= ' AND s.admin_id>0 ';
          }
          elseif (admin_priv('serve_list', '', false))
          {
               $ex_where .= " AND s.admin_id={$_SESSION['admin_id']}";
               //$ex_where .= $filter['admin_id'] ? $filter['admin_id'] : $_SESSION['role_id'];
          }
          else
          {
               $ex_where .= " AND s.admin_id={$_SESSION['admin_id']}"; 
          }

          /* 分页大小 */
          $filter = page_and_size($filter);
          $sql = 'SELECT s.user_id, s.admin_name, s.service_id, c.class service_class, '.
               'm.manner service_manner,service_time,s.user_name,service_status,logbook,admin_id FROM '
               .$GLOBALS['ecs']->table('service').' s,'.$GLOBALS['ecs']->table('service_class').
               ' c,'.$GLOBALS['ecs']->table('service_manner').' m '.$ex_where.' ORDER by '.
               $filter['sort_by'].' '.$filter['sort_order'].' LIMIT '.$filter['start'].','.$filter['page_size'];
          $filter['keywords'] = stripslashes($filter['keywords']);
          set_filter($filter, $sql);
     }
     else
     {
          $sql    = $result['sql'];
          $filter = $result['filter'];
     }

     $service_list = $GLOBALS['db']->getAll($sql);

     foreach ($service_list as &$val)
     {
          $val['service_time'] = date('Y-m-d H:i', $val['service_time']);
     }

     $arr = array (
          'service_list' => $service_list,
          'filter'       => $filter,
          'page_count'   => $filter['page_count'],
          'record_count' => $filter['record_count']
     );

     return $arr;
}

/**
 * 获取服务类别
 **/
function get_serviceClass() {
     $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('service_class');
     return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取服务方式
 **/
function get_serviceManner() {
     $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('service_manner');
     return $GLOBALS['db']->getAll($sql);
}
?>
