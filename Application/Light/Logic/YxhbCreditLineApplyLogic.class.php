<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbCreditLineApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_creditlineconfig';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('aid' => $id);
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
        if(date('Y-m-d',strtotime($res['date'])) == date('Y-m-d',strtotime($res['dtime'])) ){
            $date = date('Y-m-d',strtotime($res['date'].'-1 day'));
        }
        $info = $this->getInfo($res['clientid'],$date);
        $clientname = M('yxhb_guest2')->field('g_khjc,g_name')->where(array('id' => $res['clientid']))->find();
        $color = $info['flag']?'#f12e2e':'black';
        $result['content'][] = array('name'=>'系统类型：',
                                     'value'=>'环保信用额度申请',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>$res['date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'应收额度：',
                                     'value'=>"&yen;".$info['ye'],
                                     'type'=>'number',
                                     'color' => $color
                                    );
        $result['content'][] = array('name'=>'信用额度：',
                                     'value'=>"&yen;".number_format($res['oline'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'已有临额：',
                                     'value'=>"&yen;".$info['ed'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请额度：',
                                     'value'=>"&yen;".number_format($res['line'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black'
                                    );

        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['notice'],
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        $result['imgsrc'] = '';
        $result['applyerID'] = $res['salesid'];
        $result['applyerName'] = $res['sales'];
        $result['stat'] = $res['stat'];
        return $result;
    }

     /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        if(date('Y-m-d',strtotime($res['date'])) == date('Y-m-d',strtotime($res['dtime'])) ){
            $date = date('Y-m-d',strtotime($res['date'].'-1 day'));
        }
        $info = $this->getInfo($res['clientid'],$date);
        $clientname = M('yxhb_guest2')->field('g_khjc,g_name')->where(array('id' => $res['clientid']))->find();
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['dtime'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=> date('m-d',strtotime($res['date'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'应收额度：',
                                     'value'=>$info['ye'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'信用额度：',
                                     'value'=>number_format($res['oline'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'已有临额：',
                                     'value'=>$info['ed'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['line'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['sales'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'相关说明：',
                                     'value'=>$res['notice'],
                                     'type'=>'text'
                                    );
        return $result;
    }

    

    public function getInfo($clientid,$date){
        $result = array();
        $temp = D('Customer');
        
        $ye = $temp->getClientFHYE($clientid,$date);
        $ed = $temp->getTempCredit($clientid,'yxhb',$date);

        $result['flag'] = -$ye['ysye']<20000?true:false;
        $result['ye'] =  number_format(-$ye['ysye'],2,'.',',')."元";
        $result['line'] =  number_format($ye['line'],2,'.',',')."元";
        $result['ed']   = number_format($ed,2,'.',',')."元";
       // $result['fhye'] = number_format($ed+$ye['line']-$ye['ysye'],2,'.',',')."元"; 发货余额
        return $result;
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('aid' => $id);
        return $this->field(true)->where($map)->setField('stat',0);
    }

    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->getField('sales');
    }

        /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $clientname = M('yxhb_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $temp = array(
            array('title' => '客户名称' , 'content' => $clientname['g_name'] ),
            array('title' => '申请金额' , 'content' => number_format($res['line'],2,'.',',')."元"  ),
            array('title' => '相关说明' , 'content' => $res['notice']?$res['notice']:'无'  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $res['stat'],
            'applyerName'    => $res['sales'],
        );
        return $result;
    }
    
    /**
    * 合同客户获取
    * @param string $data  拼音缩写
    * @return array $res   合同用户结果
    */
    public function getCustomerList(){
        $today = date('Y-m-d',time());
        $data = I('math');
        $like = $data?"where g_helpword like '%{$data}%' or g_name like '%{$data}%'":'';
        $sql = "select id,g_name as text,g_khjc as jc from (select a.id as id,g_name,g_helpword,g_khjc FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM yxhb_guest2 where id=any(select a.reid as id FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t {$like} order by g_name ASC";
        $res = M()->query($sql);
        return $res;
    } 

    /**
     * 获取客户用户 各项余额
     * @param int $client_id 客户id
     * @return array $res 各项余额  
     */
    public function  getCustomerInfo(){
        $model = D('Customer');
        $res = $model->getCustomerInfo();
        return $res;
    } 

    // 信用额度增加
    public function submit(){
        $user_id = I('post.user_id');
        $reason = I('post.text');
        $money = I('post.money');
        $date = I('post.date');
        $copyto_id = I('post.copyto_id');
        $today = date('Y-m-d',time());
        
        // 参数检验
        if($user_id=='' || $reason=='' || $money=='') return array('code' => 404,'msg' => '请刷新页面，重新提交！');
        // 申请金额校验
        if($money=='' || $money<0 ) return array('code' => 404,'msg' => '申请金额不能为空，且不能为负数！');
        // 字数校验
        if(strlen($reason)<5 ||strlen($reason)>200) return array('code' => 404,'msg' => '相关说明不能少于5个字，且不能多于200字！');
        $model = D('Customer');
        // 流程检验
        $pro = D('YxhbAppflowtable')->havePro('CreditLineApply','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        $clientname = $model->getClientname($user_id,'yxhb');
        $sales      = session('name');
        $salesid    = session('yxhb_id');
        $dtime      = $model->getDatetimeMk(time());
        
        $yeArr =$model->getClientFHYE($user_id,$today);
        $oline = $yeArr['line'];
        
        $aid = M('yxhb_creditlineconfig')->field('1')->group('clientid,dtime')->select();
        $aid = count($aid)+1;

        $insertData = array(
            'aid'  => $aid,
            'date' => $date,
            'clientname' => $clientname,
            'clientid' => $user_id,
            'lower' => 0,
            'upper' => 0,
            'sales' => $sales,
            'salesid' => $salesid,
            'stat' => 2,
            'dtime' => $dtime,
            'notice' => $reason,
            'line' => $money,
            'oline' => $oline?$oline:0
        );

        // 表单重复提交
        if(!M('yxhb_creditlineconfig')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $result = M('yxhb_creditlineconfig')->add($insertData);
        if(!$result) return array('code' => 404,'msg' => '提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'CreditLineApply', $aid);
        }
        $wf = A('WorkFlow');
        $res = $wf->setWorkFlowSV('CreditLineApply', $aid, $salesid, 'yxhb');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$aid);
    }





}