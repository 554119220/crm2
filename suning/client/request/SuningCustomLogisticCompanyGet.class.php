<?php

/**
 * Class SuningCustomLogisticCompanyGet
 * @author John Doe
 */
class SuningCustomLogisticCompanyGet
{
    private $companyName;

    /**
     * Setter for companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * makeReqObject
     * @return object
     * @author nixus
     **/
    public function makeReqObject()
    {
        if (empty($this->companyName)) {
            return array (
                'error_code' => 'param_is_null',
                'error_msg'  => '快递公司名称为空，请重新选择！'
            );
        }

        $req_param = array (
            "sn_request" => array (
                "sn_body" => array (
                    "logisticCompany" => array (
                        "companyName" => "$this->companyName"
                    )
                )
            )
        );

        return json_encode($req_param);
    }
}
