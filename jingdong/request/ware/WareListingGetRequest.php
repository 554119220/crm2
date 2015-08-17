<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午1:36
 */

class WareListingGetRequest
{
    /**
     * 类目id
     */
    private $cid;

    /**
     * 分页
     */
    private $page;

    /**
     * 每页多少条
     */
    private $pageSize;

    /**
     * 起始的修改时间
     */
    private $endModified;

    /**
     * 结束的修改时间
     */
    private $startModified;


    /**
     * 可选字段
     */
    private $fields;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["cid"]=$this->cid;
        $apiParams["page"]=$this->page;
        $apiParams["page_size"]=$this->pageSize;
        $apiParams["end_modified"]=$this->endModified;
        $apiParams["start_modified"]=$this->startModified;
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
        return "360buy.ware.listing.get";
    }

    public function setCid($cid)
    {
        $this->cid = $cid;
    }

    public function getCid()
    {
        return $this->cid;
    }

    public function setEndModified($endModified)
    {
        $this->endModified = $endModified;
    }

    public function getEndModified()
    {
        return $this->endModified;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function setStartModified($startModified)
    {
        $this->startModified = $startModified;
    }

    public function getStartModified()
    {
        return $this->startModified;
    }


}