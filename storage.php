<?php
/**
 * ECSHOP 订单管理
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: yehuaixiao $
 * $Id: order.php 17157 2010-05-13 06:02:31Z yehuaixiao $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_index_info.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_common.php');

$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('goods'), $db, 'goods_id', 'goods_name');
$file = strstr(basename($_SERVER['PHP_SELF']), '.', true);

date_default_timezone_set('Asia/Shanghai');

$smarty->assign('dst_script', 'storage');

/*-- 仓库子菜单 --*/
if ($_REQUEST['act'] == 'menu')
{
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);

    if(admin_priv('mod_stock_quantity','',false) || admin_priv('all','',false)){
        $smarty->assign('stock_manager',true);
    }
    die($smarty->fetch('left.htm'));
}

/* 添加进货单 */
elseif($_REQUEST['act'] == 'add_receipt') {
    $res = array();
    if (isset($_REQUEST['ext']))
    {
        $file = basename($_SERVER['PHP_SELF'], '.php');
        $nav = list_nav();
        $smarty->assign('nav_2nd', $nav[1][$file]);
        $smarty->assign('nav_3rd', $nav[2]);
        $smarty->assign('file_name', $file);
        $res['left'] = $smarty->fetch('left.htm');
    }

    $smarty->assign('daytime',time());

    //查找商品的类型
    $res['main'] = $smarty->fetch('add_receipt.htm');

    die($json->encode($res));
}

/*-- 添加进货单处理 --*/
elseif($_REQUEST['act'] == 'add_new_receipt')
{
    //获取传过来的参数
    $stock_sn    = trim($_POST['stock_sn']);
    $arrival_day = strtotime(trim($_POST['arrival_day']));
    $contacter   = trim($_POST['contacter']);
    $phone       = intval(trim($_POST['phone']));
    $confirmer   = trim($_POST['confirmer']);
    $add_time    = time();
    $res         = array();

    //获取登录用户登录的id，对应crm_admin_user表
    $adder = $_SESSION['admin_id'];
    $list  = array();

    //重组传过来的参数
    foreach($_REQUEST as $key=>$v)
    {
        if($key == 'goods_sn')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['goods_sn'] = $c;
            }
        }

        if($key == 'goods_name')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['goods_name'] = $c;
            }
        }

        if($key == 'quantity')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['quantity'] = $c;
            }
        }

        if($key == 'prickle')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['prickle'] = $c;
            }
        }

        if($key == 'production_day')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['production_day'] = $c;
            }
        }

        if($key == 'expire_time') {
            foreach($v as $key=>$c) {
                $list[$key]['expire_time'] = $c;
            }
        }

        if($key == 'manufacturer')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['manufacturer'] = $c;
            }
        }
    }

    //插入进货单
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('stock').
        '(stock_sn,arrival_day,contacter,phone,confirmer,add_time,adder) VALUES ('.
        "'$stock_sn','$arrival_day','$contacter','$phone','$confirmer','$add_time','$adder')" ;

    $result = $GLOBALS['db']->query($sql_insert);

    if($result)
    {
        record_operate($sql_insert, 'stock');
        $stock_id = mysql_insert_id();

        //根据进货单插入进货单商品表
        foreach($list as $v)
        {
            if(!empty($v))
            {
                switch ($v['prickle'])
                {
                case 1:
                    $v['prickle'] = '瓶';
                    break;

                case 2:
                    $v['prickle'] = '罐';
                    break;

                case 3:
                    $v['prickle'] = '盒';
                    break;

                case 4:
                    $v['prickle'] = '件';
                    break;

                default:
                    $v['prickle'] = '瓶';
                    break;
                }

                $v['production_day'] = strtotime($v['production_day']);

                //插入进货单管理表
                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('stock_goods').
                    '(goods_sn,goods_name,quantity,production_day,manufacturer,prickle,stock_id,expire_time) VALUES ('.
                    "'$v[goods_sn]','$v[goods_name]','$v[quantity]','$v[production_day]','$v[manufacturer]'
                    ,'$v[prickle]','$stock_id','$v[expire_time]')" ;
                $result = mysql_query($sql_insert);

                if($result)
                {
                    record_operate($sql_insert, 'stock_goods');
                    $res['req_msg'] = true;
                    $res['timeout'] = 2000;
                    $res['message'] = '添加成功';
                }
                else
                {
                    $res['req_msg'] = 'true';
                    $res['message'] = '添加失败';
                    $res['timeout'] = 2000;

                    die($json->encode($res));
                }
            }
        }
    } else {
        $res['req_msg'] = 'true';
        $res['timeout'] = 2000;
        $res['message'] = '添加失败';

        die($json->encode($res));
    }

    die($json->encode($res));
}

/*--- 进货单管理 ---*/
elseif( $_REQUEST['act'] == 'manage_receipt')
{
    $res = array();

    if (isset($_REQUEST['ext']))
    {
        $file = basename($_SERVER['PHP_SELF'], '.php');
        $nav = list_nav();
        $smarty->assign('nav_2nd', $nav[1][$file]);
        $smarty->assign('nav_3rd', $nav[2]);
        $smarty->assign('file_name', $file);
        $res['left'] = $smarty->fetch('left.htm');
    }

    $smarty->assign('curr_title', '商品列表');

    // 获取进货单列表
    @$stock_list = stock_list($stock_id, $type);
    $smarty->assign('num', sprintf('（共%d条）', $stock_list['record_count']));
    $smarty->assign('stock_list', $stock_list['stock_list']);
    $smarty->assign('filter',       $stock_list['filter']);
    $smarty->assign('record_count', $stock_list['record_count']);
    $smarty->assign('page_count',   $stock_list['page_count']);
    $smarty->assign('pageprev',   $stock_list['filter']['page']-1);
    $smarty->assign('pagenext',   $stock_list['filter']['page']+1);
    $res['main'] = $smarty->fetch('manage_receipt.htm');

    die($json->encode($res));
}

/*--- 进货单详情 ---*/
elseif($_REQUEST['act'] == 'detail_stock')
{
    //查找进货单管理
    $id = isset($_GET['id']) ? $_GET['id'] : 1 ;
    $sql_select = 'SELECT stock_id, stock_sn, FROM_UNIXTIME(arrival_day, "%Y-%m-%d") arrival_day, confirmer, contacter, phone,adder,add_time,class_num FROM '.$GLOBALS['ecs']->table('stock')." WHERE stock_id=$id";
    $st_list = $GLOBALS['db']->getRow($sql_select);
    $smarty->assign('st_list',$st_list);

    //查找该订货单下的商品
    $sql_select = 'SELECT *,FROM_UNIXTIME(production_day, "%Y-%m-%d") production_day FROM '
        .$GLOBALS['ecs']->table('stock_goods')." WHERE stock_id=$id";
    $sg_list = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('sg_list',$sg_list);
    $res['main'] = $smarty->fetch('detail_stock.htm');

    die($json->encode($res));
}

/*-- 商品代号自动填充 --*/
elseif($_REQUEST['act'] == 'autonum')
{
    $num = intval($_POST['num']);
    $key = intval($_POST['key']);
    $sql_select = 'SELECT goods_name FROM '.$ecs->table('goods')." WHERE goods_sn=$num";
    $goods_name = $GLOBALS['db']->getOne($sql_select);
    $res['goodsname'] = "$goods_name";
    $res['key'] = $key;
    die($json->encode($res));
}
/*-- 编辑进货单 ---*/
elseif($_REQUEST['act'] == 'edit')
{
    $stock_id = isset($_GET['stock_id']) ? $_GET['stock_id'] : 1;

    //查找对应的订单详情；
    $sql_select = 'SELECT stock_id,stock_sn, FROM_UNIXTIME(arrival_day, "%Y-%m-%d")
        arrival_day, confirmer, contacter, phone FROM '.
        $GLOBALS['ecs']->table('stock')." WHERE stock_id=$stock_id";
    $edit_stock = $GLOBALS['db']->getRow($sql_select);
    $smarty->assign('edit_stock',$edit_stock);

    //根据订单找到对应的商品
    $sql_select = 'SELECT rec_id,goods_sn,goods_name,quantity,
        FROM_UNIXTIME(production_day, "%Y-%m-%d") production_day,manufacturer,
        prickle FROM '.$GLOBALS['ecs']->table('stock_goods')." WHERE stock_id=$stock_id ";
    $stock_list = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('stock_list',$stock_list);
    $res['main'] = $smarty->fetch('edit_receipt.htm');

    die($json->encode($res));
}

/*--- 更新订货单数据 ---*/
elseif($_REQUEST['act'] == 'update')
{
    //获取表单传过来的数据
    $stock_id = intval($_POST['stock_id']);
    $stock_sn = intval($_POST['stock_sn']);
    $arrival_day = strtotime($_POST['arrival_day']);
    $contacter = $_POST['contacter'];
    $phone = $_POST['phone'];
    $confirmer = $_POST['confirmer'];
    $res = array();

    //更新订单的数据
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock').
        " SET stock_sn='$stock_sn',arrival_day='$arrival_day',
        contacter='$contacter',phone='$phone',confirmer='$confirmer'
        WHERE stock_id='$stock_id'";
    $result = $GLOBALS['db']->query($sql_update);

    if(!$result)
    {
        $res['req_msg'] = true;
        $res['message'] = '更新失败';
        $res['timeout'] = 2000;
        die($json->encode($res));
    }

    record_operate($sql_update, 'stock');

    $list = array();
    //重组传过来的参数
    foreach($_REQUEST as $key=>$v)
    {
        if($key == 'goods_sn')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['goods_sn'] = $c;
            }
        }

        if($key == 'goods_name')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['goods_name'] = $c;
            }
        }

        if($key == 'quantity')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['quantity'] = $c;
            }
        }

        if($key == 'prickle')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['prickle'] = $c;
            }
        }

        if($key == 'production_day')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['production_day'] = $c;
            }
        }

        if($key == 'manufacturer')
        {
            foreach($v as $key=>$c)
            {
                $list[$key]['manufacturer'] = $c;
            }
        }
    }

    //根据传过来的stock_id删除 stock_goods表对应的记录，在重新插入
    $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('stock_goods').
        " WHERE stock_id=$stock_id";
    $result = $GLOBALS['db']->query($sql_delete);

    if(!$result)
    {
        $res['req_msg'] = true;
        $res['message'] = '更新失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    //遍历插入新的数据
    foreach($list as $v)
    {
        if(!empty($v))
        {
            switch ($v['prickle'])
            {
            case 1:
                $v['prickle'] = '瓶';
                break;

            case 2:
                $v['prickle'] = '罐';
                break;

            case 3:
                $v['prickle'] = '盒';
                break;

            case 4:
                $v['prickle'] = '件';
                break;

            default:
                $v['prickle'] = '瓶';
                break;
            }

            $v['production_day'] = strtotime($v['production_day']);

            //插入新进货单管理表数据
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('stock_goods').
                '(goods_sn,goods_name,quantity,production_day,manufacturer,prickle, stock_id) VALUES ('.
                "'$v[goods_sn]','$v[goods_name]','$v[quantity]','$v[production_day]','$v[manufacturer]'
                ,'$v[prickle]','$stock_id')" ;
            $result = $GLOBALS['db']->query($sql_insert);

            if(!$result)
            {
                $res['req_msg'] = true;
                $res['message'] = '更新失败';
                $res['timeout'] = 2000;

                die($json->encode($res));
            }

            record_operate($sql_insert, 'stock_goods');
        }
    }

    $res['req_msg'] = true;
    $res['message'] = '更新成功';
    $res['timeout'] = 2000;

    die($json->encode($res));
}

/*-- 仓库子菜单 --*/
elseif ($_REQUEST['act'] == 'menu')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);

    die($smarty->fetch('left.htm'));
}

/*--- 商品列表 ---*/
elseif($_REQUEST['act'] == 'goods_list') {
    $res = array ('switch_tag' => true, 'id' => isset($_REQUEST['brand_id']) ? $_REQUEST['brand_id'] : 0);
    if (isset($_REQUEST['ext'])) {
        $file = basename($_SERVER['PHP_SELF'], '.php');
        $nav = list_nav();
        $smarty->assign('nav_2nd', $nav[1][$file]);
        $smarty->assign('nav_3rd', $nav[2]);
        $smarty->assign('file_name', $file);
        $res['left'] = $smarty->fetch('left.htm');
    }

    $goods_list = goods_list(0);
    //保质期
    expire_time_list($goods_list['goods']);
    //print_r($goods_list);exit;
    $smarty->assign('curr_title', '商品列表');
    $smarty->assign('num', sprintf('（共%d条）', $goods_list['filter']['record_count']));

    $smarty->assign('brand', brand_list(1));

    $smarty->assign('goods_list', $goods_list['goods']);
    $smarty->assign('sort_info',  $goods_list['filter']);
    $smarty->assign('keyword',  empty($_REQUEST['keyword']) ? 0 : $_REQUEST['keyword']);
    $smarty->assign('brand_id', $res['id']);
    $smarty->assign('filter', isset($_REQUEST['filter'])?$_REQUEST['filter']:'');

    //品牌库存修改模块
    if(admin_priv('turn_on_stock','',false)){
        $smarty->assign('power_turn_on','able');
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('brand').' WHERE mod_stock_status_time<='.time();
        $smarty->assign('main_switch',$GLOBALS['db']->getOne($sql_select));
    }

    // 是否允许修改商品的建议售价
    if (admin_priv('update_min_price', '', false)) {
        $smarty->assign('update_min_price', 1);
    }

    // 是否显示商品最低价格
    if (admin_priv('show_cost_price', '', false)) {
        $smarty->assign('show_cost_price', 1);
    }

    // 是否允许修改商品最低价格
    if (admin_priv('edit_cost_price', '', false)) {
        $smarty->assign('edit_cost_price', 1);
    }

    if (admin_priv('safe_storage_number', '', false)) {
        $smarty->assign('show_safe_storage_number', true);
    }

    $res['main'] = $smarty->fetch('goods_list.htm');
    die($json->encode($res));
}

/*--- 检查库存批次 ---*/
elseif($_REQUEST['act'] == 'check_stock_batch')
{
    $res = array ('req_msg' => true, 'hide_btn' => 1);
    $goods_id = intval($_REQUEST['goods_id']);

    $sql_select = 'SELECT g.goods_name,s.rec_id,s.quantity,s.production_day,s.update_time,'.
        'st.arrival_day,g.brand_id,b.mod_stock_status_time,st.confirmer,s.expire_time FROM '.$GLOBALS['ecs']->table('goods').' g, '.
        $GLOBALS['ecs']->table('stock_goods').' s, '.$GLOBALS['ecs']->table('brand').
        ' b, '.$GLOBALS['ecs']->table('stock').' st WHERE s.quantity>0 AND g.goods_sn=s.goods_sn'.
        " AND g.brand_id=b.brand_id AND g.goods_id=$goods_id AND s.stock_id=st.stock_id ";
    $check_list = $GLOBALS['db']->getAll($sql_select);

    foreach ($check_list as &$val) {
        $val['production_day'] = date('Y-m-d', $val['production_day']);

        if($val['arrival_day'] != '' && strlen((string)($val['arrival_day'])) > 4){
            $val['arrival_day'] = date('Y-m-d', $val['arrival_day']);
        }else{
            $val['arrival_day'] = '-';
        }

        if($val['expire_time'] != '' && strlen((string)($val['expire_time'])) > 4){
            $val['expire_time'] = date('Y-m-d', $val['expire_time']);
        }else{
            $val['expire_time'] = '-';
        }

        if($val['mod_stock_status_time'] > $_SERVER['REQUEST_TIME']){
            $val['editable'] = 1;
        }else{
            $val['editable'] = 0;
        }

        if (!admin_priv('storage_number', '', false)) {
            $val['quantity'] = $val['quantity'] > 100 ? '库存充足' : '库存紧张';
        }
    }

    $smarty->assign('goods_name',$check_list[0]['goods_name']);
    $smarty->assign('main_switch',$main_switch);

    //修改库存权限
    if(admin_priv('mod_stock_quantity','',false)) {
        $smarty->assign('mod_stock_role','able');
    }

    $smarty->assign('check_list',$check_list);
    $res['message'] = $smarty->fetch('stock_batch.htm');

    die($json->encode($res));
}

/*--- 添加商品 ---*/
else if($_REQUEST['act'] == 'add_goods')
{
    $res = array ();
    if (isset($_REQUEST['ext']))
    {
        $file = basename($_SERVER['PHP_SELF'], '.php');
        $nav = list_nav();
        $smarty->assign('nav_2nd', $nav[1][$file]);
        $smarty->assign('nav_3rd', $nav[2]);
        $smarty->assign('file_name', $file);
        $res['left'] = $smarty->fetch('left.htm');
    }

    $is_add = $_REQUEST['act'] == 'add'; // 添加还是编辑的标识
    $is_copy = $_REQUEST['act'] == 'copy'; //是否复制
    $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
    $code=$code=='virual_card' ? 'virual_card': '';

    /* 取得商品信息 */
    /* 默认值 */
    $last_choose = array(0, 0);
    if (!empty($_COOKIE['ECSCP']['last_choose']))
    {
        $last_choose = explode('|', $_COOKIE['ECSCP']['last_choose']);
    }

    $goods = array(
        'goods_id'           => 0,
        'goods_desc'         => '',
        'cat_id'             => $last_choose[0],
        'brand_id'           => $last_choose[1],
        'is_on_sale'         => '1',
        'is_alone_sale'      => '1',
        'is_shipping'        => '0',
        //'other_cat'          => array(), // 扩展分类
        'goods_type'         => 0,       // 商品类型
        'shop_price'         => 0,
        //'promote_price'      => 0,
        //'market_price'       => 0,
        //'integral'           => 0,
        'goods_number'       => $_CFG['default_storage'],
        'warn_number'        => 1,
        //'promote_start_date' => local_date('Y-m-d'),
        //'promote_end_date'   => local_date('Y-m-d', local_strtotime('+1 month')),
        'goods_weight'       => 0,
        //'give_integral'      => -1,
        //'rank_integral'      => -1,
        'shelflife'          => 24,
        'per_number'         => 100,
        'every_num'          => 3,
        'everyday_use'       => 3,
        'every_num_units'    => '粒',
        'per_number_units'   => '粒'
    );

    /* 模板赋值 */
    $smarty->assign('code',    $code);
    $smarty->assign('cat_list', eff_list());
    $smarty->assign('brand_list', get_brand_id());
    //$smarty->assign('unit_list', get_unit_list());
    //$smarty->assign('user_rank_list', get_user_rank_list());
    $smarty->assign('weight_unit', $is_add ? ($goods['goods_weight'] >= 1 ? '1' : '0.001') : '1');
    $smarty->assign('cfg', $_CFG);
    $smarty->assign('form_act', $is_add ? 'insert' : ($_REQUEST['act'] == 'edit' ? 'update' : 'insert'));

    if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
        $smarty->assign('is_add', true);
    }

    /* 显示商品信息页面 */
    assign_query_info();
    $res['main'] = $smarty->fetch('add_goods.htm');

    die($json->encode($res));
}

/*--- 提交添加商品 ---*/
elseif($_REQUEST['act'] == 'add_goods_submit')
{
    //接收数据
    $_REQUEST = addslashes_deep($_REQUEST);
    $_REQUEST['add_time'] = time();

    $_REQUEST['goods_sn'] = generate_goods_sn_crm();
    unset($_REQUEST['act'], $_REQUEST['run']);

    if($_REQUEST['goods_name'] == '' || $_REQUEST['goods_sn'] == '')// || $shop_price == '')
    {
        $res['req_msg'] = true;
        $res['message'] = '提交内容不能为空';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    // 计算服用天数
    if (!isset($_REQUEST['every_num'], $_REQUEST['everyday_use']) || $_REQUEST['every_num'] == 0)
    {
        $res['req_msg'] = true;
        $res['message'] = '请务必填选每次服用量与每天服用次数！';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
    else {
        if (empty($_REQUEST['take_days'])) {
            $_REQUEST['take_days'] = $_REQUEST['per_unit_number']/$_REQUEST['every_num']/$_REQUEST['everyday_use']*24*3600;
        }
    }

    $fields = array_keys($_REQUEST);
    $values = array_values($_REQUEST);

    $fields = '('.implode(',', $fields).')';
    $values = "VALUES('".implode("','", $values)."')";

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('goods').$fields.$values;
    $result = $GLOBALS['db']->query($sql_insert);
    if($result)
    {
        record_operate($sql_insert, 'goods');
        $res['req_msg'] = true;
        $res['message'] = '新商品添加成功。商品编号为'.$_REQUEST['goods_sn'];
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
    else
    {
        $res['req_msg'] = true;
        $res['message'] = '添加失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
}

/*--- 编辑商品页面 ---*/
elseif ($_REQUEST['act'] == 'edit_goods')
{
    $goods_id = $_GET['goods_id'];

    $sql_select = 'SELECT g.*,c.cat_name,b.brand_name FROM '
        .$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('category').' c,'
        .$GLOBALS['ecs']->table('brand')." b WHERE goods_id=$goods_id ".
        ' AND g.cat_id=c.cat_id AND g.brand_id=b.brand_id';
    $goods = $GLOBALS['db']->getRow($sql_select);

    //给商品分类赋值
    $smarty->assign('cat_list', cat_list(0, $goods['cat_id']));

    //给品牌赋值
    $smarty->assign('brand_list', get_brand_list());
    $smarty->assign('goods',$goods);
    $res['main'] = $smarty->fetch('edit_goods.htm');

    die($json->encode($res));
}

/* 商品详情 */
elseif ($_REQUEST['act'] == 'get_detail') {
    $goods_id = intval($_REQUEST['goods_id']);

    // 获取商品详细信息
    $goods_info = get_goods_detail($goods_id);

    $smarty->assign('goods_info', $goods_info);

    $res['id'] = $goods_id;
    $res['info'] = $smarty->fetch('goods_detail.htm');

    die($json->encode($res));
}

/* 套餐列表 */
elseif ($_REQUEST['act'] == 'package_list')
{
    if (!admin_priv('package_list', '', false))
    {
        $res['req_msg'] = true;
        $res['timeout'] = 2000;
        $res['message'] = '当前帐号还未获得查看套餐列表权限！';

        die($json->encode($res));
    }

    $package_list = package_list ();

    $smarty->assign('package_list',    $package_list['package']);
    $smarty->assign('act',             $_REQUEST['act']);

    // 分页参数
    $smarty->assign('page_link',     $package_list['condition']);
    $smarty->assign('page_set',      $package_list['page_set']);
    $smarty->assign('record_count',  $package_list['record_count']);
    $smarty->assign('page_size',     $package_list['page_size']);
    $smarty->assign('page',          $package_list['page']);
    $smarty->assign('page_count',    $package_list['page_count']);
    $smarty->assign('page_start',    $package_list['start']);
    $smarty->assign('page_end',      $package_list['end']);

    $smarty->assign('curr_title', '套餐列表');
    $smarty->assign('num', sprintf('（共%d条）', $package_list['record_count']));
    $res['main'] = $smarty->fetch('package_list.htm');
    die($json->encode($res));
}

/* 设置套餐 */
elseif ($_REQUEST['act'] == 'combine_package')
{
    $smarty->assign('ur_here', '新增套餐');
    $smarty->assign('act', 'insert_package');
    $res['main'] = $smarty->fetch('package_info.htm');
    die($json->encode($res));
}

/* 保存套餐数据 */
elseif ($_REQUEST['act'] == 'insert_package')
{
    $_REQUEST = addslashes_deep($_REQUEST);
    extract($_REQUEST);

    $res = array (
        'timeout' => 2000,
        'req_msg' => true,
    );

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('packing').
        " WHERE packing_desc='$packing_desc'";
    $exist = $GLOBALS['db']->getOne($sql_select);
    if ($exist) {
        $res['message'] = '套餐编号已存在！';
        die($json->encode($res));
    }

    $now_time = time();
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('packing').
        '(packing_name,packing_desc,keywords,packing_price,take_days,add_admin_id,add_time)VALUES('.
        "'$packing_name','$packing_desc', '$keywords', $packing_price,$take_days,{$_SESSION['admin_id']}, $now_time)";
    $GLOBALS['db']->query($sql_insert);
    $packing_id = $GLOBALS['db']->insert_id();
    if ($packing_id)
    {
        record_operate($sql_insert, 'packing');
        $goods_list = array ();
        foreach ($list_id as $key=>$val)
        {
            $sql_select = 'SELECT goods_id FROM '.$GLOBALS['ecs']->table('goods').
                " WHERE goods_sn='$val'";
            $goods_id = $GLOBALS['db']->getOne($sql_select);
            $goods_list[] = "($packing_id,$goods_id,'$list_name[$key]',$list_number[$key])";
        }

        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('packing_goods').
            '(packing_id,goods_id,goods_name,num)VALUES'.implode(',', $goods_list);
        $GLOBALS['db']->query($sql_insert);
        if ($GLOBALS['db']->affected_rows())
        {
            record_operate($sql_insert, 'packing_goods');
            $goods_list = array ();

            // 完善商品相关信息
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('packing_goods').
                ' p SET p.brand_id=g.brand_id,p.goods_sn=g.goods_sn,p.goods_name=g.goods_name,p.goods_price=g.shop_price '.
                " WHERE g.goods_id=p.goods_id AND p.packing_id=$packing_id";
            $GLOBALS['db']->query($sql_update);
            record_operate($sql_update, 'goods');
            $res['message'] = '套餐保存成功！';
        } else {
            $res['message'] = '套餐商品保存失败！';
        }
    }
    else{
        $res['message'] = '套餐保存失败！';
    }

    die($json->encode($res));
}

/* 修改套餐 */
elseif ($_REQUEST['act'] == 'edit_packing')
{
    if (!admin_priv('edit_packing', '', false)) {
        $res['timeout'] = 2000;
        $res['req_msg'] = true;
        $res['message'] = '该账号没有权限修改套餐';
        die($json->encode($res));
    }
    $packing_info  = packing_info();
    $packing_goods = packing_goods_list();

    $smarty->assign('packing',       $packing_info);
    $smarty->assign('packing_goods', $packing_goods);

    $smarty->assign('filename', $file);
    $smarty->assign('act', 'update_package');
    $smarty->assign('ur_here', '修改套餐');

    $res['main'] = $smarty->fetch('package_info.htm');

    die($json->encode($res));
}

/* 保存修改后的数据 */
elseif ($_REQUEST['act'] == 'update_package')
{
    $res = array (
        'req_msg' => true,
        'timeout' => 2000,
        'code'    => 0
    );

    if (!admin_priv('update_package', '', false))
    {
        $res['message'] = '对不起，当前帐号没有修改套餐的权限！';
    }

    $request = addslashes_deep($_REQUEST);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('packing')." SET packing_name='{$request['packing_name']}', ".
        "packing_price={$request['packing_price']},keywords='{$request['keywords']}', ".
        " take_days={$request['take_days']} WHERE packing_id={$request['id']}";
    $GLOBALS['db']->query($sql_update);

    if (!empty($request['list_id']))
    {
        record_operate($sql_update, 'packing');
        // 删除套餐中相应的商品
        if (empty($request['rec_id']))
        {
            $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table('packing_goods').
                " WHERE packing_id={$request['id']} AND rec_id NOT IN ('".implode("','", $request['rec_id']);
            if ($GLOBALS['db']->query($sql_delete)) {
                record_operate($sql_delete, 'packing_goods');
            }
        }

        $goods_list = array ();
        $goods_sn = array ();
        foreach ($request['list_id'] as $key=>$val)
        {
            $goods_sn[] = $val;
            $goods_list[$val]['goods_name'] = $request['list_name'][$key];
            $goods_list[$val]['num'] = $request['list_number'][$key];
        }

        $sql_select = 'SELECT goods_id, goods_sn FROM '.$GLOBALS['ecs']->table('goods').
            ' WHERE goods_sn IN ("'.implode('","', $goods_sn).'")';
        $goods_info = $GLOBALS['db']->getAll($sql_select);
        foreach ($goods_info as $val)
        {
            $goods_list[$val['goods_sn']]['goods_id']   = $val['goods_id'];
            $goods_list[$val['goods_sn']]['packing_id'] = $request['id'];
        }

        foreach ($goods_list as $val)
        {
            $fields   = implode(',', array_keys($val));
            $values[] = implode("','", $val);
        }

        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('packing_goods').
            "($fields)VALUES('".implode("','", $values)."')";
        $GLOBALS['db']->query($sql_insert);
        $insert_rows = $GLOBALS['db']->affected_rows();
        if ($insert_rows == count($values))
        {
            record_operate($sql_insert, 'packing_goods');
            $res['message'] = '套餐修改成功！';
        }
    }
    else
    {
        $res['message'] = '套餐信息修改成功！';
    }

    die($json->encode($res));
}

/* 验证套餐编号是否存在 */
elseif ($_REQUEST['act'] == 'check_package_sn')
{
    $res = array (
        'timeout' => 2000,
        'req_msg' => true,
    );

    $packing_desc = addslashes_deep($_REQUEST['packing_desc']);
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('packing').
        " WHERE packing_desc='$packing_desc'";
    $exist = $GLOBALS['db']->getOne($sql_select);
    if ($exist){
        $res['message'] = '套餐编号已存在！';
    }
    else{
        $res['req_msg'] = false;
    }

    die($json->encode($res));
}

/*--- ajax编辑商品 ---*/
elseif($_REQUEST['act'] == 'ajax_edit') {
    $gid = $_REQUEST['gid'];

    if($_REQUEST['info'] != '') {
        $info = $_REQUEST['info'];

        //根据传过来的id查找对应记录的字段默认值。
        $sql_select = "SELECT $info FROM ".$GLOBALS['ecs']->table('goods').
            " WHERE goods_id=$gid ";
        $value = $GLOBALS['db']->getOne($sql_select);
    }

    // 处理两个选项
    if($_REQUEST['info1'] != '' && $_REQUEST['info2'] != '') {
        $info1 = $_REQUEST['info1'];
        $info2 = $_REQUEST['info2'];

        $sql_select = "SELECT $info1 FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id=$gid ";
        $value1 = $GLOBALS['db']->getOne($sql_select);

        $sql_select = "SELECT $info2 FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_id=$gid ";
        $value2 = $GLOBALS['db']->getOne($sql_select);
    }

    //拼装好数据返回
    if(strtolower($_REQUEST['type']) == 'input' && strtolower($_REQUEST['info']) == 'goods_sn') {
        $res['info'] = strtolower($_REQUEST['info']);
        $res['gid'] = $_REQUEST['gid'];
        $res['main'] = "<input type='text' name='$info' value='$value'
            onblur="."checkGoodsSn(this.value,'".$value."')".' />';
    } elseif(strtolower($_REQUEST['type']) == 'input') {
        $res['info'] = $_REQUEST['info'];
        $res['gid'] = $_REQUEST['gid'];
        $res['main'] = "<input type='text' name='$info' value='$value'
            onblur='goodsSaveInputInfo(this)' />";
    } elseif(strtolower($_REQUEST['type']) == 'select') {
        $res['info'] = $_REQUEST['info'];
        $res['gid'] = $_REQUEST['gid'];
        $smarty->assign('name',$info);
        $smarty->assign('packing',$value);
        $res['main'] = $smarty->fetch('ajax_goods_temp.htm');
    } elseif(strtolower($_REQUEST['type']) == 'textarea') {
        $res['info'] = $_REQUEST['info'];
        $res['gid'] = $_REQUEST['gid'];
        $res['main'] = "<textarea name='$_REQUEST[info]' cols='40' rows='4' onblur='goodsSaveInputInfo(this)' >$value</textarea>";
    } elseif(strtolower($_REQUEST['type']) == 'num_sel' ) {

        $res['name'] = $_REQUEST['name'];

        $smarty->assign('num',$_REQUEST['num']);
        $smarty->assign('name1',$_REQUEST['info1']);
        $smarty->assign('name2',$_REQUEST['info2']);
        $smarty->assign('goods_number',$value1);
        $smarty->assign('goods_number_units',$value2);

        $res['main'] = $smarty->fetch('ajax_goods_temp.htm');
    }

    die($json->encode($res));
}

/*--- ajax更新商品 ---*/
elseif($_REQUEST['act'] == 'ajax_update') {
    $gid = $_REQUEST['gid'];

    if($_REQUEST['type'] == 'num_sel')
    {
        $info1 = $_REQUEST['info1'];
        $info2 = $_REQUEST['info2'];
        $value1 = $_REQUEST['value1'];
        $value2 = $_REQUEST['value2'];

        //更新数据
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').
            " SET $info1='$value1',$info2='$value2' WHERE goods_id='$gid'";
        $result = $GLOBALS['db']->query($sql_update);

        if($result)
        {
            record_operate($sql_update, 'goods');
            $res['info'] = $info1;
            $res['info1'] = $info1;
            $res['info2'] = $info2;
            $res['num'] = $_REQUEST['num'];
            $res['main'] = $value1.'&nbsp;'.$value2;
            $res['type'] = 'num_sel';
            $res['gid'] = $gid;
        }
        else
        {
            $res['req_msg'] = true;
            $res['timeout'] = 2000;
            $res['message'] = '编辑失败';

            die($json->encode($res));
        }
    }

    else
    {
        $info = $_REQUEST['info'];
        $val = $_REQUEST['val'];

        //更新数据
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').
            " SET $info='$val' WHERE goods_id='$gid'";
        $result = $GLOBALS['db']->query($sql_update);
        if($result)
        {
            record_operate($sql_update, 'goods');
            $res['gid'] = $_REQUEST['gid'];
            $res['info'] = $_REQUEST['info'];
            $res['main'] = $_REQUEST['val'];
            $res['type'] = $_REQUEST['type'];
        }
    }

    die($json->encode($res));
}

/* 添加商品品牌 */
elseif ($_REQUEST['act'] == 'add_brand')
{
    $res['req_msg'] = true;
    $res['timeout'] = 2000;
    $res['code']    = 0;

    $_REQUEST = addslashes_deep($_REQUEST);

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('brand').
        " WHERE brand_name LIKE '%{$_REQUEST['brand_name']}%'";
    $is_exist = $GLOBALS['db']->getOne($sql_select);
    if ($is_exist)
    {
        $res['message'] = '该品牌已经存在，无需再次提交！';
        die($json->encode($res));
    }

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('brand').'(brand_name,brand_desc)VALUES('.
        "'{$_REQUEST['brand_name']}','{$_REQUEST['brand_desc']}')";
    $GLOBALS['db']->query($sql_insert);
    $res['brand_id'] = $GLOBALS['db']->insert_id();
    if ($res['brand_id'])
    {
        record_operate($sql_insert, 'brand');
        $res['code'] = 1;
        $res['message'] = '添加品牌成功！';
        $res['brand_name'] = $_REQUEST['brand_name'];
        $res['brand_desc'] = $_REQUEST['brand_desc'];

        die(json_encode($res));
    }
}

/* 删除操作 */
elseif ($_REQUEST['act'] == 'delete')
{
    $res = array (
        'req_msg' => true,
        'timeout' => 2000,
        'code'    => 0
    );

    if (!admin_priv('delete', '', false))
    {
        $res['message'] = '对不起，您没有删除权限！';
        die($json->encode($res));
    }

    $request = addslashes_deep($_REQUEST);

    $sql_delete = 'DELETE FROM '.$GLOBALS['ecs']->table($request['target_table']).
        " WHERE rec_id={$request['rec_id']}";
    $GLOBALS['db']->query($sql_delete);
    $affect_rows = $GLOBALS['db']->affected_rows();

    if ($affect_rows)
    {
        $res['id']      = $request['rec_id'];
        $res['code']    = 4;
        $res['message'] = '删除成功！';
        die($json->encode($res));
    }
}

/*--- 检查商品编号是否重复 ---*/
elseif ($_REQUEST['act'] == 'get_goods_id')
{
    $_REQUEST = addslashes_deep($_REQUEST);

    die($json->encode(array('id' => 1)));

    // 检查是否重复
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('goods').
        " WHERE goods_sn='$goods_sn'";
    $is_exist = $GLOBALS['db']->getOne($sql_select);

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('packing').
        " WHERE packing_desc='$goods_sn'";
    $is_exist_package = $GLOBALS['db']->getOne($sql_select);

    if($is_exist || $is_exist_package){
        $res['req_msg'] = true;
        $res['message'] = '编号已被使用！';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
    else{
        $res['req_msg'] = false;
        die($json->encode($res));
    }
}

/*--- 添加商品分类 ----*/
elseif($_REQUEST['act'] == 'add_category')
{
    $parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
    $category = empty($_REQUEST['cat']) ? '' : trim($_REQUEST['cat']);

    if(cat_exists($category, $parent_id))
    {
        $res['code'] = 1;
        $res['message'] = '分类存在';

        die($json->encode($res));
    }
    else
    {
        $sql_insert = 'INSERT INTO ' . $ecs->table('category') .
            '(cat_name, parent_id, is_show)' ." VALUES ( '$category', '$parent_id', 1)";
        $GLOBALS['db']->query($sql_insert);
        record_operate($sql_insert, 'category');
        $category_id = $GLOBALS['db']->insert_id();

        $res['id'] = $category_id;
        $res['parent_id'] = $parent_id;
        $res['cat'] = $category;

        die($json->encode($res));
    }
}

/*--- 添加品牌 ---*/
elseif($_REQUEST['act'] == 'brand_manage')
{
    if (!admin_priv('add_brand', '', false)){
        $res['timeout'] = 2000;
        $res['req_msg'] = true;
        $res['message'] = '当前帐号尚未获得品牌管理权限！';

        die($json->encode($res));
    }

    $brand_list = brand_list();

    $smarty->assign('brand_list', $brand_list);
    $res['main'] = $smarty->fetch('brand_manage.htm');

    die($json->encode($res));
}

/* 显示/隐藏品牌 */
elseif ($_REQUEST['act'] == 'show_or_hide') {
    $msg = array('req_msg'=>true,'timeout'=>2000);
    if (!admin_priv('show_or_hide', '', false)) {
        $msg['message'] = '对不起，您没有修改品牌的权限！';

        echo $json->encode($msg);
        return;
    }

    $brand_id = intval($_REQUEST['brand_id']);
    if (empty($brand_id)) {
        $msg['message'] = '修改失败，请刷新后再次尝试！';

        echo $json->encode($msg);
        return;
    }

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('brand').
        " SET is_show=IF(is_show, 0, 1) WHERE brand_id=$brand_id";
    if ($GLOBALS['db']->query($sql_update)) {
        record_operate($sql_update, 'brand');
        $sql_select = 'SELECT is_show FROM '.$GLOBALS['ecs']->table('brand')." WHERE brand_id=$brand_id";
        $msg['is_show'] = $GLOBALS['db']->getOne($sql_select);

        $msg['brand_id'] = $brand_id;
        $msg['success']  = true;
        $msg['message']  = '品牌状态修改成功！';
    } else {
        $msg['message'] = '品牌状态修改失败，请稍后再试！';
    }

    echo $json->encode($msg);
    return;
}



/* 产品发货日志 */
elseif ($_REQUEST['act'] == 'shipping_log') {
    // 处理查询数据
    if (isset($_REQUEST['data'])) {
        $_REQUEST = array_merge($_REQUEST, $json->decode($_REQUEST['data'], true));
        $_REQUEST['method'] = 'Ajax';
        unset($_REQUEST['data']);
    }

    // 默认为当天出库数量
    $result         = stats_shipping_goods();
    $shipping_goods = $result['shipping_goods'];
    $filter         = $result['filter'];
    $brand_list     = brand_list();

    //统计退货数量
    $shipping_goods    = get_return_number($shipping_goods);
    $only_return_goods = zero_shipping_but_return_goods($shipping_goods);

    $smarty->assign('brand_list', $brand_list);
    $smarty->assign('platform_list', get_role_list(1));
    $smarty->assign('shipping_goods', $shipping_goods);
    $smarty->assign('filter',$filter);
    $smarty->assign('condition',$filter['condition']);

    if ($_REQUEST['method'] == 'Ajax') {
        $res['response_action'] = 'search_service';
        $res['main']            = $smarty->fetch('shipping_log_div.htm');
        die($json->encode($res));
    }

    $res['main'] = $smarty->fetch('shipping_log.htm');
    die($json->encode($res));
}

//滞销产品
elseif($_REQUEST['act'] == 'dead_stock')
{
    $sch_data = stripslashes($_REQUEST['JSON']);  // 过滤转义字符
    $sch_data = json_decode($sch_data,true);      // 转JSON为Array
    $sch_data = addslashes_deep($sch_data);
    if($sch_data != null) {
        extract($sch_data);
    }else{
        $brand            = intval($_REQUEST['brand']);
        $goods_name       = mysql_real_escape_string($_REQUEST['goods_name']);
        $production_start = mysql_real_escape_string($_REQUEST['production_start']);
        $production_end   = mysql_real_escape_string($_REQUEST['production_end']);
        $arrival_start    = mysql_real_escape_string($_REQUEST['arrival_start']);
        $arrival_end      = mysql_real_escape_string($_REQUEST['arrival_end']);
        $from_sch         = intval($_REQUEST['from_sch']);
    }

    $where = " WHERE (sg.quantity<800 OR TIMESTAMPDIFF(month,FROM_UNIXTIME(sg.production_day,'%Y-%m-%d'),NOW())>12) AND g.is_delete=0";
    $condition = "&from_sch=$from_sch";

    //品牌
    if($brand) {
        $where .= ' AND g.brand_id=' .intval($brand);
        $condition .= "&brand=$brand";
    }

    //商品名
    if($goods_name) {
        $where .= " AND g.goods_name LIKE '%$goods_name%'";
        $condition .= "&goods_name=$goods_name";
    }

    //生产日期
    if($production_start && $production_end) {
        $where .= ' AND sg.production_day BETWEEN '.strtotime($production_start).' AND '.strtotime($production_end);
        $condition .= "&production_day=$production_start&production_end=$production_end";
    }

    //进货日期
    if($arrival_start && $arrival_end) {
        $where .= " AND s.arrival_day BETWEEN ".strtotime(trim($arrival_start))
            .' AND '.strtotime(trim($arrival_end));
        $condition .= "&arrival_start=$arrival_start&arrival_end=$arrival_end";
    }

    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    $filter['recorder_size'] = empty($_REQUEST['recorder_size']) ? 15 : intval($_REQUEST['recorder_size']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20;
    }

    $sql_select = 'SELECT COUNT(*) FROM '
        .$GLOBALS['ecs']->table('goods').' AS g LEFT JOIN '.$GLOBALS['ecs']->table('stock_goods')
        .' AS sg ON g.goods_sn=sg.goods_sn LEFT JOIN '.$GLOBALS['ecs']->table('stock')
        .' AS s ON sg.stock_id=s.stock_id'.$where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页
    $page_set = array (1,2,3,4,5,6,7);
    if ($filter['page'] > 4) {
        foreach ($page_set as &$val) {
            $val += $filter['page'] -4;
        }
    }

    if (end($page_set) > $filter['page_count']) {
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--) {
            if ($filter['page_count'] - $i > 0) {
                $page_set[] = $filter['page_count'] - $i;
            }
        }
    }

    $filter = array (
        'filter'        => $filter,
        'page_count'    => $filter['page_count'],
        'recorder_size' => $filter['recorder_size'],
        'record_count'  => $filter['record_count'],
        'page_size'     => $filter['page_size'],
        'page'          => $filter['page'],
        'page_set'      => $page_set,
        'condition'     => $condition,
        'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'           => $filter['page']*$filter['page_size'],
        'act'           => 'dead_stock',
    );

    //库存多的 生产日期还有1年内的
    $sql_select = "SELECT g.goods_id,g.goods_sn,g.goods_name,sg.quantity,sg.production_day,s.arrival_day,TIMESTAMPDIFF(month,FROM_UNIXTIME(sg.production_day,'%Y-%m-%d'),NOW()) AS diff_day FROM "
        .$GLOBALS['ecs']->table('goods').' AS g LEFT JOIN '.$GLOBALS['ecs']->table('stock_goods')
        .' AS sg ON g.goods_sn=sg.goods_sn LEFT JOIN '.$GLOBALS['ecs']->table('stock')
        .' AS s ON sg.stock_id=s.stock_id'.$where
        .' LIMIT '.($filter['page']-1)*$filter['page_size']
        .",{$filter['page_size']}";

    $dead_stock = $GLOBALS['db']->getAll($sql_select);

    foreach($dead_stock as &$val) {
        $val['production_day'] = date('Y-m-d',$val['production_day']);
        $val['arrival_day']    = date('Y-m-d',$val['arrival_day']);
    }

    $smarty->assign('filter',$filter);
    $smarty->assign('dead_stock',$dead_stock);
    $smarty->assign('brand_list',brand_list());
    $smarty->assign('condition',$condition);

    if(!$from_sch){
        $res['main'] = $smarty->fetch('dead_stock.htm');
    } elseif($from_sch){
        $res['response_action'] = 'search_service';
        $res['main']            = $smarty->fetch('dead_stock_div.htm');
    }
    die($json->encode($res));
}

//控制修改库存开关
elseif ($_REQUEST['act'] == 'mod_quantity_switch'){
    $res['rec_id'] = $rec_id = intval($_REQUEST['rec_id']);
    $res['behave'] = $behave = mysql_real_escape_string($_REQUEST['behave']);
    if($behave == 'on'){
        $do_what = ' SET edit_available=2';
    }elseif($behave == 'off'){
        $do_what = ' SET edit_available=0';
    }

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').$do_what." WHERE rec_id=$rec_id";
    $res['code'] = $GLOBALS['db']->query($sql_update);
    record_operate($sql_update, 'stock_goods');

    die($json->encode($res));
}

//修改库存控制
elseif ($_REQUEST['act'] == 'stock_switch')
{
    $res['behave'] = $behave = mysql_real_escape_string($_REQUEST['behave']);
    $res['brand_id'] = $brand_id = intval($_REQUEST['brand_id']);
    $where = '';
    if(admin_priv('turn_on_stock','',false))
    {
        if($brand_id === 0){
            $sql_select = 'SELECT IF(COUNT(*),0,1) FROM '.$GLOBALS['ecs']->table('brand').' WHERE mod_stock_status_time<='.time();
        }
        elseif($brand_id > 0)
        {
            $sql_select = 'SELECT IF(mod_stock_status_time<='.time().',0,1)AS mod_stock_status FROM '.$GLOBALS['ecs']->table('brand')." WHERE brand_id=$brand_id";
        }

        if($behave == 'mod_stock_status_time')
        {
            $sql_upd = 'UPDATE '.$GLOBALS['ecs']->table('brand').' SET mod_stock_status_time=IF(mod_stock_status_time<='.time().','.strtotime(date('Y-m-d 23:59:59')).',0)';
            if($brand_id > 0){
                $where = " WHERE brand_id=$brand_id";
                $sql_upd .= $where;
            }
            $res['result'] = $GLOBALS['db']->query($sql_upd);
            record_operate($sql_update, 'brand');
        }

        $res['mod_stock_status'] = $GLOBALS['db']->getOne($sql_select);
        die($json->encode($res));
    }


}

//修改库存
elseif($_REQUEST['act'] == 'modify_stock')
{
    if(admin_priv('mod_stock_quantity','',false))
    {
        $rec_id = intval($_REQUEST['rec_id']);
        $quantity = intval($_REQUEST['quantity']);
        $update_time = time();

        $sql_upd = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').
            " SET quantity=$quantity,update_time=$update_time WHERE rec_id=$rec_id";
        $result = $GLOBALS['db']->query($sql_upd);

        $res = array(
            'req_msg'=>true,
            'behave'=>$behave,
            'rec_id'=>$rec_id,
            'timeout'=>2000,
            'code'=>false
        );

        if($result)
        {
            record_operate($sql_upd, 'stock_goods');
            $res['code'] = true;
            $res['quantity'] = $quantity;
        }

        die($json->encode($res));
    }
}

//首页显示报警库存
elseif ($_REQUEST['act'] == 'index_stock_alarm')
{
    $_REQUEST['filter'] = 'low_qty';
    $sql_select         = 'SELECT g.goods_id,g.goods_sn,SUM(g.quantity) AS quantity,';
    $stock_info         = goods_list(0);

    $smarty->assign('goods_list',$stock_info['goods']);
    $smarty->assign('page_set',$stock_info['page_set']);
    $smarty->assign('page_count',$stock_info['page_count']);
    $smarty->assign('record_count',$stock_info['record_count']);
    $smarty->assign('page_size',$stock_info['page_size']);
    $smarty->assign('page',$stock_info['page']);
    $smarty->assign('condition',$stock_info['condition']);
    $smarty->assign('page_set',$stock_info['page_set']);
    $smarty->assign('start',$stock_info['start']);
    $smarty->assign('end',$stock_info['end']);
    $smarty->assign('act','index_stock_alarm');

    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('stock_alarm.htm');
    die($json->encode($res));
}

//实时库存警报
elseif ($_REQUEST['act'] == 'timely_stock_alarm'){
    //当月第一天初始提醒设置
    if(date('Y-m-1') == date('Y-m-d')){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods')
            ." SET confirm_sto_admin=0,confirm_sto_times=0,predict_arrival_time=0,add_sto_order_time,edit_status=0";
        $GLOBALS['db']->query($sql_update[$i]);
    }

    if(admin_priv('stock_alarm_confirm','',false) && !admin_priv('all','',false)){
        $smarty->assign('confirm_stock_alarm',true);
    }elseif($_REQUEST['sto_alarm_times'] >= 3){
        $res['donot_alarm'] = true;
        die($json->encode($res));
    }

    $alarm_stock_goods = timely_stock_alarm();

    if($alarm_stock_goods){
        $smarty->assign('alarm_stock_goods',$alarm_stock_goods);
        $smarty->assign('stock_manager',true);
        $res = array(
            'req_msg'         => true,
            'response_action' => 'alarm_stock',
            'btncontent'      => false,
            'message'         => $smarty->fetch('alarm_stock_goods.htm')
        );
        die($json->encode($res));
    }

    return ;
}

//库存警报确认操作
elseif ($_REQUEST['act'] == 'confirm_stock_alarm'){

    $time_now           = $_SERVER['REQUEST_TIME'];
    $goods_sn           = mysql_real_escape_string($_REQUEST['goods_sn']);
    $arrival_time       = strtotime($_REQUEST['arrival_time']);
    $add_sto_order_time = strtotime($_REQUEST['add_sto_order_time']);
    $confirm_pwd        = mysql_real_escape_string($_REQUEST['confirm_pwd']);

    $sql = "SELECT `ec_salt` FROM ".$GLOBALS['ecs']->table('admin_user')
        ." WHERE user_id={$_SESSION['admin_id']}";
    $ec_salt = $GLOBALS['db']->getOne($sql);

    if($ec_salt){
        $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_user')
            ." WHERE user_id={$_SESSION['admin_id']} AND status>0 AND password='"
            .md5(md5($confirm_pwd).$ec_salt)."'";

        $result = $GLOBALS['db']->getOne($sql);
    }

    if(!$result || !$ec_salt){
        $res['pwd_error'] = true;
        die($json->encode($res));
    }

    if($goods_sn){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods')
            ." SET predict_arrival_time=$arrival_time,confirm_sto_admin={$_SESSION['admin_id']},add_sto_order_time=$add_sto_order_time,"
            .'confirm_sto_times=confirm_sto_times+1,edit_status=0'." WHERE goods_sn='$goods_sn'";
        $result = $GLOBALS['db']->query($sql_update);
    }

    $_REQUEST['sto_alarm_repeat'] = true;
    $alarm_stock_goods            = timely_stock_alarm();
    if($alarm_stock_goods && $result){
        $res['code']    = true;
        $res['timeout'] = 100;
    }else{
        $res['code']    = false;
        $res['message'] = '确认成功';
        $res['timeout'] = 2000;
    }

    $res['req_msg']     = true;
    $res['confirm_val'] = $confirm_val;

    die($json->encode($res));
}

elseif ($_REQUEST['act'] == 'edit_warn_number'){
    $goods_id = intval($_REQUEST['goods_id']);
    $warn_number = intval($_REQUEST['warn_number']);
    $res = array(
        'req_msg' => true,
        'code'    => false,
        'timeout' => 2000,
    );

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods')." SET warn_number=$warn_number"
        ." WHERE goods_id='$goods_id'";

    $res['code'] = $GLOBALS['db']->query($sql_update);
    if($res['code']){
        record_operate($sql_update, 'goods');
        $res['message']     = '修改成功';
        $res['warn_number'] = '<label onclick="editInpValue(this,'.$warn_number
            .",$goods_id)\">$warn_number";
        $res['goods_id']    = $goods_id;
    }

    die($json->encode($res));
}

//商品上、下架
elseif ($_REQUEST['act'] == 'edit_on_sale'){
    $goods_id     = intval($_REQUEST['goods_id']);
    $val          = intval($_REQUEST['is_on_sale']);
    $row_index    = intval($_REQUEST['row_index']);
    $current_time = time();

    $res = array(
        'req_msg'   => true,
        'code'      => false,
        'timeout'   => 2000,
        'row_index' => $row_index,
    );

    if($goods_id != ''){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods')." SET is_on_sale=$val"
            ." WHERE goods_id=$goods_id";
        $res['code'] = $GLOBALS['db']->query($sql_update);
        record_operate($sql_update, 'goods');

        $sql_select     =   'SELECT goods_id FROM '.$GLOBALS['ecs']->table('soldout_goods')
            ." WHERE goods_id=$goods_id";
        $des_goods_id   =   $GLOBALS['db']->getOne($sql_select);
        if($des_goods_id == $goods_id){
            if($val){
                $do = 'on_sale_time='.$current_time;
            }else{
                $do = 'sold_out_time='.$current_time;
            }

            $sql = 'UPDATE '.$GLOBALS['ecs']->table('soldout_goods').
                " SET is_on_sale=$val,$do,admin_id={$_SESSION['admin_id']} WHERE goods_id=$goods_id";
        }else{
            $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('soldout_goods')
                .'(goods_id,is_on_sale,admin_id,on_sale_time,sold_out_time)VALUES'
                ."($goods_id,$val,{$_SESSION['admin_id']},$current_time,$current_time)";
        }

        $GLOBALS['db']->query($sql);
        record_operate($sql, 'soldout_goods');
    }

    if($res['code']){
        $res['message'] = '修改成功';
    }

    die($json->encode($res));
}

//下架所有零库存产品
elseif($_REQUEST['act'] == 'sold_out_zero'){
    if(admin_priv('mod_stock_quantity','',false)){
        $res = array(
            'req_msg'   =>  true,
            'timeout'   =>  2000,
            'code'      =>  false,
        );

        $sql_select = 'SELECT g.goods_sn,SUM(quantity) quantity FROM '
            .$GLOBALS['ecs']->table('goods').' g LEFT JOIN'.$GLOBALS['ecs']->table('stock_goods')
            .' s ON g.goods_sn=s.goods_sn'
            .' GROUP BY g.goods_sn';

        $goods_list = $GLOBALS['db']->getAll($sql_select);

        foreach($goods_list as $val){
            if(!$val['quantity']){
                $goods_sn[] = $val['goods_sn'];
            }
        }

        $goods_sn = implode("','",$goods_sn);
        if($goods_sn){
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').' SET is_on_sale=0'
                ." WHERE goods_sn IN('$goods_sn')";
            if($GLOBALS['db']->query($sql_update)){
                record_operate($sql_update, 'goods');
                $res['code']    =   true;
                $res['message'] =   '修改成功';
            }

        }else{
            $res['message']  =   '修改失败';
        }

        die($json->encode($res));
    }
}

//盘点管理
elseif ($_REQUEST['act'] == 'inventory_list'){

    $brand_list = get_brand_id();

    $sql_select = 'SELECT start_time FROM '.$GLOBALS['ecs']->table('inventory_storage').' WHERE (end_time=0 OR end_time IS NULL) AND start_time<>0 LIMIT 1';
    $start_time = $GLOBALS['db']->getOne($sql_select);

    $now_inventory_time = $start_time ? $start_time : null;
    $start_time         = $start_time ? date('Y-m-d H:i:s',$start_time) : null;

    $sql_select = 'SELECT start_time,end_time FROM '.$GLOBALS['ecs']->table('inventory_storage')
        ." WHERE start_time<>0 AND end_time<>0 GROUP BY start_time ORDER BY start_time DESC";

    $tem_time_list = $GLOBALS['db']->getAll($sql_select);
    foreach($tem_time_list as &$val){
        $time_list['start_time'][] = array(
            'start_time' => date('Y-m-d H:i:s',$val['start_time']),
            'unix_time'  => $val['start_time']
        );
        $time_list['end_time'][] = array(
            'end_time'   => date('Y-m-d H:i:s',$val['end_time']),
            'unix_time'  => $val['end_time']
        );
    }

    if ($now_inventory_time) {
        $filter['start_time'] = $now_inventory_time;
    }else if($time_list){
        $filter  = array(
            'start_time' => $time_list['start_time'][0]['unix_time'],
            'end_time'   => $time_list['end_time'][0]['unix_time']
        );
    }

    $_REQUEST['page']      = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $_REQUEST['page_size'] = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 15;

    $recently          = $filter;
    $result            = get_inventory_storage($filter);
    $inventory_storage = $result['inventory_list'];
    $filter            = $result['filter'];
    $p_url = "storage.php?act=sch_inventory_list&print=true&page={$_REQUEST['page']}&page_size={$_REQUEST['page_size']}&start_time={$recently['start_time']}&end_time={$recently['end_time']}";

    $smarty->assign('p_url',$p_url);
    $smarty->assign('recently',$recently);
    $smarty->assign('inventory_storage',$inventory_storage);
    $smarty->assign('start_time',$start_time);
    $smarty->assign('brand_list',$brand_list);
    $smarty->assign('time_list',$time_list);
    $smarty->assign('filter',$filter);
    $res['main'] = $smarty->fetch('inventory_storage.htm');

    die($json->encode($res));
}

/* 盘点记录 */
elseif ($_REQUEST['act'] == 'stocktake_records') {

    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('inventory_list_div.htm');

    die($json->encode($res));
}

/* 添加盘点记录 */
elseif ($_REQUEST['act'] == 'stocktake_add') {
    $res = array(
        'req_msg'   => true,
        'timeout'   =>  2000,
        'message'   => ''
    );

    if(admin_priv('mod_stock_quantity','',false) || admin_priv('all','',false)){

        $behave        = intval($_REQUEST['behave']);
        $res['behave'] = $behave;

        switch($behave){
        case 0 :
            $sql_update = 'DELETE FROM '.$GLOBALS['ecs']->table('inventory_storage')
                .' WHERE end_time=0';
            $GLOBALS['db']->query($sql_update);

            $res['message'] = '已经取消盘点！';
            $res['main']    = $smarty->fetch('inventory_list_div.htm');
            break;
        case 1 :
            $start_time = time();
            $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('inventory_storage')
                .' WHERE end_time=0 ';
            if($GLOBALS['db']->getOne($sql_select)>0){
                $res['message'] = '你已经开始盘点，请勿重复操作！ ';
                break;
            }
            $sql_select = 'SELECT g.goods_sn,SUM(quantity) quantity,g.goods_sn,g.goods_name FROM '.$GLOBALS['ecs']->table('goods')
                .' g LEFT JOIN '.$GLOBALS['ecs']->table('stock_goods').' s ON s.goods_sn=g.goods_sn'
                ." WHERE g.is_delete=0 GROUP BY s.goods_sn";
            $goods_list = $GLOBALS['db']->getAll($sql_select);

            foreach($goods_list as $val){
                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('inventory_storage').'(start_time,goods_sn,goods_name,quantity)VALUES'
                    ."($start_time,'{$val['goods_sn']}','{$val['goods_name']}','{$val['quantity']}')";
                $GLOBALS['db']->query($sql_insert);
                record_operate($sql_insert, 'inventory_storage');
            }

            $res['message']     = '已经开始盘点，切记要按结束盘点按钮！ ';
            $res['start_time']  = '<font size="3px">开始盘点时间：</font><font color="red">'
                .date('Y-m-d H:i:s',$start_time).'</font>';

            $filter            = array('start_time' => $start_time);
            $result            = get_inventory_storage($filter);
            $inventory_storage = $result['inventory_list'];
            $filter            = $result['filter'];

            foreach($inventory_storage as &$val){
                $val['start_time'] = date('Y-m-d H:i:s',$val['start_time']);
            }

            $smarty->assign('start_time',$start_time);
            $smarty->assign('filter',$filter);
            $smarty->assign('inventory_storage',$inventory_storage);
            $res['main'] = $smarty->fetch('inventory_list_div.htm');
            break;
        case 2 :
            /*
             * 下单未发货   @place_goods_num
             * 下单已发货   @delive_goods_num
             * 盘点前下单（盘点时发货） @before_inventory
             * 盘点前下间（盘点时还没发货）@before_inventory_not_send
             * */
            $end_time   = $_SERVER['REQUEST_TIME'];
            $sql_select = 'SELECT start_time FROM '.$GLOBALS['ecs']->table('inventory_storage' ).' WHERE end_time = 0 LIMIT 1';
            $start_time = $GLOBALS['db']->getOne($sql_select);
            $where_inventory     = " WHERE i.confirm_time BETWEEN $start_time AND $end_time-1";
            $where_pre_inventory = " WHERE i.confirm_time<$start_time AND i.shipping_time BETWEEN $start_time AND $end_time-1";
            $wait_shipping = ' AND order_status=1 AND shipping_status=0 ';
            $had_shipping  = ' AND order_status=5 AND shipping_status=1 ';

            $place_goods_num      = $where_inventory.$wait_shipping;
            $delive_goods_num     = $where_inventory." AND shipping_time BETWEEN $start_time AND $end_time-1 ".$had_shipping;
            $before_inventory_num = $where_pre_inventory.$had_shipping;

            $goods_list['place_goods_num']        = get_goods_num($place_goods_num);
            $goods_list['delive_goods_num']       = get_goods_num($delive_goods_num);
            $goods_list['before_inventory_num']   = get_goods_num($before_inventory_num);

            $sql_select = 'SELECT storage_id,goods_sn,place_goods_num,delive_goods_num,before_inventory_num FROM '.$GLOBALS['ecs']->table('inventory_storage')
                .' WHERE end_time=0';
            $pre_inventory = $GLOBALS['db']->getAll($sql_select);

            $sql_select = 'SELECT SUM(quantity) quantity,goods_sn FROM '.$GLOBALS['ecs']->table('stock_goods').' GROUP BY goods_sn';
            $actual_quantity = $GLOBALS['db']->getAll($sql_select);

            foreach ($pre_inventory as &$pre) {
                if($goods_list['place_goods_num']){
                    foreach($goods_list['place_goods_num'] as &$place){
                        if($pre['goods_sn'] == $place['goods_sn']){
                            $pre['place_goods_num'] = $place['goods_number'];
                            unset($place);
                            break;
                        }
                    }
                }

                if($goods_list['delive_goods_num']){
                    foreach($goods_list['delive_goods_num'] as &$delive){
                        if($pre['goods_sn'] == $delive['goods_sn']){
                            $pre['delive_goods_num'] = $delive['goods_number'];
                            unset($delive);
                            break;
                        }
                    }

                }

                if ($goods_list['before_inventory_num']) {
                    foreach ($goods_list['before_inventory_num'] as $before){
                        if($pre['goods_sn'] == $before['goods_sn']){
                            $pre['before_inventory_num'] = $before['goods_number'];
                            unset($before);
                            break;
                        }
                    }

                }

                foreach($actual_quantity as $actual){
                    if($actual['goods_sn'] == $pre['goods_sn']){
                        $pre['actual_quantity'] = intval($actual['quantity']);
                        unset($actual);
                        break;
                    }
                }

                if(!$pre['actual_quantity']){
                    $pre['actual_quantity'] = 0;
                }

                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('inventory_storage')
                    ." SET end_time=$end_time,place_goods_num={$pre['place_goods_num']},delive_goods_num={$pre['delive_goods_num']},before_inventory_num={$pre['before_inventory_num']},actual_quantity={$pre['actual_quantity']}"
                    ." WHERE storage_id={$pre['storage_id']}";

                $GLOBALS['db']->query($sql_update);
                record_operate($sql_update, 'inventory_storage');
            }

            $filter = array(
                'start_time' => $start_time,
                'end_time'   => $end_time
            );

            $time_filter = array(
                'start_time'      => date('Y-m-d H:i:s',$start_time),
                'end_time'        => date('Y-m-d H:i:s',$end_time),
                'unix_start_time' => $start_time,
                'unix_end_time'   => $end_time
            );

            $res = array_merge($res,$time_filter);

            $result              = get_inventory_storage($filter);
            $inventory_storage   = $result['inventory_list'];
            $filter              = $result['filter'];
            $filter['condition'] = "&start_time=$start_time&end_time=$end_time";
            $filter['act']       = 'sch_inventory_list';

            $smarty->assign('inventory_storage',$inventory_storage);
            $smarty->assign('filter',$filter);
            $res['main']    = $smarty->fetch('inventory_list_div.htm');
            $res['message'] = '盘点成功';
            break;
        }
    }else{
        $res['message'] = '你没有权限盘点库存！';
    }

    die($json->encode($res));
}

//搜索库存盘点记录
elseif ($_REQUEST['act'] == 'sch_inventory_list'){

    $start_time = intval($_REQUEST['start_time']);
    $end_time   = intval($_REQUEST['end_time']);
    $brand_id   = intval($_REQUEST['brand_id']);
    $goods_sn   = mysql_real_escape_string($_REQUEST['goods_id']);
    $keyword    = mysql_real_escape_string($_REQUEST['keyword']);
    $where      = ' WHERE 1 ';
    $condition  = '';
    $page       = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $page_size  = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 15;

    if($start_time && $end_time){
        $where     .= " AND start_time=$start_time AND end_time=$end_time";
        $condition .= "&start_time=$start_time&end_time=$end_time";
    }else{
        $where .= ' AND end_time=0';
    }

    if($brand_id && $brand_id != 0){
        $where     .= " AND brand_id=$brand_id";
        $condition .= "&brand_id=$brand_id";
    }

    if($goods_sn && $goods_sn != ''){
        $where .= " AND i.goods_sn='$goods_sn'";
        $condition .= "&goods_sn=$goods_sn";
    }

    if($keyword && $keyword != ''){
        $where     .= " AND (i.goods_sn LIKE '%$keyword%' OR i.goods_name LIKE '%$keyword%')";
        $condition .= "&keyword=$keyword";
    }

    $sql_select   = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('inventory_storage')
        .' i,'.$GLOBALS['ecs']->table('goods').' g'.$where.' AND i.goods_sn=g.goods_sn';
    $record_count = $GLOBALS['db']->getOne($sql_select);

    $sql_select = 'SELECT i.storage_id,i.goods_sn,i.goods_name,start_time,end_time,quantity,place_goods_num,delive_goods_num,(quantity-place_goods_num-delive_goods_num)AS logic_quantity,before_inventory_num,actual_quantity FROM '.$GLOBALS['ecs']->table('inventory_storage')
        .' i,'.$GLOBALS['ecs']->table('goods').' g '
        .$where.' AND i.goods_sn=g.goods_sn ORDER BY place_goods_num DESC, delive_goods_num DESC,before_inventory_num DESC LIMIT '.($page-1)*$page_size.",$page_size";
    $inventory_storage = $GLOBALS['db']->getAll($sql_select);

    $filter                 = break_pages($record_count,$page_size,$page);
    $filter['act']          = 'sch_inventory_list';
    $filter['page']         = $page;
    $filter['page_size']    = $page_size;
    $filter['condition']    = $condition;
    $filter['record_count'] = $record_count;

    $smarty->assign('filter',$filter);
    $smarty->assign('inventory_storage',$inventory_storage);

    if(isset($_REQUEST['print'])){
        $smarty->assign('start_time',date('Y-m-d H:i:s',$start_time));
        $smarty->assign('end_time',date('Y-m-d H:i:s',$end_time));
        $smarty->display('inventory_list_print.htm');
        exit();
    }else{
        $p_url = "storage.php?act=sch_inventory_list&print=true&page=$page&page_size=$record_count{$condition}";

        $res['p_url']           = $p_url;
        $res['response_action'] = 'search_service';
        $res['main']            = $smarty->fetch('inventory_list_div.htm');
    }

    die($json->encode($res));
}

//搜索商品于select控件
elseif ($_REQUEST['act'] == 'get_goods_by_brand'){
    $brand_id = intval($_REQUEST['brand_id']);
    $goods_id = intval($_REQUEST['goods_id']);

    $goods_list = get_sel_goods($goods_id,$brand_id);

    die($json->encode($goods_list));
}

elseif ($_REQUEST['act'] == 'get_inventory_time'){

    $res['time_type'] = $time_type = intval($_REQUEST['time_type']);
    $time_val  = intval($_REQUEST['time_val']);

    $where = $time_type == 0 ? " WHERE start_time=$time_val" : " WHERE end_time=$time_val";

    $sql_select = 'SELECT start_time,end_time FROM '.$GLOBALS['ecs']->table('inventory_storage').$where
        .' GROUP BY start_time LIMIT 1';
    $result     =  $GLOBALS['db']->getRow($sql_select);

    $time_val        = $time_type == 0 ? $result['end_time'] : $result['start_time'];
    $res['time_val'] = $time_val;

    die($json->encode($res));
}

//修改实际库存
elseif ($_REQUEST['act'] == 'mod_actual_quantity'){

    $quantity   = intval($_REQUEST['quantity']);
    $storage_id = intval($_REQUEST['storage_id']);

    $res = array(
        'req_msg' => true,
        'code'    => false,
        'message' => '',
        'timeout' => 2000
    );

    if($storage_id){
        $sql_update  = 'UPDATE '.$GLOBALS['ecs']->table('inventory_storage')." SET actual_quantity=$quantity WHERE storage_id=$storage_id";
        $res['code'] = $GLOBALS['db']->query($sql_update);
        if($res['code']){
            record_operate($sql_update, 'inventory_storage');
            $res['message']  = '修改成功';
            $res['quantity'] = '<label onclick = "modActualQuantity(this,'.$quantity
                .",$storage_id)\">$quantity";
            $res['storage_id']    = $storage_id;
        }
    }

    die($json->encode($res));
}

// 库存警告设置
elseif ($_REQUEST['act'] == 'stock_alarm_site'){
    $role_list  = $tmp_role_list =  get_role_list();
    $admin_list = get_role_admin_list();
    array_unshift($tmp_role_list,array('role_id'=>0,'role_name'=>'未分配部门'));

    foreach($tmp_role_list as &$role){
        foreach($admin_list as $admin){
            if($role['role_id'] == $admin['role_id']){
                $role['admin_list'][] = $admin;
                unset($admin);
            }
        }
    }

    $smarty->assign('admin_list',$tmp_role_list);
    $smarty->assign('role_list',$role_list);

    $res['main'] = $smarty->fetch('stock_alarm_site.htm');

    die($json->encode($res));
}

// 列出部门员工
elseif ($_REQUEST['act'] == 'list_admin'){
    $res = get_role_admin_list();
    die($json->encode($res));
}

//库存提醒员工设置
elseif ($_REQUEST['act'] == 'set_alarm_admin'){
    $admin_list = mysql_real_escape_string($_REQUEST['admin_list']);
    $res = array(
        'req_msg' => true,
        'message' => '',
        'timeout' => 2000,
        'code'    => false
    );

    $admin_list = explode(',',$admin_list);
    $admin_list = implode("','",$admin_list);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_user').
        " SET action_list=CONCAT(action_list,'stock_alarm_confirm,') WHERE user_id IN('$admin_list')";

    $result = $GLOBALS['db']->query($sql_update);
    if($result){
        record_operate($sql_update, 'admin_user');
        $res['code']    = true;
        $res['message']  = '设置成功';
    }else{
        $res['message'] = '设置失败';
    }

    die($json->encode($res));
}

/*订货单管理*/
elseif ($_REQUEST['act'] == 'order_sheet'){
    if(admin_priv('order_sheet','',false)){
        $sql_one = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_sheet').
            ' WHERE status<2';
        $sql_select = 'SELECT o.order_sheet_id,o.goods_sn,o.goods_name,o.add_time,o.production_day,o.manufacturer,u.unit_name,o.quantity,a.user_name FROM '.
            $GLOBALS['ecs']->table('order_sheet').' AS o LEFT JOIN'.
            $GLOBALS['ecs']->table('goods_unit').' AS u ON o.unit_id=u.unit_id LEFT JOIN '.
            $GLOBALS['ecs']->table('admin_user').' AS a ON o.add_admin=a.user_id'.
            ' WHERE o.status<2 ORDER BY add_time ASC';
        $act = 'order_sheet';

        $result           = filter_page($sql_one,$sql_select,$act);
        $filter           = $result['filter'];
        $order_sheet_list = $result['result'];

        foreach($filter as $key=>$val){
            $smarty->assign($key,$val);
        }

        foreach($order_sheet_list as &$val){
            $val['add_time']       = date('Y-m-d',$val['add_time']);
            $val['production_day'] = date('Y-m-d',$val['production_day']);
        }

        $smarty->assign('order_sheet_list',$order_sheet_list);
        $smarty->assign('brand_list',get_brand_id());
        $smarty->assign('dst_script','storage');
        $smarty->assign('foot_page',$smarty->fetch('page_fragment.htm'));
        $smarty->assign('order_sheet_div',$smarty->fetch('order_sheet_div.htm'));

        $res['main'] = $smarty->fetch('order_sheet.htm');

        die($json->encode($res));
    }
}

//添加订货操作
elseif ($_REQUEST['act'] == 'add_order_sheet_done'){

    $goods_sn       = mysql_real_escape_string($_REQUEST['goods_sn']);
    $goods_name     = mysql_real_escape_string($_REQUEST['goods_name']);
    $production_day = strtotime($_REQUEST['production_day']);
    $manufacturer   = mysql_real_escape_string($_REQUEST['manufacturer']);
    $quantity       = intval($_REQUEST['quantity']);
    $unit_id        = intval($_REQUEST['goods_unit']);


    $res = array(
        'req_msg'    => true,
        'timeout'    => 2000,
        'code'       => falsea,
        'message'    => '',
        'btncontent' => false
    );

    if($goods_sn != '' && $quantity != 0){
        $sql_insert = 'REPLACE INTO '.$GLOBALS['ecs']->table('order_sheet').
            '(goods_sn,goods_name,production_day,manufacturer,quantity,unit_id,add_admin,add_time)VALUE('.
            "'$goods_sn','$goods_name','$production_day','$manufacturer',$quantity,$unit_id,{$_SESSION['admin_id']},{$_SERVER['REQUEST_TIME']})";
        $res['code'] = $GLOBALS['db']->query($sql_insert);

        if($res['code']){
            $res['message'] = '成功添加订货单';
        }
    }else{
        $res['message'] = '添加订货单失败';
    }

    die($json->encode($res));
}


/*添加订货单表单*/
elseif($_REQUEST['act'] == 'add_order_sheet_form'){

    $goods_sn   = mysql_real_escape_string($_REQUEST['goods_sn']);
    $goods_name = mysql_real_escape_string($_REQUEST['goods_name']);

    $goods_info = array(
        'goods_sn'   => $goods_sn,
        'goods_name' => $goods_name,
    );

    $sql_select = 'SELECT unit_id,unit_name FROM '.$GLOBALS['ecs']->table('goods_unit');
    $goods_unit = $GLOBALS['db']->getAll($sql_select);

    //厂家
    $sql_select = 'SELECT manufacturer FROM '.$GLOBALS['ecs']->table('stock_goods').
        " WHERE goods_sn='$goods_sn' GROUP BY goods_sn";
    $goods_info['manufacturer_list'] = $GLOBALS['db']->getCol($sql_select);

    $smarty->assign('goods_info',$goods_info);
    $smarty->assign('goods_unit',$goods_unit);

    $res['message']    = $smarty->fetch('add_order_sheet.htm');
    $res['title']      = '添加订货单';
    $res['timeout']    = '30000';
    $res['btncontent'] = false;

    die($json->encode($res));
}

/*搜索订货订单*/
elseif($_REQUEST['act'] == 'sch_order_sheet'){

    $goods_sn   = intval($_REQUEST['goods_id']);
    $goods_name = mysql_real_escape_string($_REQUEST['goods_name']);
    $add_time   = mysql_real_escape_string($_REQUEST['add_time']);
    $conditon   = '';
    $where      = ' WHERE o.status<2';

    if($goods_sn != 0){
        $where      .= " AND o.goods_sn=$goods_sn ";
        $condition  .= "&goods_sn=$goods_sn";
    }

    if($goods_name != ''){
        $where     .= " AND o.goods_name LIKE '%$goods_name%' ";
        $condition .= "&goods_name=$goods_name";
    }

    if($add_time != ''){
        $start_time    = strtotime($add_time.' 00:00:00');
        $end_time      = strtotime($add_time.' 23:59:59');
        $where      .= " AND o.add_time>=$start_time AND o.add_time<=$end_time ";
        $condition  .= "&add_time=$add_time";
    }

    $sql_one = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('order_sheet').' o '.$where;

    $sql_select = 'SELECT o.order_sheet_id,o.goods_sn,o.goods_name,o.add_time,o.production_day,o.manufacturer,u.unit_name,o.quantity,o.status,a.user_name FROM '.
        $GLOBALS['ecs']->table('order_sheet').' AS o LEFT JOIN'.
        $GLOBALS['ecs']->table('goods_unit').' AS u ON o.unit_id=u.unit_id LEFT JOIN '.
        $GLOBALS['ecs']->table('admin_user').' AS a ON o.add_admin=a.user_id'.$where.
        ' ORDER BY add_time ASC';

    $result = filter_page($sql_one,$sql_select,'sch_order_sheet');

    $filter           = $result['filter'];
    $filter['source'] = 'storage.php';
    $order_sheet_list = $result['result'];

    foreach($filter as $key=>$val){
        $smarty->assign($key,$val);
    }

    foreach($order_sheet_list as &$val){
        $val['add_time'] = date('Y-m-d',$val['add_time']);
        $val['production_day'] = date('Y-m-d',$val['production_day']);
    }

    $res['response_action'] = 'search_service';
    $smarty->assign('order_sheet_list',$order_sheet_list);
    //$smarty->assign('filter',$filter);
    //$smarty->assign('foot_page',$smarty->fetch('foot_page.htm'));
    $smarty->assign('foot_page',$smarty->fetch('page_fragment.htm'));
    $res['main'] = $smarty->fetch('order_sheet_div.htm');

    die($json->encode($res));
}

//订货列表操作
elseif($_REQUEST['act'] == 'control_order_sheet'){

    $behave         = mysql_real_escape_string($_REQUEST['behave']);
    $order_sheet_id = intval($_REQUEST['order_sheet_id']);
    $mod_time       = $_SERVER['REQUEST_TIME'];
    $res            = array(
        'req_msg' => true,
        'timeout' => 2000,
        'code'    => false,
        'message' => ''
    );

    switch($behave){
    case 'del' :
        $tr_index = intval($_REQUEST['tr_index']);
        $sql_del = 'UPDATE '.$GLOBALS['ecs']->table('order_sheet')." SET status=3,mod_time=$mod_time".
            " WHERE order_sheet_id=$order_sheet_id";
        $result = $GLOBALS['db']->query($sql_del);
        $res['code'] = $result;
        if($result){
            record_operate($sql_del, 'order_sheet');
            $res['message'] = '删除成功';
        }else{
            $res['message'] = '删除失败';
        }
        $res['tr_index'] = $tr_index;
        break;
    }

    $res['behave'] = $behave;

    die($json->encode($res));
}

/* 更新商品建议售价 */
elseif ($_REQUEST['act'] == 'update_min_price') {
    $msg = array (
        'req_msg' => true,
        'timeout' => 2000,
    );

    $value    = floatval($_REQUEST['field']);
    $goods_sn = $_REQUEST['goods_sn'];
    if (empty($value)) {
        $msg['message'] = '请输入正确的价格！';

        echo $json->encode($msg);
        return;
    }

    if (!is_numeric($goods_sn)) {
        $msg['message'] = '商品编号错误，请刷新页面后再次尝试！';

        echo $json->encode($msg);
        return;
    }

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods')." SET min_price={$value} WHERE goods_sn={$goods_sn}";
    if ($GLOBALS['db']->query($sql_update)) {
        record_operate($sql_update, 'goods');
        $msg['message'] = '商品建议售价已修改成功！';
        $msg['value']   = sprintf('%.2f', $value);
        $msg['form_id'] = "storage.php?act=update_min_price&goods_sn={$goods_sn}";

        echo $json->encode($msg);
        return;
    } else {
        $msg['message'] = '商品编号错误 或 商品已被其他用户删除！请确认后再次尝试修改！';

        echo $json->encode($msg);
        return;
    }
}

/* 修改商品最低价格 */
elseif ($_REQUEST['act'] == 'edit_cost_price') {
    $msg = array (
        'req_msg' => true,
        'timeout' => 2000,
    );

    $value    = floatval($_REQUEST['field']);
    $goods_sn = $_REQUEST['goods_sn'];
    if (empty($value)) {
        $msg['message'] = '请输入正确的价格！';

        echo $json->encode($msg);
        return;
    }

    if (!is_numeric($goods_sn)) {
        $msg['message'] = '商品编号错误，请刷新页面后再次尝试！';

        echo $json->encode($msg);
        return;
    }

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods')." SET cost_price={$value} WHERE goods_sn={$goods_sn}";
    if ($GLOBALS['db']->query($sql_update)) {
        record_operate($sql_update, 'goods');
        $msg['message'] = '商品最低价格已修改成功！';
        $msg['value']   = sprintf('%.2f', $value);
        $msg['form_id'] = "storage.php?act=edit_cost_price&goods_sn={$goods_sn}";

        echo $json->encode($msg);
        return;
    } else {
        $msg['message'] = '商品编号错误 或 商品已被其他用户删除！请确认后再次尝试修改！';

        echo $json->encode($msg);
        return;
    }
}

/* 修改商品活动价格 */
elseif ($_REQUEST['act'] == 'edit_promote_price') {
    $msg = array (
        'req_msg' => true,
        'timeout' => 2000,
    );

    $value    = floatval($_REQUEST['field']);
    $goods_sn = $_REQUEST['goods_sn'];
    if (empty($value)) {
        $msg['message'] = '请输入正确的价格！';
        echo $json->encode($msg);
        return;
    }
    if (!is_numeric($goods_sn)) {
        $msg['message'] = '商品编号错误，请刷新页面后再次尝试！';
        echo $json->encode($msg);
        return;
    }

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods')." SET promote_price={$value} WHERE goods_sn={$goods_sn}";
    if ($GLOBALS['db']->query($sql_update)) {
        record_operate($sql_update, 'goods');
        $msg['message'] = '商品最低价格已修改成功！';
        $msg['value']   = sprintf('%.2f', $value);
        $msg['form_id'] = "storage.php?act=edit_promote_price&goods_sn={$goods_sn}";
        echo $json->encode($msg);
        return;
    } else {
        $msg['message'] = '商品编号错误 或 商品已被其他用户删除！请确认后再次尝试修改！';
        echo $json->encode($msg);
        return;
    }
}
/*仓库调拨*/
//author : wuyuanhang
elseif($_REQUEST['act'] == 'warehouse_allot'){
    $warehouse_list = get_warehouse('simple');
    $allot_list     = get_allot_list();

    if($get_allot_list){
        foreach($get_allot_list as &$al){
            foreach($warehouse_list as $wl){
                if($al['in_storage'] == $wl['warehouse_id']){
                    $al['in_storage'] = $wl['warehouse_name'];
                }

                if($al['out_storage'] == $wl['warehoust_id']){
                    $al['out_storage'] = $wl['warehouse_name'];
                }
            }
        }
    }

    if(isset($_REQUEST['from_sch'])){
        $res['main'] = $smarty->fetch('warehouse_allot_div.htm');
    }else{
        $smarty->assign('warehouse_list',$warehouse_list);
        $smarty->assign('allot_list_div',$smarty->fetch('warehouse_allot_div.htm'));
        $res['main'] = $smarty->fetch('warehouse_allot.htm');
    }

    die($json->encode($res));
}

/*创建仓库调拨*/
elseif($_REQUEST['act'] =='create_allot'){
    $behave = isset($_REQUEST['behave']) ? mysql_real_escape_string($_REQUEST['behave']) : '';
    $res['response_action'] = 'search_service';
    $warehouse_list = get_warehouse('simple','');

    $smarty->assign('warehouse',$warehouse_list);
    if('show' == $behave){
        $smarty->assign('goods_div',$smarty->fetch('allot_goods.htm'));
        $res['main'] = $smarty->fetch('add_allot.htm');
    }elseif('add' == $behave){

    }elseif('modify' == $behave){

    }

    die($json->encode($res));
}

//商品批次和批次库存
elseif($_REQUEST['act'] == 'get_pdc_day'){
    $result = get_pdc_day(' AND quantity>0 ');
    $res['text'] = '请选择批次';

    if($result){
        $res['length'] = count($result);
        $res['id'] = 'production_day';

        foreach($result as &$val){
            $res['options'][] = array(
                'value' => $val['rec_id'],
                'text' => "{$val['production_day']}【库存：{$val['quantity']}】",
            );
        }
    }

    die($json->encode($res));
}

/*修改商品状态*/
elseif($_REQUEST['act'] == 'mod_goods_status'){
    $goods_sn = isset($_REQUEST['goods_sn']) ? mysql_real_escape_string($_REQUEST['goods_sn']) : '';
    $status = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : 0;
    $td_id = isset($_REQUEST['td_id']) ? mysql_real_escape_string($_REQUEST['td_id']) : '';
    $res = array(
        'timeout' => 2000,
        'code'    => false,
        'message' => '',
        'req_msg' => true,
        'td_id'   => $td_id
    );

    if($goods_sn){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').
            " SET status=$status WHERE goods_sn='$goods_sn'";

        $res['code']    = $GLOBALS['db']->Query($sql_update);
        $message = $status == 1 ? ' 还会进货该商品' : '不再货该商品';
        $res['message'] = $res['code'] ? "操作成功,$message" : '操作失败,请联系技术部';

        if($res['code']){
            $title  = $status == 1 ? '点击不再进货该商品' : '点击恢复进货该商品';
            $src    = $status == 1 ? 'images/track.png' : 'images/nottrack.gif';
            $status = $status == 1 ? 0 : 1;
            $res['content'] = '<img src="'.$src.'" onclick="modGoodsStatus(this)" value="'.
                $goods_sn.'" sta="'.$status.'" class="png_btn" title="'.$title.'">';
        }

    }else{
        $res['message'] = '操作失败，请联系技术部';
    }

    die($json->encode($res));
}

/* 获取套餐结构 */
elseif ('get_package_struct' == $_REQUEST['act']) {
    $package_sn = mysql_real_escape_string($_REQUEST['psn']);
    // 套餐信息
    $sql_select = 'SELECT packing_id,packing_name,packing_desc,FROM_UNIXTIME(add_time, "%Y-%m-%d") add_time FROM '.
        $GLOBALS['ecs']->table('packing')." WHERE packing_desc='$package_sn'";
    $package_info = $GLOBALS['db']->getRow($sql_select);
    // 套餐商品列表
    $sql_select = 'SELECT goods_sn,goods_name,num goods_number FROM '.$GLOBALS['ecs']->table('packing_goods').
        " WHERE packing_id={$package_info['packing_id']}";
    $goods_list = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('package_info', $package_info);
    $smarty->assign('package',      $goods_list);
    $res = $smarty->fetch('package_struct.htm');
    die($res);
}

/* 修改商品分类 */
elseif ('goods_effect' == $_REQUEST['act']) {
    $sql_select = 'SELECT eff_id id, eff_name name FROM '.$GLOBALS['ecs']->table('effects').' WHERE available=1 ORDER BY sort';
    $list = $GLOBALS['db']->getAll($sql_select);
    $smarty->assign('list',  $list);
    $smarty->assign('type',  $_REQUEST['type']);
    $smarty->assign('field', $_REQUEST['info']);
    $html = $smarty->fetch('detail.htm');
    $html .= '<input type="hidden" id="ID" value="'.$_REQUEST['id'].'">';
    $result = array(
        'info' => $_REQUEST['info'],
        'type' => strtolower($_REQUEST['type']),
        'main' => $html
    );
    die($json->encode($result));
}

/* 更新商品信息 */
elseif ('save' == $_REQUEST['act']) {
    $id = intval($_REQUEST['id']);
    $field = $_REQUEST['info'];
    $value = intval($_REQUEST['value']);
    // 更新商品分类
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods')." SET $field=$value WHERE goods_id=$id";
    $msg = array('req_msg'=>true, 'timeout'=>2000);
    if ($GLOBALS['db']->query($sql_update)) {
        $msg['id'] = $id;
        $msg['type'] = strtolower($_REQUEST['type']);
        $msg['info'] = $field;
        $msg['act'] = $field;
        // 获取更新后的分类信息
        $sql_select = 'SELECT eff_name FROM '.$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('effects')
            ." e WHERE e.eff_id=g.goods_effect AND g.goods_id=$id";
        $msg['main'] = $GLOBALS['db']->getOne($sql_select);
        $msg['message'] = '商品分类更新成功！';
    } else {
        $msg['message'] = '商品分类更新失败，请稍后再试！';
    }
    die($json->encode($msg));
}

/**
 * 获取订货单列表
 */
function stock_list($user_id, $account_type = '') {
    // 检查参数
    // $where = " WHERE user_id = '$user_id' ";
    if (in_array($account_type, array('user_money', 'frozen_money', 'rank_points', 'pay_points')))
    {
        $where .= " AND $account_type <> 0 ";
    }

    //初始化分页参数
    $filter = array(
        'user_id'       => $user_id,
        'account_type'  => $account_type
    );

    //查询记录总数，计算分页数
    $sql = 'SELECT COUNT(distinct stock_sn) FROM ' . $GLOBALS['ecs']->table('stock') ;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    //查询记录
    $sql = 'SELECT stock_id, stock_sn, FROM_UNIXTIME(arrival_day, "%Y-%m-%d") arrival_day, confirmer, contacter, phone FROM '.$GLOBALS['ecs']->table('stock').' GROUP BY stock_sn';
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
    $arr = array();

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['change_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['change_time']);
        $arr[] = $row;
    }

    return array('stock_list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

/**
 * 套餐列表
 */
function package_list ()
{
    $condition = '';
    $_REQUEST = addslashes_deep($_REQUEST);
    $filter['packing_name'] = isset($_REQUEST['packing_name']) ? trim($_REQUEST['packing_name']) : 0;
    $filter['packing_desc'] = isset($_REQUEST['packing_desc']) ? trim($_REQUEST['packing_desc']) : 0;

    // 收集查询条件
    foreach ($filter as $key=>$val)
    {
        if (!empty($val))
        {
            $condition .= "&$key=$val";
        }
    }

    $ex_where = ' WHERE 1 ';

    // 通过名字查询套餐
    if ($filter['packing_name'])
    {
        $ex_where .= " AND packing_name LIKE '%{$filter['packing_name']}%' ";
    }

    // 通过编号查询套餐
    if ($filter['packing_desc'])
    {
        $ex_where .= " AND packing_desc LIKE '%{$filter['packing_desc']}%' ";
    }

    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0){
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }else{
        $filter['page_size'] = 20;
    }

    // 记录总数
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('packing').$ex_where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);

    $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页
    $page_set = array (1,2,3,4,5,6,7);
    if ($filter['page'] > 4){
        foreach ($page_set as &$val)
        {
            $val += $filter['page'] -4;
        }
    }

    if (end($page_set) > $filter['page_count']){
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--){
            if ($filter['page_count'] - $i > 0){
                $page_set[] = $filter['page_count'] - $i;
            }
        }
    }

    $sql_select = 'SELECT packing_id, packing_name,packing_desc,packing_price,is_on_sale,add_time FROM '.
        $GLOBALS['ecs']->table('packing').$ex_where.' ORDER BY add_time DESC LIMIT '.
        ($filter['page'] -1)*$filter['page_size'].",{$filter['page_size']}";
    $res = $GLOBALS['db']->getAll($sql_select);

    // 格式化时间
    foreach ($res as &$val) {
        $val['add_time'] = date('Y-m-d', $val['add_time']);
    }

    $arr = array (
        'package'      => $res,
        'filter'       => $filter,
        'page_count'   => $filter['page_count'],
        'record_count' => $filter['record_count'],
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page'],
        'page_set'     => $page_set,
        'condition'    => $condition,
        'start'        => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'          => $filter['page']*$filter['page_size'],
    );

    return $arr;
}

/**
 * 获取套餐信息
 */
function packing_info()
{
    $packing_id = intval($_REQUEST['packing_id']);
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('packing')." WHERE packing_id=$packing_id";
    $packing_info = $GLOBALS['db']->getRow($sql_select);

    return $packing_info;
}

/**
 * 套餐商品列表
 */
function packing_goods_list()
{
    $packing_id = intval($_REQUEST['packing_id']);
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('packing_goods')." WHERE packing_id=$packing_id";
    $goods_list = $GLOBALS['db']->getAll($sql_select);

    return $goods_list;
}

function generate_goods_sn_crm($offset = 1)
{
    // 生成商品编号
    $goods_sn = intval($_REQUEST['run']); // 自营。。。

    // 品牌编号
    for ($i = 0; $i < 3-strlen($_REQUEST['brand_id']); $i++) {
        $goods_sn .= '0';
    }

    $goods_sn .= intval($_REQUEST['brand_id']);

    // 获取所选品牌中的商品编号
    $sql_select = 'SELECT goods_sn FROM '.$GLOBALS['ecs']->table('goods')." WHERE brand_id={$_REQUEST['brand_id']} ORDER BY goods_sn DESC";
    $last_goods_sn = $GLOBALS['db']->getOne($sql_select);

    $tmp_sn = substr($last_goods_sn, 4,3) + $offset;
    for ($i = 0; $i < 3-strlen($tmp_sn); $i++) {
        $goods_sn .= '0';
    }

    $goods_sn .= intval($tmp_sn) .intval($_REQUEST['goods_type']);

    // 验证新生成的商品编号是否已存在
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('goods')." WHERE goods_sn={$goods_sn}";
    $res = $GLOBALS['db']->getOne($sql_select);
    if ($res > 0) {
        return generate_goods_sn_crm(++$offset);
    }

    return $goods_sn;
}

/**
 * 获取商品详细信息
 */
function get_goods_detail($goods_id)
{
    $sql_select = 'SELECT g.goods_id,g.goods_sn,g.goods_name,g.shop_price,g.shelflife,g.take_days,g.add_time,g.goods_weight,b.brand_name,'.
        'c.cat_name FROM '.$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('brand').' b,'.$GLOBALS['ecs']->table('category').
        " c WHERE c.cat_id=g.cat_id AND b.brand_id=g.brand_id AND goods_id=$goods_id";
    $goods_info = $GLOBALS['db']->getRow($sql_select);

    $goods_info['add_time'] = date('Y-m-d', $goods_info['add_time']);
    $goods_info['take_days'] = $goods_info['take_days']/24/3600;

    return $goods_info;
}

/**
 * 商品分类列表
 */
function eff_list() {
    $sql_select = 'SELECT eff_id,eff_name FROM '.$GLOBALS['ecs']->table('effects').' WHERE available=1 ORDER BY sort';
    return $GLOBALS['db']->getAll($sql_select);
}

/**
 * 统计发出/退回的商品数量
 */
function stats_shipping_goods ()
{
    $ex_where  = get_where_for_shipping_log();
    $condition = $ex_where['condition'];
    $ex_where  = $ex_where['ex_where'];

    $goods_name = isset($_REQUEST['goods_name']) ? trim(mysql_real_escape_string($_REQUEST['goods_name'])) : '';
    if ($goods_name) {
        $goods_where .= ' AND g.goods_name LIKE "%'.$goods_name.'%"';
    }
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20;
    }

    $mem = new Memcache;
    $mem->connect('127.0.0.1',11211);

    /*Memcache缓存*/
    if(!$mem->get("shipping_log_{$_SESSION['admin_id']}")){
        $sql_append = 'SELECT g.goods_sn, SUM(g.goods_number) goods_number, g.goods_name,LEFT(g.goods_name,13) goods_short_name,g.brand_id FROM '
            .$GLOBALS['ecs']->table('order_goods').' g LEFT JOIN '
            .$GLOBALS['ecs']->table('order_info').' i ON g.order_id=i.order_id ';

        $sql_select = $sql_append."WHERE $ex_where $goods_where AND i.order_status IN (1,5) AND shipping_status "
            ." IN (1,2,4) AND g.goods_sn NOT LIKE '%\_%' GROUP BY g.goods_sn ORDER BY SUM(g.goods_number) DESC ";

        $shipping_goods = $GLOBALS['db']->getAll($sql_select);

        $sql_select = $sql_append." WHERE $ex_where AND "
            .' i.order_status IN (1,5) AND shipping_status IN (1,2,4) AND g.goods_sn LIKE "%\_%" GROUP BY g.goods_sn';
        $package_goods = $GLOBALS['db']->getAll($sql_select);

        $package_sn = array();
        foreach ($package_goods as $v){
            $package_sn[] = $v['goods_sn'];
            $packing_goods[$v['goods_sn']] += $v['goods_number'];
        }

        $package_sn = implode("','", $package_sn);
        $sql_select = 'SELECT p.packing_desc,pg.goods_id,g.goods_sn,pg.num FROM '
            .$GLOBALS['ecs']->table('packing').' p LEFT JOIN '
            .$GLOBALS['ecs']->table('packing_goods')
            .' pg ON pg.packing_id=p.packing_id LEFT JOIN '.$GLOBALS['ecs']->table('goods')
            ." g ON g.goods_id=pg.goods_id WHERE p.packing_desc IN ('$package_sn')";
        $package_list = $GLOBALS['db']->getAll($sql_select);

        $package = array();
        foreach ($package_list as $val){
            $package[$val['goods_sn']] += $val['num'] * $packing_goods[$val['packing_desc']];
        }

        if($package){
            $pack_goods_sn = array_keys($package);
            $pack_goods_sn = implode("','",$pack_goods_sn);

            $sql_select = 'SELECT goods_sn, goods_name,LEFT(goods_name,13) goods_short_name,brand_id FROM '.
                $GLOBALS['ecs']->table('goods')." WHERE goods_sn IN('$pack_goods_sn')";
            $pack_goods = $GLOBALS['db']->getAll($sql_select);

            if($pack_goods){
                foreach($package as $key=>$val){
                    foreach($pack_goods as &$pg){
                        if($key == $pg['goods_sn']){
                            $pg['goods_number'] = $val;
                        }
                    }
                }
            }

            if($shipping_goods){
                foreach($pack_goods as $key=>&$pg){
                    foreach($shipping_goods as &$sg){
                        if($sg['goods_sn'] == $pg['goods_sn']){
                            $sg['goods_number'] += $pg['goods_number'];
                            unset($pack_goods[$key]);
                        }
                    }
                }

                if(count($pack_goods) > 0){
                    foreach($pack_goods as $val){
                        array_unshift($shipping_goods,$val);
                    }
                }

            }else{
                foreach($pack_goods as $val){
                    array_unshift($shipping_goods,$val);
                }
            }
        }

        if($shipping_goods){
            $shipping_goods_sn = array();
            foreach($shipping_goods as $val){
                $shipping_goods_sn[] = $val['goods_sn'];
            }

            $shipping_goods_sn = implode("','",$shipping_goods_sn);
            if($shipping_goods_sn){
                $where = " WHERE goods_sn IN('$shipping_goods_sn') ";
            }
        }

        //实时库存
        $current_stock = get_current_stock($where);

        $stock = array();
        foreach($current_stock as $value){
            if($value['goods_sn']){
                $stock[trim($value['goods_sn'])] = $value['stock'];
            }
        }

        foreach ($shipping_goods as &$value) {
            $value['current_stock'] = $stock[$value['goods_sn']] <= 800 ? '<font color="red">'.intval($stock[$value['goods_sn']]).'</font>' : $stock[$value['goods_sn']];

            //生产日期
            $sql_select = "SELECT quantity,FROM_UNIXTIME(production_day,'%Y-%m-%d') AS production_day,TIMESTAMPDIFF(month,FROM_UNIXTIME(production_day,'%y-%m-%d'),NOW()) AS diff_date FROM "
                .$GLOBALS['ecs']->table('stock_goods')." WHERE goods_sn='{$value['goods_sn']}'";

            $production_stock = $GLOBALS['db']->getAll($sql_select);

            $value['production_stock']['total'] = count($production_stock);
            $value['production_stock']['stock_list'] = $production_stock;
        }

        if($shipping_goods){
            foreach($shipping_goods as $key=>&$val){
                $goods_number[$key] = $val['goods_number'];
            }

            array_multisort($goods_number,SORT_DESC,$shipping_goods);

            $mem->set('shipping_log'.$_SESSION['admin_id'], $shipping_goods, 0, 1200);
        }
    }else{
        $shipping_goods = $mem->get('shipping_log'.$_SESSION['admin_id']);
    }

    $filter['record_count'] = count($shipping_goods);
    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页
    $page_set = array (1,2,3,4,5,6,7);
    if ($filter['page'] > 4) {
        foreach ($page_set as &$val) {
            $val += $filter['page'] - 4;
        }
    }

    if (end($page_set) > $filter['page_count']) {
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--) {
            if ($filter['page_count'] - $i > 0) {
                $page_set[] = $filter['page_count'] - $i;
            }
        }
    }

    if($shipping_goods){
        $start = ($filter['page'] - 1 ) * $filter['page_size'];
        $end   = $start + $filter['page_size'];

        for($i=$start,$j=1;$i<$end && $j<=$filter['record_count'];$i++,$j++){
            if($shipping_goods[$i]){
                $goods_list[] = $shipping_goods[$i];
            }else{
                break;
            }
        }
        $shipping_goods = $goods_list;
    }

    $filter = array (
        'filter'        => $filter,
        'page_count'    => $filter['page_count'],
        'record_count'  => $filter['record_count'],
        'page_size'     => $filter['page_size'],
        'page'          => $filter['page'],
        'page_set'      => $page_set,
        'condition'     => $condition,
        'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'           => $filter['page']*$filter['page_size'],
        'act'           => 'shipping_log',
    );

    $res = array (
        'shipping_goods' => $shipping_goods,
        'pack_goods_sn'  => $pack_goods_sn,
        'filter'         => $filter
    );

    return $res;
}

/**
 * 分页函数
 */
function page_break ($page_size, $count, $page)
{
    // 设置分页
    $page_set = array (1,2,3,4,5,6,7);
    $page_count = $count > 0 ? ceil($count/$page_size) : 1;
    if ($page > 4) {
        foreach ($page_set as &$val) {
            $val++;
        }
    }

    if (end($page_set) > $page_count) {
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--) {
            if ($page_count - $i > 0) {
                $page_set[] = $page_count - $i;
            }
        }
    }

    return array ('page_set'=>$page_set, 'page_count'=>$page_count, 'page_size'=>$page_size, 'page'=>$page);
}

/**
 * 退回的商品数量
 */
function stats_back_goods ($goods_list)
{
    // 整理商品
    $goods_list = implode(',', $goods_list);

    // 统计条件
    $filter['order_time_start'] = isset($_REQUEST['order_time_start']) ? $_REQUEST['order_time_start'] : 0;// 订单时间
    $filter['order_time_end']   = isset($_REQUEST['order_time_end'])   ? $_REQUEST['order_time_end']   : 0;// 订单时间


    $filter['shipping_time_start'] = isset($_REQUEST['shipping_time_start']) ? $_REQUEST['shipping_time_start'] : 0;// 发货时间
    $filter['shipping_time_end']   = isset($_REQUEST['shipping_time_end'])   ? $_REQUEST['shipping_time_end']   : 0;// 发货时间

    $filter['brand']      = isset($_REQUEST['brand']) ? intval($_REQUEST['brand']) : 0; // 商品品牌
    $filter['goods_name'] = isset($_REQUEST['goods_name']) ? trim($_REQUEST['goods_name']) : '';// 商品名称
    $filter['platform']   = isset($_REQUEST['platform']) ? intval($_REQUEST['platform']) : 0; // 销售平台

    // 分页参数
    $filter['page_no']   = isset($_REQUEST['page_no']) ? intval($_REQUEST['page_no']) : 1;
    $filter['page_size'] = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 20;

    $ex_where = 1;

    // 下单时间
    if ($filter['order_time_start'] && $filter['order_time_end']) {
        $ex_where .= ' AND i.add_time>='.strtotime($filter['order_time_start']).' AND i.add_time<'.strtotime($filter['order_time_end']);
    } else {
        $order_time_start = strtotime(date('Y-m-d 00:00:00'));
        $order_time_end   = $order_time_start + 24*3600;
        $ex_where .= " AND i.add_time>=$order_time_start AND i.add_time<$order_time_end ";
    }

    // 发货时间
    if ($filter['shipping_time_start'] && $filter['shipping_time_end']) {
        $ex_where .= ' AND i.shipping_time>='.strtotime($filter['shipping_time_start']).' AND i.shipping_time<'.strtotime($filter['shipping_time_end']);
    }

    // 商品品牌
    if ($filter['brand']) {
        $ex_where .= " AND g.brand_id={$filter['brand']} ";
    }

    // 商品名称
    if ($filter['goods_name']) {
        $ex_where .= ' AND g.goods_name LIKE "%'.mysql_real_escape_string($filter['goods_name']).'%"';
    }

    // 销售平台
    if ($filter['platform']) {
        $ex_where .= " AND i.team={$filter['platform']} ";
    }

    $sql_select = 'SELECT g.goods_sn, SUM(g.goods_number) goods_number FROM '.
        $GLOBALS['ecs']->table('order_goods').' g LEFT JOIN  '.$GLOBALS['ecs']->table('order_info').
        ' i ON i.order_id=g.order_id,'.$GLOBALS['ecs']->table('back_order').' b '.
        " WHERE $ex_where AND b.order_id=i.order_id AND g.goods_sn IN ($goods_list) GROUP BY g.goods_sn";
    $back_goods = $GLOBALS['db']->getAll($sql_select);

    $res = array();
    foreach ($back_goods as $val){
        $res[$val['goods_sn']] = $val['goods_number'];
    }

    return $res;
}

/*实时库存警报*/
function timely_stock_alarm(){
    /*仓库管理员操作*/
    if(admin_priv('stock_alarm_confirm','',false) && !admin_priv('all','',false)){
        $mod_stock_quantity = true;
        $where              = " AND confirm_sto_admin<>{$_SESSION['admin_id']} AND confirm_sto_times<30 AND edit_status = 0 ";
    }

    $time_now   = $_SERVER['REQUEST_TIME'];
    $sql_select = 'SELECT g.goods_sn,g.goods_name,SUM(s.quantity) AS quantity,warn_number, add_sto_order_time,predict_arrival_time FROM '
        .$GLOBALS['ecs']->table('stock_goods').' s LEFT JOIN '.$GLOBALS['ecs']->table('goods')
        .' g ON g.goods_sn=s.goods_sn'
        ." WHERE g.is_delete=0 AND s.status=1 $where AND warn_number<>0 GROUP BY s.goods_sn ";

    $stock_goods = $GLOBALS['db']->getAll($sql_select.'ORDER BY quantity DESC');

    foreach($stock_goods as $val){
        if($val['quantity'] <= $val['warn_number'] && $val['quantity'] != 0){
            $alarm_stock_goods[] = $val;
        }elseif($val['confirm_sto_admin']){
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods')
                ." SET confirm_sto_admin=0,confirm_sto_times=0,predict_arrival_time=0,edit_status=0"
                ." WHERE goods_sn='{$val['goods_sn']}'";
            $GLOBALS['db']->query($sql_update);
        }
    }

    if($mod_stock_quantity){
        $alarm_stock_goods = $GLOBALS['db']->getRow($sql_select.' HAVING quantity<warn_number LIMIT 1');
        if(!$_REQUEST['sto_alarm_repeat']){
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('stock_goods').' SET edit_status=1 '
                ." WHERE goods_sn='{$alarm_stock_goods['goods_sn']}'";

            $GLOBALS['db']->query($sql_update);
            record_operate($sql_update, 'stock_goods');
        }

        if($alarm_stock_goods){
            $alarm_stock_goods['add_sto_order_time']   = $alarm_stock_goods['add_sto_order_time'] ? date('Y-m-d',$alarm_stock_goods['add_sto_order_time']) : 0 ;
            $alarm_stock_goods['predict_arrival_time'] = $alarm_stock_goods['predict_arrival_time'] ? date('Y-m-d H:i',$alarm_stock_goods['predict_arrival_time']) : 0;
        }

    }

    return $alarm_stock_goods;
}

//统计退货数量
function get_return_number($goods_list){
    if(!$goods_list){
        return $goods_list;
    }

    foreach($goods_list as $val){
        if($val['goods_sn'] && is_numeric($val['goods_sn'])){
            $goods_sn_list[] = $val['goods_sn'];
        }
    }

    if(!empty($_REQUEST['order_time_start']) && !empty($_REQUEST['order_time_end'])){
        if(strlen($_REQUEST['order_time_start'])>10 && strlen($_REQUEST['order_time_end'])>10){
            $order_time_start = strtotime($_REQUEST['order_time_start']);
            $order_time_end   = strtotime($_REQUEST['order_time_end']);
        }else{
            $order_time_start = $_REQUEST['order_time_start'];
            $order_time_end   = $_REQUEST['order_time_end'];
        }
    }else{
        $order_time_start = strtotime(date('Y-m-d 00:00:00',$_SERVER['REQUEST_TIME']));
        $order_time_end   = strtotime(date('Y-m-d 23:59:59',$_SERVER['REQUEST_TIME']));
    }

    if($order_time_start && $order_time_end){
        $where = " AND r.return_time BETWEEN $order_time_start AND $order_time_end ";
    }elseif($shipping_time_start && $shipping_time_end){
        $where = " AND r.return_time BETWEEN $shipping_time_start AND $shipping_time_end ";
    }

    /*发货和退货记录*/
    $sql = 'SELECT g.goods_sn, SUM(g.goods_number) goods_number FROM '
        .$GLOBALS['ecs']->table('order_goods').' g, '
        .$GLOBALS['ecs']->table('returns_order').' r WHERE r.order_id=g.order_id ';

    if($goods_sn_list){
        $goods_sn_list = implode(',',$goods_sn_list);

        $sql_select = $sql." AND g.goods_sn IN($goods_sn_list) AND g.goods_sn NOT LIKE '%\_%' $where GROUP BY g.goods_sn";;

        $return_goods = $GLOBALS['db']->getAll($sql_select);
        foreach($return_goods as $val){
            $res[$val['goods_sn']] = $val['goods_number'];
        }
    }

    // 分解套餐 统计套餐中的商品数量
    $sql_select = $sql." AND g.goods_sn LIKE '%\_%' $where GROUP BY g.goods_sn";
    $package_goods = $GLOBALS['db']->getAll($sql_select);

    if($package_goods){
        $package_sn = array();
        foreach ($package_goods as $v){
            $package_sn[] = $v['goods_sn'];
            $packing_goods[$v['goods_sn']] += $v['goods_number'];
        }

        $package_sn = implode("','", $package_sn);
        $sql_select = 'SELECT p.packing_desc,pg.goods_id,g.goods_sn,pg.num FROM '
            .$GLOBALS['ecs']->table('packing').' p LEFT JOIN '
            .$GLOBALS['ecs']->table('packing_goods')
            .' pg ON pg.packing_id=p.packing_id LEFT JOIN '.$GLOBALS['ecs']->table('goods')
            ." g ON g.goods_id=pg.goods_id WHERE p.packing_desc IN ('$package_sn')";
        $package_list = $GLOBALS['db']->getAll($sql_select);

        $package = array();
        if($package_list){
            foreach ($package_list as $val){
                $package[$val['goods_sn']] += $val['num'] * $packing_goods[$val['packing_desc']];
            }
        }
    }

    if(count($return_goods) && count($package)){
        foreach($package as $key=>&$pack_val){
            foreach($return_goods as &$val){
                if($val['goods_sn'] == $key){
                    $val['goods_number'] += $pack_val;
                    unset($package[$key]);
                }
            }
        }

        if(count($package)){
            foreach($package as $key=>&$val){
                $return_goods[] = array('goods_sn'=>$key,'goods_number'=>$val);
            }
        }
    }elseif(count($package)){
        foreach($package as $key=>&$val){
            $return_goods[] = array('goods_sn'=>$key,'goods_number'=>$val);
        }
    }

    if($return_goods){
        foreach($return_goods as $re_goods){
            foreach($goods_list as &$val){
                if($re_goods['goods_sn'] == $val['goods_sn']){
                    $val['return_number'] += $re_goods['goods_number'];
                }
            }
        }
    }

    return $goods_list;
}

//获取盘点记录
function get_inventory_storage($filter = array()){

    $where = ' WHERE 1 ';
    if($filter){
        foreach($filter as $key => $val){
            $where .= " AND $key=$val";
        }
    }else{
        $where .= ' AND end_time=0 ';
    }


    $page      = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 15;


    $sql_select   = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('inventory_storage').$where;
    $record_count = $GLOBALS['db']->getOne($sql_select);
    $limit        = ' LIMIT '.($page-1)*$page_size.",$page_size";

    $sql = 'SELECT storage_id,goods_sn,goods_name,start_time,end_time,quantity,place_goods_num,delive_goods_num,(quantity-place_goods_num-delive_goods_num) AS logic_quantity,before_inventory_num,actual_quantity FROM '.$GLOBALS['ecs']->table('inventory_storage');

    $sql_select     = $sql.$where.' ORDER BY place_goods_num DESC ,delive_goods_num DESC, before_inventory_num DESC '.$limit;
    $inventory_list = $GLOBALS['db']->getAll($sql_select);

    if(!$inventory_list){
        $sql_select = 'SELECT start_time,end_time FROM '.$GLOBALS['ecs']->table('inventory_storage')
            .' ORDER BY start_time DESC LIMIT 1';
        $row = $GLOBALS['db']->getRow($sql_select);
        if($row){
            $condition = "&start_time={$row['start_time']}&end_time={$row['end_time']}";
            $where = " WHERE start_time={$row['start_time']} AND end_time={$row['end_time']}";
            $inventory_list = $GLOBALS['db']->getAll($sql.$where.' ORDER BY start_time DESC'.$limit);
        }
    }

    $filter              = break_pages($record_count,$page_size,$page);
    $filter['act']       = 'inventory_list';
    $filter['condition'] = $condition;
    $filter['page']      = $page;
    $filter['page_size'] = $page_size;
    $filter['record_count'] = $record_count;

    $inventory_list = array(
        'inventory_list' => $inventory_list,
        'filter'         => $filter
    );

    return $inventory_list;
}

function get_role_admin_list(){
    $role_id = intval($_REQUEST['role_id']);
    $where = ' WHERE status=1 ';
    if($role_id){
        $where .= " AND role_id=$role_id";
    }

    $sql_select = 'SELECT user_id,user_name,role_id FROM '.$GLOBALS['ecs']->table('admin_user').$where;
    $res = $GLOBALS['db']->getAll($sql_select);
    return $res;
}

function sort_by_spell(){
}

//统计今天没发货但有退货的商品
function get_return_no_shipping_goods($goods_list){
    $shipping_goods_sn = array();
    foreach($goods_list as $val){
        $shipping_goods_sn[] = $val['goods_sn'];
    }

    return $goods_list;
}

//每日发货查询条件
function get_where_for_shipping_log(){
    if(strlen($_REQUEST['order_time_start'])>0 && strlen($_REQUEST['order_time_end'])>0){
        $order_time_start   =   strlen($_REQUEST['order_time_start'])>10 ? strtotime($_REQUEST['order_time_start']): $_REQUEST['order_time_start'];
        $order_time_end     =   strlen($_REQUEST['order_time_end'])>10 ? strtotime($_REQUEST['order_time_end']) : $_REQUEST['order_time_end'];
    }elseif(strlen($_REQUEST['shipping_time_start'])>0 && strlen($_REQUEST['shipping_time_end'])>0){
        $shipping_time_start    =   strlen($_REQUEST['shipping_time_start'])>10 ? strtotime($_REQUEST['shipping_time_start']): $_REQUEST['shipping_time_start'];
        $shipping_time_end      =   strlen($_REQUEST['shipping_time_end'])>10 ?strtotime($_REQUEST['shipping_time_end']): $_REQUEST['shipping_time_end'];
    }else{
        $order_time_start = strtotime(date('Y-m-d 00:00:00',$_SERVER['REQUEST_TIME']));
        $order_time_end   = strtotime(date('Y-m-d 23:59:59',$_SERVER['REQUEST_TIME']));
    }

    $brand      = isset($_REQUEST['brand']) ? intval($_REQUEST['brand']) : 0;
    $goods_name = isset($_REQUEST['goods_name']) ? trim(mysql_real_escape_string($_REQUEST['goods_name'])) : '';
    $platform   = isset($_REQUEST['platform']) ? intval($_REQUEST['platform']) : 0;
    $goods_sn   = isset($_REQUEST['goods_sn']) ? mysql_real_escape_string($_REQUEST['goods_sn']) : 0;

    $condition = '&method=Ajax';
    $ex_where  = 1;

    if ($shipping_time_start && $shipping_time_end) {
        $ex_where .= " AND i.shipping_time BETWEEN $shipping_time_start AND $shipping_time_end  ";
        $condition .= "&shipping_time_start=$shipping_time_start&shipping_time_end=$shipping_time_end";
    } elseif ($order_time_start && $order_time_end) {
        $ex_where  .= " AND i.add_time BETWEEN $order_time_start AND $order_time_end ";
        $condition .= "&order_time_start=$order_time_start&order_time_end=$order_time_end";
    } else {
        $order_time_start = strtotime(date('Y-m-d 00:00:00'));
        $order_time_end   = strtotime(date('Y-m-d 23:59:59'));
        $ex_where .= " AND i.add_time BETWEEN $order_time_start AND $order_time_end ";
    }

    // 商品品牌
    if ($brand) {
        $ex_where  .= " AND g.brand_id=$brand";
        $condition .= "&brand=$brand";
    }

    // 商品名称
    if ($goods_name) {
        //$ex_where .= ' AND g.goods_name LIKE "%'.$goods_name.'%"';
        $condition .= "&goods_name=$goods_name";
    }

    if ($goods_sn){
        $ex_where .= " AND g.goods_sn='$goods_sn'";
        $condition .= "&goods_sn=$goods_sn";
    }

    // 销售平台
    if ($platform) {
        $ex_where   .=  " AND i.team=$platform ";
        $condition  .=  "&platform=$platform";
    }

    return array('ex_where'=>$ex_where,'condition'=>$condition);
}

/*条件内只退货，没有发货记录*/
function zero_shipping_but_return_goods($goods_list){
    if(!empty($_REQUEST['order_time_start']) && !empty($_REQUEST['order_time_end'])){
        if(strlen($_REQUEST['order_time_start'])>10 && strlen($_REQUEST['order_time_end'])>10){
            $return_time_start = strtotime($_REQUEST['order_time_start']);
            $return_time_end   = strtotime($_REQUEST['order_time_end']);
        }else{
            $return_time_start = $_REQUEST['order_time_start'];
            $return_time_end   = $_REQUEST['order_time_end'];
        }
    }elseif(!empty($_REQUEST['shipping_time_start']) && !empty($_REQUEST['shipping_time_end'])){
        if(strlen($_REQUEST['shipping_time_start']) >10){
            $return_time_start = strtotime($_REQUEST['shipping_time_start']);
            $return_time_end   = strtotime($_REQUEST['shipping_time_end']);
        }else{
            $return_time_start = $_REQUEST['shipping_time_start'];
            $return_time_end   = $_REQUEST['shipping_time_end'];
        }
    }else{
        $return_time_start = strtotime(date('Y-m-d 00:00:00',$_SERVER['REQUEST_TIME']));
        $return_time_end   = strtotime(date('Y-m-d 23:59:59',$_SERVER['REQUEST_TIME']));
    }

    /*发货商品编号（不包含只没有发货记录，但条件内有退货的商品）*/
    if($goods_list){
        $haved_shipping_but_just_return = array();
        foreach($goods_list as $val){
            $haved_shipping_but_just_return[] = $val['goods_sn'];
        }
        $str_goods_sn = implode("','",$haved_shipping_but_just_return);
    }

    if($str_goods_sn != ''){
        $where = " AND goods_sn NOT IN ('$str_goods_sn') ";
    }

    //退货的商品
    $sql_select = 'SELECT g.goods_sn,g.goods_name,LEFT(g.goods_name,13) goods_short_name,SUM(g.goods_number) goods_number FROM '
        .$GLOBALS['ecs']->table('order_goods').' g LEFT JOIN '
        .$GLOBALS['ecs']->table('returns_order').' r ON g.order_id=r.order_id'
        ." WHERE return_time>=$return_time_start AND return_time<=$return_time_end"
        ." $where AND g.goods_sn NOT LIKE '%\_%' GROUP BY g.goods_sn ORDER BY goods_number";

    $just_return_goods_list = $GLOBALS['db']->getAll($sql_select);

    $sql_select = 'SELECT g.goods_sn, g.goods_number FROM '
        .$GLOBALS['ecs']->table('order_goods')
        .' g LEFT JOIN '.$GLOBALS['ecs']->table('returns_order')
        ." r ON g.order_id=r.order_id "
        ." WHERE return_time>=$return_time_start AND return_time<=$return_time_end"
        .$where.' AND g.goods_sn LIKE "%\_%" ';

    $package_goods = $GLOBALS['db']->getAll($sql_select);

    if($package_goods){
        $package_sn = array();
        foreach ($package_goods as $v){
            $package_sn[] = $v['goods_sn'];
            $packing_goods[$v['goods_sn']] += $v['goods_number'];
        }

        $package_sn = implode("','", $package_sn);
        $sql_select = 'SELECT p.packing_desc,pg.goods_id,g.goods_sn,pg.num FROM '
            .$GLOBALS['ecs']->table('packing').' p LEFT JOIN '
            .$GLOBALS['ecs']->table('packing_goods')
            .' pg ON pg.packing_id=p.packing_id LEFT JOIN '.$GLOBALS['ecs']->table('goods')
            ." g ON g.goods_id=pg.goods_id WHERE p.packing_desc IN ('$package_sn')";

        $package_list = $GLOBALS['db']->getAll($sql_select);

        $package = array();
        foreach ($package_list as $val){
            $package[$val['goods_sn']] += $val['num'] * $packing_goods[$val['packing_desc']];
        }

        $package_goods_sn     = array_keys($package);
        $str_package_goods_sn = implode("','",$package_goods_sn);

        $sql_select = 'SELECT goods_sn,goods_name,LEFT(goods_name,13) goods_short_name FROM '.$GLOBALS['ecs']->table('goods').
            " WHERE goods_sn IN('$str_package_goods_sn')";
        $packing_goods_list = $GLOBALS['db']->getAll($sql_select);

        foreach($packing_goods_list as &$val){
            $val['return_number'] = $package[$val['goods_sn']];
            $val['goods_number']  = 0;
        }

        $goods_list = array_merge($just_return_goods_list,$packing_goods_list);
    }

    $current_stock = get_current_stock();
    if($goods_list && $current_stock){
        foreach($current_stock as $stock){
            foreach($goods_list as &$goods){
                if($stock['goods_sn'] == $goods['goods_sn']){
                    $goods['current_stock'] = $stock['quantity'] <= 800 ? "<font color=red>{$stock['quantity']}</font>" : $stock['quantity'];
                }
            }
        }
    }

    return $goods_list;
}


/*实时库存*/
function get_current_stock($where = ''){
    $sql_select = 'SELECT goods_sn,SUM(quantity) AS stock FROM '.$GLOBALS['ecs']->table('stock_goods').$where.' GROUP BY goods_sn';
    $current_stock = $GLOBALS['db']->getAll($sql_select);
    return $current_stock;
}

/*实体店列表*/
function get_warehouse($des,$where=''){
    if($whre){
        $where .= " AND status=1";
    }else{
        $where = " WHERE status=1";
    }

    if('detail' == $des){
        $sql_select = 'SELECT warehouse_id,warehouse_name,admin_id,tel,address FROM ';
    }elseif('simple' == $des){
        $sql_select = 'SELECT warehouse_id,warehouse_name FROM ';
    }

    $sql_select .= $GLOBALS['ecs']->table('warehouse').$where;
    $result = $GLOBALS['db']->getAll($sql_select);

    return $result;
}

/*仓库调拨记录*/
function get_allot_list(){
    $where = ' WHERE w.status=1';

    if(!empty($_REQUEST['add_time'])){
        if(strlen($_REQUEST['admin_time']) < 10){
            $add_time = strtotime(mysql_real_escape_string($_REQUEST['add_time']));
        }else{
            $add_time = intval($_REQUEST['add_time']);
        }
        $where .= " AND w.add_time=$add_time ";
    }

    if(!empty($_REQUEST['warehouse_id'])){
        $warehouse_id = intval($_REQUEST['warehouse_id']);
        $whre .= " AND w.warehouse_id=$warehouse_id";
    }

    $sql_select = 'SELECT w.allot_id,w.admin_id,w.add_time,w.out_storage,w.in_storage,w.check,w.examine,w.title,w.check_time,w.examine_time,a.user_name as admin_name FROM '.
        $GLOBALS['ecs']->table('warehouse_allot').' AS w LEFT JOIN '.
        $GLOBALS['ecs']->table('admin_user').' AS a ON w.admin_id=a.user_id '.$where;
    $result = $GLOBALS['db']->getAll($sql_select);

    return $result;
}

function get_pdc_day($sql_where=''){
    $goods_sn = isset($_REQUEST['goods_sn']) ? mysql_real_escape_string($_REQUEST['goods_sn']) : '';

    $where = ' WHERE is_delete=0 ';
    if($goods_sn){
        $where .= " AND goods_sn='$goods_sn' ";
    }

    $sql_select = 'SELECT rec_id,goods_sn,production_day,quantity FROM '.
        $GLOBALS['ecs']->table('stock_goods').$where.$sql_where.' ORDER BY quantity DESC';
    $result = $GLOBALS['db']->getAll($sql_select);

    if($result){
        foreach($result as &$val){
            $val['production_day'] = date('Y-m-d',$val['production_day']);
        }
    }

    return $result;
}

//保质期
function expire_time_list(&$goods_list){
    foreach($goods_list as $v){
        $goods_sn[] = $v['goods_sn'];
    }
    $goods_sn = implode(',',$goods_sn);
    $sql = 'SELECT goods_sn,MIN(expire_time) expire_time FROM '.$GLOBALS['ecs']->table('stock_goods')
        ." WHERE goods_sn IN ($goods_sn) AND quantity>0 GROUP BY goods_sn ORDER BY goods_sn AND expire_time>0";
    $res = $GLOBALS['db']->getAll($sql);
    foreach ($goods_list as &$v) {
        foreach ($res as $r) {
            if ($v['goods_sn'] == $r['goods_sn']) {
                if (empty($r['expire_time'])) {
                    $v['expire_time'] = '-';
                }else{
                    $v['expire_time'] = date('Y-m-d',$r['expire_time']);
                    if (date('Y',strtotime($v['expire_time']))-date('Y') == 0 && intval(date('m'))-intval(date('m',strtotime($v['expire_time'])))<=6) {
                        $v['expire_status'] = 1;
                    }else{
                        $v['expire_status'] = 0;
                    }
                }
            }
        }
    }
}
