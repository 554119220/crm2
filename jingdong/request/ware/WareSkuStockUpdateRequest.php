<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午2:00
 */

class WareSkuStockUpdateRequest
{
    /**
     * @var sku id
     */
    private $skuId;

    /**
     * @var 外部id
     */
    private $outerId;

    /**
     * @var 库存数量
     */
    private $quantity;

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
        $apiParams["quantity"] = $this->quantity;
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
        return "360buy.sku.stock.update";
    }

    public function setOuterId($outerId)
    {
        $this->outerId = $outerId;
    }

    public function getOuterId()
    {
        return $this->outerId;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getQuantity()
    {
        return $this->quantity;
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