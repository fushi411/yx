<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 新增备案客户逻辑模型
 * @author 
 */

class KkNewGuestApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_newguest';

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

    //详情(点击查看之后显示)
    public function recordContent($id)
    {
        $res = $this->record($id);
        $result = array();
        $result['content'][] = array('name'=>'系统类型：',
            'value'=>'建材新增备案客户',
            'type'=>'date',
            'color' => 'black'
        );
        $result['content'][] = array('name'=>'提交时间：',
            'value'=> date('m-d H:i',strtotime($res['dtime'])) ,
            'type'=>'date',
            'color' => 'black'
        );
        $result['content'][] = array('name'=>'申请日期：',
            'value'=>$res['date'],
            'type'=>'date',
            'color' => 'black'
        );
        $result['content'][] = array('name'=>'备案名称：',
            'value'=>$res['name'],
            'type'=>'string',
            'color' => 'black'
        );


        $result['content'][] = array('name'=>'联系人员：',
            'value'=>$res['contacts'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'联系电话：',
            'value'=>$res['telephone'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'相关说明：',
            'value'=>$res['info'],
            'type'=>'text',
            'color' => 'black'
        );
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIDFromName($res['sales']);       //申请人的id
        $result['applyerName'] = $res['sales'];                                 //申请人的姓名
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
    //审批助手显示
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();

        $result[] = array('name'=>'提交时间：',
            'value'=> date('m-d H:i',strtotime($res['dtime'])) ,
            'type'=>'date'
        );

        $result[] = array('name'=>'申请日期：',
            'value'=> $res['date'] ,
            'type'=>'date'
        );

        $result[] = array('name'=>'备案名称：',
            'value'=>$res['name'],
            'type'=>'string'
        );

        $result[] = array('name'=>'联系人员：',
            'value'=>$res['contacts'],
            'type'=>'string'
        );

        $result[] = array('name'=>'联系电话：',
            'value'=>$res['telephone'],
            'type'=>'string'
        );

        $result[] = array('name'=>'相关说明：',
            'value'=>$res['info'],
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
        return $this->field(true)->where($map)->getField('salesid');
    }

    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $result = array(
            'sales'   => $res['sales'],         //申请人的姓名
            'title2'  => '备案产品',
            'approve' => iconv('gbk','UTF-8',$res['product']),
            'notice'  => $res['info'],
            'date'    => $res['date'],
            'title'   => '备案名称',
            'name'    => $res['name'],
            'modname' => 'NewGuestApply',
            'stat'    => $res['stat']
        );
        return $result;
    }


    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function addNewGuest(){
        $name = I('post.name');
        $product = '水泥';
        $contacts = I('post.contacts');
        $telephone = I('post.telephone');
        $info = I('post.info');
        $date = I('post.time');
        $copyto_id  = I('post.copyto_id');
        $sales = session('name');
        $salesid = session('kk_id');

        $py = new PinYin();
        $helpword = $py->abbr($name);
        $sub_time = date("Y-m-d H:i:s",time());
        $res = array(
            'name'=>$name,                      //备案名称
            'salesid'=>$salesid,                //申请者的id
            'clientvalue'=>'',
            'date'=>$date,                      //申请日期
            'product'=>$product,                //产品
            'telephone'=>$telephone,            //电话
            'addr'=>'',
            'sales'=>$sales,                    //申请者姓名
            'dtime'=>$sub_time,                 //提交时间
            'helpword'=>$helpword,              //拼音
            'stat'=>2,
            'area'=>'',
            'info'=>$info,                      //相关说明
            'notice'=>'',
            'contacts'=>$contacts,              //联系人
            'edittime'=>'0000-00-00 00:00:00',
            'stat2'=>1,                         //1代表未转为合同客户的备案客户，2表示已经转为合同客户
            'stat3'=>1,
        );
        //$_POST    提交数据的方式为post
        if(!M('kk_newguest')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');    //在Model.class.php中，自动表单令牌验证
        $result = M('kk_newguest')->add($res);

        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');      //移除字符串两侧的空白字符及','
        if (!empty($copyto_id)) {
            D('KkAppcopyto')->copyTo($copyto_id,'NewGuestApply', $result);
        }
        $wf = A('WorkFlow');    //调用控制器
        $res = $wf->setWorkFlowSV('NewGuestApply',$result, $salesid, 'kk');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }

    // 重名验证
    public function checkNameIsSet(){
        $name = I('post.name');
        $map = array(
            'name' => $name,
            'stat'=>2
        );
        $data = M('kk_newguest')->where($map)->find();
        if(empty($data)) return true;
        return false;
    }

    
}