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
     * 推送可视化页面
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

     /**
     * 推送可视化页面
     */
    public function PushProcess(){
        // 进入指定的位置所需参数
        $pro_mod = I('modname'); 
        
        $where   = array(
            'stat' => 1,
            'pro_mod' => array('like','%_push')
        );
        $yxhbArr = M('yxhb_appflowtable')->where($where)->field('pro_name,pro_mod,condition')->select();
        $kkArr   = M('kk_appflowtable')->where($where)->field('pro_name,pro_mod,condition')->select();
        $yxhbPush = $this->recombinant($yxhbArr,'yxhb');
        $kkPush = $this->recombinant($kkArr,'kk');
        $this->assign('yxhb',$yxhbPush);
        $this->assign('kk',$kkPush);
        $this->display('YxhbProcess/PushProcess');
    }

    /**
     * 推送信息重构
     * @param array $condition condition 数据
     * @return array  $result 重构后的数组
     */
    private function recombinant($condition,$system){
        // 数据检验
        if(!is_array($condition)) return false;
        foreach($condition as $k => $v){
            $v['condition'] = json_decode($v['condition'],true);

            // 特殊页面显示 （临时额度） 目前只有临时页面特殊，后期可能修改
            switch($v['pro_mod']){
                case 'TempCreditLineApply_push': 
                            $func = 'TempCreditLinePush'; 
                    break;
                default:
                            $func = 'getApplyPush';
            }
            $condition[$k]['condition'] = $this->$func($v,$system);
        }
        return $condition;
    }

    private function TempCreditLinePush($data,$system){
        $result = array();
        $pushManStr = $data['condition']['two'];
        $pushManArr = explode(',',$pushManStr);
        $pushManArr = $this->getUserInfo($pushManArr,$system);
        $result[] = array(
            'title' => '二万临时额度',
            'pushMan' =>$pushManArr
        );
        $pushManStr = $data['condition']['push'];
        $pushManArr = explode(',',$pushManStr);
        $pushManArr = $this->getUserInfo($pushManArr,$system);
        $result[] = array(
            'title' => '五万,十万临时额度',
            'pushMan' =>$pushManArr
        );

        return $result;
    }

    private function getApplyPush($data,$system){
        $result = array();
        $pushManStr = $data['condition']['push'];
        $pushManArr = explode(',',$pushManStr);
        $pushManArr = $this->getUserInfo($pushManArr,$system);

        $result[] = array(
            'title' => $data['pro_name'],
            'pushMan' =>$pushManArr
        );
        return $result;
    }

    // 用户名字头像获取
    private function getUserInfo($data,$system){
        // 条件语句拼接
        $where = '(';
        foreach($data as $k => $v){
            if($k!=0) $where.=' or ';
            $where .= "wxid='{$v}'";
        }
        $where .= ')';
        $res =M($system.'_boss')->where($where)->field('name,avatar')->select();
        return $res;
    }

    /**
     * 测试
     */
    private function test(){
        
    }
}
