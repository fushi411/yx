<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/19 0019
 * Time: 下午 2:1
 */

 /**
  * 获取某个全部审批流程
  * @param string $mod_name 流程名
  *  @return array 
  */
 function GetAppFlow($system,$mod_name)
{
    if(!$mod_name) return false;
    $res = M($system.'_appflowtable a')
            ->field('a.pro_name,b.id,a.`condition`,b.name,b.avatar')
            ->join($system.'_boss b on a.per_id = b.id')
            ->where(array('a.pro_mod' => $mod_name, 'a.stat' =>1))
            ->order('a.stage_id')
            ->select();
    if(!empty($res)){
        foreach($res as $k=>$v){
            $res[$k]['condition'] = json_decode($v['condition']);
        }
    }
    return $res;
}

 /**
  * 获取某个流程的推送人员
  * @param string $mod_name 流程名
  *  @return array 
  */
  function GetPush($system,$mod_name)
  {
        if(!$mod_name) return false;
        $data = array(
            'receviers' => '',
            'data'      => array()
        );
        // 查询推送的人员
        $res = M($system.'_appflowtable')
              ->field('condition')
              ->where(array('pro_mod' => $mod_name.'_push', 'stat' => 1))
              ->find();
        // 为空，返回空数组
        if(empty($res)) return $data;
        $data['receviers'] = $res['condition'];
        $tempStr = explode('|',$res['condition']);
        // where 条件拼接
        foreach($tempStr as $k => $v){
            if($k != 0) $where .=' or ';
            $where .= "wxid = '{$v}'";
        }
        $res = M($system.'_boss')
            ->field('name,avatar')
            ->where($where)
            ->select();
        $data['data'] = $res;
        return $data;
  }