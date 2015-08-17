<?php

/**
 * 门票商品电子凭证信息
 * @author auto create
 */
class TicketEtc
{
	
	/** 
	 * 商品电子凭证是否关联本地商户-在门票商品为电子凭证时必选
	 **/
	public $associationStatus;
	
	/** 
	 * 商品电子凭证的自动退款比例-在门票商品为电子凭证时必选
	 **/
	public $autoRefund;
	
	/** 
	 * 商品电子凭证的码商-在门票商品为电子凭证时必选
	 **/
	public $merchantId;
	
	/** 
	 * 商品电子凭证的码商名-在门票商品为电子凭证时必选
	 **/
	public $merchantNick;
	
	/** 
	 * 商品电子凭证的码商-在门票商品为电子凭证时必选
	 **/
	public $networkId;
	
	/** 
	 * 商品电子凭证的过期退款比例-在门票商品为电子凭证时必选
	 **/
	public $overduePay;
	
	/** 
	 * 商品电子凭证是否核销打款-在门票商品为电子凭证时必选
	 **/
	public $verificationPay;	
}
?>