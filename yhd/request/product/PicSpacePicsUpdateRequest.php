<?php
/**
*批量更新图片空间图片信息
*/

class PicSpacePicsUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.pic.space.pics.update";
	}
	
	/**图片空间图片更新信息 由picSpaceId:picName:picCategoryId组成，不同图片信息逗号分隔。 picSpaceId:图片在图片空间的唯一标识； picName:新的图片名称； picCategoryId:新的类别Id。*/
	private  $picInfoList; 

	public function getPicInfoList(){
		return $this->picInfoList;
	}

	public function setPicInfoList($picInfoList){
		$this->picInfoList = $picInfoList;
		$this->apiParas["picInfoList"] = $picInfoList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
