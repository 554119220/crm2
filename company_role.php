<?php 
define('IN_ECS', true);
date_default_timezone_set('Asia/Shanghai');
require(dirname(__FILE__) . '/includes/init.php');

//公司规章制度详情
if($_REQUEST['act'] == 'get_rule')
{
    $id = intval($_REQUEST['id']);
    $sql_select = 'SELECT content FROM '.$GLOBALS['ecs']->table('company_system').
        " WHERE id=$id"; 
    $f = $GLOBALS['db']->getOne($sql_select);

    switch($f){
	    case 'ext':
		    $file_name = 'ext.htm';
		    break;
	    case 'company_rule':
		    $file_name = 'employee_rule.htm';
		    break;
	    case 'sales_rule':
		    $file_name = 'sales_rule.htm';
		    break;

    }
    $res['main'] = $smarty->fetch($file_name);

    die($json->encode($res));
}

?>
