<?php
/**
*查询子品促销信息
*/

class SubPromotionPriceGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.sub.promotion.price.get";
	}
	
	/**系列产品促销id*/
	private  $id; 

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
		$this->apiParas["id"] = $id;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
