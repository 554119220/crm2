<?php
/**
 * Class organizeSales
 * @author Nixus Su
 */
class organizeSales
{
    private $insertFuncArr = array('t'=>'insertMonthData', 'd'=>'insertDaysData');

    /**
     * queryOrderGoods
     * @return array goods sales
     * @author Nixus Su
     **/
    private function queryOrderGoods($start_time,$end_time,$admin_list)
    {
        $sql_select = 'SELECT SUM(g.goods_number) goods_number,SUM(g.goods_price*goods_number) goods_amount,g.goods_sn,g.goods_price,'.
            'FROM_UNIXTIME(i.add_time,"%Y-%m-%d")add_time,i.platform,i.group_id,i.admin_id FROM '.$GLOBALS['ecs']->table('order_info').
            ' i,'.$GLOBALS['ecs']->table('order_goods').' g WHERE i.order_id=g.order_id AND i.order_status IN (1,5) AND i.shipping_status<3'.
            " AND i.platform>0 AND i.add_time BETWEEN $start_time AND $end_time GROUP BY g.goods_sn $admin_list";
        $result = $GLOBALS['db']->getAll($sql_select);

        if (empty($result)) {
            return false;
        }

        return $result;
    }

    /**
     * monthStats
     * @return void
     * @author Nixus Su
     **/
    public function productStats($period,$admin_list = '')
    {
        // 以最早时间为起点
        if (empty($admin_list)) {
            $begining = $this->$period();
        } else {
            $begining = $this->ts();
        }

        if ($begining === false) {
            $begining = $this->queryEarliestOrderTime();
        }

        $end = strtotime(date("Y-m-$period 23:59:59", $begining));
        do {
            $result = $this->queryOrderGoods($begining, $end, $admin_list);

            $begining = $end + 1;
            $end = strtotime(date("Y-m-$period 23:59:59", $begining));

            if (!$result) {
                continue;
            }

            if (empty($admin_list)) {
                $insertFunc = $this->insertFuncArr[$period];
            } elseif ('t' == $period) {
                $insertFunc = 'insertMonthServicerData';
            }

            $this->$insertFunc($result);

        } while ($begining < time());
    }

    /**
     * insertMonthServicerData
     * @return void
     * @author Nixus Su
     **/
    private function insertMonthServicerData($data)
    {
        foreach ($data as $val){
            $sql_insert = 'REPLACE INTO `crm_service_sales`(the_month,goods_sn,goods_amount,goods_number,role_id,group_id,admin_id)VALUES('.
                "'{$val['add_time']}','{$val['goods_sn']}',{$val['goods_amount']},{$val['goods_number']},{$val['platform']},{$val['group_id']},{$val['admin_id']})";
            $GLOBALS['db']->query($sql_insert);
        }
    }

    /**
     * insertMonthData
     * @return void
     * @author Nixus Su
     **/
    private function insertMonthData($data)
    {
        foreach ($data as $val){
            $sql_insert = 'REPLACE INTO '.$GLOBALS['ecs']->table('sales_month').'(the_month,goods_sn,goods_amount,goods_number)VALUES('.
                "'{$val['add_time']}','{$val['goods_sn']}',{$val['goods_amount']},{$val['goods_number']})";
            $GLOBALS['db']->query($sql_insert);
        }
    }

    /**
     * insertDaysData 
     * @return void
     * @author Nixus Su
     **/
    private function insertDaysData ($data)
    {
        foreach ($data as $val){
            $sql_insert = 'REPLACE INTO '.$GLOBALS['ecs']->table('sales_day').'(the_day,goods_sn,goods_amount,goods_number)VALUES('.
                "'{$val['add_time']}','{$val['goods_sn']}',{$val['goods_amount']},{$val['goods_number']})";
            $GLOBALS['db']->query($sql_insert);
        }
    }

    /**
     * queryEarliestOrderTime
     * @return timestamp
     * @author Nixus Su
     **/
    private function queryEarliestOrderTime()
    {
        // 获取最早的订单的时间
        $sql_select = 'SELECT MIN(add_time) add_time FROM '.$GLOBALS['ecs']->table('order_info');
        $result = $GLOBALS['db']->getOne($sql_select);

        return $result;
    }

    /**
     * queryLastRecordTime
     * @return timestamp
     * @author Nixus Su
     **/
    private function t()
    {
        $sql_select = 'SELECT MAX(the_month) add_time FROM '.$GLOBALS['ecs']->table('sales_month');
        $result = $GLOBALS['db']->getOne($sql_select);
        if (empty($result)) {
            return false;
        }

        // 前移2个月
        $the_month = date('Y-m', strtotime($result) - 60*3600*24);

        return strtotime($the_month);
    }

    /**
     * queryLastRecordDay
     * @return timestamp
     * @author Nixus Su
     **/
    private function d()
    {
        $sql_select = 'SELECT MAX(the_day) add_time FROM '.$GLOBALS['ecs']->table('sales_day');
        $result = $GLOBALS['db']->getOne($sql_select);
        if (empty($result)) {
            return false;
        }

        // 前移60天
        $the_day = date('Y-m-d', strtotime($result) - 60*24*3600);
        return strtotime($the_day);
    }

    /**
     * ts
     * @return timestamp
     * @author Nixus Su
     **/
    private function ts()
    {
        $sql_select = 'SELECT MAX(the_month) add_time FROM '.$GLOBALS['ecs']->table('service_sales');
        $result = $GLOBALS['db']->getOne($sql_select);
        if (empty($result)) {
            return false;
        }

        // 前移60天
        $the_month = date('Y-m-d', strtotime($result) - 30*24*3600);
        return strtotime($the_month);
    }
}
