<?php
if (!defined('IN_ECS')) die('Hacking attempt');


//导航
/*$modules['01_order']        = 'order.php?act=menu';
$modules['02_shipping']     = 'shipping.php?act=menu';
$modules['03_after_sale']   = 'after_sale.php?act=menu';
$modules['04_goods']        = 'goods.php?act=menu';
$modules['05_users']        = 'users.php?act=menu';
$modules['06_service']      = 'service.php?act=menu';
$modules['07_storage']      = 'storage.php?act=menu';
$modules['08_finance']      = 'finance.php?act=menu';
$modules['09_performance']  = 'performance.php?act=menu';
$modules['10_checking']     = 'checking.php?act=menu';
$modules['11_report_forms'] = 'report_forms.php?act=menu';
$modules['12_system']       = 'system.php?act=menu';
 */
/* 订单 */
$modules['01_order']['01_check']['01_current']   = 'order.php?act=current';
$modules['01_order']['01_check']['02_history']   = 'order.php?act=history';
$modules['01_order']['01_check']['03_temporary'] = 'order.php?act=temporary';

$modules['01_order']['02_scheduling']['01_pending']   = 'order.php?act=pending';
$modules['01_order']['02_scheduling']['02_processed'] = 'order.php?act=processed';
$modules['01_order']['02_scheduling']['03_group']     = 'order.php?act=group';

$modules['01_order']['02_abnormal']['01_abnormal'] = 'order.php?act=abnormal';
$modules['01_order']['02_abnormal']['02_failed']   = 'order.php?act=failed';
/* 发货 */

/* 售后 */

/* 商品 */

/* 顾客 */

/* 服务 */

/* 仓库 */

/* 财务 */

/* 绩效 */

/* 对账 */

/* 报表 */

/* 系统 */








/*
$modules['01_order']['02_sales_volume'] = 'stat.php?act=sales_volume';
$modules['01_order']['04_rebuy']        = 'stat.php?act=rebuy';
$modules['01_order']['05_manual']       = 'manual.php?act=add';

//顾客管理
$modules['01_members']['00_users_search'] = 'search.php';
$modules['01_members']['01_users_list']   = 'users.php?act=list';
$modules['01_members']['02_users_add']    = 'users.php?act=add';
$modules['01_members']['03_users_file']   = 'users.php?act=file';
$modules['01_members']['04_users_batch']  = 'users.php?act=batch';
$modules['01_members']['05_sms_send']     = 'sms.php?act=display_send_ui';
$modules['01_members']['06_intention']    = 'users.php?act=intention';
$modules['01_members']['07_part_transfer']    = 'users.php?act=part_transfer';

//服务管理
$modules['02_serve']['01_serve_list']  = 'serve.php?act=list';
$modules['02_serve']['02_serve_check'] = 'serve.php?act=check';
$modules['02_serve']['05_forecast']    = 'stat.php?act=forecast';  //客服销量
$modules['02_serve']['06_birth_list']  = 'birth.php?act=list';

//订单管理
//$modules['03_order']['01_add_order']         = 'order.php?act=add';
$modules['03_order']['02_order_list']        = 'order.php?act=list';
$modules['03_order']['03_order_query']       = 'order.php?act=order_query';
$modules['03_order']['04_merge_order']       = 'order.php?act=merge';
$modules['03_order']['05_edit_order_print']  = 'order.php?act=templates';
$modules['03_order']['06_undispose_booking'] = 'order.php?act=list_all';
$modules['03_order']['07_delivery_order']    = 'order.php?act=delivery_list';
$modules['03_order']['08_back_order']        = 'order.php?act=back_list';
$modules['03_order']['09_ordersyn']          = 'order.php?act=ordersyn';

//库存管理
$modules['04_goods']['00_packing_list']      = 'packing.php?act=list';
$modules['04_goods']['01_goods_list']        = 'goods.php?act=list';
$modules['04_goods']['02_goods_add']         = 'goods.php?act=add';
$modules['04_goods']['03_category_list']     = 'category.php?act=list';
$modules['04_goods']['04_goods_brand_list']  = 'brand.php?act=list';
$modules['04_goods']['05_batch_add']         = 'goods_batch.php?act=add';
$modules['04_goods']['06_batch_edit']        = 'goods_batch.php?act=select';
$modules['04_goods']['07_goods_stock']       = 'stock.php?act=stock_list';
$modules['04_goods']['08_goods_type']        = 'goods_type.php?act =manage';
$modules['04_goods']['09_stock_add']         = 'stock.php?act=stock_add';
$modules['04_goods']['11_gift_buy']          = 'gift_buy.php?act=list';
$modules['04_goods']['12_freecard_list']     = 'freecard.php?act=list';

// 营销管理
$modules['041_marketing']['01_integral'] = 'integral.php?act=list';

//报表管理
$modules['05_stats']['report_guest']         = 'guest_stats.php?act=list';
$modules['05_stats']['report_order']         = 'order_stats.php?act=list';
$modules['05_stats']['report_sell']          = 'sale_general.php?act=list';
$modules['05_stats']['sale_list']            = 'sale_list.php?act=list';
$modules['05_stats']['sell_stats']           = 'sale_order.php?act=goods_num';
$modules['05_stats']['trend']                = 'chart_stats.php?act=trend';
$modules['05_stats']['spread']               = 'chart_stats.php?act=spread';
 */

//系统设置
$modules['06_system']['01_crm_config']       = 'shop_config.php?act=list_edit';
$modules['06_system']['02_admin_list']       = 'privilege.php?act=list';
$modules['06_system']['03_role_list']        = 'role.php?act=list';
$modules['06_system']['04_db_back']          = 'database.php?act=backup';
$modules['06_system']['05_db_optimize']      = 'database.php?act=optimize';
$modules['06_system']['06_goods_recycle']    = 'goods.php?act=trash';
$modules['06_system']['07_goods_export']     = 'goods_export.php?act=goods_export';
$modules['06_system']['08_passwd']           = 'privilege.php?act=editpasswd';
$modules['06_system']['09_option_manage']    = 'option_manage.php?act=default';
?>
