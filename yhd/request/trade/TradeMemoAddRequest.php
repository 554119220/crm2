<?php
/**
*对一笔交易添加备注(兼容淘宝) 
*/

class TradeMemoAddRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.trade.memo.add";
	}
	
	/**交易编号*/
	private  $tid; 
	/**交易备注。最大长度: 1000个字节*/
	private  $memo; 
	/**(暂不提供)交易备注旗帜，可选值为：0(灰色), 1(红色), 2(黄色), 3(绿色), 4(蓝色), 5(粉红色)，默认值为0*/
	private  $flag; 

	public function getTid(){
		return $this->tid;
	}
	public function getMemo(){
		return $this->memo;
	}
	public function getFlag(){
		return $this->flag;
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

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
