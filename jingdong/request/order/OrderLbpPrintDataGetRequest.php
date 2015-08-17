<?php
/**
 * User: zhangfeng
 * Date: 12-6-11
 * Time: 下午11:51
 */

class LbpOrderPrintDataGetRequest
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
        $apiParams["order_id"]=$this->orderId;
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
        return "360buy.order.lbp.print.data.get";
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }


}