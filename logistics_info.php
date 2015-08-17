<?php
/**
 * 查询物流信息
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($id = intval($_REQUEST['id'])) {
    $sql_select = 'SELECT consignee,mobile,tel,tracking_sn,shipping_code,shipping_name FROM '.
        $GLOBALS['ecs']->table('order_info')." WHERE order_id=$id";
    $order_info = $GLOBALS['db']->getRow($sql_select);

    // 快递公司code映射
    $shipping_code = array (
        'ems'         => 'ems',
        'ems2'        => 'ems',
        'sto_express' => 'zjs',
        'sto_nopay'   => 'zjs',
        'zto'         => 'zhongtong',
        'sto'         => 'shentong',
        'yto'         => 'yuantong',
        'yto_no_pay'  => 'yuantong',
        'sf'          => 'shunfeng',
        'emssn'       => 'ems',
        'sf2'         => 'shunfeng',
        'yunda'       => 'yunda',
        'postb'       => 'bgpyghx',
    );

    $logistics_code = $shipping_code[$order_info['shipping_code']] ? $shipping_code[$order_info['shipping_code']] : $order_info['shipping_code'];
    $aikuaidi_key = '1766788d664b4322abc0e7b4955832d9';

    $logistics_url = "http://www.aikuaidi.cn/rest/?key=$aikuaidi_key&order={$order_info['tracking_sn']}&id=$logistics_code&ord=asc&show=html";

    $logistics_info = file_get_contents($logistics_url);
    //var_dump($logistics_info);

    //$logistics_info = iconv('gb2312', 'UTF-8', $logistics_info);

    echo <<<EOF
        <span>收货人：<strong>{$order_info['consignee']}</strong></span>
        <span>配送：<strong>{$order_info['shipping_name']}</strong></span>
        <span>运单号：<strong>{$order_info['tracking_sn']}</strong></span><br><br>
EOF;
        //<span>联系电话：<strong>{$order_info['mobile']} // {$order_info['tel']}</strong></span>
    echo $logistics_info;

    echo "<br><br><a href='$logistics_url' target='_self'>点我点我</a>";
    //exit;
}
