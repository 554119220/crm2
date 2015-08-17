<?php

/**
 * 部门信息
 * @author auto create
 */
class Department
{
	
	/** 
	 * 部门ID
	 **/
	public $departmentId;
	
	/** 
	 * 部门名称
	 **/
	public $departmentName;
	
	/** 
	 * 当前部门的父部门ID
	 **/
	public $parentId;
	
	/** 
	 * 部门下关联的子账号id列表
	 **/
	public $subUserIds;	
}
?>