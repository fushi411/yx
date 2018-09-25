<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkfheditApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_fhxg';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('id' => $id);
        $res = M('kk_fh')->field(true)->where($map)->find();
        $map = array('fh_num' => $res['fh_num']);
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
        $clientname = M('kk_guest2')->field('g_name')->where(array('id' => $res['fh_client']))->find();
        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'建材发货修改',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['xg_date'])) ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>$res['fh_da'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $map  = array('id' => $id);
        $name = M('kk_fh')->field(true)->where($map)->find();
        $show_wlfs = $name;
        $html = $this->makeDeatilHtml($res,$name);
        $name = M('kk_guest2')->field('g_name')->where(array('id' => $name['fh_client']))->find();
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$name['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );

        $result['content'][] = array('name'=>'发货日期：',
                                     'value'=> date('Y-m-d H:i',strtotime($res['fh_date'])),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );                                

        
        $result['content'][] = array('name'  => '发货详情：',
                                     'value' =>  $html ,
                                     'type'  => 'date',
                                     'color' => 'black'
                                    );              
        
        
        $result['content'][] = array('name'=>'修改名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );               
        list($kh,$flag) = $this->getkhInfo($res['fh_client'],$res['fh_date'],$res['fh_kh']);
        $color = $flag ? 'black' :'#f12e2e';
        $result['content'][] = array('name'=>'授权库号：',
                                     'value'=>empty($kh[0]) ? '无' : implode(' - ' ,$kh),
                                     'type'=>'number',
                                     'color' => $color
                                    );   
        $map = array(
            'ht_stday' => array('elt',$res['fh_da']),
            'ht_enday' => array('egt',$res['fh_da']),
            'ht_khmc'  => $res['fh_client']
        );
        $wlfs = M('kk_ht')->where($map)->find();        
        $color = 'black';
        if($wlfs['ht_wlfs'] != $show_wlfs['fh_wlfs']) { $color = '#f12e2e';}
        $result['content'][] = array('name'=>'运输方式：',
                                     'value'=>$wlfs['ht_wlfs'],
                                     'type'=>'text',
                                     'color' => $color
                                    ); 

        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>$res['xg_reason'],
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        if($res['xg_person']) $res['fh_kpy'] = $res['xg_person'];
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIDFromName($res['fh_kpy']);
        $result['applyerName'] = $res['fh_kpy'];
        $result['stat'] = $this->getStat($id);
        return $result;
    }

    /**
     * 发货详情html生成
     * @param array   $data 配比数据
     * @return string $html 
     */
    public function makeDeatilHtml($data,$wlfs){
        $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='提货单号：{$data['fh_num']}'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='产品品种：{$data['fh_cate']} '>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='发货库号：{$data['fh_kh']}'> 
                  <input class='weui-input' type='text' style='color: black;'  readonly value='产品编号：{$data['fh_snbh']}'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='我方重量：{$data['fh_zl']}'> 
                  <input class='weui-input' type='text' style='color: black;'  readonly value='承运车号：{$data['fh_carnum']}'> 
                  <input class='weui-input' type='text' style='color: black;'  readonly value='运输方式：{$wlfs['fh_wlfs']}'>";
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
        $res = M('kk_fh')->field(true)->where($map)->find();
        M('kk_fh')->field(true)->where($map)->setField('fh_stat4',0);
        $map = array('fh_num' => $res['fh_num']);
        return $this->field(true)->where($map)->setField('fh_stat',0);
    }

    /**
     * 环保发货修改通过后调用函数
     * @param  integer $id 记录ID
     * @return integer     影响行数 原表置0 新增加一条信息
     */
    public function theEnd($id)
    {
        $map = array('id' => $id);
        $res = M('kk_fh')->field(true)->where($map)->setField('fh_stat',0);
        $res = $this->record($id);
        $fh  = M('kk_fh')->where($map)->find();
        $addData = array(
            'fh_date'       => $res['fh_date'],
            'fh_da'         => $res['fh_da'],
            'fh_num'        => $res['fh_num'],
            'fh_client'     => $res['fh_client'],
            'fh_anname'     => $res['fh_anname'],
            'fh_cate'       => $res['fh_cate'],
            'fh_kh'         => $res['fh_kh'],
            'fh_snbh'       => $res['fh_snbh'],
            'fh_zl'         => $res['fh_zl'],
            'fh_pz'         => $res['fh_pz'],
            'fh_mz'         => $res['fh_mz'],
            'fh_jz'         => $res['fh_jz'],
            'fh_dfzl'       => $res['fh_dfzl'],
            'fh_carnum'     => $res['fh_carnum'],
            'fh_thr'        => $res['fh_thr'],
            'fh_bz'         => $res['xg_reason'],
            'fh_bs'         => $res['fh_bs'],
            'fh_kpy'        => $res['fh_kpy'],
            'fh_show'       => $res['fh_show'],
            'fh_qy'         => $res['fh_qy'],
            'fh_bzfs'       => $res['fh_bzfs'],
            'fh_wlfs'       => $res['fh_wlfs'],
            'xg_date'       => $res['xg_date'],
            'fh_pp'         => $res['fh_pp'],
            'fh_stat'       => $res['fh_stat'],
            'fh_stat2'      => 1,
            'fh_stat4'      => 2,
            'fh_flag'       => $res['fh_flag'],
            'fh_stat_sh'    => $fh['fh_stat_sh'],
            'fh_wlname'     => $res['fh_wlname'],
            'fh_bid'        => $res['fh_bid'],
            'fh_pass'       => $res['fh_pass'],
            'fh_passtime'   => $res['fh_passtime'],
            'del_reason'    => '',
            'fh_stat5'      => 0,
            'xg_person'     => $res['xg_person']
        ); 
        M('kk_fh')->add($addData);
        $this->AutomaticRenewalTwoMonth($id);
    }


    /**
     * 发货修改后 自动续期两个月
     * @param integer $id aid
     */
    private function AutomaticRenewalTwoMonth($id){
        $res = $this->record($id);
        $bzfs = $res['fh_bzfs'] == '编织袋'?'袋装':'散装';
        
        $map = array(
            'ht_stday' => array('elt',$res['fh_da']),
            'ht_enday' => array('egt',$res['fh_da']),
            'ht_stat'  => 2,
            'ht_khmc'  => $res['fh_client'],
            'ht_cate'  => $res['fh_cate'],
            'ht_wlfs'  => $res['fh_wlfs'],
            'ht_bzfs'  => $bzfs
        );
        $date = date('Y-m-d',strtotime('+2 month'));
        $res = M('kk_ht')->where($map)->setField('ht_enday',$date);
    }
    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $clientname = M('kk_guest2')->field('g_name')->where(array('id' => $res['fh_client']))->find();
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['fh_date'])) ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>$res['fh_da'],
                                     'type'=>'date'
                                    );
        $map  = array('id' => $id);
        $name = M('kk_fh')->field(true)->where($map)->find();
        $wlfs = $name['fh_wlfs'];
        $temp = $name;
        $name = M('kk_guest2')->field('g_name')->where(array('id' => $name['fh_client']))->find();
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$name['g_name'],
                                     'type'=>'string'
                                    );

        $result[] = array('name'=>'承运车号：',
                                     'value'=>$res['fh_carnum'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'产品品种：',
                                     'value'=>$res['fh_cate'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'发货库号：',
                                     'value'=>$res['fh_kh'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'产品编号：',
                                     'value'=>$res['fh_snbh'],
                                     'type'=>'string'
                                    );

        $result[] = array('name'=>'运输方式：',
                                     'value'=>$wlfs ,
                                     'type'=>'string'
                                    );

        $result[] = array('name'=>'修改名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string'
                                    );
        list($kh,$flag,$tmp) = $this->getkhInfo($res['fh_client'],$res['fh_date'],$res['fh_kh']);

        $result[] = array('name'=>'授权库号：',
                                     'value' => empty($kh[0])?'无':implode(',' ,$kh),
                                     'type' =>'text'
                                    );    
        $map = array(
            'ht_stday' => array('elt',$res['fh_da']),
            'ht_enday' => array('egt',$res['fh_da']),
            'ht_khmc'  => $res['fh_client']
        );
        $wlfs = M('kk_ht')->where($map)->find();    
        $result[] = array('name'=>'运输方式：',
                                     'value'=>$wlfs['ht_wlfs'] ,
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['xg_reason'],
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
        $res = M('kk_fh')->field(true)->where($map)->find();
        $map = array('fh_num' => $res['fh_num']);
        return $this->field(true)->where($map)->getField('fh_kpy');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $clientname = M('kk_guest2')->field('g_name')->where(array('id' => $res['fh_client']))->find();
        $map  = array('id' => $id);
        $name = M('kk_fh')->field(true)->where($map)->find();
        $name = M('kk_guest2')->field('g_name')->where(array('id' => $name['fh_client']))->find();
        if($res['xg_person']) $res['fh_kpy'] = $res['xg_person'];
        $result = array(
            'sales'   => $res['fh_kpy'],
            'title2'  => '修改名称',
            'approve' => $name['g_name'],
            'notice'  => $res['xg_reason'],
            'date'    => $res['fh_da'],
            'title'   => '客户名称',
            'name'    => $clientname['g_name'], 
            'modname' => 'fh_edit_Apply',
            'stat'    => $this->getStat($id)
        );
        return $result;
    }

    // 获取状态
    public function getStat($id){
        // 先去环保找 没有再去建材找
        $where = array(
            'aid'      => $id,
            'mod_name' => 'fh_edit_Apply',
            'app_stat'=>array('egt',0),
             'app_stat'=>array('lt',3)
        );
        $res = M('kk_appflowproc')->field('app_stat,app_name,app_stage')->where($where)->order('app_stat asc')->select();
        // 撤销
        $map = array('id' => $id);
        $fh  = M('kk_fh')->field(true)->where($map)->find();
        if($fh['fh_stat4'] == 0) return 3;
        // 审批中
        $flag = 2;
        
        foreach($res as $v){
            if($v['app_stat'] == 1) return 1;
        }
        return $flag;
    }
    /**
     * 获取采购付款 供应商信息
     */
    public function getFhList(){
        $today    = date('Y-m-d',time()+24*3600);
        $yestoday = date('Y-m-d',time()-100*24*3600);
        $clientid = I('post.client');
        $id       = I('post.id');
        $page     = I('post.page');
        $page     = $page?$page:1;
        $complex  = array(
            '_logic'  => 'or',
            'fh_stat' => 1,
            'fh_flag' =>1
        );

        $where    = array(
            'fh_stat'  => 1,
            'fh_date'  => array('elt',$today),
            'fh_date'  => array('egt',$yestoday),
            '_complex' => $complex 
        );
        if($clientid) $where['fh_client'] = $clientid; 
        if($id)       $where['id'] = $id; 
        $data = M('kk_fh')->where($where)->order('fh_date DESC')->limit((($page-1)*20).",20")->select();
       
        $data = $this->fhInfo($data);
        return $data;
    }

    /**
     * 发货信息重构
     */
    public function fhInfo($data){
        if(!is_array($data)) return $data;
        foreach($data as $k => $v){
            $clientname = M('kk_guest2')->field('g_name')->where(array('id' => $v['fh_client']))->find();
            $data[$k]['fh_clientname'] = $clientname['g_name'];
            $data[$k]['fh_newdate'] = date('Y-m-d H:i',strtotime($v['fh_date']));
        }
        return $data;
    }

   /**
     * 合同客户获取
     * @param string $data  拼音缩写
     * @return array $res   合同用户结果
     */
    public function getCustomerList(){
        $today = date('Y-m-d',time());
        $data = I('math');
        $like = $data?"and (g_helpword like '%{$data}%' or g_name like '%{$data}%')":'';
        
        $sql  = "SELECT a.id AS id,
                        g_name as text,
                        g_khjc as jc,
                        b.ht_wlfs as wl,
                        b.ht_cate as fcate,
                        ht_bzfs   as type
                FROM
                    kk_guest2 AS a,
                    kk_ht AS b
                WHERE
                    a.id = b.ht_khmc
                AND ht_stday <= '{$today}'
                AND ht_enday >= '{$today}'
                AND ht_stat = 2
                {$like}
                GROUP BY
                    ht_khmc
                ORDER BY
                    g_name";
        $res = M()->query($sql);
        $res = $this->deal_with_customer($res);
        return $res;
    } 
    /**
     * 对合同客户数据进行处理
     */
    public function deal_with_customer($data){
        if(!is_array($data) || empty($data)) return $data;
        $check_data = array();
        foreach($data as $v){
            $key = $v['id'];
            $type = $v['type'] == '散装'?'散':'袋';
            $cate = $type.''.$v['fcate'];
            if(empty($check_data[$key]))
            {
                $v['cate'] = $cate;
                $check_data[$key] = $v;
            }else
            {
                if( strpos($check_data[$key]['cate'], $cate) === false) $check_data[$key]['cate'] = $check_data[$key]['cate'].'|'.$cate;
            }
        }
        $result = array();
        foreach( $check_data as $v){
            $result[] = $v;
        }
        return $result;
    }
   /**
     * 获取库号是否在一致
     */
    public function getkhInfo($user_id,$date,$fh_kh){
        // 判断品种
        if(!$user_id){
            $bzfs = I('post.bzfs');
            $cate = I('post.cate');
        }
        $user_id = $user_id ? $user_id : I('post.user_id');
        $date    = $date    ? $date    : I('post.date');
        $fh_kh   = $fh_kh   ? $fh_kh   : I('post.kh'); 
        $have = 0;
        if($bzfs){
            $today = date('Y-m-d',time());
            $map  = array(
                'ht_khmc'  => $user_id,
                'ht_stday' => array('elt',$today),
                'ht_enday' => array('egt',$today),
                'ht_stat'  => 2,
                'ht_bzfs'  => $bzfs,
                'ht_cate'  => $cate,
            );
            $bzfs = M('kk_ht')->where($map)->find(); 
            if(empty($bzfs)) $have=1;
        } 
        $res  = M('kk_config_kh_child')->where(array( 'clientid' => $user_id , 'dtime' => array('lt',$date)))->order('dtime desc')->find();
        $kh   = explode(',',$res['kh']);
        $flag = 1;
        if(!in_array($fh_kh,$kh)) $flag = 0;
        return array($kh,$flag,$have);
    }

    /**
     * 物流采购付款提交 
     */
    public function submit(){
        $today      = date('Y-m-d',time());
        $user       = session('name');
        $fhinfo     = I('post.fhinfo');
        $text       = I('post.text');
        $user_id    = I('post.user_id');
        $copyto_id  = I('post.copyto_id');
        $fh_wl      = I('post.fh_wl');
        // 简单校验
        if(!$user_id) return array('code' => 404,'msg' => '请选择客户名称');
        // 重复提交
        if(!M('kk_fhxg')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $count = M('kk_fhxg')->where(array('fh_num' => $fhinfo['fh_num']))->find();
        $addData = array(
            'fh_num'      => $fhinfo['fh_num'],
            'fh_client'   => $user_id,
            'fh_anname'   => $fhinfo['fh_anname'],
            'fh_cate'     => $fhinfo['fh_cate'],
            'fh_kh'       => $fhinfo['fh_kh'],
            'fh_snbh'     => $fhinfo['fh_snbh'],
            'fh_carnum'   => $fhinfo['fh_carnum'],
            'fh_thr'      => $fhinfo['fh_thr'],
            'fh_bs'       => $fhinfo['fh_bs'],
            'fh_kpy'      => $fhinfo['fh_kpy'],
            'fh_show'     => $fhinfo['fh_show'],
            'fh_qy'       => $fhinfo['fh_qy'],
            'fh_bzfs'     => $fhinfo['fh_bzfs'],
            'fh_wlfs'     => $fh_wl,
            'fh_pp'       => $fhinfo['fh_pp'],
            'fh_stat'     => 1,
            'fh_zl'       => $fhinfo['fh_zl'],
            'fh_bz'       => $fhinfo['fh_bz'],
            'xg_da'       => date('Y-m-d',time()),//
            'xg_date'     => date('Y-m-d H:i:s',time()),//
            'fh_da'       => $fhinfo['fh_da'],//
            'fh_date'     => $fhinfo['fh_date'],//
            'fh_dfzl'     => $fhinfo['fh_dfzl'],
            'fh_pz'       => $fhinfo['fh_pz'],
            'fh_mz'       => $fhinfo['fh_mz'],
            'fh_jz'       => $fhinfo['fh_jz'],
            'fh_stat4'    => 2,
            'fh_stat2'    => $fhinfo['fh_stat2'],
            'fh_wlname'   => $fhinfo['fh_wlname'],
            'fh_flag'     => $fhinfo['fh_flag'],
            'fh_bid'      => $fhinfo['fh_bid'],
            'fh_pass'     => $fhinfo['fh_pass'],//
            'fh_passtime' => $fhinfo['fh_passtime'],//
            'xg_reason'   => $text,//
            'xg_person'   => $user
        ); 
       
        if(empty($count)){
            $result = M('kk_fhxg')->add($addData);
        }else{
            $result = M('kk_fhxg')->where(array('fh_num' => $fhinfo['fh_num']))->save($addData);
        }
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        $result = $fhinfo['id'];
        M('kk_fh')->where(array('id' =>$result))->setField('fh_stat4',2);
        // 判断是否需要品管审核
        $res  = M('kk_config_kh_child')->where(array( 'clientid' => $user_id , 'dtime' => array('lt',$fhinfo['fh_date'])))->order('dtime desc')->find();
        $kh   = explode(',',$res['kh']);
        if(!in_array($fhinfo['fh_kh'],$kh)){
            $pgData = array(
                'id'      => $result,
                'aid'     => $result,
                'flag'    => 1,
                'modname' => 'fh_edit_Apply'
            );
            M('kk_fhxg_pg')->add($pgData);
        }
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('KkAppcopyto')->copyTo($copyto_id,'fh_edit_Apply', $result,1);
        }
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('fh_edit_Apply', $result, $salesid, 'kk');

        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }
    public function app_porc(){
        $system = I('post.system');
        $aid    = I('post.aid');
        $map    = array('id' => $aid);
        $res    = M($system.'_fhxg_pg')->where($map)->find();
        return empty($res)?0:1;
    }
}