<?php

/**
 * ECSHOP 权限对照表
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $purview[Author: sunxiaodong $purview[
 * $purview[Id: inc_priv.php 15503 2008-12-24 09:22:45Z sunxiaodong $purview[
*/

if (!defined('IN_ECS')) die('Hacking attempt');

//统计信息
$purview['01_from_where']   = 'from_where';
$purview['02_sales_volume'] = 'sales_volume';
$purview['rebuy']           = 'rebuy';
$purview['manual']          = 'manual';

//顾客管理
$purview['01_users_list']           = 'users_list';  //array('users_list', 'users_add', 'users_file');
$purview['02_users_add']            = 'users_add';
$purview['03_users_file']           = 'users_file';
$purview['04_users_batch']          = 'users_batch';
$purview['02_sms_my_info']          = 'my_info';
$purview['03_sms_send']             = 'sms_send';
$purview['04_sms_charge']           = 'sms_charge';
$purview['05_sms_send_history']     = 'send_history';
$purview['06_intention']            = 'intention';
//$purview['06_sms_charge_history'] = 'charge_history';
$purview['07_part_transfer']        = 'part_transfer';

//服务管理
$purview['01_serve_list']         = 'serve_list';
$purview['02_serve_check']        = 'serve_check';
$purview['06_birth_list']         = 'birth';

//订单管理
$purview['01_add_order']         = 'add_order';
$purview['02_order_list']        = 'order_list';
$purview['03_order_query']       = 'order_query';
$purview['04_merge_order']       = 'merge_order';
$purview['05_edit_order_print']  = 'edit_order_print';
$purview['06_undispose_booking'] = 'undispose_booking';
$purview['07_delivery_order']    = 'delivery_order';
$purview['08_back_order']        = 'back_order';
$purview['09_ordersyn']          = 'ordersyn';
$purview['11_gift_buy']          = 'gift_buy';

//库存管理
$purview['00_packing_list']     = 'packing_manage';
$purview['01_goods_list']       = 'goods_list';
$purview['02_goods_add']        = 'goods_add';
$purview['03_category_list']    = 'category_list';
$purview['04_goods_brand_list'] = 'goods_brand_list';
$purview['05_batch_add']        = 'batch_add';
$purview['06_batch_edit']       = 'batch_edit';
$purview['07_goods_stock']      = 'stock_list';
$purview['08_goods_type']       = 'attr_manage';   //商品属性
$purview['09_stock_add']        = 'stock_add';
$purview['10_stock_edit']       = 'stock_edit';
$purview['11_gift_buy']         = 'gift_buy';
$purview['12_freecard_list']    = 'freecard_list';

// 营销管理
$purview['041_marketing'] = 'marketing';
$purview['01_integral']   = 'integral';

//报表管理
$purview['report_guest']              = 'report_guest';
$purview['report_order']              = 'report_order';
$purview['report_sell']               = 'report_sell';
$purview['sale_list']                 = 'sale_list';
$purview['sell_stats']                = 'sell_stats';
$purview['trend']                     = 'trend';
$purview['spread']                    = 'spread';

//系统设置
$purview['01_crm_config']             = 'crm_config';
$purview['02_admin_list']             = 'admin_list';
$purview['03_role_list']              = 'role_list';
$purview['04_db_back']                = 'db_backup';
$purview['05_db_optimize']            = 'db_optimize';
$purview['06_goods_recycle']          = 'goods_recycle';
$purview['07_goods_export']           = 'goods_export';
$purview['09_option_manage']          = 'option_manage';



/*
//顾客管理
$purview['01_users_list']             = 'users_list';
$purview['02_users_add']              = 'users_add';
$purview['03_users_file']             = 'user_file';
$purview['04_users_drop']             = 'users_drop';
$purview['05_users_edit'];
$purview['06_users_check'];

//服务管理
$purview['01_serve_list']        = 'serve_list';
$purview['02_serve_add']         = 'serve_add';

//订单管理
$purview['order_drop']            = 'order_drop';
$purview['02_order_query']        = 'order_query';
$purview['order_edit']            = 'order_edit';

//库存管理
$purview['goods_list']        = 'goods_list';

//报表统计
$purview['customer_st'];

//系统设置
$purview['crm_config']                = 'crm_config';
$purview['admin_log']                 = 'admin_log';
$purview['admin_add']                 = 'admin_add';
$purview['admin_edit']                = 'admin_edit';
$purview['admin_drop']                = 'admin_drop';
$purview['role_add']                  = 'role_add';
$purview['role_drop']                 = 'role_drop';
$purview['role_edit']                 = 'role_edit';
$purview['log_clear']                 = 'log_clear';
$purview['db_back']                   = 'db_back';
$purview['db_renew']                  = 'db_renew';
$purview['db_optimize']               = 'db_optimize';
$purview['goods_recycle']             = 'goods_recycle';
$purview['goods_restore']             = 'goods_restore';
$purview['goods_del']                 = 'goods_del';
$purview['goods_export']              = 'goods_export';
$purview['other_opt']                 = 'other_opt';

/*
//商品管理权限
    $purview['01_goods_list']        = array('goods_manage', 'remove_back');
    $purview['02_goods_add']         = 'goods_manage';
    $purview['03_category_list']     = array('cat_manage', 'cat_drop');   //分类添加、分类转移和删除
    $purview['05_comment_manage']    = 'comment_priv';
    $purview['06_goods_brand_list']  = 'brand_manage';
    $purview['08_goods_type']        = 'attr_manage';   //商品属性
    $purview['11_goods_trash']       = array('goods_manage', 'remove_back');
    $purview['12_batch_pic']         = 'picture_batch';
    $purview['13_batch_add']         = 'goods_batch';
    $purview['14_goods_export']      = 'goods_export';
    $purview['15_batch_edit']        = 'goods_batch';
    $purview['16_goods_script']      = 'gen_goods_script';
    $purview['17_tag_manage']        = 'tag_manage';
    $purview['50_virtual_card_list'] = 'virualcard';
    $purview['51_virtual_card_add']  = 'virualcard';
    $purview['52_virtual_card_change'] = 'virualcard';
    $purview['goods_auto']           = 'goods_auto';

//促销管理权限
    $purview['02_snatch_list']       = 'snatch_manage';
    $purview['04_bonustype_list']    = 'bonus_manage';
    $purview['06_pack_list']         = 'pack';
    $purview['07_card_list']         = 'card_manage';
    $purview['08_group_buy']         = 'group_by';
    $purview['09_topic']             = 'topic_manage';
    $purview['10_auction']           = 'auction';
    $purview['12_favourable']        = 'favourable';
    $purview['13_wholesale']         = 'whole_sale';
    $purview['14_package_list']      = 'package_manage';
//  $purview['02_snatch_list']       = 'gift_manage';  //赠品管理
    $purview['15_exchange_goods']    = 'exchange_goods';  //赠品管理

//文章管理权限
    $purview['02_articlecat_list']   = 'article_cat';
    $purview['03_article_list']      = 'article_manage';
    $purview['article_auto']         = 'article_auto';
    $purview['vote_list']            = 'vote_priv';

//会员管理权限
    $purview['03_users_list']        = 'users_manage';
    $purview['04_users_add']         = 'users_manage';
    $purview['05_user_rank_list']    = 'user_rank';
    $purview['09_user_account']      = 'surplus_manage';
    $purview['06_list_integrate']    = 'integrate_users';
    $purview['08_unreply_msg']       = 'feedback_priv';
    $purview['10_user_account_manage'] = 'account_manage';

//权限管理
    $purview['admin_logs']           = array('logs_manage', 'logs_drop');
    $purview['admin_list']           = array('admin_manage', 'admin_drop', 'allot_priv');
    $purview['agency_list']          = 'agency_manage';
    $purview['suppliers_list']          = 'suppliers_manage'; // 供货商
    $purview['admin_role']             = 'role_manage';

//商店设置权限
    $purview['01_shop_config']       = 'shop_config';
    $purview['shop_authorized']       = 'shop_authorized';
    $purview['shp_webcollect']            = 'webcollect_manage';
    $purview['02_payment_list']      = 'payment';
    $purview['03_shipping_list']     = array('ship_manage','shiparea_manage');
    $purview['04_mail_settings']     = 'shop_config';
    $purview['05_area_list']         = 'area_manage';
    $purview['07_cron_schcron']      = 'cron';
    $purview['08_friendlink_list']   = 'friendlink';
    $purview['sitemap']              = 'sitemap';
    $purview['check_file_priv']      = 'file_priv';
    $purview['captcha_manage']       = 'shop_config';
    $purview['file_check']           = 'file_check';
    $purview['navigator']            = 'navigator';
    $purview['flashplay']            = 'flash_manage';
    $purview['ucenter_setup']        = 'integrate_users';
    $purview['021_reg_fields']       = 'reg_fields';

//广告管理
    $purview['z_clicks_stats']       = 'ad_manage';
    $purview['ad_position']          = 'ad_manage';
    $purview['ad_list']              = 'ad_manage';



//报表统计权限
    $purview['flow_stats']           = 'client_flow_stats';
    $purview['report_guest']         = 'client_flow_stats';
    $purview['report_users']         = 'client_flow_stats';
    $purview['visit_buy_per']        = 'client_flow_stats';
    $purview['searchengine_stats']   = 'client_flow_stats';
    $purview['report_order']         = 'sale_order_stats';
    $purview['report_sell']          = 'sale_order_stats';
    $purview['sale_list']            = 'sale_order_stats';
    $purview['sell_stats']           = 'sale_order_stats';


//模板管理
    $purview['02_template_select']   = 'template_select';
    $purview['03_template_setup']    = 'template_setup';
    $purview['04_template_library']  = 'library_manage';
    $purview['05_edit_languages']    = 'lang_edit';
    $purview['06_template_backup']   = 'backup_setting';
    $purview['mail_template_manage'] = 'mail_template';

//数据库管理
    $purview['02_db_manage']         = array('db_backup', 'db_renew');
    $purview['03_db_optimize']       = 'db_optimize';
    $purview['04_sql_query']         = 'sql_query';
    $purview['convert']              = 'convert';

//短信管理
    $purview['02_sms_my_info']       = 'my_info';
    $purview['03_sms_send']          = 'sms_send';
    $purview['04_sms_charge']        = 'sms_charge';
    $purview['05_sms_send_history']  = 'send_history';
    $purview['06_sms_charge_history']= 'charge_history';

//推荐管理
    $purview['affiliate']            = 'affiliate';
    $purview['affiliate_ck']         = 'affiliate_ck';

//邮件群发管理
    $purview['attention_list']       = 'attention_list';
    $purview['email_list']           = 'email_list';
    $purview['magazine_list']        = 'magazine_list';
    $purview['view_sendlist']        = 'view_sendlist';
 */
?>
