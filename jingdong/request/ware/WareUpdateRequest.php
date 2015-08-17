<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午2:08
 */

class WareUpdateRequest
{
    /**
     * @var 商品id
     */
    private $wareId;

    /**
     * @var 店内分类
     */
    private $shopCategory;

    /**
     * 商品名称
     */
    private $title;

    /**
     * upc编码
     */
    private $upcCode;

    /**
     * 操作类型,默认待售
     */
    private $optionType = "offsale";

    /**
     * 货号
     */
    private $itemNum;

    /**
     * 生产厂商
     */
    private $producter;

    /**
     * 包装规格
     */
    private $wrap;

    /**
     * 长
     */
    private $length;

    /**
     * 宽
     */
    private $wide;

    /**
     * 高
     */
    private $high;

    /**
     * 重量
     */
    private $weight;

    /**
     * 进货价
     */
    private $costPrice;

    /**
     * 市场价
     */
    private $marketPrice;

    /**
     * 京东价
     */
    private $jdPrice;

    /**
     * 描述
     */
    private $notes;

    /**
     * 价格比率
     */
    private $rate;

    /**
     * 商品包装清单信息
     */
    private $packListing;

    /**
     * 商品售后服务
     */
    private $service;

    /**
     * sku 属性字符串
     */
    private $skuProperties;

    /**
     * 属性字符串
     */
    private $attributes;

    /**
     * Sku的价格串
     */
    private $skuPrices;

    /**
     * Sku的数量串
     */
    private $skuStocks;

    /**
     * @var 流水号
     */
    private $tradeNo;

    /**
     * @var 返回的字段
     */
    private $fields;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["ware_id"] = $this->wareId;
        $apiParams["shop_category"] = $this->shopCategory;
        $apiParams["title"] = $this->title;
        $apiParams["upc_code"] = $this->upcCode;
        $apiParams["option_type"] = $this->optionType;
        $apiParams["item_num"] = $this->itemNum;
        $apiParams["producter"] = $this->producter;
        $apiParams["wrap"] = $this->wrap;
        $apiParams["length"] = $this->length;
        $apiParams["high"] = $this->high;
        $apiParams["weight"] = $this->weight;
        $apiParams["wide"] = $this->wide;
        $apiParams["cost_price"] = $this->costPrice;
        $apiParams["market_price"] = $this->marketPrice;
        $apiParams["jd_price"] = $this->jdPrice;
        $apiParams["notes"] = $this->notes;
        $apiParams["rate"] = $this->rate;
        $apiParams["pack_listing"] = $this->packListing;
        $apiParams["service"] = $this->service;
        $apiParams["sku_properties"] = $this->skuProperties;
        $apiParams["attributes"] = $this->attributes;
        $apiParams["sku_prices"] = $this->skuPrices;
        $apiParams["sku_stocks"] = $this->skuStocks;
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
        return "360buy.ware.update";
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setCostPrice($costPrice)
    {
        $this->costPrice = $costPrice;
    }

    public function getCostPrice()
    {
        return $this->costPrice;
    }

    /**
     * @param  $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function setHigh($high)
    {
        $this->high = $high;
    }

    public function getHigh()
    {
        return $this->high;
    }

    public function setItemNum($itemNum)
    {
        $this->itemNum = $itemNum;
    }

    public function getItemNum()
    {
        return $this->itemNum;
    }

    public function setJdPrice($jdPrice)
    {
        $this->jdPrice = $jdPrice;
    }

    public function getJdPrice()
    {
        return $this->jdPrice;
    }

    public function setLength($length)
    {
        $this->length = $length;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function setMarketPrice($marketPrice)
    {
        $this->marketPrice = $marketPrice;
    }

    public function getMarketPrice()
    {
        return $this->marketPrice;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setOptionType($optionType)
    {
        $this->optionType = $optionType;
    }

    public function getOptionType()
    {
        return $this->optionType;
    }

    public function setPackListing($packListing)
    {
        $this->packListing = $packListing;
    }

    public function getPackListing()
    {
        return $this->packListing;
    }

    public function setProducter($producter)
    {
        $this->producter = $producter;
    }

    public function getProducter()
    {
        return $this->producter;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }

    /**
     * @param  $shopCategory
     */
    public function setShopCategory($shopCategory)
    {
        $this->shopCategory = $shopCategory;
    }

    /**
     * @return
     */
    public function getShopCategory()
    {
        return $this->shopCategory;
    }

    public function setSkuPrices($skuPrices)
    {
        $this->skuPrices = $skuPrices;
    }

    public function getSkuPrices()
    {
        return $this->skuPrices;
    }

    public function setSkuProperties($skuProperties)
    {
        $this->skuProperties = $skuProperties;
    }

    public function getSkuProperties()
    {
        return $this->skuProperties;
    }

    public function setSkuStocks($skuStocks)
    {
        $this->skuStocks = $skuStocks;
    }

    public function getSkuStocks()
    {
        return $this->skuStocks;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
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

    public function setUpcCode($upcCode)
    {
        $this->upcCode = $upcCode;
    }

    public function getUpcCode()
    {
        return $this->upcCode;
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

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function setWide($wide)
    {
        $this->wide = $wide;
    }

    public function getWide()
    {
        return $this->wide;
    }

    public function setWrap($wrap)
    {
        $this->wrap = $wrap;
    }

    public function getWrap()
    {
        return $this->wrap;
    }


}