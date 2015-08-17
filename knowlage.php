<?php
/*=============================================================================
#     FileName: knowlage.php
#         Desc:  知识，话术，产品说明，文章等处理
#       Author: Wuyuanhang
#        Email: 1828343841@qq.com
#     HomePage: kjrs_crm
#      Version: 0.0.1
#   LastChange: 2015-07-11 10:58:48
#      History:
=============================================================================*/

//class knowlage{
//    function __construct(){
//    }
//}
define('IN_ECS', true);
$msg = array(
    'code'    => false,
    'message' => '',
    'timeout' => 2000
);

$act = isset($_REQUEST['act'])&& !empty($_REQUEST['act']) ? mysql_real_escape_string($_REQUEST['act']) ? 'product'; 
//产品详细信息
if('goods_info)' == $act){
    $goods_sn = mysql_real_escape_string($_REQUEST['goods_sn']);   
    if ($goods_sn) {
        $sql = "SELECT * FROM `cz_ecms_goods_doc` WHERE goods_sn=$goods_sn";
        $res = getRow($sql,true);
        if (!$res) {
            $msg['message'] = '没有找到产品详细信息';
            $res = $msg;
        }
    }else{
        $msg['message'] = '商品编号有误，请核实后再试';
        $res = $msg;
    }

    die(json_encode($res));
}
//产品文章
elseif('goods_article'){

}

function getAll($sql=''){
    $link = myConect(); 
    if ($sql) {
        $res = query($sql);
        if ($res !== false)
        {
            $arr = array();
            while ($row = mysql_fetch_assoc($res))
            {
                $arr[] = $row;
            }

            return $arr;
        }
        else
        {
            return false;
        }
    }
}

function getOne(){
    $link = myConect(); 
}

function myConect(){
    $link = mysqli_connect("localhost","wyh","myh@kjrs365db","crm_zhishi") or die("Error " . mysqli_error($link)); 
    return $link;
}

function query($sql){
    if (!($query = mysql_query($sql, $this->link_id)) && $type != 'SILENT')
    {
        $error_message[]['message'] = 'MySQL Query Error';
        $error_message[]['sql'] = $sql;
        $error_message[]['error'] = mysql_error($this->link_id);
        $error_message[]['errno'] = mysql_errno($this->link_id);
        ErrorMsg();
        return false;
    }
    return $query;
}
function ErrorMsg($message = '', $sql = '')
{
    if ($message)
    {
        echo "<b>ECSHOP info</b>: $message\n\n<br /><br />";
        //print('<a href="http://faq.comsenz.com/?type=mysql&dberrno=2003&dberror=Can%27t%20connect%20to%20MySQL%20server%20on" target="_blank">http://faq.comsenz.com/</a>');
    }
    else
    {
        echo "<b>MySQL server error report:";
        print_r($error_message);
        //echo "<br /><br /><a href='http://faq.comsenz.com/?type=mysql&dberrno=" . $this->error_message[3]['errno'] . "&dberror=" . urlencode($this->error_message[2]['error']) . "' target='_blank'>http://faq.comsenz.com/</a>";
    }

    exit;
}
function getRow($sql, $limited = false)
{
    if ($limited == true)
    {
        $sql = trim($sql . ' LIMIT 1');
    }

    $res = query($sql);
    if ($res !== false)
    {
        return mysql_fetch_assoc($res);
    }
    else
    {
        return false;
    }
}
?>

