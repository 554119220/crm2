<?php
/**
 * User: zhangfeng
 * Date: 12-6-12
 * Time: 下午5:33
 */

class OrderLbpOutstorageRequest
{
    /**
     * @var 订单id
     */
    private $orderId;

    /**
     * @var 包裹数
     */
    private $packageNum;

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
     * @var 发货类型
     */
    private $sendType="1";

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["order_id"]=$this->orderId;
        $apiParams["package_num"]=$this->packageNum;
        $apiParams["logistics_id"]=$this->logisticsId;
        $apiParams["waybill"]=$this->waybill;
        $apiParams["trade_no"]=$this->tradeNo;
        $apiParams["send_type"]=$this->sendType;
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
        return "360buy.order.lbp.outstorage";
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

    public function setPackageNum($packageNum)
    {
        $this->packageNum = $packageNum;
    }

    public function getPackageNum()
    {
        return $this->packageNum;
    }

    public function setSendType($sendType)
    {
        $this->sendType = $sendType;
    }

    public function getSendType()
    {
        return $this->sendType;
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