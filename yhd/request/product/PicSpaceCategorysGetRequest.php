<?php
/**
*批量查询图片空间分类信息
*/

class PicSpaceCategorysGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.pic.space.categorys.get";
	}
	
	/**图片分类ID列表。多个分类ID之间用逗号分隔，最大100个。*/
	private  $picCategoryIdList; 
	/**图片空间分类名称。图片空间分类名称。支持模糊查询。*/
	private  $picCategoryName; 

	public function getPicCategoryIdList(){
		return $this->picCategoryIdList;
	}
	public function getPicCategoryName(){
		return $this->picCategoryName;
	}

	public function setPicCategoryIdList($picCategoryIdList){
		$this->picCategoryIdList = $picCategoryIdList;
		$this->apiParas["picCategoryIdList"] = $picCategoryIdList;
	}
	public function setPicCategoryName($picCategoryName){
		$this->picCategoryName = $picCategoryName;
		$this->apiParas["picCategoryName"] = $picCategoryName;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
