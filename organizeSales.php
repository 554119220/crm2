<?php
/**
 * Class organizeSales
 * @author Nixus Su
 */
class organizeSales
{
    private $mysqli;
    private $insertFuncArr = array('t'=>'insertMonthData', 'd'=>'insertDaysData');


    public function __construct($host, $username, $passwd, $db)
    {
        $this->mysqli = new mysqli($host, $username, $passwd, $db);
    }

    /**
     * queryOrderGoods
     * @return array goods sales
     * @author Nixus Su
     **/
    private function queryOrderGoods($start_time,$end_time)
    {
        $sql_select = 'SELECT SUM(g.goods_number) goods_number,SUM(g.goods_price*goods_number) goods_amount,g.goods_sn,'.
            'g.goods_price,FROM_UNIXTIME(i.add_time,"%Y-%m-%d") add_time FROM `crm_order_info` i,`crm_order_goods` g WHERE'.
            " i.order_id=g.order_id AND i.add_time BETWEEN $start_time AND $end_time GROUP BY g.goods_sn";
        $result = $this->mysqli->query($sql_select);

        if (!empty($result)) {
            $final = array();
            while ($row = $result->fetch_assoc()) {
                $final[] = $row;
            }
        } else {
            return false;
        }

        return $final;
    }

    /**
     * monthStats
     * @return void
     * @author Nixus Su
     **/
    public function productStats($period)
    {
        // 以最早时间为起点
        $begining = $this->$period();
        if ($begining === false) {
            $begining = $this->queryEarliestOrderTime();
        }

        $end = strtotime(date("Y-m-$period 23:59:59", $begining));
        do {
            $result = $this->queryOrderGoods($begining, $end);

            $begining = $end + 1;
            $end = strtotime(date("Y-m-$period 23:59:59", $begining));

            if (!$result) {
                continue;
            }

            $insertFunc = $this->insertFuncArr[$period];
            $this->$insertFunc($result);

        } while ($begining < time());
    }

    /**
     * insertMonthData
     * @return void
     * @author Nixus Su
     **/
    private function insertMonthData($data)
    {
        foreach ($data as $val){
            $sql_insert = 'REPLACE INTO `crm_sales_month`(the_month,goods_sn,goods_amount,goods_number)VALUES('.
                "'{$val['add_time']}','{$val['goods_sn']}',{$val['goods_amount']},{$val['goods_number']})";
            $this->mysqli->query($sql_insert);
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
            $sql_insert = 'REPLACE INTO `crm_sales_day`(the_day,goods_sn,goods_amount,goods_number)VALUES('.
                "'{$val['add_time']}','{$val['goods_sn']}',{$val['goods_amount']},{$val['goods_number']})";
            $this->mysqli->query($sql_insert);
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
        $sql_select = 'SELECT MIN(add_time) add_time FROM `crm_order_info`';
        $result = $this->mysqli->query($sql_select);
        $order_time = $result->fetch_row();

        return end($order_time);
    }

    /**
     * queryLastRecordTime
     * @return timestamp
     * @author Nixus Su
     **/
    private function t()
    {
        $sql_select = 'SELECT MAX(the_month) add_time FROM `crm_sales_month`';
        $result = $this->mysqli->query($sql_select);
        if (empty($result)) {
            return false;
        }

        $the_month = $result->fetch_row();

        // 前移2个月
        $the_month = date('Y-m', strtotime(end($the_month)) - 60*3600*24);

        return strtotime($the_month);
    }

    /**
     * queryLastRecordDay
     * @return timestamp
     * @author Nixus Su
     **/
    private function d()
    {
        $sql_select = 'SELECT MAX(the_day) add_time FROM `crm_sales_day`';
        $result = $this->mysqli->query($sql_select);
        if (empty($result)) {
            return false;
        }

        $the_day = $result->fetch_row();

        // 前移60天
        $the_day = date('Y-m-d', strtotime(end($the_day)) - 60*24*3600);
        return strtotime($the_day);
    }
}

$sales = new organizeSales('localhost','root','17kjrs365','kjrs_crm2');
$sales->productStats('t');
