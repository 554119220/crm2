<?php
/**
 * 积分处理
 */
class integral
{
     var $table;
     var $db;

     function __construct($table, $db)
     {
          $this->table = $table;
          $this->db    = &$db;
     }

     /**
      * 验证选择的推荐人是否有效
      * @param  int       $parent_id    用户id
      * return  boolean   返回验证的结果
      */
     public function parentIsExist($parent_id)
     {
          $sql = 'SELECT user_id FROM '.$this->table." WHERE user_id=$parent_id";
          $res = $this->db->getOne($sql);

          return $res;
     }

     /**
      * 获取积分列表
      * @param
      */
     public function getIntegralList ($platform = '', $start = '', $end = '')
     {
          $where = array (' available=1 ');
          if (!empty($platform))
          {
               $where[] = " platform=$platform ";
          }

          if (!empty($start))
          {
               $where[] = " present_start>=$start ";
          }

          if (!empty($end))
          {
               $where[] = " present_end<$end ";
          }

          $sql = 'SELECT * FROM '.$this->table;
          if (count($where) > 0)
          {
               $sql .= ' WHERE '.implode(' AND ', $where);
          }

          $integral_list = $this->db->getAll($sql);

          // 获取可赠送积分的销售平台
          $role_list = get_role_list(1);

          foreach ($integral_list as &$val)
          {
               $val['present_start'] = date('Y-m-d', $val['present_start']);
               $val['present_end']   = date('Y-m-d', $val['present_end']);

               if ($val['platform'] == 0)
                    $val['platform'] = '全平台';
               else
                    foreach ($role_list as $v)
                    {
                         if ($v['role_id'] = $val['platform'])
                         {
                              $val['platform'] = $v['role_name'];
                         }
                    }
          }

          return $integral_list;
     }

     /**
      * 获取可以选择的赠送条件
      */
     public function getIntegralWay ()
     {
          $sql = 'SELECT integral_way_id, integral_way, scale_type FROM '.$this->table;
          return $this->db->getAll($sql);
     }

     /**
      * 赠送积分
      * @param  int $user_id    顾客ID
      * @param  int $platform   适用平台  0 ：全平台
      * @way    int $way        赠送方式  1 ：推荐顾客 2 ：顾客生日 3 ：购买商品
      */
     public function countIntegral ($platform, $way)
     {
          $sql = 'SELECT * FROM '.$this->table.
               " WHERE integral_way=$way AND available=1 ORDER BY platform DESC";
          $res = $this->db->getAll($sql);

          if (is_array($res))
          {
               foreach ($res as $val)
               {
                    if (!is_array($val)) return false;

                    if ($val['platform'] == $platform)
                    {
                         $suit_rule = $val;
                    }
                    elseif ($val['platform'] == 0)
                    {
                         $suit_rule = $val;
                    }
               }
          }

          return $suit_rule;
     }

     /**
      * 统计已经送出了多少积分 
      */
     public function integralSentTotal ()
     {
     }
}
