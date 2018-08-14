<?php
namespace Light\Model;
use Think\Model;

/**
 * 页面数据逻辑模型
 * @author 
 */

class OtherMysqlModel extends Model {
    // 虚拟模型
    protected $autoCheckFields = false;
    protected $connection = array(
        'db_type'  => 'mysql',
        'db_user'  => 'fjyuanxin',
        'db_pwd'   => 'fjyuanxin',
        'db_host'  => '220.160.55.177',
        'db_port'  => '3306',
        'db_name'  => 'sqlfjyuanxin',
        'db_charset' =>    'utf8',
    );
    /**
     * 量库库存
     */
    public function getlkkc($today){
        
        $query ="SELECT * FROM (
                            SELECT
                                date,time, yi_h,er_h,san_h,si_h,wu_h,liu_h,state
                            FROM
                                yxhb_produce_recordb
                            UNION
                                SELECT
                                    date,time,yi_h,er_h,san_h,si_h,wu_h,liu_h,state
                                FROM
                                    yxhb_produce_record
                        ) AS t
                    WHERE
                        date <= '{$today}'
                    AND state = 1
                    AND (time = '7' OR time = '19')
            GROUP BY date HAVING time=MIN(time)
                    ORDER BY
                        date DESC,
                        time DESC
                    LIMIT 0,4";
    
        $data = $this->query($query);
        $api_data = array();
        foreach($data as $key => $row){
            //库深
            $ks_1 = $row['yi_h'];
            $ks_2 = $row['er_h'];
            $ks_3 = $row['san_h'];
            $ks_4 = $row['si_h'];
            $ks_5 = $row['wu_h'];
            $ks_6 = $row['liu_h'];
            //库存数量计算
            $kc_1=((24-$row['yi_h'])*211);
            $kc_2=((24-$row['er_h'])*211);
            $kc_3=((24-$row['san_h'])*211);
            $kc_4=((24-$row['si_h'])*211);
            $kc_5=((24-$row['wu_h'])*211);
            $kc_6=((24-$row['liu_h'])*211);
            $kc_ch=$kc_1;
            $kc_F85=$kc_2+$kc_3+$kc_4+$kc_5+$kc_6;

            $api_data['lk'][$key]['date'] = $row['date'];
            $api_data['lk'][$key]['time'] = $row['time'];
            $api_data['lk'][$key][]       = array(number_format($ks_1,2,'.',''),$kc_1);
            $api_data['lk'][$key][] 	  = array(number_format($ks_2,2,'.',''),$kc_2);
            $api_data['lk'][$key][] 	  = array(number_format($ks_3,2,'.',''),$kc_3);
            $api_data['lk'][$key][] 	  = array(number_format($ks_4,2,'.',''),$kc_4);
            $api_data['lk'][$key][] 	  = array(number_format($ks_5,2,'.',''),$kc_5);
            $api_data['lk'][$key][] 	  = array(number_format($ks_6,2,'.',''),$kc_6);
        }
         return $api_data;
    }
    /**
     * 账面库存
     */
    public function LkStockUpdate($id){
        $res  = D('YxhbLkStockApply','Logic')->record($id);
        $data = array(
            'yi_h'  => $res['one_height'],
            'er_h'  => $res['two_height'],
            'san_h' => $res['three_height'],
            'si_h'  => $res['four_height'],
            'wu_h'  => $res['five_height'],
            'liu_h' => $res['six_height'],
            'bz'    => $res['bz']
        );
        
        $this->table('yxhb_produce_record')->where("date_format(`date`,'%Y-%m-%d')= '{$res['date']}' and time={$res['time']}" )->save($data);
        $this->table('yxhb_produce_recordb')->where("date_format(`date`,'%Y-%m-%d')= '{$res['date']}' and time={$res['time']}" )->save($data);
    }
}