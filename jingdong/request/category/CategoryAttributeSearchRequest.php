<?php
/**
 *
 * todo 判断是否为关键属性的条件值，输入后不能正确显示
 *
 * User: zhangfeng
 * Date: 12-6-11
 * Time: 下午11:31
 */

class CategoryAttributeSearchRequest
{
    /**
     * @var 类目id
     */
    private $cid;

    /**
     * @var 是否关键属性(true - 是 /false - 否)
     */
    private $isKeyProp;

    /**
     * @var 是否销售属性(true - 是/false - 否)
     */
    private $isSaleProp;

    /**
     * @var 属性id
     */
    private $aid;

    /**
     * @var 需返回的字段列表
     */
    private $fields;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["cid"]=$this->cid;
        $apiParams["is_key_prop"]=$this->isKeyProp;
        $apiParams["is_sale_prop"]=$this->isSaleProp;
        $apiParams["aid"]=$this->aid;
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
        return "360buy.ware.get.attribute";
    }

    /**
     * @param  $aid
     */
    public function setAid($aid)
    {
        $this->aid = $aid;
    }

    /**
     * @return
     */
    public function getAid()
    {
        return $this->aid;
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

    /**
     * @param  $isKeyProp
     */
    public function setIsKeyProp($isKeyProp)
    {
        $this->isKeyProp = $isKeyProp;
    }

    /**
     * @return
     */
    public function getIsKeyProp()
    {
        return $this->isKeyProp;
    }

    /**
     * @param  $isSaleProp
     */
    public function setIsSaleProp($isSaleProp)
    {
        $this->isSaleProp = $isSaleProp;
    }

    /**
     * @return
     */
    public function getIsSaleProp()
    {
        return $this->isSaleProp;
    }


}