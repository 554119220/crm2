<?php
/**
*批量查询系列子品信息
*/

class SerialChildproductsGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.serial.childproducts.get";
	}
	
	/**1号店产品ID列表（逗号分隔）与outerIdList、productCodeList三选一,最大长度为100，优先级最高*/
	private  $productIdList; 
	/**外部产品ID列表（逗号分隔）与productIdList、productCodeList三选一,最大长度为100，每个最多30字符，优先级次之*/
	private  $outerIdList; 
	/**产品编码列表（逗号分隔）与productIdList、outerIdList三选一,最大长度为100,每个最多30字符，优先级最低*/
	private  $productCodeList; 

	public function getProductIdList(){
		return $this->productIdList;
	}
	public function getOuterIdList(){
		return $this->outerIdList;
	}
	public function getProductCodeList(){
		return $this->productCodeList;
	}

	public function setProductIdList($productIdList){
		$this->productIdList = $productIdList;
		$this->apiParas["productIdList"] = $productIdList;
	}
	public function setOuterIdList($outerIdList){
		$this->outerIdList = $outerIdList;
		$this->apiParas["outerIdList"] = $outerIdList;
	}
	public function setProductCodeList($productCodeList){
		$this->productCodeList = $productCodeList;
		$this->apiParas["productCodeList"] = $productCodeList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
