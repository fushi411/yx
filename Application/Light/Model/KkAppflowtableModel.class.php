<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保流程记录模型
 * @author 
 */

class KkAppflowtableModel extends Model {

    /**
     * 获取全流程
     * @param  string $modname 流程名
     * @return array   摘要数组
     */
    public function getAllProc($modname)
    {
        $proInfo = array();
        $temp    = array();
        $res = $this->field('pro_name,pro_id,per_name,per_id,stage_id,point')->where(array('pro_mod'=>$modname, 'stat'=>1))->order('stage_id asc')->select();
        $boss = D('kk_boss');
        
        foreach($res as $key => $value){
            $k = $value['stage_id']-1;
            $temp[$k][] = $value;
        }
        foreach ($temp as $key => $value) {
            foreach($value as $k => $val){
                
                $procIDList = $val['per_id'].",";
                $procNameList = $val['per_name'].",";
                $wxid = $boss->getWXFromID($val['per_id']);
                $avatar = $boss->getAvatar($val['per_id']);
                $procWXIDList = $wxid['wxid'].",";
                $sign = count($value) < 2?'':1;
                $proInfo[] = array('id'=>$val['per_id'],
                                   'realname'=>$val['per_name'],
                                   'wxid'=>$wxid,
                                   'avatar'=>$avatar,
                                   'sign'  => $sign
                                  );
                
            }
        }
        $firstProc = $proInfo[0];
        return array('first'=>$firstProc,
                     'proInfo'=>$proInfo,
                     'res'=>$res,
                     'title'=>$res[0]['pro_name']
                    );
    }
     /**
     * 获取全流程
     * @param  string $modname 流程名
     * @return array   摘要数组
     */
    public function getAllProc_new($modname,$aid)
    {
        $proInfo = array();
        $temp    = array();
        $res = $this->field('pro_name,pro_id,per_name,per_id,stage_id,point,condition,stage_name')->where(array('pro_mod'=>$modname, 'stat'=>1))->order('stage_id asc')->select();
        $boss = D('kk_boss');
        
        foreach($res as $key => $value){
            $conditions=explode(";",$value['condition']);
            $conditions = array_filter($conditions);
            if( count($conditions)!=0){
                $con_flag=1;
                foreach($conditions as $cons){
                    $cons=substr($cons,1,-1);
                    $cons_sub=explode("|",$cons);
                    //数据组装
                    foreach($cons_sub as $c_s){
                        $cons_sub_sub=explode(":",$c_s);
                        $cons_array[$cons_sub_sub[0]]=$cons_sub_sub[1];
                    }
                    $con_query="SELECT 1 from ".$cons_array['table']." WHERE id=$aid ".$cons_array['conditions'];
                    $res1 = M()->query($con_query);
                    $count=count($res1);
                    if($count){
                        $con_flag*=1; //满足条件
                    } else {
                        $con_flag*=0;//不满足条件
                        break;
                    }
                }
                //不满足条件结束本次循环
                if($con_flag==0) continue;
            }
            $k = $value['stage_id']-1;
            $temp[$k][] = $value;
        }
        foreach ($temp as $key => $value) {
            foreach($value as $k => $val){
                
                $procIDList = $val['per_id'].",";
                $procNameList = $val['per_name'].",";
                $wxid = $boss->getWXFromID($val['per_id']);
                $avatar = $boss->getAvatar($val['per_id']);
                $procWXIDList = $wxid['wxid'].",";
                $sign = count($value) < 2?'':1;
                $proInfo[] = array('id'=>$val['per_id'],
                                   'realname'=>$val['per_name'],
                                   'wxid'=>$wxid,
                                   'avatar'=>$avatar,
                                   'sign'  => $sign,
                                   'stage_name' =>$val['stage_name'],
                                   'stage_id' =>$val['stage_id'],
                                  );
                
            }
        }
        
        foreach($proInfo as $key=>$val){
            $proc = 1; // 1 审批 2 会审 0 不用显示
            if($key == 0 )  $proc = 0;
            if($proInfo[$key-1]['stage_id'] == $val['stage_id']) $proc = 2;
            
            $proInfo[$key]['proc'] = $proc; 
        }
        
        $firstProc = $proInfo[0];
      
        return array('first'=>$firstProc,
                     'proInfo'=>$proInfo,
                     'res'=>$res,
                     'title'=>$res[0]['pro_name']
                    ); 
    }
    public function getStepInfo($pro_id, $stage)
    {
        $map['pro_id'] = $pro_id;
        $map['stage_id'] = $stage;
        $map['stat'] = 1;
        $res = $this->field(true)->where($map)->find();
        return $res;
    }

    public function getAllStepInfo($mod_name, $stage)
    {
        $map['pro_mod'] = $mod_name;
        $map['stage_id'] = $stage;
        $map['stat'] = 1;
        $res = $this->field(true)->where($map)->select();
        return $res;
    }

    public function getTopStep($mod_name)
    {
        $map['pro_mod'] = $mod_name;
        $map['stat'] = 1;
        return $this->where($map)->max('stage_id');
    }

     /**
     * 获取条件流程html（建议一个条件为一整套流程，不使用公用审批人）
     * @param string $modname 模块名
     * @param string $condition 当前状态
     * @return array $res 
     */
    public function getConditionStepHtml($modname,$condition)
    {
        $map = array(
            'pro_mod' => $modname, 
            'stat'    => 1,
        );
        $data = $this
                ->field('pro_name,pro_id,per_name,per_id,stage_id,point,condition,stage_name')
                ->where($map)
                ->order('stage_id asc')
                ->select();
        $data = $this->getAccordCondition($data,$condition);
        $data = $this->getProInfo($data);
        $html = D('Html')->getProHtml($data);
        return $html;
    }
    /**
     * 挑选符合条件的数据
     * @param string $condition 条件
     * @return array $res 
     */
    public function  getAccordCondition($data,$condition){
        $temp = $data;
        foreach($data as $k => $v){
            if(empty($v['condition'])) continue;
            if(strpos($v['condition'],$condition) === false) unset($temp[$k]); 
        }
        $temp = array_values($temp);
        return $temp;
    }

    /**
     * 流程人员数组重构
     * @param array $data 流程数据
     * @return array $info 
     */
    public function getProInfo($data){
        $boss = D('KkBoss');
        $info = array();
        foreach($data as $k => $val){
            $wxid   = $boss->getWXFromID($val['per_id']);
            $name   = $boss->getNameFromID($val['per_id']);
            $avatar = $boss->getAvatar($val['per_id']);
            // 同级审批 是true 否false
            $parallel = $data[$k+1]['stage_id'] == $val['stage_id'] ? true : false;
            $info[] = array(
                'wxid'     => $wxid,
                'name'     => $name,
                'avatar'   => $avatar,
                'parallel' => $parallel,
            );
        }
        return $info;
    }
}
