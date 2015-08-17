<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午1:55
 */

class WareSkuPriceUpdateRequest
{
    /**
     * @var sku id
     */
    private $skuId;

    /**
     * @var sku外部id
     */
    private $outerId;

    /**
     * @var 价格
     */
    private $price;

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
        $apiParams["outer_id"] = $this->outerId;
        $apiParams["price"] = $this->price;
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
        return "360buy.sku.price.update";
    }

    /**
     * @param \sku $outerId
     */
    public function setOuterId($outerId)
    {
        $this->outerId = $outerId;
    }

    /**
     * @return \sku
     */
    public function getOuterId()
    {
        return $this->outerId;
    }

    /**
     * @param  $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param \sku $skuId
     */
    public function setSkuId($skuId)
    {
        $this->skuId = $skuId;
    }

    /**
     * @return \sku
     */
    public function getSkuId()
    {
        return $this->skuId;
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