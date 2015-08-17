<?php
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

$res = array('req_msg'=>true,'timeout'=>2000,'message'=>'','code'=>false,'response_action'=>'init_rank_points');

$sql_select = 'SELECT integral_id FROM '.$GLOBALS['ecs']->table('integral')." WHERE integral_title='积分初始化' AND platform=0 ";
$integral_id = $GLOBALS['db']->getOne($sql_select);

if($integral_id == 0){
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('integral').'(integral_title,platform)'."VALUES('积分初始化',0)";
    if($GLOBALS['db']->query($sql_insert)){
        $integral_id = $GLOBALS['db']->insert_id();
    }else{
        $res['message'] = '添加初始化积分规则失败';
        die($json->encode($res));
    }   
}

$sql_select = 'SELECT user_id,ROUND(SUM(final_amount)-SUM(shipping_fee)) AS rank_points FROM '.$GLOBALS['ecs']->table('order_info')." WHERE order_status=5 AND shipping_status=2 AND pay_status=2 GROUP BY user_id";

$rank_points_list = $GLOBALS['db']->getAll($sql_select);

$error_user_id = array(); //未能初始积分的顾客
$current_time = $_SERVER['REQUEST_TIME'];

//等级列表
$sql_select = 'SELECT rank_id,min_points,max_points FROM '.$GLOBALS['ecs']->table('user_rank');
$rank_list = $GLOBALS['db']->getAll($sql_select);

//初始积分
$sql_update_rank_points = 'UPDATE '.$GLOBALS['ecs']->table('users').' SET rank_points=';

foreach($rank_points_list as &$val){
    $result = $GLOBALS['db']->query($sql_update_rank_points.$val['rank_points']." WHERE user_id={$val['user_id']}");

    if(!$result){
        $error_user_id[] = $val['user_id'];
    }else{
        //插入积分改变日志
        $sql_insert_log = 'INSERT INTO '.$GLOBALS['ecs']->table('user_integral')
            .'(integral_id,points,user_id,confirm,confirm_time,receive_time,exchange_points)'."VALUES($integral_id,{$val['rank_points']},{$val['user_id']},1,$current_time,$current_time,{$val['rank_points']})";

        $GLOBALS['db']->query($sql_insert_log);

        //修改等级
        foreach($rank_list as $rank){
            if($rank['min_points'] <= $val['rank_points'] && $val['rank_points'] < $rank['max_points']){
                $sql_update_rank = 'UPDATE '.$GLOBALS['ecs']->table('users')." SET user_rank={$rank['rank_id']} WHERE user_id={$val['user_id']}";
                $GLOBALS['db']->query($sql_update_rank);
            }
        }
    }
}

$res['code'] = true;
$res['message'] = '初始成功';
print_r($res);
exit;
//die($json->encode($res));

?>
