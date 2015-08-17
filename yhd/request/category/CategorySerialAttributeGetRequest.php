<?php
/**
*查询类别系列属性
*/

class CategorySerialAttributeGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.category.serial.attribute.get";
	}
	
	/**类别ID*/
	private  $categoryId; 

	public function getCategoryId(){
		return $this->categoryId;
	}

	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
