<?php
/**
 *
 * 获取单个SKU信息
 *
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午1:54
 */

class WareSkuGetRequest
{
    /**
     * @var sku id
     */
    private $skuId;

    /**
     * @var 返回字段
     */
    private $fields;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["sku_id"] = $this->skuId;
        $apiParams["fields"] = $this->fields;
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
        return "360buy.ware.sku.get";
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setSkuId($skuId)
    {
        $this->skuId = $skuId;
    }

    public function getSkuId()
    {
        return $this->skuId;
    }


}