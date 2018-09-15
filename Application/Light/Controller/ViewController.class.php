<?php
namespace Light\Controller;
use Think\Controller;

class ViewController extends BaseController
{
    private $today = null;
    private $PageArr = '';
    private $user = '';

    public function __construct(){
        parent::__construct();
        $this->today = date('Y-m-d',time());
        $model = D('Msgdata');
        $mod_name = I('modname');
        $viewtype = I('viewtype');
        $this->PageArr = $model->GetMessage($mod_name,$viewtype);
        $this->user = session('name');
    }

    public function view(){
        // 系统和模块决定
        $mod_name = I('modname');
        $system = I('system');
        $viewtype = I('viewtype');
        // 审批
        $appflow = GetAppFlow($system,$mod_name);
        // 推送
        $push = GetPush($system,$mod_name);
        
        $this -> assign('system', $system);
        $this -> assign('modname', $mod_name);
        $this -> assign('push',$push['data']);
        
        $appflow = $this->appflowJson($appflow,$mod_name);
        $this -> assign('appflow',$appflow);
        $this -> assign('info',$this->PageArr);

        $this -> assign('fixed',$this->PageArr[$viewtype.$system.$mod_name]);

        $this -> assign('today',$this->today);

        $this -> assign('title',$this->PageArr['title']);
        
        $this -> display($mod_name.'/'.ucfirst($system).$viewtype.'View');

    }

    /**
     * 审批流程转化成json格式字符串
     * @param  array   $appflow 审批数组
     * @param  string  $mod     模块名字
     * @return array:string  $result 
     */
    public function appflowJson($appflow,$mod){
        $modArr = array('TempCreditLineApply','fh_edit_Apply_hb','fh_edit_Apply');
        return in_array($mod,$modArr) ? json_encode($appflow) : $appflow;
    }

}