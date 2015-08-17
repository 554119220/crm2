<?php
/**
*更新APP使用次数
*/

class SbyUserappUsedCountUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.sby.userapp.used.count.update";
	}
	
	/**使用次数*/
	private  $count; 

	public function getCount(){
		return $this->count;
	}

	public function setCount($count){
		$this->count = $count;
		$this->apiParas["count"] = $count;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
