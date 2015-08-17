<?php

date_default_timezone_set('Asia/Shanghai');
include 'ddClient.php';

$dd = new ddClient(2100001198);
//$dd->set_method('dangdang.orders.list.get');

$dd = new ddClient(2100001198);
$resp = $dd->execute('POST', null, 'dangdang.orders.list.get');
echo '<pre>';
//print_r($resp);
foreach ($resp['OrdersList']['OrderInfo'] as $val) {
    //$dd->set_method('dangdang.order.details.get');
    $params['o'] = $val['orderID'];
    $order_detail = $dd->execute('POST', $params, 'dangdang.order.details.get');
    print_r($order_detail);
    exit;
}

//$dd->execute('POST');
