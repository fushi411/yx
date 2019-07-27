<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkClientStatementApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_clientstatement';

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
        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'建材新增对账单',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['dtime'])),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );  
        $result['content'][] = array('name'=>'对账详情：',
                                     'value'=> '查看对账信息',
                                     'type'=>'string',
                                     'color' => '#337ab7',
                                     'id' => 'look',
                                    );                            
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>D('kk_guest2')->getName($res['client']),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'起始日期：',
                                     'value'=>$res['stday'],
                                     'type'=>'data',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'结束日期：',
                                     'value'=>$res['enday'],
                                     'type'=>'data',
                                     'color' => 'black'
                                    );
       
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['qtbz']?$res['qtbz']:'无',
                                     'type'=>'data',
                                     'color' => 'black;'
                                    );
        $mydata = array(
            'id' => $res['client'],
            'auth' => data_auth_sign($res['client']),
            'stday' => $res['stday'],
            'enday' =>$res['enday'],
            'user_name' => D('kk_guest2')->getName($res['client']));
        $result['mydata'] = $mydata;
        $result['imgsrc'] = '';
        $result['applyerName'] = $res['rdy'];
        $result['applyerID'] = D('kk_boss')->getIDFromName($res['rdy']);
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
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['jl_date'])),
                                     'type'=>'date'
                                    );
         $res = $this->record($id);
        $result = array();
        $result[] = array('name'=>'申请单位：',
                                     'value'=>'建材',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>D('kk_guest2')->getName($res['client']),
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'起始日期：',
                                     'value'=>$res['stday'],
                                     'type'=>'data'
                                    );
        $result[] = array('name'=>'结束日期：',
                                     'value'=>$res['enday'],
                                     'type'=>'data'
                                    );
        return $result;
    }

        /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res    = $this->record($id);
        $ntext = $res['qtbz']?$res['qtbz']:'无';
        $temp = array(
            array('title' => '客户名称','content' => D('kk_guest2')->getName($res['client']) ),
            array('title' => '开始时间','content' => $res['stday'] ),
            array('title' => '结束时间','content' => $res['enday']),
            array('title' => '相关说明','content' => $ntext),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $res['stat'],
            'applyerName'    => $res['rdy'],
        );
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
     * 获取有效总客户
     */
    public function getCustomerList(){
        $data = I('math');
        $res = D('Guest')->getGuest('kk',$data,'ClientStatementApply');
        return $res;
    }

    // 获取用户详情
    public function getCustomerInfo(){
        $user_id    = I('post.user_id');
        return data_auth_sign($user_id);
    }
    // 提交
    public function submit(){
        $data       = I('post.data');
        $start_date = I('post.start_date');
        $end_date   = I('post.end_date');
        $user_id    = I('post.user_id');
        $text       = I('post.text');
        $copyto_id  = I('post.copyto_id');
        // 参数检验
        if(empty($user_id)) return  array('code' => 404,'msg' => '请选择客户');
        if(empty($start_date)) return  array('code' => 404,'msg' => '请选择开始时间');
        if(empty($end_date)) return  array('code' => 404,'msg' => '请选择结束时间');
        // 流程检验
        $pro = D('KkAppflowtable')->havePro('ClientStatementApply','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        // 查看是否已经有 交集时间 提交
        if($this->findData($user_id,$start_date,$end_date)) return array('code' => 404,'msg' => '选择时间段内已有提交记录');
        $add = array(
            'stday' => $start_date,
            'enday' => $end_date,
            'client' => $user_id,
            'content' =>  html_entity_decode($data['content']),
            'applyuser' => session('kk_id'),
            'dtime' => date('Y-m-d H:i:s'),
            'stat'  => 2,
            'yfbz'  => '',
            'qtbz'  => $text,
            'qmbz'  => '',
            'qcqk'  => $data['qc_int'],
            'bqyf'  => $data['totalbqhr_int'],
            'qtje'  => $data['totalqtje_int'],
            'totalje' => $data['totalje_int'],
            'qmje'   => $data['qmje_int'],
            'fhnums' => '',
            'rdy'  => session('name'),
        );
        if(!M('kk_clientstatement')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $result = M('kk_clientstatement')->add($add);
        if(!$result) return array('code' => 404,'msg' => '提交失败，请重新尝试！');
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('KkAppcopyto')->copyTo($copyto_id,'ClientStatementApply', $result);
        }
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('ClientStatementApply', $result, $salesid, 'kk');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }

    // 交集时间获取
    public function findData($client,$stday,$enday){
        $sql = "SELECT
                    1
                FROM
                    kk_clientstatement
                WHERE
                    stat <> 0
                AND client = '{$client}'
                AND (
                    (
                        stday <= '{$stday}'
                        AND enday >= '{$enday}'
                    )
                    OR (
                        stday <= '{$enday}'
                        AND enday >= '{$enday}'
                    )
                    OR (
                        stday <= '{$stday}'
                        AND enday >= '{$stday}'
                    )
                    OR (
                        stday >= '{$stday}'
                        AND enday <= '{$enday}'
                    )
                )";
        $res = M()->query($sql);
        return empty($res)?false:true;
    }
}