<?php
/**
*批量调整供应商产品的库存数量
*/

class SupplierProductsStockUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.products.stock.update";
	}
	
	/**修改商品库存的入参。格式 pmInfoId:modifySaleNum。多组数据之间用逗号分隔。此处的modifySaleNum是修改范围。如2表示库存加2，-2表示库存减2*/
	private  $updateProductStockList; 

	public function getUpdateProductStockList(){
		return $this->updateProductStockList;
	}

	public function setUpdateProductStockList($updateProductStockList){
		$this->updateProductStockList = $updateProductStockList;
		$this->apiParas["updateProductStockList"] = $updateProductStockList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
