<?php 
define('IN_ECS',true);
require(dirname(__FILE__) . '/includes/init.php');
$action = isset($_REQUEST['act']) ? mysql_real_escape_string($_REQUEST['act']) : 'update';

if($action == 'update'){
    $smarty->display('upd_order_platform.htm');
}
/*修改订单平台*/
elseif($action == 'update_done'){
    $order_sn = isset($_REQUEST['order_sn']) ? trim(mysql_real_escape_string($_REQUEST['order_sn'])) : 0;

    $res = array(
        'req_msg' => true,
        'timeout' => 3000,
        'code'    => 0,
        'message' => ''
    );

    if(admin_priv('upd_order_platform','',false) || admin_priv('all','',false)){
        if($order_sn){
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_info').
                " WHERE (order_sn='$order_sn' OR platform_order_sn='$order_sn') AND platform=25";
            $result = $GLOBALS['db']->getOne($sql_select);

            if($result){
                $res['message'] = "已经修改，无需重复操作";
            }else{
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('order_info').
                    " SET platform=25 WHERE order_sn='$order_sn' OR platform_order_sn='$order_sn'";
                $res['code'] = $GLOBALS['db']->query($sql_update);
                $res['rows'] = $GLOBALS['db']->affected_rows();
                $res['message'] = $res['rows'] ? '修改成功' : '修改失败，请确定是否正确填写订单编号';
            }   
        }else{
            $res['message'] = '修改失败，请确定是否正确填写订单编号';  
        }
    }else{
        $res['message'] = '修改失败，你没有此权限';  
    }

    die($json->encode($res));
}


?>
