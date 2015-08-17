<?php
/**
 *
 * todo 确认fileds字段
 *
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 上午11:43
 */

class WareDelistingGetRequest
{
    /**
     * @var 类目id
     */
    private $cid;

    /**
     * @var 分页
     */
    private $page;

    /**
     * @var 每页多少条
     */
    private $pageSize;

    /**
     * @var 起始的修改时间
     */
    private $endModified;

    /**
     * @var 结束的修改时间
     */
    private $startModified;


    /**
     * @var 可选字段
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
//        $apiParams["fields"]=$this->fields;
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
        return "360buy.ware.delisting.get";
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
     * @param  $endModified
     */
    public function setEndModified($endModified)
    {
        $this->endModified = $endModified;
    }

    /**
     * @return
     */
    public function getEndModified()
    {
        return $this->endModified;
    }

//    /**
//     * @param  $fields
//     */
//    public function setFields($fields)
//    {
//        $this->fields = $fields;
//    }
//
//    /**
//     * @return
//     */
//    public function getFields()
//    {
//        return $this->fields;
//    }

    /**
     * @param  $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param  $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * @return
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param  $startModified
     */
    public function setStartModified($startModified)
    {
        $this->startModified = $startModified;
    }

    /**
     * @return
     */
    public function getStartModified()
    {
        return $this->startModified;
    }


}