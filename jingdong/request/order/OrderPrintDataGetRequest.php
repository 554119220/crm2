<?php
/**
 * User: zhangfeng
 * Date: 12-6-12
 * Time: 下午5:46
 */

class OrderPrintDataGetRequest
{
    /**
     * @var 订单id
     */
    private $orderId;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $this->apiParams["order_id"]=$this->orderId;
        ksort($this->apiParams);
        return json_encode($this->apiParams);
    }

    /**
     *
     * 获取方法名称
     * @return string
     */
    public function getApiMethod()
    {
        return "360buy.order.print.data.get";
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


}