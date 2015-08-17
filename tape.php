<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

$sql = "SELECT u.user_id FROM ".$GLOBALS['ecs']->table('users').
  " u LEFT JOIN ".$GLOBALS['ecs']->table('order_info')." o ON u.user_id=o.user_id "
  ."LEFT JOIN ".$GLOBALS['ecs']->table('order_goods').
  " g ON o.order_id=g.order_id WHERE u.admin_id=541 AND g.goods_sn IN('TC001','10050071','1001','10010141','10150012','10160012','10170033','1221','10010261','10050371','10070093','10110051','10140011','10250043','10180013','10050051','10070263','10250033','1206')
GROUP BY u.user_id";
$res = $GLOBALS['db']->getAll($sql);
foreach ($res as $k=>$v) {
    if ($k<437) {
        $sql = 'UPDATE '.$GLOBALS['']
    }
}
print_r($res);exit;
?>
