<?php

/**
 * 图书类目导入返回结果
 * @author auto create
 */
class ProductBooks
{
	
	/** 
	 * 作者/著者
	 **/
	public $author;
	
	/** 
	 * 条形码，13位，9787开头
	 **/
	public $barCode;
	
	/** 
	 * 完整的图书名称
	 **/
	public $bookName;
	
	/** 
	 * 类目id
	 **/
	public $categoryId;
	
	/** 
	 * ISBN号
	 **/
	public $isbn;
	
	/** 
	 * 图书价格，若有多个，则以';'号分隔
	 **/
	public $price;	
}
?>