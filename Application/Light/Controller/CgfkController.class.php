<?php
namespace Light\Controller;
use Think\Controller;
class CgfkController extends BaseController {
    private $today = null;
    private $PageArr = '';
    private $user = '';

    public function __construct(){
        parent::__construct();
        $this->today = date('Y-m-d',time());
        $model = D('Msgdata');
        $this->PageArr = $model->GetMessage('CgfkApply');
        $this->user = session('name');
    }
    public function yxhbCgfk(){
        // 选择当前页
        $this->PageArr['url'][0]['on']=1;
        // 审批
        $appflow = GetAppFlow('yxhb','CgfkApply');
        // 推送
        $push = GetPush('yxhb','CgfkApply');
        // 单号
        $this -> assign('system','yxhb');
        $this -> assign('push',$push['data']);
        $this -> assign('appflow',$appflow);
        $this -> assign('info',$this->PageArr);

        $this -> assign('today',$this->today);
        $this -> assign('title','环保采购付款申请');
        $this -> display('YxhbCgfkApply/yxhbCgfk_qy');
    }

    public function kkCgfk(){
        // 选择当前页
        $this->PageArr['url'][1]['on']=1;
        // 审批
        $appflow = GetAppFlow('kk','CgfkApply');
        // 推送
        $push = GetPush('kk','CgfkApply');
        // 单号
        $this -> assign('system','kk');
        $this -> assign('push',$push['data']);
        $this -> assign('appflow',$appflow);
        $this -> assign('info',$this->PageArr);

        $this -> assign('today',$this->today);
        $this -> assign('title','建材采购付款申请');
        $this -> display('KkCgfkApply/kkCgfk_qy');
    }
    /**
     * 单号获取
     * @param string $system 系统
     * @return string $id    单号
     */
    public function getDhId($system){
        $sql   = "select * from {$system}_cgfksq where date_format(date, '%Y-%m-%d' )='{$this->today}' and dh like 'CS%'";
        $res   = M()->query($sql);
        $count = count($res);
        $time  = str_replace('-','',$this->today);
        $id    = "CS{$time}";
        if($count < 9)  return  $id.'00'.($count+1);
        if($count < 99) return $id.'0'.($count+1);
        return $id.$count;
    }

    /**
     * 采购外部接口 -> 接口安全检验？ 不使用单例 --- 属性固定，时间不刷新
     * @param string 
     */
    public function CgfkApi(){
        $obj = new CgfkController();
        $func = I('api');
        $result = method_exists($obj,$func) ? $this->$func():array('code' => 404,'msg' => '请联系管理员');
        $this->ajaxReturn($result);
    }

    /**
     * 采购付款提交 
     */
    public function submit(){
        $val = $this->cgfkValidata();
        if(!$val['bool']) $this->ajaxReturn($val);
        list($user_id, $notice,$money,$system) = $val['data'];
        // 重复提交
        if(M($system.'_cgfksq')->autoCheckToken($_POST))$this ->ajaxReturn(array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！'));
        $addData = array(
            'dh'      => $this->getDhId($system),
            'zd_data' => $this->today,
            'fkje'    => $money,
            'gys'     => $user_id,
            'clmc'    => '',
            'fkfs'    => '',
            'rdy'     => $this->user,
            'bm'      => 1,
            'stat'    => 3,
            'sqr'     => $this->user,
            'clgg'    => '无',
            'htbh'    => '无',
            'cwbz'    => '',
            'jjyy'    => '',
            'gyszh'   => '',
            'fylx'    => 1,
            'htlx'    => '汽运'
        ); 
        $result = M($system.'_cgfksq')->add($saveData);
        if(!$result) $this ->ajaxReturn(array('code' => 404,'msg' => '提交失败，请重新尝试！'));
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            $fix = explode(",", $copyto_id);
            // 发送抄送消息
            D($system.'Appcopyto')->copyTo($copyto_id,'CgfkApply', $result);
        }

        $wf = new WorkFlowController();
        $salesid = session($system.'_id');
        $res = $wf->setWorkFlowSV('CgfkApply', $result, $salesid, $system);

        $this ->ajaxReturn(array('code' => 200,'msg' => '提交成功' , 'aid' =>$result));

    }

    /**
     * 采购付款提交信息校验
     */
    public function cgfkValidata(){
        $user_id = I('post.user_id');
        $notice = I('post.text');
        $money = I('post.money');
        $system = I('post.system');
        // 系统检查
        $systemArr = array('kk','yxhb');
        if($system == '' || !in_array($system,$systemArr)) return array('bool'=> false, 'msg' => '系统发生错误，请联系管理员！');
        // 公司检测
        if($user_id == '' || $user_id <= 0) return array('bool'=> false, 'msg' => '请选择供货公司');
        // 金额 
        if($money == '' ||  $money <= 0 || $money > 1000000000000 ) return array('bool'=> false, 'msg' => '付款金额错误');
        // 备注检测
        if(strlen($notice)<5) return array('bool'=> false, 'msg' => '备注不能少于5个字');

        return array('bool'=> true, 'data' => array($user_id, $notice,$money,$system));
    }

    /**
     * 获取采购用户信息
     */
    public function getCustomerList(){
        $systemArr = array('kk','yxhb');
        $system = I('system');
        $word = I('math');
        if(!in_array($system,$systemArr)) $this->ajaxReturn(array(array('id' => -404,'text' => '请刷新页面')));
        $sql = "select 
                    a.id as id,g_name as text 
                from 
                    {$system}_gys as a,{$system}_cght as b 
                where 
                    a.id=b.ht_gys and b.ht_stat=2 and a.g_helpword like '%{$word}%' 
                group by 
                    a.id 
                order by 
                    g_name asc";
        $res = M()->query($sql);
        return $res;
    }

}