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
        $system  = I('get.system');
        $aid     = I('get.aid');
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
        
        foreach( $data as $k => $v){
            $html = D(ucfirst($system).'Appflowtable')->getConditionStepHtml($pro_mod,$v['condition'],$v['id']);
            $fiexd_copy = D(ucfirst($system).'Appcopyto')->getFiexdCopyHtml($v['fiexd_copy_id']);
            $str = "<span class='weui-badge' style='position: relative;top: -5.2em;right: -1.2em;padding: 1px 3px;line-height: 1;'>X</span>";
            $fiexd_copy['html'] = str_replace($str,'',$fiexd_copy['html']);
            $temp[] = array(
                'id'    => $v['id'],
                'title' => $v['title'],
                'html'  => $html, 
                'fiexd' => $fiexd_copy['html'],
            );
        }
        $url = $aid?U('Light/Apply/applyInfo',array('modname'=>$pro_mod,'aid'=>$aid,'system'=>$system)):U('Light/View/View',array('modname'=>$pro_mod,'system' => $system)); 
        $show['name'] = $mod_name;
        $show['data'] = $temp;
        $authGroup  =  $this->getAuthGroup($system,$pro_mod);
        $detailModel= D('YxDetailAuth');
        $detailAuth = $detailModel->CueAuthCheck();
        $atten      = $detailModel->ActiveAttention($system,$pro_mod);
        $this->assign('group',$authGroup);
        $this->assign('explain',$explain);
        $this->assign('CueConfig',$detailAuth);
        $this->assign('show',$show);
        $this->assign('url',$url);
        $this->assign('aid',$aid);
        $this->assign('modname',$pro_mod);
        $this->assign('system',$system);
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
        # 权限rule搜索
        $map = array(
            'name'  => "Light/{$system}/{$type}",
            'status' => 1,
        );
        $res = M('auth_rule')->field('id')->where($map)->find();
        if(empty($res)) return $reArr; // ---都无权限
        # 拥有权限分组
        $map = array(
            'rules'  => array('like',"%{$res['id']}%"),
            'status' => 1,
            'id'     => array('neq','2'),
        );
        $group = M('auth_group')->field('id,title,rules')->where($map)->select();
        $auth_group = array(); // 是否在这个权限组中
        foreach($group as  $v){
            $temp = explode(',',$v['rules']);
            if(in_array($res['id'],$temp)) $auth_group[] = $v; 
        }
        if(empty($auth_group)) return $reArr;  // ---无部门
        # 权限人员获取
        $groupStr   = '';
        $where      = '';
        $leaguerStr = ''; 
        foreach ($auth_group as $key => $value) {
            if($key != 0) $where.=' or ';
            $where .= 'group_id = '.$value['id']; 
        }
        $leaguer = M('auth_group_access a')
                    ->field('b.name,a.uid')
                    ->join($system.'_boss b on a.uid=b.wxid')
                    ->where($where)
                    ->group('a.uid')
                    ->select();
        if(empty($leaguer))return $reArr;
        $user = array();
        foreach($leaguer as $k=>$v){
            $leaguerStr .= $v['name'].' ';
            $user[] = $v['uid'];
        }
        $map = array('a.wx_id' => array('in',implode(',',$user)));
        $bm = M('yx_bm_access a')
                ->field('b.bm')
                ->join($system.'_bm b on a.bm_id=b.id')
                ->where($map)
                ->group('b.bm')
                ->select();
        foreach($bm as $v){
            if($reArr['group'] == '暂无') $reArr['group']='';
            $reArr['group'] .= "{$v['bm']} ";
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
        $system  = I('get.system');
        $pro_mod = I('get.modname');
        $aid     = I('get.aid');
        //先判断_pushlist表中是否存在这个模块，没有的话先添加一条这个模块空的推送人员的数据
        $res = M($system.'_pushlist')->where(array('stat' => 1 , 'pro_mod' => $pro_mod))->find();
        if(empty($res)){
            $data = M($system.'_appflowtable')->where(array('stat' => 1 , 'pro_mod' => $pro_mod))->field('pro_name')->find();
            $res = array(
                'pro_mod'=>$pro_mod,                      //模块名
                'pro_name'=>$data['pro_name'],           //相关说明
                'stage_name'=>'推送',
                'date'=>'0000-00-00 00:00:00',
                'push_name'=>'""',
                'condition'=>'',                        //条件
                'ranges'=>null,
                'type'=>2,
                'stat'=>1,
                'rule'=>null,
            );
            M($system.'_pushlist')->add($res);
        }

        $map = array(
            'mod_system' => $system,
            'name'       => $pro_mod,
        );
        $sy = M('yx_config_title')->field('mod_title,system')->where($map)->find();
        $arr = M($system.'_pushlist')->where(array('stat' => 1 , 'pro_mod' => $pro_mod))->field('id,pro_name,pro_mod,push_name')->select();
        $temp = array();
        $html = D('Html');
        $boss = D(ucfirst($system).'Boss');
        foreach($arr as $k => $v){
            $user = trim($v['push_name'],'"');
            $user = explode(',',$user);
            $userArr = array();
            foreach ($user as $val) {
                 $userArr[] = array(
                     'wxid'   => $val,
                     'name'   => $boss->getNameFromWX($val),
                     'avatar' => $boss->getAvatarFromWX($val),
                 );
            }
            $v['html'] = $html->PushHtml($userArr);
            $temp[] = $v;
        }
        $url = $aid?U('Light/Apply/applyInfo',array('modname'=>$pro_mod,'aid'=>$aid,'system'=>$system)):U('Light/View/View',array('modname'=>$pro_mod,'system' => $system));
        $detailModel= D('YxDetailAuth');
        $detailAuth = $detailModel->CueAuthCheck();
        $this->assign('CueConfig',$detailAuth);
        $this->assign('condition',$temp);
        $this->assign('pro_name',$sy['mod_title']);
        $this->assign('system',$system);
        $this->assign('url',$url);
        $this->assign('aid',$aid);
        $this->assign('modname',$pro_mod);
        $this->display('Process/PushProcess');
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
        $data = trim($data,',');
        $data = '"'.$data.'"';
        if(!M($system.'_pushlist')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');    //在Model.class.php中，自动表单令牌验证
        $result = M($system.'_pushlist')->where(array('pro_mod'=>$pro_mod,'id'=>$id))->setField('push_name',$data);
        if(!$result) $this->ajaxReturn(array('code' => 403,'msg' =>'无改动，无需提交'));
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

    //

}
