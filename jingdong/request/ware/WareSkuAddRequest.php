<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午1:43
 */

class WareSkuAddRequest
{
    /**
     * @var 商品id
     */
    private $wareId;

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
     * @var 店铺id
     */
    private $shopId;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["ware_id"]=$this->wareId;
        $apiParams["attributes"]=$this->attributes;
        $apiParams["jd_price"]=$this->jdPrice;
        $apiParams["stock_num"]=$this->stockNum;
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
        return "360buy.ware.sku.add";
    }

    /**
     * @param  $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param  $jdPrice
     */
    public function setJdPrice($jdPrice)
    {
        $this->jdPrice = $jdPrice;
    }

    /**
     * @return
     */
    public function getJdPrice()
    {
        return $this->jdPrice;
    }

    /**
     * @param  $stockNum
     */
    public function setStockNum($stockNum)
    {
        $this->stockNum = $stockNum;
    }

    /**
     * @return
     */
    public function getStockNum()
    {
        return $this->stockNum;
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

    /**
     * @param  $wareId
     */
    public function setWareId($wareId)
    {
        $this->wareId = $wareId;
    }

    /**
     * @return
     */
    public function getWareId()
    {
        return $this->wareId;
    }

    /**
     * @param  $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return
     */
    public function getShopId()
    {
        return $this->shopId;
    }


}