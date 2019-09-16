<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 建材客户调价查看
 * @author 
 */

class KkGuesttjApplyLogic extends Model {

    protected $trueTableName = 'kk_guest_tj';  // 实际表名（查询基本信息）      kk_appflowproc 查询时间、审核意见、审批状态   kk_appflowtable查询关注点
    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array(
            'id' => $id,
        );
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
            'value'=>'建材客户调价',
            'type'=>'string',
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
//        foreach ($html as $value){
//            $result['content'][] = array('name'=>'审批信息：',
//                'value'=>$value,
//                'type'=>'string',
//                'color' => 'black'
//            );
//        }
//        $result['abc'] = 'view_guest_tj_info.php?id='.$res['id'].'&type=view';                 //把路径传递出去
        $result['imgsrc'] = '';
        $result['applyerID'] =  $res['applyuser'];                                               //申请者的id
        $result['applyerName'] = D('KkBoss')->getNameFromID($res['applyuser']);                 //申请者的姓名
        $result['stat'] = $this->transStat($res['id']);                                        //审批状态

        return $result;
    }

    public function getAllGuestName(){
        $data = M('kk_guest2')->select();
        $temp = array('无此客户');
        foreach($data  as $val){
            $temp[$val['id']] = $val['g_name'];
        }
        return $temp;
    }
    public function getTjData($aid){
        $field = 'b.*';
        $map = array(
            'a.id' => $aid,
        );
        $system = 'kk';
        $model  = D(ucfirst($system).'Guest2');
        $data = M('kk_guest_tj a')
                ->join('kk_tj b on a.relationid = b.relationid')
                ->field($field)
                ->where($map)
                ->order('tj_client desc,tj_bzfs desc,tj_cate')
                ->select();

        $client = M('kk_guest_tj a')
                ->join('kk_tj b on a.relationid = b.relationid')
                ->field('tj_client')
                ->where($map)
                ->group('tj_client')
                ->select();
        $guest = $this->getAllGuestName();  
        $pp = array(
            array('pp' => 'P.O42.5','bz' => '散装'),
            array('pp' => 'P.S.A32.5','bz' => '散装'),
            array('pp' => 'P.O42.5','bz' => '袋装'),
            array('pp' => 'P.S.A32.5','bz' => '袋装'),
        );
        $tj_da = $data[0]['tj_da'];
        $temp = array();
        foreach ($client as  $value) {
            foreach ($pp as  $val) {
                $flag = 0;
                foreach ($data as $k=>$vo) {
                    if($vo['tj_cate'] == $val['pp'] && $vo['tj_bzfs'] == $val['bz'] && $vo['tj_client'] == $value['tj_client']){
                        $vo['g_name'] = $guest[$vo['tj_client']];
                        $bzfs         = $vo['tj_bzfs'] == '袋装'?'(袋)':'(散)';
                        $vo['cate']   = preg_replace('/[^\d]*/', '', $vo['tj_cate']).$bzfs;
                        // 当前价格
                        $vo['now'] = preg_replace('/\.0+$/', '',bcsub($vo['tj_dj'],$vo['delta_dj'],2));
                        // 调整后价格
                        $color = (int) $vo['delta_dj']>0? 'red':'green';
                        $arrow = (int) $vo['delta_dj']>0? '&uarr;':'&darr;';
                        $tmpl  = "<span style='color:".$color."'>(".$vo['delta_dj']."{$arrow})</span>";
                        $vo['dj'] = $vo['tj_dj'].$tmpl;
                        $temp[$vo['tj_client']]['g_name']  = $model->getName($vo['tj_client']);  
                        $temp[$vo['tj_client']]['date']    = '调价日期：'.$vo['tj_stday'];  
                        $temp[$vo['tj_client']]['child'][] = $vo;    
                        $flag = 1;
                        unset($data[$k]);
                    }  
                }
                if($flag == 1) continue;
                $bz   = $val['bz'] == '散装'?'(散)':'(袋)';
                $show = preg_replace('/[^\d]*/', '', $val['pp']).$bz;
                $wlfs = $this->getWlfs($value['tj_client'],$tj_da,$val['pp'],$val['bz'],$system);
                $yf   = $this->getfhyf($value['tj_client'],$tj_da,'福源鑫',$val['pp'],$val['bz'],$system);
                $yf   = ($yf == '-'|| $wlfs == '自提')?$wlfs==null?$yf:$wlfs:$yf;
                if($yf == 0) $yf = '自提';
                $item = array(
                    'cate' => $show,
                    'now'  => $this->getfhdj($value['tj_client'],$tj_da,'福源鑫',$val['pp'],$val['bz'],$system),
                    'dj'   => '-',
                    'tj_yf' => $yf,
                );
                if($item['now'] == '-') continue;
                $temp[$value['tj_client']]['child'][] = $item; 
            }
        }
        return $temp;
        
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
        $res = M('kk_appflowproc')->field('app_stat')->where($map)->select();
        $stat = array();
        foreach ($res as $value){
            $stat[] = $value['app_stat'];
        }
        if (in_array(0, $stat))  return 2; //审批中
        if (in_array(1, $stat))  return 2; //退审 先2 后1
        return 1;                                  //审批通过
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
        return M('kk_tj')->where(array('relationid' => $data['relationid']))->setField('tj_stat',0);
    }

    /**
     * 详情html生成
     * @param array   $data 配比数据
     * @return string $html
     */
    public function makeDeatilHtml($res,$res2){
        $data = array();
        $key = 0;
        foreach ($res as $value){
            $stat = '';                             //审批状态
            if($value['app_stat'] == 0){
                $stat = '审批中';
            }else if($value['app_stat'] == 1){
                $stat = '审批拒绝';
            }else if($value['app_stat'] == 2){
                $stat = '审批通过';
            }
            $html = "<input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+人：{$value['per_name']}'>
                     <input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+时间：{$value['approve_time']} '>
                     <input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+关注点：{$res2[$key]['point']} '>
                     <input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+意见：{$value['app_word']} '>
                     <input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+结果：$stat'>";
            $data[] = $html;
            $key++;
        }
        return $data;
    }
    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result[] = array('name'=>'系统类型：',
            'value'=>'建材客户调价',
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
            array('title' => '调价日期' , 'content' => date('Y-m-d',strtotime($res['date'])) ),
            array('title' => '相关说明' , 'content' => '无'  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $this->transStat($res['id']),
            'applyerName'    => D('KkBoss')->getNameFromID($res['applyuser']),
        );
        return $result;
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
        if(!M('kk_guest_tj')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        M('kk_tj')->addAll($insert);
        $tj = array(
            'date'       => date('Y-m-d'),
            'relationid' => $relationid,
            'content'    => json_encode($insert),
            'applyuser'  => session('kk_id'),
            'jbr'        => session('name'),
            'dtime'      => date('Y-m-d H:i:s'),
            'stat'       => 1,
        );
        $result = M('kk_guest_tj')->add($tj);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('kkAppcopyto')->copyTo($copyto_id,'GuesttjApply', $result);
        }
        // 签收通知
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('GuesttjApply', $result, $salesid, 'kk');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }

    public function getRelationid(){
        $data = M('kk_guest_tj')->where(array( 'date' => date('Y-m-d') ))->count();
        $lsdcount = $data + 1;
        return date('Ymd').str_pad($lsdcount,3,'0',STR_PAD_LEFT);
    }

    public function getTjInfo(){
        $date   = I('post.date');
        $system = I('post.system');
        $field  = 'a.id as id,g_name,g_khlx,reid';
        $model  = D(ucfirst($system).'Guest2');
        $pp = array(
            array('pp' => 'P.O42.5','bz' => '散装'),
            array('pp' => 'P.S.A32.5','bz' => '散装'),
            array('pp' => 'P.O42.5','bz' => '袋装'),
            array('pp' => 'P.S.A32.5','bz' => '袋装'),
        );
        $kh = array(
            array('k' => 'jxs','name' => '经销商'),
            array('k' => 'zgdw','name' => '直供单位'),
        );
        $result = array();
        foreach($kh as $value){
            $map    = array(
                'ht_stat'  => 2,
                'ht_stday' => array('elt',$date),
                'ht_enday' => array('egt',$date),
                'g_khlx'   => $value['name'],
                'reid'     => array('neq',0),
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
                $temp = array();
                $temp['fg_name'] = $model->getParentName($val['id']);
                $temp['g_name']  = $val['g_name'];
                $temp['tj_date'] = $this->getTjDate($val['id'],$date);
                $temp['client']  = $val['id'];
                foreach($pp as $vo){
                    $wlfs = $this->getWlfs($val['id'],$date,$vo['pp'],$vo['bz'],$system);
                    $dj   = $this->getfhdj($val['id'],$date,'福源鑫',$vo['pp'],$vo['bz'],$system);
                    $yf   = $this->getfhyf($val['id'],$date,'福源鑫',$vo['pp'],$vo['bz'],$system);
                    $bz   = $vo['bz'] == '散装'?'(散)':'(袋)';
                    if($dj == '-') continue;
                    $yf     = ($yf == '-'|| $wlfs == '自提')?($wlfs==null?$yf:$wlfs):$yf;
                    $yfflag = ($yf == '-'|| $wlfs == '自提')?0:1;
                    $show = preg_replace('/[^\d]*/', '', $vo['pp']).$bz;
                    $temp['data'][] = array(
                        'pp'     => $vo['pp'],
                        'bz'     => $vo['bz'],
                        'show'   => $show,
                        'dj'     => $dj,
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
    private function getWlfs($client,$date,$cate,$bz,$system){
        $query = "select ht_wlfs from {$system}_ht where ht_khmc='".$client."' and ht_stat='2' and ht_stday<='$date' and ht_enday>='$date' and ht_cate='$cate' and ht_bzfs='$bz' order by ht_stday desc";
        $res = M()->query($query);
        return $res[0]['ht_wlfs'];
    }
    private function getfhdj($client,$day,$pp,$cate,$bzfs,$system){
        if(($client==260||$client==261||$client==346||$client==339)&&$day>='2013-04-04'&&($cate=='S95'||$cate=='F95')){
            //所有调价和合同合并查询初始价格
            $query="select dj from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_date as date from {$system}_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_da as date from {$system}_tj  ) as t where pp='$pp' and stday<='$day' and bzfs='$bzfs' and stat=2 and client='".$client."' and cate='外购矿粉' order by stday desc,date desc";
        
            $data = M()->query($query);
            $rowcount = count($data);
            if($rowcount>0){
                $row = $data[0];
                return $row['dj'];
            }
            else{
                return '-';
            }
        }
        if($bzfs=='散装'){
             //所有调价和合同合并查询初始价格
            $query="select dj from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_date as date from {$system}_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_da as date from {$system}_tj  ) as t where pp='$pp' and stday<='$day' and bzfs='$bzfs' and stat=2 and client='".$client."' and cate='".$cate."' order by stday desc,date desc";
        }else{
                //所有调价和合同合并查询初始价格
            $query="select dj from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_date as date from {$system}_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_da as date from {$system}_tj  ) as t where pp='$pp' and stday<='$day' and bzfs!='散装' and stat=2 and client='".$client."' and cate='".$cate."' order by stday desc,date desc";
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

    private function getfhyf($client,$day,$pp,$cate,$bzfs,$system){
        if(($client==260||$client==261||$client==346||$client==339)&&$day>='2013-04-04'&&($cate=='S95'||$cate=='F95')){
            //所有调价和合同合并查询初始价格
            $query="select yf from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_date as date from {$system}_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_da as date from {$system}_tj  ) as t where pp='$pp' and stday<='$day' and bzfs='$bzfs' and stat=2 and client='".$client."' and cate='外购矿粉' order by stday desc,date desc";
        
            $data = M()->query($query);
            $rowcount = count($data);
            if($rowcount>0){
                $row = $data[0];
                return $row['yf'];
            }
            else{
                return '-';
            }
        }
        if($bzfs=='散装'){
             //所有调价和合同合并查询初始价格
            $query="select yf from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_date as date from {$system}_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_da as date from {$system}_tj  ) as t where pp='$pp' and stday<='$day' and bzfs='$bzfs' and stat=2 and client='".$client."' and cate='".$cate."' order by stday desc,date desc";
        }else{
                //所有调价和合同合并查询初始价格
            $query="select yf from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_date as date from {$system}_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_da as date from {$system}_tj  ) as t where pp='$pp' and stday<='$day' and bzfs!='散装' and stat=2 and client='".$client."' and cate='".$cate."' order by stday desc,date desc";
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
        $res = M()->query("select tj_stday from kk_tj where tj_client='$clientid' and tj_stday<='$day' and  tj_stat='2' order by tj_stday desc");
        $result = $res[0]['tj_stday'];
        if (empty($result)) {
          $res = M()->query("select ht_qday from kk_ht where ht_khmc='$clientid' and ht_qday<='$day' and  ht_stat='2' order by ht_qday desc");
          $result = $res[0]['ht_qday'];
        }
      } else {
        $result="无提货信息";
      }
      return $result;
    }

}