<?php
namespace Light\Controller;
use  Vendor\Overtrue\Pinyin\Pinyin;
class TestController extends \Think\Controller {
   // D('YxDetailAuth')->updateAuth();  更新申请人员
    public function Sign()
    {   
        header('Content-Type: text/html; charset=utf-8');
        dump(D('KkFpsm','Logic')->recordContent(1));
    }

     // 搜索模块
     public function getSearchTable($search){
        $tab = $this->config();
        $res = array();
        if(empty($search)) return $tab;
        foreach($tab as $k => $v){
            $flag = $this->searchFind($v['search'],$search);
            if(!$flag) continue;
            $res[] = $v;
        }
        return $res;
   }
   public function config(){
    return D('Seek')->configSign();
}


    /**
     * 流程人员数组重构
     * @param array $data 流程数据
     * @return array $info 
     */
    public function getProInfo($data){
        $boss = D('YxhbBoss');
        $info = array();
        foreach($data as $k => $val){
            $wxid   = $boss->getWXFromID($val['per_id']);
            $name   = $boss->getNameFromID($val['per_id']);
            $avatar = $boss->getAvatar($val['per_id']);
            // 同级审批 是true 否false
            $parallel = $data[$k+1]['stage_id'] == $val['stage_id'] ? true : false;
            $info[] = array(
                'wxid'     => $wxid,
                'name'     => $name,
                'avatar'   => $avatar,
                'parallel' => $parallel,
            );
        }
        return $info;
    }

    /**
     * 挑选符合条件的数据
     * @param string $condition 条件
     * @return array $res 
     */
    public function  getAccordCondition($data,$condition){
        $temp = $data;
        foreach($data as $k => $v){
            if($v['condition'] == '') continue;
            if(strpos($v['condition'],$condition) === false) unset($temp[$k]); 
        }
        $temp = array_values($temp);
        return $temp;
    }
    /**
     * 获取条件流程（建议一个条件为一整套流程，不使用公用审批人）
     * @param string $modname 模块名
     * @param string $condition 当前状态
     * @return array $res 
     */

    
 /**
     * 对特殊模块状态值进行处理
     * @param string $modname 模块名
     * @param string $stat    当前状态
     * @return int $stat 
     */
    public function transStat($modname,$stat){
        if($stat == 0) return 0;
        $statArr = array(
            'CgfkApply'               => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'WlCgfkApply'             => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'PjCgfkApply'             => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'CostMoney'               => array('5' =>2 ,'4' => 1 ,'2' => 1),
            'Contract_guest_Apply'    => array('2' =>2 ,'1' => 1 ,'5' => 0, '4' => 0 , '3' => 1),
            'Contract_guest_Apply2'   => array('2' =>2 ,'1' => 1 ,'5' => 0, '4' => 0 , '3' => 1),
        );

        if(!$statArr[$modname]) return 'false';
        return $statArr[$modname][$stat];
    }



    public function getMateriel($date,$id){
        $str = 'gt';
        $sort = 'desc';
        if($id == 2){
            $str = 'elt';
            $sort = 'asc';
        }
        $map = array(
            'pruduct_date' => array($str,$date),
            'stat'         => 1
        );
        $data = M('yxhb_materiel')->where($map)->order("pruduct_date {$sort}")->find();
       dump(M()->_sql());
        return $data;
    }
    public function arrayMerge($data){
        $seek =  D('Seek');
        $tab = $seek->arrayMerge($data);
        return $tab;
    }
     /**
     * 我提交的 sql 构造
     * @param string $searchText 搜索文字
     * @param array $table 表数组  -- 其实没必要
     * @return string 构造后的sql
     */
    public function SubmitSqlMake($searchText,$table,$stat=''){
        $flag = empty($searchText)? true:false ; # 为空值的情况下全查
        $yxhbCount = substr_count($searchText,'环保');
        $kkCount = substr_count($searchText,'建材');
        // 都查 只查期中一个
        // sql语句构造
        $submit_sql = ''; # 删除
        $name = session('name');
        foreach($table as $k =>$v){
            
            if($k != 0) $submit_sql .= ' UNION all ';

            $del = '';
            if($flag){ // 搜索值为空的情况 全查
                $del = '';
            }else{
                // echo "123<br/>";
                if(($yxhbCount < 1 && $kkCount < 1) || ($yxhbCount < 1 && $kkCount < 1)){ # 搜索值不为空，找不到系统  --- 模块查找
                    $res = $this->searchFind($v['search'],$searchText);
                    if(!$res)  $del = ' and 1=-1 '; # 模块不在搜索方位
                }elseif($yxhbCount > 0 && $kkCount < 1){ # 环保系统   ---- 模块查找
                    $res = $this->searchFind($v['search'],$searchText);
                    if(!$res && $v['system'] != 'yxhb')  $del = ' and 1=-1 '; # 排除环保系统 模块不在搜索方位
                }elseif($yxhbCount < 1 && $kkCount > 0){
                    $res = $this->searchFind($v['search'],$searchText);
                    if(!$res && $v['system'] != 'kk')  $del = ' and 1=-1 '; # 排除建材系统 模块不在搜索方位 and {$v['stat']}!={$v['submit']['stat']}
                }
            }
            $submit_sql .=  " select {$v['copy_field']},{$k} from {$v['table_name']} where {$v['submit']['name']}='{$name}' and {$v['stat']}!={$v['submit']['stat']} {$v['map']}  {$del}";
        }

        return $submit_sql;
    }

    /**
     * 搜索查看是否在所查的模块内
     * @param  string $mod        模块名
     * @param  string $searchText 搜索值
     * @return bool   $flag       布尔
     */
    public function searchFind($mod,$searchText){
        $tmp = $this->spStr($mod);
        $flag = false;
        foreach($tmp as $key => $val){
            if(substr_count($searchText,$val)>0) { // 检查是否有需要改项目
                $flag=true;
                break;
            }
        }
        return $flag;
    } 
     /** 
     * UTF-8版 中文二元分词 
     */  
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
    /**
     * 查询表 -> 用于查询未审批的
     */
    public function getAppTable(){
        $seek =  D('Seek');
        return $seek->getAppTable();
    }
    /**
     *  根据返回值，重组字符串
     * @param  array $data 重组数组
     * @return string       description
     */
    public function ReDescription($data){
        $description = '';
        foreach($data as $k =>$v){
          $description.=$v['name'].$v['value']."\n";
        }
        return $description;
    }


    /**
     * 添加尾缀
     */ 
    public function addSuffix($data,$suffix){
        if(!is_array($data)) return;
        $temp = array();
        foreach($data as $k=>$v){
            $v['text'] .= $suffix;
            $temp[] = $v;
        }
        return $temp;
    }
 //  header('Content-Type: text/html; charset=utf-8');
    public function arraySort($arr, $keys, $type = 'asc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v){
            $keysvalue[$k] = $v[$keys];
        }
        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
           $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
   
    
  
} 


