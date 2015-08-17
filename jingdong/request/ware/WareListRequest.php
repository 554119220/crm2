<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午1:39
 */

class WareListRequest
{

    /**
     * @var 商品id
     */
    private $wareIds;

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
        $apiParams["ware_ids"]=$this->wareIds;
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
        return "360buy.wares.list.get";
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setWareIds($wareIds)
    {
        $this->wareIds = $wareIds;
    }

    public function getWareIds()
    {
        return $this->wareIds;
    }


}