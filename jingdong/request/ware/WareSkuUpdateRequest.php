<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午2:03
 */

class WareSkuUpdateRequest
{
    /**
     * @var skuid
     */
    private $skuId;

    /**
     * @var 商品id
     */
    private $wareId;

    /**
     * @var 外部id
     */
    private $outerId;

    /**
     * @var 属性
     */
    private $attributes;

    /**
     * @var 价格
     */
    private $jdPrice;

    /**
     * @var 库存
     */
    private $stockNum;

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
        $apiParams["ware_id"] = $this->wareId;
        $apiParams["outer_id"] = $this->outerId;
        $apiParams["attributes"] = $this->attributes;
        $apiParams["jd_price"] = $this->jdPrice;
        $apiParams["stock_num"] = $this->stockNum;
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
        return "360buy.ware.sku.update";
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setJdPrice($jdPrice)
    {
        $this->jdPrice = $jdPrice;
    }

    public function getJdPrice()
    {
        return $this->jdPrice;
    }

    public function setOuterId($outerId)
    {
        $this->outerId = $outerId;
    }

    public function getOuterId()
    {
        return $this->outerId;
    }

    public function setSkuId($skuId)
    {
        $this->skuId = $skuId;
    }

    public function getSkuId()
    {
        return $this->skuId;
    }

    public function setStockNum($stockNum)
    {
        $this->stockNum = $stockNum;
    }

    public function getStockNum()
    {
        return $this->stockNum;
    }

    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
    }

    public function getTradeNo()
    {
        return $this->tradeNo;
    }

    public function setWareId($wareId)
    {
        $this->wareId = $wareId;
    }

    public function getWareId()
    {
        return $this->wareId;
    }


}