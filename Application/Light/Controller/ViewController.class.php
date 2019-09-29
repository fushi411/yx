<?php
namespace Light\Controller;
use Think\Controller;

class ViewController  extends BaseController
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
        // 个别特殊模块页面
        $special_page = I('get.special');
        // 系统和模块决定
        $mod_name = I('modname');
        $system   = I('system');
        $viewtype = I('viewtype');
        // 审批
        $appflow  = GetAppFlow($system,$mod_name);
        // 推送
        $push       = GetPush($system,$mod_name);
        $detailModel= D('YxDetailAuth');
        $detailAuth = $detailModel->CueAuthCheck();
        $atten      = $detailModel->ActiveAttention($system,$mod_name);
        $explain    = $detailModel->ActiveExplain($system,$mod_name);

        //签收人员
        $list =  M('yxhb_user_deploy')->where(array('modname'=>$mod_name, 'system'=>$system, 'status'=>0))->select();
        $this->assign('list',$list);

        // 是否签收
        $qsInfo =  M('yx_config_title')->field('stat')->where(array('name' => $mod_name, 'mod_system'=>$system))->find();
        $isQs = 0;
        if (!empty($qsInfo) && $qsInfo['stat'] == 3) {
            $isQs = 1;
        }
        $this -> assign('isQs', $isQs);

        // 无审批流程 提示 签收模式排除
        $flag = empty($appflow)?'true':'false';
        $flag = $this->isSign($system,$mod_name)?$flag:'false';
        $this -> assign('noAppProHtml', D('Html')->noAppProHtml());
        $this -> assign('flag', $flag);
        $this -> assign('system', $system);
        $this -> assign('modname', $mod_name);
        $this -> assign('push',$push['data']);

        $appflow = $this->appflowJson($appflow,$mod_name);
        $this -> assign('appflow',$appflow);
        $this -> assign('info',$this->PageArr);
        $this -> assign('fixed',$this->PageArr[$viewtype.$system.$mod_name]);
        $this -> assign('today',$this->today);
        $this -> assign('title',$this->PageArr['title']);
        $this -> assign('atten',$atten);
        $this -> assign('explain',$explain);
        $this -> assign('CueConfig',$detailAuth);
        $this -> assign('kp_sda', date('Y-m-01',time()+8*3600));
        $this -> assign('kp_eda', date('Y-m-d',time()+8*3600));
        if( $special_page){
            $this->display($mod_name.'/'.$special_page);
        }else{
            $this -> display($mod_name.'/'.ucfirst($system).$viewtype.'View');
        }
    }
     
    // 签收判断
    public function isSign($system,$mod){
        if($mod == 'CostMoneyPay') return false;
        $map = array(
            'name'       => $mod,
            'mod_system' => $system,
            'stat'       => 3,
        );
        $data = M('yx_config_title')->where($map)->find();
        return empty($data)?true:false;
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

    // 提示页面
    public function cue(){
        $id = I('get.cueid');
        $this->display('Cue/index');
    }


}