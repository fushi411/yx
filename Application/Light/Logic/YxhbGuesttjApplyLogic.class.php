<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbGuesttjApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_guest_tj';

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
        $result['content'][] = array('name'=>'系统类型：',
            'value'=>'环保客户调价',
            'type'=>'date',
            'color' => 'black'
        );
         $result['content'][] = array('name'=>'申请日期：',
                                    'value'=>$res['date'],
                                    'type'=>'date',
                                    'color' => 'black'
                                );

        $result['content'][] = array('name'=>'客户调价：',
                                    'value'=>'查看客户调价',
                                    'type'=>'date',
                                    'color' => '#337ab7'
                                );
        $result['mydata'] = $this->getTjData($id);

        $result['imgsrc'] = '';
        $result['applyerID'] =  $res['applyuser'];                                               //申请者的id
        $result['applyerName'] = D('YxhbBoss')->getNameFromID($res['applyuser']);               //申请者的姓名
        $result['stat'] = $this->transStat($res['id']);                                        //审批状态
        return $result;
    }

    // 状态值转换
    public function transStat($id){
        $res = $this->record($id);
        if($res['stat'] == 0) return 0;
        if($res['stat'] == 2) return 1;
        $map = array(
            'aid'=>$id,
            'mod_name'=>'GuesttjApply'
        );
        $res = M('yxhb_appflowproc')->field('app_stat')->where($map)->select();
        $stat = array();
        foreach ($res as $value){
            $stat[] = $value['app_stat'];
        }
        if (in_array(0, $stat))  return 2; //审批中
        if (in_array(1, $stat))  return 2; //退审 先2 后1
        return 1;                                  //审批通过
    }

    public function getTjData($aid){
        $field = 'b.*';
        $map = array(
            'a.id' => $aid,
        );
        $system = 'yxhb';
        $model  = D(ucfirst($system).'Guest2');
        $data = M('yxhb_guest_tj a')
                ->join('yxhb_tj b on a.relationid = b.relationid')
                ->field($field)
                ->where($map)
                ->order('tj_client desc,tj_bzfs desc,tj_cate')
                ->select();

        $client = M('yxhb_guest_tj a')
                ->join('yxhb_tj b on a.relationid = b.relationid')
                ->join('yxhb_guest2 c on b.tj_client = c.id')
                ->field('tj_client')
                ->where($map)
                ->group('c.reid,tj_client')
                ->select();
        
        $tj_da = $data[0]['tj_da'];
        // 未调价
        $map = array(
            'ht_stat'  => 2,
            'ht_stday' => array('elt',$tj_da),
            'ht_enday' => array('egt',$tj_da),
            'g_khlx'   => array(array('eq','经销商'),array('eq','直供单位'),'or'),
        ); 
        $modify = M($system.'_guest2 as a')
                ->join($system.'_ht as b on a.id = b.ht_khmc')
                ->field('a.id as tj_client')
                ->where($map)
                ->group('a.id')
                ->order('g_khlx,reid')
                ->select();
        $check = array();
        if(count($client) < count($modify)){
            $clientArr = $this->reData($client);
            $modifyArr = $this->reData($modify);
            $check = array_diff($modifyArr,$clientArr);
            $client_merge = array();
            foreach ($check as  $v) {
                $client_merge[] = array('tj_client' => $v); 
            }
        }
        $guest = $this->getAllGuestName();  
        $pp = array(
            array('pp' => 'S95','bz' => '散装'),
            array('pp' => 'F85','bz' => '散装'),
        );

        $client   = array_merge($client,$client_merge);
        $tj_da    = $data[0]['tj_da'];
        $temp     = array();
        $unModify = array();

        foreach ($client as  $value) {
            $tj_client = $value['tj_client'];
            foreach ($pp as  $val) {
                $flag = 0;
                foreach ($data as $k=>$vo) {
                    if($vo['tj_cate'] == $val['pp'] && $vo['tj_bzfs'] == $val['bz'] && $vo['tj_client'] == $tj_client){
                        $vo['g_name'] = $guest[$tj_client];
                        $vo['cate']   = $vo['tj_cate'];
                        // 当前价格
                        $vo['now'] = preg_replace('/\.0+$/', '',bcsub($vo['tj_dj'],$vo['delta_dj'],2));
                        // 调整后价格
                        $color = (int) $vo['delta_dj']>0? 'red':'green';
                        $arrow = (int) $vo['delta_dj']>0? '&uarr;':'&darr;';
                        $tmpl  = "<span style='color:".$color."'>(".$vo['delta_dj']."{$arrow})</span>";
                        $vo['dj'] = $vo['tj_dj'].$tmpl;
                        $vo['tj_yf'] = $vo['tj_yf'] == 0?'自提':$vo['tj_yf'];
                        $temp[$tj_client]['g_name']  = $model->getName($tj_client);  
                        $temp[$tj_client]['date']    = '调价日期：'.$vo['tj_stday'];  
                        $temp[$tj_client]['child'][] = $vo;    
                        $flag = 1;
                        unset($data[$k]);
                    }  
                }
                if($flag == 1) continue;
                $bz   = $val['bz'] == '散装'?'(散)':'(袋)';
                $show = $val['pp'].$bz;
                $wlfs = $this->getWlfs($value['tj_client'],$tj_da,$val['pp'],$system);
                $yf   = $this->getfhyf($value['tj_client'],$tj_da,$val['pp'],$val['bz'],$wlfs);
                $yf   = ($yf == '-'|| $wlfs == '自提')?$wlfs==null?$yf:$wlfs:$yf;
                if($yf == 0) $yf = '自提';
                $item = array(
                    'cate' => $show,
                    'now'  => $this->getfhdj($value['tj_client'],$tj_da,$val['pp'],$val['bz'],$wlfs),
                    'dj'   => '-',
                    'tj_yf' => $yf,
                );
                if($item['now'] == '-') continue;
                if( in_array($value['tj_client'],$check) ){
                    $unModify[$tj_client]['g_name']  = $model->getName($tj_client);
                    $unModify[$tj_client]['child'][] = $item;
                }else{
                    $temp[$value['tj_client']]['child'][] = $item; 
                }
            }
        }
        return array('modify' => $temp,'unmodify' => $unModify,'flag' => empty($unModify)?0:1);
    }

    // 用户数组重组
    public function reData($data){
        $temp = array();
        foreach( $data as $vo ){
            $temp[] = $vo['tj_client'];
        }
        return $temp;
    }
    public function getAllGuestName(){
        $data = M('yxhb_guest2')->select();
        $temp = array('无此客户');
        foreach($data  as $val){
            $temp[$val['id']] = $val['g_name'];
        }
        return $temp;
    }
    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('id' => $id);
        $this->field(true)->where($map)->setField('stat',0);
        $data = $this->where($map)->find();
        return M('yxhb_tj')->where(array('relationid' => $data['relationid']))->setField('tj_stat',0);
    }

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result[] = array('name'=>'系统类型：',
            'value'=>'环保客户调价',
            'type'=>'string'
        );
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['dtime'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['date'])), 
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
        $map = array('id' => $id);
        return $this->field(true)->where($map)->getField('jbr');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $temp = array(
            array('title' => '申请时间' , 'content' => date('Y-m-d',strtotime($res['date'])) ),
            array('title' => '调价日期' , 'content' => date('Y-m-d',strtotime($res['date']))  ),
            array('title' => '相关说明' , 'content' => '无' ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $this->transStat($res['id']),
            'applyerName'    => D('YxhbBoss')->getNameFromID($res['applyuser']),
        );
        return $result;
    }

    // 获取合同有效客户
    public function getCustomerList(){
        $key    = I('get.math');
        $system = I('get.system');
        $date   = I('get.date');

        $field  = 'a.id as id,g_name as text,g_khjc as jc';
        $map    = array(
                'ht_stat'  => 2,
                'ht_stday' => array('elt',$date),
                'ht_enday' => array('egt',$date),
                'g_khlx'   => array(array('eq','直供单位'),array('eq','经销商'),'or'),
                'g_name'   => array('like',"%$key%"),
                'g_helpword' => array('like',"%$key%"),
            ); 
        $dealer = M($system.'_guest2 as a')
                ->join($system.'_ht as b on a.id = b.ht_khmc')
                ->field($field)
                ->where($map)
                ->group('a.id')
                ->order('reid')
                ->select();
        return $dealer;
    }
 /**
     * 提交
     */
    public function submit(){
        $data = I('post.data');
        $date = I('post.date');
        $copyto_id = I('post.copyto_id');
        // 检查是否有修改
        $save = array();
        $relationid = $this->getRelationid();
        foreach ($data as $value) {
            foreach($value as $val){
                foreach($val['data'] as $vo){
                    if(!empty($vo['xgdj'])){
                        $dh = 'TJ'.$relationid.count($insert);
                        $insert[]= array(
                            'tj_da'      => date('Y-m-d H:i:s'),
                            'tj_stday'   => $date,
                            'tj_dj'      => $vo['xgdj'],
                            'tj_yf'      => $vo['xgyf']==''?0:$vo['xgyf'],
                            'tj_cate'    => $vo['pp'],
                            'tj_stat'    => 1,
                            'tj_bzfs'    => $vo['bz'],
                            'tj_pp'      => '福源鑫',
                            'tj_enday'   => date('Y-m-d', strtotime(date('Y-m-01') . ' +2 month -1 day')),
                            'tj_client'  => $val['client'],
                            'delta_dj'   => $vo['xgdj']-$vo['dj'],
                            'delta_yf'   => $vo['xgyf']-$vo['yf'],
                            'rdy'        => session('name'),
                            'relationid' => $relationid,
                            'dh'         => $dh,
                        );
                    }
                }
            }
        }
        if(empty($insert)) return array('code' => 404,'msg' =>'无修改,无需提交');
        if(!M('yxhb_guest_tj')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        M('yxhb_tj')->addAll($insert);
        $tj = array(
            'date'       => date('Y-m-d'),
            'relationid' => $relationid,
            'content'    => json_encode($insert),
            'applyuser'  => session('yxhb_id'),
            'jbr'        => session('name'),
            'dtime'      => date('Y-m-d H:i:s'),
            'stat'       => 1,
        );
        $result = M('yxhb_guest_tj')->add($tj);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('yxhbAppcopyto')->copyTo($copyto_id,'GuesttjApply', $result);
        }
        // 签收通知
        $wf = A('WorkFlow');
        $salesid = session('yxhb_id');
        $res = $wf->setWorkFlowSV('GuesttjApply', $result, $salesid, 'yxhb');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }

    public function getRelationid(){
        $data = M('yxhb_guest_tj')->where(array( 'date' => date('Y-m-d') ))->count();
        $lsdcount = $data + 1;
        return date('Ymd').str_pad($lsdcount,3,'0',STR_PAD_LEFT);
    }

    public function getTjInfo(){
        $date   = I('post.date');
        $system = I('post.system');
        $client = I('post.user_id');

        $field  = 'a.id as id,g_name,g_khlx,reid';
        $model  = D(ucfirst($system).'Guest2');
        $pp = array(
            array('pp' => 'S95','bz' => '散装'),
            array('pp' => 'F85','bz' => '散装'),
        );
        $kh = array(
            array('k' => 'jxs','name' => '经销商'),
            array('k' => 'zgdw','name' => '直供单位'),
        );
        $guest = $this->getAllGuest();
        $result = array();
        foreach($kh as $value){
            $map    = array(
                'ht_stat'  => 2,
                'ht_stday' => array('elt',$date),
                'ht_enday' => array('egt',$date),
                'g_khlx'   => $value['name'],
            ); 
            $dealer = M($system.'_guest2 as a')
                    ->join($system.'_ht as b on a.id = b.ht_khmc')
                    ->field($field)
                    ->where($map)
                    ->group('a.id')
                    ->order('reid')
                    ->select();
            $res = array();
            foreach($dealer as $val){
                if(!empty($guest[$val['id']])) continue;
                if(!empty($client) && $val['id'] != $client) continue;
                $temp = array();
                $temp['fg_name'] = $model->getParentName($val['id']);
                $temp['g_name']  = $val['g_name'];
                $temp['tj_date'] = $this->getTjDate($val['id'],$date);
                $temp['client']  = $val['id'];
                foreach($pp as $vo){
                    $wlfs = $this->getWlfs($val['id'],$date,$vo['pp'],$system);
                    $dj   = $this->getfhdj($val['id'],$date,$vo['pp'],$vo['bz'],$wlfs);
                    $yf   = $this->getfhyf($val['id'],$date,$vo['pp'],$vo['bz'],$wlfs);
                    if($dj == '-') continue;
                    $yf     = ($yf == '-'|| $wlfs == '自提')?($wlfs==null?$yf:$wlfs):$yf;
                    $yfflag = ($yf == '-'|| $wlfs == '自提')?0:1;
                    if($yf === 0) $yf = '自提';
                    $show = $vo['pp'];
                    $temp['data'][] = array(
                        'pp'     => $vo['pp'],
                        'bz'     => $vo['bz'],
                        'show'   => $show,
                        'dj'     => preg_replace('/\.0+$/', '',$dj),
                        'djflag' => $dj == '-'?0:1, 
                        'yf'     => $yf,
                        'yfflag' => $yfflag, 
                        'wlfs'   => $wlfs,
                        'xgyf'   => '',
                        'xgdj'   => '',
                    );
                }
                $res[] = $temp;
            }
            $result[$value['k']] = $res;
        }
       return $result;
    }

    public function getAllGuest(){
        $data = M('yxhb_guest2')->select();
        $temp = array('无此客户');
        foreach($data  as $val){
            if($val['reid'] == 0) continue;
            $temp[$val['reid']][] = $val['id'];
        }
        return $temp;
    }

    private function getWlfs($client,$date,$cate,$system){
        $query = "select ht_wlfs from {$system}_ht where ht_khmc='".$client."' and ht_stat='2' and ht_stday<='$date' and ht_enday>='$date' and ht_cate='{$cate}' order by ht_stday desc";
        $res = M()->query($query);
        return $res[0]['ht_wlfs'];
    }

    private function getfhdj($client,$day,$cate,$bzfs,$wlfs){
        if($bzfs=='散装'){
             //所有调价和合同合并查询初始价格
            $query="select dj from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_wlfs as wlfs from yxhb_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_wlfs as wlfs from yxhb_tj  ) as t where stday<='$day' and bzfs='$bzfs' and stat=2 and client='".$client."' and cate='".$cate."' and wlfs='".$wlfs."' order by stday desc";
        }else{
        //所有调价和合同合并查询初始价格
            $query="select dj from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_wlfs as wlfs from yxhb_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_wlfs as wlfs from yxhb_tj  ) as t where stday<='$day' and bzfs!='散装' and stat=2 and client='".$client."' and cate='".$cate."' and wlfs='".$wlfs."' order by stday desc";
        }
        $data = M()->query($query);
        $rowcount = count($data);
        if($rowcount>0){
            $row = $data[0];
            return $row['dj'];
        }else{
            return '-';
        }
    }

    private function getfhyf($client,$day,$cate,$bzfs,$wlfs){
        if($bzfs=='散装'){
             //所有调价和合同合并查询初始价格
             $query="select yf from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_wlfs as wlfs from yxhb_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_wlfs as wlfs from yxhb_tj  ) as t where stday<='$day' and bzfs='$bzfs' and stat=2 and client='".$client."' and cate='".$cate."' and wlfs='".$wlfs."' order by stday desc";
        }else{
             //所有调价和合同合并查询初始价格
             $query="select yf from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_wlfs as wlfs from yxhb_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_wlfs as wlfs from yxhb_tj  ) as t where stday<='$day' and bzfs!='散装' and stat=2 and client='".$client."' and cate='".$cate."' and wlfs='".$wlfs."' order by stday desc";
        }
        $data = M()->query($query);
        $rowcount = count($data);
        if($rowcount>0){
            $row = $data[0];
            return $row['yf'];
        }else{
            return '-';
        }
    }
    
    private function getTjDate($clientid,$day)
    {
      if (!empty($clientid)) {
        $res = M()->query("select tj_stday from yxhb_tj where tj_client='$clientid' and tj_stday<='$day' and  tj_stat='2' order by tj_stday desc");
        $result = $res[0]['tj_stday'];
        if (empty($result)) {
          $res = M()->query("select ht_qday from yxhb_ht where ht_khmc='$clientid' and ht_qday<='$day' and  ht_stat='2' order by ht_qday desc");
          $result = $res[0]['ht_qday'];
        }
      } else {
        $result="无提货信息";
      }
      return $result;
    }
    
}