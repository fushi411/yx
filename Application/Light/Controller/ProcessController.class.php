<?php
namespace Light\Controller;
use Think\Controller;
class ProcessController extends Controller
{
    public function __construct(){
        parent::__construct();
        $isLogin = is_login();
        if(!$isLogin)  $this->error ( '登录过期，自动重新登录！', U('Light/Login/index'), 0 );
    }
    /**
     * 流程审批详情页面
     * @param string $pro_mod 流程名 
     */
    public function ApplyProcess(){
        $pro_mod = I('modname');
        // $pro_mod 为空的情况
        $system = I('system');
        if(!$system)$system='yxhb';
        if($pro_mod == '') die;

        $proData = GetAppFlow($system,$pro_mod);
        $temp['proName'] = str_replace('表','',$proData[0]['pro_name']); 

        // 特殊页面显示 （临时额度） 目前只有临时页面特殊，后期可能修改
        switch($pro_mod){
            case 'TempCreditLineApply': 
                        $func = 'TempCreditLineApply'; 
                break;
            default:
                        $func = 'getApplyProcess';
        }
        $temp['data'] = $this->$func($proData);
        $authGroup =  $this->getAuthGroup($system,$pro_mod);
        $this->assign('group',$authGroup);
        $this->assign('data',$proData);
        $this->assign('show',$temp);
      
        $this->display('YxhbProcess/ApplyProcess');
    }

    /**
     * 获取权限组
     * @param string $type 类型
     * @return array  权限组及其成员
     */
    private function getAuthGroup($system,$type=''){
        $reArr = array(
            'group'   => '暂无',
            'leaguer' => '暂无'
        );

        $res = M('auth_rule')->field('id')->where(array('title' => array('like',$system.$type.'|%')))->find();
        if(!$res) return $reArr; // ---都无权限
        
        $group = M('auth_group')->field('id,title')->where(array('rules' => array('like',"%{$res['id']}%")))->select();
        if(empty($group)) return $reArr;  // ---无部门

        $groupStr = '';
        $where = '';
        $leaguerStr = '';
        foreach ($group as $key => $value) {
            if($key != 0) $where.=' or ';
            $where .= 'group_id = '.$value['id']; 
            $groupStr .= $value['title'].' ';
        }
        $reArr['group'] = $groupStr;

        $leaguer = M('auth_group_access a')
                    ->field('b.name')
                    ->join($system.'_boss b on a.uid=b.wxid')
                    ->where($where)
                    ->group('a.uid')
                    ->select();
        if(empty($leaguer))return $reArr;

        foreach($leaguer as $k=>$v){
            $leaguerStr .= $v['name'].' ';
        }
        $reArr['leaguer'] = $leaguerStr;
        return $reArr;
    }



    /**
     * 临时额度审批流程数据
     * @param array  流程数据
     */
    private function TempCreditLineApply($proData){
        $temp = array();
        $temp[] = array(
            'title' => '二万额度审批流程',
            'count' => -1,
            'msg'   => '无需审批',
        );
        $temp[] = array(
            'title' => '五万额度审批流程',
            'count' => 0
        );
        $temp[] = array(
            'title' => '十万额度审批流程',
            'count' => 1
        );
        return $temp;
    }


    /**
     * 其他审批流程
     * @param array  流程数据
     */
    private function getApplyProcess($proData){
        $temp = array();
        $temp[] = array(
            'title' => $proData[0]['pro_name'],
            'count' => count($proData)-1
        );
        return $temp;
    }


    public function test(){
        $system ='yxhb';
        $mod_name = 'GuesttjApply';
        $id = 21;
        // - 流程人员
        $name = session('name');
        $resArr =  M($system.'_appflowproc a')->join($system.'_boss b on b.id=a.per_id')->field('b.wxid')->where(array('a.aid' => $id ,'a.mod_name' => $mod_name,'a.per_name'=>array('neq',$name)))->select();
        $recevier = '';
        foreach($resArr as $val){
            $recevier .= $val['wxid'].',';
        }
        // - 抄送人员
        $resArr = M($system.'_appcopyto')->field('copyto_id')->where(array('aid' => $id,'mod_name' =>$mod_name,'type' => 1))->find();
        $recevier .= $reArr['copyto_id'] ;
        
    }

}
