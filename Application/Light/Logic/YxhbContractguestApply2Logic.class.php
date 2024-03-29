<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 环保新增子客户
 * @author 
 */

class YxhbContractguestApply2Logic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_guest2';

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
        $newres = $this->getsjkh($id);    //上级客户名称
        $res2 = $this->getzkhlist($id);   //子客户名称列表
        $html = $this->getEjHtml($res2);
        $result = array();
        $result['content'][] = array('name'=>'系统类型：',
            'value'=>'环保新增子客户',
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array(
            'name'=>'提交时间：',
            'value'=> date('m-d H:i',strtotime($res['g_dtime'])) ,
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array(
            'name'=>'申请日期：',
            'value'=>$res['g_date'],
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array(
            'name'=>'上级客户：',
            'value'=>$newres['g_name'],
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array(
            'name'=>'二级详情：',
            'value'=>$html,
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array(
            'name'=>'客户名称：',
            'value'=>$res['g_name'],
            'type'=>'string',
            'color' => 'black'
        );

        empty($res['g_khlx'])?$res['g_khlx']="无":$res['g_khlx'];
        $result['content'][] = array(
            'name'=>'客户类型：',
            'value'=>$res['g_khlx'],
            'type'=>'string',
            'color' => 'black'
        );

        empty($res['g_man'])?$res['g_man']="无":$res['g_man'];
        $result['content'][] = array(
            'name'=>'联系人员：',
            'value'=>$res['g_man'],
            'type'=>'string',
            'color' => 'black'
        );

        empty($res['g_phone'])?$res['g_phone']="无":$res['g_phone'];
        $result['content'][] = array(
            'name'=>'联系电话：',
            'value'=>$res['g_phone'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array(
            'name'=>'开票方式：',
            'value'=>$res['g_kpfs'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array(
            'name'=>'结算方式：',
            'value'=>$res['g_jsfs'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array(
            'name'=>'相关说明：',
            'value'=>$res['g_xmmc'],
            'type'=>'text',
            'color' => 'black'
        );
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('YxhbBoss')->getIDFromName($res['sales']);       //申请人的id
        $result['applyerName'] = $res['sales'];                                 //申请人的姓名
        $result['stat'] = $res['g_stat3'];
        return $result;
    }

    /**
     * 二级详情html生成
     * @param array   $data
     * @return string $html
     */
    public function getEjHtml($data){
        foreach ($data as $key=>$val){
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$val['g_name']}'>";
        }
        return $html;
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
    public function getDescription($id){
        $res = $this->record($id);
        $newres = $this->getsjkh($id);    //上级客户名称
        $res2 = $this->getzkhlist($id);   //子客户名称列表
//        $html = $this->getEjHtml($res2);
        $result = array();

        $result[] = array('name'=>'提交时间：',
            'value'=> date('m-d H:i',strtotime($res['g_dtime'])) ,
            'type'=>'date'
        );

        $result[] = array('name'=>'申请日期：',
            'value'=> $res['g_date'] ,
            'type'=>'date'
        );

        $result[] = array('name'=>'上级客户：',
            'value'=>$newres['g_name'] ,
            'type'=>'date'
        );

//        $result[] = array('name'=>'二级详情：',
//            'value'=>$html,
//            'type'=>'date'
//        );

        $result[] = array('name'=>'客户名称：',
            'value'=>$res['g_name'],
            'type'=>'string'
        );

        empty($res['g_khlx'])?$res['g_khlx']="无":$res['g_khlx'];
        $result[] = array('name'=>'客户类型：',
            'value'=>$res['g_khlx'],
            'type'=>'string'
        );

        empty($res['g_man'])?$res['g_man']="无":$res['g_man'];
        $result[] = array('name'=>'联系人员：',
            'value'=>$res['g_man'],
            'type'=>'string'
        );

        empty($res['g_phone'])?$res['g_phone']="无":$res['g_phone'];
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
        $temp = array(
            array('title' => '客户名称' , 'content' => $res['g_name']?$res['g_name']:'无' ),
            array('title' => '客户类型' , 'content' => $res['g_khlx']?$res['g_khlx']:'无'  ),
            array('title' => '相关说明' , 'content' => $res['g_xmmc']?$res['g_xmmc']:'无'  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $res['g_stat3'],
            'applyerName'    => $res['sales'],
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
            'reid'=>0,
            'g_stat3'=>1
        );
        $where['_string'] = " (g_name like '%{$data}%')  OR ( g_helpword like '%{$data}') ";
        $where2['_string'] = "order by g_name ASC";
        $sql1 = "select id,g_xmmc,g_name as text from yxhb_guest2 where (reid = 0 and g_stat3 = 1) order by g_name ASC";
        $sql2 = "select id,g_xmmc,g_name as text from yxhb_guest2 where (reid = 0 and g_stat3 = 1 and ( g_name like '%{$data}%' OR g_helpword like '%{$data}%') ) order by g_name ASC";
        if (!empty($data)){
            $res = M()->query($sql2);
        }else{
            $res = M()->query($sql1);
        }
        return $res;
    }

    public function getCustomerInfo(){
        $id = I('post.user_id');
        $map = array(
            'id'=>$id,
        );
        $res = M('yxhb_guest2')->field('id,g_man,g_phone,g_khlx,g_kpfs,g_jsfs')->where($map)->find();
        if (empty($res)) return array('code' => 404,'msg' => '请重新刷新页面！');
        return array('code' => 200,'msg' => '提交成功' , 'data' =>$res);
    }

    /**
     * 子客户列表信息
     * @param  integer $id 记录ID
     * @return array    所需内容
     */
    function getzkh(){
        $id = I('post.id');
        $map = array(
            'reid'=>$id,
            'g_stat3'=>1
        );
        $res = M('yxhb_guest2')->field('id,g_name')->where($map)->select();
        if (empty($res)) return array('code' => 404,'msg' => '请重新刷新页面！');
        return array('code' => 200,'msg' => '提交成功' , 'data' =>$res);
    }

    /**
     * 子客户详细信息
     * @param  integer $id 记录ID
     * @return array    所需内容
     */
    function getzkhInfo(){
        $id = I('post.id');
        $map = array(
            'id'=>$id,
            'g_stat3'=>1
        );
        $res = M('yxhb_guest2')->field('id,g_name,g_xmmc,g_man,g_phone,g_khlx,g_date,g_kpfs,g_jsfs')->where($map)->find();
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
        $salesid = session('yxhb_id');
        $g_kpfs = I('post.ht_kpfs');
        $g_sljs = I('post.ht_sljsfs');
        $check_isbeian = I('post.check_isbeian');
        $reid = I('post.reid');
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
            'reid'=>$reid,                  //0代表总客户，其他代表是这个数字的二级客户
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
        // 流程检验
        $pro = D('YxhbAppflowtable')->havePro('Contract_guest_Apply2','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        //$_POST    提交数据的方式为post
        if(!M('yxhb_guest2')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');    //在Model.class.php中，自动表单令牌验证
        $result = M('yxhb_guest2')->add($res);

        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');      //移除字符串两侧的空白字符及','
        if (!empty($copyto_id)) {
            D('YxhbAppcopyto')->copyTo($copyto_id,'Contract_guest_Apply2', $result);
        }
        $wf = A('WorkFlow');    //调用控制器
        $res = $wf->setWorkFlowSV('Contract_guest_Apply2',$result, $salesid, 'yxhb');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }


    //建材合同客户的开票方式、结算方式转存到客户表中
    public function getdata(){
        //$sql = "SELECT `id`,`ht_sljsfs`,`ht_kpfs`,`ht_khmc` FROM `kk_ht` WHERE `ht_stat` = 2 AND  (unix_timestamp(ht_date) >= unix_timestamp('2018-01-01 00:00:00'))";
        $sql = "SELECT `id`,`ht_sljsfs`,`ht_kpfs`,`ht_khmc` FROM `yxhb_ht` WHERE `ht_stat` = 2";
        $res = M('yxhb_ht')->query($sql);;
        //修改子客户的数据
        foreach ($res as $key=>$val){
            $map = array(
                'id'=>$val['ht_khmc'],
                'g_stat3'=>1,
//                'reid'!=0,
            );
            $data['g_kpfs'] = $val['ht_kpfs'];
            $data['g_jsfs'] = $val['ht_sljsfs'];
            $res = M('yxhb_guest2')->where($map)->data($data)->save();

            $map2 = array(
                'id'=>$val['ht_khmc'],
            );
            $reid = M('yxhb_guest2')->field('reid')->where($map2)->find();
            $map3 = array(
                'id'=>$reid['reid'],
                'g_stat3'=>1,
                'reid'=> 0,
            );
            $res2 = M('yxhb_guest2')->where($map3)->data($data)->save();

        }
    }

    //环保总客户审批通过后调用的方法
    public function add2($aid){
        $map = array(
            'id'=>$aid,
        );
        $data = M('yxhb_guest2')->where($map)->find();
        unset($data['id']);
        $data['reid'] = $aid;
        $data['g_name'] = str_replace('-总','',$data['g_name']);
        $sub_time = date("Y-m-d H:i:s",time());
        $data['g_dtime'] = $sub_time;

        $result = M('yxhb_guest2')->add($data);

        //审批记录表 增加审批记录
        $map2 = array(
            'aid'=>$aid,
            'app_name'=>'审批',
            'mod_name'=>'Contract_guest_Apply',
        );
        $data2 = M('yxhb_appflowproc')->where($map2)->find();
        unset($data2['id']);
        $data2['mod_name'] = 'Contract_guest_Apply2';
        $data2['aid'] = $result;
        $result2 = M('yxhb_appflowproc')->add($data2);

    }

    //获取总客户的名称
    public function getname(){
        $aid = I('post.id');
        $map = array(
            'id'=>$aid
        );
        $res = M('yxhb_guest2')->field('g_name')->where($map)->find();
        if(empty($res)) return array('code' => 404,'msg' =>'获取数据，请重新尝试！');
        return array('code' => 200,'msg' => '获取数据成功' , 'data' =>$res);
    }

    //获取上级客户名称
    public function getsjkh($id){
        $map1 = array(
            'id'=>$id,
        );
        $res1 = M('yxhb_guest2')->field('reid')->where($map1)->find();
        $map2 = array(
            'id'=>$res1['reid'],
        );
        $res2 = M('yxhb_guest2')->field('g_name')->where($map2)->find();
        return $res2;
    }

    //获取子客户列表信息
    public function getzkhlist($id){
        $map1 = array(
            'id'=>$id,
        );
        $res1 = M('yxhb_guest2')->field('reid')->where($map1)->find();
        $map2 = array(
            'reid'=>$res1['reid'],
            'g_stat3'=>1
        );
        $res2 = M('yxhb_guest2')->field('g_name')->where($map2)->select();
        $num = count($res2,COUNT_NORMAL);
        if($num == 1 || empty($res2)){
            $data = array(
                1=>array(
                    'g_name'=>'无子客户',
                )
            );
        }else{
            $data = M('yxhb_guest2')->field('g_name')->where($map2)->where(array('limit'=>$num-1))->select();
        }
        return $data;
    }

    // 重名验证
    public function checkNameIsSet(){
        $name = I('post.name');
        $map = array(
            'g_name' => $name,
            'g_stat3'=>1,
            'reid'=> array('neq',0)
        );
        $data = M('yxhb_guest2')->where($map)->find();
        if(empty($data)) return true;
        return false;
    }
    
}