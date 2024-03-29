<?php
namespace Light\Model;
use Think\Model;
/**
 * 客户信息
 */

class CustomerModel extends Model
{
   
    // 虚拟模型
    protected $autoCheckFields = false;
    private $today = null;
    
    public function __construct(){
        parent::__construct();
        $this->today = date('Y-m-d',time());
    }
    /**
     * 获取建材的有效客户
     */

    public function getVaildUser(){
        list($reid,$result,$tmp) = $this->getKkCustomerNews();
        return array_diff($result,$reid);
    }
    /**
     * 获取用户的应收余额
     */
    public function getUserYs($client_id){
        list($reid,$result,$tmp) = $this->getKkCustomerNews();
        $res = $tmp[$client_id];
        if($res['reid'] == 0){
            return $res['qmje'];
        } 
        else{
            return $tmp[$res['reid']]['qmje'];
        } 
    }

    /**
     * 获取环保的有效客户
     */

    public function getYxhbVaildUser(){
        list($reid,$result) = $this->getYxhbCustomerNews();
        return $reid;
    }
    /**
     * 获取环保客户 应收额度
     * @param int $client_id 客户id
     * @return array $res 各项余额  
     */
    public function getYxhbCustomerNews(){
        $today      = date("Y-m-d",time());
        $tomonth    = date("m",time());
        $mday       = date("Y-m",time())."-01";
        $time       = M('yxhb_px')->field('time,sdate,edate')->order('time desc')->find();
        $time1      = $time['time'];
        $sdate      = $time['sdate'];
        $edate      = $time['edate'];
        $px_time    = date("Y-m-d H:i:s",time());
        if($px_time > date("Y-m-d H:i:s",strtotime($time1)+600) || $bk_sdate != $sdate || $bk_edate != $edate){
            // 更新px表 
        }
        $pxs    = M('yxhb_px')->where('reid=0')->order('qmje')->select();
        $result = array();
        $reid   = array();
        foreach( $pxs as $k => $rpx){
            $jcje   = round($rpx['jcje'] ,2);
            $bqhr   = round($rpx['bqhr'] ,2);
            $fhje   = round($rpx['fhje'] ,2);
            $bcje   = round($rpx['bcje'] ,2);
            $flje   = round($rpx['flje'] ,2);
            $sxje   = round($rpx['sxje'] ,2);
            $qtje   = round($rpx['qtje'] ,2);
            $jctzje = round($rpx['jctzje'] ,2);
            $bz     = $rpx['bz'];
            $qmye   = -$fhje+$bcje+$flje+$sxje+$qtje+$jctzje+$jcje+$bqhr;
            if(round($jcje,2)==0&&$bqhr==0&&$fhje==0&&$bcje==0&&$flje==0&&$sxje==0&&$qtje==0&&$jctzje==0) continue;  
            $rpx['qmye'] = $qmye;
            $result[$rpx['gid']] = $rpx;
            $cor = M('yxhb_px')->where(array('reid' => $rpx['gid']))->select();
            $reid[] = $rpx['gid'];
            
        }
        return array($reid,$result);
    }

    /**
     * 获取客户 应收额度
     * @param int $client_id 客户id
     * @return array $res 各项余额  
     */
    public function getKkCustomerNews(){
        
        $sdate = date('Y-m-01',time());
        $res   = M('kk_guest_accounts_receivable')
                ->where(array('reid' => 0, 'sdate' => $sdate))
                ->group('clientid')
                ->order('reid')
                ->select();

        foreach($res as $v){
            $data = M('kk_guest_accounts_receivable')
                    ->where(array('reid' => $v['clientid'], 'sdate' => $sdate))
                    ->group('clientid')
                    ->order('reid')
                    ->select();
            if( count($data)>0){
                foreach($data as $val){
                    //期初金额
                    $ajcje=$val['qcye'];
                    //本期付款
                    $afhje=$val['fhje'];
                    //本期汇入金额
                    $abqhr=$val['bqhr'];
                    //审核磅差金额
                    $abcje=$val['bcje'];//-----审核磅差金额
                    //审核返利金额
                    $aflje=$val['flje'];//-----审核返利金额(new)
                    //审核手续费金额
                    $asxje=$val['sxje'];
                    //审核其他金额
                    $aqtje=$val['qtje'];
                    //审核价差金额
                    $ajctzje=$val['jctzje'];
                    $aqmje=$ajcje+$abqhr-$afhje+$abcje+$aflje+$asxje+$aqtje+$ajctzje;			
                    $ar_client[]=array("id"=>$val['clientid'],
                                        "jcje"=>$ajcje,
                                        "bqhr"=>$abqhr,
                                        "fhje"=>$afhje,
                                        "bcje"=>$abcje,
                                        "flje"=>$aflje,
                                        "sxje"=>$asxje,
                                        "qtje"=>$aqtje,
                                        "jctzje"=>$ajctzje,
                                        "qmje"=>$aqmje,
                                        "reid"=>$val['reid']);
                    //小计
                    $xjjcje+=number_format($ajcje,2,'.','');
                    $xjbqhr+=$abqhr;
                    $xjfhje+=number_format($afhje,2,'.','');
                    $xjbcje+=number_format($abcje,2,'.','');
                    $xjflje+=number_format($aflje,2,'.','');
                    $xjsxje+=number_format($asxje,2,'.','');
                    $xjjctzje+=number_format($ajctzje,2,'.','');
                    $xjqtje+=number_format($aqtje,2,'.','');
                    $xjqmje+=number_format($aqmje,2,'.','');
                }
                $a_client[]=array("id"=>$v['clientid'],
                                // "jcje"=>$xjjcje,
                                // "bqhr"=>$xjbqhr,
                                // "fhje"=>$xjfhje,
                                // "bcje"=>$xjbcje,
                                // "flje"=>$xjflje,
                                // "sxje"=>$xjsxje,
                                // "qtje"=>$xjqtje,
                                // "jctzje"=>$xjjctzje,
                                // "qmje"=>$xjqmje,
                                "jcje"=>$v['qcye'],
                                "bqhr"=>$v['bqhr'],
                                "fhje"=>$v['fhje'],
                                "bcje"=>$v['bcje'],
                                "flje"=>$v['flje'],
                                "sxje"=>$v['sxje'],
                                "qtje"=>$v['qtje'],
                                "jctzje"=>$v['jctzje'],
                                "qmje"=>$v['qmje'],
                                "reid"=>"0",
                                "flag"=> count($data));
                //累计金额
                //累计金额
                $ljjcje+=number_format($xjjcje,2,'.','');
                $ljbqhr+=$xjbqhr;
                $ljfhje+=number_format($xjfhje,2,'.','');
                $ljbcje+=number_format($xjbcje,2,'.','');
                $ljflje+=number_format($xjflje,2,'.','');
                $ljsxje+=number_format($xjsxje,2,'.','');
                $ljqtje+=number_format($xjqtje,2,'.','');
                $ljjctzje+=number_format($xjjctzje,2,'.','');
                //-----------------------------
                $xjjcje=$xjbqhr=$xjfhje=$xjbcje=$xjflje=$xjsxje=$xjqtje=$xjjctzje=$xjqmje=0;
            }else{
                //期初金额
			     $ijcje=$v['qcye'];
				 //本期付款
				 $ifhje=$v['fhje'];
 				//本期汇入金额
				 $ibqhr=$v['bqhr'];
				 //审核磅差金额
				 $ibcje=$v['bcje'];//-----审核磅差金额
				 //审核返利金额
				 $iflje=$v['flje'];
	             //审核手续费金额
				 $isxje=$v['sxje'];
		 		//审核其他金额
				 $iqtje=$v['qtje'];
		 		//审核价差调整金额
				 $ijctzje=$v['jctzje'];
				 $iqmje=$ijcje+$ibqhr-$ifhje+$ibcje+$iflje+$isxje+$iqtje+$ijctzje;
				 $a_client[]=array("id"=>$v['clientid'],
				 				  "jcje"=>$ijcje,
								  "bqhr"=>$ibqhr,
								  "fhje"=>$ifhje,
								  "bcje"=>$ibcje,
								  "flje"=>$iflje,
								  "sxje"=>$isxje,
								  "jctzje"=>$ijctzje,
								  "qtje"=>$iqtje,
								  "qmje"=>$iqmje,
								  "reid"=>"0",
								  "flag"=>"0");
                            //显示
                            //累计金额
                $ljjcje+=number_format($ijcje,2,'.','');
                $ljbqhr+=$ibqhr;
                $ljfhje+=number_format($ifhje,2,'.','');
                $ljbcje+=number_format($ibcje,2,'.','');
                $ljflje+=number_format($iflje,2,'.','');
                $ljsxje+=number_format($isxje,2,'.','');
                $ljjctzje+=number_format($ijctzje,2,'.','');
                $ljqtje+=number_format($iqtje,2,'.','');
            }
        }
        $ljqmje=$ljjcje+$ljbqhr-$ljfhje+$ljbcje+$ljflje+$ljsxje+$ljqtje+$ljjctzje;
        foreach ($a_client as $key => $value) {
            $kqmje[$key] = $value["qmje"];
        }
        array_multisort($kqmje,SORT_ASC,SORT_NUMERIC,$a_client);
        $tmp = array(); // 数据储存
        $reid = array(); // 有二级客户的id
        $result = array(); // id结果集
        foreach ($a_client as $key => $value) {
            if(round($value["jcje"],2)==0&&round($value["bqhr"],2)==0&&round($value["fhje"],2)==0&&round($value["bcje"],2)==0&&round($value["flje"],2)==0&&round($value["sxje"],2)==0&&round($value["qtje"],2)==0&&round($value["jctzje"],2)==0&&round($value["qmje"],2)==0) continue;
            $tmp[$value['id']] = $value;
            $result[] = $value['id'];
            if($value['flag'] == 0 ) continue;
            
            foreach($ar_client as $values){
                if($values['reid'] != $value['id']) continue;	
                if(round($values["jcje"],2)==0&&round($values["bqhr"],2)==0&&round($values["fhje"],2)==0&&round($values["bcje"],2)==0&&round($values["flje"],2)==0&&round($values["sxje"],2)==0&&round($values["qtje"],2)==0&&round($values["qmje"],2)==0) continue;
                $reid[] = $value['id'];
                $result[] = $values['id'];
                $tmp[$values['id']] = $values;
            }
        }
        $reid = array_unique($reid); // 去重
        return array($reid,$result,$tmp);
    } 


    /**
     * 获取客户用户 各项余额
     * @param int $client_id 客户id
     * @return array $res 各项余额  
     */
    public function  getCustomerInfo($date){
        if($date) $this->today = $date;
        $client_id = I('user_id');
        $system = I('system');
        if(!$system)$system='yxhb';
        if(!$client_id){
            return array('code' => 404,'msg' => '请重新刷新页面！');
        }
        // 临额申请情况
        $info = $this->getQuoteTimes($client_id,$system);

        if($system == 'kk') {
            $clientname = $this->getClientname($client_id,$system);
           // $name = htmlentities($clientname, ENT_QUOTES);
            $res['name'] = data_auth_sign($clientname);
            $res['line'] = $this->getkkline($client_id,$this->today);
            $res['info'] = $info;
            return array('code' => 200, 'data' => $res);
        }
        // 临时额度
        $existTempQuote = $this->getTempCredit($client_id,$system);

        // 应收余额  信用额度
        $ye = $this->getClientFHYE($client_id,$this->today);

        $res = array(
            'exist' => number_format($existTempQuote,2), // 已有临额 1
            'line' => number_format($ye['line'],2),  // 信用额度 1
            'ysye' => number_format(-$ye['ysye'],2)// 应收余额 0
        );
        
        $res['ysflag'] = -$ye['ysye']<20000?true:false;
        $res['info'] = $info;
        $res['fhye'] = number_format($ye['line']-$ye['ysye']+$existTempQuote,2); // 发货额度 由前面3个决定
        $res['ye'] =  $res['fhye'];
        return array('code' => 200,'data' => $res);
    }

    /**
     * 获取客户用户 各项余额
     * @param int $client_id 客户id
     * @return array $res 各项余额  
     */
    public function  getCustomerInfoParams($client_id,$system,$date){
        if($date) $this->today = $date;
        if(!$system)$system='yxhb';
        if(!$client_id){
            return array('code' => 404,'msg' => '请重新刷新页面！');
        }
        // 临额申请情况
        $info = $this->getQuoteTimes($client_id,$system);

        if($system == 'kk') {
            $clientname = $this->getClientname($client_id,$system);
           // $name = htmlentities($clientname, ENT_QUOTES);
            $res['name'] = data_auth_sign($clientname);
            $res['line'] = $this->getkkline($client_id,$this->today);
            $res['info'] = $info;
            return array('code' => 200, 'data' => $res);
        }
        // 临时额度
        $existTempQuote = $this->getTempCredit($client_id,$system);

        // 应收余额  信用额度
        $ye = $this->getClientFHYE($client_id,$this->today);

        $res = array(
            'exist' => number_format($existTempQuote,2), // 已有临额 1
            'line' => number_format($ye['line'],2),  // 信用额度 1
            'ysye' => number_format(-$ye['ysye'],2)// 应收余额 0
        );
        
        $res['ysflag'] = -$ye['ysye']<20000?true:false;
        $res['info'] = $info;
        $res['fhye'] = number_format($ye['line']-$ye['ysye']+$existTempQuote,2); // 发货额度 由前面3个决定
        $res['ye'] =  $res['fhye'];
        return array('code' => 200,'data' => $res);
    }
    /**
     * 获取客户用户 发货余额
     * @param int $client_id 客户id
     */
    public function getClientFHYE($client_id,$date){
        $tomorrow=date("Y-m-d",strtotime("+1 day",strtotime($date)));
        $last_month_fday=date("Y-m-01",strtotime("-1 months",strtotime($date)));
        $last_month_lday=date("Y-m-t",strtotime("-1 months",strtotime($date)));
      
        $ye=0;
        $fhje=0;
        
        $getReid = M('yxhb_guest2')
                ->field('id')
                ->where(array('reid' => $client_id))
                ->select();
        foreach($getReid as $k =>$v){
            $ye+=$this->GetClientQc($v['id'],$tomorrow);
			$fhje+=$this->GetFhJe2($v['id'],$last_month_fday,$last_month_lday);
        }
        $ye+=$this->GetClientQc($client_id,$tomorrow);
		$fhje+=$this->GetFhJe2($client_id,$last_month_fday,$last_month_lday);

        // 信用额度
        $res = M('yxhb_creditlineconfig')
                ->field('line')
                ->where(array('stat' => 1 , 'lower' => array('elt',$fhje),'clientid' => $client_id,'date' => array('elt',$date)))
                ->order('date desc,lower desc')
                ->select();
        
        $data = array(
            'line' => $res[0]['line'],  // 信用额度
            'ysye' => -$ye // 应收余额
        );
        return $data;

    }
    /***
     * 获取建材信用额度
     */
    public  function  getkkline($client,$date){
        //select line from kk_creditlineconfig where stat='1' and lower<=1 and clientid='467' and date<='2018-05-04' order by date desc,lower desc
        $line = M('kk_creditlineconfig')->where("stat='1' and lower<=1 and clientid='{$client}' and date<='{$date}'")->order('date desc,lower desc')->find();
        return $line['line']?$line['line']:'0';
    }
    public  function  getkkFmhline($client,$date){
        //select line from kk_creditlineconfig where stat='1' and lower<=1 and clientid='467' and date<='2018-05-04' order by date desc,lower desc
        $line = M('kk_creditlineconfig_fmh')->where("stat='1' and lower<=1 and clientid='{$client}' and date<='{$date}'")->order('date desc,lower desc')->find();
        return $line['line']?$line['line']:'0';
    }
    /**
     * 获取应收额度 client_qc
     */
    public function GetClientQc($client,$date)
    {
        $bk_edate0=$date;
        $bk_edate=date("Y-m-d",strtotime("$bk_edate0-1 day"));
        $bk_sdate='2011-08-01';
//------------已签客户名称-------------------
        $je=0;

        $sql="select g_ye,g_khlx from yxhb_guest2 where id='".$client."'";
        $res = M()->query($sql);
        $jcje=$res[0]['g_ye'];
        $khlx=$res[0]['g_khlx'];//-----7月31日结存金额
        if($date=='2011-08-01') return -$jcje;
//---------------------散装福源鑫

        $sql="select fh_cate from yxhb_fh where fh_pp='福源鑫' and fh_date>='$bk_sdate' and fh_date<='$bk_edate'+INTERVAL 1 DAY and fh_bzfs='散装' and fh_stat='1' and fh_client='".$client."' group by fh_cate";
        $res = M()->query($sql);

        foreach ($res as $key=>$val){
//------------包运
            $n = 0;
            $sql = "select ht_stday from yxhb_ht where ht_pp='福源鑫' and  ht_bzfs='散装' and ht_stat='2' and ht_khmc='".$client."' and ht_cate='".$val['fh_cate']."' and ht_stday<='$bk_edate0' and ht_wlfs='包运' order by ht_stday asc";
            $res1 = M()->query($sql);

            foreach ($res1 as $k=>$v){
                if($n == 0){
                    if(strtotime($bk_sdate)>strtotime($v['ht_stday'])){
                        $bk_sdate2=$bk_sdate;
                        $n++;
                        continue;
                    }
                    $je+=$this->GetJe2('福源鑫',$bk_sdate,$v['ht_stday'],'散装',$client,$val['fh_cate'],'包运');
                }else{
                    $je+=$this->GetJe2('福源鑫',$bk_sdate2,$v['ht_stday'],'散装',$client,$val['fh_cate'],'包运');
                }
                $bk_sdate2=$v['ht_stday'];
                $n++;
            }
            $je+=$this->GetJe2('福源鑫',$bk_sdate2,$bk_edate0,'散装',$client,$val['fh_cate'],'包运');
//-------------- 自提
            $n = 0;
            $sql="select ht_stday from yxhb_ht where ht_pp='福源鑫' and  ht_bzfs='散装' and ht_stat='2' and ht_khmc='".$client."' and ht_cate='".$val['fh_cate']."' and ht_stday<='$bk_edate0' and ht_wlfs='自提' order by ht_stday asc";
            $res2 = M()->query($sql);

            foreach ($res2 as $k=>$v){
                if($n==0){
                    if(strtotime($bk_sdate)>strtotime($v['ht_stday'])){
                        $bk_sdate2=$bk_sdate;
                        $n++;
                        continue;
                    }
                    $je+=$this->GetJe2('福源鑫',$bk_sdate,$v['ht_stday'],'散装',$client,$val['fh_cate'],'自提');
                }else{
                    $je+=$this->GetJe2('福源鑫',$bk_sdate2,$v['ht_stday'],'散装',$client,$val['fh_cate'],'自提');
                }
                $bk_sdate2=$v['ht_stday'];
                $n++;
            }
            $je+=$this->GetJe2('福源鑫',$bk_sdate2,$bk_edate0,'散装',$client,$val['fh_cate'],'自提');
        }
//-------本期汇入金额
        $bqhrSql = "select sum(nmoney) from yxhb_feexs as a,yxhb_dtg as b,yxhb_guest2 as c where a.dh=b.dh and b.gid=c.id and (a.sj_date>'2011-07-31' and a.sj_date<='$bk_edate') and a.stat='1' and c.id='".$client."'";
        $bqhrRes = M()->query($bqhrSql);
        $bqhr = round($bqhrRes[0]['sum(nmoney)'],2);
//-------磅差金额
        $bcjeSql = "select sum(js_je) from yxhb_js where js_date>='$bk_sdate' and js_date<='$bk_edate' and js_stat='2' and client='".$client."' and jslx='1'";
        $bcjeRes = M()->query($bcjeSql);
        $bcje = round($bcjeRes[0]['sum(js_je)'],2);
//-------返利金额
        $fljeSql = "select sum(js_je) from yxhb_js where js_date>='$bk_sdate' and js_date<='$bk_edate' and js_stat='2' and client='".$client."' and jslx='2'";
        $fljeRes = M()->query($fljeSql);
        $flje = round($fljeRes[0]['sum(js_je)'],2);
//-------审核返利金额
        $fljeSql = "select sum(totalmoney) from yxhb_salesrebates where exeday between '$bk_sdate' and '$bk_edate' and stat='1' and clientid='".$client."' and type='抵货款'";
        $fljeRes = M()->query($fljeSql);
        $flje += round($fljeRes[0]['sum(totalmoney)'],2);
//-----手续费金额
        $sxjeSql = "select sum(js_je) from yxhb_js where js_date>='$bk_sdate' and js_date<='$bk_edate' and js_stat='2' and client='".$client."' and jslx='3'";
        $sxjeRes = M()->query($sxjeSql);
        $sxje = round($sxjeRes[0]['sum(js_je)'],2);
//-----价差金额
        $jctzjeSql =  "select sum(js_je) from yxhb_js where js_date>='$bk_sdate' and js_date<='$bk_edate' and js_stat='2' and client='".$client."' and jslx='5'";
        $jctzjeRes = M()->query($jctzjeSql);
        $jctzje = round($jctzjeRes[0]['sum(js_je)'],2);
//-----其他金额
        $qtjeSql = "select sum(js_je) from yxhb_js where js_date>='$bk_sdate' and js_date<='$bk_edate' and js_stat='2' and client='".$client."' and jslx='4'";
        $qtjeRes = M()->query($qtjeSql);
        $qtje = round($qtjeRes[0]['sum(js_je)'],2);

        $je = round($je, 2);
        $ye=-$jcje+$bqhr-$je+$bcje+$flje+$sxje+$qtje+$jctzje;
//        echo -$jcje.' / '.$bqhr.' / '.$je.' / '.$bcje.' / '.$flje.' / '.$sxje.' / '.$qtje.' / '.$jctzje;
        return $ye;
    }

    /**
     *  获得首次发货时间 client_qc
     */
    public 	function GetSdate2($client,$sdate1,$cate,$bzfs,$pp,$wlfs)
    {
        if($bzfs=='散装'){
            if($cate=='矿粉')
                $query44="select fh_da from yxhb_fh where fh_pp='$pp' and fh_date between '".$sdate1."' and '".$sdate1."'+INTERVAL 1 MONTH  and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_bzfs='散装' and fh_wlfs='$wlfs' and fh_snbh not like 'WS%' order by fh_da asc";
            elseif($cate=='矿粉2')
                $query44="select fh_da from yxhb_fh where fh_pp='$pp' and fh_date between '".$sdate1."' and '".$sdate1."'+INTERVAL 1 MONTH  and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_bzfs='散装' and fh_snbh like 'WS%' and fh_wlfs='$wlfs' group by fh_da order by fh_da asc";
            else
                $query44="select fh_da from yxhb_fh where fh_pp='$pp' and fh_date between '".$sdate1."' and '".$sdate1."'+INTERVAL 1 MONTH  and fh_stat='1' and fh_client='".$client."' and fh_cate='$cate' and fh_bzfs='散装' and fh_wlfs='$wlfs' group by fh_da order by fh_da asc";
        }
        else
            $query44="select fh_da from yxhb_fh where fh_pp='$pp' and fh_date between '".$sdate1."' and '".$sdate1."'+INTERVAL 1 MONTH  and fh_stat='1' and fh_client='".$client."' and fh_cate='$cate' and (fh_bzfs='纸袋' or fh_bzfs='编织袋') and fh_wlfs='$wlfs' group by fh_da order by fh_da asc";
        $res = M()->query($query44);
        $fhsdate=$res[0]['fh_da'];
        if(strtotime($fhsdate)>strtotime($sdate1))
            $bk_sdate=$fhsdate;
        else
            $bk_sdate=$sdate1;
        return $bk_sdate;
    }

    /**
     * 获取发货金额 client_qc
     */
    public function GetJe2($pp,$sdate,$edate,$bzfs,$client,$cate,$wlfs){
        $je=0;
        $sdate0=$sdate;
        $sdate=$this->GetSdate2($client,$sdate0,$cate,$bzfs,$pp,$wlfs);
        $edate0=$edate;
        if($bzfs=='袋装')
            $bzfsql="(fh_bzfs='纸袋' or fh_bzfs='编织袋')";
        else
            $bzfsql="fh_bzfs='散装'";

        if(strtotime($sdate0)!=strtotime($sdate)){
            if($cate!='矿粉2')
                $query22="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date between '".$sdate." 00:00:00' and '".$edate0."' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
            else
                $query22="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date between '".$sdate." 00:00:00' and '".$edate0."' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
        }
        else {
            if($cate!='矿粉2')
                $query22="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date between '".$sdate."' and '".$edate0."' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
            else
                $query22="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date between '".$sdate."' and '".$edate0."' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
        }

        $r22=M()->query($query22);
        $bqzl=round($r22[0]['sum(fh_zl)'],2);//-----本期发出数量
        //-----起始日期价格

        if($cate!='矿粉2')
            $query3="select dj,yf,stday from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_wlfs as wlfs,ht_date as date from yxhb_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_wlfs as wlfs,tj_da as date from yxhb_tj  ) as t where pp='$pp' and  stday<='$sdate' and  bzfs='$bzfs' and stat='2' and client='".$client."' and cate='".$cate."' and wlfs='$wlfs' order by stday desc,date desc";
        else
            $query3="select tj_dj as dj,tj_yf as yf,tj_stday as stday from yxhb_tj where tj_pp='$pp' and  tj_stday<='$sdate' and  tj_bzfs='$bzfs' and tj_stat='2' and tj_client='".$client."' and tj_cate='外购矿粉' and tj_wlfs='$wlfs' order by tj_stday desc";
        $res3 = M()->query($query3);
        $counttj2=count($res3);

        //--如果起始日期前无调价，以合同单价为准
        if($counttj2==0){
            if($cate!='矿粉2')
                $query4="select ht_dj as dj,ht_yf as yf,ht_stday as stday from yxhb_ht where ht_pp='$pp' and  ht_bzfs='$bzfs' and ht_stat='2' and ht_khmc='".$client."' and ht_cate='".$cate."' and ht_stday<='$sdate' and ht_wlfs='$wlfs' order by ht_stday desc";
            else
                $query4="select ht_dj as dj,ht_yf as yf,ht_stday as stday from yxhb_ht where ht_pp='$pp' and  ht_bzfs='$bzfs' and ht_stat='2' and ht_khmc='".$client."' and ht_cate='外购矿粉' and ht_stday<='$sdate' and ht_wlfs='$wlfs' order by ht_stday desc";
            $r3=M()->query($query4);
            $csdj=$r3[0]['dj'];
            $csyf=$r3[0]['yf'];
            $csje=$csdj+$csyf;
        }
        else{
            $csdj=$res3[0]['dj'];
            $csyf=$res3[0]['yf'];
            $csje=$csdj+$csyf;
        }
        //-------------袋装价格分段
        if($cate!='矿粉2')
            $query5="select tj_dj,tj_yf,tj_stday from yxhb_tj where tj_stday>'$sdate' and tj_stday<'$edate0'  and tj_stat='2' and  tj_bzfs='$bzfs' and tj_client='".$client."' and tj_cate='".$cate."' and tj_pp='$pp' and tj_wlfs='$wlfs' order by tj_stday asc";
        else
            $query5="select tj_dj,tj_yf,tj_stday from yxhb_tj where tj_stday>'$sdate' and tj_stday<'$edate0'  and tj_stat='2' and  tj_bzfs='$bzfs' and tj_client='".$client."' and tj_cate='外购矿粉' and tj_pp='$pp' and tj_wlfs='$wlfs' order by tj_stday asc";
        $itj=1;
        $res5=M()->query($query5);
        $counttj=count($res5);
        //-如果在日期段中无调价，起始日期前的价格做为单价
        if($counttj==0){
            $je+=$csje*$bqzl;
        }
        //-如果在日期段中仅有一次调价
        elseif($counttj==1){
            $stday=$sdate;
            $enday=$res5[0]['tj_stday'];
            if(strtotime($sdate0)!=strtotime($sdate)){
                if($cate!='矿粉2')
                    $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday 00:00:00' and fh_date<'$enday' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                else
                    $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday 00:00:00' and fh_date<'$enday' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
            }
            else{
                if($cate!='矿粉2')
                    $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='$cate' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                else
                    $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
            }

            $r44=M()->query($query6);
            $cszl=round($r44[0]['sum(fh_zl)'],2);//-----起始价格对应数量
            $je+=$csje*$cszl;
            //--------------------------------------
            $stday=$enday;
            $enday=$edate;
            if($cate!='矿粉2')
                $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$edate0' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
            else
                $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$edate0' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
            $r44=M()->query($query6);
            $jszl=round($r44[0]['sum(fh_zl)'],2);//-----结束价格对应数量
            $hj=$res5[0]['tj_dj']+$res5[0]['tj_yf'];
            $je+=$hj*$jszl;
        }
        //-如果在日期段中多次调价
        else{
            foreach ($res5 as $k=>$r4){
                if($itj==1){
                    $stday=$sdate;
                    $enday=$r4['tj_stday'];
                    if(strtotime($sdate0)!=strtotime($sdate)){
                        if($cate!='矿粉2')
                            $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday 00:00:00' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                        else
                            $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday 00:00:00' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
                    }
                    else{
                        if($cate!='矿粉2')
                            $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                        else
                            $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
                    }

                    $r44=M()->query($query6);
                    $cszl=round($r44[0]['sum(fh_zl)'],2);//-----起始价格对应数量
                    $je+=$csje*$cszl;
                    $itj++;
                    $hj=$r4['tj_dj']+$r4['tj_yf'];
                }
                elseif($itj==$counttj){
                    $stday=$enday;
                    $enday=$r4['tj_stday'];
                    if($cate!='矿粉2')
                        $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                    else
                        $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";

                    $r44=M()->query($query6);
                    $zl=round($r44[0]['sum(fh_zl)'],2);//-----价格对应数量
                    $preje=$hj;
                    $je+=$preje*$zl;
                    $itj++;
                    $hj=$r4['tj_dj']+$r4['tj_yf'];
                    //------------------
                    $stday=$enday;
                    $enday=$edate;
                    if($cate!='矿粉2')
                        $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$edate0' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                    else
                        $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$edate0' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";

                    $r44=M()->query($query6);
                    $zl=round($r44[0]['sum(fh_zl)'],2);//-----价格对应数量
                    $preje=$hj;
                    $je+=$preje*$zl;
                    $itj++;
                }
                else{
                    $stday=$enday;
                    $enday=$r4['tj_stday'];
                    if($cate!='矿粉2')
                        $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                    else
                        $query6="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
                    $r44=M()->query($query6);
                    $zl=round($r44[0]['sum(fh_zl)'],2);//-----价格对应数量
                    $preje=$hj;
                    $je+=$preje*$zl;
                    $itj++;
                    $hj=$r4['tj_dj']+$r4['tj_yf'];
                }
            }
        }
        return $je;
    }

    //---------发货金额结束分隔---------------
    /**
     * 获取销售客户是否包含子客户
     * @param int $client_id 客户id
     * @return bool
    */
    public function isClientHaveChild($client_id){
        $where = array(
            'reid'   => $client_id,
            'g_stat' => 1
        );
        $res = M('yxhb_guest2')
                ->field('1')
                ->where($where)
                ->count();
       
        return $res==0?0:1;
    }


    /**
     * 合同用户 临时额度查询
     * @param int $client_id 客户id
     * @return arr   3种临时额度 申请情况
     */
    public function getQuoteTimes($client_id,$system,$date = ''){
        $this->today = date('Y-m-d H:i',time());
        if(!empty($date)) $this->today=$date;
        $tempClient = array('20000','50000','100000');
        $data = array();
        foreach($tempClient as $k=>$v){
            $where = array(
                "DATEDIFF('{$this->today}',date) <= SUBSTRING(yxq,1) and stat" => 1,
                'line' => $v,
                'clientid' => $client_id
            );
            $res = M($system.'_tempcreditlineconfig')
                ->field('date,sales')
                ->where($where)
                ->select();
            $data[] = $res;

        }
        return $data;
    }
    /**
     * 合同用户 粉煤灰临时额度查询
     * @param int $client_id 客户id
     * @return arr   3种临时额度 申请情况
     */
    public function getFmhQuoteTimes($client_id,$system,$date = ''){
        $this->today = date('Y-m-d H:i',time());
        if(!empty($date)) $this->today=$date;
        $tempClient = array('20000','50000','100000');
        $data = array();
        foreach($tempClient as $k=>$v){
            $where = array(
                "DATEDIFF('{$this->today}',date) <= SUBSTRING(yxq,1) and stat" => 1,
                'line' => $v,
                'clientid' => $client_id
            );
            $res = M($system.'_tempcreditlineconfig_fmh')
                ->field('date,sales')
                ->where($where)
                ->select();
            $data[] = $res;

        }
        return $data;
    }


    /**
     * 时间
     */
    public function GetDateTimeMk($mktime){
        if($mktime==""||ereg("[^0-9]",$mktime)) return "";
        return gmdate("Y-m-d H:i:s",$mktime + 3600 * 8);
    }
    /**
     * 获取用户名
     */
    public function getClientname($clientid,$system){
        $clientName = M($system.'_guest2')->field('g_name')->where('id='.$clientid)->find();
        return $clientName['g_name'];
    }

    public function getFmhClientname($clientid,$system){
        $clientName = M($system.'_guest2_fmh')->field('g_name')->where('id='.$clientid)->find();
        return $clientName['g_name'];
    }
    /**
     * 获取临时额度
     * @param string $clientid  客户id
     * @param string $date  今天的日期
     * @return integer 临时额度
     */
    public function getTempCredit($clientid,$system,$date=''){
        $this->today = date('Y-m-d H:i',time());
        if(!empty($date)) $this->today=$date;
        $sql = "SELECT * FROM `{$system}_tempcreditlineconfig` WHERE `clientid` = {$clientid} AND DATEDIFF('{$this->today}',date) <= SUBSTRING(yxq,1) AND `stat` = 1 ORDER BY date desc ";
        $res = M()->query($sql);
        $line = 0;
        foreach($res as $val){
            $yxq = substr($val['yxq'],0,1);
            $yxq = strtotime($val['dtime']) + $yxq*24*3600;
            if(strtotime($this->today) < $yxq){
                $line += $val['line'];
            }  
        }
        return $line;
    }
    /**
     * 获取临时额度
     * @param string $clientid  客户id
     * @param string $date  今天的日期
     * @return integer 临时额度
     */
    public function getFmhTempCredit($clientid,$system,$date=''){
        $this->today = date('Y-m-d H:i',time());
        if(!empty($date)) $this->today=$date;
        $sql = "SELECT * FROM `{$system}_tempcreditlineconfig_fmh` WHERE `clientid` = {$clientid} AND DATEDIFF('{$this->today}',date) <= SUBSTRING(yxq,1) AND `stat` = 1 ORDER BY date desc ";
        $res = M()->query($sql);
        $line = 0;
        foreach($res as $val){
            $yxq = substr($val['yxq'],0,1);
            $yxq = strtotime($val['dtime']) + $yxq*24*3600;
            if(strtotime($this->today) < $yxq){
                $line += $val['line'];
            }  
        }
        return $line;
    }
    /**
     * 获取发货额度 ok
     * @param $client 用户id
     * @param $sdate  开始时间
     * @param $edate  结束时间
     */
    public function GetFhJe2($client,$sdate,$edate)
    {
        $bk_sdate=$sdate;
        $bk_edate=$edate;
        $bk_sdate0=$bk_sdate;
        $bk_edate0=date('Y-m-d', strtotime("$bk_edate +1 day"));
//------------已签客户名称-------------------
        $je=0;
        $res_1 = M('yxhb_guest2')
            ->field('g_ye,g_khlx')
            ->where("id=".$client)
            ->find();
        $jcje=$res_1['g_ye'];
        $khlx=$res_1['g_khlx'];//-----7月31日结存金额
//---------------------散装福源鑫(包运)
        $sql="select fh_cate,fh_snbh from yxhb_fh where fh_pp='福源鑫' and fh_date between '".$bk_sdate0."' and '".$bk_edate0."' and fh_bzfs='散装' and fh_stat='1' and fh_client='".$client."' and fh_wlfs='包运' group by fh_cate";
        $res_2 = M()->query($sql);
         foreach($res_2 as $k=>$v){
            $je+=$this ->GetJe('福源鑫',$bk_sdate0,$bk_edate0,'散装',$client,$v['fh_cate'],'包运');
        }
//---------------------散装福源鑫(自提)
        $sql="select fh_cate,fh_snbh from yxhb_fh where fh_pp='福源鑫' and fh_date between '".$bk_sdate0."' and '".$bk_edate0."' and fh_bzfs='散装' and fh_stat='1' and fh_client='".$client."' and fh_wlfs='自提' group by fh_cate";
        $res_2 = M()->query($sql);
        foreach($res_2 as $k=>$v){
            $je+=$this->GetJe('福源鑫',$bk_sdate0,$bk_edate0,'散装',$client,$v['fh_cate'],'自提');
        }
        $ye=$je;
        return $ye;
    }


//获得首次发货时间 fhje2 ok
    public function GetSdate($client,$sdate1,$edate,$cate,$bzfs,$pp,$wlfs)
    {
        $group = 'fh_da';
        if($bzfs=='散装') {
            if ($cate == '矿粉') {
                $where = "fh_pp='{$pp}' and fh_date between '{$sdate1}' and '{$edate}'  and fh_stat='1' and fh_client='{$client}' and fh_cate='矿粉' and fh_bzfs='散装' and fh_snbh not like 'WS%' and fh_wlfs='{$wlfs}' ";
                $group = '';
            } elseif ($cate == '矿粉2') {
                $where = "fh_pp='{$pp}' and fh_date between '{$sdate1}' and '{$edate}' and fh_stat='1' and fh_client='{$client}' and fh_cate='矿粉' and fh_bzfs='散装' and fh_snbh like 'WS%' and fh_wlfs='{$wlfs}' ";
            } else {
                $where = "fh_pp='{$pp}' and fh_date between '{$sdate1}' and '{$edate}'  and fh_stat='1' and fh_client='{$client}' and fh_cate='{$cate}' and fh_bzfs='散装' and fh_wlfs='{$wlfs}' ";
            }
        }
        else{
            $where="fh_pp='{$pp}' and fh_date between '{$sdate1}' and '{$edate}'  and fh_stat='1' and fh_client='{$client}' and fh_cate='{$cate}' and (fh_bzfs='纸袋' or fh_bzfs='编织袋') and fh_wlfs='{$wlfs}' ";
        }
        $res = M('yxhb_fh')
            ->field('fh_da')
            ->where($where)
            ->order('fh_da asc')
            ->group($group)
            ->select();
        $fhsdate=$res[0]['fh_da'];
        if(strtotime($fhsdate)>strtotime($sdate1))
            $bk_sdate = $fhsdate;
        else
            $bk_sdate = $sdate1;
        return $bk_sdate;
    }

//------------------获取发货金额-------------------  fhje2 ok
    public function GetJe($pp,$sdate,$edate,$bzfs,$client,$cate,$wlfs)
    {
        $je     = 0;
        $sdate0 = $sdate;
        $sdate  = $this->GetSdate($client,$sdate0,$edate,$cate,$bzfs,$pp,$wlfs);
        $edate0 = date('Y-m-d', strtotime("$sdate0 +1 month"));
        if($bzfs=='袋装')
            $bzfsql="(fh_bzfs='纸袋' or fh_bzfs='编织袋')";
        else
            $bzfsql="fh_bzfs='散装'";

        if(strtotime($sdate0)!=strtotime($sdate)){
            if($cate!='矿粉2')
                $where="fh_pp='$pp' and  fh_date between '".$sdate." 00:00:00' and '".$edate."' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
            else
                $where="fh_pp='$pp' and  fh_date between '".$sdate." 00:00:00' and '".$edate."' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
        }
        else {
            if($cate!='矿粉2')
                $where="fh_pp='$pp' and  fh_date between '".$sdate."' and '".$edate."' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
            else
                $where="fh_pp='$pp' and  fh_date between '".$sdate."' and '".$edate."' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
        }
        $res = M('yxhb_fh')
            ->field('sum(fh_zl)')
            ->where($where)
            ->select();

//------ 本期发出数量
        $bqzl=round($res[0]['sum(fh_zl)'],2);

//-----起始日期价格
        if($cate!='矿粉2')
            $sql="select dj,yf,stday from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_wlfs as wlfs,ht_date as date from yxhb_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_wlfs as wlfs,tj_da as date from yxhb_tj  ) as t where pp='$pp' and  stday<='$sdate' and  bzfs='$bzfs' and stat='2' and client='".$client."' and cate='".$cate."' and wlfs='$wlfs' order by stday desc,date desc";
        else
            $sql="select dj,yf,stday from (select ht_dj as dj,ht_yf as yf,ht_khmc as client,ht_stday as stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_wlfs as wlfs,ht_date as date from yxhb_ht union all select tj_dj as dj,tj_yf as yf,tj_client as client,tj_stday as stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_wlfs as wlfs,tj_da as date from yxhb_tj  ) as t where pp='$pp' and  stday<='$sdate' and  bzfs='$bzfs' and stat='2' and client='".$client."' and cate='外购矿粉' and wlfs='$wlfs' order by stday desc,date desc";

        $resSdate = M()->query($sql);
        $counttj2 = count($resSdate);

//-----如果起始日期前无调价，以合同单价为准
        if($counttj2==0){
            if($cate!='矿粉2')
                $sql="select ht_dj as dj,ht_yf as yf,ht_stday as stday from yxhb_ht where ht_pp='$pp' and  ht_bzfs='$bzfs' and ht_stat='2' and ht_khmc='".$client."' and ht_cate='".$cate."' and ht_stday<='$sdate' and ht_wlfs='$wlfs' order by ht_stday desc";
            else
                $sql="select ht_dj as dj,ht_yf as yf,ht_stday as stday from yxhb_ht where ht_pp='$pp' and  ht_bzfs='$bzfs' and ht_stat='2' and ht_khmc='".$client."' and ht_cate='外购矿粉' and ht_stday<='$sdate' and ht_wlfs='$wlfs' order by ht_stday desc";
            $res_1 = M()->query($sql);
            $csdj = $res_1[0]['dj'];
            $csyf = $res_1[0]['yf'];
            $csje = $csdj+$csyf;
        }
        else
        {
            $csdj = $resSdate[0]['dj'];
            $csyf = $resSdate[0]['yf'];
            $csje = $csdj+$csyf;
        }
        //-------------袋装价格分段
        if($cate!='矿粉2')
            $sql1="select * from(select tj_dj,tj_yf,tj_stday from (select ht_dj as tj_dj,ht_yf as tj_yf,ht_khmc as client,ht_stday as tj_stday,ht_pp as pp,ht_cate as cate,ht_bzfs as bzfs,ht_stat as stat,ht_wlfs as wlfs,ht_date as date from yxhb_ht union all select tj_dj,tj_yf,tj_client as client,tj_stday,tj_pp as pp,tj_cate as cate,tj_bzfs as bzfs,tj_stat as stat,tj_wlfs as wlfs,tj_da as date from yxhb_tj  ) as t where pp='$pp' and  tj_stday>'$sdate' and tj_stday<'$edate' and  bzfs='$bzfs' and stat='2' and client='".$client."' and cate='".$cate."' and wlfs='$wlfs' order by tj_stday asc,date desc) as o group by tj_stday";
        else
            $sql1="select tj_dj,tj_yf,tj_stday from yxhb_tj where tj_stday>'$sdate' and tj_stday<'$edate'  and tj_stat='2' and  tj_bzfs='$bzfs' and tj_client='".$client."' and tj_cate='外购矿粉' and tj_pp='$pp' and tj_wlfs='$wlfs' order by tj_stday asc";
        $itj     = 1;
        $res_2   = M()->query($sql1);
        $counttj = count($res_2);
//----如果在日期段中无调价，起始日期前的价格做为单价
        if($counttj==0){
            $je+=$csje*$bqzl;
        }
//----如果在日期段中仅有一次调价
        elseif($counttj==1){
            $stday=$sdate;
            $enday=$res_2[0]['tj_stday'];
            if(strtotime($sdate0)!=strtotime($sdate)){
                if($cate!='矿粉2')
                    $sql2="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday 00:00:00' and fh_date<'$enday' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                else
                    $sql2="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday 00:00:00' and fh_date<'$enday' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
            }
            else{
                if($cate!='矿粉2')
                    $sql2="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and  ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='$cate' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                else
                    $sql2="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
            }
            $res_3 = M()->query($sql2);
            $cszl  = round($res_3[0]['sum(fh_zl)'],2);//-----起始价格对应数量
            $je    += $csje*$cszl;
//--------------------------------------
            $stday=$enday;
            $enday=$edate;
            if($cate!='矿粉2')
                $sql2="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
            else
                $sql2 ="select sum(fh_zl) from yxhb_fh where fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
            $res_3 = M()->query($sql2);
            $jszl  = round($res_3[0]['sum(fh_zl)'],2);//-----结束价格对应数量
            $hj    = $res_2[0]['tj_dj']+$res_2[0]['tj_yf'];
            $je    += $hj*$jszl;
        }
//----在日期段中多次调价
        else{
            foreach($res_2 as $k=>$v){
                if($itj==1){
                    $stday = $sdate;
                    $enday = $v['tj_stday'];
                    if(strtotime($sdate0)!=strtotime($sdate)){
                        if($cate!='矿粉2')
                            $where1="fh_pp='$pp' and  fh_date>='$stday 00:00:00' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                        else
                            $where1="fh_pp='$pp' and  fh_date>='$stday 00:00:00' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
                    }
                    else{
                        if($cate!='矿粉2')
                            $where1="fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                        else
                            $where1="fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
                    }
                    $res_4 = M('yxhb_fh')
                        ->field('sum(fh_zl)')
                        ->where($where1)
                        ->select();
                    $cszl =  round($res_4[0]['sum(fh_zl)'],2);//-----起始价格对应数量
                    $je   += $csje*$cszl;
                    $hj   =  $v['tj_dj']+$v['tj_yf'];
                    $itj++;
                }
                elseif($itj == $counttj){
                    $stday=$enday;
                    $enday=$v['tj_stday'];
                    if($cate!='矿粉2')
                        $where1="fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                    else
                        $where1="fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";

                    $res_4 = M('yxhb_fh')
                        ->field('sum(fh_zl)')
                        ->where($where1)
                        ->select();
                    $zl=round($res_4[0]['sum(fh_zl)'],2);//-----价格对应数量
                    $preje =  $hj;
                    $je    += $preje*$zl;
                    $hj    =  $v['tj_dj']+$v['tj_yf'];
                    $itj++;
                    //------------------
                    $stday=$enday;
                    $enday=$edate;
                    if($cate!='矿粉2')
                        $where1="fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                    else
                        $where1="fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";

                    $res_4 = M('yxhb_fh')
                        ->field('sum(fh_zl)')
                        ->where($where1)
                        ->select();
                    $zl=round($res_4[0]['sum(fh_zl)'],2);//-----价格对应数量
                    $preje=$hj;
                    $je+=$preje*$zl;
                    $itj++;
                }
                else{
                    $stday=$enday;
                    $enday=$v['tj_stday'];
                    if($cate!='矿粉2')
                        $where1="fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='".$cate."' and fh_snbh not like 'WS%' and fh_wlfs='$wlfs'";
                    else
                        $where1="fh_pp='$pp' and  fh_date>='$stday' and fh_date<'$enday' and ".$bzfsql." and fh_stat='1' and fh_client='".$client."' and fh_cate='矿粉' and fh_snbh like 'WS%' and fh_wlfs='$wlfs'";
                    $res_4 = M('yxhb_fh')
                        ->field('sum(fh_zl)')
                        ->where($where1)
                        ->select();
                    $zl=round($res_4[0]['sum(fh_zl)'],2);//-----价格对应数量
                    $preje=$hj;
                    $je+=$preje*$zl;
                    $itj++;
                    $hj=$v['tj_dj']+$v['tj_yf'];
                }
            }
        }
        return $je;
    }
//------------------获取发货金额 结束-------------------


}

