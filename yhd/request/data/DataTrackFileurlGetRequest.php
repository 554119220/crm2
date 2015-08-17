<?php
/**
*查询用户track文件链接
*/

class DataTrackFileurlGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.data.track.fileurl.get";
	}
	



	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
