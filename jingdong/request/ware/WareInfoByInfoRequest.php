<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 上午11:47
 */

class WareInfoByInfoRequest
{
    /**
     * @var 类目id
     */
    private $cid;

    /**
     * @var 最大价格
     */
    private $startPrice;

    /**
     * @var 最大价格
     */
    private $endPrice;

    /**
     * @var 页码
     */
    private $page;

    /**
     * @var 每页条数
     */
    private $pageSize;

    /**
     * @var 商品名称
     */
    private $title;

    /**
     * @var 排序
     */
    private $orderBy;

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
        $apiParams["cid"]=$this->cid;
        $apiParams["start_price"]=$this->startPrice;
        $apiParams["end_price"]=$this->endPrice;
        $apiParams["page"]=$this->page;
        $apiParams["page_size"]=$this->pageSize;
        $apiParams["title"]=$this->title;
        $apiParams["order_by"]=$this->orderBy;
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
        return "360buy.wares.search";
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
     * @param  $endPrice
     */
    public function setEndPrice($endPrice)
    {
        $this->endPrice = $endPrice;
    }

    /**
     * @return
     */
    public function getEndPrice()
    {
        return $this->endPrice;
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
     * @param  $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

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
     * @param  $startPrice
     */
    public function setStartPrice($startPrice)
    {
        $this->startPrice = $startPrice;
    }

    /**
     * @return
     */
    public function getStartPrice()
    {
        return $this->startPrice;
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


}