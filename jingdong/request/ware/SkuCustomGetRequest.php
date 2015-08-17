<?php
/**
 *
 * 根据外部ID获取商品SKU
 *
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 上午10:58
 */

class SkuCustomGetRequest
{
    /**
     * @var sku的外部id
     */
    private $outerId;

    /**
     * @var 需要返回的字段
     */
    private $fields;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["outer_id"]=$this->outerId;
        $apiParams["fields"]=$this->fields;
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
        return "360buy.sku.custom.get";
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setOuterId($outerId)
    {
        $this->outerId = $outerId;
    }

    public function getOuterId()
    {
        return $this->outerId;
    }


}