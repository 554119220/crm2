<?php
define('IN_ECS',true);

/*实时库存警报*/
function timely_stock_alarm(){
    /*分页参数*/
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_size = 9;

    $time_now = time();
    $sql_select = 'SELECT g.goods_sn,g.goods_name,SUM(quantity) AS quantity,warn_number FROM '
        .$GLOBALS['ecs']->table('stock_goods').' s LEFT JOIN '.$GLOBALS['ecs']->table('goods')
        .' g ON g.goods_sn=s.goods_sn'
        ." WHERE g.is_delete=0 AND confirm_sto_times<2 AND confirm_sto_admin<>{$_SESSION['admin_id']} GROUP BY s.goods_sn LIMIT "
        .($page-1)*$record_size.",$record_size";
    /*
        ." WHERE g.is_delete=0 AND confirm_sto_times<2 GROUP BY s.goods_sn LIMIT "
     */
    $stock_goods = $GLOBALS['db']->getAll($sql_select);
    $alarm_stock_goods = array();

    //如果库存高于安全库存，则初始库存警报的相关字段
    foreach($stock_goods as $val){
        if($val['quantity'] <= $val['warn_number']){
            if($val['quantity'] == 0){
                $alarm_stock_goods['zero'][$val['goods_sn']]['goods_sn'] = $val['goods_sn'];
                $alarm_stock_goods['zero'][$val['goods_sn']]['quantity'] = $val['quantity'];
                $alarm_stock_goods['zero'][$val['goods_sn']]['goods_name'] = $val['goods_name'];
            }else{
                $alarm_stock_goods['bit'][$val['goods_sn']]['goods_sn'] = $val['goods_sn'];
                $alarm_stock_goods['bit'][$val['goods_sn']]['quantity'] = $val['quantity'];
                $alarm_stock_goods['bit'][$val['goods_sn']]['goods_name'] = $val['goods_name'];
            }
        }else{
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods')
                .' SET alarm_sto_eve=0,confirm_sto_times=0,next_alarm_time=0'
                ." goods_sn={$val['goods_sn']}";
            $GLOBALS['db']->query($sql_update);
        }
    }

    $record_count = count($alarm_stock_goods['bit']);
    $page_count = ceil($record_count/9);
    for($i = 0 ;$i < $page_count; $i++){
        $page_arr[] = $i+1;
    }

    $filter = array(
        'page' => $page,
        'record_count' => $record_count,
        'page_arr' => $page_arr
    );

    $result['alarm_stock_goods'] = $alarm_stock_goods;
    $result['filter'] = $filter;

    return $result;
}
?>
