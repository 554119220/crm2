<?php
/**
*团购下架
*/

class GroupVirtualProductOfflineRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.group.virtual.product.offline";
	}
	
	/**1号店团购id*/
	private  $groupId; 

	public function getGroupId(){
		return $this->groupId;
	}

	public function setGroupId($groupId){
		$this->groupId = $groupId;
		$this->apiParas["groupId"] = $groupId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
