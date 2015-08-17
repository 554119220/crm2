<?php

/**
 * 交易详细信息
 * @author auto create
 */
class Trade
{
	
	/** 
	 * Acookie订单唯一ID。
	 **/
	public $acookieId;
	
	/** 
	 * 卖家手工调整金额，精确到2位小数，单位：元。如：200.07，表示：200元7分。来源于订单价格修改，如果有多笔子订单的时候，这个为0，单笔的话则跟[order].adjust_fee一样
	 **/
	public $adjustFee;
	
	/** 
	 * 买家的支付宝id号，在UIC中有记录，买家支付宝的唯一标示，不因为买家更换Email账号而改变。
	 **/
	public $alipayId;
	
	/** 
	 * 支付宝交易号，如：2009112081173831
	 **/
	public $alipayNo;
	
	/** 
	 * 付款时使用的支付宝积分的额度,单位分，比如返回1，则为1分钱
	 **/
	public $alipayPoint;
	
	/** 
	 * 创建交易接口成功后，返回的支付url
	 **/
	public $alipayUrl;
	
	/** 
	 * 淘宝下单成功了,但由于某种错误支付宝订单没有创建时返回的信息。taobao.trade.add接口专用
	 **/
	public $alipayWarnMsg;
	
	/** 
	 * 区域id，代表订单下单的区位码，区位码是通过省市区转换而来，通过区位码能精确到区内的划分，比如310012是杭州市西湖区华星路
	 **/
	public $areaId;
	
	/** 
	 * 物流到货时效截单时间，格式 HH:mm
	 **/
	public $arriveCutTime;
	
	/** 
	 * 物流到货时效，单位天
	 **/
	public $arriveInterval;
	
	/** 
	 * 同步到卖家库的时间，taobao.trades.sold.incrementv.get接口返回此字段
	 **/
	public $asyncModified;
	
	/** 
	 * 交易中剩余的确认收货金额（这个金额会随着子订单确认收货而不断减少，交易成功后会变为零）。精确到2位小数;单位:元。如:200.07，表示:200 元7分
	 **/
	public $availableConfirmFee;
	
	/** 
	 * 买家支付宝账号
	 **/
	public $buyerAlipayNo;
	
	/** 
	 * 买家下单的地区
	 **/
	public $buyerArea;
	
	/** 
	 * 买家货到付款服务费。精确到2位小数;单位:元。如:12.07，表示:12元7分
	 **/
	public $buyerCodFee;
	
	/** 
	 * 买家邮件地址
	 **/
	public $buyerEmail;
	
	/** 
	 * 买家备注旗帜（与淘宝网上订单的买家备注旗帜对应，只有买家才能查看该字段）红、黄、绿、蓝、紫 分别对应 1、2、3、4、5
	 **/
	public $buyerFlag;
	
	/** 
	 * 买家下单的IP信息，仅供taobao.trade.fullinfo.get查询返回。需要对返回结果进行Base64解码。
	 **/
	public $buyerIp;
	
	/** 
	 * 买家备注（与淘宝网上订单的买家备注对应，只有买家才能查看该字段）
	 **/
	public $buyerMemo;
	
	/** 
	 * 买家留言
	 **/
	public $buyerMessage;
	
	/** 
	 * 买家昵称
	 **/
	public $buyerNick;
	
	/** 
	 * 买家获得积分,返点的积分。格式:100;单位:个。返点的积分要交易成功之后才能获得。
	 **/
	public $buyerObtainPointFee;
	
	/** 
	 * 买家是否已评价。可选值:true(已评价),false(未评价)。如买家只评价未打分，此字段仍返回false
	 **/
	public $buyerRate;
	
	/** 
	 * 买家可以通过此字段查询是否当前交易可以评论，can_rate=true可以评价，false则不能评价。
	 **/
	public $canRate;
	
	/** 
	 * 货到付款服务费。精确到2位小数;单位:元。如:12.07，表示:12元7分。
	 **/
	public $codFee;
	
	/** 
	 * 货到付款物流状态。初始状态 NEW_CREATED,接单成功 ACCEPTED_BY_COMPANY,接单失败 REJECTED_BY_COMPANY,接单超时 RECIEVE_TIMEOUT,揽收成功 TAKEN_IN_SUCCESS,揽收失败 TAKEN_IN_FAILED,揽收超时 TAKEN_TIMEOUT,签收成功 SIGN_IN,签收失败 REJECTED_BY_OTHER_SIDE,订单等待发送给物流公司 WAITING_TO_BE_SENT,用户取消物流订单 CANCELED
	 **/
	public $codStatus;
	
	/** 
	 * 交易佣金。精确到2位小数;单位:元。如:200.07，表示:200元7分
	 **/
	public $commissionFee;
	
	/** 
	 * 物流发货时效，单位小时
	 **/
	public $consignInterval;
	
	/** 
	 * 卖家发货时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $consignTime;
	
	/** 
	 * 交易创建时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $created;
	
	/** 
	 * 使用信用卡支付金额数
	 **/
	public $creditCardFee;
	
	/** 
	 * 建议使用trade.promotion_details查询系统优惠系统优惠金额（如打折，VIP，满就送等），精确到2位小数，单位：元。如：200.07，表示：200元7分
	 **/
	public $discountFee;
	
	/** 
	 * 交易结束时间。交易成功时间(更新交易状态为成功的同时更新)/确认收货时间或者交易关闭时间 。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $endTime;
	
	/** 
	 * 商家的预计发货时间
	 **/
	public $estConTime;
	
	/** 
	 * 车牌号码
	 **/
	public $etPlateNumber;
	
	/** 
	 * 天猫汽车服务预约时间
	 **/
	public $etSerTime;
	
	/** 
	 * 电子凭证预约门店地址
	 **/
	public $etShopName;
	
	/** 
	 * 电子凭证核销门店地址
	 **/
	public $etVerifiedShopName;
	
	/** 
	 * 电子凭证的垂直信息
	 **/
	public $eticketExt;
	
	/** 
	 * 快递代收款。精确到2位小数;单位:元。如:212.07，表示:212元7分
	 **/
	public $expressAgencyFee;
	
	/** 
	 * 判断订单是否有买家留言，有买家留言返回true，否则返回false
	 **/
	public $hasBuyerMessage;
	
	/** 
	 * 是否包含邮费。与available_confirm_fee同时使用。可选值:true(包含),false(不包含)
	 **/
	public $hasPostFee;
	
	/** 
	 * 订单中是否包含运费险订单，如果包含运费险订单返回true，不包含运费险订单，返回false
	 **/
	public $hasYfx;
	
	/** 
	 * 出生日期
	 **/
	public $hkBirthday;
	
	/** 
	 * 证件号码
	 **/
	public $hkCardCode;
	
	/** 
	 * 证件类型001代表港澳通行证类型、002代表入台证003代表护照
	 **/
	public $hkCardType;
	
	/** 
	 * 中文名
	 **/
	public $hkChinaName;
	
	/** 
	 * 拼音名
	 **/
	public $hkEnName;
	
	/** 
	 * 航班飞行时间
	 **/
	public $hkFlightDate;
	
	/** 
	 * 航班号
	 **/
	public $hkFlightNo;
	
	/** 
	 * 性别M: 男性，F: 女性
	 **/
	public $hkGender;
	
	/** 
	 * 提货地点
	 **/
	public $hkPickup;
	
	/** 
	 * 提货地点id
	 **/
	public $hkPickupId;
	
	/** 
	 * 商品字符串编号(注意：iid近期即将废弃，请用num_iid参数)
	 **/
	public $iid;
	
	/** 
	 * 发票类型（ 1 电子发票 2  纸质发票 ）
	 **/
	public $invoiceKind;
	
	/** 
	 * 发票抬头
	 **/
	public $invoiceName;
	
	/** 
	 * 发票类型
	 **/
	public $invoiceType;
	
	/** 
	 * 是否3D交易
	 **/
	public $is3D;
	
	/** 
	 * 表示是否是品牌特卖（常规特卖，不包括特卖惠和特实惠）订单，如果是返回true，如果不是返回false。当此字段与is_force_wlb均为true时，订单强制物流宝发货。
	 **/
	public $isBrandSale;
	
	/** 
	 * 表示订单交易是否含有对应的代销采购单。如果该订单中存在一个对应的代销采购单，那么该值为true；反之，该值为false。
	 **/
	public $isDaixiao;
	
	/** 
	 * 订单是否强制使用物流宝发货。当此字段与is_brand_sale均为true时，订单强制物流宝发货。此字段为false时，该订单根据流转规则设置可以使用物流宝或者常规方式发货
	 **/
	public $isForceWlb;
	
	/** 
	 * 是否保障速递，如果为true，则为保障速递订单，使用线下联系发货接口发货，如果未false，则该订单非保障速递，根据卖家设置的订单流转规则可使用物流宝或者常规物流发货。
	 **/
	public $isLgtype;
	
	/** 
	 * 是否是多次发货的订单如果卖家对订单进行多次发货，则为true否则为false
	 **/
	public $isPartConsign;
	
	/** 
	 * 表示订单交易是否网厅订单。 如果该订单是网厅订单，那么该值为true；反之，该值为false。
	 **/
	public $isWt;
	
	/** 
	 * 次日达订单送达时间
	 **/
	public $lgAging;
	
	/** 
	 * 次日达，三日达等送达类型
	 **/
	public $lgAgingType;
	
	/** 
	 * 订单出现异常问题的时候，给予用户的描述,没有异常的时候，此值为空
	 **/
	public $markDesc;
	
	/** 
	 * 交易修改时间(用户对订单的任何修改都会更新此字段)。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $modified;
	
	/** 
	 * 商品购买数量。取值范围：大于零的整数,对于一个trade对应多个order的时候（一笔主订单，对应多笔子订单），num=0，num是一个跟商品关联的属性，一笔订单对应多比子订单的时候，主订单上的num无意义。
	 **/
	public $num;
	
	/** 
	 * 商品数字编号
	 **/
	public $numIid;
	
	/** 
	 * 卡易售垂直表信息，去除下单ip之后的结果
	 **/
	public $nutFeature;
	
	/** 
	 * 导购宝=crm
	 **/
	public $o2o;
	
	/** 
	 * 导购宝提货方式，inshop：店内提货，online：线上发货
	 **/
	public $o2oDelivery;
	
	/** 
	 * 导购员id
	 **/
	public $o2oGuideId;
	
	/** 
	 * 导购员名称
	 **/
	public $o2oGuideName;
	
	/** 
	 * 外部订单号
	 **/
	public $o2oOutTradeId;
	
	/** 
	 * 导购员门店id
	 **/
	public $o2oShopId;
	
	/** 
	 * 导购门店名称
	 **/
	public $o2oShopName;
	
	/** 
	 * 订单列表
	 **/
	public $orders;
	
	/** 
	 * 付款时间。格式:yyyy-MM-dd HH:mm:ss。订单的付款时间即为物流订单的创建时间。
	 **/
	public $payTime;
	
	/** 
	 * 实付金额。精确到2位小数;单位:元。如:200.07，表示:200元7分
	 **/
	public $payment;
	
	/** 
	 * 天猫点券卡实付款金额,单位分
	 **/
	public $pccAf;
	
	/** 
	 * 商品图片绝对途径
	 **/
	public $picPath;
	
	/** 
	 * 买家使用积分,下单时生成，且一直不变。格式:100;单位:个.
	 **/
	public $pointFee;
	
	/** 
	 * 邮费。精确到2位小数;单位:元。如:200.07，表示:200元7分
	 **/
	public $postFee;
	
	/** 
	 * 商品价格。精确到2位小数；单位：元。如：200.07，表示：200元7分
	 **/
	public $price;
	
	/** 
	 * 交易促销详细信息
	 **/
	public $promotion;
	
	/** 
	 * 优惠详情
	 **/
	public $promotionDetails;
	
	/** 
	 * 买家实际使用积分（扣除部分退款使用的积分），交易完成后生成（交易成功或关闭），交易未完成时该字段值为0。格式:100;单位:个
	 **/
	public $realPointFee;
	
	/** 
	 * 卖家实际收到的支付宝打款金额（由于子订单可以部分确认收货，这个金额会随着子订单的确认收货而不断增加，交易成功后等于买家实付款减去退款金额）。精确到2位小数;单位:元。如:200.07，表示:200元7分
	 **/
	public $receivedPayment;
	
	/** 
	 * 收货人的详细地址
	 **/
	public $receiverAddress;
	
	/** 
	 * 收货人的所在城市<br>注：因为国家对于城市和地区的划分的有：省直辖市和省直辖县级行政区（区级别的）划分的，淘宝这边根据这个差异保存在不同字段里面比如：广东广州：广州属于一个直辖市是放在的receiver_city的字段里面；而河南济源：济源属于省直辖县级行政区划分，是区级别的，放在了receiver_district里面<br>建议：程序依赖于城市字段做物流等判断的操作，最好加一个判断逻辑：如果返回值里面只有receiver_district参数，该参数作为城市
	 **/
	public $receiverCity;
	
	/** 
	 * 收货人国籍
	 **/
	public $receiverCountry;
	
	/** 
	 * 收货人的所在地区<br>注：因为国家对于城市和地区的划分的有：省直辖市和省直辖县级行政区（区级别的）划分的，淘宝这边根据这个差异保存在不同字段里面比如：广东广州：广州属于一个直辖市是放在的receiver_city的字段里面；而河南济源：济源属于省直辖县级行政区划分，是区级别的，放在了receiver_district里面<br>建议：程序依赖于城市字段做物流等判断的操作，最好加一个判断逻辑：如果返回值里面只有receiver_district参数，该参数作为城市
	 **/
	public $receiverDistrict;
	
	/** 
	 * 收货人的手机号码
	 **/
	public $receiverMobile;
	
	/** 
	 * 收货人的姓名
	 **/
	public $receiverName;
	
	/** 
	 * 收货人的电话号码
	 **/
	public $receiverPhone;
	
	/** 
	 * 收货人的所在省份
	 **/
	public $receiverState;
	
	/** 
	 * 收货人街道地址
	 **/
	public $receiverTown;
	
	/** 
	 * 收货人的邮编
	 **/
	public $receiverZip;
	
	/** 
	 * 卖家支付宝账号
	 **/
	public $sellerAlipayNo;
	
	/** 
	 * 卖家是否可以对订单进行评价
	 **/
	public $sellerCanRate;
	
	/** 
	 * 卖家货到付款服务费。精确到2位小数;单位:元。如:12.07，表示:12元7分。卖家不承担服务费的订单：未发货的订单获取服务费为0，发货后就能获取到正确值。
	 **/
	public $sellerCodFee;
	
	/** 
	 * 卖家邮件地址
	 **/
	public $sellerEmail;
	
	/** 
	 * 卖家备注旗帜（与淘宝网上订单的卖家备注旗帜对应，只有卖家才能查看该字段）红、黄、绿、蓝、紫 分别对应 1、2、3、4、5
	 **/
	public $sellerFlag;
	
	/** 
	 * 卖家备注（与淘宝网上订单的卖家备注对应，只有卖家才能查看该字段）
	 **/
	public $sellerMemo;
	
	/** 
	 * 卖家手机
	 **/
	public $sellerMobile;
	
	/** 
	 * 卖家姓名
	 **/
	public $sellerName;
	
	/** 
	 * 卖家昵称
	 **/
	public $sellerNick;
	
	/** 
	 * 卖家电话
	 **/
	public $sellerPhone;
	
	/** 
	 * 卖家是否已评价。可选值:true(已评价),false(未评价)
	 **/
	public $sellerRate;
	
	/** 
	 * 订单将在此时间前发出，主要用于预售订单
	 **/
	public $sendTime;
	
	/** 
	 * 服务子订单列表
	 **/
	public $serviceOrders;
	
	/** 
	 * 物流标签
	 **/
	public $serviceTags;
	
	/** 
	 * 创建交易时的物流方式（交易完成前，物流方式有可能改变，但系统里的这个字段一直不变）。可选值：free(卖家包邮),post(平邮),express(快递),ems(EMS),virtual(虚拟发货)，25(次日必达)，26(预约配送)。
	 **/
	public $shippingType;
	
	/** 
	 * 线下自提门店编码
	 **/
	public $shopCode;
	
	/** 
	 * 物流运单号
	 **/
	public $sid;
	
	/** 
	 * 交易快照详细信息
	 **/
	public $snapshot;
	
	/** 
	 * 交易快照地址
	 **/
	public $snapshotUrl;
	
	/** 
	 * 交易状态。可选值:    * TRADE_NO_CREATE_PAY(没有创建支付宝交易)    * WAIT_BUYER_PAY(等待买家付款)    * SELLER_CONSIGNED_PART(卖家部分发货)    * WAIT_SELLER_SEND_GOODS(等待卖家发货,即:买家已付款)    * WAIT_BUYER_CONFIRM_GOODS(等待买家确认收货,即:卖家已发货)    * TRADE_BUYER_SIGNED(买家已签收,货到付款专用)    * TRADE_FINISHED(交易成功)    * TRADE_CLOSED(付款以后用户退款成功，交易自动关闭)    * TRADE_CLOSED_BY_TAOBAO(付款以前，卖家或买家主动关闭交易)    * PAY_PENDING(国际信用卡支付付款确认中)    * WAIT_PRE_AUTH_CONFIRM(0元购合约中)
	 **/
	public $status;
	
	/** 
	 * 分阶段付款的已付金额（万人团订单已付金额）
	 **/
	public $stepPaidFee;
	
	/** 
	 * 分阶段付款的订单状态（例如万人团订单等），目前有三返回状态FRONT_NOPAID_FINAL_NOPAID(定金未付尾款未付)，FRONT_PAID_FINAL_NOPAID(定金已付尾款未付)，FRONT_PAID_FINAL_PAID(定金和尾款都付)
	 **/
	public $stepTradeStatus;
	
	/** 
	 * 交易编号 (父订单的交易编号)
	 **/
	public $tid;
	
	/** 
	 * 超时到期时间。格式:yyyy-MM-dd HH:mm:ss。业务规则：前提条件：只有在买家已付款，卖家已发货的情况下才有效如果申请了退款，那么超时会落在子订单上；比如说3笔ABC，A申请了，那么返回的是BC的列表, 主定单不存在如果没有申请过退款，那么超时会挂在主定单上；比如ABC，返回主定单，ABC的超时和主定单相同
	 **/
	public $timeoutActionTime;
	
	/** 
	 * 交易标题，以店铺名作为此标题的值。注:taobao.trades.get接口返回的Trade中的title是商品名称
	 **/
	public $title;
	
	/** 
	 * 商品金额（商品价格乘以数量的总金额）。精确到2位小数;单位:元。如:200.07，表示:200元7分
	 **/
	public $totalFee;
	
	/** 
	 * 交易扩展表信息
	 **/
	public $tradeExt;
	
	/** 
	 * 交易内部来源。WAP(手机);HITAO(嗨淘);TOP(TOP平台);TAOBAO(普通淘宝);JHS(聚划算)一笔订单可能同时有以上多个标记，则以逗号分隔
	 **/
	public $tradeFrom;
	
	/** 
	 * 交易备注。
	 **/
	public $tradeMemo;
	
	/** 
	 * 交易外部来源：ownshop(商家官网)
	 **/
	public $tradeSource;
	
	/** 
	 * 交易类型列表，同时查询多种交易类型可用逗号分隔。默认同时查询guarantee_trade, auto_delivery, ec, cod的4种交易类型的数据 可选值 fixed(一口价) auction(拍卖) guarantee_trade(一口价、拍卖) auto_delivery(自动发货) independent_simple_trade(旺店入门版交易) independent_shop_trade(旺店标准版交易) ec(直冲) cod(货到付款) fenxiao(分销) game_equipment(游戏装备) shopex_trade(ShopEX交易) netcn_trade(万网交易) external_trade(统一外部交易)o2o_offlinetrade（O2O交易）step (万人团)nopaid(无付款订单)pre_auth_type(预授权0元购机交易)
	 **/
	public $type;
	
	/** 
	 * 订单的运费险，单位为元
	 **/
	public $yfxFee;
	
	/** 
	 * 运费险支付号
	 **/
	public $yfxId;
	
	/** 
	 * 运费险类型
	 **/
	public $yfxType;
	
	/** 
	 * 在返回的trade对象上专门增加一个字段zero_purchase来区分，这个为true的就是0元购机预授权交易
	 **/
	public $zeroPurchase;	
}
?>