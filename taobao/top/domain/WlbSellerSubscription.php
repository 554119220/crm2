<?php

/**
 * 卖家定购的服务
 * @author auto create
 */
class WlbSellerSubscription
{
	
	/** 
	 * 定购有效结束日期
	 **/
	public $endDate;
	
	/** 
	 * 创建时间
	 **/
	public $gmtCreate;
	
	/** 
	 * 修改时间
	 **/
	public $gmtModified;
	
	/** 
	 * 定购ID
	 **/
	public $id;
	
	/** 
	 * 判断该仓库是否是实体仓，还是虚拟仓，null是实体仓，10:代表虚拟仓
	 **/
	public $isOwnService;
	
	/** 
	 * 父定购ID
可通过该字段来得之服务上下级关系。
例定购了仓储服务，下又有TMS服务。
该字段保存仓储服务ID。
	 **/
	public $parentId;
	
	/** 
	 * 服务商ID
	 **/
	public $providerUserId;
	
	/** 
	 * 备注
	 **/
	public $remark;
	
	/** 
	 * 自有仓的别名，当当前订购记录为自有仓时才会有值
	 **/
	public $serviceAlias;
	
	/** 
	 * 服务编码
	 **/
	public $serviceCode;
	
	/** 
	 * 服务ID
	 **/
	public $serviceId;
	
	/** 
	 * 服务名
	 **/
	public $serviceName;
	
	/** 
	 * 服务类型，
STORE 1-仓储、
TMS 2-TMS、
PACKAGE 3-包装服务
SUPPLIER 4-供货
INSTALL 5-安装
COMPLEX_SERVICE 100-综合服务
	 **/
	public $serviceType;
	
	/** 
	 * 定购有效开始日期
	 **/
	public $startDate;
	
	/** 
	 * 状态
AUDITING 1-待审核
CANCEL 2-撤销
CHECKED 3-审核通过
FAILED 4-审核未通过
SYNCHRONIZING 5-同步中
	 **/
	public $status;
	
	/** 
	 * 定购用户ID
	 **/
	public $subscriberUserId;
	
	/** 
	 * 定购用户NICK
	 **/
	public $subscriberUserNick;
	
	/** 
	 * 联系人地址信息
	 **/
	public $wlbPartnerAddress;
	
	/** 
	 * 联系人联系详情
	 **/
	public $wlbPartnerContact;	
}
?>