<?php
/**
 * User: zhangfeng
 * Date: 12-6-11
 * Time: 下午11:47
 */

class DeliveryLogisticsGetRequest
{

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        ksort($apiParams);
//        $apiParams = array();
        return json_encode($apiParams);
    }

    /**
     *
     * 获取方法名称
     * @return string
     */
    public function getApiMethod()
    {
        return "360buy.delivery.logistics.get";
    }
}