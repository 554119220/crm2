<?php
/**
*更新联合发券活动状态
*/

class PromotionUnitecouponsStateUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.unitecoupons.state.update";
	}
	
	/**联合发券活动id*/
	private  $id; 
	/**活动状态。具体请见此接口描述。*/
	private  $newState; 

	public function getId(){
		return $this->id;
	}
	public function getNewState(){
		return $this->newState;
	}

	public function setId($id){
		$this->id = $id;
		$this->apiParas["id"] = $id;
	}
	public function setNewState($newState){
		$this->newState = $newState;
		$this->apiParas["newState"] = $newState;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
