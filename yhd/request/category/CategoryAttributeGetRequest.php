<?php
/**
*查询类别基本属性(不包含系列属性)
*/

class CategoryAttributeGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.category.attribute.get";
	}
	
	/**类别ID(二选一，categoryId优先，备注：必须为叶子类目ID)*/
	private  $categoryId; 
	/**属性ID(二选一)*/
	private  $attributeId; 

	public function getCategoryId(){
		return $this->categoryId;
	}
	public function getAttributeId(){
		return $this->attributeId;
	}

	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
	}
	public function setAttributeId($attributeId){
		$this->attributeId = $attributeId;
		$this->apiParas["attributeId"] = $attributeId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
