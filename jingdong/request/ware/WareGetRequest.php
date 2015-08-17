<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 上午11:46
 */

class WareGetRequest
{
    private $wareId;
    private $fields;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["ware_id"]=$this->wareId;
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
        return "360buy.ware.get";
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
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