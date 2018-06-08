<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbCgfkApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_cgfksq';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->find();
    }

    public function getTableName()
    {
        return $this->trueTableName;
    }

    public function recordContent($id)
    {
        $res = $this->record($id);
        $result = array();
        $clientname = M('yxhb_gys')->field('g_name')->where(array('id' => $res['gys']))->find();


        $color = $res['yfye'] > 0? '#f12e2e':'black';
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>$res['zd_date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        // $result['content'][] = array('name'=>'申请单号：',
        //                              'value'=>$res['dh'],
        //                              'type'=>'string',
        //                              'color' => 'black'
        //                             );
        $result['content'][] = array('name'=>'应付余额：',
                                     'value'=> "&yen;".number_format($res['yfye'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => $color
                                    );
        $result['content'][] = array('name'=>'付款金额：',
                                     'value'=>"&yen;".number_format($res['fkje'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black;font-weight: 600;'
                                    );
        $result['content'][] = array('name'=>'大写金额：',
                                     'value'=>cny($res['fkje']),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $fkfs = '暂无';
        if($res['fkfs'] == 4 ){
            $fkfs = '现金';
        }elseif($res['fkfs'] == 2 ){
            $fkfs = '公户';
        }elseif ($res['fkfs'] == 3 ) {
            $fkfs = '汇票';
        }
        $result['content'][] = array('name'=>'付款方式：',
                                     'value'=>$fkfs,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );    
        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>$res['zy'],
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        $result['imgsrc'] = '';
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['rdy']);
        $result['applyerName'] = $res['rdy'];
        $result['stat'] = $res['stat'];
        return $result;
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',0);
    }

         /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $clientname = M('yxhb_gys')->field('g_name')->where(array('id' => $res['gys']))->find();
        
        $result[] = array('name'=>'申请日期：',
                                     'value'=>$res['zd_date'],
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string'
                                    );
        // $result[] = array('name'=>'申请单号：',
        //                              'value'=>$res['dh'],
        //                              'type'=>'string'
        //                             );        
        $result[] = array('name'=>'应付余额：',
                                     'value'=> number_format($res['yfye'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['fkje'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['rdy'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['zy'],
                                     'type'=>'text'
                                    );
        return $result;
    }

    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->getField('rdy');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res    = $this->record($id);
        $name   = M('yxhb_gys')->field('g_name')->where(array('id' => $res['gys']))->find();
        $result = array(
            'sales'   => $res['rdy'],
            'approve' => number_format($res['fkje'],2,'.',',')."元",
            'notice'  => $res['zy'],
            'date'    => $res['zd_date'],
            'title'   => '供货单位',
            'name'    => $name['g_name'], 
            'stat'    => $res['stat']
        );
        return $result;
    }

    /**
     * 获取采购付款 供应商信息
     */
    public function getCustomerList(){

        $word = I('math');
        $sql = "select 
                    a.id as id,g_name as text 
                from 
                    yxhb_gys as a,yxhb_cght as b 
                where 
                    a.id=b.ht_gys and b.ht_stat=2 and (a.g_helpword like '%{$word}%' or a.g_name like '%{$word}%')
                group by 
                    a.id 
                order by 
                    g_name asc";
        $res = M()->query($sql);
        return $res;
    }

    /**
     * 单号获取
     * @param string $system 系统
     * @return string $id    单号
     */
    public function getDhId(){
        $today = date('Y-m-d',time());
        $sql   = "select * from yxhb_cgfksq where date_format(date, '%Y-%m-%d' )='{$today}' and dh like 'CS%'";
        $res   = M()->query($sql);
        $count = count($res);
        $time  = str_replace('-','',$today);
        $id    = "CS{$time}";
        if($count < 9)  return  $id.'00'.($count+1);
        if($count < 99) return $id.'0'.($count+1);
        return $id.$count;
    }

    /**
     * 采购付款提交 
     */
    public function submit(){
        $today = date('Y-m-d',time());
        $user  = session('name');
        $val   = $this->cgfkValidata();
        $ysye  = I('post.ysye');
        $bank  = I('post.type');
        $gyszh = I('post.gyszh');
        $copyto_id = I('post.copyto_id');
        if(!$val['bool']) return $val;
        list($user_id, $notice,$money,$system) = $val['data'];
        // 重复提交
        if(M('yxhb_cgfksq')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $addData = array(
            'dh'      => $this->getDhId(),
            'zd_date' => $today,
            'fkje'    => $money,
            'gys'     => $user_id,
            'zy'      => $notice,
            'clmc'    => '',
            'fkfs'    => $bank,
            'rdy'     => $user,
            'bm'      => 1,
            'stat'    => 3,
            'sqr'     => $user,
            'clgg'    => '无',
            'htbh'    => '无',
            'cwbz'    => '',
            'jjyy'    => '',
            'gyszh'   => $gyszh,
            'date'    => date('Y-m-d H:i:s',time()),
            'fylx'    => 1,
            'htlx'    => '汽运',
            'yfye'    =>  $ysye
        ); 

        $result = M('yxhb_cgfksq')->add($addData);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            $fix = explode(",", $copyto_id);
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'CgfkApply', $result);
        }
        
        $wf = A('WorkFlow');
        $salesid = session('yxhb_id');
        $res = $wf->setWorkFlowSV('CgfkApply', $result, $salesid, 'yxhb');

        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }

    /**
     * 采购付款提交信息校验
     */
    public function cgfkValidata(){
        $user_id = I('post.user_id');
        $notice = I('post.text');
        $money = I('post.money');

        // 公司检测
        if($user_id == '' || $user_id <= 0) return array('bool'=> false, 'msg' => '请选择供货公司');
        // 金额 
        if($money == '' ||  $money <= 0 || $money > 1000000000000 ) return array('bool'=> false, 'msg' => '付款金额错误');
        // 备注检测
        if(strlen($notice)<5) return array('bool'=> false, 'msg' => '备注不能少于5个字');

        return array('bool'=> true, 'data' => array($user_id, $notice,$money,$system));
    }  

    /**
     * 获取环保应收余额
     */
    public function getSupplyPaymentApi(){
        $id   = I('post.user_id');
        $auth = data_auth_sign($id);
        // 计算应收额度
        $post_data = array(
            'auth' => $auth,
            'id'   => $id
        );
        $res = send_post('http://www.fjyuanxin.com/yxhb/include/getSupplyPaymentApi.php', $post_data);
        return $res;
    }

    /**
     * 获取银行账号信息
     */
    public function bankInfo(){
        $gys   = I('post.user_id'); 
        $type  = I('post.type');
        $where = array(
            'bank_stat' => 1,
            'bank_gys'  => $gys,
            'bank_lx'   => $type
        );
        $data  = M('yxhb_bankgys')->field('bank_gys,bank_zhmc,bank_account,bank_khh,bank_lx,id')->where($where)->select();
        foreach($data as $k => $v){
            $account = $v['bank_account'];
            $data[$k]['bank_account'] = substr($account,0,4).'****'.substr($account,-4);
        }
        return $data;
    }
}