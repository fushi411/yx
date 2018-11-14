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
}