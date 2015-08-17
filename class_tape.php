<?php
define('IN_ECS',true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

$current_dir = scandir("../class_tape/");

array_shift($current_dir);
array_shift($current_dir);

foreach($current_dir as $val){
    $sql_insert = 'REPLACE INTO '.$GLOBALS['ecs']->table('tape_favorite').
        '(file_path,public,status,add_time)'."VALUES('class_tape/$val',3,1,{$_SERVER['REQUEST_TIME']})";
    $GLOBALS['db']->query($sql_insert);
}

$sql_select = 'SELECT favor_id,file_path,public FROM '.$GLOBALS['ecs']->table('tape_favorite').
" WHERE public=3";
$result = $GLOBALS['db']->getAll($sql_select);

echo "<pre>";
var_dump($result);
echo "</pre>";

?>
