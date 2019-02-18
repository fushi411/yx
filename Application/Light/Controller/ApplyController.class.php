<?php
namespace Light\Controller;
use Think\Controller;
class ApplyController extends BaseController {
    public function index(){
    }

    public function applyInfo(){
        // 判断依据1.管理员、副总、董事长；
        //$authArr = array('wk','shh', 'csl', 'ChenBiSong','HuangShiQi');
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
        // 注意事项配置
        $detailAuth = D('YxDetailAuth')->CueAuthCheck();
        $atten      = D('YxDetailAuth')->ActiveAttention($system,$mod_name);
        $this -> assign('atten',$atten);
        $this -> assign('CueConfig',$detailAuth);
        
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
        $userInfoList  = M('wx_info')->where($where)->field('wxid as id,name,avatar')->group('wxid')->select();
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
        // 流程
        $recevier = D('WxMessage')->getAllCurrentProcessPeople($system,$mod_name,$aid,0,'No');
        // 数据重构  -- 去除重复的人员
        $tmpRecevierArr = explode('|',$recevier);  
        $tmpRecevierArr = array_unique($tmpRecevierArr); // -- 去除重复
        $tmpRecevierArr = array_filter($tmpRecevierArr); // ---- 去除空值

        // 查询数组
        $User = D($system.'_boss');
        $resArr = array();
        
        foreach ($tmpRecevierArr as $key => $value) {
            $info = $User->getWXInfo($value);
            if (!empty($info)) {
                $avatar =$info['avatar']?$info['avatar']:'Public/assets/i/defaul.png';
                $html .= '<a class="weui-cell weui-cell_access select-comment-user" href="javascript:;" data-id="'.$info['id'].'" data-uid="'.$value['userid'].'" data-type="user" data-img="'.$info['avatar'].'" data-name="'.$info['name'].'" style="text-decoration:none;"><div class="weui-cell__hd"><img src="'.$avatar.'" alt="" style="width:20px;margin-right:5px;display:block"></div><div class="weui-cell__bd"><p style="margin-bottom: 0px;">'.$info['name'].'</p></div><div class="weui-cell__ft"></div></a>';
            }
        }
        
        $this->ajaxReturn(array("html"=>$html, "keywords"=>$keywords));
    }

  

    // 评论记录
    public function saveApplyComment()
    {
        $system   = I('post.system');
        $aid      = I('post.aid');
        $ctoid    = I('post.ctoid');
        $image    = I('post.file');
        $mod      = I('post.mod_name');
        $word     = I('post.word');
        $per_id   = session($system.'_id');
        $per_name = session('name');
        # 重复提交
        if (!M()->autoCheckToken($_POST)) $this->ajaxReturn('error');
        # 数据处理
        $ctoid = explode(',',$ctoid);
        $ctoid = array_filter($ctoid);
        $ctoid = implode(',',$ctoid);

        $data['aid']           = $aid;
        $data['comment_to_id'] = $ctoid;
        $data['mod_name']      = $mod;
        $data['app_word']      = $word;
        $data['per_id']        = $per_id;
        $data['per_name']      = $per_name;
        $data['app_stat']      = 1;
        $data['comment_img']   = $image;
        $data['time']          = date('Y-m-d H:i:s');
        $data['reply_id']      = 0;
        
        // 发送消息提醒相关人员 
        $temrecevier           = D('WxMessage')->commentSendMessage($system,$mod,$aid,$ctoid);
        if (empty($ctoid)) {
            $temrecevier           = str_replace('|',',',$temrecevier);
            $data['comment_to_id'] = $temrecevier;
            $data['app_word']     .= '@所有人';
        }
       
        // - 数据插入
        $res = M($system.'_appflowcomment')->add($data);
        $this->ajaxReturn($res); 
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

    // 撤销申请
    public function delRecord()
    {
        $system      =  I('post.system');
        $id          =  I('post.id');
        $mod_name    =  I('post.mod_name');
        $reason      =  I('post.reason');
        if (!$id) $this->ajaxReturn('failure');
        # 撤销处理
        $res = D(ucfirst($system).$mod_name, 'Logic')->delRecord($id);
        # 信息发送以及保存
        $receviers             = D('WxMessage')->delRecordSendMessage($system,$mod_name,$id,$reason);
        $receviers             = str_replace('|',',',$receviers);
        $data['aid']           = $id;
        $data['comment_to_id'] = $receviers;
        $data['mod_name']      = $mod_name;
        $data['per_id']        = 8888;
        $data['per_name']      = '系统撤销提醒';
        $data['app_word']      = "此流程已被".session('name')."撤销<br/>撤销理由：".$reason;
        $data['app_stat']      = 1;
        $data['time']          = date('Y-m-d H:i:s');
        $commentRes            = M($system.'_appflowcomment')->add($data);

        $wf=new WorkFlowController();
        $wf->workFlowSVReset($mod_name,$id,$system);
        $this->ajaxReturn('success');
    }

    // 审核撤回
    public function delProc(){
        $id = I('post.id');
        $mod_name = I('post.mod_name');
        $system = I('post.system');
        if ($id) {
            $wf=new WorkFlowController();
            $res = $wf->workFlowPrcReset($mod_name,$id,$system);
            $this->ajaxReturn($res);
        } else {
            $this->ajaxReturn('failure');
        }
    }

    /*
    * 评论时多图上传
    */
    public function commentUploadBaseImg() {
        $uploader = new \Think\Upload\Driver\Local;
        if(!$uploader){
            E("不存在上传驱动");
        }
        // 生成子目录名
        $savePath = date('Y-m-d')."/";

        // 生成文件名
        $img_str = I('post.imagefile');
        $order = I('post.order');
        $img_header = substr($img_str, 0, 23);
        // echo $img_header;exit();
        if (strpos($img_header, 'png')) {
            $output_file = uniqid('comment_').'_'.$order.'.png';
        }else{
            $output_file = uniqid('comment_').'_'.$order.'.jpg';
        }
        //  $base_img是获取到前端传递的src里面的值，也就是我们的数据流文件
        $base_img = I('post.imagefile');
        if (strpos($img_header, 'png')) {
            $base_img = str_replace('data:image/png;base64,', '', $base_img);
        }else{
            $base_img = str_replace('data:image/jpeg;base64,', '', $base_img);
        }

        //  设置文件路径和文件前缀名称
        $rootPath = "/www/web/default/WE/Public/upload/html5uploads/";
        /* 检测上传根目录 */
        if(!$uploader->checkRootPath($rootPath)){
            $error = $uploader->getError();
            return false;
        }
        /* 检查上传目录 */
        if(!$uploader->checkSavePath($savePath)){
            $error = $uploader->getError();
            return false;
        }
        $path = $rootPath.$savePath.$output_file;
        //  创建将数据流文件写入我们创建的文件内容中
        file_put_contents($path, base64_decode($base_img));
        $val['path'] = $path;
        $val['output_file'] = $savePath.$output_file;
        $res[] = $val;
        $this->ajaxReturn($res);
    }
    
    /*
    * 评论时多图上传
    */
    public function upImage() {
        $uploader = new \Think\Upload\Driver\Local;
        if(!$uploader){
            E("不存在上传驱动");
        }
        // 生成子目录名
        $savePath = date('Y-m-d')."/";

        // 生成文件名
        $img_str = I('post.img');
        $img_header = substr($img_str, 0, 23);
        // echo $img_header;exit();
        if (strpos($img_header, 'png')) {
            $output_file = uniqid('comment_').'_'.$order.'.png';
        }else{
            $output_file = uniqid('comment_').'_'.$order.'.jpg';
        }
        //  $base_img是获取到前端传递的src里面的值，也就是我们的数据流文件
        $base_img = I('post.img');
        if (strpos($img_header, 'png')) {
            $base_img = str_replace('data:image/png;base64,', '', $base_img);
        }else{
            $base_img = str_replace('data:image/jpeg;base64,', '', $base_img);
        }

        //  设置文件路径和文件前缀名称
        $rootPath = "/www/web/default/WE/Public/upload/sign/";
        /* 检测上传根目录 */
        if(!$uploader->checkRootPath($rootPath)){
            $error = $uploader->getError();
            return false;
        }
        /* 检查上传目录 */
        if(!$uploader->checkSavePath($savePath)){
            $error = $uploader->getError();
            return false;
        }
        $path = $rootPath.$savePath.$output_file;
        //  创建将数据流文件写入我们创建的文件内容中
        file_put_contents($path, base64_decode($base_img));
        $val['path'] = $path;
        $val['output_file'] = $savePath.$output_file;
        $this->ajaxReturn( array('code' => 200,'data' => $val));
    }

// ---END---
}
