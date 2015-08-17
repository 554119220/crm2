<?php

/**
 * 权限信息
 * @author auto create
 */
class Permission
{
	
	/** 
	 * 1 :允许授权  2：不允许授权 6：不允许授权但默认已有权限
	 **/
	public $isAuthorize;
	
	/** 
	 * 父权限code
	 **/
	public $parentCode;
	
	/** 
	 * 注册到权限中心的code值
	 **/
	public $permissionCode;
	
	/** 
	 * 权限名称
	 **/
	public $permissionName;	
}
?>