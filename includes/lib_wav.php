<?php
date_default_timezone_set('Asia/Shanghai');
// 获取通话信息
function getWavInfo() {
    $url = 'http://192.168.1.240/wavTime/wavTime.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('date'=>date('Y-m-d')));

    $result = json_decode(curl_exec($ch),true);
    curl_close($ch);

    return $result;
}

// 保存通话信息
function saveWavInfo() {
    $sql = 'SELECT user_id,ext FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE ext>0';
    $extList = $GLOBALS['db']->getAll($sql);
    $extLink = array();
    foreach ($extList as $val) {
        $extLink[$val['ext']] = $val['user_id'];
    }
    
    $wavTimes = getWavInfo();
    $fields = array();
    $values = array();
    if (empty($wavTimes)) {
       return false; 
    }
    foreach ($wavTimes as $val) {
        if (empty($extLink[$val['ext']])) {
            continue;
        }
        $vals = $val;
        $vals['update_time'] = strtotime(date('Y-m-d'));
        $vals['admin_id']    = $extLink[$val['ext']];
        empty($fields) && $fields = array_keys($vals);
        $vals = array_values($vals);
        $values[] = implode("','", $vals);
    }
    $keys = array();
    foreach ($fields as $val) {
        $keys[] = "$val=VALUES($val)";
    }
    
    $fieldStr = implode(',', $fields);
    $keyStr   = implode(',', $keys);
    $valueStr = implode("'),('", $values);

    if ($fieldStr && $keyStr) {
        $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('wavtime').
            "($fieldStr)VALUES('$valueStr') ON DUPLICATE KEY UPDATE $keyStr";
        $GLOBALS['db']->query($sql);
    }
}

// 获取个人通话信息
function getSingleWavInfo($single = true) {
    saveWavInfo();
    $dateTime = strtotime(date('Y-m-d'));
    $sql_select = 'SELECT u.user_id,u.user_name,w.number_info,w.time_info FROM '.$GLOBALS['ecs']->table('wavtime').
        ' w, '.$GLOBALS['ecs']->table('admin_user')." u WHERE w.admin_id=u.user_id AND w.update_time=$dateTime";
    if ($single) {
        $sql_select .= " AND u.user_id={$_SESSION['admin_id']}";
        $result = $GLOBALS['db']->getRow($sql_select);
        if (empty($result)) {
            return false;
        }
        $result['time_info'] = round($result['time_info']/60);
    } else {
        $info = $GLOBALS['db']->getAll($sql_select);
        $result = array();
        foreach ($info as &$val) {
            $val['time_info'] = round($val['time_info']/60);
            $result[$val['user_name']] = $val;
        }
    }
    return $result;
}
