<?php
/**
 * ECSHOP 快速购买
 * ============================================================================
 * 版权所有 2010-2020 广州康健人生健康管理有限公司。
 * 网站地址: http://www.kjrs365.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: user.php 17067 2010-03-26 03:59:37Z liuhui $
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/lib_payment.php');

$act = $_REQUEST['act'] ? $_REQUEST['act'] : 'default';

$fields = array(
      'from_where'      => array(
            'id'        => 'from_id',
            'name'      => 'from',
            'enddate'   => 'enddate',
            'available' => 'available',
            'sort'      => 'sort'
      ),
      'customer_type'   => array(
            'id'        => 'type_id',
            'name'      => 'type_name',
            'available' => 'available',
            'sort'      => 'sort'
      ),
      'character'       => array(
            'id'        => 'character_id',
            'name'      => 'characters',
            'available' => 'available',
            'sort'      => 'sort'
      ),
      'income'          => array(
            'id'        => 'income_id',
            'name'      => 'income',
            'available' => 'available',
            'sort'      => 'sort'
      ),
      'disease'         => array(
            'id'        => 'disease_id',
            'name'      => 'disease',
            'available' => 'available',
            'sort'      => 'common_rate'
      ),
      'service_class'   => array(
            'id'        => 'class_id',
            'name'      => 'class',
            'available' => 'available',
            'sort'      => 'sort'
      ),
      'service_manner'  => array(
            'id'        => 'manner_id',
            'name'      => 'manner',
            'available' => 'available',
            'sort'      => 'sort'
      ),
      'payment'         => array(
            'id'        => 'pay_id',
            'name'      => 'pay_name',
            'available' => 'enabled',
            'sort'      => 'pay_order'
      ),
      'shipping'        => array(
            'id'        => 'shipping_id',
            'name'      => 'shipping_name',
            'available' => 'enabled',
            'code'      => 'shipping_code',
            'sort'      => 'shipping_order'
      ),
      'effects'         => array(
            'id'        => 'eff_id',
            'name'      => 'eff_name',
            'available' => 'available',
            'sort'      => 'sort'
      ),
      /*START 功能需求添加的代码 */
      'grade_type'      => array(
            'id'        => 'grade_type_id',
            'name'      => 'grade_type_name',
            'available' => 'available',
            'sort'      => 'sort'
      /*END 功能需求添加的代码 */
      )
);

if ($act == 'default')
{
      admin_priv('option_manage');

      $field = array(
            'from_where'     => ' from_id id, `from` name, FROM_UNIXTIME(enddate, "%Y-%m-%d") enddate, sort, available ',
            'customer_type'  => ' type_id id, type_name name, sort, available ',
            'character'      => ' character_id id, characters name, sort, available ',
            'income'         => ' income_id id, income name, sort, available ',
            'disease'        => ' disease_id id, disease name, common_rate sort, available ',
            'service_class'  => ' class_id id, class name, sort, available ',
            'service_manner' => ' manner_id id, manner name, sort, available ',
            'payment'        => 'pay_id id, pay_name name, pay_order sort, enabled available',
            'shipping'       => 'shipping_id id, shipping_code code, shipping_name name, shipping_order sort, enabled available',
            'effects'        => 'eff_id id, eff_name name, sort, available, user_name ',
            'grade_type'     => 'grade_type_id id, grade_type_name name, sort, available'
      );

      $table = $_REQUEST['table'] ? $_REQUEST['table'] : 'from_where';
      $sql = "SELECT $field[$table] FROM ".$ecs->table($table);
      if ($table == 'effects')
      {
            $sql .= ' , '.$GLOBALS['ecs']->table('admin_user').' WHERE admin_id=user_id ';
      }
      $sql .= ' ORDER BY sort ASC';
      //file_put_contents('sql.txt', $sql);

      $smarty->assign('data', $db->getAll($sql));
      if (isset($_REQUEST['table']))
      {
            $smarty->assign('t', $_REQUEST['table']);
            $cont = $smarty->fetch('option_manage.htm');
            die($cont);
      }
}

else if ($act == 'update')
{
      admin_priv('option_manage');

      if ($_POST['f'] == 'enddate')
      {
          $v = strtotime($_POST['v']) -28800;
          $v = $v >0 ? $v : 0;
      }
      else
      {
          $v = $_POST['v'];
      }

      $table = $_POST['t'];
      $field = '`'.$fields[$table][$_POST['f']].'`=\''.$v.'\'';
      $id = $fields[$table]['id'].'='.$_POST['id'];

      $sql = 'UPDATE '.$ecs->table($table)." SET $field WHERE $id";
      $db->query($sql);

      die(0);
}

elseif ($act == 'insert')
{
      admin_priv('option_manage');

      $arr = array(
            $fields[$_POST['t']]['name'] => $_POST['name'],
            $fields[$_POST['t']]['sort'] => $_POST['sort']
      );

      print_r($arr);

      if ($_POST['t'] == 'from_where' && isset($_POST['enddate']))
      {
            $enddate = strtotime($_POST['enddate']) -28800;
            $enddate >0 && $arr['enddate'] = $enddate;
      }
      elseif ($_POST['t'] == 'shipping' && isset($_POST['code']))
      {
            trim($_POST['code']) && $arr['shipping_code'] = trim($_POST['code']);
      }
      elseif ($_POST['t'] == 'payment' && isset($_POST['code']))
      {
            trim($_POST['code']) && $arr['pay_code'] = trim($_POST['code']);
      }

      $field = implode('`,`', array_keys($arr));
      $value = implode("','", array_values($arr));

      $sql = 'INSERT INTO '.$ecs->table($_POST['t'])."(`$field`)VALUES('$value')";
      $db->query($sql);
      die(0);
}

elseif ($act == 'del')
{
      admin_priv('option_manage');

      $id = $fields[$_POST['t']]['id'];
      $ava = $fields[$_POST['t']]['available'];
      $sql = 'DELETE FROM '.$ecs->table($_POST['t'])." WHERE $id=$_POST[id] AND $ava=0";
      $db->query($sql);
      die(0);
}
elseif ($act == 'ava')
{
      admin_priv('option_manage');
      $id    = $fields[$_POST['t']]['id'];
      $field = $fields[$_POST['t']]['available'];

      $sql = 'UPDATE'.$ecs->table($_POST['t'])." SET $field=IF($field, 0, 1 ) WHERE $id=$_POST[id]";
      $db->query($sql);
      die(0);

}

elseif ($act == 'getMemAdmin')
{
      include_once(ROOT_PATH.'includes/cls_json.php');
      $json = new JSON;
      $sql = 'SELECT user_id, user_name FROM '.$GLOBALS['ecs']->table('admin_user').
            ' WHERE role_id=9 AND status=1';
      $adminList['opt'] = $GLOBALS['db']->getAll($sql);
      $adminList['itemId'] = $_REQUEST['itemId'];
      die($json->encode($adminList));
}

/* 修改分类负责人 */
elseif ($act == 'updateEffAdmin')
{
      $admin_id= intval($_REQUEST['adminId']);
      $eff_id   = intval($_REQUEST['effId']);
      $sql = 'UPDATE '.$GLOBALS['ecs']->table('effects').
            " SET admin_id=IF($admin_id, $admin_id, admin_id) WHERE eff_id=$eff_id";
      $GLOBALS['db']->query($sql);

      include_once(ROOT_PATH.'includes/cls_json.php');
      $json = new JSON;
      $sql = 'SELECT a.user_name FROM '.$GLOBALS['ecs']->table('admin_user').' a, '.
            $GLOBALS['ecs']->table('effects').
            " e WHERE a.user_id=e.admin_id AND e.eff_id=$eff_id";
      die($json->encode(array('user_name'=>$GLOBALS['db']->getOne($sql), 'eff_id'=>$eff_id)));
}

$smarty->assign('default', 1);
$smarty->display('option_manage.htm');

?>
