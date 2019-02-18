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
        $pro_mod = I('get.modname');
        // $pro_mod 为空的情况
        $system = I('get.system');
        if(!$system)$system='yxhb';
        if($pro_mod == '') die;
        $mod_name  = D('Seek')->getModname($pro_mod,$system);
        $mod_name  = $mod_name.'流程';
        $map = array(
            'system' => $system,
            'mod'    => $pro_mod,
            'stat'   => 1,
        );
        $data = M('yx_config_viewpro')->where($map)->order('id desc')->select();
        $temp = array();
        if(empty($data)){
            $html = D(ucfirst($system).'Appflowtable')->getConditionStepHtml($pro_mod);
            $temp[] = array(
                'title' => $mod_name,
                'html'  => $html, 
            );
        }else{
            foreach( $data as $k => $v){
                $html = D(ucfirst($system).'Appflowtable')->getConditionStepHtml($pro_mod,$v['condition']);
                $temp[] = array(
                    'title' => $v['title'],
                    'html'  => $html, 
                );
            }
        }
        $show['name'] = $mod_name;
        $show['data'] = $temp;
        $authGroup =  $this->getAuthGroup($system,$pro_mod);
        $this->assign('group',$authGroup);
        //$this->assign('data',$proData);
        $this->assign('show',$show);
      
        $this->display('Process/ApplyProcess');
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
     * 环保发货修改流程数据
     * @param array  流程数据
     */
    private function fh_edit_Apply_hb($proData){
        $temp = array();
        $temp[] = array(
            'title' => '授权库号一致审批流程',
            'count' => 0
        );
        $temp[] = array(
            'title' => '授权库号不一致审批流程',
            'count' => 1
        );
        return $temp;
    }

    /**
     * 环保物料配置数据
     * @param array  流程数据
     */
    private function KfMaterielApply($proData){
        $temp = array();
        $temp[] = array(
            'title' => '化验室提交审批流程',
            'count' => 0
        );
        $temp[] = array(
            'title' => '生产部提交审批流程',
            'count' => 1,
            'only'  => 1
        );
        return $temp;
    }


        /**
     * 环保发货修改流程数据
     * @param array  流程数据
     */
    private function fh_edit_Apply($proData){
        $temp = array();
        $temp[] = array(
            'title' => '授权库号一致审批流程',
            'count' => 0
        );
        $temp[] = array(
            'title' => '授权库号不一致审批流程',
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
     * 推送可视化页面 独立
     * @param string $system 系统
     * @param string $modname 模块
     */
    public function pushAll(){
        // 进入指定的位置所需参数
        $system = I('system'); 
        $pro_mod = I('modname'); 
        
        $where   = array(
            'stat' => 1,
            'pro_mod' => array('like','%_push')
        );
        $yxhbArr = M('yxhb_appflowtable')->where($where)->field('pro_name,pro_mod,condition')->select();
        $kkArr   = M('kk_appflowtable')->where($where)->field('pro_name,pro_mod,condition')->select();
        $push     = array_merge($this->pushData($yxhbArr,'yxhb'),$this->pushData($kkArr,'kk'));
        $this->assign('push',$push);
        $this->display('Process/pushAll');
    }

    /**
     * 推送可视化页面  总页面数据重构
     * @param array $data  推送数组
     * @param string $sy 系统 
     * @return array $result 重构数组
     */
    private function pushData($data,$sy){
        $system = array('kk' => '建材','yxhb' => '环保');
        $result = array();
        foreach($data as $k=>$v){
            $res = array(
                'pro_name' => $system[$sy].$v['pro_name'],
                'pro_mod'  => str_replace('_push','',$v['pro_mod']),
                'system'   => $sy
            );
            $result[] = $res;
        }
        return $result;
    }

    public function PushProcess(){
        // 进入指定的位置所需参数
//        $system = I('system');
//        $pro_mod = I('modname');
//        $sy = array('kk' => '建材','yxhb' => '环保');
//        $arr = M($system.'_appflowtable')->where(array('stat' => 1 , 'pro_mod' => $pro_mod.'_push'))->field('pro_name,pro_mod,condition')->select();
//        $push = $this->recombinant($arr,$system);
//        $this->assign('condition',$push);
//        $this->assign('pro_name',$sy[$system].$push[0]['pro_name']);
//        $this->display('Process/push');


        $system = I('get.system');
        $pro_mod = I('get.modname');
        $sy = array('kk' => '建材','yxhb' => '环保');
        $arr = M($system.'_pushlist')->where(array('stat' => 1 , 'pro_mod' => $pro_mod))->field('id,pro_name,pro_mod,push_name')->select();
        //不能重复添加相同的模块名
        $arr = $this->a_array_unique($arr);
        $push = $this->recombinant($arr,$system,$pro_mod,$sy);
        $this->assign('condition',$push);
        $this->assign('pro_name',$sy[$system].$push[0]['pro_name']);
        $this->assign('pro_mod',$push[0]['pro_mod']);
        $this->display('Process/push');
    }

    //去除重复模块名的方法
    function a_array_unique($data)
    {
        $i = array();
        foreach($data as $key => &$value){
            if(in_array($value['pro_mod'],$i)){
                unset($data[$key]);
            }else{
                $i = $value;
            }
        }
        return $data;
    }

    /**
     * 推送信息重构
     * @param array $condition condition 数据
     * @return array  $result 重构后的数组
     */
    private function recombinant($condition,$system,$mod,$sy){
        // 数据检验
//        if(!is_array($condition)) return false;
//        foreach($condition as $k => $v){
//            $v['condition'] = json_decode($v['condition'],true);
//
//            // 特殊页面显示 （临时额度） 目前只有临时页面特殊，后期可能修改
//            switch($v['pro_mod']){
//                case 'TempCreditLineApply_push':
//                            $func = 'TempCreditLinePush';
//                    break;
//                default:
//                            $func = 'getApplyPush';
//            }
//            if($sy == $system && $mod == $v['pro_mod']) $condition[$k]['display']   = 'black';
//            $condition[$k]['condition'] = $this->$func($v,$system);
//        }
//        return $condition;

        if(!is_array($condition)) return false;
        foreach($condition as $k => $v){
            $v['push_name'] = json_decode($v['push_name'],true);

            // 特殊页面显示 （临时额度） 目前只有临时页面特殊，后期可能修改
            switch($v['pro_mod']){
                case 'TempCreditLineApply':
                    $func = 'TempCreditLinePush';
                    break;
                default:
                    $func = 'getApplyPush';
            }
            if($sy == $system && $mod == $v['pro_mod']) $condition[$k]['display']   = 'black';
            $condition[$k]['push_name'] = $this->$func($v,$system);
        }
        return $condition;
    }

    private function TempCreditLinePush($data,$system){
//        $result = array();
//        $pushManStr = $data['condition']['two'];
//        $pushManArr = explode(',',$pushManStr);
//        $pushManArr = $this->getUserInfo($pushManArr,$system);
//        $result[] = array(
//            'title' => '二万临时额度',
//            'pushMan' =>$pushManArr
//        );
//        $pushManStr = $data['condition']['push'];
//        $pushManArr = explode(',',$pushManStr);
//        $pushManArr = $this->getUserInfo($pushManArr,$system);
//        $result[] = array(
//            'title' => '五万,十万临时额度',
//            'pushMan' =>$pushManArr
//        );
//        return $result;

        $pro_mod = $data['pro_mod'];
        $res = M($system.'_pushlist')->where(array('typs'=>2,'stat'=>1,'pro_mod'=>$pro_mod))->select();
        $result = array();
        foreach ($res as $key=>$val){
            if($val['condition']=='line=20000'){
                $pushManStr = $val['push_name'];
                $pushManStr = trim($pushManStr,'"');
                $pushManArr = explode(',',$pushManStr);
                $pushManArr = $this->getUserInfo($pushManArr,$system);
                $result[] = array(
                    'title' => '二万临时额度',
                    'tiaojian'=>$val['condition'],
                    'id'=>$val['id'],
                    'pushMan' =>$pushManArr
                );
            }else{
                $pushManStr = $val['push_name'];
                $pushManStr = trim($pushManStr,'"');
                $pushManArr = explode(',',$pushManStr);
                $pushManArr = $this->getUserInfo($pushManArr,$system);
                $result[] = array(
                    'title' => '五万,十万临时额度',
                    'tiaojian'=>$val['condition'],
                    'id'=>$val['id'],
                    'pushMan' =>$pushManArr
                );
            }
        }
        return $result;
    }

    private function getApplyPush($data,$system){
//        $result = array();
//        $pushManStr = $data['condition']['push'];
//        $pushManArr = explode(',',$pushManStr);
//        $pushManArr = $this->getUserInfo($pushManArr,$system);
//
//        $result[] = array(
//            'title' => $data['pro_name'],
//            'pushMan' =>$pushManArr
//        );
//        return $result;

        $result = array();
        $pushManStr = $data['push_name'];
        if($pushManStr=='""'||$pushManStr==""){
            $result[] = array(
                'title' => $data['pro_name'],
                'id'=>$data['id'],
                'pushMan' =>array(),
            );
            return $result;
        }
        $pushManStr = trim($pushManStr,'"');
        $pushManArr = explode(',',$pushManStr);
        $pushManArr = $this->getUserInfo($pushManArr,$system);

        $result[] = array(
            'title' => $data['pro_name'],
            'id'=>$data['id'],
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
        $res =M($system.'_boss')->where($where)->field('name,wxid,avatar')->select();
        $temp = $res;
        foreach($temp as $k => $v){
            $temp[$k]['sortwxid'] = strtolower($v['wxid']); 
        }
        $top  = array('ChenBiSong','csh','csl');
        $array = array('','','');

        $temp  = list_sort_by($temp,'sortwxid','asc');
      
        foreach($temp as $v){
            if($v['wxid'] == $top[0]){ $array[0] = $v;continue;}
            if($v['wxid'] == $top[1]){ $array[1] = $v;continue;}
            if($v['wxid'] == $top[2]){ $array[2] = $v;continue;}
            $array[] = $v;
        }
        $res = array_filter($array);
        return $res;
    }

    //查看新增的推送人员是否已存在
    public function check_ts(){
        $system = I('post.system');
        $name = I('post.name');
        $pro_mod = I('post.pro_mod');
        $id = I('post.id');
        $res = M($system.'_pushlist')->field('push_name')->where(array('pro_mod'=>$pro_mod,'id'=>$id))->find();
        if (empty($res)) $this->ajaxReturn(array('code'=>200,'dtata'=>true));
        $res = $res['push_name'];
        $res = trim($res,'"');
        $res = explode(',',$res);
        //$res = json_decode($res['push_name'],true);  //因为存进去的不是json格式的，所以不能用json_decode
        //$this->ajaxReturn($res);
        //验证推送人员是否已存在
        foreach ($res as $val){
            if ($name == $val) $this->ajaxReturn(array('code'=>404,'data'=>'推送人员已存在，请重新选择！'));
        }

        //验证推送人员是否在建材、环保系统
        $res = M($system.'_boss')->where(array('wxid'=>$name))->find();
        if(empty($res)) $this->ajaxReturn(array('code'=>404,'data'=>'该推送人员不在本系统，请重新选择'));
        $this->ajaxReturn(array('code'=>200,'dtata'=>true));
    }

    //修改推送名单
    public function save_ts(){
        $system = I('post.system');         //系统
        $pro_mod = I('post.pro_mod');       //模块名
        $data = I('post.data');             //推送人员名单
        $id = I('post.id');                 //数据id
        //$this->ajaxReturn($data);
        $data = implode(',', $data);
        $data = '"'.$data.'"';
        if(!M($system.'_pushlist')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');    //在Model.class.php中，自动表单令牌验证
        $result = M($system.'_pushlist')->where(array('pro_mod'=>$pro_mod,'id'=>$id))->setField('push_name',$data);
        if(!$result) $this->ajaxReturn(array('code' => 404,'msg' =>'提交失败，请重新尝试！'));
        $this->ajaxReturn(array('code' => 200,'msg' => '提交成功' , 'aid' =>$result));
    }


    //验证登入用户是否有权限修改推送名单
    public function check_jurisdiction(){
        $detailModel= D('YxDetailAuth');
        $detailAuth = $detailModel->CueAuthCheck();
        $this->ajaxReturn($detailAuth);
    }

    /**
     * 测试
     */
    public function forTest(){
        $this->display('Process/index');
    }

    //数据格式转化
    public function sjzh(){
        $system = I('post.system');
        $res = M($system.'_pushlist')->field('id,push_name')->select();
        $arr = array();
        $data = array(
            'id'=>'',
            'push_name'=>array(),
        );
        foreach ($res as $value){
            $data['id']=$value['id'];
            $value = trim($value['push_name'],'"');
            $value = explode(',',$value);
            $data['push_name']=$value;
            $data['push_name'] = array_filter( $data['push_name']);
            $arr[]=$data;
        }
        foreach ($arr as $value){
            M($system.'_pushlist')->where(array('id'=>$value['id']))->setField('push_name',json_encode($value['push_name']));
        }

    }

}
