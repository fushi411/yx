<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbLkStockApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_produce_stock';

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

        $result['content']['show'][] = array('name'=>'申请单位：',
                                     'value'=> '环保量库库存',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content']['show'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['cretime'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );

        $result['content']['show'][] = array('name'=>'量库时间：',
                                     'value'=> date('Y-m-d H:i',strtotime($res['date'])+$res['time']*3600),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content']['date'] = date('m月d日',strtotime($res['cretime']));
        $result['content']['data'] = $this->deal_data($id);
        
        $result['imgsrc']      = '';
        $result['applyerID']   =  D('YxhbBoss')->getIDFromName($res['rdy']);
        $result['applyerName'] = $res['rdy'];
        $result['stat']        = $res['stat'];
        return $result;
    }
    
    /**
     * 表格显示信息处理
     */
    public function deal_data($id){
        $res = $this->record($id);
        // 账面库存 有 -> 存，没有 -> 获取
        if(!$res['zm']){
            $time = strtotime($res['date'])+$res['time']*3600;
            $auth = data_auth_sign($time);
            $url  = "http://www.fjyuanxin.com/sngl/kf_stock_send_api.php?time={$time}&auth={$auth}";
            $post_data = array();
            $data = send_post($url,$post_data);
            $this->where(array('id'=> $id))->setField('zm',json_encode($data));
        }else{
            $data = json_decode($res['zm'],true);
        }
        
        $lkdata = D('OtherMysql')->getlkkc($res['date']);
        
        $data = array_merge($data,$lkdata);
        $data['lk'][0][0][0] = $res['one_height'];
        $data['lk'][0][1][0] = $res['two_height'];
        $data['lk'][0][2][0] = $res['three_height'];
        $data['lk'][0][3][0] = $res['four_height'];
        $data['lk'][0][4][0] = $res['five_height'];
        $data['lk'][0][5][0] = $res['six_height'];
        $lk_data        = $this->lk_data($data['lk']);
        $data['lk']     = $lk_data;
        $data['date']   = array($res['time'],$lkdata['lk'][1]['time']);
        $data['zm_yestoday'][] = $this->get_yestoday($id);
        $data['zm']     = $this->zm_data($data['zm'],$data['zm_yestoday'],$res['date']);
        return $data;
    }

    /**
     * 账面数据处理
     */
    public function zm_data($data_1,$data_2,$date){
        $year       = date('Y-m',strtotime($date)); 
        $firstday   = $year.'-01';
        $lastday    = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        $query      = "select * from yxhb_config_cate where stat!='0' and config_date<='$lastday' order by config_date desc";
        $type       = M()->query($query);  
        $r          = $type[0];
        $cate_1     = $r['yhk'];
        $cate_2     = $r['ehk'];
        $cate_3     = $r['shk'];
        $cate_4     = $r['sshk'];
        $cate_5     = $r['whk'];
        $cate_6     = $r['lhk'];

        $total_1    = 0;
        $total_2    = 0;
        $total_3    = 0;
        $total_4    = 0;
      
        for($i = 0 ; $i<6 ;$i++){
            $name = '';
            $num  = $i+1;
            eval('$name=$cate_'.$num.';');  
            $temp   = array();
            $temp[] = $num.'#('.$name.')';
            $temp[] = round($data_1[$i],2);
            $temp[] = round($data_2[0]['zm']['sc'][$i],2);
            $temp[] = round($data_2[0]['zm']['xs'][$i],2);
            $temp[] = round($data_2[0]['zm']['kc'][$i],2);

            $result[]  =  $temp;
            $total_1   += $data_1[$i];
            $total_2   += $data_2[0]['zm']['sc'][$i];
            $total_3   += $data_2[0]['zm']['xs'][$i];
            $total_4   += $data_2[0]['zm']['kc'][$i];
        }
        
        $result[] =array(
            '粗灰总',round($data_1[0],2),round($data_2[0]['zm']['sc'][0],2),round($data_2[0]['zm']['xs'][0],2)
        );
        $result[] =array(
            'S95总',round($data_1[1],2),
            round($data_2[0]['zm']['sc'][1] + $data_2[0]['zm']['sc'][4],2),
            round($data_2[0]['zm']['xs'][1] + $data_2[0]['zm']['xs'][4],2),
        );
        $result[] =array(
            'F85总',round($data_1[2]+$data_1[3]+$data_1[4]+$data_1[5]+$data_1[6],2) ,
             
             round($data_2[0]['zm']['sc'][2] + $data_2[0]['zm']['sc'][3] + $data_2[0]['zm']['sc'][5],2),
             round($data_2[0]['zm']['xs'][2] + $data_2[0]['zm']['xs'][3] + $data_2[0]['zm']['xs'][5],2),
        );
        $temp =array(
            '合计',round($total_1,2) ,round($total_2,2) ,round($total_3,2) ,round($total_4,2)
        );
        $result[] = $temp;
        return $result;
    }
    /**
     * 量库数据处理
     */
    public function lk_data($data){
        $year       = date('Y-m',strtotime($data[0]['date'])); 
        $firstday   = $year.'-01';
        $lastday    = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        $query      = "select * from yxhb_config_cate where stat!='0' and config_date<='$lastday' order by config_date desc";
        $type       = M()->query($query);  
        $r          = $type[0];
        $cate_1     = $r['yhk'];
        $cate_2     = $r['ehk'];
        $cate_3     = $r['shk'];
        $cate_4     = $r['sshk'];
        $cate_5     = $r['whk'];
        $cate_6     = $r['lhk'];
      
        $total_1    = 0;
        $total_2    = 0;
        $total_3    = 0;
        $total_4    = 0;
        $total_5    = 0;
        $total_6    = 0;
        $total_7    = 0;
        $total_8    = 0;
        $result     = array();
        for($i = 0 ; $i<6 ;$i++){
            $name = '';
            $num  = $i+1;
            eval('$name=$cate_'.$num.';');  
            $temp   = array();
            $temp[] = $num.'#('.$name.')';
            $temp[] = sprintf("%.1f",$data[0][$i][0]);
            $temp[] = $data[0][$i][1];
            $temp[] = sprintf("%.1f",$data[1][$i][0]);
            $temp[] = $data[1][$i][1];
            $temp[] = round($data[2][$i][0],2);
            $temp[] = $data[2][$i][1];
            $temp[] = round($data[3][$i][0],2);
            $temp[] = $data[3][$i][1];
            $total_1    += $data[0][$i][0];
            $total_2    += $data[0][$i][1];
            $total_3    += $data[1][$i][0];
            $total_4    += $data[1][$i][1];
            $total_5    += $data[2][$i][0];
            $total_6    += $data[2][$i][1];
            $total_7    += $data[3][$i][0];
            $total_8    += $data[3][$i][1];
            $result[] = $temp;
        }
        
        $result[] =array(
            '粗灰总',$data[0][0][1] ,$data[1][0][1] ,$data[2][0][1] ,$data[3][0][1]
        );
        $result[] =array(
            'S95总',$data[0][1][1] ,$data[1][1][1] ,$data[2][1][1] ,$data[3][1][1],
        );
        $result[] =array(
            'F85总',
            $data[0][2][1]+$data[0][3][1]+$data[0][4][1]+$data[0][5][1],
            $data[1][2][1]+$data[1][3][1]+$data[1][4][1]+$data[1][5][1],
            $data[2][2][1]+$data[2][3][1]+$data[2][4][1]+$data[2][5][1],
            $data[3][2][1]+$data[3][3][1]+$data[3][4][1]+$data[3][5][1],
        );
        $result[] =array(
            '合计',$total_2 ,$total_4 ,$total_6,$total_8
        );
        return $result;
    }


    /**
     * 获取表格信息
     */
    public function getTableInfo(){
        $datetime    = I('post.datetime');
        //$datetime    = '2018-08-13 07:00';
        $res['date'] = date('Y-m-d',strtotime($datetime));
        $res['time'] = date('H',strtotime($datetime));
        // if(date('H',time())< $res['time']) return array('code' => 404,'msg' => '不能提前提交' );
        $time = strtotime($res['date']);
        $auth = data_auth_sign($time);
        $url  = "http://www.fjyuanxin.com/sngl/kf_stock_send_api.php?time={$time}&auth={$auth}";
        $post_data = array();
        $data = send_post($url,$post_data);
         
        $lkdata = D('OtherMysql')->getlkkc($res['date']);
        
        $data = array_merge($data,$lkdata);
        
        $lk_data        = $this->lk_data($data['lk']);
        $data['lk']     = $lk_data;
        $data['date']   = array($res['time'],$lkdata['lk'][1]['time']);
        $data['zm_yestoday'][] = $this->get_yestoday(1,$datetime);
        $data['zm']     = $this->zm_data($data['zm'],$data['zm_yestoday'],$res['date']);
        
        return array('code' => 200,'msg' => '请求成功' , 'data' => $data );
    }


    /**
     * 获取上一日的销售 入库 出库
     */
    public function get_yestoday($id,$paramDate){
        $yestoday = array();
        $res      = $this->record($id);
        if($paramDate) $res['date'] = $paramDate;
        $yes      = date('Y-m-d',strtotime($res['date'])-24*3600);
        $year     = date('Y-m',strtotime($res['date'])-24*3600);
        list($qckc_1,$qckc_2,$qckc_3,$qckc_4,$qckc_5,$qckc_6,$qckc_S95,$qckc_F75) = $yestoday['zm']['kc'] = $this-> getkc($id,$paramDate);
        $flag    = 0;
        $query   = "select date from (select date_format(fh_da,'%Y-%m-%d') as date from yxhb_fh where date_format(fh_da,'%Y-%m')='$year' and fh_stat='1' GROUP BY date_format(fh_da,'%Y-%m-%d') union select date_format(sc_date,'%Y-%m-%d') as date from yxhb_product_daily_report where date_format(sc_date,'%Y-%m')='$year' GROUP BY date_format(sc_date,'%Y-%m-%d')) as t order by date";
        $dateArr = M()->query($query);
       
        foreach($dateArr as $r){
           //---1#库销售数量---
           $query1                 = "select sum(fh_zl) as zl from yxhb_fh where fh_da='".$r['date']."'  and fh_stat='1' and fh_kh='环保1#'";
           $r1                     = M()->query($query1);
           $sale_1                 = $r1[0]['zl']; 
           if($r['date'] == $yes) $yestoday['zm']['xs'][] = $r1[0]['zl']; 
           //---2#库销售数量---
           $query1                 = "select sum(fh_zl) as zl from yxhb_fh where fh_da='".$r['date']."'  and fh_stat='1' and fh_kh='环保2#'";
           $r1                     = M()->query($query1); 
           $sale_2                 = $r1[0]['zl'];  
           if($r['date'] == $yes) $yestoday['zm']['xs'][] = $r1[0]['zl'];  
           //---3#库销售数量---
           $query1                  = "select sum(fh_zl) as zl from yxhb_fh where fh_da='".$r['date']."'  and fh_stat='1' and fh_kh='环保3#'";
           $r1                      = M()->query($query1); 
           $sale_3                  = $r1[0]['zl'];  
           if($r['date'] == $yes) $yestoday['zm']['xs'][]  = $r1[0]['zl'];    
           //---4#库销售数量---
           $query1                  = "select sum(fh_zl) as zl from yxhb_fh where fh_da='".$r['date']."'  and fh_stat='1' and fh_kh='环保4#'";
           $r1                      = M()->query($query1);  
           $sale_4                  = $r1[0]['zl'];  
           if($r['date'] == $yes) $yestoday['zm']['xs'][]  = $r1[0]['zl'];   
           //---5#库销售数量---
           $query1                  = "select sum(fh_zl) as zl from yxhb_fh where fh_da='".$r['date']."'  and fh_stat='1' and fh_kh='环保5#'  and fh_stat2='0'";
           $r1                      = M()->query($query1); 
           $sale_5                  = $r1[0]['zl'];  
           if($r['date'] == $yes) $yestoday['zm']['xs'][]  = $r1[0]['zl'];   
           //---6#库销售数量---
           $query1                  = "select sum(fh_zl) as zl from yxhb_fh where fh_da='".$r['date']."'  and fh_stat='1' and fh_kh='环保6#'";
           $r1                      = M()->query($query1); 
           $sale_6                  = $r1[0]['zl']; 
           if($r['date'] == $yes) $yestoday['zm']['xs'][]  = $r1[0]['zl'];   

           //---S95销售数量---
           $query1                  = "select sum(fh_zl) as zl from yxhb_fh where fh_da='".$r['date']."'  and fh_stat='1' and fh_cate='粗灰'";
           $r1                      = M()->query($query1);  
           $sale_S95                = $r1[0]['zl']; 
           if($r['date'] == $yes) $yestoday['zm']['xs'][]  = $r1[0]['zl'];    
           //---F75销售数量---
           $query1                  = "select sum(fh_zl) as zl from yxhb_fh where fh_da='".$r['date']."'  and fh_stat='1' and (fh_cate='F75' or fh_cate='F95' or fh_cate='F85')";
           $r1                      = M()->query($query1);  
           $sale_F75                = $r1[0]['zl']; 
           if($r['date'] == $yes) $yestoday['zm']['xs'][]  = $r1[0]['zl'];  
           /*****生产数据*****/    
           //连接220生产数据库

           //---生产数量计算---
           $query1                  = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date='".$r['date']."' and kh='1#库'";
           $r1                      = M()->query($query1); 
           $sc_1                    = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
           if($r['date'] == $yes) $yestoday['zm']['sc'][]  = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];

           $query1                  = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date='".$r['date']."' and kh='2#库'";
           $r1                      = M()->query($query1); 
           $sc_2                    = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
           if($r['date'] == $yes) $yestoday['zm']['sc'][]  = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];

           $query1                  = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date='".$r['date']."' and kh='3#库'";
           $r1                      = M()->query($query1); 
           $sc_3                    = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
           if($r['date'] == $yes) $yestoday['zm']['sc'][]  = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];

           $query1                  = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date='".$r['date']."' and kh='4#库'";
            $r1                     = M()->query($query1); 
            $sc_4_1                 = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
            
            if($r['date'] == $yes) $yestoday['zm']['sc'][] = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
           /*外购矿粉*/
           $sc_4                    = $sc_4_1+$sc_4_2;
           $query1                  = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date='".$r['date']."' and kh='5#库'";
           $r1                      = M()->query($query1); 
           $sc_5                    = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
           if($r['date'] == $yes) $yestoday['zm']['sc'][]  = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];

            $query1                 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date='".$r['date']."' and kh='6#库'";
            $r1                     = M()->query($query1); 
            $sc_6                   = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
            if($r['date'] == $yes) $yestoday['zm']['sc'][] = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];

            $query1                 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date='".$r['date']."' and cate='粗灰'";
            $r1                     = M()->query($query1); 
            if($r['date'] == $yes) $yestoday['zm']['sc'][] = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
            $sc_S95                 = $sc_S95_1+$sc_4_2;

            $query1                 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date='".$r['date']."' and (cate='F75' or cate='F85')";
            $r1                     = M()->query($query1); 
            if($r['date'] == $yes) $yestoday['zm']['sc'][] = $r1[0]['sum(gjcl)']+$r1[0]['sum(tlh)']+$r1[0]['sum(gzfhkf)'];
           
            //---------库存计算-----------
            //期初库存
            if($flag==0){
                $kc_1=$sc_1-$sale_1+$qckc_1;
                $kc_2=$sc_2-$sale_2+$qckc_2;
                $kc_3=$sc_3-$sale_3+$qckc_3;
                $kc_4=$sc_4-$sale_4+$qckc_4;
                $kc_5=$sc_5-$sale_5+$qckc_5;
                $kc_6=$sc_6-$sale_6+$qckc_6;
                $kc_S95=$sc_S95-$sale_S95+$qckc_S95;
                $kc_F75=$sc_F75-$sale_F75+$qckc_F75;
                $flag=1;
            }else{
                $kc_1=$sc_1-$sale_1+$kc_1;
                $kc_2=$sc_2-$sale_2+$kc_2;
                $kc_3=$sc_3-$sale_3+$kc_3;
                $kc_4=$sc_4-$sale_4+$kc_4;
                $kc_5=$sc_5-$sale_5+$kc_5;
                $kc_6=$sc_6-$sale_6+$kc_6;
                $kc_S95=$sc_S95-$sale_S95+$kc_S95;
                $kc_F75=$sc_F75-$sale_F75+$kc_F75;
            }
            $yestoday['zm']['kc'] = array($kc_1 , $kc_2 ,$kc_3 ,$kc_4 ,$kc_5 ,$kc_6);
                
            if($r['date'] == $yes) break;
        }
            
        return $yestoday;
    }

    /**
     * 获取库存  ** 代码看瞎
     */ 
    public function getkc($id,$paramDate){
        $res   = $this->record($id);
        if($paramDate) $res['date'] = $paramDate;
        $year  = date("Y-m",strtotime($res['date']));
        $year1=date("Y-m",strtotime("-1 month",strtotime($year)));
        //期初库存和盘点数计算
        $stday=$year.'-01';
        $last_month=date("Y-m",strtotime("-1 months",strtotime($stday)));
        
        //原始库存
        $query2 = "select qcsl from yxhb_beginkc where stat='1' and kh='1#'";
        $r2     = M()->query($query2);
        $qc_1   = $r2[0]['qcsl'];

        $query2 = "select qcsl from yxhb_beginkc where stat='1' and kh='2#'";
        $r2     = M()->query($query2);
        $qc_2   = $r2[0]['qcsl'];
          
        $query2 = "select qcsl from yxhb_beginkc where stat='1' and kh='3#'";
        $r2     = M()->query($query2);
        $qc_3   = $r2[0]['qcsl'];
          
        $query2 = "select qcsl from yxhb_beginkc where stat='1' and kh='4#'";
        $r2     = M()->query($query2);
        $qc_4   = $r2[0]['qcsl'];
          
        $query2 = "select qcsl from yxhb_beginkc where stat='1' and kh='5#'";
        $r2     = M()->query($query2);
        $qc_5   = $r2[0]['qcsl'];
          
        $query2 = "select qcsl from yxhb_beginkc where stat='1' and kh='6#'";
        $r2     = M()->query($query2);
        $qc_6   = $r2[0]['qcsl'];
          
        $query2 = "select sum(qcsl) as a from yxhb_beginkc where stat='1' and cate='S95'";
        $r2     = M()->query($query2);
        $qc_S95 = $r2[0]['a'];
          
        $query2 = "select sum(qcsl) as a from yxhb_beginkc where stat='1' and (cate='F75' or cate='F85')";
        $r2     = M()->query($query2);
        $qc_F75 = $r2[0]['a'];
          
        //原始盘点数
        $query2 = "select * from yxhb_check_kc where stat='1' and date_format(pd_date,'%Y-%m')='$year'";
        $r2     = M()->query($query2);
          
        $pd_1   = $r2[0]['pdsl1'];
        $pd_2   = $r2[0]['pdsl2'];
        $pd_3   = $r2[0]['pdsl3'];
        $pd_4   = $r2[0]['pdsl4'];
        $pd_5   = $r2[0]['pdsl5'];
        $pd_6   = $r2[0]['pdsl6'];
        $pd_S95 = $pd_1;
        $pd_F75 = $pd_2+$pd_3+$pd_4+$pd_5+$pd_6;

        //---------期间出入库数据-----------
        //销售
        $query2 = "select sum(fh_zl) as zl from yxhb_fh where fh_da>='2018-04-01'  and fh_da<'$stday' and fh_stat='1' and fh_kh='环保1#'";
        $r2     = M()->query($query2);
        $qcxs_1 = $r2[0]['zl'];
          
        $query2 = "select sum(fh_zl) as zl from yxhb_fh where fh_da>='2018-04-01'  and fh_da<'$stday' and fh_stat='1' and fh_kh='环保2#'";
        $r2     = M()->query($query2);
        $qcxs_2 = $r2[0]['zl'];
          
        $query2 = "select sum(fh_zl) as zl from yxhb_fh where fh_da>='2018-04-01'  and fh_da<'$stday' and fh_stat='1' and fh_kh='环保3#'";
        $r2     = M()->query($query2);
        $qcxs_3 = $r2[0]['zl'];
          
        $query2 = "select sum(fh_zl) as zl from yxhb_fh where fh_da>='2018-04-01'  and fh_da<'$stday' and fh_stat='1' and fh_kh='环保4#'";
        $r2     = M()->query($query2);
        $qcxs_4 = $r2[0]['zl'];
          
        $query2 = "select sum(fh_zl) as zl from yxhb_fh where fh_da>='2018-04-01'  and fh_da<'$stday' and fh_stat='1' and fh_kh='环保5#'  and fh_stat2='0'";
        $r2     = M()->query($query2);
        $qcxs_5 = $r2[0]['zl'];
          
        $query2 = "select sum(fh_zl) as zl from yxhb_fh where fh_da>='2018-04-01'  and fh_da<'$stday' and fh_stat='1' and fh_kh='环保6#'";
        $r2     = M()->query($query2);
        $qcxs_6 = $r2[0]['zl'];
          
        $query2 = "select sum(fh_zl) as zl from yxhb_fh where fh_da>='2018-04-01'  and fh_da<'$stday' and fh_stat='1' and fh_cate='粗灰'";
        $r2     = M()->query($query2);
        $qcxs_S95 = $r2[0]['zl'];
          
        $query2 = "select sum(fh_zl) as zl from yxhb_fh where fh_da>='2018-04-01'  and fh_da<'$stday' and fh_stat='1' and (fh_cate='F75' or fh_cate='F95' or fh_cate='F85')";
        $r2     = M()->query($query2);
        $qcxs_F75 = $r2[0]['zl'];
          
        //生产
        $query2 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date>='2018-04-01'  and sc_date<'$stday' and kh='1#库'";
        $r2     = M()->query($query2);
          
        $qcsc_1 += ($r2[0]['sum(gjcl)']+$r2[0]['sum(tlh)']+$r2[0]['sum(gzfhkf)']);
        $query2 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date>='2018-04-01'  and sc_date<'$stday' and kh='2#库'";
        $r2     = M()->query($query2);
          
        $qcsc_2 += ($r2[0]['sum(gjcl)']+$r2[0]['sum(tlh)']+$r2[0]['sum(gzfhkf)']);
        $query2 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date>='2018-04-01'  and sc_date<'$stday' and kh='3#库'";
        $r2     = M()->query($query2);
          
        $qcsc_3 += ($r2[0]['sum(gjcl)']+$r2[0]['sum(tlh)']+$r2[0]['sum(gzfhkf)']);
        $query2 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date>='2018-04-01'  and sc_date<'$stday' and kh='4#库'";
        $r2     = M()->query($query2);
          
        $qcsc_4 += ($r2[0]['sum(gjcl)']+$r2[0]['sum(tlh)']+$r2[0]['sum(gzfhkf)']);
        /*外购矿粉*/


        $query2 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date>='2018-04-01'  and sc_date<'$stday' and kh='5#库'";
        $r2     = M()->query($query2);
          
        $qcsc_5 += ($r2[0]['sum(gjcl)']+$r2[0]['sum(tlh)']+$r2[0]['sum(gzfhkf)']);
        $query2 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date>='2018-04-01'  and sc_date<'$stday' and kh='6#库'";
        $r2     = M()->query($query2);
          
        $qcsc_6 += ($r2[0]['sum(gjcl)']+$r2[0]['sum(tlh)']+$r2[0]['sum(gzfhkf)']);
        $query2 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date>='2018-04-01'  and sc_date<'$stday' and cate='粗灰'";
        $r2     = M()->query($query2);
          
        $qcsc_S95 += ($r2[0]['sum(gjcl)']+$r2[0]['sum(tlh)']+$r2[0]['sum(gzfhkf)']);
        $query2 = "select sum(gjcl),sum(tlh),sum(gzfhkf) from yxhb_product_daily_report where sc_date>='2018-04-01'  and sc_date<'$stday' and (cate='F75' or cate='F85')";
        $r2     = M()->query($query2);
          
        $qcsc_F75 += ($r2[0]['sum(gjcl)']+$r2[0]['sum(tlh)']+$r2[0]['sum(gzfhkf)']);
        //盘点
        $query2 = "select sum(pdsl1) as a,sum(pdsl2) as b,sum(pdsl3) as c,sum(pdsl4) as d,sum(pdsl5) as e,sum(pdsl6) as f from yxhb_check_kc where stat='1' and pd_date>='2018-04-01'  and pd_date<'$stday'";
        $r2    = M()->query($query2);
          
        $qcpd_1 = $r2[0]['a'];
        $qcpd_2 = $r2[0]['b'];
        $qcpd_3 = $r2[0]['c'];
        $qcpd_4 = $r2[0]['d'];
        $qcpd_5 = $r2[0]['e'];
        $qcpd_6 = $r2[0]['f'];
        $qcpd_S95 = $qcpd_1;
        $qcpd_F75 = $qcpd_2+$qcpd_3+$qcpd_4+$qcpd_5+$qcpd_6;

        if($stday=='2018-04-01'){
            $qc_1=$qc_1;
            $qc_2=$qc_2;
            $qc_3=$qc_3;
            $qc_4=$qc_4;
            $qc_5=$qc_5;
            $qc_6=$qc_6;
        }else{
            $qc_1=($qc_1-$qcxs_1+$qcsc_1+$qcpd_1);
            $qc_2=($qc_2-$qcxs_2+$qcsc_2+$qcpd_2);
            $qc_3=($qc_3-$qcxs_3+$qcsc_3+$qcpd_3);
            $qc_4=($qc_4-$qcxs_4+$qcsc_4+$qcpd_4);
            $qc_5=($qc_5-$qcxs_5+$qcsc_5+$qcpd_5);
            $qc_6=($qc_6-$qcxs_6+$qcsc_6+$qcpd_6);
            $qc_S95=($qc_S95-$qcxs_S95+$qcsc_S95+$qcpd_S95);
            $qc_F75=($qc_F75-$qcxs_F75+$qcsc_F75+$qcpd_F75);
        }
        $qckc_1=($qc_1+$pd_1);
        $qckc_2=($qc_2+$pd_2);
        $qckc_3=($qc_3+$pd_3);
        $qckc_4=($qc_4+$pd_4);
        $qckc_5=($qc_5+$pd_5);
        $qckc_6=($qc_6+$pd_6);
        $qckc_S95=($qc_S95+$pd_S95);
        $qckc_F75=($qc_F75+$pd_F75);
        return array($qckc_1,$qckc_2,$qckc_3,$qckc_4,$qckc_5,$qckc_6,$qckc_S95,$qckc_F75);
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
     * 拒收
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function refuseRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',3);
    }
         /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $result[] = array('name'=>'提交时间：',
                                     'value'=>  date('m-d H:i',strtotime($res['cretime'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>  date('Y-m-d H:i',strtotime($res['date'])+$res['time']*3600) ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['rdy'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['bz'] ? $res['bz'] :'无',
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
        return $this->field(true)->where($map)->getField('rdy');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);

        $result = array(
            array('提交时间',date('Y-m-d H:i',strtotime($res['cretime']))),
            array('量库时间',date('Y-m-d H:i',strtotime($res['date'])+$res['time']*3600)),
            array('相关说明',$res['bz']?$res['bz']:'无')
        );
        return $result;
    }

    /**
     * 原材料采购付款提交 
     */
    public function submit(){
        $scale     = I('post.scale');
        $text      = I('post.text');
        $datetime  = I('post.datetime');
        $copyto_id = I('post.copyto_id');
        $sign_id   = I('post.sign');
        $sign_arr  = explode(',',$sign_id);
        $sign_arr  = array_filter($sign_arr);// 去空
        $sign_arr  = array_unique($sign_arr); // 去重

        $date = Date('Y-m-d',strtotime($datetime));
        $time = Date('H',strtotime($datetime));
        $flag = D('OtherMysql')->haveProduceRecord($date,$time);
        if(!$flag) return array('code' => 404,'msg' => "请确认是否有{$datetime}量库记录");
        
        // 提交同一时间段的
        $Arrtime  = Date('Y-m-d',strtotime($datetime));
        $Arrhour  = Date('H',strtotime($datetime));
        $dataArr = M('yxhb_produce_stock')
                    ->where("date_format(`date`,'%Y-%m-%d')='{$Arrtime}' and time={$Arrhour} and stat BETWEEN 1 and 2 ")
                    ->select();
         if(!empty($dataArr))  return array('code' => 404,'msg' => '此时间段已有一条记录，确认时间段');

        // a - 非当天不能提交     b - 19点以前不能提交19点的
        if( date('Y-m-d',strtotime($datetime)) != date('Y-m-d',time())) return array('code' => 404,'msg' => '非当天不能提交');
        if(strtotime($datetime) > time() )  return array('code' => 404,'msg' => '禁止提前提交');

        $insert = array(
            'date'         => Date('Y-m-d',strtotime($datetime)),
            'time'         => Date('H',strtotime($datetime)),
            'one_height'   => $scale[0]['name']?$scale[0]['name']:0 ,
            'two_height'   => $scale[1]['name']?$scale[1]['name']:0 ,
            'three_height' => $scale[2]['name']?$scale[2]['name']:0 ,
            'four_height'  => $scale[3]['name']?$scale[3]['name']:0 ,
            'five_height'  => $scale[4]['name']?$scale[4]['name']:0 ,
            'six_height'   => $scale[5]['name']?$scale[5]['name']:0 ,
            'rdy'          => session('name'),
            'stat'         => 2 ,
            'bz'           => $text,
            'cretime'      => date('Y-m-d H:i:s',time()) ,
        );

        if(!M('yxhb_produce_stock')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $result = M('yxhb_produce_stock')->add($insert);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'LkStockApply', $result);
        }
        // 签收通知
        $all_arr = array();
        foreach($sign_arr as $val){
            $per_name = M('yxhb_boss')->where(array('wxid'=>$val))->Field('name,id')->find();
            $data = array(
                'pro_id'        => 31,
                'aid'           => $result,
                'per_name'      => $per_name['name'],
                'per_id'        => $per_name['id'],
                'app_stat'      => 0,
                'app_stage'     => 1,
                'app_word'      => '',
                'time'          => date('Y-m-d H:i',time()),
                'approve_time'  => '0000-00-00 00:00:00',
                'mod_name'      => 'LkStockApply',
                'app_name'      => '签收',
                'apply_user'    => '',
                'apply_user_id' => 0, 
                'urge'          => 0,
            );
            $all_arr[]=$data;
        }
        $boss_id = implode('|',$sign_arr);
        M('yxhb_appflowproc')->addAll($all_arr);
        $this->sendMessage($result,$boss_id);
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }
    /**
     * 通知信息发送
     * @
     */
    public function sendMessage($apply_id,$boss){
        $system = 'yxhb';
        $mod_name = 'LkStockApply';
        $logic = D(ucfirst($system).$mod_name, 'Logic');
        $res   = $logic->record($apply_id);
        $systemName = array('kk'=>'建材', 'yxhb'=>'环保');
        // 微信发送
        $WeChat = new \Org\Util\WeChat;
        
        $descriptionData = $logic->getDescription($apply_id);
     
        $title = '量库库存(签收)';
        $url = "https://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$apply_id."&modname=".$mod_name;
      
        $applyerName='('.$res['rdy'].'提交)';
        $description = "您有一个流程需要签收".$applyerName;

        $receviers = "wk|HuangShiQi|".$boss;
        foreach( $descriptionData as $val ){
            $description .= "\n{$val['name']}{$val['value']}";
        }
        $agentid = 15;
        $WeChat = new \Org\Util\WeChat;
        $info = $WeChat->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod_name,$system);
    }


}