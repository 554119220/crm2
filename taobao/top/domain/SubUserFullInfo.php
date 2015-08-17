<?php

/**
 * 子账号详细信息，其中包括账号基本信息、员工信息和部门职务信息
 * @author auto create
 */
class SubUserFullInfo
{
	
	/** 
	 * 部门Id
	 **/
	public $departmentId;
	
	/** 
	 * 部门名称
	 **/
	public $departmentName;
	
	/** 
	 * 职务Id
	 **/
	public $dutyId;
	
	/** 
	 * 职务等级
	 **/
	public $dutyLevel;
	
	/** 
	 * 职务名称
	 **/
	public $dutyName;
	
	/** 
	 * 员工ID
	 **/
	public $employeeId;
	
	/** 
	 * 员工姓名
	 **/
	public $employeeName;
	
	/** 
	 * 员工花名
	 **/
	public $employeeNickname;
	
	/** 
	 * 入职员工工号
	 **/
	public $employeeNum;
	
	/** 
	 * 员工入职时间
	 **/
	public $entryDate;
	
	/** 
	 * 直接上级的员工ID
	 **/
	public $leaderId;
	
	/** 
	 * 办公电话
	 **/
	public $officePhone;
	
	/** 
	 * 父部门Id
	 **/
	public $parentDepartment;
	
	/** 
	 * 员工性别  1:男;  2:女
	 **/
	public $sex;
	
	/** 
	 * 子账号是否参与分流 true:参与分流 false:未参与分流
	 **/
	public $subDispatchStatus;
	
	/** 
	 * 子账号Id
	 **/
	public $subId;
	
	/** 
	 * 子账号用户名
	 **/
	public $subNick;
	
	/** 
	 * 子账号是否已欠费 true:已欠费 false:未欠费
	 **/
	public $subOwedStatus;
	
	/** 
	 * 子账号当前状态：1正常，2卖家停用，3处罚冻结
	 **/
	public $subStatus;
	
	/** 
	 * 子账号企业邮箱
	 **/
	public $subuserEmail;
	
	/** 
	 * 主账号企业邮箱
	 **/
	public $userEmail;
	
	/** 
	 * 主账号Id
	 **/
	public $userId;
	
	/** 
	 * 主账号用户名
	 **/
	public $userNick;
	
	/** 
	 * 工作地点
	 **/
	public $workLocation;	
}
?>