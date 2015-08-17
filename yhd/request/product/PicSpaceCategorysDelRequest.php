<?php
/**
*批量删除图片空间分类
*/

class PicSpaceCategorysDelRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.pic.space.categorys.del";
	}
	
	/**图片分类ID列表。多个分类ID之间用逗号分隔，最大100个。*/
	private  $picCategoryIdList; 

	public function getPicCategoryIdList(){
		return $this->picCategoryIdList;
	}

	public function setPicCategoryIdList($picCategoryIdList){
		$this->picCategoryIdList = $picCategoryIdList;
		$this->apiParas["picCategoryIdList"] = $picCategoryIdList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
