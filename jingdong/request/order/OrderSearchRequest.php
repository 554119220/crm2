<?php
/**
 * User: zhangfeng
 * Date: 12-6-12
 * Time: 下午5:51
 */

class OrderSearchRequest
{
    /**
     * @var 开始时间
     */
    private $startDate;

    /**
     * @var 结束时间
     */
    private $endDate;

    /**
     * @var 订单状态
     */
    private $orderState;

    /**
     * @var 页码
     */
    private $page;

    /**
     * @var 每页的大小
     */
    private $pageSize;

    /**
     * @var 可选字段
     */
    private $optionalFields;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["start_date"]=$this->startDate;
        $apiParams["end_date"]=$this->endDate;
        $apiParams["order_state"]=$this->orderState;
        $apiParams["page"]=$this->page;
        $apiParams["page_size"]=$this->pageSize;
        $apiParams["optional_fields"]=$this->optionalFields;
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
        return "360buy.order.search";
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setOptionalFields($optionalFields)
    {
        $this->optionalFields = $optionalFields;
    }

    public function getOptionalFields()
    {
        return $this->optionalFields;
    }

    public function setOrderState($orderState)
    {
        $this->orderState = $orderState;
    }

    public function getOrderState()
    {
        return $this->orderState;
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

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }
}
