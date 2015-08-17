<?php
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

/*-- 服务子菜单 --*/
if ($_REQUEST['act'] == 'menu')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav  = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);

    die($smarty->fetch('left.htm'));
}

//事件提醒
//  包括（ 预约提醒、站内信、备忘录、黑名单警报、通话录音推送）
elseif ($_REQUEST['act'] == 'traversal_appointments'){
    $res = array(
        'blacklist_ctr' => false,
        'service'       => array(),
    );

    /* 预约服务*/
    if(strstr(MEMBER_SALE,$_SESSION['role_id'])){
        $admin_id     = $_SESSION['admin_id'];
        $current_time = strtotime(date('Y-m-d H:00:00',$_SERVER['REQUEST_TIME']));

        $sql_select = 'SELECT appointments_id,appointments_name,alarm_time,user_id,user_name,comment,confirm_times FROM '.
            $GLOBALS['ecs']->table('appointments').
            " WHERE admin_id=$admin_id AND status=1 AND alarm_time=$current_time".
            ' AND confirm_times<3 ORDER BY alarm_time ASC';

        $appointments    = $GLOBALS['db']->getAll($sql_select);
        if($appointments){
            foreach($appointments as $val){
                $pre_differ = $val['alarm_time'] + 10 * 60 * $val['confirm_times'];

                if($_SERVER['REQUEST_TIME'] >= $pre_differ){
                    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('appointments').' SET confirm_times=confirm_times+1 WHERE appointments_id='
                        .$val['appointments_id'];
                    $GLOBALS['db']->query($sql_update);

                    $val['alarm_time'] = date('Y-m-d H时',$val['alarm_time']);
                    $res['service'][] = "<p>【{$val['alarm_time']}】你将预约【{$val['user_name']}】进行服务<br/>".  "留言：{$val['comment']}<br/></p>";
                }
            }
        }
    }elseif(admin_priv('all','',false) || admin_priv('ignore_blacklist','',false)){
        /*解除黑名单推送*/
        $users_blacklist = push_blacklist('userssyn');
        if(!$users_blacklist){
            $users_blacklist    = push_blacklist('users');
        }

        /*黑名单参照*/
        if($users_blacklist){
            foreach($users_blacklist as $key=>&$val){
                if(empty($val)){
                    $users_blacklist[$key] = 'error_null';
                }
            }

            $sql_select = 'SELECT user_name,IFNULL(mobile,0) AS mobile FROM '.$GLOBALS['ecs']->table('network_blacklist').
                " WHERE user_name LIKE '%{$users_blacklist['user_name']}%' OR mobile LIKE '%{$users_blacklist['mobile_phone']}%' GROUP BY blacklist_user_id";
            $refer_to = $GLOBALS['db']->getAll($sql_select);

            if($refer_to){
                $smarty->assign('refer_to',$refer_to);
                $smarty->assign('users_blacklist',$users_blacklist);
                $res['message']       = $smarty->fetch('push_blacklist.htm');
                $res['title']         = '排除黑名单警报';
                $res['btncontent']    = '';
                $res['blacklist_ctr'] = true;
            }
        }
    }

    die($json->encode($res));
}

//所有服务记录
elseif ($_REQUEST['act'] == 'records')
{
    $result = service_records(); // 服务记录
    if(admin_priv('all','',false)) {
        $customer_service = get_admin_tmp_list();
        $rsql = 'select role_id,role_name FROM '.$GLOBALS['ecs']->table('role').
            ' WHERE role_id IN('.SALE.')'." ORDER BY convert(role_name using gbk) ASC";
        $role_list = $GLOBALS['db']->getAll($rsql);
        $smarty->assign('role_list',$role_list);
    }else{
        $sql_select = 'SELECT user_name, user_id FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE status>0 AND stats>0 AND role_id={$_SESSION['role_id']}";
        $customer_service = $GLOBALS['db']->getAll($sql_select);
    }
    $smarty->assign('records',$result['records']);
    $smarty->assign('filter',$result['filter']);
    isset($customer_service) && $smarty->assign('admin_list', $customer_service);
    $smarty->assign('records_div',$smarty->fetch('records_div.htm'));
    $res['main'] = $smarty->fetch('records.htm');
    die($json->encode($res));
}

//添加服务
elseif ($_REQUEST['act'] == 'insert_service')
{
    $sql_select = 'SELECT user_name, user_id FROM '.$GLOBALS['ecs']->table('admin_user').
        " WHERE transfer=1 AND status=1 ORDER BY user_id ASC";

    $adminList = $GLOBALS['db']->getAll($sql_select);

    $service_class = service_class();
    $smarty->assign('service_class',$service_class);

    $manner = service_manner();
    $smarty->assign('service_manner',$manner);
    $smarty->assign('adminList',$adminList);

    $res['main'] = $smarty->fetch('server_add.htm');
    die($json->encode($res));
}

///搜索健康档案
elseif ($_REQUEST['act'] == 'sch_healthy_manager')
{
    $res['main'] = $smarty->fetch('sch_healthy_manager.htm');
    die($json->encode($res));
}


//添加服务记录
elseif ($_REQUEST['act'] == 'add_service')
{
    $admin_name     = $_SESSION['admin_name'];
    $admin_id       = $_SESSION['admin_id'];
    $platform       = intval($_SESSION['role_id']);
    $group_id       = $_SESSION['group_id'];
    $service_class  = intval($_POST['service_class']);
    $service_manner = intval($_POST['service_manner']);
    $service_time   = strtotime($_POST['service_time']);
    $alarm_time     = mysql_real_escape_string($_REQUEST['alarm_time']);

    if($service_time == '') {
        $service_time = strtotime(date('Y-m-d H:i'));
    }

    $logbook          = mysql_real_escape_string($_POST['logbook']);
    $user_id          = intval($_POST['user_id']);
    $show_sev         = intval($_POST['show_it']);
    $user_active      = intval($_POST['user_active']);

    $sql_one = 'SELECT user_name FROM'.$GLOBALS['ecs']->table('users')." WHERE user_id=$user_id";
    $user_name = $GLOBALS['db']->getOne($sql_one);

    $server_score = array(
        1 => $_POST['company_server'],
        2 => $_POST['order_server'],
        3 => $_POST['product_server']
    );

    //判断是否频繁或重复添加 第一次提交后1分才能提交第二次
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('service').
        " WHERE user_id=$user_id AND TIMESTAMPDIFF(MINUTE,FROM_UNIXTIME(service_time,'%Y-%m-%d %H:%i'),NOW())<1";

    $result = $GLOBALS['db']->getOne($sql_select);

    //频繁添加 
    if($result) {
        $res['timeout'] = 2000;
        $res['req_msg'] = true;
        $res['code']    = 0;
        $res['message'] = '不能频繁添加';

        die($json->encode($res));
    }else{
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('service').
            '(user_id,user_name,admin_name,service_class,service_manner,service_time,logbook,admin_id,platform,group_id,show_sev,user_active)'.
            "VALUES($user_id,'$user_name','$admin_name',$service_class,$service_manner,'$service_time','$logbook',$admin_id,$platform,$group_id,$show_sev,$user_active)";

        $result     = $GLOBALS['db']->query($sql_insert);
        $service_id = $GLOBALS['db']->insert_id();

        if($show_sev == 1 && $result) {
            //空号停机不计入服务数量
            if($result){
                if(strstr($logbook,'空号') || strstr($logbook,'停机')){
                    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('service')." SET valid=0 WHERE (logbook LIKE '%空号%' OR logbook LIKE '%停机%' OR logbook LIKE '%没人接%' OR logbook LIKE '%不接%') AND service_id=$service_id";
                    $GLOBALS['db']->query($sql_update);
                }
            }
        }

        /*删除过期一天的预约服务*/
        $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('appointments').
            ' WHERE '.$_SERVER['REQUEST_TIME'].'-alarm_time>3600*24';
        $GLOBALS['db']->query($sql_delete);

        $res = array(
            'timeout' => 2000,
            'req_msg' => true,
            'code'    => false,
            'message' => ''
        );

        //修改上次服务时间
        if($result) {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').
                " SET service_time=$service_time"." WHERE user_id=$user_id";
            $res['code'] = $GLOBALS['db']->query($sql_update);
        }

        if($res['code']) {
            $res['message'] = '服务添加成功';
        } else {
            $res['message'] = '服务添加失败';
        }

        /*添加预约*/
        if($alarm_time != ''){
            $alarm_time = strtotime($alarm_time);
            $alarm_time = strtotime(date('Y-m-d H:00:00',$alarm_time));
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('appointments').
                '(appointments_name,alarm_time,user_id,user_name,comment,admin_id)VALUES('.
                "'预约【".$user_name."】',$alarm_time,$user_id,'$user_name','$logbook',$admin_id)";

            $res['code'] = $GLOBALS['db']->query($sql_insert);
            if($res['code']){
                $res['message'] = '预约成功';
            }else{
                $res['message'] = '预约失败';
            }
        }
    }

    die($json->encode($res));
}

//修改服务
elseif ($_REQUEST['act'] == 'service_update')
{
    $adminList      = get_admin();
    $user_name      = trim($_GET['user_name']);
    $result         = service_update($user_name);
    $service_class  = service_class();
    $service_manner = service_manner();

    $smarty->assign('adminList',$adminList);
    $smarty->assign('service_class',$service_class);
    $smarty->assign('service_manner',$service_manner); 
    $smarty->assign('user_name',$user_name);
    $smarty->assign('service_info',$result);

    $res['main']= $smarty->fetch('update_delete.htm');
    die($json->encode($res));
}

//删除服务
elseif ($_REQUEST['act'] == 'service_delete')
{
    $user_name  = $_GET['user_name'];
    $sql_delete = 'DELETE FROM '.$GLOBALS['esc']->table('service')." WHERE user_name='$user_name'";

    $result=mysql_query($sql_delete);

    if($result) {
        die(1); 
    } else {
        die(0);
    }
}

//添加预约服务模板
elseif($_REQUEST['act'] == 'add_appointment_view'){
    $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
    $res = array(
        'req_msg'    => true,
        'message'    => '',
        'btncontent' => false
    );

    if($user_id != 0){
        $sql_select = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('users')." WHERE user_id=$user_id";
        $user_info  = $GLOBALS['db']->getRow($sql_select);

        $smarty->assign('user_info',$user_info);

        $res['message'] = '您预定将在 <input class="Wdate" id="alarm_time" onclick="WdatePicker()" type="text"/> 再次对【' .$user_info['user_name'].'】顾客进行服务'.
            '<br/><span style="color:red;float:left;display:block;" id="alarm_info"></span>'.
            '<br/><input type="button" onclick="submitService(this)" class="b_submit" value="确定" style="float:right;"/>';

        $res['title'] = "正在预约 {$user_info['user_name']}";
    }

    die($json->encode($res));
}

/*预约服务管理*/
elseif($_REQUEST['act'] == 'book_service_list'){
    $admin_id     = $_SESSION['admin_id'];
    $current_time = strtotime(date('Y-m-d H:00:00',$_SERVER['REQUEST_TIME']));
    $where = " WHERE status=1 AND admin_id=$admin_id AND alarm_time>=$current_time";
    $condition = '';

    if(!empty($_REQUEST['sch'])){
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $start_time = strtotime($_REQUEST['start_time']);
            $end_time   = strtotime($_REQUEST['end_time']);
            $where      .= " AND alarm_time>$start_time AND alarm_time<=$end_time";
            $condition  .= "&start_time=$start_time&end_time=$end_time";
        }

        if(!empty($_REQUEST['user_name']) && mysql_real_escape_string($_REQUEST['user_name'])!=''){
            $user_name = mysql_real_escape_string($_REQUEST['user_name']);
            $where     .= " AND user_name LIKE '%$user_name%'";
            $condition .= "&user_name=$user_name";
        }
    }

    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else{
        $filter['page_size'] = 20; 
    }

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20; 
    }

    $sql_select   = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('appointments').$where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
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

    $filter = array (
        'filter'        => $filter,
        'page_count'    => $filter['page_count'],
        'recorder_size' => $filter['recorder_size'],
        'record_count'  => $filter['record_count'],
        'page_size'     => $filter['page_size'],
        'page'          => $filter['page'],
        'page_set'      => $page_set,
        'condition'     => $condition,
        'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'           => $filter['page']*$filter['page_size'],
        'act'           => 'book_service_list',
    );

    $sql_select = 'SELECT appointments_id,appointments_name,user_name,alarm_time,comment FROM '.$GLOBALS['ecs']->table('appointments').$where.
        ' ORDER BY alarm_time ASC'.
        ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";
    $appointments_list = $GLOBALS['db']->getAll($sql_select);

    foreach($appointments_list as &$val){
        $val['alarm_time'] = date('Y-m-d H:i',$val['alarm_time']);
    }

    $smarty->assign('appointments_list',$appointments_list);
    $smarty->assign('filter',$filter);

    if(isset($_REQUEST['sch'])){
        $res['main'] = $smarty->fetch('sch_appointments.htm');
    }else{
        $res['main'] = $smarty->fetch('appointments.htm');
    }

    die($json->encode($res));
}

//修改预约服务
elseif($_REQUEST['act'] == 'mod_appointment'){
    $appointments_id = isset($_REQUEST['appointments_id']) ? intval($_REQUEST['appointments_id']) : 0;
    $behave          = isset($_REQUEST['behave']) ?  mysql_real_escape_string($_REQUEST['behave']) : '';
    $tr_index        = isset($_REQUEST['tr_index']) ? intval($_REQUEST['tr_index']) : 0;

    $res = array(
        'req_msg' => true,
        'code'    => false,
        'message' => '',
        'timeout' => 2000
    );

    switch($behave){
    case 'del' :
        $sql = ' DELETE FROM '.$GLOBALS['ecs']->table('appointments').
            " WHERE appointments_id=$appointments_id";
        $res['code'] = $GLOBALS['db']->query($sql);

        if($res['code']){
            $res['message'] = '删除成功';
        }else{
            $res['message'] = '删除失败';
        }
        break;

    case 'postphone' :  //推迟
        if(!empty($_REQUEST['alarm_time']) && intval($_REQUEST['alarm_time']) != 0){
            $res['alarm_time']      = $_REQUEST['alarm_time'];
            $res['appointments_id'] = $appointments_id;
            $alarm_time             = strtotime($_REQUEST['alarm_time']);

            $sql = 'UPDATE '.$GLOBALS['ecs']->table('appointments').
                " SET alarm_time=$alarm_time WHERE appointments_id=$appointments_id";

            if($GLOBALS['db']->query($sql)){
                $res['code']    = true;
                $res['message'] = '推迟成功';
            }else{
                $res['message'] = '推迟失败';
            }
        }
        break;
    }

    $res['behave']   = $behave;
    $res['tr_index'] = $tr_index;

    die($json->encode($res));
}

//服务查询
elseif ($_REQUEST['act'] == 'service_search')
{
    $admin_id    = $_SESSION['admin_id'];
    $user_id     = intval($_POST['user_id']);
    $start_date  = strtotime($_POST['startTime']);
    $end_date    = strtotime($_POST['endTime']);
    $where       = " WHERE s.user_id=$user_id ";

    $append      =  $GLOBALS['ecs']->table('service').' AS s LEFT JOIN '.$GLOBALS['ecs']->table('service_class').
        ' AS c ON s.service_class=c.class_id LEFT JOIN '.$GLOBALS['ecs']->table('service_manner').
        ' AS m ON s.service_manner=m.manner_id';

    $sql_select = 'SELECT s.admin_name,c.class,m.manner,s.logbook,s.service_time FROM '.$append;

    if($start_date != "" && $end_date != "") {
        $where .= " AND s.service_time BETWEEN $start_date AND $end_date ";
    }

    $sql_select  .= $where.' ORDER BY s.service_time DESC ';
    $result       = $GLOBALS['db']->getAll($sql_select);

    foreach($result AS &$val) {
        $val['service_time'] = date('Y-m-d H:i',$val['service_time']);
    }

    $smarty->assign('service',$result);
    $smarty->assign('page',$page);
    $res['main'] = $smarty->fetch('service_records.htm');
    $res['code'] = 1;

    die($json->encode($res));
}


//存货订单管理
elseif ($_REQUEST['act'] == 'inventory_order_list')
{
    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20; 
    }

    $sql_select = 'SELECT COUNT(*) FROM '
        .$GLOBALS['ecs']->table('inventory_order')
        .' AS iv LEFT JOIN '.$GLOBALS['ecs']->table('users')
        .' AS u ON iv.user_id=u.user_id '
        .' GROUP BY iv.user_id';

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
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
    foreach($user_list AS $val) {
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('inventory_order')
            .' WHERE user_id='.$val['user_id'];

        $inventory_order = $GLOBALS['db']->getAll($sql_select);
        $val['inventory_order'] = $inventory_order;
    }

    $smarty->assign('inventory_order',$inventory_order);
    $smarty->assign('filter',$filter);

    $res['main'] = $smarty->fetch('ventory_order_list.htm');

    die($json->encode($res));
}

//搜索存货订单
elseif ($_REQUEST['act'] == 'sch_inventory')
{
    $user_name  = isset($_REQUEST['user_name']) ? mysql_real_escape_string(trim($_REQUEST['user_name'])) : '';
    $phone      = isset($_REQUEST['phone']) ? mysql_real_escape_string(trim($_REQUEST['phone'])) : '';
    $store_time = isset($_REQUEST['store_time']) ? mysql_real_escape_string(trim($_REQUEST['store_time'])) : '';
    $order_sn   = isset($_REQUEST['order_sn']) ? mysql_real_escape_string(trim($_REQUEST['order_sn'])) : '';

    $where     = ' WHERE 1';
    $condition = '';

    if($user_name != '') {
        $where .= " AND user_name LIKE '%$user_name%' ";
        $condition .= "&user_name=$user_name";
    }

    if($phone != '') {
        $where     .= " AND phone LIKE '%$phone%' ";
        $condition .= "&phone=$phone";
    }

    if($store_time != '') {
        $where     .= " AND store_time=$store_time ";
        $condition .= "&store_time=$store_time";
    }

    if($order_sn != '') {
        $where     .= " AND order_sn=$order_sn ";
        $condition .= "&order_sn=$order_sn";
    }

    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20; 
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users');

    $res['response_action'] = 'search_service';
    $res['main']            = $smarty->fetch('sch_inventory_order.htm');

    die($json->encode($res));
}

//获取产品的服用明细
elseif ($_REQUEST['act'] == 'get_goods_hlp')
{
    $goods_id   = intval($_REQUEST['goods_id']); 
    $goods_name = mysql_real_escape_string($_REQUEST['goods_name']);
    $goods_num  = intval($_REQUEST['goods_num']);

    $sql_select = 'SELECT * FROM '
        .$GLOBALS['ecs']->table('goods')." WHERE goods_id=$goods_id";

    $goods_info = $GLOBALS['db']->getAll($sql_select);
    $goods_info = $goods_info[0];

    $res = array('req_msg'=>true,'timeout'=>'16000','message'=>'','code'=>true);
    if(!$goods_info)
    {
        $res['timeout'] = 2000;
        $res['message'] = '查询错误';
    }
    else
    {
        $res['timeout'] = '20000';
        $res['message'] = '<p>【'.$goods_name.'】'."每天{$goods_info['everyday_use']}次,每次{$goods_info['every_num']}{$goods_info['every_num_units']},$goods_num"
            .$goods_info['goods_number_units']
            .'可服用'.floor($goods_num*$goods_info['take_days']/3600/24).'天'
            .'</p><p>【生产商】'.$goods_info['provider_name']
            .'</p><p>【说明】'.$goods_info['goods_brief']
            .'</p><p>【功能】'.$goods_info['functios'];
    }

    die($json->encode($res));
}

//获取套餐
elseif ($_REQUEST['act'] == 'get_package')
{
    $goods_id   = intval($_REQUEST['goods_id']);
    $sql_select = 'SELECT packing_id FROM '.$GLOBALS['ecs']->table('packing_goods')
        ." WHERE goods_id=$goods_id";
    $package_list = $GLOBALS['db']->getCol($sql_select);

    if($package_list)
    {
        $package_list_total = count($package_list);
        for($i = 0;$i<$package_list_total;$i++)
        {
            $sql_select = 'SELECT packing_name,packing_price,goods_price FROM '.$GLOBALS['ecs']->table('packing').
                " WHERE packing_id={$package_list[$i]} AND is_delete=0";
            $packing[$i]['packing_info'] = $GLOBALS['db']->getRow($sql_select);

            $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('packing_goods').
                " WHERE packing_id={$package_list[$i]}";

            $packing[$i]['goods_list'] = $GLOBALS['db']->getAll($sql_select);
        }

        $packing_show = '';
        $packing_total = count($packing);
        for($i = 0;$i<$packing_total;$i++)
        {
            $packing_show .="<details><summary>【{$packing[$i]['packing_info']['packing_name']}
                】商品价格:￥{$packing[$i]['packing_info']['goods_price']} 套餐价格:￥{$packing[$i]['packing_info']['packing_price']}</summary>";

            foreach($packing[$i]['goods_list'] AS $val)
            {
                $packing_show .= '<p><strong>'.$val['goods_name'].' </strong>'.$val['num'].'*￥'.$val['goods_price'].'</p>';
            }
            $packing_show .= '</details>';
        }
    }

    $res['req_msg'] = true ;
    $res['timeout'] = 30000;
    if($packing_show != '')
    {
        $res['message'] = $packing_show;
    }
    else
    {
        $res['message'] = '该产品没有套餐';
    }

    die($json->encode($res));
}

/* 发送短信 */
elseif ($_REQUEST['act'] == 'display_send_ui')
{
    // 顾客等级
    $special_ranks = get_type_list();
    foreach ($special_ranks as $val) {
        $send_rank['2_' . $val['type_id']] = $val['type_name'];//.'['.$val['num'].']';
    }
    $smarty->assign('send_rank',   $send_rank);

    // 销售平台
    $smarty->assign('platform', get_platform_list());

    $smarty->assign('country_list',   get_regions());
    $smarty->assign('province_list',  get_regions(1,1));

    if (admin_priv('send_sms_all', '', false)) {
        $admin_list = get_admin_tmp_list();
    } elseif (admin_priv('send_sms_part', '', false)) { // 所属部门客服
        // 部门
        $admin_list = get_admin_tmp_list($_SESSION['role_id']);
    } elseif (admin_priv('send_sms_group', '', false)) { // 所属小组客服
        // 小组
        $admin_list = get_admin_list_by_group($_SESSION['group_id']);
    } else {
        // 客服
        $admin_list = '';
    }

    $sql_select = 'SELECT eff_name, eff_id FROM '.$GLOBALS['ecs']->table('effects').' WHERE available=1 ORDER BY sort DESC';
    $effect_list = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('effect_list', $effect_list);

    $smarty->assign('admin_list', $admin_list);

    assign_query_info();
    $res['main'] = $smarty->fetch('sms_send_ui.htm');
    die($json->encode($res));
}

/* 检索符合条件的用户数量 */
elseif ($_REQUEST['act'] == 'send_sms_count') {
    $res['code']    = 1;
    $res['req_msg'] = true;
    $res['timeout'] = 2000;

    if (!admin_priv('send_sms', '', false)){
        $res['message'] = '当前帐号无权发送短信！';
        die($json->encode($res));
    }

    $req = addslashes_deep($_REQUEST);

    $ex_where = 1;

    // 如果到区 就使用区
    if (!isset($req['nationwide'])) {
        if ($req['district']) {
            $ex_where .= " AND addr.district={$req['district']}";
        } elseif ($req['city']) { // 如果到市 就使用市
            $ex_where .= " AND addr.city={$req['city']}";
        } elseif ($req['province']) { // 如果到省 就使用省
            $ex_where .= " AND addr.province={$req['province']}";
        }
    }

    // 顾客类型
    if (isset($req['eff_id']) && $req['eff_id'] > 0) {
        $ex_where .= " AND u.eff_id={$req['eff_id']}";
    }

    // 顾客等级
    if (isset($req['send_rank']) && $req['send_rank'] > 0) {
        $send_rank = explode('_', $req['send_rank']);
        $ex_where .= " AND u.customer_type={$send_rank[1]}";
    }

    // 顾客平台
    if (isset($req['platform']) && $req['platform'] > 0) {
        $ex_where .= " AND u.platform={$req['platform']}";
    }

    // 顾客性别
    $sex = intval($_POST['sex']);
    if ($sex && 4 != $sex) {
        $sex = 3 == $sex ? 0 : $sex;
        $ex_where .= " AND u.sex=$sex";
    }

    // 所属客服
    $admin_id = intval($_REQUEST['admin_id']);
    if (admin_priv('send_sms_all', '', false)) {
        if ($admin_id > 0) {
            $ex_where .= " AND u.admin_id=$admin_id ";
        }
    } elseif (admin_priv('send_sms_part', '', false)) {
        if ($admin_id > 0) {
            $ex_where .= " AND u.admin_id=$admin_id AND u.role_id={$_SESSION['role_id']} ";
        }
    } elseif (admin_priv('send_sms_group', '', false)) {
        if ($admin_id > 0) {
            $ex_where .= " AND u.admin_id=$admin_id AND u.group_id={$_SESSION['group_id']} ";
        }
    } else {
        $ex_where .= " AND u.admin_id={$_SESSION['admin_id']} ";
    }

    // 统计号码数量
    $sql_select = "SELECT COUNT(DISTINCT u.mobile_phone) FROM ".$GLOBALS['ecs']->table('users').' u, '.
        $GLOBALS['ecs']->table('user_address')." addr WHERE $ex_where AND u.user_id=addr.user_id AND mobile_phone REGEXP '^1[34578][0-9]{9}$'";
    $mobile_total = $GLOBALS['db']->getOne($sql_select);

    // 计算短信条数
    $content_length = mb_strlen(str_replace(' ', '', $req['sms']).'【广州康健人生健康管理有限公司】', 'UTF-8');
    $mobile_number = substr_count($req['send_num'], ',');
    $mobile_total += $mobile_number ? $mobile_number+1 : 0;
    $sms_number = ceil($content_length/67) * $mobile_total;

    $res = array (
        'req_msg'=>true,
        'message'=> '有'.$mobile_total.'个号码，短息长度是'.$content_length.'个字符。需发送短信'.$sms_number.'条!',
    );

    die($json->encode($res));
}

/* 发送短信息 */
elseif ($_REQUEST['act'] == 'send_sms') {
    $res['code']    = 1;
    $res['req_msg'] = true;
    $res['timeout'] = 2000;

    if (!admin_priv('send_sms', '', false)){
        $res['message'] = '当前帐号无权发送短信！';
        die($json->encode($res));
    }
    require_once('includes/cls_sms.php');
    $sms = new sms;

    $phone = is_numeric($_POST['phone']) ? $_POST['phone'].',' : '';

    $fields = '';
    if ($_POST['plus_name']) $fields = ', u.user_name';

    $where = ' WHERE 1 ';

    // 按区域
    if (!isset($_POST['nationwide'])) {
        if ($_POST['district']) {
            $where .= ' AND a.district='.$_POST['district'];
        } elseif ($_POST['city']) {
            $where .= ' AND a.city='.$_POST['city'];
        } elseif($_POST['province']) {
            $where .= ' AND a.province='.$_POST['province'];
        }
    }

    $where != ' WHERE 1 ' && $where = ', '.$GLOBALS['ecs']->table('user_address').' a '.$where.' AND u.user_id=a.user_id ';

    // 性别
    $sex = intval($_POST['sex']);
    if ($sex && 4 != $sex) {
        $sex = 3 == $sex ? 0 : $sex;
        $where .= " AND u.sex=$sex";
    }

    // 功效
    $eff_id = intval($_POST['eff_id']);
    if ($eff_id) {
        $where .= " AND u.eff_id=$eff_id ";
    }

    // 所属客服
    $admin_id = intval($_REQUEST['admin_id']);
    if (admin_priv('send_sms_all', '', false)) {
        if ($admin_id > 0) {
            $ex_where .= " AND u.admin_id=$admin_id ";
        }
    } elseif (admin_priv('send_sms_part', '', false)) {
        if ($admin_id > 0) {
            $ex_where .= " AND u.admin_id=$admin_id AND u.role_id={$_SESSION['role_id']} ";
        }
    } elseif (admin_priv('send_sms_group', '', false)) {
        if ($admin_id > 0) {
            $ex_where .= " AND u.admin_id=$admin_id AND u.group_id={$_SESSION['group_id']} ";
        }
    } else {
        $ex_where .= " AND u.admin_id={$_SESSION['admin_id']} ";
    }

    // 销售平台
    $platform = isset($_REQUEST['platform']) ? intval($_REQUEST['platform']) : 0;
    if ($platform) {
        $where .= " AND u.platform=$platform ";
    }

    /*
    if ($platform != 0) {
        $sql = "SELECT DISTINCT i.user_id,mobile_phone $fields FROM ".$ecs->table('users').' u,'.$ecs->table('order_info').
            " i $where u.admin_id={$_SESSION['admin_id']} AND i.team=$platform".' AND u.mobile_phone<>""';
    } 
     */

    $send_rank = isset($_POST['send_rank']) ? $_POST['send_rank'] : 0;
    if ($send_rank != 0) {
        $rank_array = explode('_', $send_rank);
        if ($rank_array['0'] != 1) {
            $where .= " AND u.customer_type={$rank_array[1]} ";
        }
    }

    $sql = "SELECT u.mobile_phone $fields FROM ".$ecs->table('users').
        " u $where AND u.mobile_phone<>'' AND u.admin_id={$_SESSION['admin_id']}";

    if (!empty($sql)) {
        $row = $db->query($sql);
        while ($rank_rs = $db->fetch_array($row)) {
            $phone_value[] = $rank_rs['mobile_phone'];
            $username[$rank_rs['mobile_phone']] = $rank_rs['user_name'];
        }

        if(isset($phone_value)) {
            $phone .= implode(',', $phone_value);
        }
    }

    $msg = isset($_POST['sms']) ? mysql_real_escape_string($_POST['sms']) : '';
    if (empty($msg)){
        // 终止发送短信 返回错误信息
        $res['message'] = '无短信内容，已终止发送！';
        $res['code'] = 0;
        die($json->encode($res));
    }

    $send_date = isset($_POST['send_date']) ? $_POST['send_date'] : '';   

    $phone_tmp = explode(',', $phone);
    $phone_tmp = array_unique($phone_tmp);
    $phone_num = count($phone_tmp);

    $kjrs_offset = 100;  //每次发送短信的条数
    if ($fields) {
        foreach ($username as $mobile=>$uname){
            $result = $sms->send($mobile, str_replace('{Y}', $uname, $msg).'【广州康健人生健康管理有限公司】', $send_date);
        }
    } else {
        if ($phone_num > $kjrs_offset) {
            $kjrs_sendTime = ceil($phone_num/$kjrs_offset);

            //短信发送功能彻底通过测试后，可删除下行代码
            for ($i = 0; $i < $kjrs_sendTime; $i++) {
                if($phone = array_slice($phone_tmp, $i*$kjrs_offset, $kjrs_offset)) {
                    $phone = preg_grep('#^[1]\d{10}$#', $phone);
                    $result = $sms->send($phone, $msg, $send_date, $username);
                }
            }
        } else {
            $result = $sms->send($phone, $msg, $send_date, $username);
        }
    }

    if ($result == 1 || $result === NULL)//发送成功
    {
        $words_num = strlen($msg);
        $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('sms_log').'(phone_num,province,city,district,customer_type,sms_content,is_plus_user,send_time,admin_id,words_num)VALUES('."'$phone_num','$_POST[province]','$_POST[city]','$_POST[district]',RIGHT('$_POST[send_rank]',1),'$msg','$_POST[plus_name]',UNIX_TIMESTAMP(NOW()),$_SESSION[admin_id], $words_num)";
        $GLOBALS['db']->query($sql);

        // 短信发送成功
        $res['message'] = '已成功发送短信！';
    } else {
        // 短信发送失败
        @$error_detail = $sms->errors['server_errors']['error_no'].$sms->errors['api_errors']['error_no'];
        $res['message'] = $error_detail;
    }

    die($json->encode($res));
}

/* 发送记录 */
elseif ('sms_history' == $_REQUEST['act']) {

    if (admin_priv('sms_history_all', '', false)) {
        $ex_where = '';
    } elseif (admin_priv('sms_history_part', '', false)) {
        $ex_where = " AND role_id={$_SESSION['role_id']} ";
    } elseif (admin_priv('sms_history_group', '', false)) {
        $ex_where = " AND group_id={$_SESSION['group_id']} ";
    } else {
        $ex_where = " AND user_id={$_SESSION['admin_id']} ";
    }

    $sql_select = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('admin_user').
        " WHERE action_list LIKE '%,send_sms,%' AND status=1 $ex_where";
    $admin_list = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('admin_list', $admin_list);

    $sms_history = sms_history();

    if (empty($sms_history['outflow_list'])) {
        die($json->encode(array('req_msg'=>true,'timeout'=>2000,'code'=>1,'message'=>'未查到符合条件的短信记录！')));
    }

    $smarty->assign('sms_history', $sms_history['outflow_list']);
    $res['main'] = $smarty->fetch('sms_history_data.htm');

    $smarty->assign('page_count', $sms_history['page_count']);
    $smarty->assign('record_count', $sms_history['record_count']);
    $smarty->assign('page_size', $sms_history['page_size']);
    $smarty->assign('condition', $sms_history['condition']);
    $smarty->assign('page_list', $sms_history['page_set']);
    $smarty->assign('page_start', $sms_history['start']);
    $smarty->assign('page_end', $sms_history['end']);
    $smarty->assign('page', $sms_history['page']);
    $smarty->assign('act', 'sms_history');
    $smarty->assign('dst_script', 'service');

    $res['page'] = $smarty->fetch('page_fragment.htm');

    if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'Ajax') {
        die($json->encode($res));
    }

    $smarty->assign('data', $res['main']);
    $smarty->assign('page', $res['page']);
    unset($res['main'], $res['page']);

    $res['main'] = $smarty->fetch('sms_history.htm');

    die($json->encode($res));
}

// 事件提醒
elseif ($_REQUEST['act'] == 'event_remind')
{
    $res['main'] = $smarty->fetch('event_remind.htm');
    die($json->encode($res));
}

// 提醒基本设置
elseif ($_REQUEST['act'] == 'remind_conf')
{
    $res['main'] = $smarty->fetch('remind_conf.htm');
    die($json->encode($res));
}

elseif ($_REQUEST['act'] == 'check')
{
    $sql_select = 'SELECT u.user_id, u.user_name, u.mobile_phone, u.home_phone, u.age_group, u.sex, FROM_UNIXTIME(s.handler, "%Y-%m-%d %H:%i") add_time, FROM_UNIXTIME(s.service_time, "%Y-%m-%d") service_time, u.admin_name, s.logbook FROM '.$GLOBALS['ecs']->table('users').' u, '.$GLOBALS['ecs']->table('service').
        ' s WHERE u.user_id=s.user_id AND s.handler<>0 AND s.service_time=u.service_time ORDER BY s.handler DESC';
    $user_list = $GLOBALS['db']->getAll($sql_select);


    $sql = 'SELECT character_id, characters FROM '.$GLOBALS['ecs']->table('character').' ORDER BY sort';
    $characters = $GLOBALS['db']->getAll($sql);

    $smarty->assign('characters',$characters);
    $smarty->assign('userlist',$user_list);
    $res['main'] = $smarty->fetch('serve_schedule.htm');
    die($json->encode($res));  
}

//顾客信息编辑
elseif ($_REQUEST['act'] == 'edit')
{
    admin_priv('users_edit');
    $sql_select = " ";
}

//顾客详细地址
elseif ($_REQUEST['act'] == 'address')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $sql = 'SELECT a.*, c.region_name AS country_name, p.region_name AS province, ct.region_name AS city_name, d.region_name AS district_name FROM '.
        $ecs->table('user_address'). ' AS a '.
        ' LEFT JOIN ' . $ecs->table('region') . ' AS c ON c.region_id = a.country '.
        ' LEFT JOIN ' . $ecs->table('region') . ' AS p ON p.region_id = a.province '.
        ' LEFT JOIN ' . $ecs->table('region') . ' AS ct ON ct.region_id = a.city '.
        ' LEFT JOIN ' . $ecs->table('region') . ' AS d ON d.region_id = a.district '.
        " WHERE user_id='$id'";

    $address = $db->getAll($sql);

    $smarty->assign('address',$address);
    $res['main'] = $smarty->fetch('user_address_list.htm');
}

/**
 * 顾客健康档案
 */
elseif ($_REQUEST['act'] == 'healthy_manager')
{
    $user['user_id']   = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : '';
    $user['user_name'] = isset($_REQUEST['user_name']) ? mysql_real_escape_string($_REQUEST['user_name']) : '';
    $user['sex']       = isset($_REQUEST['sex']) ? intval($_REQUEST['sex']) : '';

    //获取病例
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('disease').' WHERE available=1';
    $disease_list = $GLOBALS['db']->getAll($sql_select);

    //既往病史
    $sql_select = 'SELECT s.sickness_id,s.disease,c.class_id FROM '.$GLOBALS['ecs']->table('sickness').' AS s LEFT JOIN '.$GLOBALS['ecs']->table('sick_class').' AS c ON s.class=c.class_id WHERE s.availble=1';
    $before_case = $GLOBALS['db']->getAll($sql_select);
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('sick_class');
    $case_list = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('users',$user);
    $smarty->assign('before_case',$before_case);    //疾病
    $smarty->assign('case_list',$case_list);        //疾病类型
    $smarty->assign('disease_list',$disease_list);
    $res['main'] = $smarty->fetch('healthy_file.htm');

    die($json->encode($res));
}

/**
 * 返回匹配用户资料
 */
elseif ($_REQUEST['act'] == 'get_match_user')
{
    $user_name = isset($_REQUEST['user_name']) ? mysql_real_escape_string($_REQUEST['user_name']) : '';    //模糊查找顾客姓名

    $sql_select = 'SELECT u.user_id,u.user_name,u.sex,a.address FROM '.$GLOBALS['ecs']->table('users').
        ' AS u LEFT JOIN '.$GLOBALS['ecs']->table('user_address').' AS a ON u.user_id=a.user_id'.
        " WHERE u.user_name LIKE '%$user_name%'";
    $user_list = $GLOBALS['db']->getAll($sql_select);

    $res = $user_list;

    die($json->encode($res));
}

//上传健康档案基本信息
elseif ($_REQUEST['act'] == 'upload_healthy')
{
    $user_id         = intval($_REQUEST['user_id']);
    $user_name       = mysql_real_escape_string($_REQUEST['user_name']);
    $admin_id        = $_SESSION['admin_id'];
    $born_address    = mysql_real_escape_string(trim($_REQUEST['born_address']));
    $work_address    = mysql_real_escape_string(trim($_REQUEST['work_address']));
    $marry           = intval($_REQUEST['marry']);
    $recheck         = intval($_REQUEST['recheck']);
    $cycle_check     = intval($_REQUEST['cycle_check']);
    $work_type       = intval($_REQUEST['job_type']);
    $worktime        = intval($_REQUEST['worktime']);
    $travel          = mysql_real_escape_string($_REQUEST['travel']);
    $enviroment      = mysql_real_escape_string($_REQUEST['enviroment']);
    $healthy_element = intval($_REQUEST['healthy_element']);
    $blood_type      = intval($_REQUEST['blood_type']);

    $weight_condition                    = array();
    $weight_condition['height']          = intval($_REQUEST['height']);
    $weight_condition['weight']          = intval($_REQUEST['weight']);
    $weight_condition['BMI']             = mysql_real_escape_string($_REQUEST['BMI']);
    $weight_condition['waistline ']      = intval($_REQUEST['waistline']);
    $weight_condition['hipline']         = intval($_REQUEST['hipline']);
    $weight_condition['WHR']             = mysql_real_escape_string(trim($_REQUEST['WHR']));
    $weight_condition['blood_pressure '] = mysql_real_escape_string($_REQUEST['blood_pressure']); //血压
    $weight_condition['blood_fat']       = intval($_REQUEST['blood_fat']);
    $weight_condition['blood_sugar']     = intval($_REQUEST['blood_sugar']);
    $input_time                          = $_SERVER['REQUEST_TIME'];

    $res = array();

    //添加基本信息
    $sql_insert =  'INSERT INTO '.$GLOBALS['ecs']->table('user_archive').
        '(archive_id,user_id,user_name,born_address,work_address,is_marry,regular_check,cycle_check)'.
        "VALUES($user_id,$user_id,'$user_name','$born_address','$work_address',$marry,$recheck,$cycle_check)";

    $base = $GLOBALS['db']->query($sql_insert);

    //添加体重信息
    if($base)
    {
        $i = 0;
        foreach($weight_condition AS $key=>$val)
        {
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('other_examination_test').
                '(user_id,examination_name,examination_value,admin_id,input_time)VALUES('.
                "$user_id,'$key','$val',$admin_id,$input_time".')';
            if($GLOBALS['db']->query($sql_insert))
                $i++;
        }
        if($i >= count($weight_condition))
            $condition_status = true;
    }

    //添加工作信息
    if($condition_status)
    {
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('work_condition').
            '(user_id,user_name,work_type,work_time,travel_situation,enviroment,healthy_element,blood_type)'.
            "VALUES($user_id,'$user_name',$work_type,$worktime,'$travel','$enviroment',$healthy_element,$blood_type)";

        $work = $GLOBALS['db']->query($sql_insert);
    }

    die($work);
}

//验证客户是否拥有健康档案
elseif ($_REQUEST['act'] == 'isExistHFile')
{
    $user_id = intval($_REQUEST['user_id']); 
    $sql_select = 'SELECT COUNT(*) AS count,u.sex,a.address,u.user_name FROM '.$GLOBALS['ecs']->table('users').
        ' AS u LEFT JOIN '.$GLOBALS['ecs']->table(user_address).
        ' AS a ON u.user_id=a.user_id'.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('user_archive').
        " AS c ON c.user_id=u.user_id WHERE c.user_id=$user_id";
    $result = $GLOBALS['db']->getAll($sql_select);

    die($json->encode($result));
}

// 顾客生活习惯
elseif ($_REQUEST['act'] == 'upload_lifestyle')
{
    $user_id        = intval($_REQUEST['user_id']);
    $food_taste     = mysql_real_escape_string($_REQUEST['food_taste']);
    $fixed_dinner   = intval($_REQUEST['fixed_dinner']);
    $mealtime       = intval($_REQUEST['mealtime']);
    $sleep_habit    = intval($_REQUEST['sleep_habit']);
    $bedtime_start  = intval($_REQUEST['bedtime_start']);
    $sleep_quality  = intval($_REQUEST['sleep_quality']);
    $bedtime        = intval($_REQUEST['bedtime']);
    $sport_times    = intval($_REQUEST['sport_times']);
    $sport_time     = intval($_REQUEST['sport_time']);
    $sport_period   = intval($_REQUEST['sport_period']);
    $sport_type     = mysql_real_escape_string($_REQUEST['sport_type']);
    $smoke          = intval($_REQUEST['smoke']);
    $smoke_number   = intval($_REQUEST['smoke_number']);
    $smoke_age      = intval($_REQUEST['smoke_age']);
    $passive_smoke  = intval($_REQUEST['passive_smoke']);
    $drink          = intval($_REQUEST['drink']);
    $drink_type     = mysql_real_escape_string($_REQUEST['drink_type']);
    $drink_capacity = intval($_REQUEST['drink_capacity']);

    //  添加饮食与睡眠习惯
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table(lifestyle).
        '(lifestyle_id,user_id,food_taste,fixed_dinner,mealtime,sleep_habit,bedtime_start,sleep_quality,bedtime,sport_times,sport_time,sport_period,sport_type,smoke,smoke_number,smoke_age,passive_smoke,drink,drink_type,drink_capacity)'.
        "VALUES($user_id,$user_id,'$food_taste',$fixed_dinner,$mealtime,$sleep_habit,$bedtime_start,$sleep_quality,$bedtime,$sport_times,$sport_time,$sport_period,'$sport_type',$smoke,$smoke_number,$smoke_age,$passive_smoke,$drink,'$drink_type',$drink_capacity)";

    die($GLOBALS['db']->query($sql_insert));
}

// 上传过敏史
elseif ($_REQUEST['act'] == 'load_allergy')
{
    $user_id = $_REQUEST['user_id'];
    $allergy = $_REQUEST['allergy'];    
    $reason  = $_REQUEST['reason'];

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_archive').
        " SET allergy=$allergy,allergy_reason='$reason' WHERE user_id=$user_id";

    $result = $GLOBALS['db']->query($sql_update);

    die($result);
}

// 家族病例
elseif ($_REQUEST['act'] == 'family_disease')
{
    $user_id        = $_REQUEST['user_id']; 
    $family_disease = $_REQUEST['family_disease'];
    $tumour         = $_REQUEST['tumour'];

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_archive').
        " SET family_case='$family_disease',tumour='$tumour' WHERE user_id=$user_id";

    die($GLOBALS['db']->query($sql_update));

}

// 既往病史
elseif ($_REQUEST['act'] == 'before_case')
{
    $user_id     = $_REQUEST['user_id'];
    $before_case = $_REQUEST['before_disease'];

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_archive').
        " SET before_case='$before_case' WHERE user_id=$user_id";

    die($GLOBALS['db']->query($sql_update));
}

//心理信息
elseif ($_REQUEST['act'] == 'psychology')
{
    $user_id    = $_REQUEST['user_id'];
    $psychology = $_REQUEST['psychology'];

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_archive').
        " SET psychology='$psychology' WHERE user_id=$user_id";

    die($GLOBALS['db']->query($sql_update));
}

//健康档案补充信息
elseif ($_REQUEST['act'] == 'subother')
{
    $user_id = $_REQUEST['user_id'];
    $other   = $_REQUEST['other'];

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_archive').
        " SET other='$other' WHERE user_id=$user_id";

    die($GLOBALS['db']->query($sql_update));

}

//服务高级查询
elseif ($_REQUEST['act'] == 'service_fuse')
{
    $result = service_records();
    $smarty->assign('condition',$result['filter']['condition']);
    $smarty->assign('records',$result['records']);
    $smarty->assign('filter',$result['filter']);
    $smarty->assign('admin_list',get_admin_tmp_list());

    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('records_div.htm');

    die($json->encode($res));
}

//会员积分等级管理
elseif ($_REQUEST['act'] == 'user_rank')
{
    admin_priv('user_rank','',false);
    //销售平台
    $sql_select = 'SELECT * FROM '.$ecs->table('role').' WHERE role_id IN(1,2,6,7,10)';
    $platform = $db->getAll($sql_select);


    //积分等级信息
    $sql_select = 'SELECT ur.*,r.role_id,r.role_name FROM '.$ecs->table('user_rank').' AS ur LEFT JOIN '.
        $GLOBALS['ecs']->table('role').' AS r ON ur.role_id=r.role_id ORDER BY ur.modify_time DESC';
    $user_rank = $GLOBALS['db']->getAll($sql_select);

    foreach($user_rank as &$val)
    {
        $val['modify_time'] = date('y-m-d H:i',$val['modify_time']);
        if(isset($val['platform']) && $val['platform'] != 'all'){}
        else
        {
            $val['platform'] = '所有';
        }
    }

    //积分规则
    $sql_select = 'SELECT i.*,u.user_name,r.*,b.* FROM '.$GLOBALS['ecs']->table('integral').
        ' AS i LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' AS u ON i.admin_id=u.user_id LEFT JOIN'.
        $GLOBALS['ecs']->table('role').' AS r ON i.platform=r.role_id LEFT JOIN '.$GLOBALS['ecs']->table('brand').
        ' AS b ON i.suit_brand=b.brand_id'.
        ' ORDER BY i.add_time DESC';
    $integral = $GLOBALS['db']->getAll($sql_select);

    //重装数组（分启用，未启用）
    $en_integral       = array();
    $dis_integral      = array();
    $past_due_integral = array();

    $j = $i = $k = 0;
    foreach($integral as &$val)
    {
        $val['add_time'] = date('Y-m-d',$val['add_time']);        
        $val['present_start'] = date('Y-m-d',$val['present_start']);        
        $val['present_end'] = date('Y-m-d',$val['present_end']);
        switch($val['integral_way']) {
        case 1: $val['integral_way'] = '消费'; break;
        case 2: $val['integral_way'] = '推荐'; break;
        case 3: $val['integral_way'] = '充值'; break;
        case 4: $val['integral_way'] = '生日送积分'; break;
        case 5: $val['integral_way'] = '其它'; break;
        }

        if($val['role_id'] == 0) {
            $val['role_name'] = '全部平台';
        }

        if($val['suit_brand'] == 0) {
            $val['brand_name'] = "全部品牌";
        }

        if($val['available'] == 1) {
            $en_integral[$i] = $val;
            $i++;
        } elseif($val['available'] == 0) {
            $dis_integral[$j] = $val;
            $j++;
        } elseif($val['available'] == 2) {
            $past_due_integral[$k] = $val;
            $k++;
        }
    }

    $smarty->assign('en_integral',$en_integral);
    $smarty->assign('dis_integral',$dis_integral);
    $smarty->assign('past_due_integral',$past_due_integral);
    $smarty->assign('user_rank',$user_rank);
    $smarty->assign('platform',$platform);
    $smarty->assign('brand',get_brand());
    $res['main'] = $smarty->fetch('user_rank.htm');

    die($json->encode($res));
}


//修改、添加等级
elseif ($_REQUEST['act'] == 'add_mod_rank')
{
    if(admin_priv('add_rank','',false)) {
        $id                = intval($_REQUEST['id']);
        $row_id            = intval($_REQUEST['row_id']);
        $admin_name        = $_SESSION['admin_name'];
        $res               = array();
        $rank_name         = mysql_real_escape_string($_REQUEST['rank_name']); //等级名称
        $min_points        = intval($_REQUEST['min_point']); //积分下限
        $max_points        = intval($_REQUEST['max_point']); //积分上限
        $convert_scale     = intval($_REQUEST['convert_scale']);             //兑换比例
        $discount          = intval($_REQUEST['discount']); //折扣比例
        $integral_discount = intval($_REQUEST['integral_discount']); //额外比例
        $platform          = intval($_REQUEST['platform']); //适用平台列表
        $validity          = intval($_REQUEST['validity']); //有效期
        $unit_data         = intval($_REQUEST['unit_data']); //日期单位

        $res = array(
            'req_msg' => true,
            'timeout' => 2000,
            'code'    => false
        );

        if($unit_data == 1) {
            $validity =  $validity * 365; 
        } elseif($unit_date == 2) {
            $validity = $validity * 30;
        }

        //添加等级
        if(!$id) {
            //检查是否有相同的等级
            $sql_count = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_rank').
                " WHERE rank_name='$rank_name' AND role_id=$platform AND (($min_points BETWEEN min_points AND max_points) OR ($max_points BETWEEN min_points AND max_points))";

            $result = $GLOBALS['db']->getOne($sql_count);

            if($result) {
                $res['message'] = "已经存在相同等级";
                die($json->encode($res));
            }

            $current_time = $_SERVER['REQUEST_TIME'];
            $sql_select   = 'SELECT MAX(level)+1 FROM '.$GLOBALS['ecs']->table('user_rank');
            $level        = $GLOBALS['db']->getOne($sql_select);

            $sql_insert   = 'INSERT INTO '.$GLOBALS['ecs']->table('user_rank').
                '(rank_name,min_points,max_points,discount,convert_scale,integral_discount,role_id,validity,modify_time,modify_admin,level)'.
                " VALUES('$rank_name',$min_points,$max_points,$discount,$convert_scale,$integral_discount,$platform,$validity,$current_time,'$admin_name',$level)";

            $result = $GLOBALS['db']->query($sql_insert);    
        } else {
            //  修改等级
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_rank').
                " SET rank_name='$rank_name',min_points=$min_points,max_points=$max_points,discount=$discount,convert_scale=$convert_scale,integral_discount=$integral_discount,role_id=$platform,validity=$validity,modify_time=UNIX_TIMESTAMP(NOW()),modify_admin='$admin_name' WHERE rank_id=$id";

            $result = $GLOBALS['db']->query($sql_update);
        }

        if($result) {
            $res['code']    = true;
            $res['message'] = '添加/修改成功';   
        } else {
            $res['message'] = '添加/修改失败';   
        }

        die($json->encode($res));
    }
}

//删除等级操作
elseif ($_REQUEST['act'] == 'del_ranks')
{
    $rank_id       = intval($_REQUEST['rank_id']);
    $res['row_id'] = intval($_REQUEST['row_id']);

    $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('user_rank')." WHERE rank_id=$rank_id";
    $result = $GLOBALS['db']->query($sql_del);

    $res['req_msg'] = true;
    $res['timeout'] = 2000;
    if($result) {
        $res['message']  = '删除成功';
        $res['code'] = 1;
    } else {
        $res['message'] = '删除失败';
        $res['code'] = 0;
    }

    die($json->encode($res));
}

//修改等级/积分规则设置模板
elseif ($_REQUEST['act'] == 'mod_rank_inte')
{
    if(admin_priv('mod_rank_inte','',false)) {
        $plan          = intval($_REQUEST['plan']);
        $res['row_id'] = intval($_REQUEST['row_id']);
        $id            = intval($_REQUEST['id']);    // insert new row's index

        if($plan == 1) {
            $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('user_rank')." WHERE rank_id=$id";
            $rank = $GLOBALS['db']->getRow($sql_select);
            $rank['modify_time'] = date('Y-m-d',$rank['modify_time']);

            $smarty->assign('rank',$rank);
        } else {
            $sql_select = 'SELECT i.*,u.user_name,r.role_name,r.role_id FROM '.
                $GLOBALS['ecs']->table('integral').
                ' AS i LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
                ' AS u ON i.admin_id=u.user_id'.
                ' LEFT JOIN '.$GLOBALS['ecs']->table('role').
                ' AS r ON i.platform=r.role_id '.
                " WHERE i.integral_id=$id ".
                ' ORDER BY i.add_time DESC';

            $integral                  = $GLOBALS['db']->getRow($sql_select);
            $integral['other_brand']   = explode(',',$integral['other_brand']);
            $integral['present_start'] = date('Y-m-d H:i',$integral['present_start']);
            $integral['present_end']   = date('Y-m-d H:i',$integral['present_end']);

            $smarty->assign('integral',$integral);
            $smarty->assign('brand',get_brand());
        }
    }

    $smarty->assign('platform', get_platform_list());    
    $smarty->assign('plan',$plan);
    $smarty->assign('row_id',$res['row_id']);

    $res['plan'] = $plan;
    $res['main'] = $smarty->fetch('mod.htm');

    die($json->encode($res));
}

//添加/修改积分等级规则操作
elseif ($_REQUEST['act'] == 'add_mod_inte')
{
    if(admin_priv('add_mod_inte','',false)) {
        $admin_id       = $_SESSION['admin_id'];  //添加人
        $integral_title = trim(mysql_real_escape_string($_REQUEST['integral_title']));//规则名称
        $platform       = intval($_REQUEST['platform']); //适用平台
        $integral_way   = intval($_REQUEST['integral_way']); //获取积分的途径
        $available      = intval($_REQUEST['available']); //是否可用
        $id             = intval($_REQUEST['id']); //修改的规则id
        $che_mod        = intval($_REQUEST['che_mod']); //判断是否为修改
        $suit_brand     = intval($_REQUEST['suit_brand']);

        //检查是不是存在相同的规则
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('integral').
            " WHERE platform=$platform AND integral_way=$integral_way AND suit_brand=$suit_brand AND available=1";

        $is_exist = $GLOBALS['db']->getOne($sql_select);

        if($is_exist > 0 && $che_mod == 0) {
            $res['code'] = 2;
        } else {
            $add_time      = strtotime(date('Y-m-d H:i')); //添加时间
            $scale         = floatval($_REQUEST['scale']); //获取比例
            $present_start = strtotime($_REQUEST['present_start']); //启动时间
            $present_end   = strtotime($_REQUEST['present_end']); //结束时间
            $suit_brand    = intval($_REQUEST['suit_brand']);  //例外品牌
            $min_consume   = intval($_REQUEST['min_consume']);  //消费下限
            $max_consume   = intval($_REQUEST['max_consume']);  //消费上限

            //添加积分规则
            if($id == 0) {
                $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('integral').
                    '(integral_title,scale,platform,integral_way,add_time,admin_id,available,present_start,present_end,suit_brand,min_consume,max_consume)'.
                    "VALUES('$integral_title',$scale,$platform,$integral_way,$add_time,$admin_id,$available,$present_start,$present_end,$suit_brand,$min_consume,$max_consume)";
            } else {
                //修改积分规则
                $sql = 'UPDATE '.$GLOBALS['ecs']->table('integral').
                    " SET integral_title='$integral_title',scale=$scale,platform=$platform,integral_way='$integral_way',add_time=$add_time,admin_id=$admin_id,available=$available,present_start=$present_start,present_end=$present_end,suit_brand='$suit_brand',min_consume=$min_consume,max_consume=$max_consume WHERE integral_id=$id";
            }

            $result = $GLOBALS['db']->query($sql);    

            if($result) {
                $res['code'] = 1;
            } else {
                $res['code'] = 0;
            }
        }

        die($json->encode($res));
    }
} 

//启用积分规则
elseif ($_REQUEST['act'] == 'enable_inte')
{
    if(admin_priv('all','',false)) {
        $res['row_id'] = intval($_REQUEST['row_id']);
        $integral_id   = intval($_REQUEST['integral_id']);

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('integral')." SET available=1 WHERE integral_id=$integral_id";
        $result = $GLOBALS['db']->query($sql_update);

        $res['req_msg'] = true;
        $res['timeout'] = 2000;
        if($result) {
            $res['message'] = '启用成功';
            $res['code'] = 1; 
        } else {
            $res['message'] = '启用失败';
            $res['code'] = 2;
        }
        die($json->encode($res));
    }
}

//删除积分规则
elseif ($_REQUEST['act'] == 'del_integrals')
{
    $integral_id = intval($_REQUEST['integral_id']);

    $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('integral').
        " WHERE integral_id=$integral_id";

    $res['req_msg'] = true;
    $res['timeout'] = 2000;

    if($GLOBALS['db']->query($sql_del)) {
        $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('integral_plat_brand').
            " WHERE integral_id=$integral_id";
    }

    if($GLOBALS['db']->query($sql_del)) {
        $res['code']    = true;
        $res['message'] = '删除成功';
        $res['row_id']  = intval($_REQUEST['row_id']);
    } else {
        $res['message'] ='删除失败';
        $res['code'] = 0;
    }

    die($json->encode($res));
}

//确认会员积分模板
elseif ($_REQUEST['act'] == 'confirm_inte')
{
    if(admin_priv('confirm_inte','',false) || admin_priv('all','',false)) {
        $sort      = empty($_REQUEST['sort']) ? 'receive_time' : mysql_real_escape_string($_REQUEST['sort']);
        $sort_type = empty($_REQUEST['sort_type']) ? ' ASC' : ' '.mysql_real_escape_string($_REQUEST['sort_type']);
        $sort      = $sort.$sort_type;

        $sort_type = $sort_type == ' ASC' ? 'DESC' : 'ASC';

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } else {
            $filter['page_size'] = 20; 
        }

        //视图
        $sql_select = 'SELECT COUNT(*) AS count FROM '.$GLOBALS['ecs']->table('user_integral_view').
            ' AS i LEFT JOIN '.$GLOBALS['ecs']->table('users').
            ' AS u ON i.user_id=u.user_id LEFT JOIN '.
            $GLOBALS['ecs']->table('role').' AS r ON u.role_id=r.role_id '.
            ' WHERE u.user_name IS NOT NULL AND u.role_id IS NOT NULL';

        $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
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
            'condition'     => empty($condition) ? '': $condition,
            'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
            'end'           => $filter['page']*$filter['page_size'],
            'act'           => 'confirm_inte',
        );

        //查询未确认积分
        $sql_select = 'SELECT ui.*, u.user_name,u.role_id, r.rank_name, r.rank_id, r.role_id AS platform, a.user_name AS admin_name, i.integral_title, (ui.pre_points + ui.exchange_points) AS cur_integral, o.goods_amount FROM '. 
            $GLOBALS['ecs']->table('user_integral').' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('users').
            ' AS u ON ui.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
            ' AS a ON u.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('integral').
            ' AS i ON ui.integral_id=i.integral_id LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
            ' AS o ON ui.source_id=o.order_id LEFT JOIN '.$GLOBALS['ecs']->table('user_rank').
            ' AS r ON u.user_rank=r.rank_id WHERE ui.confirm=0 AND u.user_name IS NOT NULL AND r.role_id IS NOT NULL ORDER BY '.$sort.
            ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

        $integral = $GLOBALS['db']->getAll($sql_select);
        foreach($integral as &$val) {
            $val['receive_time'] = date('Y-m-d H:i',$val['receive_time']);
            $val['validity']     = date('Y-m-d H:i',$val['validity']);

            if($val['rank_id'] == '' && $val['rank_name'] == '' && $val['platform'] == '' && $val['user_name'] == '' && $val['role_id'] == '') {
                $val['rank_id']   = 0;
                $val['rank_name'] = '未分配';
                $val['platform']  = '0';
                $val['user_name'] = '未知顾客';
                $val['role_id']   = 0;
                continue;
            }

            //是否升级
            $sql_select = 'SELECT rank_id,rank_name,min_points,max_points,role_id FROM '
                .$GLOBALS['ecs']->table('user_rank').
                " WHERE {$val['cur_integral']} BETWEEN min_points AND max_points AND role_id={$val['role_id']}";

            $inte = $GLOBALS['db']->getRow($sql_select);

            if($inte['rank_id'] != $val['rank_id']) {
                $val['rankup_name'] = $inte['rank_name'];
                $val['rankup_id']   = $inte['rank_up_id'];
            }
        }

        //积分规则
        $sql_select = 'SELECT integral_id,integral_title FROM '
            .$GLOBALS['ecs']->table('integral').' WHERE available = 1';

        $inte_rule  = $GLOBALS['db']->getAll($sql_select);

        $smarty->assign('integral',$integral);
        $smarty->assign('inte_rule',$inte_rule);
        $smarty->assign('role',get_role());
        $smarty->assign('admin',get_admin_tmp_list());
        $smarty->assign('filter',$filter);
        $smarty->assign('empty',$filter['record_count']);
        $smarty->assign('sort_type',$sort_type);

        $res['main'] = $smarty->fetch('confirm_inte.htm');
    }

    die($json->encode($res));
}

//确认会员积分操作
elseif ($_REQUEST['act'] == 'confirm_integral')
{
    if(admin_priv('confirm_integral','',false) || admin_priv('all','',false)) {
        $confirm_type = intval($_REQUEST['type']);
        $res = array(
            'req_msg'  => true,
            'time_out' => 2000,
            'code'     => 0,
            'message'  => ''
        );

        if(isset($_REQUEST['inte_id']))
        {  
            //确认当前页
            if($confirm_type == 1) {
                $inte_id = mysql_real_escape_string($_REQUEST['inte_id']);    
            }
            //确认当天
            elseif($confirm_type == 2) {
                $sql_select = 'SELECT user_integral_id FROM '.$GLOBALS['ecs']->table('user_integral')
                    ." WHERE TO_DAYS(NOW())=TO_DAYS(FROM_UNIXTIME(receive_time))";

                $inte_id = $GLOBALS['db']->getCol($sql_select);
                if($inte_id){
                    $inte_id = implode(',',$inte_id);
                } else {
                    $res['messsage'] = '没有当天可确认的积分';
                    die($json->encode($res));
                }
            }
            elseif($confirm_type == 3) //确认全部
            {
                $sql_select = 'SELECT user_integral_id FROM '.$GLOBALS['ecs']->table('user_integral').' WHERE confirm=0';

                $inte_id = $GLOBALS['db']->getCol($sql_select);
                $inte_id = implode(',',$inte_id);
            }

            //确认积分
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_integral').
                " SET confirm=1,admin_id={$_SESSION['admin_id']},confirm_time=UNIX_TIMESTAMP(NOW()) WHERE user_integral_id IN(".$inte_id.') AND confirm=0';

            $result_first = $GLOBALS['db']->query($sql_update);

            //修改会员的积分
            if($result_first){
                $sql_select = 'SELECT user_id,SUM(points) AS points FROM '.
                    $GLOBALS['ecs']->table('user_integral').
                    " WHERE user_integral_id IN($inte_id) GROUP BY user_id";
                $points_list = $GLOBALS['db']->getAll($sql_select);

                foreach($points_list as $val){
                    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').
                        " SET rank_points=rank_points+{$val['points']} WHERE user_id={$val['user_id']}";

                    $check = $GLOBALS['db']->query($sql_update);
                }
                $result_second = $check;
            }

            //是否升级
            if($result_second) {
                //确认的积分记录
                $sql_select = 'SELECT ui.user_id, i.platform FROM '
                    .$GLOBALS['ecs']->table('user_integral')
                    .' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('integral')
                    .' AS i ON ui.integral_id=i.integral_id '
                    ." WHERE ui.user_integral_id IN($inte_id) GROUP BY ui.user_id";

                $user_inte = $GLOBALS['db']->getAll($sql_select);

                $user_id_list = "";
                foreach($user_inte as &$val) {
                    $user_id_list .= $val['user_id'].',';
                }

                $user_id_list = explode(',',$user_id_list);
                array_pop($user_id_list);
                $user_id_list = implode(',',$user_id_list);

                //会员的等级信息
                $sql_select = 'SELECT user_id,user_rank,rank_points,role_id FROM '
                    .$GLOBALS['ecs']->table('users').
                    " WHERE user_id IN($user_id_list)";
                $user_info = $GLOBALS['db']->getAll($sql_select);

                //会员等级列
                $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('user_rank');
                $user_rank  = $GLOBALS['db']->getAll($sql_select);

                $sql     = 'UPDATE '.$GLOBALS['ecs']->table('users').' SET ';
                $str_sql = "";

                //遍历并判断升级
                $total = 0;
                foreach($user_info as &$user_val) {
                    foreach($user_rank as &$rank_val) {
                        if( $rank_val['min_points'] < $user_val['rank_points'] && $user_val['rank_points'] <= $rank_val['max_points']) {
                            if($rank_val['role_id'] == $user_val['role_id']) {
                                $str_sql .= $sql." user_rank={$rank_val['rank_id']} WHERE user_id={$user_val['user_id']};"; 
                            } elseif($rank_val['role_id'] == 0) {
                                $str_sql .= $sql." user_rank={$rank_val['rank_id']} WHERE user_id={$user_val['user_id']};"; 
                            }
                        }
                    }
                }

                if($str_sql != '') {
                    $arr_sql = explode(';',$str_sql);
                    array_pop($arr_sql);
                    $total = count($arr_sql);
                } else {
                    $result_third = $total = 0;
                    $no_level_up = true;
                }

                $count = 0;
                if($total > 0) {
                    foreach($arr_sql as $val) {
                        if($GLOBALS['db']->query($val)) {
                            $count++;
                        } else {
                            continue;
                        }
                    }
                }
            }

            if($count > 0 || $no_level_up) {
                $res['code'] = 1;
                if($result_third == $total)
                    $res['message'] = '确认成功';
                else
                    $res['message'] = '未全都确认';
            } else {
                $res['message'] = '确认失败';
            }

            die($json->encode($res));
        }

        $user_integral_id = intval($_REQUEST['user_integral_id']);
        $rank_points      = intval($_REQUEST['points']);
        $user_id          = intval($_REQUEST['user_id']);
        $res['row_id']    = intval($_REQUEST['row_id']);

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_integral').
            " SET confirm=1,admin_id={$_SESSION['admin_id']},confirm_time=UNIX_TIMESTAMP(NOW()) WHERE user_integral_id=$user_integral_id";

        $result = $GLOBALS['db']->query($sql_update);
        if($result) {
            //是否有本平台的等级增长【未完成】
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').
                " SET rank_points=$rank_points,user_rank=(SELECT rank_id FROM ".
                $GLOBALS['ecs']->table('user_rank').
                " WHERE min_points<=$rank_points AND $rank_points < max_points) WHERE user_id=$user_id";

            $result = $GLOBALS['db']->query($sql_update);
            if($result) {
                //判断自动升级
                if($result) {
                    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').' SET user_rank=(SELECT rank_id FROM '
                        .$GLOBALS['ecs']->table('user_rank')
                        ." WHERE $rank_points BETWEEN min_points AND max_points ) WHERE user_id=$user_id";
                    if($GLOBALS['db']->query($sql_update))
                    {
                        $res['code'] = 1;
                        $res['message'] = '确认成功';
                    }
                    else
                    {
                        $res['code'] = 0;
                        $res['message'] = '确认失败'; 
                    }    
                }
            }
        }
        die($json->encode($res));
    }
}

//查看平台已有积分规则
elseif ($_REQUEST['act'] == 'sch_integral')
{
    $role_id = intval($_REQUEST['role_id']);

    $sql = 'SELECT i.integral_id,i.integral_title,i.available,i.scale,i.present_start,i.present_end,i.integral_way,i.validity,i.add_time,i.min_consume,i.max_consume,b.brand_name,u.user_name,iw.way_name AS integral_way';
    if($role_id){
        $sql_select = $sql.',r.role_name FROM '.
            $GLOBALS['ecs']->table('integral').
            ' i,'.$GLOBALS['ecs']->table('role').' r, '.
            $GLOBALS['ecs']->table('brand').' b, '.
            $GLOBALS['ecs']->table('admin_user').' u, '.
            $GLOBALS['ecs']->table('integral_way').' iw '.
            " WHERE i.platform=$role_id AND b.brand_id=i.suit_brand AND r.role_id=i.platform AND u.user_id=i.admin_id AND i.integral_way=iw.way_id ORDER BY i.add_time DESC";    
    } else {
        $sql_select = $sql.' FROM '.$GLOBALS['ecs']->table('integral').
            ' i LEFT JOIN '.$GLOBALS['ecs']->table('role').
            ' r ON i.platform=r.role_id LEFT JOIN '.
            $GLOBALS['ecs']->table('brand').
            ' b ON b.brand_id=i.suit_brand '.
            ' LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
            ' u ON i.admin_id=u.user_id LEFT JOIN '.
            $GLOBALS['ecs']->table('integral_way').' iw'.
            ' ON i.integral_way=iw.way_id ORDER BY i.add_time DESC';    
    }

    $result = $GLOBALS['db']->getAll($sql_select);

    //启用,未启用,过期
    $en_able  = $dis_able = $past_due = array();
    foreach($result as &$val) {
        $val['add_time']      = date('Y-m-d',$val['add_time']);
        $val['present_start'] = date('Y-m-d',$val['present_start']);
        $val['present_end']   = date('Y-m-d',$val['present_end']);

        $val['platform']   = $val['platform']   == 0 ? '全平台' : $val['platform'];
        $val['suit_brand'] = $val['suit_brand'] == 0 ? '全品牌' : $val['suit_brand'];

        if($val['available'] == 1) {
            $en_able[] = $val;
        } elseif($val['available'] == 0) {
            $dis_able[] = $val;
        }elseif($val['available'] == 3) {
            $past_due[] = $val;
        }
    }

    $smarty->assign('en_able',$en_able);
    $smarty->assign('dis_able',$dis_able);
    $smarty->assign('plan',2);
    $res['main'] = $smarty->fetch('get_rank_part.htm');

    die($json->encode($res));
}

//查看积分日志模板
elseif ($_REQUEST['act'] == 'ch_points_history')
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


    if(admin_priv('all','',false))
    {
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_integral').
            ' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('users').
            ' AS u ON ui.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
            ' AS a ON u.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('integral').
            ' AS i ON ui.integral_id=i.integral_id LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
            ' AS o ON ui.source_id=o.order_id LEFT JOIN '.$GLOBALS['ecs']->table('user_rank').
            ' AS r ON u.user_rank=r.rank_id WHERE ui.confirm=1 AND u.user_name IS NOT NULL AND u.role_id IS NOT NULL ORDER BY ui.confirm_time DESC,ui.receive_time DESC';

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
            'condition'     => empty($condition) ? '' : $condition,
            'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
            'end'           => $filter['page']*$filter['page_size'],
            'act'           => 'ch_points_history',
        );

        $role = get_role();
        $sql_select = 'SELECT ui.*,u.user_name,r.rank_name,a.user_name as admin_name,i.integral_title,(ui.pre_points+ui.exchange_points) as cur_integral,o.goods_amount FROM '.$GLOBALS['ecs']->table('user_integral').
            ' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('users').
            ' AS u ON ui.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
            ' AS a ON u.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('integral').
            ' AS i ON ui.integral_id=i.integral_id LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
            ' AS o ON ui.source_id=o.order_id LEFT JOIN '.$GLOBALS['ecs']->table('user_rank').
            ' AS r ON u.user_rank=r.rank_id WHERE ui.confirm=1 AND u.user_name IS NOT NULL AND u.role_id IS NOT NULL ORDER BY ui.confirm_time  DESC,ui.receive_time DESC'.
            ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";
    }

    elseif(admin_priv('ch_points_history','',false))
    {
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('role').' WHERE role='.$_SESSION['role_id'];
        $role = $GLOBALS['db']->getAll($sql_select);
    }

    $result = $GLOBALS['db']->getAll($sql_select);
    foreach($result as &$val)
    {
        $val['receive_time'] = date('m月d H:i',$val['receive_time']);
        $val['confirm_time'] = date('m月d H:i',$val['confirm_time']);
        $val['validity'] = date('Y-m-d H:i',$val['validity']);
    }

    //默认列出本部门的会员积分变化记录
    $smarty->assign('integral',$result);
    $smarty->assign('brand',get_brand());
    $smarty->assign('role',$role);
    $smarty->assign('filter',$filter);
    $res['main'] = $smarty->fetch('integral_log.htm');

    die($json->encode($res));
}

//会员列表
elseif ($_REQUEST['act'] == 'show_vips')
{
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0){
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }else{
        $filter['page_size'] = 20; 
    }

    $rank_id = intval($_REQUEST['rank_id']);    //等级id
    $res['row_id'] = intval($_REQUEST['row_id'] + 1);
    $by_upgrade = mysql_real_escape_string($_REQUEST['by_upgrade']);
    $sort = empty($_REQUEST['sort']) ? 'rank_points' : $_REQUEST['sort'];  //排序
    $sort_type = empty($_REQUEST['sort_type']) ? 'DESC ': $_REQUEST['sort_type'];

    $condition = "&rank_id=$rank_id&sort=$sort&sort_type=$sort_type";
    $sort = ' ORDER BY '.$sort.' '.$sort_type;

    //会员列表：
    $sql_select = 'SELECT count(*) FROM '.$GLOBALS['ecs']->table('users')." WHERE user_rank=$rank_id AND customer_type IN('2, 3, 4, 5, 11')";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);

    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    //  paging config
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
        'act'           => 'show_vips',
    );

    $limit = ' LIMIT '.($filter['page']-1)*$filter['page_size'].','.$filter['page_size'];

    $sql_select = 'SELECT user_id as vip_id,user_name,rank_points,user_rank FROM ' .$GLOBALS['ecs']->table('users')." WHERE user_rank=$rank_id AND customer_type IN('2, 3, 4, 5, 11') ".$sort.$limit;
    $user_list = $GLOBALS['db']->getAll($sql_select);
    foreach($user_list as $val){
        $user_id_list[] = $val['vip_id'];
    }
    $user_id_list = implode("','",$user_id_list);

    $sql_select = 'SELECT user_id,max(add_time) AS recently_pur,min(add_time) AS earliest_pur,COUNT(*) AS total,sum(final_amount) AS final_amount FROM '.
        $GLOBALS['ecs']->table('order_info').
        " WHERE user_id IN('$user_id_list')".
        ' GROUP BY user_id';
    $order_list = $GLOBALS['db']->getAll($sql_select); 

    $user_total  = count($user_list);
    $order_total = count($order_list);

    for($i = 0; $i < $user_total; $i++){
        for($j = 0; $j < $order_total; $j++){
            if($user_list[$i]['vip_id'] == $order_list[$j]['user_id']){
                $user_list[$i] = array_merge($user_list[$i],$order_list[$j]);
            }
        }
    }
    $vip_list = $user_list;

    include(dirname(__FILE__).'/includes/lib_goods.php');
    $user_rank = get_user_rank_list();

    foreach($vip_list as &$val)
    {
        $val['earliest_pur'] = !$val['earliest_pur'] ? '-' : date('Y-m-d',$val['earliest_pur']);
        $val['recently_pur'] = !$val['recently_pur'] ? '-' :date('Y-m-d',$val['recently_put']);
        foreach($user_rank as $rank){
            if($val['user_rank'] + 1 == $rank['rank_id'])
            {
                $val['upgrade_gap'] = $rank['min_points'] - $val['rank_points'];
                $val['up_rank'] = $rank['rank_name'];
                $val['up_rank_id'] = $rank['rank_id'];
                break;
            }
        }
    }

    //是否按距上一等级积分值排序
    if(!empty($by_upgrade) && $by_upgrade == 'by_upgrade')
    {
        $sort_type == 'ASC' ? SORT_ASC : SORT_DESC;
        $vip_list = array_sort($vip_list,'upgrade_gap',$sort_type);
    }

    if(count($vip_list))        //判断是不是有记录 
    {
        $smarty->assign('has_records',1); 
    } else {
        $smarty->assign('has_records',0); 
    }

    $sort_type = $sort_type == 'DESC' ? 'ASC' : 'DESC';

    $smarty->assign('sort_type',$sort_type);
    $smarty->assign('rank_id',$rank_id);
    $smarty->assign('vip_list',$vip_list);
    $smarty->assign('filter',$filter);
    $smarty->assign('condition',$condition);
    $smarty->assign('section','by_rank');
    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('vip_part.htm');

    die($json->encode($res));
}

// 快捷营销模板
elseif ($_REQUEST['act'] == 'fast_sale')
{
    $rank_id = intval($_REQUEST['rank_id']);
    $res['row_id'] = intval($_REQUEST['row_id'] + 1);
    $sql_select = 'SELECT ur.*,r.* FROM '.$GLOBALS['ecs']->table('user_rank').' ur, '.$GLOBALS['ecs']->table('role')." r WHERE ur.rank_id=$rank_id AND ur.role_id=r.role_id";
    $rank = $GLOBALS['db']->getRow($sql_select);

    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('integral').' WHERE platform=(SELECT role_id FROM '.$GLOBALS['ecs']->table('user_rank')." WHERE rank_id=$rank_id)";
    $integral = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('integral',$integral);
    $smarty->assign('rank',$rank);
    $res['main'] = $smarty->fetch('fast_sale.htm');

    die($json->encode($res));
}

//按平台检查部门规则
elseif ($_REQUEST['act'] == 'get_rank_part')
{
    $role_id = intval($_REQUEST['role_id']);

    //积分等级信息
    if($role_id == 0) {
        $sql_select = "SELECT rank_id,rank_name,min_points,max_points,discount,modify_time,modify_admin,'全平台' AS role_name,integral_discount,convert_scale,validity FROM ".$GLOBALS['ecs']->table('user_rank').' WHERE role_id=0 ORDER BY modify_time DESC';
    } elseif($role_id == -1) {
        $sql_select = 'SELECT ur.*,r.role_id,r.role_name FROM '.$GLOBALS['ecs']->table('user_rank').' AS ur LEFT JOIN '.
            $GLOBALS['ecs']->table('role').' AS r ON ur.role_id=r.role_id'.
            ' ORDER BY ur.modify_time DESC';
    } else {
        $sql_select = 'SELECT ur.*,r.role_id,r.role_name FROM '.$GLOBALS['ecs']->table('user_rank').' AS ur LEFT JOIN '.
            $GLOBALS['ecs']->table('role').' AS r ON ur.role_id=r.role_id '.
            " WHERE ur.role_id=$role_id".
            ' ORDER BY ur.modify_time DESC';
    }

    $user_rank = $GLOBALS['db']->getAll($sql_select);

    foreach($user_rank as &$val) {
        $val['modify_time'] = date('y-m-d H:i',$val['modify_time']);
        if($val['platform'] != 'all'){}
        else {
            $val['platform'] = '所有';
        }
    }

    $smarty->assign('user_rank',$user_rank);
    $smarty->assign('plan',1);
    $res['main'] = $smarty->fetch('get_rank_part.htm');

    die($json->encode($res));
}

/*查看积分模板*/
elseif ($_REQUEST['act'] == 'view_inte')
{
    $integral_id = intval($_REQUEST['integral_id']);

    $res['row_id'] = intval($_REQUEST['row_id']);
    $sql_select = 'SELECT i.*,u.user_name,r.*,b.* FROM '.$GLOBALS['ecs']->table('integral').
        ' AS i LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' AS u ON i.admin_id=u.user_id LEFT JOIN'.
        $GLOBALS['ecs']->table('role').' AS r ON i.platform=r.role_id LEFT JOIN '.$GLOBALS['ecs']->table('brand').
        ' AS b ON i.suit_brand=b.brand_id'.
        " WHERE i.integral_id=$integral_id";

    $integral = $GLOBALS['db']->getRow($sql_select);

    $integral['add_time'] = date('Y-m-d',$integral['add_time']);        
    $integral['present_start'] = date('Y-m-d',$integral['present_start']);        
    $integral['present_end'] = date('Y-m-d',$integral['present_end']);
    switch($integral['integral_way'])
    {
    case 1: $integral['integral_way'] = '消费'; break;
    case 2: $integral['integral_way'] = '推荐'; break;
    case 3: $integral['integral_way'] = '充值'; break;
    case 4: $integral['integral_way'] = '生日送积分'; break;
    case 5: $integral['integral_way'] = '其它'; break;
    }
    if($integral['role_id'] == 0)
    {
        $integral['role_name'] = '全部平台';
    }
    if($integral['suit_brand'] == 0)
    {
        $integral['brand_name'] = "全部品牌";
    }
    $smarty->assign('inteview',$integral);
    $smarty->assign('plan',3);

    $res['main'] = $smarty->fetch('mod.htm');

    die($json->encode($res));
}

//搜索未确认积分
elseif ($_REQUEST['act'] == 'sch_con_inte')
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

    $where = $condition = '';
    if(admin_priv('all','',false))
    {
        $role_id = 0; 
    }
    elseif(admin_priv('sch_con_inte'))
    {
        $where = 'AND u.role_id='.$_SESSION['role_id'];
    }

    $integral_way = intval($_REQUEST['integral_way']);
    $integral_id = intval($_REQUEST['integral_id']);
    $platform = intval($_REQUEST['platform']);
    $user_name = mysql_real_escape_string($_REQUEST['user_name']);
    $admin = intval($_REQUEST['admin']);
    $sort = mysql_real_escape_string($_REQUEST['sort']);
    $sort_type = mysql_real_escape_string($_REQUEST['sort_type']);

    $sort = empty($sort) ? 'receive_time' : $sort;
    $sort_type = empty($sort_type) ? 'ASC' : $sort_type;
    $append_condition = "&sort=$sort&sort_type=$sort_type";
    $sort = $sort.' '.$sort_type;
    $sort_type = $sort_type == 'ASC' ? 'DESC' : 'ASC';

    if($integral_way)
    {
        $where .= " AND i.integral_way=$integral_way";
        $condition .= "&integral_way=$integral_way";
    }
    if($integral_id)
    {
        $where .= " AND ui.integral_id=$integral_id";
        $condition .= "&integral_id=$integral_id";
    }
    if($platform && !$role_id)
    {
        $where .= " AND u.role_id=$platform";
        $condition .= "&platform=$platform";
    }
    if(!empty($user_name))
    {
        $where .= " AND u.user_name LIKE '%$user_name%'";
    }
    if($admin)
    {
        $where .= " AND a.user_id=$admin";
        $condition .= "&admin_id=$admin";
    }

    $sql_select = 'SELECT COUNT(*) AS count FROM '.$GLOBALS['ecs']->table('user_integral').
        ' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('users').
        ' AS u ON ui.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS a ON u.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('integral').
        ' AS i ON ui.integral_id=i.integral_id LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
        ' AS o ON ui.source_id=o.order_id LEFT JOIN '.$GLOBALS['ecs']->table('user_rank').
        ' AS r ON u.user_rank=r.rank_id WHERE ui.confirm=0 '.$where.' AND u.user_name IS NOT NULL AND u.role_id IS NOT NULL';

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
        'act'           => 'sch_con_inte',
    );

    $sql_select = 'SELECT ui.*,u.user_name,r.rank_name,r.rank_id,r.role_id,a.user_name as admin_name,i.integral_title,(ui.pre_points+ui.exchange_points) as cur_integral,o.goods_amount FROM '.$GLOBALS['ecs']->table('user_integral').
        ' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('users').
        ' AS u ON ui.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS a ON u.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('integral').
        ' AS i ON ui.integral_id=i.integral_id LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
        ' AS o ON ui.source_id=o.order_id LEFT JOIN '.$GLOBALS['ecs']->table('user_rank').
        ' AS r ON u.user_rank=r.rank_id WHERE ui.confirm=0 '.$where.' AND u.role_id IS NOT NULL AND u.user_name IS NOT NULL ORDER BY '.$sort.
        ' LIMIT '.($filter['page'] - 1)*$filter['page_size'].",{$filter['page_size']}";

    $result = $GLOBALS['db']->getAll($sql_select);

    $integral = $result;
    $filter['act'] = 'sch_con_inte'; 

    foreach($integral as &$val)
    {
        $val['receive_time'] = date('Y-m-d H:i',$val['receive_time']);
        $val['validity'] = date('Y-m-d H:i',$val['validity']);
    }

    $smarty->assign('sort_condition',$condition);
    $smarty->assign('sort_type',$sort_type);
    $smarty->assign('integral',$integral);
    $smarty->assign('condition',$condition.$append_condition);
    $smarty->assign('plan',3);
    $smarty->assign('filter',$filter);
    $smarty->assign('empty',$filter['record_count']);
    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('sch_confirm_inte.htm');

    die($json->encode($res));
}

//搜索积分日志
elseif ($_REQUEST['act'] == 'sch_inte_log')
{
    $role_id = intval($_REQUEST['role_id']);
    $user_info = mysql_real_escape_string($_REQUEST['user_info']);
    $start_time = strtotime($_REQUEST['start_time']);
    $end_time = strtotime($_REQUEST['end_time']);
    $distinct = mysql_real_escape_string($_REQUEST['distinct']);

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

    $condition = '';
    $where;
    if($role_id != -1)
    {
        $where .= " AND i.platform=$role_id "; 
        $condition .= "&role_id=$role_id"; 
    }

    if(!empty($user_info))
    {
        $pattern = '/[^\x00-\x80]/'; 
        //用户名
        if(preg_match($pattern,$user_info))
        {
            $where .= " AND u.user_name LIKE '%$user_info%' ";
            $condition .= "&user_info=$user_info";
        }
        //电话号码
        elseif(preg_match("/1[35]{1}\d{9}/",$user_info))
        {
            $where .= " AND u.mobile_phone LIKE '$user_info' ";
            $condition .= "&user_info LIKE '%$user_info'%";
        }
    }

    if($start_time && $end_time)
    {
        $where .= " AND ui.confirm_time BETWEEN $start_time AND $end_time "; 
        $condition .= "&start_time=$start_time&end_time=$end_time";
    }
    elseif($start_time || $end_time)
    {
        if($start_time == 0)
        {
            $where .= " AND ui.confirm_time BETWEEN 0 AND $end_time ";
            $condition .= "&end_time=$end_time";
        }
        else
        {
            $where .= " AND ui.confirm_time BETWEEN $start_time AND UNIX_TIMESTAMP(NOW()) ";
            $condition .= "&start_time=$start_time";
        }
    }

    if(!empty($distinct))
    {
        $arr_distinct = explode('/',$distinct);
        $where .= " AND (ui.exchange_points+ui.pre_points) BETWEEN $arr_distinct[0] AND $arr_distinct[1] ";
        $condition .= "&distinct=$distinct";
    }


    $res['row_id'] = intval($_REQUEST['row_id'] + 1);

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_integral').
        ' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('users').
        ' AS u ON ui.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS a ON u.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('integral').
        ' AS i ON ui.integral_id=i.integral_id LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
        ' AS o ON ui.source_id=o.order_id LEFT JOIN '.$GLOBALS['ecs']->table('user_rank').
        " AS r ON u.user_rank=r.rank_id WHERE ui.confirm=1 ".$where.' ORDER BY ui.confirm_time DESC,ui.receive_time DESC';

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
        'act'           => 'sch_inte_log',
    );

    $sql_select = 'SELECT ui.*,u.user_name,r.rank_name,a.user_name as admin_name,i.integral_title,(ui.pre_points+ui.exchange_points) as cur_integral,o.goods_amount FROM '.$GLOBALS['ecs']->table('user_integral').
        ' AS ui LEFT JOIN '.$GLOBALS['ecs']->table('users').
        ' AS u ON ui.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS a ON u.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('integral').
        ' AS i ON ui.integral_id=i.integral_id LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
        ' AS o ON ui.source_id=o.order_id LEFT JOIN '.$GLOBALS['ecs']->table('user_rank').
        " AS r ON u.user_rank=r.rank_id WHERE ui.confirm=1 ".$where.' ORDER BY ui.confirm_time DESC,ui.receive_time DESC'.
        ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

    $result = $GLOBALS['db']->getAll($sql_select);

    foreach($result as &$val)
    {
        $val['receive_time'] = date('m月d H:i:s',$val['receive_time']);
        $val['confirm_time'] = date('m月d H:i:s',$val['confirm_time']);
        $val['validity'] = date('Y-m-d H:i',$val['validity']);
    }

    $smarty->assign('integral',$result);
    $smarty->assign('condition',$condition);
    $smarty->assign('filter',$filter);

    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('integral_log_part.htm');

    die($json->encode($res));

}

//添加会员子分组
elseif ($_REQUEST['act'] == 'create_cld')
{
    if(admin_priv('create_cld','',false))
    {
        $res['row_id'] = intval($_REQUEST['row_id']) + 1;
        $rank_id = intval($_REQUEST['rank_id']);

        $sql_select = 'SELECT r.*,p.* FROM '.$GLOBALS['ecs']->table('user_rank').' r, '.
            $GLOBALS['ecs']->table('role').' p '.
            " WHERE rank_id=$rank_id AND r.role_id=p.role_id";
        $rank = $GLOBALS['db']->getRow($sql_select);

        $smarty->assign('rank',$rank);
        $res['info'] = $smarty->fetch('vip_child_group.htm');

        die($json->encode($res));
    }
}

//使用积分
elseif ($_REQUEST['act'] == 'pay_points')
{
    $user_id = intval($_REQUEST['user_id']);
    $pay_points = intval($_REQUEST['pay_points']);
    $money = floatval($_REQUEST['money']);
    $res = array('timeout'=>2000,'req_msg'=>true,'code'=>1,'message'=>'');

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET pay_points=pay_points+$pay_points,rank_points=rank_points-$pay_points WHERE user_id=$user_id";

    $result = $GLOBALS['db']->query($sql_update);
    $res['message'] = $result == true ? '成功确认使用' : '确认使用失败';

    //积分改变记录
    if($result)
    {
        $sql_select = 'SELECT rank_points FROM '.$GLOBALS['ecs']->table('users')." WHERE user_id=$user_id";
        $pre_points = $GLOBALS['db']->getOne($sql_select);
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_integral')
            .'(integral_id,points,haduse,source,source_id,receive_time,validity,integral_info,user_id,admin_id,exchange_points,increase_reduce,pre_points,confirm,confirm_time)'
            ." VALUES(0,0-$pay_points,0,'order',0,".time().
            ",0,0,$user_id,{$_SESSION['admin_id']},0-$pay_points,0,$pre_points,1,".time().')';

        $GLOBALS['db']->query($sql_insert);
    }
    die($json->encode($res));
}

//冲值和提现申请
elseif ($_REQUEST['act'] == 'surplus_list')
{
    $user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    $payment = array();
    $sql = "SELECT pay_id, pay_name FROM ".$GLOBALS['ecs']->table('payment').
        " WHERE enabled = 1 AND pay_code != 'cod' ORDER BY pay_id";

    $res = $GLOBALS['db']->getAll($sql);
    $payment = $res;

    if (isset($_REQUEST['process_type']))
    {
        $smarty->assign('process_type_' . intval($_REQUEST['process_type']), 'selected="selected"');
    }
    if (isset($_REQUEST['is_paid']))
    {
        $smarty->assign('is_paid_' . intval($_REQUEST['is_paid']), 'selected="selected"');
    }
    if(isset($_REQUEST['keywords']))
    {
        $smarty->assign('keywords',mysql_real_escape_string($_REQUEST['keywords']));
    }
    if(isset($_REQUEST['payment']))
    {
        $smarty->assign('payment',$_REQUEST['payment']);
    }
    else
    {
        $smarty->assign('payment',-1);
    }

    if(isset($_REQUEST['start_date']))
    {
        $smarty->assign('start_date',$_REQUEST['start_date']);
    }
    if(isset($_REQUEST['end_date']))
    {
        $smarty->assign('end_date',$_REQUEST['end_date']); 
    }

    $smarty->assign('ur_here',       $_LANG['09_user_account']);
    $smarty->assign('id',            $user_id);
    $smarty->assign('payment_list',  $payment);
    $smarty->assign('action_link',   array('text' => $_LANG['surplus_add'], 'href'=>'user_account.php?act=add'));

    $list = account_list();

    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('condition',    $list['condition']);

    assign_query_info();
    $res['main'] = $smarty->fetch('user_account_list.htm');

    die($json->encode($res));
}

//冲值记录查询
elseif ($_REQUEST['act'] == 'query')
{
    $list = account_list();
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('user_account_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
} 

//搜索将要批量修改等级的
elseif ($_REQUEST['act'] == 'sch_batch')
{
    $min_order_times = isset($_REQUEST['min_order_times']) ? floatval($_REQUEST['min_order_times']) : '';
    $max_order_times = isset($_REQUEST['max_order_times']) ? floatval($_REQUEST['max_order_times']) : '';
    $min_order_amount = isset($_REQUEST['min_order_amount']) ? floatval($_REQUEST['min_order_amount']) : '';
    $max_order_amount = isset($_REQUEST['max_order_amount']) ? floatval($_REQUEST['max_order_amount']) : '';
    $once_order = isset($_REQUEST['once_order']) ? floatval($_REQUEST['once_order']) : '';
    $role_id = intval($_REQUEST['role_id']);

    $clb_where = ' WHERE order_status=5 AND pay_status=2';
    $where = ' WHERE 1 ';
    $condition = 1;

    if($min_order_times != '' && $max_order_times != '')
    {
        $where .= " AND o.order_times BETWEEN $min_order_times AND $max_order_times "; 
        $condition = 0;
    }

    if($min_order_amount != '' && $max_order_amount != '')
    {
        if($condition != 0)
        {
            $where .= " OR o.order_amount BETWEEN $min_order_amount AND $max_order_amount ";
        }
        else
        {
            $where .= " AND o.order_amount BETWEEN $min_order_amount AND $max_order_amount ";
            $condition = 0;
        }
    }

    if($once_order != '')
    {
        if($condition != 0)
        {
            $where .=  " OR o.once_order>=$once_order ";
        }
        else
        {
            $where .=  " AND o.once_order>=$once_order ";
        }
    }

    $sql_clb_select = 'SELECT MAX(goods_amount) AS once_order,SUM(goods_amount) AS order_amount,COUNT(order_id) AS order_times,user_id FROM '.$GLOBALS['ecs']->table('order_info').$clb_where.' GROUP BY user_id';

    if($role_id != 0)
    {
        $where .= " AND u.role_id=$role_id";
        $sql_select = 'SELECT COUNT(*) FROM ('.$sql_clb_select.') AS o LEFT JOIN '.
            $GLOBALS['ecs']->table('users').' AS u ON o.user_id=u.user_id '.$where;
    }
    else
    {
        $sql_select = 'SELECT COUNT(*) FROM ('.$sql_clb_select.') AS o '.$where;
    }

    $result = $GLOBALS['db']->getOne($sql_select);
    if($result)
    {
        $res['main'] = '确认修改'.$result.'个顾客等级';
    }
    else
    {
        $res['main'] = '确认修改0个顾客等级';
    }

    die($json->encode($res));
}

//会员充值提现操作
elseif ($_REQUEST['act'] == 'act_account')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $amount = isset($_REQUEST['surplus_amount']) ? floatval($_REQUEST['surplus_amount']) : 0;
    $user_id = intval($_REQUEST['user_id']);

    $res['req_msg'] = true;
    $res['timeout'] = 2000;

    if ($amount <= 0) {
        $res['message'] = '添加失败';
        die($json->encode($res));
    }

    $surplus = array(
        'user_id'      => $user_id,
        'rec_id'       => !empty($_REQUEST['rec_id']) ? intval($_REQUEST['rec_id']) : 0,
        'process_type' => isset($_REQUEST['surplus_type']) ?intval($_REQUEST['surplus_type']) : 0,

        'payment'   => isset($_REQUEST['payment_id']) ? intval($_REQUEST['payment_id']) : 0,
        'user_note' => isset($_REQUEST['user_note']) ? trim($_REQUEST['user_note']) : '',
        'amount'    => $amount
    );


    //充值
    if($process_type == 0) {
        $res['insert_id'] = insert_user_account($surplus,$amount);
        if($res['insert_id']) {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_account')." SET admin_user='{$_SESSION['admin_name']}'";
            $GLOBALS['db']->query($sql_update);
            $res['message'] = '添加成功';
        } else {
            $res['message'] = '添加失败';
        }
    }

    die($json->encode($res));
}

//撤销确定会员积分
elseif ($_REQUEST['act'] == 'del_user_inte') {
    $res           = array();
    $res['row_id'] = intval($_REQUEST['row_id']);
    $user_inte_id  = intval($_REQUEST['user_inte_id']);

    $res['req_msg'] = true;
    $res['timeout'] = 2000;
    $res['code']    = 0;

    $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('user_integral')." WHERE user_integral_id=$user_inte_id";
    $result = $GLOBALS['db']->query($sql_del);
    if($result) {
        $res['code']    = 1;
        $res['message'] = '撤消成功';
    } else {
        $res['message'] = '撤消失败';
    }

    die($json->encode($res));
}

/* 列出通话记录 */
elseif ($_REQUEST['act'] == 'rec_list') {
    $msg = array (
        'req_msg' => true,
        'timeout' => 2000,
        'message' => '该服务记录无录音提供！',
    );

    $service_id = intval($_REQUEST['service_id']);
    $sql_select= 'SELECT user_id,FROM_UNIXTIME(service_time, "%Y%m%d") audio_date FROM '.
        $GLOBALS['ecs']->table('service')." WHERE service_id=$service_id";
    $service_info = $GLOBALS['db']->getRow($sql_select);

    $dir_name = '/records/record_'.($service_info['user_id']%7)."/{$service_info['user_id']}";
    if (!file_exists($dir_name)) {
        echo $json->encode($msg);
        return;
    }

    $file_list = scandir($dir_name);
    array_shift($file_list);
    array_shift($file_list);

    $final_list = array();
    foreach ($file_list as $val){
        if (preg_match("/{$service_info['audio_date']}/", $val)) {
            $final_list[] = "http://192.168.1.217/$dir_name/$val";
        }
    }

    if (empty($final_list)) {
        echo $json->encode($msg);
        return;
    }

    $smarty->assign('service_id', $service_id);
    $smarty->assign('audio_list', $final_list);

    $msg = array(
        'req_msg'    => true,
        'title'      => '通话录音',
        'btncontent' => '关闭',
        'message'    => $smarty->fetch('tapes.htm')
    );

    echo $json->encode($msg);
    return;
}

/*经典通话录音*/
elseif($_REQUEST['act'] == 'class_tape'){
    $favor_id = isset($_REQUEST['favor_id']) ? intval($_REQUEST['favor_id']) : 0;
    if($favor_id){
        $sql_select = "SELECT favor_id,CONCAT('../../',file_path) file_path FROM ".$GLOBALS['ecs']->table('tape_favorite').
            " WHERE favor_id=$favor_id";
        $file_list = $GLOBALS['db']->getRow($sql_select);
        if($file_list){
            $audio_list[] = $file_list['file_path'];
            $smarty->assign('audio_list', $audio_list);
        }
    }

    $msg = array(
        'req_msg'    => true,
        'title'      => '通话录音',
        'btncontent' => '关闭',
        'message'    => $smarty->fetch('tapes.htm')
    );

    die($json->encode($msg));
}

/*分类查找录音*/
elseif($_REQUEST['act']=='select_tape'){

    $class        = isset($_REQUEST['class']) ? intval($_REQUEST['class']) : 0;
    $tape_collect = select_tape();

    $smarty->assign('class',$class);
    $smarty->assign('tape_collect_copyright','boutique');
    $smarty->assign('tape_collect',$tape_collect);

    if(admin_priv('all','',false)){
        $smarty->assign('authority','authority');
    }

    $res['main'] = $smarty->fetch('tape_collect_div.htm');
    die($json->encode($res));
}

/*添加录音说明*/
elseif($_REQUEST['act'] == 'add_tape_explain'){
    $simple_explain = isset($_REQUEST['simple_explain']) ? mysql_real_escape_string($_REQUEST['simple_explain']) : '';
    $favor_id = isset($_REQUEST['favor_id']) ? intval($_REQUEST['favor_id']) : 0;

    $res = array(
        'req_msg' => true,
        'timeout' => 2000,
        'code'    => false,
        'message' => ''
    );

    if($simple_explain && $favor_id){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('tape_favorite').
            " SET simple_explain='$simple_explain' WHERE favor_id=$favor_id";
        $res['code'] = $GLOBALS['db']->query($sql_update);
        $res['message'] = $res['code'] ? '添加成功' : '添加失败，请联系技术部';
    }else{
        $res['message'] = '添加失败';
    }
    die($json->encode($res));
}

//修改通话录音类别
elseif($_REQUEST['act'] =='modify_tape_class'){

    $favor_id    = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    $class       = isset($_REQUEST['value']) ? intval($_REQUEST['value']) : 0;
    $res['code'] = false;
    $arr = array('减肥','补肾','三高');

    if($favor_id && $class){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('tape_favorite').
            " SET class=$class WHERE favor_id=$favor_id";

        $res['code'] = $GLOBALS['db']->query($sql_update);
        $res['id']   = $favor_id;
    }

    if($res['code']){
        $res['main'] = '<button onclick="showSelect(this,'.$favor_id.
            ')" class="btn_new">'.$arr[$class-1].'</button>';
    }else{
        $res['main'] = '<button onclick="showSelect(this,'.$favor_id.
            ')" class="btn_new">分类</button>';
    }

    die($json->encode($res));
}

/* 收藏录音 */
elseif ($_REQUEST['act'] == 'collect_tape') {
    $file = mysql_real_escape_string($_REQUEST['file']);
    $service_id = intval($_REQUEST['service_id']);

    $file = str_replace("{$_SERVER['HTTP_ORIGIN']}/", '', $file);

    $sql_insert = 'REPLACE INTO '.$GLOBALS['ecs']->table('tape_favorite').
        '(admin_id,service_id,file_path)VALUES('."{$_SESSION['admin_id']},$service_id,'$file')";
    if ($GLOBALS['db']->query($sql_insert)) {
        $msg = array(
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '收藏成功！'
        );
    } else {
        $msg = array(
            'req_msg' => true,
            'timeout' => 2000,
            'message' => '收藏失败，请联系技术人员！'
        );
    }

    echo $json->encode($msg);
    return;
}

/*
 * 通话录音收藏
 * public 0 私人 1 公开 2 回收站 3 面向所有员工公开
 */
elseif ($_REQUEST['act'] == 'tape_favorite') {
    $where                  = ' WHERE 1 ';
    $order_by               = ' ORDER BY a.add_time DESC,a.praise DESC';
    $tape_collect_copyright = isset($_REQUEST['tape_collect_copyright']) ? mysql_real_escape_string($_REQUEST['tape_collect_copyright']) : 'privacy';
    $user_name              = isset($_REQUEST['user_name']) ? trim(mysql_real_escape_string($_REQUEST['user_name'])) : '';

    if(admin_priv('all','',false)){
        $smarty->assign('tape_favorite_view',true);
        $where .= ' AND a.status=1 ';
        switch($tape_collect_copyright){
        case 'privacy' :
            $where .= ' AND t.public=0 ';
            break;
        case 'my_public' :
            $where .= ' AND t.public=1 ';
            break;
        case 'other_public' :
            $where .= ' AND t.public=1 ';
            break;
        case 'recycle' :
            $where .= ' AND t.public=2 ';
            break;
        case 'collect_other_public' :
            $where .= " AND t.public=0 AND original_favor_id<>0 ";
            break;
        case 'boutique' :
            $where .= ' AND t.public=3 ';
            break;
        }
    }else{
        switch($tape_collect_copyright){
        case 'privacy' :
            $where .= " AND t.public=0 AND t.admin_id={$_SESSION['admin_id']} AND original_favor_id=0";
            break;
        case 'my_public' :
            $where .= " AND t.public=1 AND t.admin_id={$_SESSION['admin_id']}";
            break;
        case 'other_public' :
            $where .= " AND t.public=1 AND t.admin_id<>{$_SESSION['admin_id']}";
            break;
        case 'recycle' :
            $where .= " AND t.public=2 AND t.admin_id={$_SESSION['admin_id']}";
            break;
        case 'collect_other_public' :
            $where .= " AND t.public=0 AND t.admin_id={$_SESSION['admin_id']} AND original_favor_id<>0";
            break;
        case 'boutique' :
            $where .= ' AND t.public=3 ';
            break;
        }
    }
    if('boutique' == $tape_collect_copyright){

        $class        = isset($_REQUEST['class']) ? intval($_REQUEST['class']) : 0;
        $tape_collect = select_tape();
        $smarty->assign('class',$class);
    }else{
        if(!empty($user_name)){
            $where .= " AND s.user_name LIKE '%$user_name%' ";
        }
        $tape_collect = get_tape_collect($where,$order_by);
    }

    $smarty->assign('tape_collect',$tape_collect);
    $smarty->assign('tape_collect_copyright',$tape_collect_copyright);

    if(admin_priv('all','',false)){
        $smarty->assign('authority','authority');
    }

    if(isset($_REQUEST['from_sch'])){
        $res['main'] = $smarty->fetch('tape_collect_div.htm');
    }else{
        $smarty->assign('tape_collect_div',$smarty->fetch('tape_collect_div.htm'));
        $res['main'] = $smarty->fetch('tape_collect.htm');
    }
    die($json->encode($res));
}

/*取消收藏*/
elseif($_REQUEST['act'] == 'favor_del'){

    $table_name = mysql_real_escape_string($_REQUEST['table_name']);
    $favor_id   = isset($_REQUEST['favor_id']) ? intval($_REQUEST['favor_id']) : 0;
    $tr_index   = isset($_REQUEST['tr_index']) ? intval($_REQUEST['tr_index']) : 0;

    $res = array(
        'req_msg'    => true,
        'timeout'    => 2000,
        'code'       => false,
        'message'    => '',
        'tr_index'   => $tr_index,
        'table_name' => $table_name
    );

    if($favor_id != 0){
        $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('tape_favorite')." WHERE favor_id=$favor_id";
        $res['code'] = $GLOBALS['db']->query($sql_del);

        $res['message'] = $res['code'] ? '删除成功' : '删除失败';
    }

    die($json->encode($res));
}

/*公开、收藏、私有、评论通话录音*/
elseif($_REQUEST['act'] == 'ch_tape_public'){
    $table_name = isset($_REQUEST['table_name']) ? mysql_real_escape_string($_REQUEST['table_name']) : '';
    $tr_index   = isset($_REQUEST['tr_index']) ? intval($_REQUEST['tr_index']) : '';
    $copyright  = isset($_REQUEST['copyright']) ? mysql_real_escape_string($_REQUEST['copyright']) : ''; 
    $favor_id   = isset($_REQUEST['favor_id']) ? intval($_REQUEST['favor_id']) : 0;
    $res        = array(
        'req_msg'    => true,
        'timeout'    => 2000,
        'message'    => '',
        'table_name' => $table_name,
        'tr_index'   => $tr_index,
        'code'       => 0
    );

    $do_what = $alarm_label = '';

    if('praise' == $copyright){
        $comment = !empty($_REQUEST['comment']) ? trim(mysql_real_escape_string($_REQUEST['comment'])) : '';

        if(!empty($comment) && !empty($favor_id)){
            comment_tape($comment,$res,$favor_id);
        }else{
            $res['message'] = '评论失败';
        }
        die($json->encode($res));

    }elseif('del' == $copyright){
        if(empty($favor_id)){
            $res['message'] = '删除失败';
        }else{
            $sql_del        = 'DELETE FROM '.$GLOBALS['ecs']->table('tape_favorite')." WHERE favor_id = $favor_id";
            $res['code']    = $GLOBALS['db']->query($sql_del);
            $res['message'] = $res['code'] ? '彻底删除成功' : '删除失败';
        }

        die($json->encode($res));

    }elseif('collect' == $copyright){
        /*收藏他人公开录音*/
        $sql_select = 'SELECT public,COUNT(*) AS total FROM '.$GLOBALS['ecs']->table('tape_favorite').
            " WHERE admin_id={$_SESSION['admin_id']} AND original_favor_id=$favor_id";
        $result = $GLOBALS['db']->getRow($sql_select);

        if(empty($result)||empty($result['total'])){
            $sql_select = 'SELECT file_path,service_id,simple_explain FROM '.$GLOBALS['ecs']->table('tape_favorite')." WHERE favor_id = $favor_id";
            $result     = $GLOBALS['db']->getRow($sql_select);

            if($result){
                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('tape_favorite').
                    '(admin_id,service_id,original_favor_id,add_time,file_path,public,simple_explain)'.
                    "VALUES({$_SESSION['admin_id']},{$result['service_id']},$favor_id,{$_SERVER['REQUEST_TIME']},'{$result['file_path']}',0,'{$result['simple_explain']}')";

                $res['code']    = $GLOBALS['db']->query($sql_insert);
                $res['message'] = $res['code'] ? '收藏成功' : '收藏失败，请联系管理员';
            }else{
                $res['message'] = '收藏失败，请联系管理员';
            }
        }else{
            if(0 == $result['public'] || 1 == $result['public']){
                $res['message'] = '你已经收藏的本录音,不需要重复收藏';
            }else{
                $res['message'] =  '已经存在回收站，你可以重新收藏'; 
            }

        }

        $res['table_name '] = '';
        die($json->encode($res));
    }elseif('public' == $copyright){
        $explain = isset($_REQUEST['explain']) ? mysql_real_escape_string($_REQUEST['explain']) : '';

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('tape_favorite').
            " SET public=1,simple_explain='$explain' WHERE favor_id=$favor_id";

        $res['code']    = $GLOBALS['db']->query($sql_update);
        $res['message'] = $res['code'] ? '成功公开' : '公开失败';

        die($json->encode($res));
    }
    else{
        switch($copyright){
        case 'recollect':
            $public = 0;
            $alarm_label = '重新收藏';
            break;
        case 'move' :
            $public = 2;
            $alarm_label = '删除';
            break ;
        case 'privacy' :
            $public = 0;
            $alarm_label = '私有';
            break ;
        }

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('tape_favorite').
            " SET public=$public WHERE favor_id=$favor_id";
        $res['code'] = $GLOBALS['db']->query($sql_update);
        $res['message'] = $res['code'] ? "$alarm_label 成功" : "$alarm_label 失败";
    }

    die($json->encode($res));
}

/*查看通话评论*/
elseif($_REQUEST['act'] == 'get_tape_comment'){
    $favor_id = isset($_REQUEST['favor_id']) ? intval($_REQUEST['favor_id']) : 0; 

    if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'comment'){
        $comment = isset($_REQUEST['comment']) ? time(mysql_real_escape_string($_REQUEST['comment'])) : ''; 
        $res = array();
        if(!empty($comment) && !empty($favor_id)){
            comment_tape($comment,$res,$favor_id);
        }else{
            $res['message'] = '评论失败';
        }
    }

    if(!empty($favor_id)){
        $sql_select = 'SELECT g.comment,g.grade_admin,a.user_name AS admin_name,g.grade_time FROM '.$GLOBALS['ecs']->table('tape_grade').
            ' g LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
            " a ON g.grade_admin=a.user_id WHERE favor_id=$favor_id";
        $tape_praise = $GLOBALS['db']->getAll($sql_select);

        if($tape_praise){
            foreach($tape_praise as &$val){
                $val['grade_time'] = date('Y-m-d H:i:s',$val['grade_time']);
            }
        }
    }

    $smarty->assign('favor_id',$favor_id);
    $smarty->assign('res',$res);
    $smarty->assign('total',count($tape_praise));
    $smarty->assign('tape_praise',$tape_praise);
    $smarty->display('tape_praise.htm');
}

/*清空回收站的通话录音收藏*/
elseif($_REQUEST['act'] == 'clear_tape'){
    $item = isset($_REQUEST['item']) ? mysql_real_escape_string($_REQUEST['item']) : '';
    $res = array(
        'req_msg' => true,
        'timeout' => 2000,
        'message' => '',
        'code' => false,
    );

    if('privacy' == $item){
        $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('tape_favorite').
            " WHERE admin_id={$_SESSION['admin_id']} AND public=2";
    }else if('half' == $item){
        //清空半年前的通话录音
        $sql_select = 'SELECT original_favor_id FROM '.$GLOBALS['ecs']->table('tape_favorite').
            " GROUP BY original_favor_id";

        $collected_favor_id = $GLOBALS['db']->getCol($sql_select);
        $refer_time         = strtotime(date('Y-'.date('m').'-1 00:00:00'));

        if($collected_favor_id){
            $collected_favor_id = join(',',$collected_favor_id); 
            $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('tape_favorite').
                " WHERE favor_id NOT IN($collected_favor_id) AND add_time>=$refer_time AND public<>3";
        }
    }

    if($sql_del){
        $res['code'] = $GLOBALS['db']->query($sql_del);
        $res['message'] = $res['code'] ? '清除成功' : '清除失败,请联系技术人员';
    }else{
        $res['message'] =   '清除失败,请联系技术人员';
    }

    die($json->encode($res));
}

/*选择员工*/
elseif($_REQUEST['act'] == 'select_admin'){
    $sql_select = 'SELECT role_name,role_id FROM '.$GLOBALS['ecs']->table('role').
        ' WHERE role_id IN ('.SALE.')';

    $role_list  = $GLOBALS['db']->getAll($sql_select);
    $sql_select = 'SELECT user_id,user_name,role_id FROM '.$GLOBALS['ecs']->table('admin_user').
        ' WHERE status>0 && stats>0';

    $admin_list = $GLOBALS['db']->getAll($sql_select);

    foreach($role_list as &$val){
        foreach($admin_list as $key=>&$admin){
            if($val['role_id'] == $admin['role_id']){
                $val['admin_list'][] = $admin;
                unset($admin_list[$key]);
            }
        }
    }

    $smarty->assign('platform',$role_list);

    $res['req_msg']    = true;
    $res['btncontent'] = false;
    $res['message']    = $smarty->fetch('admin_list.htm');

    die($json->encode($res));
}

/*添加录音推送*/
elseif($_REQUEST['act'] == 'push_tape'){
    $tape_list  = isset($tape_list) ? mysql_real_escape_string($tape_list) : '';
    $admin_list = isset($admin_list) ? mysql_real_escape_string($admin_list) : '';

    if (empty($tape_list) && empty($admin_list)) {
        $res['message'] == '推送失败,请联系技术部';
    }else{
    }
}

/* 促销管理 */
elseif ($_REQUEST['act'] == 'promotion_list') {
    $promote_status = array(1 => '未开始',2 => '进行中',3 => '已结束',4 => '已暂停',5 => '已删除');
    $sql_select = 'SELECT p.promote_id,p.promote_name,p.goods_sn,g.goods_name,p.start_time,p.end_time,r.role_name,'.
        'p.promote_status,p.promote_price,g.cost_price FROM '.$GLOBALS['ecs']->table('promote').' p LEFT JOIN '.
        $GLOBALS['ecs']->table('goods').' g ON p.goods_sn=g.goods_sn LEFT JOIN '.$GLOBALS['ecs']->table('role').
        ' r ON r.role_id=p.platform WHERE p.end_time>UNIX_TIMESTAMP() AND p.promote_status<5 ORDER BY end_time DESC';
    $promotion_list = $GLOBALS['db']->getAll($sql_select);
    $now_time = time();
    foreach ($promotion_list as &$val){
        if ($now_time < $val['start_time']) {
            $val['promote_status'] = $promote_status[1];
        } elseif ($now_time > $val['start_time'] && $now_time < $val['end_time']) {
            $val['promote_status'] = $promote_status[2];
        } elseif ($now_time > $val['end_time']) {
            $val['promote_status'] = $promote_status[3];
        }
        $val['start_time']     = date('Y-m-d H:i:s', $val['start_time']);
        $val['end_time']       = date('Y-m-d H:i:s', $val['end_time']);
    }

    $smarty->assign('promotion_list', $promotion_list);
    $smarty->assign('curr_title', '促销商品列表');
    $res['main'] = $smarty->fetch('promotion_list.htm');
    echo $json->encode($res);
    return;
}

/* 添加促销活动 */
elseif ($_REQUEST['act'] == 'add_promote') {
    $smarty->assign('curr_title', '添加促销活动');
    $smarty->assign('platform', platform_list());
    $res['main'] = $smarty->fetch('add_promote.htm');
    echo $json->encode($res);
    return;
}

/* 编辑促销活动 */
elseif ($_REQUEST['act'] == 'edit_promote') {
    $id = intval($_REQUEST['id']);
    $sql_select = 'SELECT p.promote_id,p.promote_name,p.goods_sn,FROM_UNIXTIME(p.start_time, "%Y-%m-%d %H:%i:%s")'.
        ' start_time,FROM_UNIXTIME(p.end_time,"%Y-%m-%d %H:%i:%s") end_time,g.goods_name,r.role_name,p.platform,'.
        'p.promote_status,p.promote_price,g.cost_price FROM '.$GLOBALS['ecs']->table('promote').' p LEFT JOIN '.
        $GLOBALS['ecs']->table('goods').' g ON p.goods_sn=g.goods_sn LEFT JOIN '.$GLOBALS['ecs']->table('role').
        " r ON r.role_id=p.platform WHERE p.promote_id=$id ORDER BY end_time DESC";
    $promotion_info = $GLOBALS['db']->getRow($sql_select);
    $smarty->assign('curr_title', '修改促销活动');
    $smarty->assign('platform', platform_list());
    $smarty->assign('promotion_info', $promotion_info);
    $res['main'] = $smarty->fetch('add_promote.htm');
    echo $json->encode($res);
    return;
}

/* 删除促销活动 */
elseif ($_REQUEST['act'] == 'delete_promote') {
    $msg = array('req_msg'=>true,'timeout'=>2000);
    $id = intval($_REQUEST['id']);
    $sql_delete = 'UPDATE '.$GLOBALS['ecs']->table('promote').
        " SET promote_status=5 WHERE promote_id=$id";
    if ($GLOBALS['db']->query($sql_delete)) {
        $msg['message'] = '删除成功！';
        $msg['redirectURL'] = 'service.php?act=promotion_list';
        echo $json->encode($msg);
        return;
    }
    $msg['message'] = '删除失败，请稍后再试';
    echo $json->encode($msg);
    return;
}

/* 获取商品列表 */
elseif ($_REQUEST['act'] == 'get_goods_list') {
    $goods_name = mysql_real_escape_string($_REQUEST['goods_name']);
    $sql_select = 'SELECT goods_sn,goods_name FROM '.$GLOBALS['ecs']->table('goods')." WHERE goods_name LIKE '%$goods_name%'";
    $goods_list = $GLOBALS['db']->getAll($sql_select);
    echo $json->encode($goods_list);
    return;
}

/* 保存促销信息 */
elseif ($_REQUEST['act'] == 'insert') {
    $msg = array('req_msg'=>true,'timeout'=>2000);
    $info = $json->decode($_REQUEST['info'], true);
    $info['promote_name'] = mysql_real_escape_string($info['promote_name']);
    $info['platform'] = intval($info['platform']);
    $info['goods_sn'] = intval($info['goods_sn']);
    $info['promote_price'] = floatval($info['promote_price']);
    if (isset($info['start_time'], $info['end_time']) && (!$info['start_time'] == !$info['end_time'])) {
        $info['start_time'] = strtotime($info['start_time']);
        $info['end_time']   = strtotime($info['end_time']);
    }
    $fields = implode(',',array_keys($info));
    $values = implode("','", array_values($info));
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('promote')."($fields)VALUES('$values')";
    if ($GLOBALS['db']->query($sql_insert)) {
        $msg['message']     = '添加成功！';
        $msg['redirectURL'] = 'service.php?act=promotion_list';
        echo $json->encode($msg);
        return;
    }
    $msg['message'] = '添加失败，请重新添加！';
    echo $json->encode($msg);
    return;
}

/* 修改促销活动 */
elseif ($_REQUEST['act'] == 'update') {
    $msg = array('req_msg'=>true,'timeout'=>2000);
    $info = $json->decode($_REQUEST['info'], true);
    $id = intval($info['promote_id']);
    unset($info['promote_id']);
    $info['promote_name'] = mysql_real_escape_string($info['promote_name']);
    $info['platform'] = intval($info['platform']);
    $info['goods_sn'] = intval($info['goods_sn']);
    $info['promote_price'] = floatval($info['promote_price']);
    if (isset($info['start_time'], $info['end_time']) && (!$info['start_time'] == !$info['end_time'])) {
        $info['start_time'] = strtotime($info['start_time']);
        $info['end_time']   = strtotime($info['end_time']);
    }
    $fields = array_keys($info);
    $update = array();
    foreach ($fields as $val){
        if ($info[$val]) {
            $update[] = "$val='{$info[$val]}'";
        }
    }
    $values = implode(',', $update);
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('promote')."SET $values WHERE promote_id=$id";
    if ($GLOBALS['db']->query($sql_update)) {
        $msg['message']     = '修改成功！';
        $msg['redirectURL'] = 'service.php?act=promotion_list';
        echo $json->encode($msg);
        return;
    }
    $msg['message'] = '修改失败，请重新修改！';
    echo $json->encode($msg);
    return;
}

//快速拨号
elseif($_REQUEST['act'] == 'quick_call'){
    $user_id = intval($_GET['user_id']);
    $f = intval($_GET['f']);

    $admin_id = $_SESSION['admin_id'];
    $sql = ' SELECT ext FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$admin_id";
    $ext   = $GLOBALS['db']->getOne($sql);
    $res = 2;
    if ($user_id && $ext) {
        $manner = mysql_real_escape_string($_GET['manner']);
        $field = $manner == 'tel' ? 'home_phone' : 'mobile_phone';
        $sql = "SELECT $field FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id=$user_id"; 
        $phone = $GLOBALS['db']->getOne($sql);
        if ($phone) {
            //本地固话去区号
            $phone = str_replace('-','',$phone);
            if ($manner == 'tel' && preg_match('/^020\d+/',$phone)) {
                $phone = str_replace('020','',$phone);
            }
            if (1 == $f) {
                $phone = isLocalPhone($phone);
            }else{
                $phone = $f == 2 ? "0$phone" : $phone;
            }
            $res = file_get_contents("http://192.168.1.240/call.php?phone=$phone&exten=$ext",'r');    
            //判断是否是本地
            //if($manner == 'mobile'){
            //$url = "http://virtual.paipai.com/extinfo/GetMobileProductInfo?mobile=$phone&amount=10000&callname=getPhoneNumInfoExtCallback";
            //$result = json_decode(file_get_contents($url,'r'));
            //print_r($result);exit;
            //}
        }
    }
    die($res);
}
elseif('miss_call' == $_REQUEST['act']){
    //未接来电
    if ($_SESSION['role_id']>31 && !empty($_SESSION['ext'])) {
        $miss_call_list =  miss_call();
        if ($miss_call_list) {
            save_miss_call($miss_call_list);
        }
        $sql = 'SELECT concat(FROM_UNIXTIME(issue_time,"%Y-%m-%d %H:%i")," |",content) content FROM '.$GLOBALS['ecs']->table('public_notice')
            ." WHERE admin_id={$_SESSION['admin_id']} AND role_id={$_SESSION['role_id']} AND status=0 AND notice_type NOT IN(4,5) ORDER BY issue_time DESC";
        $res = $GLOBALS['db']->getCol($sql);
    }
    die($json->encode($res));
}
//系统消息中心
elseif('sys_msg' == $_REQUEST['act']){
    $admin_id = $_SESSION['admin_id'];
    $status   = intval($_REQUEST['status']);
    $where = ' WHERE notice_type NOT IN(4,5) ';
    if ($status && $status != 3) {
        $where .= " AND status=$status ";
    }
    $smarty->assign('status',$status);
    if (!admin_priv('all','',false)) {
        $role_id  = intval($_SESSION['role_id']);
        $where .= " AND role_id=$role_id AND admin_id=$admin_id";
        $where_role = " WHERE role_id=$role_id";
    }else{
        $role_id  = intval($_REQUEST['role_id']);
        if (!empty($role_id)) {
            $where .= " AND role_id=$role_id";
        }
        $where_role = ' WHERE role_id>31';
    }
    $sql = 'select role_id,role_name FROM '.$GLOBALS['ecs']->table('role')."$where_role ORDER BY convert(role_name using gbk) ASC";
    $role_list = $GLOBALS['db']->getAll($sql);

    $sql = 'SELECT notice_id,content,status,issue_time,title,notice_type FROM '.$GLOBALS['ecs']->table('public_notice')
        ."$where ORDER BY issue_time DESC";
    $msg_list = $GLOBALS['db']->getAll($sql);
    if ($msg_list) {
        foreach($msg_list as &$v){
            $v['issue_time'] = date('Y-m-d H:i',$v['issue_time']);
        } 
    }
    $smarty->assign('role_list',$role_list);
    $smarty->assign('msg_list',$msg_list);

    $res['main'] = $smarty->fetch('sys_msg.htm');
    die($json->encode($res));
}

//标记消息
elseif('mark_msg_status' == $_REQUEST['act']){
    $notice_id = intval($_REQUEST['notice_id']);   
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('public_notice')." SET status=1 WHERE notice_id=$notice_id";
    $code = $GLOBALS['db']->query($sql);
    if ($code) {
        $res = crm_msg('标记已读',$code);
    }else{
        $res = crm_msg();
    }

    die($json->encode($res));
}
elseif('call_history' == $_REQUEST['act']){
    $res['main'] = '';
    die($json->encode($res));
}


/* 函数区 */

//服务类型
function service_class() {
    $sql_select    = 'SELECT * FROM '.$GLOBALS['ecs']->table('service_class');
    $service_class = $GLOBALS['db']->getAll($sql_select);
    return $service_class;
}

//服务方式 
function service_manner()
{
    $sql_select    = 'SELECT * FROM '.$GLOBALS['ecs']->table('service_manner');
    $servicemanner = $GLOBALS['db']->getAll($sql_select);
    return $servicemanner;
}

//服务修改
function service_update($user_name)
{
    $sql_select = 'SELECT service_id,service_class,service_manner,service_time,user_id,user_name,logbook,admin_id,admin_name,platform,group_id,show_sev,valid FROM '
        .$GLOBALS['ecs']->table('service')
        ." where user_name=$user_name";
    $service_info = $GLOBALS['db']->getAll($sql_select);
    return $service_info;
}

/*
 * 服务记录
 */
function service_records($where = '') {
    $role_id   = $_SESSION['role_id'];
    $user_name = isset($_REQUEST['user_name']) ? trim(mysql_real_escape_string($_REQUEST['user_name'])) : '';
    $admin_id  = isset($_REQUEST['admin_id']) ? intval($_REQUEST['admin_id']) : 0;
    $condition = '';
    //起始时间
    if(isset($_REQUEST['start_time'])){
        $start_date = strlen($_REQUEST['start_time'])<10 ? strtotime($_REQUEST['start_time']) : $_REQUEST['start_time'];
    }

    if(isset($_REQUEST['end_time'])){
        $end_date = strlen($_REQUEST['end_time'])<10 ? strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']; //结束时间
    }

    //if (admin_priv('all', '', false) || admin_priv('adv_service','',false)) {
    if (admin_priv('all', '', false)) {
        if($admin_id > 0) {
            $where .= " AND admin_id=$admin_id";
            $condition .= "&admin_id=$admin_id";
        }
    }elseif (admin_priv('adv_service', '', false)) {
        $where .= " AND platform=$role_id";
        if($admin_id > 0) {
            $where .= " AND admin_id=$admin_id";
            $condition .= "&admin_id=$admin_id";
        }
    }else{
        if($admin_id > 0) {
            $where .= " AND admin_id=$admin_id";
            $condition .= "&admin_id=$admin_id";
        } else {
            $where .= " AND admin_id={$_SESSION['admin_id']} AND show_sev=1";
            $condition .= "&admin_id={$_SESSION['admin_id']}";
        }
    }

    if(empty($start_date) && empty($end_date)) {
        $start_date    = strtotime('2013-10-10 00:00:00');
        $end_date       = $_SERVER['REQUEST_TIME'];
    } elseif(!empty($start_date) || !empty($end_date)) {
        if(!empty($start_date)) {
            $end_date = $_SERVER['REQUEST_TIME'];
        } else {
            $start_date = strtotime('2013-01-01 00:00:00');
        }
    }
    $where .= " AND service_time BETWEEN $start_date AND $end_date"; 
    $condition .= "&start_time=$start_date&end_time=$end_date";

    if($user_name != '') {
        $where     .= " AND user_name LIKE '%$user_name%'";
        $condition .= "&user_name=$user_name";
    }

    if (!empty($_REQUEST['role_id'])) {
        $where     .= " AND platform=".intval($_REQUEST['role_id']);
        $condition .= "&role_id=".$_REQUEST['role_id'];
    }

    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 15; 
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('service')." WHERE 1 $where";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
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

    $sql_select = 'SELECT user_id,user_name,admin_name,service_id,service_status,logbook,admin_id,'.
        'service_time,show_sev FROM '.$GLOBALS['ecs']->table('service')
        ." WHERE 1 $where ORDER BY service_time DESC LIMIT ".($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

    $records = $GLOBALS['db']->getAll($sql_select);

    if($records){
        foreach ($records as &$val) {
            $val['service_time'] = date('Y-m-d H:i', $val['service_time']);
        }
    }
    return array('filter'=>$filter,'records'=>$records);
}

// 获取销售平台
function get_platform_list() {
    $sql_select = 'SELECT role_name, role_id FROM '.$GLOBALS['ecs']->table('role').' WHERE role_type>=1';
    $res = $GLOBALS['db']->getAll($sql_select);

    return $res;
}

//获取品牌
function get_brand()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('brand')." WHERE is_show=1";
    $brand = $GLOBALS['db']->getAll($sql_select);
    return $brand;
}

//会员充值列表
function account_list() {
    if(admin_priv('all','',false)) {
        $by_role = ''; 
    } elseif(admin_priv('account_list')) {
        $by_role = " u.role_id={$_SESSION['role_id']}";
    }

    if (isset($_REQUEST['sch_account']) && $_REQUEST['sch_account'] == 1); {
        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } else {
            $filter['page_size'] = 20; 
        }

        $user_id  = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
        $keywords = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);

        $process_type = isset($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : -1;
        $payment = isset($_REQUEST['payment']) ? intval($_REQUEST['payment']) : -1;
        $is_paid = isset($_REQUEST['is_paid']) ? intval($_REQUEST['is_paid']) : -1;
        $sort_by = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $sort_order = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $start_date = empty($_REQUEST['start_date']) ? '' : strtotime($_REQUEST['start_date']);
        $end_date = empty($_REQUEST['end_date']) ? '' : (strtotime($_REQUEST['end_date']));

        $where = " WHERE 1 ";
        $condition = '';
        if($user_id > 0) {
            $where     .= " AND ua.user_id=$user_id";
            $condition .= "&user_id=$user_id";
        }

        if($process_type != -1) {
            $where      .= " AND ua.process_type = $process_type";
            $condition  .= "&process_type=$process_type";
        } else {
            $where .= " AND ua.process_type ".db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
        }

        if($payment != -1) {
            $where      .= " AND ua.payment = $payment";
            $condition  .= "&payment=$payment";
        }

        if($is_paid != -1) {
            $where      .= " AND ua.is_paid = $is_paid";
            $condition  .= "&is_paid=$is_paid";
        }

        if ($keywords) {
            $where .= " AND u.user_name LIKE '%" . mysql_like_quote($keywords) . "%'";
            $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('user_account'). " AS ua LEFT JOIN ".
                $GLOBALS['ecs']->table('users') . " AS u ON ua.user_id=u,u.user_id " . $where;
        }

        /*　时间过滤　*/
        if (!empty($start_date) && !empty($end_date)) {
            if($is_paid != 0) {
                $where .= " AND ua.paid_time >= $start_date AND ua.paid_time < $end_date ";
            } elseif($is_paid == 0) {
                $where .= " AND ua.add_time >= $start_date AND ua.add_time < $end_date ";
            }

            $condition .= "&start_date=$start_date&end_date=$end_date";
        }

        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('user_account'). " AS ua LEFT JOIN ".
            $GLOBALS['ecs']->table('users') . " AS u ON ua.user_id=u.user_id " . $where;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
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
            'act'           => 'list'
        );

        //查询数据
        $sql  = 'SELECT ua.*, u.user_name, p.pay_name AS payment FROM ' .
            $GLOBALS['ecs']->table('user_account'). ' AS ua LEFT JOIN ' .
            $GLOBALS['ecs']->table('users'). ' AS u ON ua.user_id = u.user_id LEFT JOIN '.
            $GLOBALS['ecs']->table('payment').' AS p ON ua.payment=p.pay_id '.
            $where.$by_role. " LIMIT ".($filter['page']-1)*$filter['page_size'].", ".$filter['page_size'];

    }

    $list = $GLOBALS['db']->getAll($sql);
    foreach ($list AS $key => $value) {
        $list[$key]['surplus_amount'] = price_format(abs($value['amount']), false);
        $list[$key]['add_date']       = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
        $list[$key]['process_type']   = $value['process_type'] ? '充值' : '提现';
    }

    $arr = array(
        'list'      => $list,
        'filter'    => $filter,
        'condition' => $condition
    );

    return $arr;
}


//清除对数据库的恶意代码输入
function sanitize_input_data($input_data)
{  
    $input_data = trim(htmlentities(strip_tags($input_data,",")));  
    if (get_magic_quotes_gpc())
        $input_data = stripslashes($input_data);
    $input_data = mysql_real_escape_string($input_data);  
    return $input_data;  
}  

/**
 * 短信记录
 */
function sms_history()
{
    // 通用参数
    $filter['page_size']    = empty($_REQUEST['page_size']) ? 20 : intval($_REQUEST['page_size']);
    $filter['current_page'] = empty($_REQUEST['page_no'])   ? 1  : intval($_REQUEST['page_no']);

    $filter['page_size']    = $filter['page_size']    ?: 20;
    $filter['current_page'] = $filter['current_page'] ?: 1;

    // 查询参数
    $filter['start']    = empty($_REQUEST['start'])    ? '' : strtotime($_REQUEST['start']);
    $filter['end']      = empty($_REQUEST['end'])      ? '' : strtotime($_REQUEST['end']);
    $filter['admin_id'] = empty($_REQUEST['admin_id']) ? '' : intval($_REQUEST['admin_id']);
    $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : mysql_real_escape_string($_REQUEST['keywords']);

    $filter['start'] = $filter['start'] ? $filter['start'] : 0;
    $filter['end']   = $filter['end']   ? $filter['end']   : 0;

    $filter['admin_id'] = $filter['admin_id'] ? $filter['admin_id'] : '';
    $filter['keywords'] = $filter['keywords'] ? $filter['keywords'] : '';

    $condition = array();
    foreach ($_REQUEST as $key=>$val){
        if (!in_array($key,array('act','page_size','page_no')) && !empty($val)) {
            $condition[] = "$key=$val";
        }
    }
    $condition = '&'.implode('&', $condition);

    $where = 1;

    if (admin_priv('sms_history_all', '', false)) {
        $where .= '';
    } elseif (admin_priv('sms_history_part', '', false)) {
        $where .= " AND u.role_id={$_SESSION['role_id']} ";
    } elseif (admin_priv('sms_history_group', '', false)) {
        $where .= " AND u.group_id={$_SESSION['group_id']} ";
    } else {
        $filter['admin_id'] = $_SESSION['admin_id'];
    }

    if ($filter['admin_id']) {
        $where .= " AND s.admin_id={$filter['admin_id']} ";
    }

    if ($filter['start'] && $filter['end']) {
        $where .= " AND s.send_time BETWEEN {$filter['start']} AND {$filter['end']} ";
    }

    if ($filter['keywords']) {
        $where .= " AND s.sms_content LIKE '%{$filter['keywords']}%' ";
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('sms_log').' s LEFT JOIN '.
        $GLOBALS['ecs']->table('admin_user')." u ON s.admin_id=u.user_id WHERE $where";
    $record_count = $GLOBALS['db']->getOne($sql_select);

    $page = break_pages($record_count, $filter['page_size'], $filter['current_page']);

    $sql_select = 'SELECT s.phone_num,s.sms_content,FROM_UNIXTIME(s.send_time, "%Y-%m-%d %H:%i:%s") send_time,u.user_name,s.words_num FROM '.
        $GLOBALS['ecs']->table('sms_log').' s LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' u ON u.user_id=s.admin_id WHERE '.
        "$where ORDER BY send_time DESC LIMIT ".($filter['current_page']-1)*$filter['page_size'].','.$filter['page_size'];
    $sms_list = $GLOBALS['db']->getAll($sql_select);

    $result = array(
        'outflow_list' => $sms_list,
        'filter'       => $filter,
        'page_count'   => $page['page_count'],
        'record_count' => $record_count,
        'page_size'    => $filter['page_size'],
        'page'         => $filter['current_page'],
        'page_set'     => $page['page_set'],
        'condition'    => $condition,
        'start'        => $page['start'],
        'end'          => $page['end'],
    );

    return $result;
}

/*推送黑名单（用于排除警报）*/
function push_blacklist($table_name){
    $sql_select = 'SELECT u.user_id,u.user_name,u.mobile_phone,b.from_table FROM '.$GLOBALS['ecs']->table('user_blacklist').
        ' b LEFT JOIN '.$GLOBALS['ecs']->table($table_name).' u ON u.user_id=b.user_id'.
        " WHERE b.type_id=4 AND b.status=2 AND b.ignore_status=0 AND u.is_black=3 LIMIT 1";

    return $GLOBALS['db']->getRow($sql_select);
}

/*获得通话录音收藏列表*/
function get_tape_collect($where,$order_by){
    $sql_select = 'SELECT t.favor_id,t.file_path,t.add_time,a.user_name AS admin_name,s.service_time,s.service_id,s.logbook,s.user_name,COUNT(g.favor_id) praise,t.simple_explain FROM '.
        $GLOBALS['ecs']->table('tape_favorite').' t LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' a ON t.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('service').
        ' s ON t.service_id=s.service_id LEFT JOIN '.$GLOBALS['ecs']->table('tape_grade').
        ' g ON t.favor_id=g.favor_id '.
        $where.' GROUP BY t.favor_id ORDER BY t.add_time DESC';

    $tape_collect = $GLOBALS['db']->getAll($sql_select);

    foreach($tape_collect as &$val){
        $val['add_time']     = date('Y-m-d H:i',$val['add_time']);
        $val['service_time'] = date('Y-m-d H:i',$val['service_time']);
    }

    return $tape_collect;
}

/*评论通话录音*/
function comment_tape($comment,&$res,$favor_id){
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('tape_grade').
        " WHERE favor_id=$favor_id AND grade_admin={$_SESSION['admin_id']}";
    $result = $GLOBALS['db']->getOne($sql_select);
    if(0 >= $result){
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('tape_grade').
            '(favor_id,grade_admin,comment,grade_time)VALUES('.
            "$favor_id,{$_SESSION['admin_id']},'$comment',{$_SERVER['REQUEST_TIME']})";

        $res['code']       = $GLOBALS['db']->query($sql_insert);
        $res['message']    = $res['code'] ? "评论成功" : "评论失败";
    }else{
        $res['message']    = '你已经评论过此通话录音。';
    }
    $res['table_name'] = '';    
}

function select_tape(){
    $class = isset($_REQUEST['class']) ? intval($_REQUEST['class']) : 0;
    $where = ' WHERE public=3 ';
    if($class){
        $where .=" AND class=$class";
    }

    $sql_select = "SELECT favor_id,file_path,public,class,add_time,simple_explain FROM ".$GLOBALS['ecs']->table('tape_favorite')." $where ORDER BY file_path DESC";
    $tape_collect = $GLOBALS['db']->getAll($sql_select);
    $arr = array(
        '0' => '',
        '1' => '减肥',
        '2' => '补肾',
        '3' => '三高',
    );

    if($tape_collect){
        foreach($tape_collect as &$val){
            $val['add_time'] = date('Y-m-d',$val['add_time']);
            $val['class_name'] = $arr[$val['class']];
        }
    }   

    return $tape_collect;
}

//保存未接来电到消息中心
function save_miss_call($miss_call_list){
    foreach($miss_call_list as $v){
        $issue_time = strtotime($v['activation']);
        $sql = 'SELECT weight FROM '.$GLOBALS['ecs']->table('public_notice')." WHERE issue_time=$issue_time AND notice_type=3";
        $count = $GLOBALS['db']->getOne($sql);
        if (!$count) {
            $sql = 'select u.user_name,u.user_id FROM '.$GLOBALS['ecs']->table('users')
                .' u LEFT JOIN '.$GLOBALS['ecs']->table('user_contact')
                .' c ON u.user_id=c.user_id'
                ." WHERE u.mobile_phone={$v['calla']} OR u.home_phone={$v['calla']} OR c.contact_value={$v['calla']}";
            $user_info = $GLOBALS['db']->getRow($sql);
            if ($user_info) {
                $v['calla'] = hideContact($v['calla']);
                $user_info = "{$user_info['user_id']} | {$user_info['user_name']} |";
            }else{
                $user_info = '未知 |';
            }
            $content = "$user_info 主叫 {$v['calla']} ---- 分机 {$v['callb']}";
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('public_notice').'(content,weight,notice_type,title,issue_time,role_id,admin_id,status)VALUES('
                ."'$content',{$v['id']},3,'未接来电',$issue_time,{$_SESSION['role_id']},{$_SESSION['admin_id']},0)";
            $GLOBALS['db']->query($sql_insert);
        }
    }
}
