<?php
define('IN_ECS', true);

require(dirname(__FILE__).'/data/config.php');

$db = new mysqli('localhost',$db_user,$db_pass,$db_name);
$db->set_charset('utf8');

date_default_timezone_set('Asia/Shanghai');

$fields = ' o.order_id,o.tracking_sn,o.shipping_code,o.consignee,o.order_sn,o.admin_id,o.shipping_time ';
$sql_select = "SELECT $fields FROM `crm_order_info` o,`crm_shipping` s WHERE o.team<>6 AND ".
    ' o.order_status=5 AND o.shipping_status=1 AND s.shipping_id=o.shipping_id AND s.shipping_type=1 ';

$end_time = strtotime(date('Y-m-d 00:00:00'));
$start_time = $end_time -8*3600*24;
$where = " AND o.province IN (110000,310000) AND o.shipping_time BETWEEN $start_time AND $end_time ORDER BY RAND() LIMIT 300";
$res = $db->query($sql_select.$where); // 发往北京上海的快递信息
$order_list_bjsh = fetch_assoc($res);

$where = " AND o.city=440100 AND o.shipping_time BETWEEN $start_time AND $end_time ORDER BY RAND() LIMIT 300";
$res = $db->query($sql_select.$where);  // 发往广州市的快递信息
$order_list_gz = fetch_assoc($res);

$end_time = time() -1*24*3600;
$start_time = $end_time -8*3600*24;
$where = " AND o.shipping_time BETWEEN $start_time AND $end_time ORDER BY RAND() LIMIT 600";
$res = $db->query($sql_select.$where); // 全国的快递信息
$order_list = fetch_assoc($res);

$order_list = array_merge($order_list, $order_list_bjsh, $order_list_gz);

if (empty($order_list)) exit;

foreach ($order_list as $val)
{
    sleep(4);
    if (!is_numeric($val['tracking_sn'])) continue;

    unset($typeCom,$typeNu);
    switch ($val['shipping_code'])
    {
    case 'sto_express':
    case 'sto_nopay':
        $typeCom = 'zhaijisong'; break;
    case 'yto':
        $typeCom = 'yuantong'; break;
    case 'yunda':
        $typeCom = 'yunda'; break;
    case 'zto':
        $typeCom = 'zhongtong'; break;
    case 'ems':
    case 'ems2':
        $typeCom = 'ems'; break;
    case 'sf':
        $typeCom = 'shunfeng'; break;
    case 'sto':
        $typeCom = 'shentong'; break;
    default : continue;
    }

    if (empty($typeCom)) {
        continue;
    }

    $typeNu  = $val['tracking_sn']; // 快递单号

    if (in_array($typeCom, array('yuantong','yunda','zhongtong')))# && date('H') < 11)
    {
        $key = '06b6d67cfed750fd';
        $url = "http://api.kuaidi100.com/api?id=$key&com=$typeCom&nu=$typeNu&show=0&muti=0&order=asc";
    }
    else
    {
        $typeCom = $typeCom == 'zhaijisong' ? 'zjs' : $typeCom;
        $key = '1766788d664b4322abc0e7b4955832d9';
        $url = "http://www.aikuaidi.cn/rest/?key=$key&order=$typeNu&id=$typeCom";
    }

    // 优先使用curl模式发送数据
    $curl = curl_init();
    curl_setopt ($curl, CURLOPT_URL, $url);
    curl_setopt ($curl, CURLOPT_HEADER,0);
    curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.32 Safari/537.36');
    curl_setopt ($curl, CURLOPT_TIMEOUT,5);
    $get_content = curl_exec($curl);
    curl_close ($curl);

    if (mb_check_encoding($get_content, 'GBK'))
    {
        $get_content = mb_convert_encoding($get_content,'UTF-8','GBK');
    }

    $express_info = json_decode($get_content,true);
    echo "\r\n",$key,"：：\r\n";
    print_r($express_info);

    $update = '';
    $title  = '';
    $status = 0;
    $nowtime = time();
    $receive_time = '';

    // kuaidi100.com
    if ($key == '06b6d67cfed750fd')
    {
        if($express_info['status'] != 1) {
            continue;
        }

        $status = $express_info['state'];
        $cont = implode(',', $express_info['data']['0']);
        switch ($express_info['state']) {
        case 2: $title = $val['order_sn'].'-疑难件';
        case 4:
            $title = empty($title) ? $val['order_sn'].'-已退回' : $title;
            $sql_insert = 'INSERT INTO `crm_admin_message` (message_id,sender_id,receiver_id,sent_time,readed,title,message)'
                ."VALUES({$val['order_id']},0,{$val['admin_id']},$nowtime,0,'$title','$cont') ON DUPLICATE KEY UPDATE ".
                " title='$title',message='$cont',readed=IF(deleted=1,0,1),sent_time=$nowtime";  // 4为设定的不再提醒
            $db->query($sql_insert);
            break;
        case 3:
            $receive_time = strtotime($express_info['data'][0]['time']);
            $update = "shipping_status=2,receive_time='$receive_time',";
            break;
        }

        $status = $express_info['state'] == 5 ? 0 : $express_info['state'];
    }

    // aikuaidi.cn
    elseif ($key == '1766788d664b4322abc0e7b4955832d9')
    {
        if ($express_info['errCode'] || !is_array($express_info['data']) || empty($express_info['data'])) {
            continue;
        }

        $cont = implode(' ', end($express_info['data']));
        switch ($express_info['status']) {
        case 6:
            $title = $val['order_sn'].'-疑难件';
            $status = 2;
            break;
        case 5:
            $title = empty($title) ? $val['order_sn'].'-拒收' : $title;
            $status = 6;
            break;
        case 7:
            $title = empty($title) ? $val['order_sn'].'-已退回' : $title;
            $status = 4;
            break;
        case 3: $status = 5; break;
        case 2: $status = 0; break;
        case 4:
            $status = 3;
            $temp = end($express_info['data']);
            $receive_time = strtotime($temp['time']);
            $update = "shipping_status=2,receive_time='$receive_time',";
            break;
        }
    }

    /** 结果说明
     * com     物流公司编号
     * nu      物流单号
     * time    每条跟踪信息的时间
     * context 每条跟踪信息的描述
     * state   快递单当前状态
     *         0:在途中
     *         1:已发货
     *         2:疑难件
     *         3:已签收
     *         4:已退货
     *         5:派件中
     *         6:拒收
     *         7:
     * status  查询结果状态
     *         0:物流单暂无结果
     *         1:查询成功
     *         2:接口出现异常
     */

    if (in_array($status,array(2,4,6)))
    {
        $sql_insert = 'INSERT INTO `crm_admin_message`(message_id,sender_id,receiver_id,sent_time,readed,title,message)'
            ."VALUES({$val['order_id']},0,{$val['admin_id']},$nowtime,0,'$title','$cont') ON DUPLICATE KEY UPDATE".
            " title='$title',message='$cont',readed=IF(deleted=1,0,1),sent_time=$nowtime";  // 4为设定的不再提醒
        $db->query($sql_insert);
    }

    $sql_update = "UPDATE `crm_order_info` SET $update exp_status=$status,".
        "exp_info='$cont' WHERE order_id={$val['order_id']}";
    if ($db->query($sql_update) && $status==3)
    {
        $sql = 'SELECT IF(shipping_status=2, 1, 0) shipping_status,order_id,user_id '.
            " FROM `crm_order_info` WHERE order_id={$val['order_id']} LIMIT 1";
        $order_result = $db->query($sql);
        $order_result = reset(fetch_assoc($order_result));

        $sql = 'UPDATE `crm_users` u, `crm_order_info` o ';
        if ($order_result['shipping_status'])
        {
            $sql .= ' SET u.number_purchased=u.number_purchased+1 ';
        }
        else
        {
            $sql .= ' SET u.number_purchased=u.number_purchased-1 ';
        }

        $sql .= " WHERE u.user_id={$order_result['user_id']} AND o.order_id={$val['order_id']} ".
            ' AND o.add_time>UNIX_TIMESTAMP("2013-02-17 18:00:00")';
        $db->query($sql);

        //start 20130813 积分
        $order_id = $val['order_id'];
        $sql_select = 'SELECT * FROM `crm_order_info`'." WHERE order_id=$order_id";
        $res = $db->query($sql_select);   //订单详情
        $order = reset(fetch_assoc($res));

        $sql_select = 'SELECT * FROM `crm_users`'." WHERE user_id={$order['user_id']}";
        $res = $db->query($sql_select);
        $user = reset(fetch_assoc($res));

        //本平台适用的消费获积分是否启用  全品牌
        $sql = 'SELECT * FROM `crm_integral` WHERE available=1 AND suit_brand=0';
        $sql_session = $sql." AND platform={$order['team']} ";
        $res = $db->query($sql_session);
        $inte_pur = reset(fetch_assoc($res));
        $is_enable_pur_all = false; 

        if($inte_pur)
        {
            //消费启用
            $points = ceil($order['goods_amount'] * $inte_pur['scale']);

            //生日获积分
            $sql_bir = $sql_session.' AND integral_way=4';
            $res = $db->query($sql_bir);
            $inte_bir = reset(fetch_assoc($res));

            if(count($inte_bir))    //生日启用
            {
                if(date('m-d') == substr($user['birthday'],5))
                {
                    $bir_points = ceil($inte_bir['scale'] * $order['goods_amount']);
                }
            }
            //所获积分
            if($points<$bir_points)
            {
                $points = $bir_points;
                $integral = $inte_bir;
            }
            else
            {
                $integral = $inte_pur;
            }
            $is_enable_pur_all = true;
        }
        //是否有存在全平台可用消费积分 全品牌
        else
        {
            $sql_global = $sql." AND platform=0 AND integral_way=1";
            $res = $db->query($sql_global);
            $inte_global = reset(fetch_assoc($res));
            $points = ceil($order['goods_amount'] * $inte_global['scale']);

            //生日获积分
            $sql_bir = $sql.' AND platform=0 AND integral_way=4';

            $res = $db->query($sql_bir);
            $inte_bir = reset(fetch_assoc($res));
            $bir_point = 0;

            if($inte_bir)    //生日启用
            {
                if(date('m-d') == substr($user['birthday'],5))
                {
                    $bir_point = ceil($inte_bir['scale'] * $order['goods_amount']);
                }
            }

            //所获积分
            if($points<$bir_point)
            {
                $points = $bir_point;
                $integral = $inte_bir;
            }
            else
            {
                $integral = $inte_global;
            }

            $is_enable_pur_all = true;
        }

        if($is_enable_pur_all)
        {
            //添加到未确认表
            $sql_insert = 'INSERT INTO `crm_integral`'.
                '(integral_id,points,haduse,source,source_id,receive_time,validity,integral_info,user_id,admin_id,exchange_points,increase_reduce,pre_points,confirm)'.
                "VALUES({$integral['integral_id']},$points,0,'order',{$order['order_id']},'$receive_time',{$integral['present_end']},0,{$order['user_id']},0,$points,1,{$user['rank_points']},0)";

            $db->query($sql_insert);
        }
        //end 20130813 积分

        // 获取会员部所有客服的user_id及user_name
        $sql_select = 'SELECT user_id FROM `crm_admin_user` WHERE role_id=9 '.
            ' AND status=1 AND stats=1 AND assign=1 ORDER BY counter ASC LIMIT 1';
        $admin_id = $db->query($sql_select);
        $admin_id = reset(fetch_assoc($admin_id));
        $admin_id = $admin_id['user_id'];

        $sql_select = 'SELECT user_id FROM `crm_admin_user` WHERE role_id NOT IN ('.OFFLINE_SALE.')';
        $admin_list = $db->query($sql_select);
        $admin_list = fetch_assoc($admin_list);
        $list = array();
        foreach ($admin_list as $a)
        {
            $list[] = $a['user_id'];
        }
        $admin_list = implode(',', $list);

        $sql_select = 'SELECT final_amount FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_id=$order_id";
        $order_amount = $GLOBALS['db']->getOne($sql_select);
        if ($order_amount < 800) {
            $sql_update = 'UPDATE '.$ecs->table('users').' u,'.$GLOBALS['ecs']->table('admin_user').
                " a SET u.admin_id=$admin_id,u.order_time=$order_time,u.admin_name=a.user_name,u.role_id=a.role_id,".
                'u.group_id=a.group_id,u.assign_time=UNIX_TIMESTAMP(),a.counter=a.counter+1 WHERE u.admin_id IN ('.
                "$admin_list) AND u.user_id={$res['user_id']} AND a.user_id=$admin_id";
        } else {
            $sql_update = 'UPDATE '.$ecs->table('users').' u,'.$GLOBALS['ecs']->table('admin_user').
                ' a SET u.admin_id=4,u.admin_name=a.user_name,u.assign_time=UNIX_TIMESTAMP(),u.group_id=a.group_id,'.
                "u.role_id=a.role_id, u.order_time=$order_time, a.counter=a.counter+1 WHERE u.admin_id IN (".
                "$admin_list) AND u.user_id={$res['user_id']} AND a.user_id=4";
        }

        $db->query($sql_update);
        update_taking_time ($order_id);

        unset($admin_list);

        $sql_select = 'SELECT rec_id,is_package FROM `crm_order_goods`'." WHERE order_id={$val['order_id']}";
        $goods_list = $db->query($sql_select);
        $goods_list = fetch_assoc($goods_list);

        foreach ($goods_list as $v)
        {
            if ($v['is_package'])
            {
                $sql_update = 'UPDATE `crm_packing` p,`crm_order_goods` o SET '.
                    "o.taking_time=o.goods_number*p.take_days*24*3600 WHERE o.rec_id={$v['rec_id']}";
                $db->query($sql_update);
            }
            else
            {
                $sql_update = 'UPDATE `crm_goods` g,`crm_order_goods` o SET '.
                    "o.taking_time=o.goods_number*g.take_days WHERE o.rec_id={$v['rec_id']}";
                $db->query($sql_update);
            }
        }
    }
}

/**
 * 将查询结果以数组形式返回
 */
function fetch_assoc ($res)
{
    $final = array ();
    while ($row = $res->fetch_assoc())
    {
        $final[] = $row;
    }

    return $final;
}

/**
 * 更新当前商品服用结束的日期
 */
function update_taking_time ($order_id)
{
    $sql_select = 'SELECT rec_id,is_package FROM '.$GLOBALS['ecs']->table('order_goods')." WHERE order_id=$order_id";
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

