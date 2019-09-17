<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkGuestJsApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_js';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('pid' => $id);
        return $this->field(true)->where($map)->find();
    }

    public function getTableName()
    {
        return $this->trueTableName;
    }

    public function recordContent($id)
    {
        $jslx = array('1'=>'磅差调整', '3'=>'其他', '4'=>'其他', '5'=>'价差调整', '6'=>'业务费调整',7 => '应收坏账调整',8=>'预收呆账调整');
        $res = $this->record($id);
        $result = array();
        $result['content'][] = array('name'=>'系统类型：',
                                     'value'=>'建材客户结算',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['jl_date'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
         
        $result['content'][] = array('name' => '结算详情：',
                                    'value' => '查看结算详情',
                                    'type'  => 'date',
                                    'id'    => 'btnhp',
                                    'color' => '#337ab7'
                                    );              
        $result['content'][] = array('name'=>'结算类型：',
                                     'value'=>$jslx[$res['jslx']],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>D('kk_guest2')->getParentName($res['client']),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'时间范围：',
                                     'value'=>$res['js_stday'].' 至 '.$res['js_enday'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'执行日期：',
                                     'value'=>$res['js_date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );

        $data = M('kk_js')->field('sum(js_je) as js_je')->where(array('pid' => $id))->find();
        $color = $data['js_je'] > 0 ? 'black':'#f12e2e';
        $result['content'][] = array('name'=>'结算金额：',
                                    'value'=> "&yen;".number_format($data['js_je'],2,'.',',')."元",
                                    'type'=>'string',
                                    'color' => $color.';font-weight: 600;'
                                );
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['js_bz']?$res['js_bz']:'无',
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        $result['mydata']['js_jslx']  = $res['jslx'];
        $result['mydata']['jslx']     = $jslx[$res['jslx']];
        $result['mydata']['g_name']   = D('kk_guest2')->getParentName($res['client']);                      
        $result['mydata']['js_stday'] = $res['js_stday'];
        $result['mydata']['js_enday'] = $res['js_enday'];
        $result['mydata']['js_date']  = $res['js_date'];

        if($res['jslx'] == 5 ){
            $result['mydata']['data'] = $this->getJcHtml($id);
            $result['mydata']['tzje'] = "<span style='color:$color'>&yen;".number_format($data['js_je'],2,'.',',')."元</span>";
        }elseif($res['jslx'] == 4){
            $result['mydata']['je'] = $data['js_je'];
            $result['mydata']['jestr'] = cny($data['js_je']);
        }else{
            $info = $this->getClientInfoDate($res['client'],'kk',$res['jl_date']);
            $result['mydata']['ysje'] = $info['format'];
            $result['mydata']['color'] = $info['color'];
            $result['mydata']['tzje'] = $data['js_je'];
            $result['mydata']['tzye'] = bcsub($data['js_je'],$info['tzje']);
        }
        $result['imgsrc'] = '';
        $result['applyerName'] = $res['rdy'];
        $result['applyerID'] = D('kk_boss')->getIDFromName($res['rdy']);
        $result['stat'] = $this->transStat($res['js_stat']);
        return $result;
    }

    // 状态值转换
    public function transStat($stat){
        $statArr = array(
            0 => 0,
            1 => 2,
            2 => 1,
        );
        return $statArr[$stat];
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
         $map = array('pid' => $id);
         return $this->field(true)->where($map)->setField('js_stat',0);
    }

    /**
     * 拒收
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function refuseRecord($id)
    {
        $map = array('pid' => $id);
        return $this->field(true)->where($map)->setField('js_stat',0);
    }

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result[] = array('name'=>'系统类型：',
            'value'=>'建材客户结算',
            'type'=>'string'
        );
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['jl_date'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['js_date'])), 
                                     'type'=>'date'
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
        $map = array('pid' => $id);
        return $this->field(true)->where($map)->getField('rdy');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $jslx = array('1'=>'磅差调整', '3'=>'手续费', '4'=>'其他', '5'=>'价差调整', '6'=>'业务费调整',7 => '应收坏账调整',8=>'预收呆账调整');
        $temp = array(
            array('title' => '结算类型' , 'content' => $jslx[$res['jslx']] ),
            array('title' => '客户名称' , 'content' =>  D('kk_guest2')->getParentName($res['client'])),
            array('title' => '相关说明' , 'content' => $res['js_bz']?$res['js_bz']:'无'  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $this->transStat($res['js_stat']),
            'applyerName'    => $res['rdy'],
        );
        return $result;
    }
    /**
     * 获取价差详情数据
     */
    public function getJcHtml($id){
        $data   =  $this->where(array('pid' => $id))->order('client')->select();
        $temp = array();
        foreach($data as $vo){
            $stday  = $vo['js_stday'];
            $enday  = $vo['js_enday'];
            $client = $vo['client'];
            $g_name = M('kk_guest2')->where(array('id' => $client))->find();
            $res  = $this->getJc($stday,$enday,$client);
            foreach($res as $val){
                if( $val['bzfs']== $vo['js_bzfs'] && $val['pp'] == $vo['js_cate'] ){
                    $xgdj = $vo['js_zl']>0?bcsub($val['dj'],$vo['js_dj'],2):bcadd($val['dj'],$vo['js_dj'],2);
                    $xjColor = $vo['js_je']>0?'black':'#f12e2e';
                    $val['djformat'] = '&yen;'.preg_replace('/\.0+$/', '', number_format($val['dj'],2,'.',','));
                    $val['xgdj'] = '&yen;'.preg_replace('/\.0+$/', '', number_format( $xgdj,2,'.',',')).'<span style="color:#f12e2e">('.$vo['js_dj'].')</span>';
                    $val['xgyf'] = $vo['js_yf']==0?($val['yf'] == 0 ? '自提':$val['yf']):$vo['js_yf'];
                    $val['yf']   = $val['yf'] == 0 ? '自提':$val['yf'];
                    $val['xgzl'] = preg_replace('/\.0+$/', '',number_format($vo['js_zl'],2,'.',','));
                    $val['xgxj'] = '<span style="color:'.$xjColor.'">&yen;'.preg_replace('/\.0+$/', '',number_format($vo['js_je'],2,'.',',')).'</span>';
                    $val['zlformat'] = preg_replace('/\.0+$/', '',$val['zlformat']);
                    $val['xjformat'] = '&yen;'.$val['xjformat'];
                    $temp[$client]['g_name'] = $g_name['g_name'];
                    $temp[$client]['data'][] = $val;
                }
            }
        }
        return $temp;
    }
    /**
     * 获取用户范围
     */
    public function getCustomerList(){ 
        $data = I('math');
        $res = D('Guest')->getGuest('kk',$data,'GuestJsApply');
        return $res;
    }

    /**
     * 获取用户应收
     */
    public function getClientInfo(){
        $client = I('post.user_id');
        $system = I('post.system');
        $data = M($system.'_guest_accounts_receivable')
                ->field('qmje,reid')
                ->where(array('clientid' => $client))
                ->order('date desc')
                ->find();
        $data['color']   = $data['qmje'] <0 ?'red':'black';
        $data['tzje']    = -$data['qmje'];
        $data['format']  = number_format($data['qmje'],2,'.',',');
        $data['format2'] = number_format(-$data['qmje'],2,'.',',');
        return $data;
    }

    public function getClientInfoDate($client,$system,$date){
        $data = M($system.'_guest_accounts_receivable')
                ->field('qmje,reid')
                ->where(array('clientid' => $client,'date' => array('elt' => $date)))
                ->order('date desc')
                ->find();
        $data['color']   = $data['qmje'] <0 ?'red':'black';
        $data['tzje']    = -$data['qmje'];
        $data['format']  = number_format($data['qmje'],2,'.',',');
        $data['format2'] = number_format(-$data['qmje'],2,'.',',');
        return $data;
    }
    /**
     * 获取价差内容
     */
    public  function getJcData($stday,$enday,$client){
        $stday  = $stday?$stday:I('post.stday');
        $enday  = $enday?$enday:I('post.enday');
        $client = $client?$client:I('post.user_id');
        // 总客户获取
        $child = M('kk_guest2')->field('id,g_name')->where(array('_string' => "reid=$client or id=$client" , 'g_stat3' => 1))->select();
        $childData = array();
        foreach($child as $value){
            $client = $value['id'];
            $res = $this->getJc($stday,$enday,$client);
            if(empty($res)) continue;
            $childData[$client]['g_name'] = $value['g_name'];
            $childData[$client]['data'] = $res;
        }
        return $childData;
      }
    
    // 获取区间
    public function getJc($stday,$enday,$client){
        $map = array(
            'ht_khmc' => $client,
            'fh_stat' => 1,
            'fh_da'   => array('between',array($stday,$enday)),
            'fh_bzfs' => array(array('eq','纸袋'),array('eq','编织袋'),array('eq','散装'),'or'),
        );
        // 查询发货品种和包装
        $data = M('kk_fh')->field("fh_cate,if(fh_bzfs='散装','散装','袋装') as fh_bzfs,fh_bzfs as bzfs")->where($map)->group('fh_cate,fh_bzfs')->select();
        $temp = array();
        // 调价查询
        foreach( $data as $k => $val){
            // 初始价格
            list($csdj,$csyf,$csje) = $this->getFirstPrice($val['fh_cate'],$val['fh_bzfs'],$client,$stday);
            $map = array(
                'tj_pp'    => '福源鑫',
                'tj_stday' => array('between',array($stday,$enday)),
                'tj_stat'  => 2,
                'tj_client'=> $client,
                'tj_cate'  => $val['fh_cate'],
                'tj_bzfs'  => $val['fh_bzfs'], 
            );
            $tj = M('kk_tj')
                ->field('tj_dj as dj,tj_yf as yf,tj_stday as stday')
                ->where($map)
                ->select();
            if(empty($tj)){ 
                // 无调价
                $temp[$k][] = array(
                    'st' => $stday,
                    'end' => $enday,
                    'pp' => $val['fh_cate'],
                    'bz' => $val['fh_bzfs'],
                    'bzfs' => $val['bzfs'],
                    'dj' => round(floatval($csdj),2),
                    'yf' => round(floatval($csyf),2),
                    'je' => round(floatval($csje),2),
                    'zl' => $this->getTotalWeight($val['fh_cate'],$val['fh_bzfs'],$client,$stday,$enday),
                );
            }else{ 
                //有调价
                $temp[$k][] = array(
                    'st' => $stday,
                    'end' => date('Y-m-d',strtotime($tj[0]['stday'].' -1 day')),
                    'pp' => $val['fh_cate'],
                    'bz' => $val['fh_bzfs'],
                    'bzfs' => $val['bzfs'],
                    'dj' => round(floatval($csdj),2),
                    'yf' => round(floatval($csyf),2),
                    'je' => round(floatval($csje),2),
                    'zl' => $this->getTotalWeight($val['fh_cate'],$val['fh_bzfs'],$client,$stday,date('Y-m-d',strtotime($tj[0]['stday'].' -1 day'))),
                );
                $tempStday = $tj[0]['stday'];
                foreach($tj as $key => $vo){
                    $tempEnday = empty($tj[$key+1])?$enday: date('Y-m-d',strtotime($tj[$key+1]['stday'].' -1 day'));
                    if(strtotime($tempEnday) < strtotime($tempStday)) continue;
                    $csdj=$vo['dj'];
                    $csyf=$vo['yf'];
                    $csje=$csdj+$csyf;
                    $temp[$k][] = array(
                        'st' => $tempStday,
                        'end' => $tempEnday,
                        'pp' => $val['fh_cate'],
                        'bz' => $val['fh_bzfs'],
                        'bzfs' => $val['bzfs'],
                        'dj' => round(floatval($csdj),2),
                        'yf' => round(floatval($csyf),2),
                        'je' => round(floatval($csje),2),
                        'zl' => $this->getTotalWeight($val['fh_cate'],$val['fh_bzfs'],$client,$tempStday,$tempEnday),
                    );
                    $tempStday = $tj[$key+1]['stday'];
                }
            }
        }
        $res= array();
        foreach($temp as $key => $val){
            foreach($val as $vo){
                if($vo['zl'] == 0) continue;
                $bzfs     = $vo['bzfs'] == '散装'?'(散)':'(袋)';
                $vo['xj'] = round(floatval(bcadd($vo['zl']*$vo['dj'],0,4)),2);
                $vo['zl'] = round(floatval(bcadd($vo['zl'],0,4)),2);
                $vo['ppformat'] = $vo['pp'].$bzfs;
                $vo['djformat'] = '&yen;'.number_format($vo['dj'],2,'.',',');
                $vo['yfformat'] = '&yen;'.number_format($vo['yf'],2,'.',',');
                $vo['jeformat'] = '&yen;'.number_format($vo['je'],2,'.',',');
                $vo['zlformat'] = number_format($vo['zl'],2,'.',',');
                $vo['xjformat'] = number_format($vo['xj'],2,'.',',');
                $vo['client']   = $client;
                $res[] = $vo;
            }
        }
        return $res;
    }

    // 获取区间内的发货数量
    public function getTotalWeight($cate,$bzfs,$client,$stday,$enday){
        $mapBzfs = $bzfs == '袋装'?array(array('eq','纸袋'),array('eq','编织袋'),'or'):'散装';
        $map = array(
            'fh_da'     => array(array('egt',$stday),array('elt',$enday),'and'),
            'fh_stat'   => 1,
            'fh_client' => $client,
            'fh_cate'   => $cate,
            'fh_bzfs'   => $mapBzfs,
        );
        $weight = M('kk_fh')
                ->field('sum(fh_zl) as weight')
                ->where($map)
                ->select();
        return empty($weight[0]['weight'])?0:$weight[0]['weight'];
    } 
  
    // 获取所有的价格区间
    public function getFirstPrice($cate,$bzfs,$client,$stday){
        $map = array(
            'tj_pp'    => '福源鑫',
            'tj_stday' => array('elt',$stday),
            'tj_stat'  => 2,
            'tj_client'=> $client,
            'tj_cate'  => $cate,
            'tj_bzfs'  => $bzfs, 
        );
        $tj = M('kk_tj')
            ->field('tj_dj as dj,tj_yf as yf,tj_stday as stday')
            ->where($map)
            ->order('tj_stday desc')
            ->find();
            // 无调价 查询合同
        if(empty($tj)) {
            $map = array(
                'ht_pp'    => '福源鑫',
                'ht_stday' => array('elt',$stday),
                'ht_stat'  => 2,
                'ht_khmc'  => $client,
                'ht_cate'  => $cate,
                'ht_bzfs'  => $bzfs, 
            );
            $tj = M('kk_ht')
                ->field('ht_dj as dj,ht_yf as yf,ht_stday as stday')
                ->where($map)
                ->order('ht_stday desc')
                ->find();
            // 无合同 初始时间后得
            if(empty($tj)){
                $map['ht_stday'] = array('gt',$stday);
                $tj = M('kk_ht')
                    ->field('ht_dj as dj,ht_yf as yf,ht_stday as stday')
                    ->where($map)
                    ->order('ht_stday')
                    ->find();
            }
        }
        $csdj=$tj['dj'];
        $csyf=$tj['yf'];
        $csje=$csdj+$csyf;
        return array($csdj,$csyf,$csje);
     }
     // 提交 
     public function submit(){
        $jslx = I('post.jslx');
        switch($jslx){
            case '5':
                // 显示价差
                return  $this->submitJc();
                break;
            default:
                // 显示呆账坏账 其他
                return   $this->submitDz();
        }
     }
   

    // 提交 呆账坏账 提交其他
    public function submitDz(){
        $money = I('post.money');
        $jslx  = I('post.jslx');
        $text  = I('post.text');
        $stday = date('Y-m-d');
        $enday = date('Y-m-d');
        $js_date   = I('post.js_date');
        $user_id   = I('post.user_id');
        $copyto_id = I('post.copyto_id');
        $system    = I('post.system');
        // 流程检验
        // $pro = D('kkAppflowtable')->havePro('GuestJsApply','');
        // if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        $dh  = $this->getDh();
        $pid = str_replace('JS','',$dh);
        $insert = array(
            'dh'       => $dh,
            'js_date'  => $js_date,
            'js_je'    => $money,
            'client'   => $user_id,
            'jl_date'  => date('Y-m-d H:i:s'),
            'rdy'      => session('name'),
            'js_stat'  => 1,
            'jslx'     => $jslx,
            'js_bz'    => $text,
            'js_stday' => $stday,
            'js_enday' => $enday,
            'pid'      => $pid,
        ); 
        if(!M('kk_js')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $result = M('kk_js')->add($insert);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('kkAppcopyto')->copyTo($copyto_id,'GuestJsApply', $pid);
        }
        // 签收通知
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('GuestJsApply', $pid, $salesid, 'kk');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$pid);
    }

    // 提交 价差
    public function submitJc(){
        $data  = I('post.data');
        $jslx  = I('post.jslx');
        $text  = I('post.text');
        $stday = I('post.stday');
        $enday = I('post.enday');
        $js_date   = I('post.js_date');
        $user_id   = I('post.user_id');
        $copyto_id = I('post.copyto_id');
        $system    = I('post.system');
        // 流程检验
        // $pro = D('kkAppflowtable')->havePro('GuestJsApply','');
        // if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        $dh  = $this->getDh();
        $pid = str_replace('JS','',$dh);
        $insert = array();
        foreach($data as $vo){
            foreach($vo['data'] as $val){
                if($val['xj'] == 0) continue;
                $insert[] = array(
                    'dh'       => $dh,
                    'js_date'  => $js_date,
                    'js_je'    => $val['xj'],
                    'client'   => $val['client'],
                    'jl_date'  => date('Y-m-d H:i:s'),
                    'rdy'      => session('name'),
                    'js_bzfs'  => $val['bzfs'],
                    'js_cate'  => $val['pp'],
                    'js_pp'    => '福源鑫',
                    'js_stday' => $val['st'],
                    'js_enday' => $val['end'],
                    'js_stat'  => 1,
                    'js_dj'    => $val['dj'],
                    'js_yf'    => $val['yf'],
                    'js_zl'    => $val['zl'],
                    'jslx'     => $jslx,
                    'js_bz'    => $text,
                    'pid'      => $pid,
                );
            }
        }
        if(empty($insert))  return array('code' => 404,'msg' => '无修改,无需提交');
        if(!M('kk_js')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $result = M('kk_js')->addAll($insert);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('kkAppcopyto')->copyTo($copyto_id,'GuestJsApply', $pid);
        }
        // 签收通知
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('GuestJsApply', $pid, $salesid, 'kk');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$pid);
    }

    // 获取单号
    public function getDh(){
        $today = date('Y-m-d',time());
        $map = array(
            'date_format(jl_date, "%Y-%m-%d" )' => $today,
        );
        $data = M('kk_js')->where($map)->group('dh')->select();
        $lsdcount = count($data) + 1;
        return 'JS'.date('Ymd',time()).str_pad($lsdcount,3,'0',STR_PAD_LEFT);
    }
}