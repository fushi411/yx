<?php
namespace Light\Model;
use Think\Model;

/**
 * 页面数据逻辑模型
 * @author 
 */

class GuestModel extends Model {
    // 虚拟模型
    protected $autoCheckFields = false;
    // 获取建材账户有效客户
    public function get_kk_guest($data){
        $like = $data?"and (g_helpword like '%{$data}%' or g_name like '%{$data}%')":'';
        $res = D('Customer')->getVaildUser();
        $result = $this->getKkHtUserId();
        $res = array_merge($res,$result);
        $res = array_unique($res);
        $id = '';
        foreach( $res as $v){
            $id .= "{$v},";
        }
        
        $id = trim($id,',');
        $sql = "SELECT
                    id,
                    g_name AS text,
                    g_khjc AS jc
                FROM
                    kk_guest2
                WHERE
                    id  IN ({$id})  {$like} 
                GROUP BY
                    g_name
                ORDER BY
                    g_name ASC";
        $res  = M()->query($sql);
        return $res;
    }
    
    // 获取环保账户有效客户
    public function get_yxhb_guest($data){
        $today = date('Y-m-d',time());
        
        $like = $data?"and (g_helpword like '%{$data}%' or g_name like '%{$data}%')":'';
        $res = D('Customer')->getYxhbVaildUser();
        $result = $this->getYxhbHtUserId();
        $res = array_merge($res,$result);
        $res = array_unique($res);
        $id = '';
        foreach( $res as $v){
            $id .= "{$v},";
        }

        $id = trim($id,',');
        $sql = "SELECT
                    id,
                    g_name AS text,
                    g_khjc AS jc
                FROM
                    yxhb_guest2
                WHERE
                    id  IN ({$id})  {$like} 
                GROUP BY
                    g_name
                ORDER BY
                    g_name ASC";
        $res  = M()->query($sql);
        return $res;
    }

    // 获取建材合同有效客户
    public function getKkHtUser(){
        $today = date('Y-m-d',time());
        $data = I('math');
        $like = $data?"where g_helpword like '%{$data}%' or g_name like '%{$data}%'":'';
        $sql = "select id,g_name as text,g_khjc as jc from (select a.id as id,g_name,g_helpword,g_khjc FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM kk_guest2 where id=any(select a.reid as id FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t {$like} order by g_name ASC";
        $res = M()->query($sql);
        return $res;
    }

    // 获取环保合同有效客户
    public function getYxhbHtUser(){
        $today = date('Y-m-d',time());
        $data = I('math');
        $like = $data?"where g_helpword like '%{$data}%' or g_name like '%{$data}%'":'';
        $sql = "select id,g_name as text,g_khjc as jc from (select a.id as id,g_name,g_helpword,g_khjc FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM yxhb_guest2 where id=any(select a.reid as id FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t {$like} order by g_name ASC";
        $res = M()->query($sql);
        return $res;
    }
    
    // 获取建材合同有效客户id
    public function getKkHtUserId(){
        $today = date('Y-m-d',time());
        $sql = "select id from (select a.id as id,g_name,g_helpword,g_khjc FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM kk_guest2 where id=any(select a.reid as id FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t  order by g_name ASC";
        $res = M()->query($sql);
        $id  = array();
    
        foreach($res as $val){
                $id[] = $val['id'];
        }
        return $id;
    }

    // 获取环保合同有效客户id
    public function getYxhbHtUserId(){
        $today = date('Y-m-d',time());
        $sql = "select id from (select a.id as id,g_name,g_helpword,g_khjc FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM yxhb_guest2 where id=any(select a.reid as id FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t order by g_name ASC";
        $res = M()->query($sql);
        $id  = array();
        foreach($res as $val){
                $id[] = $val['id'];
        }
        return $id;
    }

    /**
     * 获取建材备案总客户 
     * @param string 客户名或客户简称
     * @param array  备案客户
     */
    public function getKkrecordUser($keyWord){
        $field = 'id,g_xmmc,g_name as text';
        $map   = array(
            'reid'    => 0,
            //'g_stat3' => 1
        );
        $where = array(
            'g_name'     => array('like',"%{$keyWord}%"),
            'g_helpword' => array('like',"%{$keyWord}%"),
            '_logic'     => 'or'
        );
        $map['_complex'] = $where;
        $data = M('kk_guest2')->field($field)->where($map)->order('g_name ASC')->select();
        $data = $this->addTailAffix($data);
        return $data;
    }

    /**
     * 获取粉煤灰备案总客户
     * @param string 客户名或客户简称
     * @param array  备案客户
     */
    public function getKkrecordUser_fmh($keyWord){
        $field = 'id,g_xmmc,g_name as text';
        $map   = array(
            'reid'    => 0,
           // 'g_stat3' => 1
        );
        $where = array(
            'g_name'     => array('like',"%{$keyWord}%"),
            'g_helpword' => array('like',"%{$keyWord}%"),
            '_logic'     => 'or'
        );
        $map['_complex'] = $where;
        $data = M('kk_guest2_fmh')->field($field)->where($map)->order('g_name ASC')->select();
        $data = $this->addTailAffix($data);
        return $data;
    }

    /**
     * 分发器
     */
    public function getGuest($system,$keyWord,$mod){
        $data = $this->getGuestData($system,$keyWord);
        $map = array(
            'url'      => $system,
            'mod_name' => $mod,
            'stat'     => 1, 
        );
        $function = M($system.'_guest_function_configuration')->where($map)->find();
        $function = $function['guest_function'];
        return $this->$function($data);
    }

    /**
     * 总客户 有效
     */
    public function getEffectCustomer($data){
        $res = array();
        if( empty($data) || !is_array($data)) return $res;
        list($father,$child) = $data;
        foreach($father as $v){
            if($v['status'] == '总正常') $res[] = $v;
        }
        return $res;
    }
    /**
     * 子客户 有效
     */
    public function getEffectChild($data){
        $res = array();
        if( empty($data) || !is_array($data)) return $res;
        list($father,$child) = $data;
        foreach($child as $v){
            foreach($v as $val){
                if($val['status'] == '正常') $res[] = $val;
            }
        }
        return $res;
    }
    /**
     * 总客户 有效+冻结 
     */
    public function getFrozenCustomer($data){
        $res = array();
        if( empty($data) || !is_array($data)) return $res;
        list($father,$child) = $data;
        foreach($father as $v){
            if($v['status'] == '总冻结' || $v['status'] == '总正常') $res[] = $v;
        }
        return $res;
    }
    /**
     *  子客户 有效+冻结
     */
    public function getChild($data){
        $res = array();
        if( empty($data) || !is_array($data)) return $res;
        list($father,$child) = $data;
        foreach($child as $v){
            foreach($v as $val){
                if($val['status'] == '冻结' || $val['status'] == '正常') $res[] = $val;
            }
        }
        return $res;
    }
    /**
     * 所有客户 有效
     */
    public function getAllEffectCustomer($data){
        $res = array();
        if( empty($data) || !is_array($data)) return $res;
        list($father,$child) = $data;
        foreach($father as $v){
            if($v['status'] == '总正常') $res[] = $v;
        }
        foreach($child as $v){
            foreach($v as $val){
                if($val['status'] == '正常') $res[] = $val;
            }
        }
        return $res;
    }
    /**
     * 所有客户 有效+冻结
     */
    public function getAllChild($data){
        $res = array();
        if( empty($data) || !is_array($data)) return $res;
        list($father,$child) = $data;
        foreach($father as $v){
            if($v['status'] == '总正常' || $v['status'] == '总冻结') $res[] = $v;
        }
        foreach($child as $v){
            foreach($v as $val){
                if($val['status'] == '正常' || $val['status'] == '冻结') $res[] = $val;
            }
        }
        return $res;
    }
    /**
     *  获取所有客户的状态
     *  @param string 系统
     *  @param string 关键字查询
     *  @return data 总客户数据  子客户数据 
     */ 
    public function getGuestData($system,$keyWord){
        $today=date("Y-m-d",time()+8*3600);
        $thisday = date("Y-m-d",time());
        if(!isset($action)) 
        {
            $action = "";
            $bk_sdate = "";
            $bk_edate = "";
        }
        $query = "SELECT count(1) AS cnt,ht_khmc,ht_enday from (select ht_khmc,ht_enday from {$system}_ht where ht_stat='2' and ht_stday<='{$today}' and ht_enday>='{$today}' ORDER BY ht_enday DESC )as tb GROUP BY ht_khmc";
        $res = M()->query($query);
        $ht_data = array();
        foreach($res as $r){
            $ht_data[$r['ht_khmc']] = $r;
        }
        $res = M($system.'_ht')->field('COUNT(1) AS cnt,ht_khmc')->where('ht_stat>0')->group('ht_khmc')->select();
        $ht = array();
        foreach($res as $r){
            $ht[$r['ht_khmc']] = $r;
        }
        $tday=date("Y-01-01",time());
        $day_90=date("Y-m-d",strtotime("-90 day",strtotime($today)));
        $map   = array(
            'g_stat3'    => array('gt',0),
        );
        $where = array(
            'g_name'     => array('like',"%{$keyWord}%"),
            'g_helpword' => array('like',"%{$keyWord}%"),
            '_logic'     => 'or'
        );
        $map['_complex'] = $where;
        $data = M($system.'_guest2')->field('*,g_name as text,g_khjc as jc')->where($map)->order('g_dtime desc')->select();
        foreach($data as $r){
            $cound = $ht_data[$r['id']];
			$r['cnt'] = $cound['cnt'];
			// 合同状态
			if ($r['cnt'] == 0) {
                $r['contract_status'] = '合同失效';
				$end = M($system.'_ht')->field('ht_enday')->where(array('ht_stat' => 2,'ht_khmc' => $r['id']))->order('ht_enday DESC')->find();
				if (!empty($end['ht_enday'])) {
					$r['contract_eday'] = $end['ht_enday'];
					$r['contract_string'] = "<span style='color:black;background-color:yellow;'>自".$r['contract_eday']."起合同失效</span>";
					$r['contract_eday'] = $end['ht_enday'];
				} else {
					$r['contract_eday'] = "";
					$r['contract_string'] = '合同失效';
					$r['contract_eday'] = "";
				}
			} else {
				$r['contract_status'] = '合同有效';
				$r['contract_string'] = '合同有效';
				$r['contract_eday'] = $cound['ht_enday'];
				if ($r['reid'] == 0) {
					$r['contract_string'] = "总合同有效期至".$r['contract_eday'];
				}
            }
            // 跟踪内容
			$r3=M($system.'_clientreport')->where(array('stat'=>1,'clientname' => $r['g_name']))->order('date desc,dtime desc')->find();
			$r['contents'] = "";
			$r['date'] = "";
			if (!empty($r3)) {
				$r['contents'] = $r3['date'].$r3['contactmethod'].$r3['content']."<br>";
				$r['date'] = $r3['date'];
			}
			// 跟踪状态
			$r['follow_status'] =($r['date']>=$day_90)?'90天内':'超90天';
			$time=$this->getMonthNum($r['date'],$today);
	
			// 客户状态
			$r['ye'] = '';
			$status = "";
			$statusRes=$ht[$r['id']];
			if (!$statusRes['cnt']) {
				$r['contract_status'] = "无合同";
				$r['contract_string'] = "无合同";
				$status = "删除";
			} else {
				if ($r['contract_status'] == '合同失效') {
					if ((strtotime($today) - strtotime($r['contract_eday'])) > 6*30*24*3600) {
						$status = "冻结转删除";
					} else {
						$status = "冻结";
					}
				} else {
					$status = "正常";
				}
			}
			$r['status'] = $status;
	
			// 样式
			if($r['g_stat3']=='2')
				$r['style'] = "color:red;";
			else {
				$r['style'] = ($r['g_stat3']=='3')?'color:gray;':"color:#036;";
			}
	
			if ($r['reid'] == 0) {
				// 一级客户余额计算
				$yeRes=M($system.'_guest_accounts_receivable')->field('qmje')->where(array('clientid' => $r['id']))->find();
				if (!empty($yeRes['qmje'])) {
					$r['ye'] = round($yeRes['qmje'], 2);
				} else {
					$r['ye'] = 0;
				}
				$r['count'] = 0;
				if (empty($father[$r['id']]['id'])) {
					$father[$r['id']] = $r;
				} else {
					if (!empty($r['contents'])) {
						if ($father[$r['id']]['date'] < $r['date']) {
							$father[$r['id']]['contents'] = $r['contents'];
						}
					}
				}
			} else {
				if (empty($father[$r['reid']]['id'])) {
					$info = M($system.'_guest2')->field('*,g_name as text,g_khjc as jc')->where(array('id' => $r['reid']))->find();
					$info['date'] = $r['date'];
					$info['style'] = $r['style'];
					$info['class'] = $r['class'];
					$info['count'] = 0;
					$info['contents'] = "";
					$info['contract_eday'] = "";
					// 一级客户余额计算
                    $yeQuery = "SELECT qmje FROM yxhb_guest_accounts_receivable WHERE clientid='".$r['reid']."' ORDER BY id DESC";
					$yeRes=M($system.'_guest_accounts_receivable')->field('qmje')->where(array('clientid' => $r['reid']))->order('id desc')->find();
					$info['ye'] = round($yeRes['qmje'], 2);
					$father[$r['reid']] = $info;
				}
				$child[$r['reid']][] = $r;
				if (!empty($r['contents'])) {
					if ($father[$r['reid']]['date'] < $r['date']) {
						$father[$r['reid']]['contents'] = $r['contents'];
					}
				}
				if (strtotime($r['contract_eday']) > strtotime($father[$r['reid']]['contract_eday'])) {
					$father[$r['reid']]['contract_eday'] = $r['contract_eday'];
				}
				$father[$r['reid']]['count']++;
			}
		}
        $res = array();
        $djz = array();
       
        // 调整一级客户状态
        foreach ($father as $ck => &$v) {
            // 当天注册客户 == 正常
            if( date('Y-m-d',strtotime($v['g_jltime'])) == $thisday){
                $v['status'] = '总正常';
                $v['name'] =    $v['g_name'];     
                $res[] = $v;  
                continue;
            }
            // 余额非0==正常
            if ($v['ye'] != 0) {
                $v['status'] = '总正常';
                $v['name'] =    $v['g_name'];     
                $res[] = $v;  
                continue;
            }
            if ($v['count'] > 0) {
                // 含子客户的一级客户
                $v['status'] = '总删除';
                foreach ($child[$ck] as $cv) {
                    if ($cv['status'] == '正常' || $cv['follow_status'] == '90天内') {
                        $v['status'] = '总正常';
                        $v['name'] =    $v['g_name'];               
                        break;
                    } elseif ($cv['status'] == "冻结") {
                        $v['status'] = '总冻结';    
                    }
                }
            } else {
                if ($v['contract_status'] == '合同有效' || $v['follow_status'] == '90天内') {
                        $v['name'] =    $v['g_name'];  
                              
                }elseif ($v['contract_status'] == '合同失效') {
                    if($v['status']=='冻结'){
                        $v['status'] = "总冻结";
                    }
                }else{
                    $v['status'] = '总删除';
                }
            }
        }
        $res = $this->addTailAffix($father);
        return array($father,$child);
    }

    // 给备案总客户 添加尾缀
    public function addTailAffix($data){
        $tailAffix = '-总';
        $temp      = array();
        foreach($data as $key => $val){
            // 检查简称
            if($val['g_xmmc']) $val['g_xmmc'] .= $tailAffix;
            // 检查是否带尾缀
            if(strpos($val['text'],$tailAffix) === false) $val['text'] .= $tailAffix;
            $temp[]       = $val;
        }
        return $temp;
    }

    public function getMonthNum($date1,$date2){
        $date1_stamp=strtotime($date1);
        $date2_stamp=strtotime($date2);
        list($date_1['y'],$date_1['m'])=explode("-",date('Y-m',$date1_stamp));
        list($date_2['y'],$date_2['m'])=explode("-",date('Y-m',$date2_stamp));
        return abs($date_1['y']-$date_2['y'])*12 +$date_2['m']-$date_1['m'];
     }
}