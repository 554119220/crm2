<?php
/**
 * todo 修改运单号，等主干代码合并到沙箱后才能测试
 *
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 上午10:49
 */

class SopOrderWaybillUpdateRequest
{

    /**
     * @var 订单id
     */
    private $orderId;

    /**
     * @var 物流公司id
     */
    private $logisticsId;

    /**
     * @var 运单号
     */
    private $waybill;

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
        return "360buy.order.sop.waybill.update";
    }

    public function setLogisticsId($logisticsId)
    {
        $this->logisticsId = $logisticsId;
    }

    public function getLogisticsId()
    {
        return $this->logisticsId;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
    }

    public function getTradeNo()
    {
        return $this->tradeNo;
    }

    public function setWaybill($waybill)
    {
        $this->waybill = $waybill;
    }

    public function getWaybill()
    {
        return $this->waybill;
    }


}