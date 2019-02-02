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
    public function __construct(){
        parent::__construct();
        $this->wx = new \Org\Util\WeChat;
    }

    // 系统自动取消 推送
    public function autoDeleteSendMessage(){
        $recevier = 'HuangShiQi';
        $title    = '系统自动取消';
        $description = 'xxxxx';
        $url = 'https://www.fjyuanxin.com';
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
        $url = 'https://www.fjyuanxin.com/WE/index.php?m=Light&c=task&a=taskLook&taskid='.$data['id'];
        $sender = session('wxid');
        $system = 'yxhb';
        $this->wx->sendCardMessage($recevier,$title,$description,$url,15,$sender,$system);
    }

    
    //  自动催审
    public function autoProSendMessage($data,$applyerName)
    {
        $system  = $data['system'];
        $modname = $data['modname'];
        $aid     = $data['aid'];
        $mod     = $data['mod'];
        $per_id  = $data['per_id']; // 当前审批人id

        // 微信发送
        $title = $modname.'(催审)';
        $url = "https://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$aid."&modname=".$mod;
        $applyerName='('.$applyerName.'提交)';
        $boss = D($system.'_boss')->getWXFromID($per_id);
        $description = "您有一个流程需要审批".$applyerName;

        $receviers = "wk|HuangShiQi|".$boss;
        $comment_list = D($system.'Appflowcomment')->autoMessageNumber($mod, $aid,$boss);
        $description .= "\n系统发起的第{$comment_list}次催审";
        $agentid = 15;
        $info = $this->wx->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod,$system);
        return $info;
    }

}