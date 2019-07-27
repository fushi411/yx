<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkTempCreditLineApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_tempcreditlineconfig';

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
        $info = $this->getInfo($res['clientid'],$res['date'],$res['clientname']);
        $info['flag'] =true;
        $result = array();
        $clientname = M('kk_guest2')->field('g_name,g_khjc')->where(array('id' => $res['clientid']))->find();
         //计算应收额度
        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'建材临时额度申请',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['dtime'])),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'执行时间：',
                                     'value'=>$res['date'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_khjc']?$clientname['g_khjc']:$clientname['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    ); 

        $color = -($info['tmpline']-$info['ye']) < 20000?'#f12e2e':'black';
        $result['content'][] = array('name'=>'应收余额：',
                                     'value'=>"&yen;".number_format(-($info['tmpline']-$info['ye']),2,'.',',')."元",
                                     'type'=>'string',
                                     'color' =>$color
                                    ); 
        $result['content'][] = array('name'=>'信用额度：',
                                     'value'=>$info['line'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    ); 
        $result['content'][] = array('name'=>'发货余额：',
                                     'value'=>"&yen;".number_format($info['ye']+$res['ed'],2,'.',',')."元",
                                     'type'=>'string',
                                     'color' => 'black'
                                    ); 
        $result['content'][] = array('name'=>'已有临额：',
                                     'value'=>"&yen;".number_format($res['ed'],2,'.',',')."元",
                                     'type'=>'string',
                                     'color' => 'black'
                                    ); 
        $twoColor = $info['two']==0?'#f12e2e':'black';
        $fiveColor = $info['five']==0?'#f12e2e':'black';
        $tenColor = $info['ten']==0?'#f12e2e':'black';
        $sycs = "<input class='weui-input color' type='text' style='color:{$twoColor}' name='custodian' readonly value='临额二万元 - 剩余{$info['two']}次提交'>
                <input class='weui-input color' type='text' style='color:{$fiveColor}' name='custodian' readonly value='临额五万元 - 剩余{$info['five']}次提交'>
                <input class='weui-input color' type='text' style='color:{$tenColor}' name='custodian' readonly value='临额十万元 - 剩余{$info['ten']}次提交'>";
        $result['content'][] = array('name'=>'剩余次数：',
                                     'value'=>$sycs,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );    
        $result['content'][] = array('name'=>'申请额度：',
                                     'value'=>"&yen;".number_format($res['line'],2,'.',',')."元",
                                     'type'=>'string',
                                     'color' => 'black'
                                    );   
        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>$res['notice'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );   

        //计算应收额度
      
        $result['imgsrc'] = '';
        $result['applyerID'] = $res['salesid'];
        $result['applyerName'] = $res['sales'];
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

    public function getInfo($clientid,$date,$name){
        $result = array();
        $temp = A('TempQuote');

        $info = $temp->getQuoteTimes($clientid,'kk',$date);
        $result['two'] = 5-count($info[0]);
        $result['five'] = 3-count($info[1]);
        $result['ten'] = 1-count($info[2]);

        $ye = $temp->getkkline($clientid,$date);
        // 计算应收额度
        $post_data = array(
            'name' => $name,
            'auth' => data_auth_sign($name),
            'date' => $date
          );
        $res = send_post('http://www.fjyuanxin.com/sngl/include/getClientCreditApi.php', $post_data);
        
        // $result['ye'] = number_format(-($ye-$res['ye']+$res['tmp']),2,'.',',')."元";
        // $result['flag'] = -$res['ye']<20000?true:false;

        $result['ye'] = $res['ye'];
        $result['line'] =  "&yen;".number_format($ye,2,'.',',')."元"; // 信用额度
        $result['tmpline'] = $ye;
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
        $info = $this->getInfo($res['clientid'],$res['date'],$res['clientname']);
        $clientname = M('kk_guest2')->field('g_khjc,g_name')->where(array('id' => $res['clientid']))->find();
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['dtime'])) ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=> date('m-d H:i',strtotime($res['date'])) ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_khjc']?$clientname['g_khjc']:$clientname['g_name'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'应收余额：',
                                     'value'=>number_format(-($info['tmpline']-$info['ye']),2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'已有临额：',
                                     'value'=>number_format($res['ed'],2,'.',',')."元",
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
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['notice'],
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
        return $this->field(true)->where($map)->getField('sales');
    }

    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $clientname = M('kk_guest2')->field('g_khjc,g_name')->where(array('id' => $res['clientid']))->find();
        $temp = array(
            array('title' => '客户名称' , 'content' => $clientname['g_khjc']?$clientname['g_khjc']:$clientname['g_name'] ),
            array('title' => '申请金额' , 'content' => number_format($res['line'],2,'.',',')."元" ),
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
        $sql = "select id,g_name as text,g_khjc as jc from (select a.id as id,g_name,g_helpword,g_khjc FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM kk_guest2 where id=any(select a.reid as id FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t {$like} order by g_name ASC";
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

    // 临时额度增加
    public function submit(){
        $user_id = I('post.user_id');
        $reason = I('post.text');
        $money = I('post.money');
        $copyto_id = I('post.copyto_id');
        $system = 'kk';
        $today = date('Y-m-d',time());
        // 临时 关闭五W额度
        // if($money==1) return array('code' => 404,'msg' => '五万额度暂时无法提交');

        // 参数检验
        if($user_id=='' || $reason=='' || $money=='')  return array('code' => 404,'msg' => '请刷新页面，重新提交');
        
        // 字数校验
        if(strlen($reason)<5 ||strlen($reason)>200)  return array('code' => 404,'msg' => '申请理由不能少于5个字，且不能多于200字');
        // 次数校验
        $model = D('Customer');
        $timeArr = array(5,3,1);
        $times = $model->getQuoteTimes($user_id,$system);
        if($timeArr[$money] <= count($times[$money])) return array('code' => 404,'msg' => '申请次数已达本月上限');
       
        $yxqArr = array('2天','5天','7天');
        $lineArr = array(20000,50000,100000);

         // 有效期校验
         $res     = M($system.'_tempcreditlineconfig')
                    ->field('id,date,dtime,stat,yxq')
                    ->where(array('clientid' => $user_id ,'stat' => array('neq',0),'line' => $lineArr[$money]))
                    ->order('date desc')
                    ->find();
        
        //  为过审的
        if($res['stat'] == 2){
            $app_stat = M($system.'_appflowproc')
                        ->where(array('aid' =>$res['id'],'mod_name' => 'TempCreditLineApply' ,'app_stat' => 1))
                        ->find();
            if(empty($app_stat)) return array('code' => 404,'msg' => '已有一条同等额度申请在审批');
        }
        
        // 过审的情况 有效期判断
        $day = str_replace('天','',$res['yxq']);
        if(strtotime($res['dtime'].' +'.$day.' day')>time()) return array('code' => 404,'msg' => '已有同等额度在有效期内');

        $stat = 2;
        $clientname = $model->getClientname($user_id,$system);
        $sales = session('name');
        $salesid = session($system.'_id');

        $dtime=$model->getDatetimeMk(time());

        $yxq =$yxqArr[$money];
        $line = $lineArr[$money];
        // 流程检验
        $pro = D('KkAppflowtable')->havePro('TempCreditLineApply','line='.$line);
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        $ye = I('ye');
        $ed = $model->getTempCredit($user_id,$system);

        $saveData = array(
             'date'         => $today ,
             'clientname'   => $clientname,
             'clientid'     => $user_id,
             'line'         => $line,
             'sales'        => $sales,
             'salesid'      => $salesid,
             'dtime'        => $dtime,
             'stat'         => $stat,
             'notice'       => $reason,
             'ye'           => $ye,
             'ed'           => $ed,
             'yxq'          => $yxq
         );

        // 表单重复提交
        if(!M($system.'_tempcreditlineconfig')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $result = M($system.'_tempcreditlineconfig')->add($saveData);
        if(!$result) return array('code' => 404,'msg' => '提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D($system.'Appcopyto')->copyTo($copyto_id,'TempCreditLineApply', $result);
        }

        $wf = A('WorkFlow');
        $res = $wf->setWorkFlowSV('TempCreditLineApply', $result, $salesid, $system);
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }

    
}