<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkAddGysLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_gys';
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
                                     'value'=>'建材新增供应商',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['g_dtime'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['g_dtime'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'商户全称：',
                                     'value'=>$res['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
     
        $result['content'][] = array('name'=>'商户类型：',
                                     'value'=>$res['g_type']?$res['g_type']:'无',
                                     'type'=>'string',
                                     'color' => 'black'
                                    );                    
        $result['content'][] = array('name'=>'联系人员：',
                                     'value'=>$res['g_man']?$res['g_man']:'无',
                                     'type'=>'string',
                                     'color' => 'black'
                                    );   
        $result['content'][] = array('name'=>'联系电话：',
                                     'value'=>$res['g_phone']?$res['g_phone']:'无',
                                     'type'=>'string',
                                     'color' => 'black'
                                    );                                 
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['bz']?$res['bz']:'无',
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        $result['imgsrc'] = '';
        
        if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $res['g_people'])>0){
            $applyerId   = D('KkBoss')->getIDFromName($res['g_people']);
            $applyerName = $res['g_people'];
        }else{
            $data = M('kk_boss')->where(array('boss' => $res['g_people']))->find();
            $applyerId   = $data['wxid'];
            $applyerName = $data['name'];
        }
        $result['applyerID'] =  $applyerId;
        $result['applyerName'] = $applyerName;
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
        
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['g_dtime'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['g_dtime'])), 
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'商户全称：',
                                     'value'=>$res['g_name'],
                                     'type'=>'string'
                                    );

        $result[] = array('name'=>'商户类型：',
                                     'value'=>$res['g_type']?$res['g_type']:'无',
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'联系人员：',
                                     'value'=>$res['g_man']?$res['g_man']:'无',
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'联系电话：',
                                     'value'=>$res['g_phone']?$res['g_phone']:'无',
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'相关说明：',
                                     'value'=>$res['bz']?$res['bz']:'无',
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
        $res = $this->record($id);
        $result = array(
            'first_title'    => '商户全称',
            'first_content'  => $res['g_name']?$res['g_name']:'无',
            'second_title'   => '商户类型',
            'second_content' => $res['g_type']?$res['g_type']:'无',
            'third_title'    => '相关说明',
            'third_content'  => $res['bz']?$res['bz']:'无',
            'stat'           => $res['stat'],
        );
        return $result;
    }

    public function submit(){
        $name      = I('post.name');
        $type      = I('post.type');
        $telephone = I('post.telephone');
        $man       = I('post.man');
        $text      = I('post.text');
        $copyto_id = I('post.copyto_id');

        if(!$name) return  array('code' => 404,'msg' => '请输入供应商全称');
        if(!$type || $type == -1)     return  array('code' => 404,'msg' => '请选择供应商类型');
        if(!$telephone) return  array('code' => 404,'msg' => '请输入供应商电话');
        if(!$man) return  array('code' => 404,'msg' => '请输入联系人员','data' => $man);
        $py = new PinYin();
        $g_helpword = $py->abbr($name);

        if(!M('kk_gys')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $data = array(
            'g_name'     => $name,
            'g_man'      => $man,
            'g_address'  => '',
            'g_phone'    => $telephone,
            'g_qq'       => '',
            'g_bank'     => '',
            'g_card'     => '',
            'g_group'    => '',
            'g_people'   => session('name'),
            'g_dtime'    => date('Y-m-d H:i:s',time()),
            'g_helpword' => $g_helpword,
            'g_cl'       => '',
            'g_ye'       => 0,
            'g_type'     => $type,
            'reid'       => 0,
            'stat'       => 2,
            'bz'         => $text,
        );
        $result = M('kk_gys')->add($data);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('KkAppcopyto')->copyTo($copyto_id,'AddGys', $result);
        }
        
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('AddGys', $result, $salesid, 'kk');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }
    
}