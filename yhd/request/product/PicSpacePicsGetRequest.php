<?php
/**
*查询图片空间图片信息
*/

class PicSpacePicsGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.pic.space.pics.get";
	}
	
	/**图片空间类别ID */
	private  $picCategoryId; 
	/**图片名称*/
	private  $picName; 
	/**当前页数*/
	private  $curPage; 
	/**每页显示记录数*/
	private  $pageRows; 
	/**图片空间id(用于图片空间图片的新增，删除，修改，查询功能)*/
	private  $picSpaceIdList; 

	public function getPicCategoryId(){
		return $this->picCategoryId;
	}
	public function getPicName(){
		return $this->picName;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getPicSpaceIdList(){
		return $this->picSpaceIdList;
	}

	public function setPicCategoryId($picCategoryId){
		$this->picCategoryId = $picCategoryId;
		$this->apiParas["picCategoryId"] = $picCategoryId;
	}
	public function setPicName($picName){
		$this->picName = $picName;
		$this->apiParas["picName"] = $picName;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setPicSpaceIdList($picSpaceIdList){
		$this->picSpaceIdList = $picSpaceIdList;
		$this->apiParas["picSpaceIdList"] = $picSpaceIdList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
