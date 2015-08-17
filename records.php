<?php
/*=============================================================================
#     FileName: records.php
#         Desc: 整理录音文件
#       Author: Wuyuanhang
#        Email: 1828343841@qq.com
#     HomePage: kjrs_crm
#      Version: 0.0.1
#   LastChange: 2015-05-09 09:01:52
#      History:
=============================================================================*/
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

$dir = '/var/www/html/records';
//$dir = "./records";
$file_list = scandir($dir);
array_shift($file_list);
array_shift($file_list);

//遍历录音目录移动文件
//查询user_id
$start_time = strtotime(date('2014-04-01'));
$end_time = strtotime(date('2014-06-01'));
$sql = 'SELECT s.user_id,u.mobile_phone,u.home_phone,c.contact_value,c.contact_name FROM '.$GLOBALS['ecs']->table('service')
    .' AS s LEFT JOIN '.$GLOBALS['ecs']->table('users').' u ON s.user_id=u.user_id LEFT JOIN '
    .$GLOBALS['ecs']->table('user_contact').' AS c ON s.user_id=c.user_id'
    ." WHERE s.service_time BETWEEN $start_time AND $end_time AND c.contact_name IN('tell','mobile') GROUP BY s.user_id";
$user_list = $GLOBALS['db']->getAll($sql);

$count = 0;
foreach ($file_list as &$v) {
    if (!is_dir("$dir/$v")) {
        $phone = explode('-',$v);
        $phone = substr($phone[1],1);
        $date = explode('-',$v);
        $date = $date[2];

        if ($user_list) {
            foreach ($user_list as $u) {
                if ($u['mobile_phone'] == $phone || $u['home_phone'] == $phone || $u['contact_value'] == $phone) {
                   $user_id = $u['user_id']; 
                   break;
                }
            }
        }

        ////查询user_id
        //$sql = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('users')
        //    ." WHERE mobile_phone=$phone OR home_phone=$phone";
        //$user_id = $GLOBALS['db']->getOne($sql);
        //if (!$user_id) {
        //    $sql = ' SELECT user_id FROM '.$GLOBALS['ecs']->table('user_contact')
        //        ." WHERE contact_value=$phone AND contact_name IN ('mobile','tel')";
        //}
        //$user_id = $GLOBALS['db']->getOne($sql);
        //查看是否已经有该顾客的录音目录
        if($user_id){
            $dir_name = '/record_'.($user_id%7)."/$user_id";
            if (!file_exists($dir.$dir_name)) {
                mkdir($dir.$dir_name, 0777, true);
            }
            $final_dir = "$dir$dir_name/$date";
            if (!file_exists($final_dir)) {
                mkdir($final_dir, 0777, true);
            }
            copy("$dir/$v","$final_dir/$v"); //拷贝到新目录
            unlink("$dir/$v"); //删除旧目录下的文件
            $count++;
        }
    }
}
//print_r($count."个录音文件添加成功");
