<?php
namespace Light\Controller;
use Think\Controller;

class SignController 
#extends Controller { 
extends BaseController {
    // 页面
    public function View(){
        $view = I('get.view');
        $title = array(
            array('title' => '待我签收','url' => U('Light/Sign/View',array('view' => 'myApprove')),'on' => '','action' => 'myApprove','unRead' => $this->unSignCount()),//
            array('title' => '我的提交','url' => U('Light/Sign/View',array('view' => 'mySubmit')),'on' => '','action' => 'mySubmit','unRead' => 0),
            array('title' => '推送我的','url' => U('Light/Sign/View',array('view' => 'pushToMe')),'on' => '','action' => 'pushToMe','unRead' => $this->getPushCount()),
            array('title' => '抄送我的','url' => U('Light/Sign/View',array('view' => 'copyToMe')),'on' => '','action' => 'copyToMe','unRead' => $this->getCopyCount())//
        );    
        $titleName = '';
        foreach($title as $key => $val){
            if($val['action'] == $view) {
                $title[$key]['on'] = 'weui-bar__item_on';
                $titleName = $val['title'];
                break;
            } 
        }
        $this->assign('title',$titleName);   
        $this->assign('titleArr',$title);
        $this->display('Sign/'.$view);
    }

    // 接口
    public function Api(){
        $action = I('post.action');  
        $this->$action();
    }

    /**
     * 抄送未读数量
     */
    public function getCopyCount(){
        $wx_id = session('wxid'); 
        //抄送未读  yxhb - kk
        $copeSql = "SELECT count(1) as count from (
            select * from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 and stat!=0 {$this->qs_sql_or()} GROUP BY aid
            union ALL
            select * from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1  and stat!=0 {$this->qs_sql_or()} GROUP BY aid)a ";
        $copyRes = M()->query($copeSql);
        $copy_count =$copyRes[0]['count'];
        return $copy_count?$copy_count:0;
    }

    /**
     * 未读抄送接口
     */
    public function unReadCopy(){
        $systemName = array('1'=>'建材', '2'=>'环保');
        $page = I('post.page_num');
        $searchText = I('post.search');
        $searchArr = $this->dealSearch($searchText);
        $page = $page?$page:1;
        $wx_id = session('wxid'); 
        $result = array();
        $copySql = "SELECT * from (
            select *,1 from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 {$searchArr['yxhb']} and stat!=0 {$this->qs_sql_or()}
            UNION ALL
            select *,2 from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1  {$searchArr['kk']} and stat!=0 {$this->qs_sql_or()}) a 
            GROUP BY aid order by time desc limit ".(($page-1)*20).",20"; 

        $copy = M()->query($copySql);   

        $result = $this->dealWithCopy($copy);
        $this->ajaxReturn($result);   

    }

    /**
     * 抄送未读数量
     */
    public function getPushCount(){
        $wx_id = session('wxid'); 
        //抄送未读  yxhb - kk
        $copeSql = "SELECT count(1) as count from (
            select * from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 and stat<>0 {$this->qs_sql_or()} GROUP BY aid
            union ALL
            select * from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2  and stat<>0 {$this->qs_sql_or()} GROUP BY aid)a ";
        $copyRes = M()->query($copeSql);
        $copy_count =$copyRes[0]['count'];
        return $copy_count?$copy_count:0;
    }

    /**
     * 未读推送接口
     */
    public function unReadPush(){
        $systemName = array('1'=>'建材', '2'=>'环保');
        $page = I('post.page_num');
        $searchText = I('post.search');
        $searchArr = $this->dealSearch($searchText);
        $page = $page?$page:1;
        $wx_id = session('wxid'); 
        $result = array();
        $copySql = "SELECT * from (
            select *,1 from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 {$searchArr['yxhb']} and stat!=0 {$this->qs_sql_or()}
            UNION ALL
            select *,2 from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2  {$searchArr['kk']} and stat!=0 {$this->qs_sql_or()}) a 
            GROUP BY aid order by time desc limit ".(($page-1)*20).",20"; 

        $copy = M()->query($copySql);   

        $result = $this->dealWithCopy($copy);
        $this->ajaxReturn($result);   

    }

    /**
     * 抄送接口
     * @param integer 页码
     */
    public function copyToApi(){
        $systemName = array('1'=>'建材', '2'=>'环保');
        $page = I('post.page_num');
        $searchText = I('post.search');
        $searchArr = $this->dealSearch($searchText);
        $page = $page?$page:1;
        $wx_id = session('wxid'); 
        $result = array();
        $copySql = "SELECT * from (
            select *,1 from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 {$searchArr['yxhb']} and stat!=0 {$this->qs_sql_or()}
            UNION ALL
            select *,2 from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and FIND_IN_SET('{$wx_id}',`readed_id`) and type=1  {$searchArr['kk']} and stat!=0 {$this->qs_sql_or()}) a 
            GROUP BY aid order by time desc limit ".(($page-1)*20).",20"; 

        $copy = M()->query($copySql);   

        $result = $this->dealWithCopy($copy);
        $this->ajaxReturn($result);   
    }

    /**
     * 抄送接口
     */
    public function pushToApi(){
        $systemName = array('1'=>'建材', '2'=>'环保');
        $page = I('post.page_num');
        $page = $page?$page:1;
        $searchText = I('post.search');
        $searchArr = $this->dealSearch($searchText);
        $wx_id = session('wxid'); 
        
        $copySql = "SELECT * from (
            select *,1 from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and FIND_IN_SET('{$wx_id}',`readed_id`) and type=2  {$searchArr['yxhb']} and stat!=0 {$this->qs_sql_or()}
            UNION ALL
            select *,2 from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 {$searchArr['kk']} and stat!=0 {$this->qs_sql_or()} ) a 
            GROUP BY aid order by time desc limit ".(($page-1)*20).",20"; 
        
        $copy = M()->query($copySql);   
        $result = $this->dealWithCopy($copy);

        $this->ajaxReturn($result);    
    }

    /**
     * 抄送数据处理
     */ 
    public function dealWithCopy($data){
        $result = array();
        foreach($data as $k => $v){
            // 查询数据
            $system = $v[1]==1?'kk':'yxhb';
            $logic  = D(ucfirst($system).$v['mod_name'], 'Logic');
            $res    = $logic->sealNeedContent($v['aid']);
            $info   = $this->searchTableInfo($system,$v['mod_name']);
            $Content   = $logic->recordContent($v['aid']);
            $statRes   = $this->transStat($v['mod_name'],$Content['stat']);
            $stat      = $statRes ? $statRes: $Content['stat'];
            $arr = array(
                'system'    => $system,
                'systemName'=> $info['title'],
                'mod_name'  => $v['mod_name'],
                'title'     => $info['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($v['time'])),
                'applyer'   => $Content['applyerName'],
                'stat'      => $stat,
                'content'   => $res
            );
            $result[] = $arr;
       }
       return $result;
    }

    /**
     * 搜索查询模块检索
     * @param string $searchText 搜索字段
     */
    public function dealSearch($searchText){
        
        $result = array('yxhb' => '','kk' => '');
        // 搜索为空，全查
        if(empty($searchText)) return $result;
   
        // 系统是否要搜索 -- 1、只输入模块名，无系统名  2、系统加模块名
        $tableArr = $this->config();
        $mod_name = array();
        foreach($tableArr as $k =>$v){
            $tmp = $this->spStr($v['search']);
            $flag = false;
            foreach($tmp as $key => $val){
                if(substr_count($searchText,$val)>0) { // 检查是否有需要改项目
                    $flag=true;
                    break;
                }
            }
            // 需要此项目
            if($flag) $mod_name[] = $v['mod_name'];
        }
        // 去除重复项
        $mod_name = array_unique($mod_name);
        // 拼接 sql
        $sql = '';
        foreach ($mod_name as $k =>$v) {
            $k == 0 ?$sql.=' ( ':$sql.=' or ';
            $sql .= "mod_name='{$v}'";
        }
        
        // 系统检查  -- 同时为空,或同时存在 全系统查询    -- 单方面为空 屏蔽单系统
        // sql为空 -- 没有查询到结果  不为空查询到结果
        $yxhbCount = substr_count($searchText,'环保');
        $kkCount = substr_count($searchText,'建材');
        if(($yxhbCount>0 && $kkCount>0)){ // sql无结果，系统检索有结果
            // 排除上方查询无结果的情况
            if(!empty($sql)){
                $result['yxhb'] = ' and '.$sql.') ';
                $result['kk'] = ' and '.$sql.' ) ';
            }
        }elseif($yxhbCount<1 && $kkCount<1){ // sql无结果，系统检索无结果
            if(!empty($sql)){
                $result['yxhb'] = ' and '.$sql.') ';
                $result['kk'] = ' and '.$sql.' ) ';
            }else{
                $result['yxhb'] = ' and type=99 ';
                $result['kk'] = ' and type=99 ';
            }
        }elseif($yxhbCount>0 && $kkCount<1){ // 查建材不查环保
            if (empty($sql)) {
                $result['yxhb'] = ' and type=99 ';
            } else {
                $result['yxhb'] = ' and '.$sql.') and type=99 ';
                $result['kk'] = ' and '.$sql.' ) ';
            }
        }elseif($yxhbCount<1 && $kkCount>0){// 查环保不查建材
            if (empty($sql)) {
                $result['kk'] = ' and type=99 ';
            } else {
                $result['yxhb'] = ' and '.$sql.') ';
                $result['kk'] = ' and '.$sql.' ) and type=99 ';
            }
        }
        return $result;
    }

    



    /**
     * 签收排除数组
     */
    public function qs_sql(){
        $sql = '';
        $mod_array = D('Msgdata')->QsArray();
        foreach($mod_array as $val){
            $sql .= " and `mod_name` = '{$val['pro_mod']}'";
        }
        return $sql;
    }

    /**
     * 签收排除数组
     */
    public function qs_sql_or(){
        $sql = '';
        $mod_array = D('Msgdata')->QsArray();
        $sql = " and ( ";
        foreach($mod_array as $key=>$val){
            if($key != 0) $sql .=" or ";
            $sql .= "`mod_name` = '{$val['pro_mod']}'";
        }
        $sql .= ")";
        return $sql;
    }


    // 审核中的提交
    public function mySubmit(){
        $submit = $this->mySubmitData(1);
        $this->ajaxReturn($submit); 
    }
    // 已经过审的提交
    public function mySubmited(){
        $submit = $this->mySubmitData('');
        $this->ajaxReturn($submit); 
    }
    /**
     * 获取我的提交记录
     */
    public function mySubmitData($limit=''){
        $page       = I('post.page_num');
        $page       = $page?$page:1;
        $result     = array();
        $searchText = I('post.search');
        $table_info = $this->config();
        $submit_sql = 'SELECT * from(';
        if(empty($limit)){
            $stat = 'stat!=2';
            // sql语句构造
            $submit_sql .= $this->SubmitSqlMake($searchText,$table_info,$stat);
        }else{
            $name = session('name');
            foreach($table_info as $k =>$v){
                if($k != 0) $submit_sql .= ' UNION all ';
                $submit_sql .=  " select {$v['copy_field']},{$k} from {$v['table_name']} where {$v['submit']['name']}='{$name}' and {$v['stat']}={$v['submit']['stat']}";
            }
        }
        $submit_sql .=')a ORDER BY date desc';
        
        if(empty($limit)){
            $submit_sql .= ' LIMIT '.(($page-1)*20).',20';
        }
       
        $sub = M()->query($submit_sql);

        $data  = $this->dealWithDate($sub);
        return $data;
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
                    if(!$res && $v['system'] != 'kk')  $del = ' and 1=-1 '; # 排除建材系统 模块不在搜索方位
                }
            }
            $submit_sql .=  " select {$v['copy_field']},{$k} from {$v['table_name']} where {$v['submit']['name']}='{$name}' and {$v['stat']}!={$v['submit']['stat']} {$del}";
        }

        return $submit_sql;
    }

    // 未签收数据
    public function unSignData(){
        $config = $this->config(); 
        $result = array();
        $sub = array();
        foreach ($config as $k => $v ) {
            $id = session($v['system'].'_id'); 
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field($v['copy_field'])
                    ->where(array('a.app_stat' => 0,"{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],'a.mod_name' => $v['mod_name'], 'a.per_id' => $id ))
                    ->select();    
            if(!empty($res)){
                foreach($res as $key => $val){
                    $res[$key][0] = $k; 
                }
            }
            $sub = array_merge($sub,$res);
        }
        return $sub;

    }
    // 未签收的数量
    public function unSignCount(){
        $sub   = $this->unSignData();
        return count($sub);
    }

    // 未签收的接口
    public function unSign(){
        $sub   = $this->unSignData();
        $count = count($sub);
        $count = $count == 0?'':$count;
        $data  = $this->dealWithDate($sub);
       
        $this->ajaxReturn($data);        
    }

    // 签收的接口
    public function isSign(){
        $page       = I('post.page_num');
        $page       = $page?$page:1;
        $result     = array();
        $searchText = I('post.search'); # 非空的情况对sql 进行限制
        $yxhbCount  = substr_count($searchText,'环保');
        $kkCount    = substr_count($searchText,'建材');
        
        $table_info = $this->config();
        $yxhb_id    = session('yxhb_id');
        $kk_id      = session('kk_id');
        $Mod        = array('kk' => '', 'yxhb' => '');
        $where      = '';
        // sql语句构造  -- 先进行模块查找，再做系统分析
        foreach ($table_info as $k => $v) {
            if(!empty($Mod[$v['system']])){
                $Mod[$v['system']] .= ' or ';
            }
            # 进行模块查找，拼接 条件限制语句
            $res = $this->searchFind($v['search'],$searchText);
            if($res){
                empty($where)?$where = 'and (' : $where .= ' or ';
                $where .=" mod_name='{$v['mod_name']}' ";
            } 
            $Mod[$v['system']] .= "mod_name='{$v['mod_name']}'";
        }
        if(!empty($where)) $where .= ')';
        # 系统分析 -- 查看是否限制系统 3种情况 只处理 单系统情况
        if($yxhbCount > 0 && $kkCount < 1){
            $where .=' and a.1=1';
        }elseif($yxhbCount <1  && $kkCount > 0){
            $where .=' and a.1=2';
        }
        $yxhbSql = 'SELECT aid,mod_name,time,1 from yxhb_appflowproc where per_id='.$yxhb_id.' and (app_stat=1 or app_stat=2) and ('.$Mod['yxhb'].')';
        $kkSql   = 'SELECT aid,mod_name,time,2 from kk_appflowproc where per_id='.$kk_id.' and (app_stat=1 or app_stat=2) and ('.$Mod['kk'].')';
        $sql     = "select * from ({$yxhbSql} union all {$kkSql}) a where 1=1 {$where} order by time desc limit ".(($page-1)*20).",20";
        $approve = M()->query($sql);   

        foreach($approve as $k => $v){
            // 查询数据
            $system = $v[1]==2?'kk':'yxhb';
            $logic  = D(ucfirst($system).$v['mod_name'], 'Logic');
            $res    = $logic->sealNeedContent($v['aid']);
            $info   = $this->searchTableInfo($system,$v['mod_name']);
            $Content   = $logic->recordContent($v['aid']);
            $statRes   = $this->transStat($v['mod_name'],$Content['stat']);
            $stat      = $statRes ? $statRes: $Content['stat'];
            $arr = array(
                'system'    => $system,
                'systemName'=> $info['title'],
                'mod_name'  => $v['mod_name'],
                'title'     => $info['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($v['time'])),
                'applyer'   => $Content['applyerName'],
                'stat'      => $stat,
                'content'   => $res
            );
            $result[] = $arr;
        }

        
        $this->ajaxReturn($result);        
    }


     /**
     * 数据处理
     * @param  $data 查出的数据
     * @return $returnData  处理后的数据
    */
    public function  dealWithDate($data){
        $config = $this->config();
        $returnData    = array();
        // 数据重构
        foreach($data as $k => $v){
            $res       = D(ucfirst($config[$v[0]]['system']).$config[$v[0]]['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            $statRes   = $this->transStat($config[$v[0]]['mod_name'],$v['state']);
            $stat      = $statRes ? $statRes: $v['state'];
            $arr = array(
                'system'    => $config[$v[0]]['system'],
                'systemName'=> $config[$v[0]]['title'],
                'mod_name'  => $config[$v[0]]['mod_name'],
                'title'     => $config[$v[0]]['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($v['date'])),
                'applyer'   => $v['applyer'],
                'stat'      => $stat,
                'content'   => $res
            );
            $returnData[] = $arr;
        }

        return $returnData;
    }


    /**
     * 对特殊模块状态值进行处理
     * @param string $modname 模块名
     * @param string $stat    当前状态
     * @return int $stat 
     */
    public function transStat($modname,$stat){
        if($stat == 0) return 0;
        $statArr = array(
            'CgfkApply' => array('4' =>2 ,'3' => 2 ,'2' => 1)
        );

        if(!$statArr[$modname]) return 0;
        return $statArr[$modname][$stat];
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
     * 获取所需查询的模块的表名
     * @param string $mod_name 模块名
     * @return string 对应的表名
     */
    public function searchTableInfo($system,$mod_name=''){
        $tableArr = $this->config();
        $result = '';
        foreach($tableArr as $k =>$v){
            if($v['system'] == $system && $v['mod_name'] == $mod_name){
                $result = $v;
                break;
            }
        }
        return $result;
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

    // 基本配置
    public function config(){
        return array(
            array( 
                'title'      => '水泥配比通知' , 
                'search'     => '水泥配比通知',
                'system'     => 'kk' ,
                'mod_name'   => 'SnRatioApply'    ,
                'table_name' => 'kk_zlddtz'    ,
                'id'         => 'id',
                'stat'       => 'STAT',
                'submit'     => array('name' => 'zby','stat' => 2),
                'copy_field' => 'kk_zlddtz.id as aid, kk_zlddtz.jlsj as date,kk_zlddtz.STAT as state ,kk_zlddtz.zby as applyer'
            ),
            array( 
                'title'      => '矿粉配比通知' , 
                'search'     => '矿粉配比通知',
                'system'     => 'yxhb' ,
                'mod_name'   => 'KfRatioApply'    ,
                'table_name' => 'yxhb_assay'    ,
                'id'         => 'id',
                'stat'       => 'state',
                'submit'     => array('name' => 'name','stat' => 2),
                'copy_field' => 'yxhb_assay.id as aid, yxhb_assay.cretime as date,yxhb_assay.state as state ,yxhb_assay.name as applyer'
            ),
        );
      
    }

}
