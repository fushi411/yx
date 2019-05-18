<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保流程记录模型
 * @author 
 */

class YxhbAppflowtableModel extends Model {

    /**
     * 获取全流程
     * @param  string $modname 流程名
     * @return array   摘要数组
     */
    public function getAllProc($modname)
    {
        $proInfo = array();
        $temp    = array();
        $res = $this->field('pro_name,pro_id,per_name,per_id,stage_id,point,auth_id')->where(array('pro_mod'=>$modname, 'stat'=>1))->order('stage_id asc')->select();
        $boss = D('yxhb_boss');
        
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
        $res = $this->field('pro_name,pro_id,per_name,per_id,stage_id,point,condition,stage_name,auth_id')->where(array('pro_mod'=>$modname, 'stat'=>1))->order('stage_id asc')->select();
        $boss = D('yxhb_boss');
        $info = D('Yxhb'.$modname,'Logic')->recordContent($aid);
        $flow = array();
        $wxid = $boss->getWxiDFromName($info['applyerName']);
        $flag = 'defalt';
        foreach($res as $v){
            if(empty($v['auth_id'])) $flow['defalt'][]=$v;
            $auth = explode(',',$v['auth_id']);
            if(in_array($wxid,$auth)){
                $flow['auth'][] = $v;
                $flag = 'auth';
            }
        }
        $res = $flow[$flag];
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
    public function getStepInfo($pro_id, $stage,$mod)
    {
        $map['pro_mod'] = $mod;
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
     * @param string $view_id 显示对应
     * @return array $res 
     */
    public function getConditionStepHtml($modname,$condition,$view_id)
    {
        $map = array(
            'pro_mod' => $modname, 
            'stat'    => 1,
        );
        if(!empty($view_id)) $map['view_id'] = $view_id;
        $data = $this
                ->field('pro_name,pro_id,per_name,per_id,stage_id,point,condition,stage_name')
                ->where($map)
                ->order('stage_id asc,role_id asc')
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
        $boss = D('YxhbBoss');
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
     /**
     * 获取各级流程
     */
    public function getProStep($id){
        $data = $this->where(array('stat' => 1,'view_id' => $id))->order('stage_id')->select();
        $res = array();
        $wx  = array();
        $boss = D('YxhbBoss');
        foreach($data as $v){
            $wxid = $boss->getWXFromID($v['per_id']);
            $res[$v['stage_id']][] = array(
                'wxid'   => $wxid,
                'name'   => $v['per_name'],
                'avatar' => $boss->getAvatar($v['per_id']),
                'parallel' => 1,
            );
            $wx[$v['stage_id']]['wxid'] .= "$wxid,";
            $wx[$v['stage_id']]['auth_id'] = $v['auth_id'];
        }
        foreach($res as $k=>$v){
            $res[$k]['html'] = D('Html')->getProConfigHtml($v);
            $res[$k]['wxid'] = trim($wx[$k]['wxid'],',');
            $res[$k]['auth_id'] = trim($wx[$k]['auth_id'],',');
        }
        return $res;
    }
    
    public function getProIdByViewid($id){
        $data = $this->where(array('view_id' => $id))->order('stat desc')->find();
        return empty($data)?$this->getProId():$data['pro_id'];
    }

    /**
     * 获取最新pro_id
     */
    public function getProId(){
        $data = $this->where(array('stat' => 1))->order('pro_id desc')->find();
        return $data['pro_id']+1;
    }
    // 获取同条件 人员
    public function getOtherProBycondition($condition,$id,$mod){
        $res = '';
        $data = $this->where(array('view_id'=> array('neq',$id),'condition' => $condition,'stat' => 1,'pro_mod'=>$mod))->group('auth_id')->select();
        if(empty($data)) return $res;
        foreach($data as $v){
            $res .= ','.$v['auth_id'];
        }
        $res = explode(',',trim($res,','));
        $res = array_unique($res);
        $res = implode(',',array_filter($res));
        return $res;
    }

        // 是否有审批流程 判断
        public function havePro($flowName,$condition){
            $system = 'yxhb';
            // 非审批流程 return true
            if($flowName == 'CostMoneyPay') return true;
            $systemU = ucfirst($system);
            $boss = D($systemU.'Boss');
            $appflowtable = D($systemU.'Appflowtable');
            $getInit = $appflowtable->getAllStepInfo($flowName, 1);
            // 查找所属流程 有指定流程 没有指定流程
            $wxid = session('wxid');
            $flow = array();
            $flag = 'defalt';
            foreach($getInit as $v){
                if(empty($v['auth_id'])) $flow['defalt'][]=$v;
                $auth = explode(',',$v['auth_id']);
                if(in_array($wxid,$auth)){
                    $flow['auth'][] = $v;
                    $flag = 'auth';
                }
            }
            $getInit = $flow[$flag];
            // 无审批流 return false
            if(empty($getInit)) return false;
            if(empty($condition)) return true;
            $temp = array();
            foreach($getInit as $v){
                if(strrpos($v['condition'],$condition) === false) $temp[] = $v;
            }
            if(empty($temp)) return false;
            return true;
        }
        // 获取当前审批 流程
        public function getStepNow($mod,$pro_id,$per_id){
            $map = array(
                'pro_id' => $pro_id,
                'pro_mod' => $mod,
                'stat' => 1,
                'per_id' => $per_id,
            );
            $res = $this->field('sign')->where($map)->find();
            return empty($res)?0:$res['sign'];
        }
}
