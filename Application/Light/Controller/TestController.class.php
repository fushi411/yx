<?php
namespace Light\Controller;
use  Vendor\Overtrue\Pinyin\Pinyin;
class TestController extends BaseController {
   
    public function Sign()
    {   
        header('Content-Type: text/html; charset=utf-8');
        // 经销商调价
        $date   = '2019-09-10';
        $system = 'yxhb';
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
                $temp = array();
                $temp['fg_name'] = $model->getParentName($val['id']);
                $temp['g_name']  = $val['g_name'];
                $temp['tj_date'] = $this->getTjDate($val['id'],$date);
                $temp['client']  = $val['id'];
                foreach($pp as $vo){
                    $wlfs = $this->getWlfs($val['id'],$date,$vo['pp'],$system);
                    $dj   = $this->getfhdj($val['id'],$date,$vo['pp'],$vo['bz'],$wlfs);
                    $yf   = $this->getfhyf($val['id'],$date,$vo['pp'],$vo['bz'],$wlfs);
                    $show = $vo['pp'];
                    $temp['data'][] = array(
                        'pp'     => $vo['pp'],
                        'bz'     => $vo['bz'],
                        'show'   => $show,
                        'dj'     => $dj,
                        'djflag' => $dj == '-'?0:1, 
                        'yf'     => ($yf == '-'|| $wlfs == '自提')?$wlfs==null?$yf:$wlfs:$yf,
                        'yfflag' => ($yf == '-'|| $wlfs == '自提')?0:1, 
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
    public function getAllGuestName(){
        $data = M('yxhb_guest2')->select();
        $temp = array('无此客户');
        foreach($data  as $val){
            $temp[$val['id']] = $val['g_name'];
        }
        return $temp;
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

    public function getWlfs($client,$date,$cate,$system){
        $query = "select ht_wlfs from {$system}_ht where ht_khmc='".$client."' and ht_stat='2' and ht_stday<='$date' and ht_enday>='$date' and ht_cate='{$cate}' order by ht_stday desc";
        $res = M()->query($query);
        return $res[0]['ht_wlfs'];
    }

    public function getfhdj($client,$day,$cate,$bzfs,$wlfs){
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

    public function getfhyf($client,$day,$cate,$bzfs,$wlfs){
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
    
    public function getTjDate($clientid,$day)
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


