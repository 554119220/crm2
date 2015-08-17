<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 上午11:00
 */

class WareAddRequest
{

    /**
     * @var 流水号
     */
    private $tradeNo;

    /**
     * @var 三级类目ID
     */
    private $cid;

    /**
     * @var 店内分类
     */
    private $shopCategory;

    /**
     * @var 商品名称
     */
    private $title;
    /**
     * @var upc编码
     */
    private $upcCode;

    /**
     * @var 操作类型,默认待售
     */
    private $optionType = "offsale";
    /**
     * @var 货号
     */
    private $itemNum;

    /**
     * @var 库存数
     */
    private $stockNum;

    /**
     * @var 生产厂商
     */
    private $producter;
    /**
     * @var 包装规格
     */
    private $wrap;
    /**
     * @var 长
     */
    private $length;
    /**
     * @var 宽
     */
    private $wide;
    /**
     * @var 高
     */
    private $high;
    /**
     * @var 重量
     */
    private $weight;
    /**
     * @var 进货价
     */
    private $costPrice;
    /**
     * @var 市场价
     */
    private $marketPrice;
    /**
     * @var 京东价
     */
    private $jdPrice;
    /**
     * @var描述
     */
    private $notes;

    /**
     * @var 新增商品主图byte
     */
    private $wareImage;

    /**
     * @var 商品包装清单信息
     */
    private $packListing;

    /**
     * @var 商品售后服务
     */
    private $service;

    /**
     * sku 属性字符串
     */
    private $skuProperties;

    /**
     * @var 属性字符串
     */
    private $attributes;

    /**
     * @var Sku的价格串
     */
    private $skuPrices;

    /**
     * @var Sku的数量串
     */
    private $skuStocks;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["trade_no"]=$this->tradeNo;
        $apiParams["cid"]=$this->cid;
        $apiParams["shop_category"]=$this->shopCategory;
        $apiParams["title"]=$this->title;
        $apiParams["upc_code"]=$this->upcCode;
        $apiParams["option_type"]=$this->optionType;
        $apiParams["item_num"]=$this->itemNum;
        $apiParams["stock_num"]=$this->stockNum;
        $apiParams["producter"]=$this->producter;
        $apiParams["wrap"]=$this->wrap;
        $apiParams["length"]=$this->length;
        $apiParams["wide"]=$this->wide;
        $apiParams["high"]=$this->high;
        $apiParams["weight"]=$this->weight;
        $apiParams["cost_price"]=$this->costPrice;
        $apiParams["market_price"]=$this->marketPrice;
        $apiParams["jd_price"]=$this->jdPrice;
        $apiParams["notes"]=$this->notes;
        $apiParams["ware_image"]=$this->wareImage;
        $apiParams["pack_listing"]=$this->packListing;
        $apiParams["service"]=$this->service;
        $apiParams["sku_properties"]=$this->skuProperties;
        $apiParams["attributes"]=$this->attributes;
        $apiParams["sku_prices"]=$this->skuPrices;
        $apiParams["sku_stocks"]=$this->skuStocks;
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
        return "360buy.ware.add";
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
     * @param  $cid
     */
    public function setCid($cid)
    {
        $this->cid = $cid;
    }

    /**
     * @return
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * @param  $costPrice
     */
    public function setCostPrice($costPrice)
    {
        $this->costPrice = $costPrice;
    }

    /**
     * @return
     */
    public function getCostPrice()
    {
        return $this->costPrice;
    }

    /**
     * @param  $high
     */
    public function setHigh($high)
    {
        $this->high = $high;
    }

    /**
     * @return
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * @param  $itemNum
     */
    public function setItemNum($itemNum)
    {
        $this->itemNum = $itemNum;
    }

    /**
     * @return
     */
    public function getItemNum()
    {
        return $this->itemNum;
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
     * @param  $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param  $marketPrice
     */
    public function setMarketPrice($marketPrice)
    {
        $this->marketPrice = $marketPrice;
    }

    /**
     * @return
     */
    public function getMarketPrice()
    {
        return $this->marketPrice;
    }

    /**
     * @param  $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param  $optionType
     */
    public function setOptionType($optionType)
    {
        $this->optionType = $optionType;
    }

    /**
     * @return
     */
    public function getOptionType()
    {
        return $this->optionType;
    }

    /**
     * @param  $packListing
     */
    public function setPackListing($packListing)
    {
        $this->packListing = $packListing;
    }

    /**
     * @return
     */
    public function getPackListing()
    {
        return $this->packListing;
    }

    /**
     * @param  $producter
     */
    public function setProducter($producter)
    {
        $this->producter = $producter;
    }

    /**
     * @return
     */
    public function getProducter()
    {
        return $this->producter;
    }

    /**
     * @param  $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return
     */
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

    /**
     * @param \Sku $skuPrices
     */
    public function setSkuPrices($skuPrices)
    {
        $this->skuPrices = $skuPrices;
    }

    /**
     * @return \Sku
     */
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

    /**
     * @param \Sku $skuStocks
     */
    public function setSkuStocks($skuStocks)
    {
        $this->skuStocks = $skuStocks;
    }

    /**
     * @return \Sku
     */
    public function getSkuStocks()
    {
        return $this->skuStocks;
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
     * @param  $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return
     */
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

    /**
     * @param \upc $upcCode
     */
    public function setUpcCode($upcCode)
    {
        $this->upcCode = $upcCode;
    }

    /**
     * @return \upc
     */
    public function getUpcCode()
    {
        return $this->upcCode;
    }

    /**
     * @param  $wareImage
     */
    public function setWareImage($wareImage)
    {
        $this->wareImage = $wareImage;
    }

    /**
     * @return
     */
    public function getWareImage()
    {
        return $this->wareImage;
    }

    /**
     * @param  $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param  $wide
     */
    public function setWide($wide)
    {
        $this->wide = $wide;
    }

    /**
     * @return
     */
    public function getWide()
    {
        return $this->wide;
    }

    /**
     * @param  $wrap
     */
    public function setWrap($wrap)
    {
        $this->wrap = $wrap;
    }

    /**
     * @return
     */
    public function getWrap()
    {
        return $this->wrap;
    }


}