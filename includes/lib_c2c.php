<?php
/**
 * ECSHOP 管理中心公用函数库
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: lib_main.php 17217 2011-01-19 06:29:08Z liubo $
 */
if (!defined('IN_ECS')) {
    die('Hacking attempt');
}


/**
 * 查询顾客是否已经存在
 * @param   string          $value 要查询的值
 * @param   string          $field 要查询的字段 QQ或阿里旺旺
 * @param   string          $mobile 手机号码 
 * @param   string          $tel    固话号码 
 * return   array|boolean   如果存在，则返回数组，不存在返回false
 */
function userIsExist ($user_info, $table_name = 'users')
{
    if (!empty($user_info['qq']) && is_numeric($user_info['qq'])) {
        $where[] = " qq='{$user_info['qq']}' ";
    }

    if (!empty($user_info['aliww'])) {
        $where[] = " aliww='{$user_info['aliww']}' ";
    }

    if (!empty($user_info['mobile']) && is_numeric($user_info['mobile'])) {
        $where[] = " mobile_phone='{$user_info['mobile']}' ";
    }

    if (!empty($user_info['tel']) && preg_match('/^(0\d{2,3}-|0\d{2,3})?\d{6,8}$/', $user_info['tel'])) {
        $where[] = " home_phone='{$user_info['tel']}' ";
    }

    if (empty($where)) {
        return 1;
    }
    $sql = 'SELECT user_id,admin_id,role_id,group_id FROM '.$GLOBALS['ecs']->table('users').' WHERE ';
    $sql .= implode(' OR ', $where);
    return $GLOBALS['db']->getRow($sql);
}

/**
 * checkUserIsExist
 * @return int user_id
 * @author Nixus
 **/
function checkUserIsExist($buyer_nick, $platform, $order_sn) {
    $buyer_nick = mysql_real_escape_string($buyer_nick);
    $sql_select = 'SELECT role_code FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id=$platform";
    $platform = $GLOBALS['db']->getOne($sql_select);
    if ($platform) {
        $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('user_contact').
            " WHERE contact_name='$platform' AND contact_value='$buyer_nick'";
        $user_id = $GLOBALS['db']->getOne($sql_select);
        if ($user_id === '') {
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_contact').
                '(order_sn,contact_name,contact_value,add_time)VALUES('.
                "'$order_sn','$platform','$buyer_nick',UNIX_TIMESTAMP())";
            $GLOBALS['db']->query($sql_insert);
        }
    }
    if ($user_id) {
        $sql_select = 'SELECT user_id,group_id,role_id,admin_id FROM '.
            $GLOBALS['ecs']->table('users')." WHERE user_id=$user_id";
        $user = $GLOBALS['db']->getRow($sql_select);
        $user['sync'] = false;
    }
    return $user;
}

/**
 * 分割地址信息为数组
 * @param     string  $addr  地址
 * return     array          地址数组
 */
function splitAddr ($addr)
{
    $temp = explode(' ', $addr);
    if (count($temp) > 2)
    {
        $region['state'] = array_shift($temp);
        $region['city'] = array_shift($temp);
        $region['district'] = array_shift($temp);
        $region['a'] = implode('', $temp);
    }
    elseif ( count($temp) < 3)
    {
        $region['state'] = array_shift($temp);
        $region['city'] = array_shift($temp);
        $region['a'] = implode('', $temp);
    }

    return $region;
}

/**
 * 取得相应省市区的region_id，若无，则新建省/市/区
 * return   int   region_id
 */
function getProCitDis($state, $city, $district)
{
    if (empty($state) || empty($city)) return false;
    // 查询省级ID
    $region = array();
    $state = mb_substr($state, 0, 2, 'utf-8').'%';
    $sql = 'SELECT region_id FROM '.$GLOBALS['ecs']->table('region').
        " WHERE region_name LIKE '$state'";
    $region['state'] = $GLOBALS['db']->getOne($sql);

    if (empty($region['state'])) return false;

    $city = mb_substr($city, 0, 2, 'utf-8').'%';
    $sql = 'SELECT region_id FROM '.$GLOBALS['ecs']->table('region').
        " WHERE region_name LIKE '$city' AND parent_id={$region['state']}";
    $region['city'] = $GLOBALS['db']->getOne($sql);

    if (empty($region['city'])) return false;
    if (empty($district)) return $region;

    $district = mb_substr($district, 0, 2, 'utf-8').'%';
    $sql = 'SELECT region_id FROM '.$GLOBALS['ecs']->table('region').
        " WHERE region_name LIKE '$district' AND parent_id={$region['city']}";
    $region['district'] = $GLOBALS['db']->getOne($sql);

    return $region;
}

/**
 * 新建省市区
 * @param   string    $proCitDis    省/市/区名
 * @param   int       $region_type  region类型   
 * return   int       新建省/市/区的region_id
 */
function createProCitDis ($proCitDis, $parent_id = 1, $region_type = 1)
{
    $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('region')
        .'(region_name, parent_id, region_type)VALUES('."'$proCitDis', $parent_id, $region_type)";
    $GLOBALS['db']->query($sql);
    return $GLOBALS['db']->insert_id();
}

/**
 * 生成订单SQL
 * @param     array     $user_info  顾客信息
 * @param     array     $order_info 订单信息
 * @param     array     $goods_info 商品信息
 * return     int       订单id
 */
function createOrderSql ($user_info, $order_info, $goods_info)
{
    $order  = array_merge($user_info, $order_info);
    $fields = implode(',', array_keys($order));
    $values = implode("','", array_values($order));

    $sql[] = 'INSERT INTO '.$GLOBALS['ecs']->table('ordersyn_info')."($fields) SELECT '$values' FROM DUAL WHERE NOT EXISTS (SELECT * FROM ".
        $GLOBALS['ecs']->table('ordersyn_info')." WHERE order_sn='{$order_info['order_sn']}')";
    //file_put_contents('dangdang.txt', $xxxxx.PHP_EOL, FILE_APPEND);
    //$sql[] = 'INSERT INTO '.$GLOBALS['ecs']->table('ordersyn_info')."($fields)VALUES('$values')";
    $temp_sn = '';
    foreach ($goods_info as $val)
    {
        extract($val);
        /* 如果是套餐 */
        if (!is_numeric($outer_iid))
        {
            // 获取套餐的本地名称
            $sql_select = 'SELECT packing_name FROM '.$GLOBALS['ecs']->table('packing').' p, '.
                $GLOBALS['ecs']->table('packing_goods')." pg WHERE p.packing_id=pg.packing_id AND p.packing_desc='$outer_iid'";
            $packing_name = $GLOBALS['db']->getOne($sql_select);

            $sql[] = 'INSERT INTO '.$GLOBALS['ecs']->table('ordersyn_goods').
                '(goods_sn,goods_name,goods_price,goods_number,order_sn,is_package)'.
                "VALUES('$outer_iid','$packing_name',$price,$num,'{$order['order_sn']}', 1)";
        }
        elseif ((strpos($title, 'TB_') !== false || strpos(trim($title), 'TC_') !== false) && $goods_sn = strstr(trim($title), '-', true))
        {
            $goods_sn = str_replace('【1111购物狂欢节】', '', $goods_sn);

            // 获取套餐的本地名称
            $sql_select = 'SELECT packing_name FROM '.$GLOBALS['ecs']->table('packing').' p, '.
                $GLOBALS['ecs']->table('packing_goods')." pg WHERE p.packing_id=pg.packing_id AND p.packing_desc='$goods_sn'";
            $packing_name = $GLOBALS['db']->getOne($sql_select);

            $sql[] = 'INSERT INTO '.$GLOBALS['ecs']->table('ordersyn_goods').
                '(goods_sn,goods_name,goods_price,goods_number,order_sn,is_package)'.
                "VALUES('$goods_sn','$packing_name',$price,$num,'{$order['order_sn']}',1)";

            // 根据购买的套餐查询赠品及数量
            $gift_info_package[$goods_sn] = read_gift_info($order_info['team'], $order_info['confirm_time'], '', $goods_sn, $num);
            if (!implode('', $gift_info_package) && confirm_inventor_enough($gift_info_package[$goods_sn]))
            {
                unset($gift_info_package[$goods_sn]);
                $gift_info_package[$goods_sn] = read_gift_info ($order_info['team'], $order_info['confirm_time'], '', $goods_sn, $num, $gift_info_package['gift_id']);
            }
        }

        /* 如果是单品 */
        else 
        {
            if ($temp_sn == $order['order_sn']) continue;

            if (isset($total_fee))
            {
                $price = $total_fee/$num;
                unset($total_fee);
            }

            $sql[] = 'INSERT INTO '.$GLOBALS['ecs']->table('ordersyn_goods').
                '(goods_sn,goods_price,goods_number,order_sn)VALUES('.
                "'$outer_iid', $price, $num, '{$order['order_sn']}')";

            // 根据购买的商品查询赠品及数量
            $gift_info_item[$outer_iid] = read_gift_info ($order_info['team'], $order_info['confirm_time'], '', $outer_iid, $num);
            if (!implode('', $gift_info_item) && confirm_inventor_enough($gift_info_item[$outer_iid]))
            {
                unset($gift_info_item[$outer_iid]);
                $gift_info_item[$outer_iid] = read_gift_info ($order_info['team'], $order_info['confirm_time'], '', $outer_iid, $num, $gift_info_item['gift_id']);
            }
        }
    }

    // 如果没有活动赠品，则查询订单金额赠品
    count($gift_info_package) && $gift_info = $gift_info_package;
    count($gift_info_item) && $gift_info = $gift_info_item;

    $rres = implode('', $gift_info);
    if (empty($rres))
    {
        // 根据订单金额查询赠品
        $gift_info_total = read_gift_info($order_info['team'], $order_info['confirm_time'], $order_info['goods_amount'], '', '');

        // 判断赠品库存是否充足
        if ($gift_info_total && confirm_inventor_enough($gift_info_total))
        {
            unset($gift_info_total);
            $gift_info_total = read_gift_info ($order_info['team'], $order_info['confirm_time'], $order_info['goods_amount'], '', '', $gift_info_total['gift_id']);
        }
    }

    $gift_info[] = $gift_info_total;
    if (count($gift_info))
    {
        $sql[] = createGiftSql($gift_info, $order_info['order_sn']);
    }

    // 添加赠品到此完成，剩下的就是修改订单状态，确定订单归属
    // 添加赠品处需要增加选择套餐
    return $sql;
}

/**
 * 提交订单
 * @param     array     $sql 订单提交语句
 * return     boolean   订单提交结果
 */
function submitSql ($sql)
{
    if (!is_array($sql))
    {
        return false;
    }

    $sql = array_filter($sql);

    $GLOBALS['db']->query('BEGIN');
    foreach ($sql as $val)
    {
        if (empty($val))
        {
            continue;
        }
        if (!$GLOBALS['db']->query($val))
        {
            $GLOBALS['db']->query('ROLLBACK');
            return false;
        }
    }

    /* 更新订单商品表中的订单id */
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_info').' i, '
        .$GLOBALS['ecs']->table('ordersyn_goods').' g SET '.
        'g.order_id=i.order_id WHERE g.order_sn=i.order_sn';
    $GLOBALS['db']->query($sql);

    /* 更新订单商品表中的商品信息 */
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('ordersyn_goods').' o, '.
        $GLOBALS['ecs']->table('goods').' g SET '.
        "o.goods_id=g.goods_id, o.goods_name=g.goods_name, o.is_real=g.is_real WHERE o.goods_sn=g.goods_sn";
    $GLOBALS['db']->query($sql);

    return $GLOBALS['db']->query('COMMIT');
}


/**
 * 分配订单
 */
function orderAssign ()
{
    $sql = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE assign=1";
    $res = $GLOBALS['db']->getAll($sql);
    foreach ($res as $val)
    {
        $user_id[] = $val['user_id'];
    }

    $sql = 'SELECT operator FROM '.$GLOBALS['ecs']->table('ordersyn_info').
        ' WHERE order_status=0 AND pay_status=0 AND shipping_status=0 '.
        ' AND operator IN ('.implode(',', $user_id).') GROUP BY operator ORDER BY COUNT(operator) ASC';
    $res = $GLOBALS['db']->getOne($sql);

    if (empty($res))
    {
        return $user_id[array_rand($user_id)];
    }

    return $res;
}

/**
 * 延长客服在线时间
 */
function extendOnlineTime ()
{
    date_default_timezone_set('Asia/Shanghai');
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('admin_user').
        ' SET expiry=UNIX_TIMESTAMP()+600 ';
    if (date('w') == 0 || date('H') >= 18 || in_array(date('H'), array(12, 13)))
    {
        /* 如果是周日 或 时间是晚上18点以后*/
        $sql .= " WHERE role_id IN (6,7) AND assign=1";
    }
    else
    {
        /* 如果不是周日 */
        $sql .= " WHERE user_id={$_SESSION['admin_id']} AND assign=1";
    }

    $GLOBALS['db']->query($sql);
}

/**
 * 读取符合条件的赠品数据
 * @param   $order_amount   float      订单金额
 * @param   $goods_num      int        购买商品的数量
 * @param   $team           int        适用平台
 * @param   $now_time       timestamp  赠送的时间条件， 一般为当前时间
 * @param   $goods_sn       int/string 商品编码/套餐
 * @param   $drop_gift_id   int        库存不足的赠品记录，若有则启用备用的赠品
 */
function read_gift_info ($team, $now_time, $order_amount, $goods_sn = '', $goods_num = '', $drop_gift_id = '')
{
    $where    = array('level' => 'level=0', 'status=1'); // 赠品条件
    $order_by = '';    // 赠品条件

    if ($team)
    {
        $where[] = "platform='$team'";
    }

    if ($goods_sn)
    {
        $where[] = "goods_sn='$goods_sn'";

        if ($goods_num)
        {
            $where[] = "goods_num<='$goods_num'";
            $order_by = ' ORDER BY goods_num DESC';
        }

    }
    elseif ($order_amount)
    {
        $where[]  = "order_amount<=$order_amount AND order_amount<>0";
        $order_by = ' ORDER BY order_amount DESC';
    }
    else
    {
        return '';
    }


    if (!$now_time)
    {
        $now_time = time() +28800;
    }

    $where[] = "start_time<$now_time AND end_time>$now_time";

    if ($drop_gift_id)
    {
        $where[] = " gift_id<>$drop_gift_id";
        $where['level'] = 'level=1';
    }

    $sql = 'SELECT gift_info, goods_num, order_amount FROM '.
        $GLOBALS['ecs']->table('goods_gift').' WHERE '.implode(' AND ', $where).$order_by;
    $res = $GLOBALS['db']->getRow($sql);

    if ($goods_sn && $res['goods_num'] > $goods_num)
    {
        return '';
    }

    if ($order_amount && $res['order_amount'] > $order_amount)
    {
        return '';
    }

    if ($res['gift_info'])
        return $res['gift_info'];
    else
        return '';
}


/**
 * 确认库存中的赠品数量是否满足该订单的需求
 * @param   $gift   Array   赠品信息
 */
function confirm_inventor_enough ($gift)
{
    $gift_info = json_decode($gift, true);

    if (!is_array($gift_info))
    {
        return true;
    }

    foreach ($gift_info as $val)
    {
        // 获取该赠品的库存数量
        $sql = 'SELECT SUM(quantity) FROM '.
            $GLOBALS['ecs']->table('stock_goods').
            ' s, '.$GLOBALS['ecs']->table('goods').
            " g WHERE g.goods_id={$val['goods_id']} AND g.goods_sn=s.goods_sn";
        $goods_stock = $GLOBALS['db']->getOne($sql);

        // 该赠品库存不足
        if ($val['goods_num'] > $goods_stock)
        {
            return true;
        }
    }

    return $gift_info;
}

/**
 * 组装赠品SQL
 */
function createGiftSql ($gift_info, $order_sn)
{
    foreach ($gift_info as $val)
    {
        $temp = json_decode($val, true);
        if (empty($temp))
        {
            continue;
        }

        foreach ($temp as $val)
        {
            $name_sql = 'SELECT goods_name, goods_sn FROM '.$GLOBALS['ecs']->table('goods').
                " WHERE goods_id='{$val['goods_id']}'";
            $goods_info = $GLOBALS['db']->getRow($name_sql);
            $values[] = "('{$val['goods_id']}', '{$val['goods_num']}', '{$goods_info['goods_name']}', '{$goods_info['goods_sn']}', '$order_sn')";
        }
    }

    count($values) && $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('ordersyn_goods').
        '(goods_id, goods_number, goods_name, goods_sn, order_sn)VALUES'.implode(',', $values);

    return $sql;
}

/**
 * 获取待确认的订单 
 */
function get_order_owner ($role_id)
{
    $now_time = time();
    $today_time = strtotime('2012-12-21 08:00:00');
    $sql = 'SELECT order_sn FROM '.$GLOBALS['ecs']->table('ordersyn_info').
        " WHERE order_status=0 AND pay_status=0 AND team=$role_id AND (syn_time+3600)<$now_time AND syn_time>$today_time AND pay_id<>3";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 根据地址信息查询淘宝的地址ID数据
 * @param   $region array  地址信息     
 * @param   $id     int    地址ID
 * return   最终地址ID
 */
function get_shipping ($region, $id = 0)
{
    $tmp = next($region);
    if (!$tmp)
    {
        return search_shipping($id);
    }

    $sql = 'SELECT id FROM '.$GLOBALS['ecs']->table('area_taobao').' WHERE name="'.$tmp.'"';
    if ($id) $sql .= " AND parent_id=$id";
    $res = $GLOBALS['db']->getOne($sql);

    if ($res)
    {
        return get_shipping($region, $res);
    }
}

/**
 * 确认配送方式
 * @param $target_id  int   淘宝目的地ID
 * return 根据配送优先级返回可用的一种配送方式
 */
function search_shipping ($target_id, $check = '') {
    if (!class_exists('TopClient')) {
        return false;
    }
    $c            = new TopClient;
    $c->appkey    = '21219338';
    $c->secretKey = '225e440bb09d11f4bb1e76484c9c92c2';
    $c->format    = 'json';

    $req = new LogisticsPartnersGetRequest;
    $req->setServiceType('online');
    $req->setSourceId('440111');
    $req->setTargetId($target_id);

    $resp = $c->execute($req);

    // 未获取到有效的快递信息
    if (!is_array($resp)) {
        $resp = json_decode($resp, true);
    }
    if (empty($resp['logistics_partners'])) return false;
    $shipping = $resp['logistics_partners']['logistics_partner'];

    foreach ($shipping as $val) {
        if ($check) {
            $ship[] = $val;
        } else {
            $ship[] = $val['partner']['company_code'];
        }
    }

    if ($check) {
        return $ship;
    } else {
        $search_ship = '("'.implode('","', $ship).'")';
        $sql = 'SELECT shipping_id id, shipping_name name, shipping_code code FROM '.
            $GLOBALS['ecs']->table('shipping').
            ' WHERE priority<>0 AND company_code IN '.
            " $search_ship ORDER BY priority ASC";
        $res = $GLOBALS['db']->getRow($sql);
    }

    return $res;
}

/**
 * 获取套餐商品
 */
function get_packing_syn_goods ($package_sn)
{
    file_put_contents('package_sn.txt', $package_sn."\r\n", FILE_APPEND);
    // 根据套餐编码 获取套餐id
    $sql_select = 'SELECT g.goods_sn outer_iid,pg.goods_name title,pg.num num FROM '.
        $GLOBALS['ecs']->table('packing_goods').' pg, '.$GLOBALS['ecs']->table('packing').' p,'.
        $GLOBALS['ecs']->table('goods')." g WHERE g.goods_id=pg.goods_id AND pg.packing_id=p.packing_id AND p.packing_desc='$package_sn'";
    $goods_list = $GLOBALS['db']->getAll($sql_select);

    foreach ($goods_list as &$val)
    {
        $val['price']        = 0.1;
        $val['is_package']   = 1;
        $val['goods_sn'] = $packing_sn;
    }

    return $goods_list;
}

/**
 * xml转数组
 */
function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();

    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }

    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }

            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }

    return $arrData;
}

/**
 * @post 实现方法如下函数 curl_post：
 * @param $url $file(为上传的附件全路径)
 * @return string
 */
//例如：$url ='http://api.dangdang.com/sendGoods.php?gShopID=489';
function curl_post($url ,$file)
{
    $url  = get_md5_string($url);
    $urls = explode('?',$url);
    $url  = $urls[0];

    $params    = $urls[1];
    $params    = explode('&',$params);
    $file_name = substr($url ,(strrpos($url ,'/')+1),(strpos($urls,'\.php')-4));
    $data      = array();
    foreach($params as $value){
        $tmp =explode('=',$value);
        $data[$tmp[0]] =$tmp[1];
    }
    $data[$file_name] ='@'.realpath($file).';type=text/xml';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/*@get 实现方法如下函数 curl_get： 
 *@ param $url
 *@ return string
 */
//例如：$url ='http://api.dangdang.com/searchOrders.php?gShopID=489&os=300&sm=1'
function curl_get($url)
{
    $url =get_md5_string($url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function get_md5_string($url)
{ 
    $arr = explode('?',$url);
    $params = $arr[1];
    $arr = explode('&',$params);
    foreach($arr as $v){
        $tmp = explode('=',$v);
        $request_arr[$tmp[0]] = $tmp[1];
    }
    ksort($request_arr);
    $validateString_new ='';
    foreach($request_arr as $k=>$v){
        $validateString_new .= mb_convert_encoding(trim($v),'gbk','gbk');
    } 
    $api_key = '0abf13b8428d9289d164'; //为商家后台所设置的 key 值
    $validateString_new .=  mb_convert_encoding($api_key,'gbk','gbk');
    $validateString = md5($validateString_new);
    $url .='&validateString='.$validateString;
    return $url;
}

/**
 * 获取第三方平台sessionkey
 */
function curl($url, $postFields = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (is_array($postFields) && 0 < count($postFields))
    {
        $postBodyString = "";
        foreach ($postFields as $k => $v)
        {
            $postBodyString .= "$k=" . urlencode($v) . "&"; 
        }
        unset($k, $v);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
    }

    $reponse = curl_exec($ch);
    var_dump($reponse);
    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch),0);
    } else {
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode){
            throw new Exception($reponse,$httpStatusCode);
        }
    }

    curl_close($ch);
    return $reponse;
}

/**
 * 验证订单是否为刷单
 */
function is_brush_order($order_sn)
{
    $sql_select = 'SELECT b.source_order_id,b.brush_order_sn order_sn,b.brush_platform team,i.tracking_sn,i.shipping_id FROM '.
        $GLOBALS['ecs']->table('brush_order').' b LEFT JOIN '.$GLOBALS['ecs']->table('order_info').
        " i ON i.order_id=b.source_order_id WHERE b.brush_order_sn='$order_sn'";
    $brush_order = $GLOBALS['db']->getRow($sql_select);

    if (!empty($brush_order)) {
        $func = shipping_sync_.$brush_order['team'];
        $func($brush_order);

        return true;
    }

    return false;
}

/**
 * 刷单标记发货：天猫
 */
function shipping_sync_6($order_info)
{
    $platform_path = array (6 => 'taobao', 21 => 'taobao01', 22 => 'taobao02');
    require('../admin/taobao/order_synchro.php');
    require("../admin/{$platform_path[$order_info['team']]}/sk.php");
    $auth = require("../admin/{$platform_path[$order_info['team']]}/config.php");

    $logistics = logistics_code($order_info['shipping_id']);

    // 配置淘宝签权参数
    $c = new TopClient;
    $c->appkey    = $auth['appkey'];
    $c->secretKey = $auth['secretKey'];

    // 查询订单当前状态是否符合发货条件
    $req = new TradeFullinfoGetRequest;
    $req->setFields("status");
    $req->setTid($order_info['order_sn']);
    $shipping_able = $c->execute($req, $sk['access_token']);
    $shipping_able = $json->decode($json->encode($shipping_able), true);

    // 订单状态符合发货条件
    if ($shipping_able['trade']['status'] == 'WAIT_SELLER_SEND_GOODS')
    {
        // 构建标记发货的数据格式
        $req = new LogisticsOfflineSendRequest;

        if ($logistics['company_code'] == 'zjs') {
            $req->setOutSid($tracking_sn);
            $req->setTid($order_info['order_sn']);
            $req->setCompanyCode(strtoupper($logistics['company_code']));
        } else {
            $req->setOutSid($tracking_sn);
            $req->setTid($order_info['order_sn']);
            $req->setCompanyCode(strtoupper($logistics['company_code']));
        }

        // 发送发货请求
        $shipping_resp = $c->execute($req, $sk['access_token']);
    }

    // 订单符合修改运单号条件
    elseif ($shipping_able['trade']['status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
        $req = new LogisticsConsignResendRequest;
        $req->setOutSid($tracking_sn);
        $req->setTid(number_format($order_info['order_sn'], 0, '', ''));
        $req->setCompanyCode(strtoupper($logistics['company_code']));

        $shipping_resp = $c->execute($req, $sk['access_token']);
    } elseif ($shipping_able['code'] == 27) {
        $res['message'] = '淘宝授权到期，请联系天猫推广，进行授权后再发货！';
        file_put_contents('taobao.txt', 27);
    } else {
        $res['message'] = '订单状态已改变，不符合发货条件！【天猫商城提示您】';
        $res['shipping_name'] = $order_info['shipping_name'];
        $res['errMsg'] = 1;
    }

    if (!isset($shipping_resp['shipping']['is_success']) || !$shipping_resp['shipping']['is_success']) {
        $res['message'] = $shipping_resp['sub_msg'];
    }
}

/**
 * 刷单标记发货：京东
 */
function shipping_sync_10($order_info)
{
    include('../admin/jingdong/JdClient.php');
    include('../admin/jingdong/JdException.php');
    include('../admin/jingdong/request/order/OrderSopOutstorageRequest.php');

    include('../admin/jingdong/sk.php');
    $auth = include('../admin/jingdong/config.php');

    $req = new OrderSopOutstorageRequest;
    $req->setOrderId($order_info['order_sn']);
    $req->setWaybill($tracking_sn);

    $sql_select = 'SELECT jd_code FROM '.$GLOBALS['ecs']->table('shipping').
        " WHERE shipping_id={$order_info['shipping_id']}";
    $req->setLogisticsId($GLOBALS['db']->getOne($sql_select));

    $jd = new JdClient;

    $jd->appKey      = $auth['appkey'];     // 京东AppKey
    $jd->appSecret   = $auth['secretKey'];  // 京东AppSecret
    $jd->accessToken = $sk['access_token']; // 京东sessionkey(access_token)
    $jd->timestamp   = date('Y-m-d H:i:s');
    $jd->v = '2.0';

    $resp = $jd->execute($req);
    $resp = json_decode(json_encode($resp), true);

    if ($resp['error_response']['code']) {
        $res['message'] = $resp['error_response']['zh_desc'].'【京东商城提示您】';
        $res['tracking_sn'] = $order_info['tracking_sn'];
    }
}

/**
 * 刷单标记发货：一号店
 */
function shipping_sync_14($order_info)
{
    include('../admin/yhd/YhdClient.php');
    include('../admin/yhd/sk.php');
    include('../admin/yhd/request/order/OrderLogisticsPushRequest.php');
    include('../admin/yhd/request/logistics/LogisticsOrderShipmentsUpdateRequest.php');

    $auth = include('../admin/yhd/config.php');

    $req = new LogisticsOrderShipmentsUpdateRequest;

    // 应用级参数
    $req->setOrderCode($order_info['order_sn']);
    $req->setExpressNbr($tracking_sn);

    $sql_select = 'SELECT 1mall_code FROM '.$GLOBALS['ecs']->table('shipping').
        " WHERE shipping_id={$order_info['shipping_id']}";
    $req->setDeliverySupplierId($GLOBALS['db']->getOne($sql_select));

    $yhdClient = new YhdClient();

    // 系统级参数
    $yhdClient->appkey    = $auth['appkey'];
    $yhdClient->secretKey = $auth['secretKey'];
    $yhdClient->format    = 'json';

    $result = $yhdClient->execute($req, $sk['accessToken']);
    $result = objectsIntoArray($result);

    if ($result['response']['errInfoList']) {
        $req = new OrderDetailGetRequest;

        $req->setOrderCode($order_info['order_sn']);

        $yhdClient = new YhdClient();

        // 系统级参数
        $yhdClient->appkey    = $auth['appkey'];
        $yhdClient->secretKey = $auth['secretKey'];
        $yhdClient->format    = 'json';

        $express = $yhdClient->execute($req, $sk['accessToken']);
        $express = json_decode($express, true);

        if (isset($express['response']['orderInfo']['orderDetail']['merchantExpressNbr']) && $express['response']['orderInfo']['orderDetail']['merchantExpressNbr'] == $tracking_sn) {
        } elseif (isset($express['response']['orderInfo']['orderDetail']['merchantExpressNbr']) && $express['response']['orderInfo']['orderDetail']['merchantExpressNbr'] != $tracking_sn) {
            $res['message'] = '该订单已经在一号店标记发货，所使用的运单号为【'.
                $express['response']['orderInfo']['orderDetail']['merchantExpressNbr'].'】';
        } else {
            $res['message']     = $result['response']['errInfoList']['errDetailInfo'][0]['errorDes'];
            $res['tracking_sn'] = $order_info['tracking_sn'];
        }
    }
}

/**
 * 刷单标记发货：当当
 */
function shipping_sync_16($order_info)
{
    require_once('../admin/dangdang/ddClient.php');
    $dd = new ddClient(2100001198);

    // 获取商品列表
    $sql_select = 'SELECT goods_sn,goods_number FROM '.$GLOBALS['ecs']->table('order_goods')." WHERE order_id={$order_info['b']}";
    $goods_list = $GLOBALS['db']->getAll($sql_select);
    unset($val);
    foreach ($goods_list as &$val){
        $params['oit'] = $val['goods_sn'];
        $resp = $dd->execute('POST', $params, 'dangdang.item.itemid.get');
        $val['goods_sn'] = $resp['Result']['itemID'];
    }
    unset($val);

    $order_info['shipping_tel']  = 1234567;
    $order_info['shipping_name'] = mb_strcut($order_info['shipping_name'], 0, 6).'快递';
    $order_info['shipping_name'] = mb_convert_encoding($order_info['shipping_name'], 'GBK', 'UTF-8');
    $order_info['tracking_sn']   = trim($order_info['tracking_sn']);

    global $smarty;
    $smarty->assign('time',       date('Y-m-d H:i:s'));
    $smarty->assign('method',     'dangdang.order.goods.send');
    $smarty->assign('order_info', $order_info);
    $smarty->assign('goods_list', $goods_list);
    $send_goods = $smarty->fetch('dangdang_XML.htm');
    if (file_exists('ddXML.xml')) {
        unlink('ddXML.xml');
    }

    $bytes = file_put_contents('ddXML.xml', $send_goods);
    $params['sendGoods'] = '/var/www/html/crm2/admin/ddXML.xml';

    //$dd = new ddClient(2100001198);
    $resp = $dd->execute('POSTXML', $params, 'dangdang.order.goods.send');

    if (isset($resp['Result']['OrdersList']['OrderInfo']['orderOperCode'])) {
        $res['message'] = '当当网提示您：订单'.$resp['Result']['OrdersList']['OrderInfo']['orderID'].
            '，'.$resp['Result']['OrdersList']['OrderInfo']['orderOperation'];
    } else {
        $res = true;
    }
}

// 获取快递公司编码
function logistics_code($shipping_id)
{
    $sql = 'SELECT company_code, company_name FROM '.$GLOBALS['ecs']->table('shipping').
        " WHERE shipping_id='$shipping_id'";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 是否是刷单
 */
function isFlusher($account, $order_sn, $platform) {
    $sql_select = 'SELECT flusher_id FROM '.$GLOBALS['ecs']->table('flusher').
        " WHERE flush_account='$account' AND flush_platform='$platform'";
    $flusher_id = $GLOBALS['db']->getOne($sql_select);
    if ($flusher_id) {
        $sql_insert = "INSERT INTO ".$GLOBALS['ecs']->table('flush_order').
            '(flusher_id,order_sn,platform_code)VALUES('."$flusher_id,'$order_sn','$platform')";
        $GLOBALS['db']->query($sql_insert);
        return 1;
    }
}

/**
 * 去别名
 */
function filterAlias ($goodsSn) {
    return strpos($goodsSn, '@') > 0 ? strstr($goodsSn, '@', true) : $goodsSn;
}

