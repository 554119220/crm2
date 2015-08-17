<?php

/**
 * Class SuningCustomOrderQuery
 * @author John Doe
 */
class SuningCustomOrderQuery
{
    private $startTime;
    private $endTime;
    private $orderStatus;
    private $pageNo;
    private $pageSize;

    /**
     * Setter for startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * Setter for endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * Setter for orderStatus
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * Setter for pageNo
     */
    public function setPageNo($pageNo)
    {
        $this->pageNo = $pageNo;
    }

    /**
     * Setter for pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * Getter for postFields
     */
    public function getPostFields()
    {
        $postFields = array (
            "sn_request" => array(
                "sn_body" => array(
                    "orderQuery" => array(
                        "startTime"   => "$this->startTime",
                        "endTime"     => "$this->endTime",
                        "orderStatus" => "$this->orderStatus",
                        "pageNo"      => "$this->pageNo",
                        "pageSize"    => "$this->pageSize"
                    )
                )
            )
        );

        //$postFields = sprintf('<sn_request><sn_body><orderQuery><startTime>%s</startTime><endTime>%s</endTime><orderStatus>%d</orderStatus><pageNo>%d</pageNo><pageSize>%d</pageSize></orderQuery></sn_body></sn_request>', $this->startTime, $this->endTime, $this->orderStatus, $this->pageNo, $this->pageSize);

        if (is_array($postFields)) {
            $postFields = json_encode($postFields);
        }

        return $postFields;
    }
}
