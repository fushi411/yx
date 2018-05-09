<?php
namespace Light\Controller;

class WorkFlowOpTvController extends BaseController {

    public function WorkFlowSubmit(){
    	$wf     = new WorkFlowController();
    	$mod_name   = I('post.mod_name');
    	$system     = I('post.system');
    	$DB_name    = D(ucfirst($system).$mod_name, 'Logic')->getTableName();			
	    $id         = I('post.id');
		$pid        = session($system.'_id');
		$copyto_id  = I('post.copyto_id');
		$option     = I('post.option');
		$word       = I('post.word');
		$apply_user = I('post.apply_user');
		$time       = date("Y-m-d H:i:s",time());
		// dump($mod_name);exit();
		if (!M()->autoCheckToken($_POST)){
			$arr[] = array("optiontype"=>'令牌验证失败！');
			$this -> ajaxReturn($arr);
		}
    	// if (isWorkFlowUnique($mod_name,$id,$pid,$option,$word)) {
			$wfStatus = $wf->nextWorkFlowTH($mod_name,$id,$pid,$option,$word,$apply_user,$system);
    	// }
		if($option==1){
			$optionType = '审批拒绝';
		}else{
			$optionType = '审批通过';
		}

		// 抄送消息
		$copyto_id = trim($copyto_id,',');
		if (!empty($copyto_id)) {
			// 发送抄送消息
			D($system.'Appcopyto')->copyTo($copyto_id, $mod_name, $id);
		}
		// 调用审批后处理方法
		// 同理可处理开始审批、过程中、拒绝后调用方法
	    if (!empty($wfStatus)&&$wfStatus['status']=='end'&&$wfStatus['option']==2) {
	        $wfClass = new WorkFlowFuncController();
	        $func = ucfirst($system).$mod_name.'End';
			$funcRes = $wfClass->$func($id, $system);
			// 用户推送
			$res = M($system.'_appflowtable')->field('condition')->where(array('pro_mod'=>$mod_name.'_push'))->find();
			if(!empty($res)){
				$pushArr = json_decode($res['condition'],true);
				$push_id = $pushArr['push'];
				
				$del_arr = M($system.'_appflowproc a')->join($system.'_boss b on b.id=a.per_id')->field('b.wxid')->where(array('a.aid' => $id,'a.mod_name' => $mod_name))->order('a.app_stage desc')->find();
				$del_id = $del_arr['wxid'];
				$res = array_search($del_id,$push_id);
				if($res !== false){
					$push_id = str_replace($del_id,'',$push_id);
					$push_id = explode(',',$push_id);
					$push_id = implode(',',array_filter($push_id));
				}
				D($system.'Appcopyto')->copyTo($push_id, $mod_name, $id,2);
			}
	    }

		$arr[] = array("optiontype"=>$optionType, "wfStatus"=>$wfStatus);
		//echo $optionType;
		$this -> ajaxReturn($arr);
	}

	/*审批请求是否唯一
	  @ mod_name 审批流程名称
	  @ id 审批ID
	  @ pid 审批人ID
	  @ option 审批人是否通过
	  @ word 审批意见
	 */

	public function isWorkFlowUnique($mod_name,$id,$pid,$option,$word)
	{
	  // $sql=new dedesql(false);
	  // $query="SELECT 1 FROM #@__appflowproc WHERE aid='{$id}' and mod_name='{$mod_name}' and per_id='{$pid}' and app_stat='{$option}' and app_word='{$word}'";
	  // // 后续可增加1分钟内不得提交重复记录的判断
	  // $count=$sql->GetTotalRow($query);
	  // if ($count==-1) return true;
	  // else return false;
	  // $sql->close();
	}
}
?>