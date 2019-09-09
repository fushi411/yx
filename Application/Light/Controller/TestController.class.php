<?php
namespace Light\Controller;
use  Vendor\Overtrue\Pinyin\Pinyin;
class TestController extends BaseController {
   
    public function Sign()
    {   
        header('Content-Type: text/html; charset=utf-8');
        // 经销商调价
        $id = '20190905003';
        $data   =  M('yxhb_js')->where(array('pid' => $id))->order('client')->select();
        $temp = array();
        foreach($data as $vo){
            $stday  = $vo['js_stday'];
            $enday  = $vo['js_enday'];
            $client = $vo['client'];
            $g_name = M('yxhb_guest2')->where(array('id' => $client))->find();
            $res  = $this->getJc($stday,$enday,$client);
            foreach($res as $val){
                if( $val['bzfs']== $vo['js_bzfs'] && $val['pp'] == $vo['js_cate'] ){
                    $xgdj = $vo['js_zl']>0?($val['dj']-$vo['js_dj']):($val['dj']+$vo['js_dj']);
                    $val['djformat'] = '&yen;'.preg_replace('/\.0+$/', '', number_format($val['dj'],2,'.',','));
                    $val['xgdj'] = '&yen;'.preg_replace('/\.0+$/', '', number_format( $xgdj,2,'.',',')).'<span style="color:#f12e2e">('.$vo['js_dj'].')</span>';
                    $val['xgyf'] = $vo['js_yf'];
                    $val['xgzl'] = number_format($vo['js_zl'],2,'.',',');
                    $val['xgxj'] = number_format($vo['js_je'],2,'.',',');
                    $temp[$client]['g_name'] = $g_name['g_name'];
                    $temp[$client]['data'][] = $val;
                }
            }
        }
        dump($temp);
    }

    public function getJc($stday,$enday,$client){
        $map = array(
            'ht_khmc' => $client,
            'fh_stat' => 1,
            'fh_da'   => array('between',array($stday,$enday)),
            'fh_bzfs' => array(array('eq','纸袋'),array('eq','编织袋'),array('eq','散装'),'or'),
        );
        // 查询发货品种和包装
        $data = M('yxhb_fh')->field("fh_cate,if(fh_bzfs='散装','散装','袋装') as fh_bzfs,fh_bzfs as bzfs")->where($map)->group('fh_cate,fh_bzfs')->select();
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
            $tj = M('yxhb_tj')
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
                dump($temp);
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
        $weight = M('yxhb_fh')
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
        $tj = M('yxhb_tj')
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
            $tj = M('yxhb_ht')
                ->field('ht_dj as dj,ht_yf as yf,ht_stday as stday')
                ->where($map)
                ->order('ht_stday desc')
                ->find();
            // 无合同 初始时间后得
            if(empty($tj)){
                $map['ht_stday'] = array('gt',$stday);
                $tj = M('yxhb_ht')
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
    public function getWlfs($client,$date,$cate,$bz,$system){
        $query = "select ht_wlfs from {$system}_ht where ht_khmc='".$client."' and ht_stat='2' and ht_stday<='$date' and ht_enday>='$date' and ht_cate='$cate' and ht_bzfs='$bz' order by ht_stday desc";
        $res = M()->query($query);
        return $res[0]['ht_wlfs'];
    }
    public function getfhdj($client,$day,$pp,$cate,$bzfs,$system){
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
                return '无单价';
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
            return '无单价';
        }
    }

    public function getfhyf($client,$day,$pp,$cate,$bzfs,$system){
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
                return '无运费';
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
            return '无运费';
        }
    }
    
    public function getTjDate($clientid,$day)
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
    // 流程失效，重置
    public function flow(){
        $wf = A('WorkFlow');
        $result = 147991;
        $salesid = 19;
        $res = $wf->setWorkFlowSV('fh_refund_Apply', $result, $salesid, 'yxhb');
    }

    // 重置 发票上传未同步更新spsm字段
    public function updateFpsm(){
        // 发票上传
        $map = array(
            'stat' => 1,
            'type' => 2,
        );
        $data = M('yxhb_fpsm')->where($map)->select();
        // 采购申请单
        $idStr = '';
        foreach($data as $v){
            $idStr .= $v['dh'].',';
        }
        $map = array(
            'id' => array('in',trim($idStr,',') ),
        );
        $data = M('yxhb_cgfksq')->where($map)->select();
        // 采购费用
        foreach( $data as $v){
            $map = array(
                'sqdh' => $v['dh'] ,
                'fpsm' => 2,
            );
            $res = M('yxhb_feecg')->where($map)->find();
            if(empty($res)) continue;
            dump($res);
            // 发票状态重置
            $res = M('yxhb_feecg')->where(array('id' => $res['id']))->setField('fpsm',1);
            dump(M()->_sql());
        }
    }

        // 搜索模块
        public function getSearchTable($search){
            $tab = $this->getAppTable();
            $tab = $this->arrayMerge($tab);
            $res = array();
            if(empty($search)) return $tab;
            foreach($tab as $k => $v){
                $flag = $this->searchFind($v['search'],$search);
                if(!$flag) continue;
                $res[] = $v;
            }
            return $res;
       }
      
    
        // 查询表
        public function getAppTable(){
            $seek =  D('Seek');
            return $seek->getAppTable();
        }
    
        public function arrayMerge($data){
            $seek =  D('Seek');
            $cost = $this->flag?2:1;
            $tab = $seek->arrayMerge($data,$cost);
            return $tab;
        }
        
        /**
         * 搜索查看是否在所查的模块内
         * @param  string $mod        模块名
         * @param  string $searchText 搜索值
         * @return bool   $flag       布尔
         */
        public function searchFind($mod,$searchText){
            $tmp = $this->spStr($mod);
            $sys = array('建材','环保','投资');
            $idx = '';
            foreach($sys as $k => $v){
                if( in_array($v,$tmp)){
                    $idx = $k;
                    break; 
                }
            }
            if($idx !== '') {
                unset($tmp[array_search($sys[$idx],$tmp)]);
                $tmp = array_values($tmp);
            }
            $flag = false;
            foreach($tmp as $key => $val){
                if($idx != ''){
                    if(substr_count($searchText,$sys[$idx] ) <=0 )continue; 
                }
                if(substr_count($searchText,$val)>0) { // 检查是否有需要改项目
                    $flag=true;
                    break;
                }
            }
            return $flag;
        } 
    
        // UTF-8版 中文二元分词 
        public function spStr($str)  
        {  
            $cstr = array();  
        
            $search = array(",", "/", "\\", ".", ";", ":", "\"", "!", "~", "`", "^", "(", ")", "?", "-", "\t", "\n", "'", "<", ">", "\r", "\r\n", "{1}quot;", "&", "%", "#", "@", "+", "=", "{", "}", "[", "]", "：", "）", "（", "．", "。", "，", "！", "；", "“", "”", "‘", "’", "［", "］", "、", "—", "　", "《", "》", "－", "…", "【", "】",);  
        
            $str = str_replace($search, " ", $str);  
            preg_match_all("/[a-zA-Z]+/", $str, $estr);  
            preg_match_all("/[0-9]+/", $str, $nstr);  
        
            $str = preg_replace("/[0-9a-zA-Z]+/", " ", $str);  
            $str = preg_replace("/\s{2,}/", " ", $str);  
        
            $str = explode(" ", trim($str));  
            foreach ($str as $s) {  
                $l = strlen($s);  
        
                $bf = null;  
                for ($i= 0; $i< $l; $i=$i+3) {  
                    $ns1 = $s{$i}.$s{$i+1}.$s{$i+2};  
                    if (isset($s{$i+3})) {  
                        $ns2 = $s{$i+3}.$s{$i+4}.$s{$i+5};  
                        if (preg_match("/[\x80-\xff]{3}/",$ns2)) $cstr[] = $ns1.$ns2;  
                    } else if ($i == 0) {  
                        $cstr[] = $ns1;  
                    }  
                }  
            }  
            $estr = isset($estr[0])?$estr[0]:array();  
            $nstr = isset($nstr[0])?$nstr[0]:array();  
            return array_merge($nstr,$estr,$cstr);  
        } 
 
} 


