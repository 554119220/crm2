<?php

/**
 * 订单状态回传请求数据
 * @author auto create
 */
class WlbWmsOrderStatusUpload
{
	
	/** 
	 * 操作内容：物流详情显示
	 **/
	public $content;
	
	/** 
	 * 扩展属性
	 **/
	public $features;
	
	/** 
	 * 操作时间
	 **/
	public $operateDate;
	
	/** 
	 * 操作人
	 **/
	public $operator;
	
	/** 
	 * 操作人联系方式
	 **/
	public $operatorContact;
	
	/** 
	 * 仓库订单编码
	 **/
	public $orderCode;
	
	/** 
	 * 仓库订单类型：201 交易出库单（销售出库） 502 换货出库单 503 补发出库单 302 调拨入库单 501退货入库单 （销退入库单 ） 504换货入库 601 采购入库单 901 退供出库单 301 调拨出库单
	 **/
	public $orderType;
	
	/** 
	 * 备注
	 **/
	public $remark;
	
	/** 
	 * 订单状态:WMS_ ARRIVALREGISTER到货登记 WMS_RECEIVED收货完成（返回） WMS_ONSALE上架完成（返回） WMS_PICKED拣货完成 WMS_CHECKED复核完成 WMS_PACKAGED打包完成（返回） （确认物流宝的语义，修正标准。） WMS_ACCEPT - 接单（返回） WMS_PRINT - 打印 WMS_PICK – 拣货 WMS_CHECK – 复核 WMS_PACKAGE - 打包 WMS_REJECT-接单失败 WMS_FAILED-订单发货失败(买家拒签)
	 **/
	public $status;
	
	/** 
	 * 仓库订单编码
	 **/
	public $storeOrderCode;	
}
?>