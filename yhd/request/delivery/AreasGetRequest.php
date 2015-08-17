<?php
/**
*查询地址区域（兼容淘宝）
*/

class AreasGetRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.areas.get";
	}
	
	/**需返回的字段列表.可选值:Area 结构中的所有字段;多个字段之间用","分隔.如:id,type,name,parent_id,zip. */
	private  $fields; 

	public function getFields(){
		return $this->fields;
	}

	public function setFields($fields){
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
