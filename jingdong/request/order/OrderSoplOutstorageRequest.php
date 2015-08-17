<?php
/**
 * User: zhangfeng
 * Date: 12-6-12
 * Time: 下午5:56
 */

class OrderSoplOutstorageRequest
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
     * @var 发货类型
     */
    private $sendType;

    /**
     * @var 发货地址对应的id
     */
    private $addressId;

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
        $apiParams["package_num"]=$this->packageNum;
        $apiParams["send_type"]=$this->sendType;
        $apiParams["address_id"]=$this->addressId;
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
        return "360buy.order.sopl.outstorage";
    }

    /**
     * @param  $addressId
     */
    public function setAddressId($addressId)
    {
        $this->addressId = $addressId;
    }

    /**
     * @return
     */
    public function getAddressId()
    {
        return $this->addressId;
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
     * @param  $packageNum
     */
    public function setPackageNum($packageNum)
    {
        $this->packageNum = $packageNum;
    }

    /**
     * @return
     */
    public function getPackageNum()
    {
        return $this->packageNum;
    }

    /**
     * @param  $sendType
     */
    public function setSendType($sendType)
    {
        $this->sendType = $sendType;
    }

    /**
     * @return
     */
    public function getSendType()
    {
        return $this->sendType;
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


}