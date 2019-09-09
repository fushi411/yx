<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保审批流程记录模型
 * @author 
 */

class KkAppflowprocModel extends Model {

    /**
     * 获取审批流程记录
     * @param  string $modname 流程名
     * @param  int    $aid 记录ID
     * @return array   摘要数组
     */
    public function contentProc($mod_name, $aid, $authArr)
    {
        $uid = session('kk_id');
        $resArr = $this->field(true)->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>array(array('egt',0), array('lt',3), 'and')))->order('app_stat desc,app_stage asc,approve_time asc')->select();
        $isCopyto = 0;
        $isApplyer = 0;
        $isPasser = 0;
        $isRefuse = 0;
        $isFlowBegin = 0;
        $boss = D('kk_boss');
        $per_word = '';
        foreach ($resArr as $k=> $val) {
            if ($val['app_stat']==0 && $val['per_id']==$uid) {
                $isApplyer = 1;
            }
            // 是否为审批人之一
            if ($val['per_id']==$uid) {
                $isPasser = 1;
                $per_word = $val['app_word'];
            }
            
            $wxid = $boss->getWXFromID($val['per_id']);
            array_push($authArr, $wxid);
            $val['avatar'] = $boss->getAvatar($val['per_id']);
            // 审批撤销时间限制
            $val['able_del'] = 0;
            $now = time();
            $proc_time = strtotime($val['approve_time']);
            if($proc_time < $now && $proc_time+300 >$now) $val['able_del'] = 1;
            $resApply[] = $val;

            // 是否被拒绝
            if ($val['app_stat']==1) {
                $isRefuse = 1;
            }
            // 审批是否已经开始
            if ($val['app_stat']>0) {
                $isFlowBegin = 1;
            }
            
        }
        $procArr = array(
                    'isCopyto'   => $isCopyto,
                    'process'    => $resApply,
                    'isApplyer'  => $isApplyer,
                    'isPasser'   => $isPasser,
                    'isRefuse'   => $isRefuse,
                    'isFlowBegin'=> $isFlowBegin,
                    'authArr'    => $authArr,
                    'per_word'   => $per_word
                );
        return $procArr;
    }

    public function getWorkFlowStatus($mod_name, $aid){
        $appInfo = $this->field('app_stat,app_name,app_stage')->where(array('mod_name'=>$mod_name, 'aid'=>$aid, 'app_stat'=>array('egt',0), 'app_stat'=>array('lt',3)))->order('app_stat asc')->select();
        $temp = array();
        foreach($appInfo as $val){
            if($val['app_stat'] != 1 ) continue;
            $temp = $val;
        }
        $appInfo = !empty($temp)?$temp:$this->field('app_stat,app_name,app_stage')->where(array('mod_name'=>$mod_name, 'aid'=>$aid, 'app_stat'=>array('egt',0), 'app_stat'=>array('lt',3)))->order('app_stat asc')->find();

      if($appInfo['app_stat']=='1'){
        $apply = array("stat"=>1, "content"=>"已退审", "stage"=>$appInfo['app_stage']);
      } elseif ($appInfo['app_stat']=='0'){
        $apply = array("stat"=>0, "content"=>$appInfo['app_name']."中", "stage"=>$appInfo['app_stage']);
      } elseif ($appInfo['app_stat']=='2'){
        $apply = array("stat"=>2, "content"=>"已通过", "stage"=>$appInfo['app_stage']);
      }else{
        $apply = array("stat"=>-1, "content"=>"系统出错", "stage"=>$appInfo['app_stage']);
      }
      return $apply;
    }

    /**
     * 获取实际单步审批流程信息
     * @param  [string] $mod_name [流程名]
     * @param  [integer] $aid      [记录ID]
     * @param  integer $uid      [用户ID]
     * @return [array]           [流程信息]
     */
    public function getStepInfo($mod_name, $aid, $uid='')
    {
        if (empty($uid)) {
            $uid = session('kk_id');
        }
        $map['mod_name'] = $mod_name;
        $map['app_stat'] = 0;
        $map['aid']      = $aid;
        $map['per_id']   = $uid;
        $map['app_stat'] = array('lt',3);
        $res = $this->field(true)->where($map)->order('app_stage desc')->find();
        return $res;
    }

    // 审批记录保存
    public function updateProc($mod_name,$rid, $option, $word,$img)
    {
        $per_id = session('kk_id');
        $flowtable = D('KkAppflowtable');
        // 查看是否免签
        $StepInfo = $this->getStepInfo($mod_name,$rid,$per_id);
        $signIsNeed = $flowtable->getStepNow($mod_name,$StepInfo['pro_id'],$per_id);
        if($signIsNeed == 1){
            $res = M('yx_config_sign')->where(array('wxid' => session('wxid') ,'stat' => 1))->find();
            // 无指定签字  选取上一次签字
            if(empty($res)){
                $signMap = array(
                    'per_id' => session('kk_id'),
                    'stat' => 1,
                    'sign' => array('neq',''),
                );
                $res = $this->where($signMap)->order('time desc')->find();
                $res['url'] = $res['sign'];
            }
            $img = empty($img)?$res['url']:$img;
        }
        $map['mod_name'] = $mod_name;
        $map['per_id'] = $per_id;
        $map['app_stat'] = 0;
        $map['aid'] = $rid;
        $data['app_stat'] = $option;
        $data['app_word'] = $word;
        $data['sign'] = $img;
        $data['approve_time'] = date('Y-m-d H:i:s');
        return $this->where($map)->save($data);
    }

    //会签同级+996
    public function refuse($mod_name, $aid, $stage_id)
    {
        $map['mod_name'] = $mod_name;
        $map['aid'] = $aid;
        $map['app_stat'] = array('neq', 1);
        //$data['approve_time'] = date('Y-m-d H:i:s');
        return $this->where($map)->save($data);
    }

    /**
     * 获取同级未签数
     * @param  [string]  $mod_name [流程名]
     * @param  [integer] $stage_id [审批ID]
     * @param  [integer] $uid      [用户ID]
     * @return [array]             [流程信息]
     */
    public function getSameProcNum($mod_name, $aid, $stage_id)
    {
        $map['mod_name'] = $mod_name;
        $map['aid'] = $aid;
        $map['app_stage'] = $stage_id;
        $map['app_stat'] = 0;
        $res = $this->field(true)->where($map)->count();
        return $res;
    }

    /**
     * 获取人员已在审批中
     * @param  [string]  $mod_name [流程名]
     * @param  [integer] $stage_id [审批ID]
     * @param  [integer] $uid      [用户ID]
     * @return [array]             [流程信息]
     */
    public function getPerSameProcNum($per_name,$per_id,$mod_name, $aid, $stage_id)
    {
        $map['mod_name']  = $mod_name;
        $map['aid']       = $aid;
        $map['app_stage'] = $stage_id;
        $map['per_name']  = $per_name;
        $map['per_id']    = $per_id;
        $map['app_stat']  = array(array('eq',2),array('eq',0),'or');
        $res = $this->field(true)->where($map)->count();
        return $res;
    }
    
    public function addProc($data, $aid, $per_name, $per_id, $stageID)
    {
      $record['pro_id'] = $data['pro_id'];
      $record['mod_name'] = $data['pro_mod'];
      $record['app_name'] = $data['stage_name'];
      $record['aid'] = $aid;
      $record['per_name'] = $per_name;
      $record['per_id'] = $per_id;
      $record['app_stat'] = 0;
      $record['app_stage'] = $stageID;
      $record['time'] = date('Y-m-d H:i:s');
      $record['approve_time'] = date('Y-m-d H:i:s');
      $res = $this->add($record);
      return $res;
    }

    public function reset($mod_name,$aid)
    {
        $res = $this -> where(array('mod_name'=>$mod_name,'aid'=>$aid,'app_stat' => 3)) -> setInc('app_stat', 3);
        return $res;
    }


    /**
     * 审批撤回
     */
    public function procReset($mod_name,$aid){
        $res = $this -> where(array('mod_name'=>$mod_name,'aid'=>$aid,'app_stat' => 0)) -> select();
        $count = count($res);
        if($count <= 0) return array('code' => 404, 'msg' => '流程已结束，无法撤回');
        $name = session('name');
        
        // 查看下一级是否已经审批
        $sql = "SELECT id,app_stat,per_name,app_stage FROM  kk_appflowproc WHERE  mod_name = '{$mod_name}' AND aid = {$aid}
                AND app_stage = (
                    SELECT  app_stage  FROM kk_appflowproc WHERE  mod_name = '{$mod_name}' AND aid = {$aid}   
                    AND per_name = '{$name}'  ) + 1 ORDER BY app_stat desc";
        $res = M()->query($sql);
        if($res[0]['app_stat'] != 0)  return array('code' => 404, 'msg' => '下一流程人已批复,无法撤回');
         // 撤销-> 会审阶段  下一阶段 置 3
         if(!empty($res) ) $this->where(array('app_stage' => $res[0]['app_stage'],'aid'=>$aid,'mod_name' => $mod_name))->setInc('app_stat', 3);
         // 撤销-> 自身置0
        $this->where(array('mod_name' => $mod_name,'aid' => $aid, 'per_name' => $name))->setField('app_stat', 0);
        return array('code' => 200, 'msg' => '撤回成功');
    }

    // 更新审批字段
    public function setApp_word($mod_name,$aid,$id,$word){
        $res = $this -> where(array('mod_name'=>$mod_name,'aid'=>$aid, 'per_id' => $id,'app_stat' => 0)) ->setField('app_word', $word);
        return $res;
    }
    /**
     * 审批状态修改
     */
    public function proStat($mod_name,$aid,$stat){
        if(empty($stat) && $stat !== 0 ) return false;
        return $this -> where(array('mod_name'=>$mod_name,'aid'=>$aid)) ->setField('app_stat', $stat);
    }
}
