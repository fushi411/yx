<?php
namespace Light\Controller;
use Think\Controller;
class ApplyController extends BaseController {
    public function index(){
    }

    public function applyInfo(){
        // 判断依据1.管理员、副总、董事长；
        $authArr = array('wk','shh', 'csl', 'ChenBiSong','HuangShiQi');
        $mod_name = I('modname');
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
        $allArr = D($system.'Appflowtable')->getAllProc($mod_name);
        // dump($allArr);
        $this->assign('first',$allArr['first']);
        $this->assign('title',$allArr['title']);
        $this->assign('proInfo',$allArr['proInfo']);

        //审批内容
        $process = D($system.'Appflowproc');
        $procArr = $process->contentProc($mod_name, $apply_id, $authArr);
        $this->assign('process', $procArr['process']);      //审批流程
        $this->assign('isApplyer', $procArr['isApplyer']);
        $this->assign('isPasser', $procArr['isPasser']);
        $this->assign('isRefuse', $procArr['isRefuse']);
        $this->assign('isFlowBegin', $procArr['isFlowBegin']);
        $appStatus = $process->getWorkFlowStatus($mod_name, $apply_id);
        $this->assign('apply', $appStatus);
        $authArr = $procArr['authArr'];
        
        //评论内容
        $comment_list = D($system.'Appflowcomment')->contentComment($mod_name, $apply_id);
        $this->assign('comment_list', $comment_list);

        //抄送内容
        $copyTo = D($system.'Appcopyto');
        $copyArr = $copyTo->contentCopyto($mod_name, $apply_id, $authArr);
        $this->assign('readedArr',$copyArr['readedArr']);
        $this->assign('fixed_id',$copyArr['fixed_id']);
        $this->assign('already_cp', $copyArr['already_cp']);
        $this->assign('isCopyto',$copyArr['isCopyto']);
        $authArr = $copyArr['authArr'];

        //推送内容
        $copyTo = D($system.'Appcopyto');
        $copyArr = $copyTo->contentCopyto($mod_name, $apply_id, $authArr,2);
        $this->assign('readedArrPush',$copyArr['readedArr']);
        $this->assign('fixed_idPush',$copyArr['fixed_id']);
        $this->assign('already_cpPush', $copyArr['already_cp']);
        $this->assign('isCopytoPush',$copyArr['isCopyto']);
        $authArr = $copyArr['authArr'];
        // 推送
        $push = GetPush($system,$mod_name);
        $this ->assign('push',$push['data']);
        // 抄送标记为已读
        $copyTo->readCopytoApply($mod_name, $apply_id,null,1);
        // 推送标记为已读
        $copyTo->readCopytoApply($mod_name, $apply_id,null,2);

        if (!in_array($wxid, $authArr)) {
            $this->error ( '无查看权限！', U('Light/Index/index',array('system'=>$system)), 2 );
        }
        $this->display($mod_name.':'.ucfirst($system).'ApplyInfo');
    }

    // 获取当前目录
    public function getDeptHtml()
    {
        $type = I('post.type');
        if(!empty($type)){
            $this->searchDeptHtml();
        }else{
            $id = I('post.id');
            $Dept = D('department');
            $DeptInfo = $Dept->getWXDeptInfo();
            $childDeptInfo = $Dept->getChildDept($DeptInfo, $id);
            // 无子部门返回成员信息
            if (empty($childDeptInfo)) {
                $userInfo = $Dept->getWXDeptUserInfo($id);
                $childDeptHtml = $Dept->genDeptUserHtml($userInfo);
            } else {
                $childDeptHtml = $Dept->genDeptHtml($childDeptInfo);
            }
            $this->ajaxReturn($childDeptHtml);
        }
    }

    // 获取上级目录
    public function getParentDeptHtml()
    {
        $id = I('post.id');
        $Dept = D('department');
        $DeptInfo = $Dept->getWXDeptInfoOnly($id);
        $parentDeptInfo = $Dept->getWXDeptInfo($DeptInfo['parentid']);
        $childDeptInfo = $Dept->getChildDept($parentDeptInfo, $DeptInfo['parentid']);
        $childDeptHtml = $Dept->genDeptHtml($childDeptInfo);
        $this->ajaxReturn(array("pid"=>$DeptInfo['parentid'], "html"=>$childDeptHtml));
    }

    // 抄送搜索
    public function searchDeptHtml(){
        $word = I('post.search');
        $where = array(
            'stat'     => 1,
            '_complex' => array(
                '_logic' => 'or',
                'jc'     => array('like',"%{$word}%"),
                'name'   => array('like',"%{$word}%")
            )
        );

        $id = 1;
        if(!cookie('copyFlag')){
            $flagData = M('wx_info')->where(array('stat' => 1))->field('time')->find();
            // 检查过期情况
            $expire   = time()-$flagData['time'];
            if($expire-86400*30>0){ // 过期 之前的用户失效
                M('wx_info')->where(array('stat' => 1))->setField('stat',0);
                $userInfo = $this->getAllUser($id); 
                $userList = array();
                foreach( $userInfo as $val ){
                    // 简称获取
                    $length = strlen($val['id']);
                    $flag   = 1;
                    $jc    = '';
                    for($i=0;$i<$length;$i++){
                        $asc = ord($val['id'][$i]);
                        if($asc>64 && $asc<91 ){
                            $jc.=$val['id'][$i];
                            $flag = 0;
                        }
                    }
                    if($flag){
                        $jc = $val['id'];
                    }
                    // 数据插入
                    $userList[] = array(
                        'wxid'   => $val['id'],
                        'name'   => $val['name'],
                        'avatar' => $val['avatar'],
                        'stat'   => 1,
                        'time'   => time(),
                        'jc'     => $jc
                    );
                }
                M('wx_info')->addAll($userList);
                cookie('copyFlag',1);
            }
        }
        $userInfoList  = M('wx_info')->where($where)->field('wxid as id,name,avatar')->select();
        $Dept          = D('department');
        $childDeptHtml = $Dept->genDeptUserHtml($userInfoList);
        $this->ajaxReturn($childDeptHtml);
    }

    // 查询所有的用户信息
    public function getAllUser($id){
        $result = array();
        $Dept = D('department');
        $DeptInfo = $Dept->getWXDeptInfo();
        $childDeptInfo = $Dept->getChildDept($DeptInfo, $id);
        if (empty($childDeptInfo)) {
            $result = array_merge($result,$Dept->getWXDeptUserInfo($id));
        } else {
            foreach($childDeptInfo as $val){
                $result = array_merge($result,$this->getAllUser($val['id']));
            }
        }
        return $result;
    }

    // 获取搜索通讯录用户
    public function getSearchUser()
    {
        $aid      = I('post.id');
        $system   = I('post.system');
        $mod_name = I('post.mod_name');
        $per_name = session('name'); // 去除自己
        // - 申请人
        $res = D(ucfirst($system).$mod_name, 'Logic')->recordContent($aid);
        $apply_id = $res['applyerID'];
        $res = M($system.'_boss')->field('wxid')->where(array('id' => $apply_id))->find();
        $receviers = $res['wxid'].',';

        // - 流程人员
        $resArr =  M($system.'_appflowproc a')->join($system.'_boss b on b.id=a.per_id')->field('b.wxid')->where(array('a.aid' => $aid ,'a.mod_name' => $mod_name,'a.per_name'=>array('neq',$per_name)))->select();               
        foreach($resArr as $val){
            $receviers .= $val['wxid'].',';
        }

        // - 抄送人员
        $resArr = M($system.'_appcopyto')->field('copyto_id')->where(array('aid' => $aid,'mod_name' =>$mod_name,'type' => 1))->find();
        $receviers .= $reArr['copyto_id'] ;
        $recevier = str_replace(',', '|',  $receviers);
        
        // 数据重构  -- 去除重复的人员
        $tmpRecevierArr = explode('|',$recevier);  
        $tmpRecevierArr = array_filter($tmpRecevierArr); // ---- 去除空值
        $tmpRecevierArr = array_unique($tmpRecevierArr); // -- 去除重复

        // 查询数组
        $User = D($system.'_boss');
        $resArr = array();
        
        // dump($matchUser);
        foreach ($tmpRecevierArr as $key => $value) {
            $info = $User->getWXInfo($value);
            if (!empty($info)) {
                $html .= '<a class="weui-cell weui-cell_access select-comment-user" href="javascript:;" data-id="'.$info['id'].'" data-uid="'.$value['userid'].'" data-type="user" data-img="'.$info['avatar'].'" data-name="'.$info['name'].'" style="text-decoration:none;"><div class="weui-cell__hd"><img src="'.$info['avatar'].'" alt="" style="width:20px;margin-right:5px;display:block"></div><div class="weui-cell__bd"><p style="margin-bottom: 0px;">'.$info['name'].'</p></div><div class="weui-cell__ft"></div></a>';
            }
        }
        unset($value);
        // dump($resArr);
        $this->ajaxReturn(array("html"=>$html, "keywords"=>$keywords));
    }



    // 评论记录
    public function saveApplyComment()
    {
        $system = I('post.system');
        $per_id = session($system.'_id');
        $per_name = session('name');
        if (M()->autoCheckToken($_POST)){
            $data['aid'] = I('post.aid');
            $ctoid = I('post.ctoid');
            $data['comment_to_id'] = trim($ctoid,',');
            $data['mod_name'] = I('post.mod_name');
            $data['app_word'] = I('post.word');
            $data['per_id'] = $per_id;
            $data['per_name'] = $per_name;
            $data['app_stat'] = 1;
            $data['time'] = date('Y-m-d H:i:s');
            // 后期使用
            $data['reply_id'] = 0;
            
            // 发送消息提醒相关人员 
            if (!empty($data['comment_to_id'])) {
            // 发送抄送消息
                $recevier = 'wk|HuangShiQi|'.str_replace(',', '|', $data['comment_to_id']);
            }else{
            // 无@指定人 发送流程内的人
               // - 申请人，抄送人员，流程人员（不包括自己本身） #appflowproc  #copyto 
                
                // - 申请人
                $res = D(ucfirst($system).$data['mod_name'], 'Logic')->recordContent($data['aid']);
                $apply_id = $res['applyerID'];
                $res = M($system.'_boss')->field('wxid')->where(array('id' => $apply_id))->find();
                $receviers = $res['wxid'].',';

                // - 流程人员
                $resArr =  M($system.'_appflowproc a')->join($system.'_boss b on b.id=a.per_id')->field('b.wxid')->where(array('a.aid' => $data['aid'] ,'a.mod_name' => $data['mod_name'],'a.per_name'=>array('neq',$per_name)))->select();               
                foreach($resArr as $val){
                    $receviers .= $val['wxid'].',';
                }

                // - 抄送人员
                $resArr = M($system.'_appcopyto')->field('copyto_id')->where(array('aid' => $data['aid'],'mod_name' =>$data['mod_name'],'type' => 1))->find();
                $receviers .= $reArr['copyto_id'] ;
                $recevier = 'wk|HuangShiQi|'.str_replace(',', '|',  $receviers);
                
                // 数据重构  -- 去除重复的人员
                $tmpRecevierArr = explode('|',$recevier);  
                $tmpRecevierArr = array_filter($tmpRecevierArr); // ---- 去除空值
                $tmpRecevierArr = array_unique($tmpRecevierArr); // -- 去除重复
                $tmpRecevier = implode(',',$tmpRecevierArr);

                $temrecevier = str_replace('|',',',$tmpRecevier);
                $data['comment_to_id'] = $tmpRecevier;
                $data['app_word'].='@所有人';
            }
            // - 发送信息
            $flowTable = M($system.'_appflowtable');
            $mod_cname = $flowTable->getFieldByProMod($data['mod_name'], 'pro_name');
            $title = $mod_cname;
            $description = "您有新的评论：".$per_name."@了你!";
            $url = "http://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$data['aid']."&modname=".$data['mod_name'];
            $WeChat = new \Org\Util\WeChat;
            $WeChat->sendCardMessage($recevier,$title,$description,$url,15,$data['mod_name'],$system);
            // - 数据插入

            $res = M($system.'_appflowcomment')->add($data);
            $this->ajaxReturn($res);
        }
        $this->ajaxReturn('error');
    }

    public function delCommentRecord()
    {
        $id = I('post.id');
        $system = I('post.system');
        if (!empty($id)) {
            M($system.'_appflowcomment')->where(array('id'=>$id))->setField('app_stat', 0);
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('failure');
        }
    }

    public function applyChange() {
        $wxid = session('wxid');
        $aid = I('aid');
        $mod_name = I('mod_name');
        $system = I('system');
        $uid = session($system.'_id');

        $resArr = M($system.'_appflowproc')->field(true)->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'per_id'=>$uid, 'app_stat'=>0))->find();
        $isApplyer = 0;
        if ($resArr['app_stat']==0) {
            $isApplyer = 1;
        }
        // $apply = M('applyseal')->field('applicantid')->find($apply_id);
        $this->assign('aid', $aid);
        $this->assign('mod_name', $mod_name);
        $this->assign('system', $system);
        $this->assign('isApplyer', $isApplyer);
        $this->assign('apply_uid', $apply['applicantid']);
        $this->assign('res', $resArr);
        $this->display();
    }

    public function saveApplyChange()
    {
        if (!M()->autoCheckToken($_POST)){
            $this->error('请勿重复提交！');
        } else {
            $pro_id = I('post.pro_id');
            $aid = I('post.aid');
            $mod_name = I('post.mod_name');
            $system = I('post.system');
            $stage_id = I('post.app_stage');
            $changeto_id = I('post.changeto_id');
            $boss = D($system.'_boss');
            $others_id = $boss->getIDFromWX($changeto_id);
            $reason = I('post.reason');
            $applyUserid = I('post.apply_uid');
            $wf = new WorkFlowController();
            $wf->setOthersApply($mod_name, $pro_id, $aid, $stage_id, $others_id, $reason, $applyUserid,$system);
            $this->success('提交成功',U('Light/Apply/applyInfo',array('modname'=>$mod_name,'aid'=>$aid,'system'=>$system)));
        }
    }

    public function delRecord()
    {

        $id = I('post.id');
        $mod_name = I('post.mod_name');
        $system = I('post.system');
        if ($id) {
            $res = D(ucfirst($system).$mod_name, 'Logic')->delRecord($id);
            $wf=new WorkFlowController();
            $wf->workFlowSVReset($mod_name,$id,$system);
            $this->ajaxReturn('success');
        } else {
            
            $this->ajaxReturn('failure');
        }
    }

    public function forTest()
    {
        header("Content-type:text/html;charset=utf-8");
        $system = 'kk';
        $apply_id=668;
        $mod_name = 'CgfkApply';
        $authArr = array();
        $process = D($system.'Appflowproc');
        $procArr = $process->contentProc($mod_name, $apply_id, $authArr);
        dump($procArr);
        $this->assign('process', $procArr['process']);      //审批流程
        $this->assign('isApplyer', $procArr['isApplyer']);
        $this->assign('isPasser', $procArr['isPasser']);
        $this->assign('isRefuse', $procArr['isRefuse']);
        $this->assign('isFlowBegin', $procArr['isFlowBegin']);
        $appStatus = $process->getWorkFlowStatus($mod_name, $apply_id);
        $this->assign('apply', $appStatus);
       
    }
// ---END---
}