<?php
/**
*查找单个满就换购详情促销
*/

class PromotionFullchangeSingleGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.fullchange.single.get";
	}
	
	/**促销的id*/
	private  $id; 
	/**满减类型。1：商品   2:分类    3:分类 品牌,4:全场*/
	private  $fullGiftType; 

	public function getId(){
		return $this->id;
	}
	public function getFullGiftType(){
		return $this->fullGiftType;
	}

	public function setId($id){
		$this->id = $id;
		$this->apiParas["id"] = $id;
	}
	public function setFullGiftType($fullGiftType){
		$this->fullGiftType = $fullGiftType;
		$this->apiParas["fullGiftType"] = $fullGiftType;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
