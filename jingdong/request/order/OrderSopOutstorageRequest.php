<?php
/**
 * User: zhangfeng
 * Date: 12-6-12
 * Time: 下午6:02
 */

class OrderSopOutstorageRequest
{
    /**
     * @var 物流公司id
     */
    private $logisticsId;

    /**
     * @var 运单号
     */
    private $waybill;

    /**
     * @var 订单id
     */
    private $orderId;

    /**
     * @var 流水号
     */
    private $tradeNo;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["order_id"]=$this->orderId;
        $apiParams["logistics_id"]=$this->logisticsId;
        $apiParams["waybill"]=$this->waybill;
        $apiParams["trade_no"]=$this->tradeNo;
        ksort($apiParams);
        return json_encode($apiParams);
    }

    /**
     *
     * 获取方法名称
     * @return string
     */
    public function getApiMethod()
    {
        return "360buy.order.sop.outstorage";
    }

    /**
     * @param  $logisticsId
     */
    public function setLogisticsId($logisticsId)
    {
        $this->logisticsId = $logisticsId;
    }

    /**
     * @return
     */
    public function getLogisticsId()
    {
        return $this->logisticsId;
    }

    /**
     * @param  $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param  $tradeNo
     */
    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
    }

    /**
     * @return
     */
    public function getTradeNo()
    {
        return $this->tradeNo;
    }

    /**
     * @param  $waybill
     */
    public function setWaybill($waybill)
    {
        $this->waybill = $waybill;
    }

    /**
     * @return
     */
    public function getWaybill()
    {
        return $this->waybill;
    }


}