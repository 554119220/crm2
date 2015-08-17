<?php
/**
*提交查询用户track文件信息的申请
*/

class DataTrackSubmitRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.data.track.submit";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
