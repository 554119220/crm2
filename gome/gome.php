<?php


function addGemoOrderIntoDB ($start, $end, $page_no = 1) {
    $auth = include 'config.php';

    $url = 'http://api.coo8.com/ApiControl';

    $sys_param = array (
        'venderId'  => $auth['appkey'],
        'timestamp' => date('Y-m-d H:i:s'),
        'v'         => '2.0',
    );

    $sys_param['signMethod'] = 'md5';
    $sys_param['format']     = 'json';

    // 应用级参数
    $sys_param['method']    = 'coo8.orders.get';
    $sys_param['startDate'] = $start;
    $sys_param['endDate']   = $end;
    $sys_param['pageSize']  = 100;
    $sys_param['pageNo']    = $page_no;

    $sys_param['sign'] = makeSign($sys_param, $auth['secretKey']);

    $url_param = array();
    foreach ($sys_param as $key=>$val){
        $url_param[] = $key.'='.$val;
    }

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

    $data = json_decode($data, true);

    echo '<pre>';
    print_r($data);

    $now_time = time();

    $order_list = $resp['orders_get_response'];

    if (is_array($order_list) && $order_list['total_result'] > 0) {
        foreach ($order_list['orders'] as $val) {

            $order_info = array ();
            $user_info  = array ();

            /* 如果订单状态不是 等待卖家发货，则跳过该订单 */
            if ($val['status'] != 'PR') continue;

            $tmp_order_sn = number_format($val['order_id'], 0, '', '');
            // 查询临时订单表中  该订单是否已经存在
            $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('ordersyn_info').
                " WHERE order_sn='$tmp_order_sn'";

            /* 如果订单已经存在，则跳过该订单 */
            if ($GLOBALS['db']->getOne($sql)) {
                continue;
            }

            // 查询正式订单表中 该订单是否已经存在
            $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').
                " WHERE order_sn='$tmp_order_sn' OR platform_order_sn='$tmp_order_sn'";
            if ($GLOBALS['db']->getOne($sql)) {
                continue;
            }

            /* 顾客信息 */
            $user_info = array (
                'consignee' => trim($val['consignee']['name']),
                'country'   => 1,
                'tel'       => trim($val['consignee']['telephone']),
                'mobile'    => trim($val['consignee']['mobilephone']),
                'email'     => trim($val['consignee']['email']),
            );

            $pcd = getProCitDis($val['consignee']['province'], $val['consignee']['city'], $val['consignee']['county']);
            if ($pcd === false) {
                $user_info['province'] = 0;
                $user_info['city']     = 0;
                $user_info['district'] = 0;
                $user_info['address']  = $val['consignee']['province'].$val['consignee']['city'].$val['consignee']['county'].$val['consignee']['address'];
            } else {
                $user_info['province'] = $pcd['state'];
                $user_info['city']     = $pcd['city'];
                $user_info['district'] = $pcd['district'];
                $user_info['address']  = $val['consignee']['address'];
            }

            $remarks = array ();
            if (!empty($val['buyer_message'])) {
                $remarks[] = '买家留言：'.$val['consignee']['want_send_time'];
            }

            if (!empty($val['buyer_memo'])) {
                $remarks[] = '买家备注：'.$val['buyer_memo'];
            }

            if (!empty($val['seller_memo'])) {
                $remarks[] = '卖家备注：'.$val['seller_memo'];
            }

            /* 订单信息 */
            $order_info = array (
                'goods_amount'     => $val['payment'],
                'shipping_fee'     => 0,bcadd($val['post_fee'], $val['cod_fee'], 2),
                'final_amount'     => $val['payment'],
                'add_time'         => strtotime($val['order_time']),
                'confirm_time'     => strtotime($val['order_change_time']),
                'remarks'          => implode('<br>', $remarks),
                'to_seller'        => trim(strstr($val['seller_memo'], '#')),
                'inv_type'         => trim($val['consignee']['invoice_title']),
                'order_sn'         => number_format($val['order_id'], 0, '', ''),
                'team'             => 12,
                'syn_time'         => $now_time,
                'pay_id'           => 17,
                'pay_name'         => '国美在线支付',
                'platform'         => 12,
                'discount_amount'  => '',
                'discount_explain' => ''
            );

            if ($val['type'] != 'cod') {
                $region = array (
                    'state'    => $val['consignee']['province'],
                    'city'     => $val['consignee']['city'],
                    'district' => $val['consignee']['county'],
                );

                $shipping = get_shipping($region);

                $order_info['shipping_id']   = $shipping['id'];
                $order_info['shipping_name'] = $shipping['name'];
                $order_info['shipping_code'] = $shipping['code'];
            }

            /* 判断顾客是否已存在 */
            $user = userIsExist($user_info);

            if ($user) {
                /* 如果顾客已存在，将订单归到该顾客名下 */
                $user_info['user_id']   = $user['user_id'];
                $order_info['admin_id'] = $user['admin_id'];
                $order_info['platform'] = $user['role_id'];
                $order_info['group_id'] = $user['group_id'];

                /* 分配订单 */
                if ($order_info['admin_id']) {
                    $order_info['operator'] = $order_info['admin_id'];
                } else {
                    $sql = 'SELECT operator FROM '.$GLOBALS['ecs']->table('order_info').
                        " WHERE user_id={$user_info['user_id']} AND operator<>0";
                    $order_info['operator'] = $GLOBALS['db']->getOne($sql);
                }
            } else {
                /* 如果顾客不存在，将顾客信息录入数据库  */
                $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('userssyn').
                    '(user_name,home_phone,mobile_phone,email,from_where,add_time,customer_type,role_id)VALUES('.
                    "'{$user_info['consignee']}','{$user_info['tel']}','{$user_info['mobile']}','{$user_info['email']}',
                    3, {$order_info['add_time']}, 2,6)";
                $GLOBALS['db']->query($sql);

                $user_info['user_id'] = $GLOBALS['db']->insert_id();

                //$order_info['operator'] = orderAssign();
            }

            unset($user_info['aliww']);
            $goods_info = array ();
            foreach ($val['order_details']['order_detail'] as $v) {
                $goods_info[] = array (
                    'outer_iid' => $v['outId'],
                    'price'     => bcsub($v['price'],$v['part_discount_price']/$v['count'], 2),
                    'num'       => $v['count'],
                    'title'     => $v['item_name'],
                );
            }

            /* 生成订单SQL */
            $sql = createOrderSql($user_info, $order_info, $goods_info);
            if (submitSql($sql) === false) {
            } elseif ($val['seller_flag'] === 0) {
            }
        }
    }

    $page = ceil($val['total_result']/$sys_param['pageSize']);

    if ($page > 1) {
        return addGemoOrderIntoDB($start, $end, ++$page_no);
    }
}
/**
 * 生成签名
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

