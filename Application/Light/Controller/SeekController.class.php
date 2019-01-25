<?php
namespace Light\Controller;
use Think\Controller;

class SeekController  extends BaseController  
{
    private $titleArr;

    public function __construct(){
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
        $this->titleArr = array(
            array('title' => '待我审批','url' => U('Light/Seek/myApprove'),'on' => '','unRead' => $this->getApproveCount()),//
            array('title' => '我的提交','url' => U('Light/Seek/mySubmit'),'on' => '','unRead' => 0),
            array('title' => '推送我的','url' => U('Light/Seek/pushToMe'),'on' => '','unRead' => $this->getPushCount()),
            array('title' => '抄送我的','url' => U('Light/Seek/copyToMe'),'on' => '','unRead' => $this->getCopyCount())//
        );
        
    }

    /**
     * 审批接口
     * @param $func 取用的函数标识 
     */
    public function seekApi(){
        $func = I('post.type');
        $data = array();
        $code = 404;
        switch($func){
            case 'copy':
                $data = $this->copyToApi();
                $code = 200;
                break;
            case 'submit':
                $data = $this->mySubmitData();
                $code = 200;
                break;
            case 'approve':
                $data = $this->approve();
                $code = 200; 
                break;
            case 'push':
                $data = $this->pushToApi();
                $code = 200;        
                break;
            case 'pushRead':
                $data = $this->pushReadApi();
                $code = 200; 
                break;
            case 'copyRead':
                $data = $this->copyReadApi();
                $code = 200; 
                break;   
            case 'config_api':
                $data = $this->config_api(I('post.product'),I('mod'));
                $code = 200; 
                break;     
            case 'config_submit':
                $data = $this->config_submit();
                $code = 200; 
                break;    
            case 'config_sn_submit':
                $data = $this->config_sn_submit();
                $code = 200; 
                break;  
            case 'config_fhf_submit':
                $data = $this->config_fhf_submit();
                $code = 200; 
                break;   
        }
        $this->ajaxReturn(array('code' => $code , 'data' => $data));
    }

    /**
     * 签收排除数组
     */
    public function qs_sql(){
        $sql = '';
        $mod_array = D('Msgdata')->QsArray();
        foreach($mod_array as $val){
            $sql .= " and `mod_name` <> '{$val['pro_mod']}'";
        }
        return $sql;
    }
    /**
     *  一键全读功能之抄送全读
     */
    public function copyReadApi(){
        $wx_id = session('wxid'); 
        $copySql = "SELECT * from (
                    SELECT  `aid`,`readed_id`,`mod_name`,`time`,1 FROM `yxhb_appcopyto` WHERE FIND_IN_SET('{$wx_id}',`copyto_id`) AND  !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 AND `stat` <> 0 {$this->qs_sql()}
                UNION ALL
                    SELECT  `aid`,`readed_id`,`mod_name`,`time`,2 FROM `kk_appcopyto` WHERE FIND_IN_SET('{$wx_id}',`copyto_id`) AND !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 AND `stat` <> 0 {$this->qs_sql()}
                ) a GROUP BY aid order by time desc";
                
        $copy = M()->query($copySql);   
        foreach($copy as $k => $v){
            // 查询数据
            $system = $v[1] == 1?'yxhb':'kk';
            $copyTo = D($system.'Appcopyto');
            $copyTo->readCopytoApply($v['mod_name'],$v['aid'] ,null,1);
        }
        return  'success';
    }

    /**
     *  一键全读功能之推送全读
     */
    public function pushReadApi(){
        $wx_id = session('wxid'); 
        $pushSql = "SELECT * from (
                    SELECT  `aid`,`readed_id`,`mod_name`,`time`,1 FROM `yxhb_appcopyto` WHERE FIND_IN_SET('{$wx_id}',`copyto_id`) AND !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 AND `stat` <> 0 {$this->qs_sql()}
                UNION ALL
                    SELECT  `aid`,`readed_id`,`mod_name`,`time`,2 FROM `kk_appcopyto` WHERE FIND_IN_SET('{$wx_id}',`copyto_id`) AND !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 AND `stat` <> 0 {$this->qs_sql()}
                ) a GROUP BY aid order by time desc";
                
        $push = M()->query($pushSql);   
        foreach($push as $k => $v){
            // 查询数据
            $system = $v[1] == 1?'yxhb':'kk';
            $copyTo = D($system.'Appcopyto');
            $copyTo->readCopytoApply($v['mod_name'],$v['aid'] ,null,2);
        }
        return 'success';
    }

    /**
     * 我的审批
     */
    public function myApprove(){
        if(session('wxid') != 'HuangShiQi') return $this->display('Cue/404');
        $this->titleArr[0]['on'] = 'weui-bar__item_on';

        $this->assign('title','待我审批');   
        $this->assign('titleArr',$this->titleArr);   
        $noApprove = $this->noApprove();
        $this->assign('noApprove',$noApprove);
        $this->display('Seek/myApprove');
    }


    /**
     * 已审批记录获取
     */
    public function approve(){
        $page       = I('post.page_num');
        $page       = $page?$page:1;
        $result     = array();
        $searchText = I('post.search'); # 非空的情况对sql 进行限制
        $yxhbCount  = substr_count($searchText,'环保');
        $kkCount    = substr_count($searchText,'建材');
        
        $table_info = $this->getAppTable();
        $table_info = $this->arrayMerge($table_info);
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
                if($v['mod_name'] == 'CgfkApply' ){
                    $where .=" mod_name='{$v['mod_name']}' or mod_name='PjCgfkApply' or mod_name='WlCgfkApply'";
                }
                elseif($v['mod_name'] == 'fh_edit_Apply' || $v['mod_name'] == 'fh_edit_Apply_hb'){
                    $where .=" mod_name='{$v['mod_name']}' or mod_name='fh_refund_Apply' ";
                }else{
                    $where .=" mod_name='{$v['mod_name']}' ";
                }
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
        $yxhbSql = 'SELECT *,1 from yxhb_appflowproc where per_id='.$yxhb_id.' and (app_stat=1 or app_stat=2 or app_stat=0) and ('.$Mod['yxhb'].')';
        $kkSql   = 'SELECT *,2 from kk_appflowproc where per_id='.$kk_id.' and (app_stat=1 or app_stat=2 or app_stat=0) and ('.$Mod['kk'].')';
        $sql     = "select * from ({$yxhbSql} union all {$kkSql}) a where 1=1 {$where} order by time desc limit ".(($page-1)*20).",20";
        $approve = M()->query($sql);   

        foreach($approve as $k => $v){
            // 查询数据
            $system = $v[1]==2?'kk':'yxhb';
            $tableInfo = $this->searchTableInfo($system,$v['mod_name']);
            // $res = M($tableInfo['table_name'])->field($tableInfo['copy_field'])->where(array($tableInfo['id'] => $v['aid']))->find();

            $res = D(ucfirst($system).$v['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            if( empty($res['sales']) ) continue;
            $appStatus =D($system.'Appflowproc')->getWorkFlowStatus($res['modname'], $v['aid']);
            $statRes   = $this->transStat($v['mod_name'],$res['stat']);
            $stat      = $statRes !== 'false' ? $statRes: $res['stat'];
            $arr = array(
                'system'    => $system,
                'systemName'=> $systemName[$system],
                'mod_name'  => $res['modname'],
                'title'     => $tableInfo['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($res['date'])),
                'applyer'   => $res['sales'],
                'titlename' => $res['title'],
                'title2'    => $res['title2'],
                'name'      => $res['name'],
                'stat'      => $stat,
                'approve'   => $res['approve'],
                'notice'    => $res['notice'],
                'apply'     => $appStatus
            );
            $result[] = $arr;
        }
        // dump($result);
        return $result;
    }
    /**
     * 待审批的记录
     */
    public function noApprove(){
        $tab = $this->getAppTable();
        $tab = $this->arrayMerge($tab);
        $result = array();
        $sub = array();
        foreach ($tab as $k => $v ) {
            $id = session($v['system'].'_id'); 
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field($v['copy_field'])
                    ->where(array('a.app_stat' => 0,"{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],'a.mod_name' => $v['mod_name'] , 'a.per_id' => $id))
                    ->select();    
            if(!empty($res)){
                foreach($res as $key => $val){
                    $res[$key][0] = $k; 
                }
            }
            $sub = array_merge($sub,$res);
        }
       
        // 数据重构
        foreach($sub as $k => $v){
            if( empty($v['applyer']) ) continue;
            $res       = D(ucfirst($tab[$v[0]]['system']).$tab[$v[0]]['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            $appStatus = D($tab[$v[0]]['system'].'Appflowproc')->getWorkFlowStatus($res['modname'], $v['aid']);
            $statRes   = $this->transStat($res['modname'],$v['stat']);
            $stat      = $statRes !== 'false' ? $statRes: $v['stat'];
            $arr = array(
                'system'    => $tab[$v[0]]['system'],
                'systemName'=> $tab[$v[0]]['title'],
                'mod_name'  => $res['modname'],
                'title'     => $tab[$v[0]]['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($v['date'])),
                'applyer'   => $v['applyer'],
                'title2'    => $res['title2'],
                'stat'      => $stat,
                'titlename' => $res['title'],
                'name'      => $res['name'],
                'approve'   => iconv('gbk','UTF-8',$v['approve']),
                'notice'    => $v['notice'],
                'apply'     => $appStatus
            );
            $result[] = $arr;
        }
        return $result;
    }

    public function arrayMerge($data){
        $seek =  D('Seek');
        $tab = $seek->arrayMerge($data);
        return $tab;
    }
    /**
     * 提交记录
     */
    public function mySubmit(){
        if(session('wxid') != 'HuangShiQi') return $this->display('Cue/404');
        $this->titleArr[1]['on'] = 'weui-bar__item_on';

        $this->assign('title','我的提交');   
        $this->assign('titleArr',$this->titleArr);
        $submit = $this->mySubmitData(1);
        $tmp = array();
        $count = 1;
        foreach($submit as $val){
            if($val['apply']['stat'] == 1 && $count < 5) continue;
            if($val['apply']['stat'] == 1) $count++;
            
            $tmp = $val;
        }
        
        $this->assign('submit',$submit);
        $this->display('Seek/mySubmit');
    }
    /**
     *   deal with  my submission of record data
     * @param array [$data] 
     */
   

    /**
     * 获取我的提交记录
     */
    public function mySubmitData($limit=''){
        $page       = I('post.page_num');
        $page       = $page?$page:1;
        $result     = array();
        $searchText = I('post.search');
        $table_info = $this->getAppTable();
        $table_info = $this->arrayMerge($table_info);
        $submit_sql = 'SELECT * from(';
        if(empty($limit)){
            // sql语句构造
            $submit_sql .= $this->SubmitSqlMake($searchText,$table_info);
        }else{
            $name = session('name');
            foreach($table_info as $k =>$v){
                if($k != 0) $submit_sql .= ' UNION all ';
                $submit_sql .=  " select {$v['copy_field']},{$k} from {$v['table_name']} where {$v['submit']['name']}='{$name}' and {$v['stat']}={$v['submit']['stat']}  {$v['map']} ";
            }
        }
        $submit_sql .=')a GROUP BY aid,`0` ORDER BY date desc';
        
        if(empty($limit)){
            $submit_sql .= ' LIMIT '.(($page-1)*20).',20';
        }
       
        $sub = M()->query($submit_sql);
        // 数据重构
        foreach($sub as $k => $v){
            if( empty($v['applyer']) ) continue;
            $res       = D(ucfirst($table_info[$v[0]]['system']).$table_info[$v[0]]['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            $appStatus =D($table_info[$v[0]]['system'].'Appflowproc')->getWorkFlowStatus($res['modname'], $v['aid']);
            $statRes = $this->transStat($table_info[$v[0]]['mod_name'],$v['stat']);
            $stat = $statRes !== 'false' ? $statRes: $v['stat'];
            if(($res['modname'] == 'fh_refund_Apply' || $res['modname'] == 'Contract_guest_Apply2' ) && $appStatus['stat'] == -1) continue;
            
            if(!empty($limit)){
                if($stat == 2 && ($appStatus['stat'] == 1 || $appStatus['stat'] == 2)) continue;
                if($stat == 3) continue;
            }
            $arr = array(
                'system'    => $table_info[$v[0]]['system'],
                'systemName'=> $table_info[$v[0]]['title'],
                'mod_name'  => $res['modname'],
                'title'     => $table_info[$v[0]]['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($v['date'])),
                'applyer'   => $v['applyer'],
                'stat'      => $stat,
                'title2'    => $res['title2'],
                'titlename' => $res['title'],
                'name'      => $res['name'],
                'approve'   => iconv('gbk','UTF-8',$v['approve']),
                'notice'    => $v['notice']?$v['notice']:'无',
                'apply'     => $appStatus
            );
            $result[] = $arr;
        }
        return $result;  
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
     * 抄送我的  
     *  - 未读抄送渲染，已读使用接口 
     */
    public function copyToMe(){
        if(session('wxid') != 'HuangShiQi') return $this->display('Cue/404');
        //header("Content-type: text/html; charset=utf-8"); 
        $this->titleArr[3]['on'] = 'weui-bar__item_on';
        // 系统分开 yxhb kk 
        $wx_id = session('wxid'); 
        $copy = array();
        // 环保未读 yxhb 

        $copySql = "SELECT * from (
                     SELECT  `aid`,`readed_id`,`mod_name`,`time`,1 FROM `yxhb_appcopyto` WHERE FIND_IN_SET('{$wx_id}',`copyto_id`) AND !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 AND `stat` <> 0 {$this->qs_sql()}
                    UNION ALL
                     SELECT  `aid`,`readed_id`,`mod_name`,`time`,2 FROM `kk_appcopyto` WHERE FIND_IN_SET('{$wx_id}',`copyto_id`) AND !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 AND `stat` <> 0 {$this->qs_sql()}
                    ) a GROUP BY aid order by time desc";
        $copy = M()->query($copySql);          

        $copy = $this->dealCopyArr($copy);
        $this->assign('title','抄送我的');   
        $this->assign('titleArr',$this->titleArr);
        $this->assign('copyto',$copy);
        $this->display('Seek/copyToMe');
    }
    /**
     * 推送我的  
     *  - 未读推送渲染，已读使用接口 
     */
    public function pushToMe(){
        //header("Content-type: text/html; charset=utf-8"); 
          if(session('wxid') != 'HuangShiQi') return $this->display('Cue/404');
        $this->titleArr[2]['on'] = 'weui-bar__item_on';
        // 系统分开 yxhb kk 
        $wx_id = session('wxid'); 
        $copy = array();
        // 环保未读 yxhb 

        $copySql = "SELECT * from (
                     SELECT  `aid`,`readed_id`,`mod_name`,`time`,1 FROM `yxhb_appcopyto` WHERE FIND_IN_SET('{$wx_id}',`copyto_id`) AND !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 AND `stat` <> 0 {$this->qs_sql()}
                    UNION ALL
                     SELECT  `aid`,`readed_id`,`mod_name`,`time`,2 FROM `kk_appcopyto` WHERE FIND_IN_SET('{$wx_id}',`copyto_id`) AND !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 AND `stat` <> 0 {$this->qs_sql()}
                    ) a GROUP BY aid order by time desc";
                    
        $copy = M()->query($copySql);          

        $copy = $this->dealCopyArr($copy);
        $this->assign('title','推送我的');   
        $this->assign('titleArr',$this->titleArr);
        $this->assign('copyto',$copy);
        $this->display('Seek/pushToMe');
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
        $result = array();
        $copySql = "SELECT * from (
            select *,1 from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and FIND_IN_SET('{$wx_id}',`readed_id`) and type=2  {$searchArr['yxhb']} and stat!=0 {$this->qs_sql()}
            UNION ALL
            select *,2 from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 {$searchArr['kk']} and stat!=0 {$this->qs_sql()} ) a 
            GROUP BY aid order by time desc limit ".(($page-1)*20).",20"; 
        
        $copy = M()->query($copySql);   

        foreach($copy as $k => $v){
            // 查询数据
            $system = $v[1]==1?'kk':'yxhb';
            $tableInfo = $this->searchTableInfo($system,$v['mod_name']);
            // $res = M($tableInfo['table_name'])->field($tableInfo['copy_field'])->where(array($tableInfo['id'] => $v['aid']))->find();

            $res = D(ucfirst($system).$v['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            if( empty($res['sales']) ) continue;
            $appStatus =D($system.'Appflowproc')->getWorkFlowStatus($res['modname'], $v['aid']);
            $statRes = $this->transStat($v['mod_name'],$res['stat']);
            $stat = $statRes !== 'false' ? $statRes: $res['stat'];
            $arr = array(
                'system'    => $system,
                'systemName'=> $systemName[$system],
                'mod_name'  => $res['modname'],
                'title'     => $tableInfo['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($res['date'])),
                'applyer'   => $res['sales'],
                'stat'      => $stat,
                'title2'    => $res['title2'],
                'titlename' => $res['title'],
                'name'      => $res['name'],
                'approve'   => $res['approve'],
                'notice'    => $res['notice'],
                'apply'     => $appStatus
            );
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * 抄送未读数量
     */
    public function getPushCount(){
        $wx_id = session('wxid'); 
        //抄送未读  yxhb - kk
        $copeSql = "SELECT count(1) as count from (
            select * from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2 and stat<>0 {$this->qs_sql()} GROUP BY aid
            union ALL
            select * from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=2  and stat<>0 {$this->qs_sql()} GROUP BY aid)a ";
        $copyRes = M()->query($copeSql);
        $copy_count =$copyRes[0]['count'];
        return $copy_count?$copy_count:0;
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
            select *,1 from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 {$searchArr['yxhb']} and stat!=0 {$this->qs_sql()}
            UNION ALL
            select *,2 from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and FIND_IN_SET('{$wx_id}',`readed_id`) and type=1  {$searchArr['kk']} and stat!=0 {$this->qs_sql()}) a 
            GROUP BY aid order by time desc limit ".(($page-1)*20).",20"; 
        
        $copy = M()->query($copySql);   

        foreach($copy as $k => $v){
            // 查询数据
            $system = $v[1]==1?'kk':'yxhb';
            $tableInfo = $this->searchTableInfo($system,$v['mod_name']);
            // $res = M($tableInfo['table_name'])->field($tableInfo['copy_field'])->where(array($tableInfo['id'] => $v['aid']))->find();

            $res = D(ucfirst($system).$v['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            if( empty($res['sales']) ) continue;
            $appStatus =D($system.'Appflowproc')->getWorkFlowStatus($res['modname'], $v['aid']);
            $statRes = $this->transStat($v['mod_name'],$res['stat']);
            $stat = $statRes !== 'false' ? $statRes: $res['stat'];
            $arr = array(
                'system'    => $system,
                'systemName'=> $systemName[$system],
                'mod_name'  => $res['modname'],
                'title'     => $tableInfo['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($res['date'])),
                'applyer'   => $res['sales'],
                'stat'      => $stat,
                'title2'    => $res['title2'],
                'titlename' => $res['title'],
                'name'      => $res['name'],
                'approve'   => $res['approve'],
                'notice'    => $res['notice'],
                'apply'     => $appStatus
            );
            $result[] = $arr;
        }
        
        return $result;
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
            'CgfkApply'               => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'WlCgfkApply'             => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'PjCgfkApply'             => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'CostMoney'               => array('5' =>2 ,'4' => 1 ,'2' => 1),
            'Contract_guest_Apply'    => array('2' =>2 ,'1' => 1 ,'5' => 0, '4' => 0 , '3' => 1),
            'Contract_guest_Apply2'   => array('2' =>2 ,'1' => 1 ,'5' => 0, '4' => 0 , '3' => 1),
            'ContractApply'           => array(0 => 0, 1 => 2,2 => 1,3 => 2),
        );

        if(!$statArr[$modname]) return 'false';
        return $statArr[$modname][$stat];
    }
    /**
     * 对抄送数组进行处理
     * @param string $system 系统类型
     * @param array  $copy   抄送的数组
     * @return array $result 处理过后的数组  
     */
    public function dealCopyArr($copy){
        $systemName = array('kk'=>'建材', 'yxhb'=>'环保');
        $result = array();
        foreach($copy as $k => $v){
            // 查询数据
            $system = $v[1] == 1?'yxhb':'kk';
            $tableInfo = $this->searchTableInfo($system,$v['mod_name']);
           // $res = M($tableInfo['table_name'])->field($tableInfo['copy_field'])->where(array($tableInfo['id'] => $v['aid']))->find();

            $res = D(ucfirst($system).$v['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            if( empty($res['sales']) ) continue;
            $appStatus =D($system.'Appflowproc')->getWorkFlowStatus($res['modname'], $v['aid']);
            $statRes = $this->transStat($v['mod_name'],$res['stat']);
            $stat = $statRes !== 'false' ? $statRes: $res['stat'];
            $arr = array(
                'system'    => $system,
                'systemName'=> $systemName[$system],
                'mod_name'  => $res['modname'],
                'title'     => $tableInfo['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($res['date'])),
                'myDate'    => $res['date'],
                'applyer'   => $res['sales'],
                'titlename' => $res['title'],
                'title2'    => $res['title2'],
                'name'      => $res['name'],
                'stat'      => $stat,
                'approve'   => $res['approve'],
                'notice'    => $res['notice'],
                'apply'     => $appStatus
            );
            $result[] = $arr;
           
        }
        return $result;
    }

    
    /**
     * 抄送未读数量
     */
    public function getCopyCount(){
        $wx_id = session('wxid'); 
        //抄送未读  yxhb - kk
        $copeSql = "SELECT count(1) as count from (
            select * from kk_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1 and stat!=0 {$this->qs_sql()} GROUP BY aid
            union ALL
            select * from yxhb_appcopyto where FIND_IN_SET('{$wx_id}',`copyto_id`) and !FIND_IN_SET('{$wx_id}',`readed_id`) and type=1  and stat!=0 {$this->qs_sql()} GROUP BY aid)a ";
        $copyRes = M()->query($copeSql);
        $copy_count =$copyRes[0]['count'];
        return $copy_count?$copy_count:0;
    }

    /**
     * 未审批数量
     */
    public function getApproveCount(){
        $tab = $this->getAppTable();
        $count = 0;
        foreach ($tab as $k => $v ) {
            $id = session($v['system'].'_id'); 
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']} b on a.aid=b.{$v['id']}")
                    ->field('1')
                    ->where(array('a.app_stat' => 0,'b.'.$v['stat'] => $v['submit']['stat'],'a.mod_name' => $v['mod_name'] , 'a.per_id' => $id))
                    ->select();
            $count += count($res);
        }
        return $count;
    }
    /**
     * 查询表 -> 用于查询未审批的
     */
    public function getAppTable(){
        $seek =  D('Seek');
        return $seek->getAppTable();
    }

    /**
     * 获取所需查询的模块的表名
     * @param string $mod_name 模块名
     * @return string 对应的表名
     */
    public function searchTableInfo($system,$mod_name=''){
        $tableArr = $this->getAppTable();
        $tableArr = $this->arrayMerge($tableArr);
        $result = '';
        foreach($tableArr as $k =>$v){
            if($mod_name == 'WlCgfkApply' || $mod_name == 'PjCgfkApply' ) $mod_name = 'CgfkApply';
            if($v['system'] == $system && $v['mod_name'] == $mod_name){
                $result = $v;
               
                break;
            }
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
        $tableArr = $this->getAppTable();
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
     * 指标配置页面
     */
    public function  config(){
        $modname = I('get.mod');
        switch($modname){
            case 'SnRatioApply':
                $this->config_sn();      
            break;
            case 'FhfRatioApply':
                $this->config_fhf();      
            break;
            default:
                $this->config_kf();      
        }
    } 
    // 矿粉配置
    public function config_kf(){
        $pro = I('get.product');

        $sql  = "SELECT DISTINCT product from ratio_config where stat=1 and modname='KfRatioApply' ORDER BY id";
        $type = M()->query($sql);
        
        $this->assign('product',$pro);
        $this->assign('ratioType',$type);
        $this->assign('title','指标配置');
        $this->display('Seek/config');
    }
    // 水泥配置
    public function config_sn(){
        $pro = I('get.product');
        $sql  = "SELECT DISTINCT product from ratio_config where stat=1 and modname='SnRatioApply' ORDER BY id";
        $type = M()->query($sql);
        
        $this->assign('product',$pro);
        $this->assign('ratioType',$type);
        $this->assign('title','指标配置');
        $this->display('Seek/config_sn');
    }
     // 水泥配置
     public function config_fhf(){
        $pro = I('get.product');
        $sql  = "SELECT DISTINCT product from ratio_config where stat=1 and modname='FhfRatioApply' ORDER BY id";
        $type = M()->query($sql);
        
        $this->assign('product',$pro);
        $this->assign('ratioType',$type);
        $this->assign('title','指标配置');
        $this->display('Seek/config_fhf');
    }
    /**
     * 配置数据查询 
     * @param string
     */
    public function config_api($type,$modname){

        $modname = $modname?$modname:'KfRatioApply';
        $sql  = "SELECT * from ratio_config where stat=1 and product='{$type}' and modname='{$modname}' ORDER BY id";
        $type = M()->query($sql);
        return $type;
    }

    /***
     * 配置修改
     */
    public function config_submit(){
        $type = I('post.product');
        $data = I('post.data');
        // 参数检验
        $ini  = $this->config_api($type,'KfRatioApply');
        // 修改查看
        $name   = session('name');
        $change = array(); 
        if($data[0] != $ini[0]['value'])  $change[] = array('筛余',$ini[0]['value'],$data[0]);
        if($data[1] != $ini[1]['value'])  $change[] = array('比表',$ini[1]['value'],$data[1]);
        if($data[2] != $ini[2]['value'])  $change[] = array('水份',$ini[2]['value'],$data[2]);

        // 重复提交
        if(!M('ratio_config')->autoCheckToken($_POST)) return array('code' => 404);
        // 修改
        foreach($change as $v){
            M('ratio_config')->where(array('product' => $type, 'name' => $v[0],'stat' => 1,'modname' => 'KfRatioApply'))->setField('value', $v[2]);
        }
      
        // 历史记录
        $history = array(
            'name' => $name,
            'time' => date('Y-m-d H:i',time()),
            'word' => json_encode($change),
            'modname' => 'KfRatioApply',
            'type'    =>  $type
        );
        $res = M('ratio_history')->add($history);
        
        return $res?array('code' => 200):array('code' => 404);

    }

    public function config_sn_submit(){
        $type = I('post.product');
        $data = I('post.data');
        // 参数检验
        $ini  = $this->config_api($type,'SnRatioApply');

        // 修改查看
        $name   = session('name');
        $change = array(); 
        if($data[0] != $ini[0]['value'])  $change[] = array('细度方式',$ini[0]['value'],$data[0]);
        if($data[1] != $ini[1]['value'])  $change[] = array('细度',$ini[1]['value'],$data[1]);
        if($data[2] != $ini[2]['value'])  $change[] = array('SO3',$ini[2]['value'],$data[2]);
        if($data[3] != $ini[3]['value'])  $change[] = array('比表',$ini[3]['value'],$data[3]);
        // 重复提交
        
        if(!M('ratio_config')->autoCheckToken($_POST)) return array('code' => 404);
        // 修改
        foreach($change as $v){
            M('ratio_config')->where(array('product' => $type, 'name' => $v[0],'stat' => 1,'modname' => 'SnRatioApply'))->setField('value', $v[2]);
        }

        // 历史记录
        $history = array(
            'name' => $name,
            'time' => date('Y-m-d H:i',time()),
            'word' => json_encode($change),
            'modname' => 'SnRatioApply',
            'type'    =>  $type
        );
        $res = M('ratio_history')->add($history);

        return $res?array('code' => 200):array('code' => 404);
    }

    // 复合粉 提交
    public function config_fhf_submit(){
        $type = I('post.product');
        $data = I('post.data');
        // 参数检验
        $ini  = $this->config_api($type,'FhfRatioApply');

        // 修改查看
        $name   = session('name');
        $change = array(); 
        if($data[0] != $ini[0]['value'])  $change[] = array('筛余',$ini[0]['value'],$data[0]);
        if($data[1] != $ini[1]['value'])  $change[] = array('比表',$ini[1]['value'],$data[1]);

        // 重复提交
        
        if(!M('ratio_config')->autoCheckToken($_POST)) return array('code' => 404);
        // 修改
    
        foreach($change as $v){
            M('ratio_config')->where(array('product' => $type, 'name' => $v[0],'stat' => 1,'modname' => 'FhfRatioApply'))->setField('value', $v[2]);
        }

        // 历史记录
        $history = array(
            'name' => $name,
            'time' => date('Y-m-d H:i',time()),
            'word' => json_encode($change),
            'modname' => 'FhfRatioApply',
            'type'    =>  $type
        );
        $res = M('ratio_history')->add($history);

        return $res?array('code' => 200):array('code' => 404);
    }
}
