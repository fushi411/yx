<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 建材新增总客户
 * @author 
 */

class KkContractguestApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_guest2';

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
            'value'=>'建材新增总客户',
            'type'=>'date',
            'color' => 'black'
        );
        $result['content'][] = array('name'=>'提交时间：',
            'value'=> date('m-d H:i',strtotime($res['g_dtime'])) ,
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'申请日期：',
            'value'=>$res['g_date'],
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'客户名称：',
            'value'=>$res['g_name'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'客户类型：',
            'value'=>$res['g_khlx'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'联系人员：',
            'value'=>$res['g_man'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'联系电话：',
            'value'=>$res['g_phone'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'开票方式：',
            'value'=>$res['g_kpfs'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'结算方式：',
            'value'=>$res['g_jsfs'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'相关说明：',
            'value'=>$res['g_xmmc'],
            'type'=>'text',
            'color' => 'black'
        );
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIDFromName($res['sales']);       //申请人的id
        $result['applyerName'] = $res['sales'];                                 //申请人的姓名
        $result['stat'] = $res['g_stat3'];
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
        return $this->field(true)->where($map)->setField('g_stat3',0);
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
            'value'=> date('m-d H:i',strtotime($res['g_dtime'])) ,
            'type'=>'date'
        );
        $result[] = array('name'=>'申请日期：',
            'value'=> $res['g_date'] ,
            'type'=>'date'
        );

        $result[] = array('name'=>'客户名称：',
            'value'=>$res['g_name'],
            'type'=>'string'
        );

        $result[] = array('name'=>'客户类型：',
            'value'=>$res['g_khlx'],
            'type'=>'string'
        );

        $result[] = array('name'=>'联系人员：',
            'value'=>$res['g_man'],
            'type'=>'string'
        );

        $result[] = array('name'=>'联系电话：',
            'value'=>$res['g_phone'],
            'type'=>'string'
        );

        $result[] = array('name'=>'开票方式：',
            'value'=>$res['g_kpfs'],
            'type'=>'string'
        );

        $result[] = array('name'=>'结算方式：',
            'value'=>$res['g_jsfs'],
            'type'=>'string'
        );

        $result[] = array('name'=>'相关说明：',
            'value'=>$res['g_xmmc'],
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
            'first_title'    => '客户名称',
            'first_content'  => $res['g_name']?$res['g_name']:'无',
            'second_title'   => '客户类型',
            'second_content' => $res['g_khlx']?$res['g_khlx']:'无',
            'third_title'    => '相关说明',
            'third_content'  => $res['g_xmmc']?$res['g_xmmc']:'无',
            'stat'           => $res['g_stat3'],
        );
        return $result;
    }



    /**
     * 备案客户获取
     * @param string $data  拼音缩写
     * @return array $res   备案客户结果
     */
    public function getCustomerList(){
        $data = I('math');
        //$like = $data?"where helpword like '%{$data}%' or name like '%{$data}%'":'';
        //$sql = "select id,g_name as text,g_khjc as jc from (select a.id as id,g_name,g_helpword,g_khjc FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM kk_guest2 where id=any(select a.reid as id FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t {$like} order by g_name ASC";
       // $res = M()->query($sql);
        $map1 = array(
            'stat'=>2,
            'stat2'=>1
        );
        $where['_string'] = " (name like '%{$data}%')  OR ( helpword like '%{$data}') ";

       if (!empty($data)){
           $res = M('kk_newguest')->field('id,name as text')->where($map1)->where($where)->select();
       }else{
           $res = M('kk_newguest')->field('id,name as text')->where($map1)->select();
       }
        return $res;
    }

    public function getCustomerInfo(){
        $id = I('post.user_id');
        $map = array(
            'id'=>$id,
            'stat'=>2
        );
        $res = M('kk_newguest')->field('id,contacts,telephone,name,info')->where($map)->find();
        if (empty($res)) return array('code' => 404,'msg' => '请重新刷新页面！');
        return array('code' => 200,'msg' => '提交成功' , 'data' =>$res);
    }




    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function add(){
        $g_name = I('post.name');
        $g_man = I('post.contacts');
        $g_phone = I('post.telephone');
        $g_xmmc = I('post.info');
        $g_date = I('post.time');
        $copyto_id  = I('post.copyto_id');
        $g_khlx = I('post.g_khlx');
        $sales = session('name');
        $salesid = session('kk_id');
        $g_kpfs = I('post.ht_kpfs');
        $g_sljs = I('post.ht_sljsfs');
        $check_isbeian = I('post.check_isbeian');
        $py = new PinYin();
        $g_helpword = $py->abbr($g_name);
        $sub_time = date("Y-m-d H:i:s",time());
        $res = array(
            'g_name'=>$g_name,           //客户名称
            'g_xmmc'=>$g_xmmc,           //相关说明
            'g_man'=>$g_man,             //联系人员
            'g_ctlman'=>$g_man,
            'g_khjc'=>'',                //客户简称
            'g_address'=>'',
            'g_phone'=>$g_phone,          //电话
            'g_qq'=>'',
            'g_bank'=>'',
            'g_card'=>'',
            'g_group'=>0,
            'g_people'=>'',
            'g_helpword'=>$g_helpword,
            'g_dtime'=>$sub_time,       //提交时间
            'g_qy'=>'',
            'g_ye'=>0,                  //余额
            'g_khlx'=>$g_khlx,          //客户类型
            'g_ed'=>0,
            'reid'=>0,                  //0代表总客户，其他代表是这个数字的二级客户
            'g_stat'=>1,
            'g_flag'=>0,
            'salesid'=>$salesid,        //申请人的id
            'sales'=>$sales,            //申请人的姓名
            'g_jltime'=>$sub_time,
            'g_xgtime'=>'0000-00-00 00:00:00',  //修改时间
            'g_method'=>1,
            'g_supply'=>0,              //供应商（0代表自己，其他数字代表供应商）
            'g_stat3'=>2,               //新增为2；审批通过为1；冻结为3;冻结转删除为4；删除为5；
            'g_date'=>$g_date,          //申请日期
            'g_stat4'=>0,
            'g_kpfs'=>$g_kpfs,          //开票方式
            'g_jsfs'=>$g_sljs,          //结算方式
            'g_beian'=>$check_isbeian,   //是否来自备案，备案客户的ID，空代表不是，其他数值代表是
        );

        //$_POST    提交数据的方式为post
        if(!M('kk_guest2')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');    //在Model.class.php中，自动表单令牌验证
        $result = M('kk_guest2')->add($res);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');

        // 抄送
        $copyto_id = trim($copyto_id,',');      //移除字符串两侧的空白字符及','
        if (!empty($copyto_id)) {
            D('KkAppcopyto')->copyTo($copyto_id,'Contract_guest_Apply', $result);
        }
        $wf = A('WorkFlow');    //调用控制器
        $res = $wf->setWorkFlowSV('Contract_guest_Apply',$result, $salesid, 'kk');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }



    
}