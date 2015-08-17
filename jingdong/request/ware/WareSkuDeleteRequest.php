<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午1:52
 */

class WareSkuDeleteRequest
{
    /**
     * @var sku id
     */
    private $skuId;

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
        $apiParams["sku_id"] = $this->skuId;
        $apiParams["trade_no"] = $this->tradeNo;
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
        return "360buy.ware.sku.delete";
    }

    public function setSkuId($skuId)
    {
        $this->skuId = $skuId;
    }

    public function getSkuId()
    {
        return $this->skuId;
    }

    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
    }

    public function getTradeNo()
    {
        return $this->tradeNo;
    }


}