<?php
/**
*新增图片空间类别
*/

class PicSpaceCategorysAddRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.pic.space.categorys.add";
	}
	
	/**图片类别名称*/
	private  $picCategoryName; 

	public function getPicCategoryName(){
		return $this->picCategoryName;
	}

	public function setPicCategoryName($picCategoryName){
		$this->picCategoryName = $picCategoryName;
		$this->apiParas["picCategoryName"] = $picCategoryName;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
