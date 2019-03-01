<?php
namespace Light\Model;
use Think\Model;

/**
 * 企微发送信息
 * @author 
 */

class WxMessageModel extends Model {
    // 虚拟模型
    protected $autoCheckFields = false;
    protected $wx = '';
    protected $url = 'https://www.fjyuanxin.com';
    protected $mUrl = 'https://www.fjyuanxin.com/WE/index.php?';
    
    public function __construct(){
        parent::__construct();
        $this->wx = new \Org\Util\WeChat;
    }

    // 系统自动取消 推送
    public function autoDeleteSendMessage(){
        $recevier = 'HuangShiQi';
        $title    = '系统自动取消';
        $description = 'xxxxx';
        $url = $this->url;
        $sender = 'system';
        $system = 'yxhb';
        $this->wx->sendCardMessage($recevier,$title,$description,$url,15,$sender,$system);
    }

    /**
     * 任务排单
     * @param $id 任务id
     */
    public function taskSendMessage($id){
        $map = array(
            'id' => $id
        );
        $data = M('yx_task')->where($map)->find();
        $recevier = str_replace(',','|',$data['part']);
        $title = '任务排单';
        $description = $data['tjr'].'向您派发了一条任务';
        $url = $this->mUrl.'m=Light&c=task&a=taskLook&taskid='.$data['id'];
        $sender = session('wxid');
        $system = 'yxhb';
        $this->wx->sendCardMessage($recevier,$title,$description,$url,15,$sender,$system);
    }

    
    //  自动催审 - 小于3条待审批
    public function autoProSendMessage($data,$applyerName)
    {
        $system  = $data['system'];
        $modname = $data['modname'];
        $aid     = $data['aid'];
        $mod     = $data['mod'];
        $per_id  = $data['per_id']; // 当前审批人id
        
        // 微信发送
        $title = $modname.'(催审)';
        $url = $this->mUrl."m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$aid."&modname=".$mod;
        $applyerName='('.$applyerName.'提交)';
        $boss = D($system.'_boss')->getWXFromID($per_id);
        $description = "您有一个流程需要审批".$applyerName;

        $receviers = $this->getFiexMan('|');
        $receviers.= $boss;
        $comment_list = D($system.'Appflowcomment')->autoMessageNumber($mod, $aid,$boss);
        $description .= "\n系统发起的第{$comment_list}次催审";
        $agentid = $mod == 'CostMoney'?1000049:15;
        $info = $this->wx->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod,$system);
        return $info;
    }

    // 自动催审 - 超3条待审批
    public function autoProMoreSendMessage($data,$num)
    {
        $system  = $data['system'];
        $mod     = $data['mod'];
        $per_id  = $data['per_id']; // 当前审批人id
        // 微信发送
        $title = '(自动催审)';
        $url = $this->mUrl."m=Light&c=Seek&a=myApprove&system=kk";
        if($mod == 'CostMoney') $url = $this->mUrl.'m=Light&c=Seek&a=myApprove&cost=1&system=yxhb';
        $boss = D($system.'_boss')->getWXFromID($per_id);
        $description = "您有{$num['num']}个流程需要审批";
        foreach($num['data'] as $val){
            $description .="\n{$val['name']}:{$val['num']}次";
        }
        $receviers = $this->getFiexMan('|');
        $receviers.= $boss;
        $agentid = $mod == 'CostMoney'?1000049:15;
        $info = $this->wx->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod,$system);
        return $info;
    }
    // 撤销通知 - 撤销推送，排除本人
    public function delRecordSendMessage($system,$mod,$id,$reason){
        $temrecevier = $this->getAllCurrentProcessPeople($system,$mod,$id,1);
        // - 申请人员
        $res = D(ucfirst($system).$mod, 'Logic')->recordContent($id);
        $apply_user = $res['applyerName'];
        // - 模块名
        $modName     = D('Seek')->getModname($mod,$system);
        $title       = '【已撤销推送】';
        $description = $modName."({$apply_user}提交)\n撤销理由：".$reason;
        $url         = $this->mUrl."m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$mod;
        # 信息发送
        $this->wx->sendCardMessage($temrecevier,$title,$description,$url,15,$mod,$system);
        return $temrecevier;
    }

    // - 评论通知 
    public function commentSendMessage($system,$mod,$id,$copyid){
        $per_name    = session('name');
        # 流程所有人员
        $recevier = $this->getFiexMan('|');
        $recevier.= str_replace(',', '|', $ctoid);
        if(empty($copyid)) $recevier    = $this->getAllCurrentProcessPeople($system,$mod,$id,0);
        // - 模块名
        $title       = D('Seek')->getModname($mod,$system);
        $description = "您有新的评论：".$per_name."@了你!";
        $url         = $this->mUrl."m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$mod;
        # 信息发送
        $this->wx->sendCardMessage($recevier,$title,$description,$url,15,$mod,$system);
        return $recevier;
    }

    // - 手动催审推送
    public function urgeSendMessage($system,$mod,$id,$per_id,$reason){
        # 推送人员
        $boss           = D(ucfirst($system).'Boss');
        $wxid           = $boss->getWXFromID($per_id);
        $receviers      = $this->getFiexMan('|');
        $receviers     .= $boss;
        $tmpRecevierArr = explode('|',$receviers);  
        $tmpRecevierArr = array_filter($tmpRecevierArr); // ---- 去除空值
        $tmpRecevierArr = array_unique($tmpRecevierArr); // -- 去除重复
        $receviers      = implode('|',$tmpRecevierArr);
        # title
        $title  = D('Seek')->getModname($mod,$system);
        $title .= '(催审)'; 
        # contents
        $applyerName = '('.session('name').'提交)';
        $description = "您有一个流程需要审批".$applyerName;
        if(!empty($reason)) $description .= "\n催审理由：".$reason;
        # url
        $url  = $this->mUrl."m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$mod;
        $agentid = 15;
        # 信息发送
        $this->wx->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod,$system);
        return $receviers;
    }

    // - 流程推送 文字推送
    public function ProSendMessage($system,$mod,$id,$per_id,$applyerid,$type){
        # 推送人员
        $seek = D('Seek');
        list($receviers,$description) = $this->getProPeople($system,$mod,$id,$per_id,$applyerid,$type);
        $systemname = $seek->getConfig($mod,$system,'system');
        $name       = $seek->getTitle($mod,$system);
        $url        = $this->mUrl."m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$mod;
        $logic      = D(ucfirst($system).$mod, 'Logic');
        $content    = $logic->getDescription($id);
        $template   = $this->ReDescription($content);
        $template   = "{$description}\n申请单位：{$systemname}\n申请类型：{$name}\n{$template}<a href='{$url}'>点击查看</a>";
        $agentid = 15;
        $this->wx->sendMessage($receviers,$template,$agentid,$system);
        return $receviers;
    }

    // - 流程推送 卡片推送
    public function ProSendCarMessage($system,$mod,$id,$per_id,$applyerid,$type){
         # title
        $seek  = D('Seek');
        $stat  = $seek->getConfig($mod,$system,'stat');
        $title = $stat==3?'签收':'审批'; 
        $top   = $seek->getModname($mod,$system);
        $title = "{$top}({$title})";
        # 推送人员
        list($receviers,$description) = $this->getProPeople($system,$mod,$id,$per_id,$applyerid,$type);
        // - 申请人员
        $logic       = D(ucfirst($system).$mod, 'Logic');
        $res         = $logic->recordContent($id);
        $apply_user  = $res['applyerName'];
        $description.= "({$apply_user}提交)\n";
        $content     = $logic->sealNeedContent($id);
        $template    = $this->CarReDescription($content);
        $description.= $template;
        # url 
        $url     = $this->mUrl."m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$mod;
        $agentid = $mod == 'CostMoney'?1000049:15;
        # 信息发送
        $this->wx->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod,$system);
        return $receviers;
    }

    // 抄送推送
    public function copyTo($system,$mod,$id,$copyid){
        # 推送人员
        $boss           = D(ucfirst($system).'Boss');
        $receviers      = $this->getFiexMan('|');
        $receviers     .= $boss;
        $tmpRecevierArr = explode('|',$receviers);  
        $tmpRecevierArr = array_filter($tmpRecevierArr); // ---- 去除空值
        $tmpRecevierArr = array_unique($tmpRecevierArr); // -- 去除重复
        $receviers      = implode('|',$tmpRecevierArr);
        # title
        $title  = D('Seek')->getModname($mod,$system);
        $title .= '(催审)'; 
        # contents
        $applyerName = '('.session('name').'提交)';
        $description = "您有一个流程需要审批".$applyerName;
        if(!empty($reason)) $description .= "\n催审理由：".$reason;
        # url
        $url  = $this->mUrl."m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$mod;
        $agentid = 15;
        # 信息发送
        $this->wx->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod,$system);
        return $receviers;
    }

    // 退审推送
    public function refuseMsg($system,$mod,$id,$word){
        $apply_user  = $res = D(ucfirst($system).$mod, 'Logic')->recordContent($id);
        $apply_user  = $res['applyerName'];

        $title       = '【已退审推送】';
        $mod_cname   = D('Seek')->getModname($mod,$system);
		$description = $mod_cname."({$apply_user}提交)\n退审意见：".$word;
        $url         = "https://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$mod;
        $temrecevier = $this->getAllCurrentProcessPeople($system,$mod,$id,1);
        $this->wx->sendCardMessage($temrecevier,$title,$description,$url,15,$mod,$system);
    }

    // 人员获取 - 流程推送
    public function getProPeople($system,$mod,$id,$per_id,$applyerid,$type){
        $description = '您有一个流程';
        $receviers   = $this->getFiexMan('|');

        $boss        = D(ucfirst($system).'Boss');
        $applyerwxid = $boss->getWXFromID($applyerid);
        $prowxid     = $type == 'QS'?$per_id:$boss->getWXFromID($per_id);
        
        $seek  = D('Seek');
        $stat  = $seek->getConfig($mod,$system,'stat');
        $title = $stat == 3?'签收':'审批'; 

        switch ($type) {
            case 'pass':
              $description .= "已{$title}通过";
              $receviers   .= $applyerwxid;
              break;
            case 'refuse':
              $description .= "被拒绝";
              $receviers   .= $applyerwxid;
              break;
            case 'other':
              $description .= "需要处理";
              $receviers   .= $prowxid;
              break;           
            default:
              $description .= "需要{$title}";
              $receviers   .= $prowxid;
              break;
        }
        # 推送人员处理
        $tmpRecevierArr = explode('|',$receviers);  
        $tmpRecevierArr = array_filter($tmpRecevierArr); // ---- 去除空值
        $tmpRecevierArr = array_unique($tmpRecevierArr); // -- 去除重复
        $receviers      = implode('|',$tmpRecevierArr);
        return array($receviers,$description);
    }
    /**
     * 人员获取 - 目前所有的流程人
     * @param string $system 系统
     * @param string $mod  模块名
     * @param string || int $id aid
     * @param string $isDel  是否撤销 1-是 0-否
     */
    public function getAllCurrentProcessPeople($system,$mod,$id,$isDel,$need=''){
        $receviers   = $need=='No'?'': $this->getFiexMan(',');
        // - 申请人
        if(!$isDel){
            $res       = D(ucfirst($system).$mod, 'Logic')->recordContent($id);
            $wxid      = D(ucfirst($system).'Boss')->getWXFromID($res['applyerID']);
            $receviers .= $wxid.','; 
        }
        // - 流程人员
        $authArr = array();
        $pro     = D($system.'Appflowproc')->contentProc($mod, $id, $authArr); 
        $authArr = $pro['authArr'];
        // - 抄送人员
        $copyTo  = D($system.'Appcopyto');
        $copyArr = $copyTo->contentCopyto($mod, $id, $authArr); 
        $authArr = $copyArr['authArr'];
        // 推送人员 - 撤销不通知推送人员
        if(!$isDel){
            $copyArr = $copyTo->contentCopyto($mod_name, $apply_id, $authArr,2);
            $authArr = $copyArr['authArr'];
        }
        // 数据重构  - 去除重复的人员,以及自身
        foreach($authArr as $val){
            $receviers .= $val.',';
        }
        $wxid = session('wxid');
        $arr  = $this->getFiexMan();
        if(!in_array($wxid,$arr)) $receviers = str_replace(session('wxid'),'',  $receviers);
        $recevier  = str_replace(',', '|',  $receviers);

        $tmpRecevierArr = explode('|',$recevier);  
        $tmpRecevierArr = array_filter($tmpRecevierArr); // ---- 去除空值
        $tmpRecevierArr = array_unique($tmpRecevierArr); // -- 去除重复
        $temrecevier    = implode('|',$tmpRecevierArr);
        return $temrecevier;
    }

    /**
     *  根据返回值，重组字符串
     * @param  array $data 重组数组
     * @return string       description
     */
    public function ReDescription($data){
        $description = '';
        foreach($data as $k =>$v){
          $description.=$v['name'].$v['value']."\n";
        }
        return $description;
    }

    /**
     *  流程推送-卡片推送 根据返回值，重组字符串
     * @param  array $data 重组数组
     * @return string       description
     */
    public function CarReDescription($data){
        $arr = array(
            'first_title'  => 'first_content',
            'second_title' => 'second_content',
            'third_title'  => 'third_content',
            'fourth_title' => 'fourth_content'
        );
        $description = '';
        foreach($arr as $k => $v){
            if(empty($data[$k])) continue;
            $description .= "{$data[$k]}：{$data[$v]}\n";
        }
        return $description;
    }

    // 获取固定推送人员
    public function getFiexMan($glue){
        $FiexMan = array('HuangShiQi','wk','WangTongJin');
        if(empty($glue)) return $FiexMan;
        return implode($glue,$FiexMan).$glue;
    }

}