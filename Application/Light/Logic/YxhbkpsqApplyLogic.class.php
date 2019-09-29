<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author
 */

class YxhbkpsqApplyLogic extends Model {

    // 实际表名
    protected $trueTableName = 'kk_gys';

    public function getTableName()
    {
        return $this->trueTableName;
    }

    /**
     * 获取有效总客户
     */
    public function getCustomerList(){
        $data = I('math');
        $res = D('Guest')->getGuest('yxhb',$data,'kpsq_Apply');
        return $res;
    }

     public function getCustomerInfo()
     {
         $id = I('user_id');
         return array();
     }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('gys_stat',0);
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
            'value'=>$res['gys_bz']?$res['gys_bz']:'无',
            'type'=>'text'
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
        // 流程判断
        $pro = D('KkAppflowtable')->havePro('AddGys','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
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
            'gys_stat'       => 2,
            'gys_bz'         => $text,
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