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
define('TIME_LAG', 50400);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
date_default_timezone_set('Asia/Shanghai');

/*
每日回购率
订单总额
订单总量
配送方式
支付方式

各个团队订单总额
各个团队订单总量

整体销量走势及订单量走势
每个客服订单量及销量走势
每个团队的销量及订单量走势
 */

/* 默认情况下获取整体的销量 */

if ($_REQUEST['act'] == 'trend')
{
     admin_priv('trend');
     $role_list = get_role_list(1);

     if (in_array($_POST['trend'], array('day', 'week', 'month', 'year')))
          $trend = $_POST['trend'];

     $start = isDataType(trim($_POST['start']));
     $end   = isDataType(trim($_POST['end']));
     $team  = intval($_POST['team']);
     $saler = trim($_POST['saler']);

     // 初始化查询条件
     $where = " WHERE order_status=5 AND pay_status=2";

     if ($start && $end)
     {
          $end_time = counting_period($start, $end, $trend);
     }
     else 
     {
          // 获取当前的年月
          $start = date('Y-m-1');
          $end   = date('Y-m-d');

          $end_time = counting_period($start, $end);
     }

     if (date('H', time()) >= 18)
     {
         foreach ($end_time as &$val)
         {
             $val += 24*3600;
         }
     }
     $start = $end_time[0];
     $end   = end($end_time);

     if ($team)
     {
          $sql = 'SELECT role_name FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id=$team";
          $data['title'] = $GLOBALS['db']->getOne($sql);
     }

     // 统计团队销量
     if($saler)
     {
          $sql = 'SELECT user_name, user_id FROM '.$GLOBALS['ecs']->table('admin_user').
               " WHERE user_id IN ($saler) AND status>0 AND stats>0";
          $admin_list = $GLOBALS['db']->getAll($sql);

          $where .= " AND admin_id IN ($saler)";
     }
     elseif ($team)
     {
         /* 可删除 
          $sql = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user').
               " WHERE role_id=$team AND status>0";
          $team_users = $GLOBALS['db']->getAll($sql);

          foreach ($team_users as $val)
          {
                $users[] = $val['user_id'];
          }

          $where .= ' AND admin_id IN ('.implode(',', $users).')';
          */
          $where .= " AND platform=$team ";
     }
     else 
     {
     }

     $time = " AND add_time>=$start AND add_time<$end ";
     $sql = 'SELECT final_amount, add_time, add_admin_id, admin_id FROM '.
          $GLOBALS['ecs']->table('order_info').$where.$time.$only.' ORDER BY add_time ASC';
     $res = $GLOBALS['db']->getAll($sql);

     $format = array ('day' => 'm-d', 'week' => 'm-d', 'month' => 'Y-m', 'year' => 'Y');
     $lang   = array ('day' => '每日', 'week' => '每周', 'month' => '月度','year' => '年度');

     foreach ($end_time as $k=>$v)
     {
          if ($v == end($end_time)) break;

          foreach ($res as &$val)
          {
               if ($val['add_time'] >= $v && $val['add_time'] < $end_time[$k+1])
               {
                    if ($saler)
                    {
                         $amount[$val['admin_id']] = bcadd($amount[$val['admin_id']], $val['final_amount'], 2);
                         $number[$val['admin_id']] += 1;
                    }
                    else 
                    {
                         $amount = bcadd($amount, $val['final_amount'], 2);
                         $number += 1;
                    }
                    unset($val);
               }
          }

          $key = empty($trend) ? 'day' : $trend;
          $data['labels'][] = date($format[$key], $v+28800);
          $data['amount'][] = $amount;
          $data['number'][] = $number;

          unset($amount, $number);
     }

     if (is_array($data['amount']))
     {
          $data['amount'] = build_data($data['amount'], $lang[$key].'销量', $saler);
          $data['number'] = build_data($data['number'], $lang[$key].'订单量', $saler);
     }

     // 计算纵坐标标尺
     $amount = calc_scale($data['amount']);
     $number = calc_scale($data['number']);

     $zoom = pow(10, (strpos($amount['max'], '.') - strlen(strval($number['max']))));

     $data['start_scale'] = 0;
     $offset = round(max($amount['max']/$zoom, $number['max']), 0); 
     $data['end_scale']   =  $offset + ceil($offset/10);

     if (isset($data['amount']['value']))
     {
          foreach ($data['amount']['value'] as &$val)
          {
               $val = round($val/$zoom, 2);
          }
     }
     else 
     {
          foreach ($data['amount'] as $key=>$val)
          {
                foreach ($val as $k=>$v)
                {
                     if ($k == 'value')
                     {
                           foreach ($v as $e=>$a)
                           {
                                 $data['amount'][$key][$k][$e] = round($a/$zoom, 2);
                           }
                     }
                }
          }
     }

     $data['title'] = isset($data['title']) ? $data['title'] : '公司';
     $sale_unit = array(10 => '拾', 100 => '百', 1000 => '仟', 10000 => '万', 100000 => '十万');
     $data['subtitle'] = '（销量单位：'.$sale_unit[$zoom].'， '.'订单单位：笔）';
     $data['height'] = 500;
     $data['scale_space'] = $data['end_scale']/10;
     $data['scale_size']  = 5;
     $data['time'] = date('Y-m-d H:i:s');

     $data['data'] = array ($data['amount'], $data['number']);
     if ($saler)
     {
          foreach ($data['data'] as $val)
          {
               foreach ($val as $v)
               {
                    $temp[] = $v;
               }
          }
          $data['data'] = $temp;
     }

     $data['label'] = array('fontsize'=>10, 'textAlign'=>'left', 'rotate'=>0);
     if (count($data['labels']) >= 15)
     {
          $data['label']['rotate'] = 50;
     }

     unset($data['amount'], $data['number']);

     /**
           * 数据 data
           * 横坐标标签 labels
           * 标题 title
           * 副标题 subtitle
           * 纵坐标起始 start_scale
           * 纵坐标结束 end_scale
           * 纵坐标刻度 scale_space scale_size
           *
           *  var data = [
           *          {name : '总销量', value : res.amount, color : '#3687cf', line_width : 2},
           *          {name : '订单量', value : res.number, color : '#000', line_width : 2}
           *  ];
           */


     include '../includes/cls_json.php';
     $json = new JSON;
     $data = $json->encode($data);

     $trend && die($data);
     $smarty->assign('data', $data); // 输出JSON数据

     $smarty->assign('role_list', $role_list);
     $smarty->display('trend.htm');
}

elseif ($_REQUEST['act'] == 'query_saler')
{
     $team = intval($_POST['team']);
     if ($team)
     {
          $sql = 'SELECT user_id, user_name FROM '.$GLOBALS['ecs']->table('admin_user').
               " WHERE role_id=$team AND status=1";
          $saler_list = $GLOBALS['db']->getAll($sql);

          include '../includes/cls_json.php';
          $json = new JSON;
          die($json->encode($saler_list));
     }
}

/**
 * 验证是否为有效的日期格式
 * @param $str  日期
 */
function isDataType ($str)
{
     $pattern = '/^2\d{3}-\d{2}-\d{2}$/';
     if(preg_match($pattern, $str))
          return $str;
     else 
          return false;
}

/**
 * 计算时间周期
 * @param   $start  开始日期
 * @param   $end    结束日期
 * @param   $trend  周期参数：day, week, month, year
 * return   时间戳数组
 */
function counting_period ($start, $end, $trend = 'day')
{
     $start = strtotime($start);
     $end   = strtotime($end);
     $end_time = array ($start - 6*3600);

     switch ($trend)
     {
           case 'day' : 
                while (end($end_time) <= $end)
                {
                     $end_time[] = end($end_time) + 24*3600;
                }
                break;
           case 'week' : 
                $week = date('w', $start);
                $end_time[] = $start + 24*3600*(8-$week) - 6*3600;
                while (end($end_time) <= $end)
                {
                      $end_time[] = end($end_time) + 24*3600*7;
                }
                break;
           case 'month' :
                $day = date('d', $start); // 计算当前为本月第几天
                $month = date('t', $start); // 计算本月总天数
                $end_time[] = $start + ($month - $day)*24*3600 +18*3600;
                while (end($end_time) <= $end)
                {
                     $temp = end($end_time) + 24*3600;
                     $day = date('d', $temp); // 计算当前为本月第几天
                     $month = date('t', $temp); // 计算本月总天数
                     $end_time[] = strtotime(date('Y-m-d 18:00:00', $temp + ($month - $day)*24*3600));
                }
                break;
           case 'year' :
                $end_time[] = strtotime((date('Y', end($end_time))).'-12-31 18:00:00');
                while (end($end_time) <= $end)
                {
                     $end_time[] = strtotime((date('Y', end($end_time))+1).'-12-31 18:00:00');
                }
                break;
     }

     $end_time[count($end_time) -1] = $end +18*3600;
     return $end_time;
}

/**
 * 构造数据
 * @param   $data   原数据
 * @param   $saler  查询条件中的客服
 * return   符合图表格式的数据
 */
function build_data ($data, $note, $saler = '')
{
     $color = '0123456789ABCDEF';
     if (!empty($saler))
     {
          $saler = explode(',', $saler);
          foreach ($saler as $v)
          {
               foreach ($data as $val)
               {
                    $arr[$v]['value'][]    = $val[$v] ? $val[$v] : 0;
                    $arr[$v]['line_width'] = 1;

                    $arr[$v]['color'] = '#';
                    for (; strlen($arr[$v]['color']) < 7; )
                         $arr[$v]['color'] .= $color[mt_rand(0, strlen($color))];

                    $sql = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$v";
                    $arr[$v]['name'] = $GLOBALS['db']->getOne($sql);
                    $arr[$v]['name'] .= '-'.$note;
               }
          }
     }
     else 
     {
          foreach ($data as $val)
          {
               $arr['value'][]     = $val ? $val : 0;
               $arr['line_width'] = 2;

               $arr['color'] = '#';
               for (; strlen($arr['color']) < 7; )
                    $arr['color'] .= $color[mt_rand(0, strlen($color))];

               $arr['name'] = '公司当月-'.$note;
          }
     }

     return $arr;
}

/**
 * 计算标尺
 * @param   $arr   数据源
 * return   返回数据源中最大与最小值
 */
function calc_scale ($arr)
{
     if (isset($arr['value']))
          return array ('max' => max($arr['value']), 'min' => min($arr['value']));
     else 
     {
          foreach ($arr as $val)
          {
               if (is_array($val))
               {
                    $max[] = max($val['value']);
                    $min[] = min($val['value']);
               }
          }
     }

     return array ('max' => max($max), 'min' => min($min));
}
