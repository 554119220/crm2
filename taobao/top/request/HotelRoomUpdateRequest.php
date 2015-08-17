<?php
/**
 * TOP API: taobao.hotel.room.update request
 * 
 * @author auto create
 * @since 1.0, 2015.05.06
 */
class HotelRoomUpdateRequest
{
	/** 
	 * 面积。可选值：A,B,C,D。分别代表：
A：15平米以下，B：16－30平米，C：31－50平米，D：50平米以上
	 **/
	private $area;
	
	/** 
	 * 宽带服务。A,B,C,D。分别代表：
A：无宽带，B：免费宽带，C：收费宽带，D：部分收费宽带
	 **/
	private $bbn;
	
	/** 
	 * 床型。可选值：A,B,C,D,E,F,G,H,I。分别代表：A：单人床，B：大床，C：双床，D：双床/大床，E：子母床，F：上下床，G：圆形床，H：多床，I：其他床型
	 **/
	private $bedType;
	
	/** 
	 * 早餐。A,B,C,D,E。分别代表：
A：无早，B：单早，C：双早，D：三早，E：多早
	 **/
	private $breakfast;
	
	/** 
	 * 订金。0～99999900的正整数。在payment_type为订金时必须输入，存储的单位是分。不能带角分。
	 **/
	private $deposit;
	
	/** 
	 * 商品描述。不能超过25000个汉字（50000个字符）。
	 **/
	private $desc;
	
	/** 
	 * 手续费。0～99999900的正整数。在payment_type为手续费或手续费/间夜时必须输入，存储的单位是分。不能带角分。
	 **/
	private $fee;
	
	/** 
	 * 酒店房间商品gid。必须为数字。
	 **/
	private $gid;
	
	/** 
	 * 购买须知。不能超过300个字。
	 **/
	private $guide;
	
	/** 
	 * 酒店商品是否提供发票
	 **/
	private $hasReceipt;
	
	/** 
	 * 为到店支付卖家特殊使用，代表多种支付类型的房态。room_quotas可选，如果有值，也会处理。
	 **/
	private $multiRoomQuotas;
	
	/** 
	 * 支付类型。可选值：A,B,C,D,E。分别代表：
A：全额支付，B：手续费，C：订金，D：手续费/间夜，E：前台面付
	 **/
	private $paymentType;
	
	/** 
	 * 酒店商品图片。类型:JPG,GIF;最大长度:500K。支持的文件类型：gif,jpg,jpeg,png。更新的时候只能更新一张图片，此图片覆盖原有所有图片。如果不传则使用原有所有图片。
如需再发图片，需要调用商品图片上传接口，1个商品最多5张图片。
	 **/
	private $pic;
	
	/** 
	 * 商品主图需要关联的图片空间的相对url。这个url所对应的图片必须要属于当前用户。pic_path和image只需要传入一个,如果两个都传，默认选择pic_path
	 **/
	private $picPath;
	
	/** 
	 * 价格类型。可选值：A,B。分别代表：A：参考预订价，B实时预订价 。未选该参数默认为参考预订价。选择实时预订价的情况下，支付类型必须选择为A(全额支付)
	 **/
	private $priceType;
	
	/** 
	 * 发票说明，不能超过100个汉字,200个字符。
	 **/
	private $receiptInfo;
	
	/** 
	 * 发票类型为其他时的发票描述,不能超过30个汉字，60个字符。
	 **/
	private $receiptOtherTypeDesc;
	
	/** 
	 * 发票类型。A,B。分别代表： A:酒店住宿发票,B:其他
	 **/
	private $receiptType;
	
	/** 
	 * 1. 全额支付类型必填 2. t代表类别(1表示任意退;2表示不能退;3表示阶梯退)，p代表退款规则（数组）， d代表天数，r代表扣除手续费比率。示例代表的意思就是"阶梯退:提前3天内退订，收取订单总额10%的违约金;提前2天内退订，收取订单总额20%的违约金，提前1天内退订，收取订单总额30%的违约金"。 3. 任意退、不能退不能指定退款规则;阶梯退不能没有退款规则;阶梯退规则最多10条,且每条规则天数、费率不能相同;阶梯退遵循天数越短,违约金越大的业务规则;天数需为整数,且90>天数>=0;费率需为整数且100<=费率<=0;阶梯退规则只有一条时,费率不能设置为100%;阶梯退规则只有一条时,不能设定0天收取0%;
	 **/
	private $refundPolicyInfo;
	
	/** 
	 * 房态信息。可以传今天开始90天内的房态信息。日期必须连续。JSON格式传递。
date：代表房态日期，格式为YYYY-MM-DD，
price：代表当天房价，0～99999999，存储的单位是分,
num：代表当天可售间数，0～999。
如：
[{"date":2011-01-28,"price":10000, "num":10},{"date":2011-01-29,"price":12000,"num":10}]
	 **/
	private $roomQuotas;
	
	/** 
	 * 设施服务。JSON格式。
value值true有此服务，false没有。
bar：吧台，catv：有线电视，ddd：国内长途电话，idd：国际长途电话，toilet：独立卫生间，pubtoliet：公共卫生间。
如：
{"bar":false,"catv":false,"ddd":false,"idd":false,"pubtoilet":false,"toilet":false}
	 **/
	private $service;
	
	/** 
	 * 商品的site_param
	 **/
	private $siteParam;
	
	/** 
	 * 床宽。可选值：A,B,C,D,E,F,G,H。分别代表：A：1米及以下，B：1.1米，C：1.2米，D：1.35米，E：1.5米，F：1.8米，G：2米，H：2.2米及以上
	 **/
	private $size;
	
	/** 
	 * 状态。可选值1，2，3。1：上架。2：下架。3：删除。传入相应状态代表去执行相应的操作。
	 **/
	private $status;
	
	/** 
	 * 楼层。长度不超过8
	 **/
	private $storey;
	
	/** 
	 * 酒店商品名称。不能超过60字节
	 **/
	private $title;
	
	private $apiParas = array();
	
	public function setArea($area)
	{
		$this->area = $area;
		$this->apiParas["area"] = $area;
	}

	public function getArea()
	{
		return $this->area;
	}

	public function setBbn($bbn)
	{
		$this->bbn = $bbn;
		$this->apiParas["bbn"] = $bbn;
	}

	public function getBbn()
	{
		return $this->bbn;
	}

	public function setBedType($bedType)
	{
		$this->bedType = $bedType;
		$this->apiParas["bed_type"] = $bedType;
	}

	public function getBedType()
	{
		return $this->bedType;
	}

	public function setBreakfast($breakfast)
	{
		$this->breakfast = $breakfast;
		$this->apiParas["breakfast"] = $breakfast;
	}

	public function getBreakfast()
	{
		return $this->breakfast;
	}

	public function setDeposit($deposit)
	{
		$this->deposit = $deposit;
		$this->apiParas["deposit"] = $deposit;
	}

	public function getDeposit()
	{
		return $this->deposit;
	}

	public function setDesc($desc)
	{
		$this->desc = $desc;
		$this->apiParas["desc"] = $desc;
	}

	public function getDesc()
	{
		return $this->desc;
	}

	public function setFee($fee)
	{
		$this->fee = $fee;
		$this->apiParas["fee"] = $fee;
	}

	public function getFee()
	{
		return $this->fee;
	}

	public function setGid($gid)
	{
		$this->gid = $gid;
		$this->apiParas["gid"] = $gid;
	}

	public function getGid()
	{
		return $this->gid;
	}

	public function setGuide($guide)
	{
		$this->guide = $guide;
		$this->apiParas["guide"] = $guide;
	}

	public function getGuide()
	{
		return $this->guide;
	}

	public function setHasReceipt($hasReceipt)
	{
		$this->hasReceipt = $hasReceipt;
		$this->apiParas["has_receipt"] = $hasReceipt;
	}

	public function getHasReceipt()
	{
		return $this->hasReceipt;
	}

	public function setMultiRoomQuotas($multiRoomQuotas)
	{
		$this->multiRoomQuotas = $multiRoomQuotas;
		$this->apiParas["multi_room_quotas"] = $multiRoomQuotas;
	}

	public function getMultiRoomQuotas()
	{
		return $this->multiRoomQuotas;
	}

	public function setPaymentType($paymentType)
	{
		$this->paymentType = $paymentType;
		$this->apiParas["payment_type"] = $paymentType;
	}

	public function getPaymentType()
	{
		return $this->paymentType;
	}

	public function setPic($pic)
	{
		$this->pic = $pic;
		$this->apiParas["pic"] = $pic;
	}

	public function getPic()
	{
		return $this->pic;
	}

	public function setPicPath($picPath)
	{
		$this->picPath = $picPath;
		$this->apiParas["pic_path"] = $picPath;
	}

	public function getPicPath()
	{
		return $this->picPath;
	}

	public function setPriceType($priceType)
	{
		$this->priceType = $priceType;
		$this->apiParas["price_type"] = $priceType;
	}

	public function getPriceType()
	{
		return $this->priceType;
	}

	public function setReceiptInfo($receiptInfo)
	{
		$this->receiptInfo = $receiptInfo;
		$this->apiParas["receipt_info"] = $receiptInfo;
	}

	public function getReceiptInfo()
	{
		return $this->receiptInfo;
	}

	public function setReceiptOtherTypeDesc($receiptOtherTypeDesc)
	{
		$this->receiptOtherTypeDesc = $receiptOtherTypeDesc;
		$this->apiParas["receipt_other_type_desc"] = $receiptOtherTypeDesc;
	}

	public function getReceiptOtherTypeDesc()
	{
		return $this->receiptOtherTypeDesc;
	}

	public function setReceiptType($receiptType)
	{
		$this->receiptType = $receiptType;
		$this->apiParas["receipt_type"] = $receiptType;
	}

	public function getReceiptType()
	{
		return $this->receiptType;
	}

	public function setRefundPolicyInfo($refundPolicyInfo)
	{
		$this->refundPolicyInfo = $refundPolicyInfo;
		$this->apiParas["refund_policy_info"] = $refundPolicyInfo;
	}

	public function getRefundPolicyInfo()
	{
		return $this->refundPolicyInfo;
	}

	public function setRoomQuotas($roomQuotas)
	{
		$this->roomQuotas = $roomQuotas;
		$this->apiParas["room_quotas"] = $roomQuotas;
	}

	public function getRoomQuotas()
	{
		return $this->roomQuotas;
	}

	public function setService($service)
	{
		$this->service = $service;
		$this->apiParas["service"] = $service;
	}

	public function getService()
	{
		return $this->service;
	}

	public function setSiteParam($siteParam)
	{
		$this->siteParam = $siteParam;
		$this->apiParas["site_param"] = $siteParam;
	}

	public function getSiteParam()
	{
		return $this->siteParam;
	}

	public function setSize($size)
	{
		$this->size = $size;
		$this->apiParas["size"] = $size;
	}

	public function getSize()
	{
		return $this->size;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setStorey($storey)
	{
		$this->storey = $storey;
		$this->apiParas["storey"] = $storey;
	}

	public function getStorey()
	{
		return $this->storey;
	}

	public function setTitle($title)
	{
		$this->title = $title;
		$this->apiParas["title"] = $title;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getApiMethodName()
	{
		return "taobao.hotel.room.update";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxLength($this->area,1,"area");
		RequestCheckUtil::checkMaxLength($this->bbn,1,"bbn");
		RequestCheckUtil::checkMaxLength($this->bedType,1,"bedType");
		RequestCheckUtil::checkMaxLength($this->breakfast,1,"breakfast");
		RequestCheckUtil::checkMaxValue($this->deposit,99999900,"deposit");
		RequestCheckUtil::checkMinValue($this->deposit,0,"deposit");
		RequestCheckUtil::checkMaxLength($this->desc,50000,"desc");
		RequestCheckUtil::checkMaxValue($this->fee,99999900,"fee");
		RequestCheckUtil::checkMinValue($this->fee,0,"fee");
		RequestCheckUtil::checkNotNull($this->gid,"gid");
		RequestCheckUtil::checkMaxLength($this->guide,300,"guide");
		RequestCheckUtil::checkMaxLength($this->paymentType,1,"paymentType");
		RequestCheckUtil::checkMaxLength($this->priceType,1,"priceType");
		RequestCheckUtil::checkMaxLength($this->size,1,"size");
		RequestCheckUtil::checkMaxLength($this->storey,8,"storey");
		RequestCheckUtil::checkMaxLength($this->title,90,"title");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
