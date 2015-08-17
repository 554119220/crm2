<?php
/**
 * User: zhangfeng
 * Date: 12-6-12
 * Time: 下午5:09
 */

class OrderGetRequest
{
    /**
     * @var 订单id
     */
    private $orderId;

    /**
     * @var 可选字段
     */
    private $optionalFields;

    /**
     * @var 订单状态
     */
    private $orderState;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["order_id"]=$this->orderId;
        $apiParams["optional_fields"]=$this->optionalFields;
        $apiParams["order_state"]=$this->orderState;
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
        return "360buy.order.get";
    }

    public function setOptionalFields($optionalFields)
    {
        $this->optionalFields = $optionalFields;
    }

    public function getOptionalFields()
    {
        return $this->optionalFields;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setOrderState($orderState)
    {
        $this->orderState = $orderState;
    }

    public function getOrderState()
    {
        return $this->orderState;
    }


}