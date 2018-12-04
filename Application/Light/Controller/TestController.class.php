<?php
namespace Light\Controller;

class TestController extends \Think\Controller {
   

    public function Sign()
    {
        $mod_name = I('modname');
        $detailAuthArr =  D('YxDetailAuth')->getAuthArray($mod_name);
        $authArr = $detailAuthArr[0];
        
        $delete = $detailAuthArr[1];
        //if($mod_name == 'fh_edit_Apply_hb' || $mod_name == 'fh_edit_Apply') $authArr[]='ShangZuLu';
        $this->assign('delete',$delete);
        $apply_id = I('aid');
        $system = I('system');
        $wxid = session('wxid');
        $uid = session($system.'_id');
        $this->assign('aid',$apply_id);
        $this->assign('wxid',$wxid);
        $this->assign('uid',$uid);
        $this->assign('mod_name',$mod_name);
        $this->assign('system',$system);

        $res = D(ucfirst($system).$mod_name, 'Logic')->recordContent($apply_id);
        $this->assign('date', date('Y-m-d'));
        $this->assign('content', $res['content']);
        $this->assign('applyer', $res['applyerName']);
        $this->assign('applyerID', $res['applyerID']);
        $this->assign('stat', $res['stat']);
        $this->assign('mydata', $res['mydata']);
        $this->assign('imgsrc', $res['imgsrc']);
        // 是否签收
        $qsRes =  M($system.'_appflowtable')->field('pro_mod')->where(array('stage_name' => '签收'))->select();
        $qsArr = array();
        foreach($qsRes as $val){
                $qsArr[] = $val['pro_mod'];
        }
        $isQs = in_array($mod_name,$qsArr)?1:0;
        $this->assign('isqs', $isQs);
        $boss = D($system.'Boss');
        $avatar = $boss->getAvatar($res['applyerID']);
        $this->assign('avatar', $avatar);

        $applyerWXID = $boss->getWXFromID($res['applyerID']);
        array_push($authArr, $applyerWXID);
        // 是否为申请人
        if ($res['applyerID'] == $uid) {
            $isApplyUser = 1;
        } else {
            $isApplyUser = 0;
        }
        $this->assign('isApplyUser', $isApplyUser);

        //审批全流程
        $allArr = D($system.'Appflowtable')->getAllProc_new($mod_name,$apply_id);
        // dump($allArr);
        $this->assign('first',$allArr['first']);
        $this->assign('title',D('seek')->getTitle($mod_name,$system));
        $this->assign('proInfo',$allArr['proInfo']);

        //审批内容
        $process = D($system.'Appflowproc');
        $procArr = $process->contentProc($mod_name, $apply_id, $authArr);
        $this->assign('process', $procArr['process']);      //审批流程
        $isSigntemp = $procArr['process'];
        $isSigntemp = end($isSigntemp);
        $isSigning  = $isSigntemp['app_name'];
        $this->assign('isSigning',$isSigning);
        $this->assign('isApplyer', $procArr['isApplyer']);
        $this->assign('isPasser', $procArr['isPasser']);
        $this->assign('isRefuse', $procArr['isRefuse']);
        $this->assign('isFlowBegin', $procArr['isFlowBegin']);
        $appStatus = $process->getWorkFlowStatus($mod_name, $apply_id);
        $this->assign('apply', $appStatus);
        $StepInfo = D(ucfirst($system).'Appflowproc')->getStepInfo($mod_name,$apply_id,session($system."_id"));
        $StepStatus = $StepInfo['app_name'];
        $this->assign('stepStatus', $StepStatus);
        $authArr = $procArr['authArr'];
        
        //评论内容
        $comment_list = D($system.'Appflowcomment')->autoMessageNumber($mod_name, $apply_id,'HuangChuiLiang');
        
        dump($comment_list);

        
        
        
    }

   
    public function arraySort($arr, $keys, $type = 'asc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v){
            $keysvalue[$k] = $v[$keys];
        }
        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
           $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
} 


