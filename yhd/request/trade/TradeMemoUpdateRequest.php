<?php
/**
*修改一笔交易备注（兼容淘宝） 
*/

class TradeMemoUpdateRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.trade.memo.update";
	}
	
	/** 交易编号*/
	private  $tid; 
	/**交易备注。最大长度: 1000个字节*/
	private  $memo; 
	/**	 交易备注旗帜，可选值为：0(灰色), 1(红色), 2(黄色), 3(绿色), 4(蓝色), 5(粉红色)，默认值为0(暂不提供)*/
	private  $flag; 
	/**是否对memo的值置空 若为true，则不管传入的memo字段的值是否为空，都将会对已有的memo值清空，慎用； 若用false，则会根据memo是否为空来修改memo的值：若memo为空则忽略对已有memo字段的修改，若memo非空，则使用新传入的memo覆盖已有的memo的值（暂不提供）*/
	private  $reset; 

	public function getTid(){
		return $this->tid;
	}
	public function getMemo(){
		return $this->memo;
	}
	public function getFlag(){
		return $this->flag;
	}
	public function getReset(){
		return $this->reset;
	}

	public function setTid($tid){
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}
	public function setMemo($memo){
		$this->memo = $memo;
		$this->apiParas["memo"] = $memo;
	}
	public function setFlag($flag){
		$this->flag = $flag;
		$this->apiParas["flag"] = $flag;
	}
	public function setReset($reset){
		$this->reset = $reset;
		$this->apiParas["reset"] = $reset;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
