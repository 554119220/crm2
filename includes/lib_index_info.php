<?php
define('IN_ECS', true);

if (1 != $_SESSION['admin_id']) {
    error_reporting(0);
}

//获取在首页报警的库存商品
function get_index_alarm_stock(){
    $stock_alarm = array();

    //分页参数
    $page          = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1 ;
    $recorder_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page']) : 5;

    $sql = 'SELECT sg.goods_sn,g.goods_name,g.warn_number,g.goods_id,SUM(sg.quantity) quantity,sg.status FROM '
        .$GLOBALS['ecs']->table('goods').' g, '
        .$GLOBALS['ecs']->table('stock_goods').' sg '
        .' WHERE g.goods_sn=sg.goods_sn AND g.is_delete=0 AND sg.quantity>0 GROUP BY sg.goods_sn  ';

    $unlimit        = $GLOBALS['db']->getAll($sql.'HAVING quantity<=warn_number ORDER BY SUM(sg.quantity) DESC');

    $hot_sale_goods = get_hot_sale();

    if(!$unlimit){
        $unlimit = $GLOBALS['db']->getAll($sql.'HAVING quantity<800 ORDER BY SUM(sg.quantity) DESC');
    }

    foreach($unlimit as &$um){
        foreach($hot_sale_goods as $hot){
            if($um['goods_sn'] == $hot['goods_sn']){
                $um['sales_num'] = $hot['goods_number'];
            }
        }

        if(!$um['sales_num']){
            $um['sales_num'] = 0;
        }
    }

    //按本月销量排行
    foreach($unlimit as $key=>&$row){
        $sales_num[$key] = $row['sales_num'];
    }

    if($unlimit){
        array_multisort($sales_num, SORT_DESC,$unlimit);
    }

    $recorder_count = count($unlimit);
    $page_size      = ceil($recorder_count/$recorder_size);
    $page           = $page > $page_size ? 1 : $page;

    for($i = ($page-1)*$recorder_size,$j = 0; $j < $recorder_size; $i++){
        if($unlimit[$i]){
            $stock_alarm['stock_alarm'][] = $unlimit[$i];
            $goods_sn_list[]              = $unlimit[$i]['goods_sn'];
            $j++;
        }else{
            break;
        }
    }

    /*是还是已经订货*/
    if($goods_sn_list){
        $goods_sn_str = implode("','",$goods_sn_list);
        $sql_select   = 'SELECT goods_sn,status FROM '.$GLOBALS['ecs']->table('order_sheet').
            " WHERE goods_sn IN('".$goods_sn_str."') AND status<2 GROUP BY goods_sn";

        $order_sheet_list = $GLOBALS['db']->getAll($sql_select);

        foreach($stock_alarm['stock_alarm'] as &$stock){
            foreach($order_sheet_list as $key=>$sheet){
                if($sheet['goods_sn'] == $stock['goods_sn']){
                    $stock['order_sheet_status'] = $sheet['status']; 
                    unset($order_sheet_list[$key]);
                } 
            }
        }
    }

    $list_page = array();
    for($i = 1; $i <= $page_size; $i++){
        $list_page[] = $i;
    }

    $filter = array(
        'page'          => $page,
        'recorder_size' => $recorder_size,
        'list_page'     => $list_page,
        'end'           => $page_size,
        'recorder'      => $recorder,
        'action'        => "getIndexAlarm($page,$recorder_size)",
    );

    $stock_alarm['filter'] = $filter;
    return $stock_alarm;
}

//销售情况
function get_nature_stats(){
    $admin_id = $_SESSION['admin_id'];
    $group_id = $_SESSION['group_id'];
    $role_id  = $_SESSION['role_id'];

    $ranklist_date = get_ranklist_date();
    extract($ranklist_date);

    $statistics = sales_stats($date_start,$date_end);
    $statistics['service_ranklist'] = stats_service($date_start,$date_end);

    //权限判断
    $pwd_info = authority_judge();

    //当前员工的各指标排行
    if(!admin_priv('service_all_mgr','',false)){
        if($pwd_info['ordinary_employee'] == true){
            $my_ranking = array(
                'own_ranking'       => 0,
                'final_amount'      => 0,
                'order_num'         => 0,
                'own_group_ranking' => 0,
                'num_service'       => 0
            );
        }

        $person_ranklist_total = count($statistics['person_ranklist']); 
        for($i = 0; $i < $person_ranklist_total; $i++){
            if($admin_id == $statistics['person_ranklist'][$i]['admin_id']){
                $my_ranking['own_ranking'] = $i+1;
                $my_ranking['final_amount'] = $statistics['person_ranklist'][$i]['final_amount'];
                $my_ranking['order_num'] = $statistics['person_ranklist'][$i]['num'];
                break;
            }
        }

        $group_ranklist_total = count($statistics['group_ranklist']);
        for($i = 0; $i < $group_ranklist_total; $i++){
            if($group_id == $statistics['group_ranklist'][$i]['group_id']){
                $my_ranking['own_group_ranking'] = $i+1;
                break;
            }
        }

        $role_ranklist_total = count($statistics['role_ranklist']);
        for($i = 0; $i < $role_ranklist_total; $i++){
            if($role_id == $statistics['role_ranklist'][$i]['platform']){
                $my_ranking['own_role_ranking'] = $i+1;
                break;
            }
        }

        $service_ranklist_total = count($statistics['service_ranklist']);
        for($i = 0; $i < $service_ranklist_total; $i++){
            if($admin_id == $statistics['service_ranklist'][$i]['admin_id']){
                $my_ranking['num_service'] = $statistics['service_ranklist'][$i]['num_service'];
                break;
            }
        }

        foreach($my_ranking as $key=>&$val){
            if($val == ''){
                $my_ranking[$key] = 0;
            }
        }

        $cn_key = array(
            '个人销量排行',
            '订单总金额',
            '订单数',
            '团队排行',
            '服务记录'
        );

        $my_ranking = array_combine($cn_key,$my_ranking);
        $stats_info['my_ranking'] = $my_ranking;
    }

    $stats_info['statistics']  = $statistics;
    $stats_info['pwd_info']    = $pwd_info;
    $stats_info['date_status'] = $_REQUEST['date_status'];

    return $stats_info;
}

//统计和排名
function sales_stats ($start,$end) {
    $pwd_info = authority_judge();

    if($pwd_info['company_mgr']){
        $platform_list = platform_list(); 
    }

    $tidy_date = tidysql();
    extract($tidy_date);

    //部门排行
    if($pwd_info['company_mgr'] || $pwd_info['statistic_part_mgr']){
        $sql_role_ranklist = " GROUP BY platform ";
        $sales['role_ranklist'] = get_ranklist($sql_role_ranklist,'role_ranklist',$start,$end);
    }

    //团队排行
    if($pwd_info['statistic_part_mgr']){
        $sql_group_ranklist = " AND platform={$_SESSION['role_id']}";
    }

    $sales['group_ranklist'] = get_ranklist($sql_group_ranklist,'group_ranklist',$start,$end);

    //成交订单排行
    if ($pwd_info['statistic_part_mgr']) {
        $sql_finish_order_ranklist = " AND platform={$_SESSION['role_id']} ";
    }elseif($pwd_info['statistic_group_mgr']){
        $sql_finish_order_ranklist = " AND platform={$_SESSION['role_id']} AND group_id={$_SESSION['group_id']} ";
    }

    $sales['finish_order_ranklist'] = get_ranklist($sql_finish_order_ranklist,'finish_order_ranklist',$start,$end);

    //个人排行
    $sales['person_ranklist'] = get_ranklist($sql_person_ranklist,'personal_ranklist',$start,$end);

    return $sales;
}

//服务量排行
function stats_service($start,$end) {
    $admin_id = $_SESSION['admin_id'];
    $group_id = $_SESSION['group_id'];
    $role_id  = $_SESSION['role_id'];

    $sql_select = 'SELECT s.admin_id,a.user_name,COUNT(*) AS num_service FROM '.
        $GLOBALS['ecs']->table('service').' s LEFT JOIN '.
        $GLOBALS['ecs']->table('admin_user').' a ON a.user_id=s.admin_id'.
        " WHERE a.status>0 AND service_time BETWEEN $start AND $end AND valid=1 ";
    $order_by = ' ORDER BY num_service DESC';

    $role_id_str = get_role_str();

    $platform_condition = $role_id_str ? " s.platform IN($role_id_str) " : " s.platform=$role_id";

    if(admin_priv('service_all_mgr','',false) || admin_priv('all','',false)){
        $sql_select .= " GROUP BY admin_id $order_by";
    }elseif(admin_priv('service_part_mgr','',false)){
        $sql_select .= " AND $platform_condition GROUP BY admin_id $order_by";
    }else{
        $sql_select .= " AND $platform_condition GROUP BY admin_id $order_by";
    }

    return $GLOBALS['db']->getAll($sql_select);
}

//公告
function public_notice(){
    $pwd_info = authority_judge();
    $sql_select = 'SELECT notice_id,notice_type,title,a.user_name AS writed_name,issue_time FROM '
        .$GLOBALS['ecs']->table('public_notice').' AS p LEFT JOIN '
        .$GLOBALS['ecs']->table('admin_user').' AS a ON p.writed_id=a.user_id WHERE 1 ';

    if($pwd_info['company_mgr'] || admin_priv('all','',false)){

    }elseif($pwd_info['statistic_part_mgr']){
        $where = "AND  p.role_id={$_SESSION['role_id']}";
    }elseif($pwd_info['statistic_group_mgr']){
        $where = "AND  CONCAT(p.role_id,p.group_id,p.admin_id)IN("
            ."'{$_SESSION['role_id']}{$_SESSION['group_id']}0','{$_SESSION['role_id']}00','000')";
    }else{
        $where = "AND  CONCAT(p.role_id,p.group_id,p.admin_id)IN("
            ."'{$_SESSION['role_id']}{$_SESSION['group_id']}0','{$_SESSION['role_id']}{$_SESSION['group_id']}{$_SESSION['admin_id']}','{$_SESSION['role_id']}00','000')";
    }

    $where .= ' AND p.remind_time BETWEEN '.strtotime(date('Y-m-d 0:0:0',time())).' AND '.strtotime(date('Y-m-d 23:59:59'),time());

    $result =  $GLOBALS['db']->getAll($sql_select.$where." ORDER BY weight DESC, issue_time DESC");

    foreach($result as &$val){
        $val['issue_time'] = date('Y-m-d H:i',$val['issue_time']);
    }
    return $result;
}

//权限判断
function authority_judge(){
    $pwd_info = array();
    if(admin_priv('statistic_all_mgr','',false) || admin_priv('all','',false)){
        $pwd_info['company_mgr'] = $pwd_info['statistic_manager_rule'] = true;
    }elseif(admin_priv('statistic_part_mgr','',false)){
        $pwd_info['statistic_part_mgr'] = $pwd_info['statistic_manager_rule'] = true;
    }elseif(admin_priv('statistic_group_mgr','',false)){
        $pwd_info['statistic_group_mgr'] = true;
    }else{
        $pwd_info['ordinary_employee'] = true;
    }

    return $pwd_info;
}

//近一个月顾客记念日
function get_commemoration(){

    $month         = date('n',time());
    $page          = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $recorder_size = isset($_REQUEST['recorder_size']) ? $_REQUEST['recorder_size'] : 7;

    if(!admin_priv('all','',false)){
        $where = " AND u.admin_id={$_SESSION['admin_id']}";
    }

    $sql_select = "SELECT '生日' AS subject,u.user_id,u.user_name,rank_points,birthday,SUM(final_amount) AS sale_amount,COUNT(*) AS order_count FROM ".$GLOBALS['ecs']->table('users')
        .' AS u LEFT JOIN '.$GLOBALS['ecs']->table('order_info').' AS o ON u.user_id=o.user_id'
        ." WHERE $month=MONTH(birthday) $where AND is_black=0"
        .' AND DATE(birthday)>='.date('d',$_SERVER['REQUEST_TIME'])
        .' GROUP BY u.user_id ORDER BY birthday ASC ';

    $recorder_count = count($GLOBALS['db']->getAll($sql_select));
    $page_size      = ceil($recorder_count/$recorder_size);


    $list_page = array (1,2,3,4,5,6,7);
    if ($page > 4) {
        foreach ($list_page as &$val) {
            $val += $page -4;
        }
    }

    if (end($list_page) > $page_size) {
        $list_page = array ();
        for ($i = 7; $i >= 0; $i--) {
            if ($page_size - $i > 0) {
                $list_page[] = $page_size - $i;
            }
        }
    }

    $page = $page > $page_size ? 1 : $page;
    $filter = array(
        'page'          => $page,
        'recorder_size' => $recorder_size,
        'list_page'     => $list_page,
        'end'           => $page_size,
    );

    $sql_select .= ' LIMIT '.($page-1)*$recorder_size.','.$recorder_size;
    $commemoration['commemoration_list'] = $GLOBALS['db']->getAll($sql_select);
    $commemoration['filter'] = $filter; 

    return $commemoration;
}


//返回排行数据
function get_ranklist($sql_sub,$ranklist_name,$start,$end){
    $sql = 'SELECT SUM(final_amount) final_amount,COUNT(*) num,admin_id,admin_name,platform,group_id FROM '.
        $GLOBALS['ecs']->table('order_info').
        ' WHERE order_status IN (1,5) AND shipping_status<>3 AND order_type IN(4,5,6) '.
        " AND add_time BETWEEN $start AND $end ";

    $order_by = isset($_REQUEST['order_by']) ? mysql_real_escape_string($_REQUEST['order_by']): ' ORDER BY final_amount DESC';

    if(!$platform_list){
        $platform_list = get_index_role();
    }

    switch($ranklist_name){
    case 'role_ranklist' :
        $ranklist = $GLOBALS['db']->getAll($sql.$sql_sub.$order_by);

        foreach($ranklist as &$sale_platform){
            foreach($platform_list as $platform){
                if($sale_platform['platform'] == $platform['role_id']){
                    $sale_platform['role_name'] = $platform['role_name'];
                }
            }
        }
        break;
    case 'group_ranklist' :
        $group_list = get_index_group();
        $ranklist = $GLOBALS['db']->getAll($sql.$sql_sub.$order_by);

        foreach($ranklist as $key=>&$sale_group){
            foreach($group_list as $group){
                if (!$sale_group['final_amount']) {
                    unset($ranklist[$key]);
                }

                if($sale_group['group_id'] == $group['group_id']){
                    $sale_group['group_name'] = $group['group_name'];
                }
            }
        }
        break;
    case 'personal_ranklist' :
        $ranklist = $GLOBALS['db']->getAll($sql.$sql_sub.$order_by);
        break;
    case 'finish_order_ranklist' :
        $ranklist = $GLOBALS['db']->getAll($sql.$sql_sub);
        if($_REQUEST['company_mgr']){
            foreach($ranklist as $key=>&$finish_order){
                foreach($platform_list as $platform){
                    if(!$finish_order['final_amount']){
                       unset($finish_order[$key]); 
                    }

                    if($platform['role_id'] == $finish_order['platform']){
                        $finish_order['role_name'] = $platform['role_name'];
                    }
                }
            }
        }else{
            foreach($ranklist as $key=>&$finish_order){
                if(empty($finish_order['final_amount'])){
                    unset($ranklist[$key]); 
                }
            }
        }
        break;
    }
    return $ranklist;
}

//返回统计时期
function get_ranklist_date(){
    $_REQUEST['date_status'] = isset($_REQUEST['date_status']) ? $_REQUEST['date_status'] : 1;

    if(!$_REQUEST['date_status']){
        $date_start = strtotime(date('Y-m-01 00:00:00', $_SERVER['REQUEST_TIME'])); // 本月初
        $date_end   = strtotime(date('Y-m-t 23:59:59', $_SERVER['REQUEST_TIME'])); // 本月末
    }elseif($_REQUEST['date_status'] == 1){
        $date_start = strtotime(date('Y-m-d 00:00:00',$_SERVER['REQUEST_TIME'])); //今天
        $date_end   = strtotime(date('Y-m-d 23:59:59',$_SERVER['REQUEST_TIME']));
    }elseif ($_REQUEST['date_status'] == 2) {
        $date_start = strtotime(date('Y-m-d 00:00:00',$_SERVER['REQUEST_TIME']))-24*3600;  //昨天
        $date_end   = strtotime(date('Y-m-d 23:59:59',$_SERVER['REQUEST_TIME']))-24*3600;
    }

    return array('date_start'=>$date_start,'date_end'=>$date_end);
}

function tidysql($sql_ranklist_name = ''){
    $admin_id  = $_SESSION['admin_id'];
    $group_id  = $_SESSION['group_id'];
    $role_id   = $_SESSION['role_id'];
    $tidy_date = array();
    $pwd_info  = authority_judge();

    if($pwd_info['company_mgr']){
        $tidy_date['sql_person_ranklist']       = " GROUP BY admin_id $order_by";
        $tidy_date['sql_group_ranklist']        = " AND group_id<>0 GROUP BY group_id";
        $tidy_date['sql_finish_order_ranklist'] = ' GROUP BY platform ORDER BY num DESC';
        $_REQUEST['company_mgr']                = true;
    }elseif ($pwd_info['statistic_part_mgr']){
        $role_id_str = get_role_str();

        $tidy_date['sql_person_ranklist'] = $role_id_str ? " AND platform IN($role_id_str) GROUP BY admin_id " : " AND platform=$role_id GROUP BY admin_id";
        $tidy_date['sql_group_ranklist'] = $role_id_str ? " AND platform IN($role_id_str) GROUP BY group_id " : " AND platform=$role_id GROUP BY group_id";
        $tidy_date['sql_finish_order_ranklist'] = ' GROUP BY admin_id ORDER BY num DESC';
    }else{
        $tidy_date['sql_person_ranklist']       = " AND group_id=$group_id GROUP BY admin_id ";
        $tidy_date['sql_group_ranklist']        = " AND group_id=$group_id GROUP BY group_id "; 
        $tidy_date['sql_finish_order_ranklist'] = " AND group_id=$group_id GROUP BY admin_id ORDER BY num DESC";
    }

    $tidy_date['sql_role_ranklist'] = " GROUP BY platform ";

    if($sql_ranklist_name != ''){
        return $tidy_date[$sql_ranklist_name];
    }
    else{
        return $tidy_date;
    }
}

//热销产品
function get_hot_sale($start_time = 0,$end_time = 0){
    //默认本月
    if(!$start_time && !$end_time){
        $start_time = strtotime(date('Y-m-1 00:00:00',time()));
        $end_time   = strtotime(date('Y-m-t 23:59:59',time()));
    }

    $where = " WHERE i.add_time>=$start_time AND i.add_time<=$end_time AND i.order_status IN (1,5) AND shipping_status IN(1,2,0)";

    $goods_list = get_goods_num($where); 

    return $goods_list;
}

//统计商品数量
function get_goods_num($where){
    /*统计非套餐商品*/
    $sql_select = 'SELECT g.goods_sn, SUM(g.goods_number) goods_number FROM '
        .$GLOBALS['ecs']->table('order_goods').' g LEFT JOIN '
        .$GLOBALS['ecs']->table('order_info')
        ." i ON g.order_id=i.order_id $where AND "
        ." g.goods_sn NOT LIKE '%\_%' GROUP BY g.goods_sn ORDER BY SUM(g.goods_number) DESC";
    $single_goods = $GLOBALS['db']->getAll($sql_select);

    /*统计套餐商品*/
    $sql_select = 'SELECT g.goods_sn, g.goods_number FROM '
        .$GLOBALS['ecs']->table('order_goods')
        .' g LEFT JOIN '.$GLOBALS['ecs']->table('order_info')
        ." i ON g.order_id=i.order_id $where AND "
        .' g.goods_sn LIKE "%\_%" ';
    $package_goods = $GLOBALS['db']->getAll($sql_select);

    /*统计套餐数量*/
    $package_sn = array();
    foreach ($package_goods as $v){
        $package_sn[] = $v['goods_sn'];
        $packing_goods[$v['goods_sn']] += $v['goods_number'];
    }

    /*统计套餐中商品数量*/
    $package = array();
    $package_sn = implode("','", $package_sn);
    $sql_select = 'SELECT p.packing_desc,pg.goods_id,g.goods_sn,pg.num FROM '
        .$GLOBALS['ecs']->table('packing').' p LEFT JOIN '
        .$GLOBALS['ecs']->table('packing_goods')
        .' pg ON pg.packing_id=p.packing_id LEFT JOIN '.$GLOBALS['ecs']->table('goods')
        ." g ON g.goods_id=pg.goods_id WHERE p.packing_desc IN ('$package_sn')";
    $package_list = $GLOBALS['db']->getAll($sql_select);

    foreach ($package_list as $val){
        $package[$val['goods_sn']] += $val['num'] * $packing_goods[$val['packing_desc']];
    }

    /*合并套餐和非套餐商品*/
    if($package){
        foreach($package as $key=>&$package_val){
            foreach($single_goods as &$single_val){
                if($key == $single_val['goods_sn']){
                    $single_val['goods_number'] += $package_val;
                    unset($package_val);
                }
            }
        }
    }

    return $single_goods;
}

// 推广活动
function index_spread_activity(){
    $start_time = strtotime(date('Y-m-1 00:00:00',$_SERVER['REQUEST_TIME']));
    $end_time   = strtotime(date('Y-m-t 23:59:59',$_SERVER['REQUEST_TIME']));

    $page          = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $recorder_size = 10;

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('spread_activity').' a,'.$GLOBALS['ecs']->table('role')
        ." r WHERE a.role_id=r.role_id AND start_time>=$start_time AND status=1";

    $recorder_count = $GLOBALS['db']->getOne($sql_select);
    $page_size      = ceil($recorder_count/$recorder_size);
    if($page_size < $page || $page == 0){
        $page = 1;
    }

    $list_page = array();

    for($i = 0; $i < $page_size; $i++){
        $list_page[] = $i+1;
    }


    $limit       = ' LIMIT '.($page-1)*$page_size.",$recorder_size";
    $sql_select = 'SELECT activity_id,r.role_name,activity_name,act_describe,start_time,end_time FROM '.$GLOBALS['ecs']->table('spread_activity').' a,'.$GLOBALS['ecs']->table('role')
        ." r WHERE a.role_id=r.role_id AND start_time>=$start_time AND status=1 ORDER BY start_time ASC $limit";

    $spread_activity_list = $GLOBALS['db']->getAll($sql_select);

    if($spread_activity_list){
        foreach ($spread_activity_list as &$val) {
            $val['start_time'] = date('m-d',$val['start_time']);
            $val['end_time']   = date('m-d',$val['end_time']);
        }
    }

    $filter = array(
        'page'          => $page,
        'list_page'     => $list_page,
        'end'           => $page_size,
        'recorder_size' => $recorder_size
    );

    return array('spread_activity_list'=>$spread_activity_list,'filter'=>$filter);
}


/*销售库存
/*库存大于前3个月的平均销量*6
/*销量警报*/
function get_sale_alarm_list(){
    $start_time    = strtotime(date('Y-m-1',strtotime("-3 month")));
    $end_time      = strtotime(date('Y-m-1',$_SERVER['REQUEST_TIME']));
    $page          = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $recorder_size = 7;

    /*获取各商品三个月的总销量*/
    $where = " WHERE i.add_time>=$start_time AND i.add_time<=$end_time AND i.order_status IN (1,5) AND shipping_status IN(1,2,0)";
    $tmp_goods_list = get_goods_num($where);

    //公式：库存>此前三个月平均销量*6
    foreach($tmp_goods_list as &$val){
        $val['three_month_sale'] = floor($val['goods_number'] / 3);
        $goods_sn_list[] = $val['goods_sn'];
    }

    if(is_array($goods_sn)){
        $goods_sn_str = implode("','",$goods_sn_list);

        $sql_select = 'SELECT goods_sn,goods_name,SUM(quantity) AS quantity FROM '
            .$GLOBALS['ecs']->table('stock_goods')
            ." WHERE goods_sn IN('$goods_sn_str') AND quantity>0 GROUP BY goods_sn";

        $goods_quantity_list = $GLOBALS['db']->getAll($sql_select);
        $goods_list          = array();

        foreach($tmp_goods_list as $val){
            foreach($goods_quantity_list as $quantity_list){
                if($val['goods_sn'] == $quantity_list['goods_sn'] && $val['three_month_sale']*6<$quantity_list['quantity']){

                    $val['goods_name'] = $quantity_list['goods_name'];
                    $val['quantity']   = $quantity_list['quantity'];
                    $goods_list[]      = $val;
                }
            }
        }

        $recorder_count = count($goods_list);
        $page_size      = ceil($recorder_count/$recorder_size);
        $page           = $page > $page_size ? 1 : $page;

        for($i = ($page-1)*$recorder_size,$j = 0; $j < $recorder_size; $i++){
            if($goods_list[$i]){
                $sale_alarm_list['sale_alarm'][] = $goods_list[$i]; 
                $j++;
            }else{
                break;
            }
        }

        $list_page = array();
        for($i = 1; $i <= $page_size; $i++){
            $list_page[] = $i;
        }

        $filter = array(
            'page'          => $page,
            'recorder_size' => $recorder_size,
            'list_page'     => $list_page,
            'end'           => $page_size,
            'recorder'      => $recorder,
            'action'        => "getSaleAlarm($page,$recorder_size)",
        );

        $sale_alarm_list['filter'] = $filter;
    }else{
        $sale_alarm_list['sale_alarm'] = '没有记录';
    }

    return $sale_alarm_list;
}

//查看今天任务
function get_task_progress(){
    $admin_tasks_list = array();
    $current_time     = date('Y-m-d',$_SERVER['REQUEST_TIME']);

    if(!admin_priv('all','',false)){
        $where = " WHERE at.admin_id={$_SESSION['admin_id']} AND FROM_UNIXTIME(at.record_time,'%Y-%m-%d')='$current_time'";

        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_task').' AS at '.$where;
        $task_num   = $GLOBALS['db']->getOne($sql_select);

        if($task_num > 0){
            $admin_tasks_list = get_admin_tasks_list($where); 
        }else{
            //分配今天任务
            $group_id  = intval($_SESSION['group_id']);
            $role_id   = intval($_SESSION['role_id']);
            $condition = " platform=$role_id ";

            if($group_id > 0){
                $condition .= " OR group_id=$group_id";
            }

            $sql_select = 'SELECT task_id FROM '.$GLOBALS['ecs']->table('tasks').
                " WHERE period_id=1 AND ($condition)";

            $tasks_list = $GLOBALS['db']->getCol($sql_select); 

            foreach($tasks_list as $val){
                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('admin_task').
                    '(task_id,admin_id,record_time)VALUES('.
                    "$val,{$_SESSION['admin_id']},{$_SERVER['REQUEST_TIME']})";
                $GLOBALS['db']->query($sql_insert);
            }

            $admin_tasks_list = get_admin_tasks_list($where);
        }

        $tasks_total = count($admin_tasks_list);

        /*重新计算任务完成进度*/
        if($tasks_total > 0){
            $start_time = strtotime(date('Y-m-d 00:00:00',$_SERVER['REQUEST_TIME']));
            $end_time   = strtotime(date('Y-m-d 23:59:59',$_SERVER['REQUEST_TIME']));
            $is_complate = true;

            foreach($admin_tasks_list as &$val){
                if($val['column_name'] == 'service'){
                    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('service').
                        " WHERE admin_id={$_SESSION['admin_id']} AND service_time>=$start_time".
                        " AND service_time<=$end_time AND valid=1 "; 
                }elseif($val['column_name'] == 'final_amount'){
                    $sql_select = 'SELECT SUM(final_amount) FROM '
                        .$GLOBALS['ecs']->table('order_info')
                        ." WHERE order_status IN (1,5) AND shipping_status<>3 AND admin_id={$_SESSION['admin_id']}"
                        ." AND add_time>=$start_time AND add_time<=$end_time"
                        .' GROUP BY admin_id';
                }
                $val['task_value'] = intval($GLOBALS['db']->getOne($sql_select));

                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_task').
                    " SET task_value={$val['task_value']} WHERE task_id={$val['task_id']}";
                $GLOBALS['db']->query($sql_update);

                if($val['task_value'] < $val['purpose_value']){
                    $is_complate = false;
                }

                $percent = ($val['task_value']/$val['purpose_value']*100);
                $percent = $percent > 0 ? $percent : 1; 
                $style = 'class="';

                if($percent >= 0){
                    $val['sts1'] = $style.'sts1"';
                }

                if($percent >= 25){
                    $val['sts2'] = $style.'sts2"';
                }

                if($percent >= 50){
                    $val['sts3'] = $style.'sts3"';
                }

                if($percent >= 75){
                    $val['sts4'] = $style.'sts4"';
                }

                if($percent == 100){
                    $val['sts5'] = $style.'sts5"';
                }
            }    
        }
    }

    return array('admin_tasks_list'=>$admin_tasks_list,'is_complate'=>$is_complate);
}

function get_admin_tasks_list($where){
    $sql_select = 'SELECT at.task_id,at.task_value,t.task_name,t.purpose_value,column_name FROM '.
        $GLOBALS['ecs']->table('admin_task').' at LEFT JOIN '.
        $GLOBALS['ecs']->table('tasks').' t ON at.task_id=t.task_id'.$where;   
    $admin_tasks_list = $GLOBALS['db']->getAll($sql_select);

    return $admin_tasks_list;
}

/*查询子部门*/
function get_role_str(){
    if($_SESSION['role_id']){
        $sql_select = 'SELECT role_describe FROM '.$GLOBALS['ecs']->table('role').
            " WHERE role_id={$_SESSION['role_id']}";
        $role_desc = $GLOBALS['db']->getOne($sql_select);
        if($role_desc){
            $sql_select = 'SELECT role_id FROM '.$GLOBALS['ecs']->table('role').
                " WHERE role_describe='{$role_desc['role_describe']}'";
            $role_id_list = $GLOBALS['db']->getCol($sql_select);

            if($role_id_list){
                $role_id_str = implode(',',$role_id_list);
            }
        }
    }

    return $role_id_str;
}

function get_index_group(){
    $sql_select = 'SELECT group_id,group_name FROM '.$GLOBALS['ecs']->table('group');
    $group_list = $GLOBALS['db']->getAll($sql_select);
    return $group_list;
}

function get_index_role(){
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').' WHERE role_type>0';
    $platform_list = $GLOBALS['db']->getAll($sql_select);
    return $platform_list;
}
