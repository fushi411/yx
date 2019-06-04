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

    // 分发接口
    public function Api(){
        $action = I('post.action');  
        $this->$action();
    }
    // 基本配置
    public function config(){
        return D('Seek')->configSign();
    }
    # START 数量 --------------------
    // 未签收的数量
    public function unSignCount(){
        $sub   = $this->unSignData();
        return count($sub);
    }
    public function getPushCount(){
        $res = $this->PushAndCopyData(2,1);
        return count($res);
    }
    public function getCopyCount(){
        $res = $this->PushAndCopyData(1,1);
        return count($res);
    }
    # END 数量 ---------------------

    # START 接口 ------------------
    // 未签收的接口
    public function unSign(){
        $sub = $this->unSignData();
        $sub = list_sort_by($sub,'date','desc');
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,2,1); 
        $this->ajaxReturn($res);        
    }
    // 签收的接口
    public function isSign(){
        $res = $this->getIsSign();
        $res = $this->reData($res);
        $res = D('Html')->getMyApproveHtml($res,2); 
        $this->ajaxReturn($res);        
    }
    // 提交 => 待审核
    public function mySubmit(){
        $submit = $this->SubmitData(1);
        $this->ajaxReturn($submit); 
    }
    // 提交 => 已审核
    public function mySubmited(){
        $submit = $this->SubmitData('');
        $this->ajaxReturn($submit); 
    }
    // 推送 => 未读
    public function unReadPush(){
        $sub = $this->PushAndCopyData(2,1);
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,2,1); 
        $this->ajaxReturn($res); 
    }
    // 推送 => 已读
    public function pushToApi(){
        $sub = $this->PushAndCopyData(2);
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,2); 
        $this->ajaxReturn($res); 
    }
     // 抄送 => 未读
     public function unReadCopy(){
        $sub = $this->PushAndCopyData(1,1);
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,2,1); 
        $this->ajaxReturn($res);
    }
    // 超市 => 已读
    public function copyToApi(){
        $sub = $this->PushAndCopyData(1);
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,2); 
        $this->ajaxReturn($res);
    }
    #END 接口 -------------------------

    #START 数据 -----------------------
    // 未签收数据
    public function unSignData(){
        $config = $this->config(); 
        $result = array();
        $sub = array();
        foreach ($config as $k => $v ) {
            $map = array(
                'a.app_stat'                      => 1,
                "{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],
                'a.mod_name'                      => $v['mod_name']
            );
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field("a.aid")
                    ->where($map)
                    ->select();  
            $aid = '';
            foreach($res as $val){
                $aid .= ",{$val['aid']}";
            };
            // $aid 退审记录
            $id                = session($v['system'].'_id');
            $aid               = trim($aid,',');
            $map['a.app_stat'] = 0;
            $map['a.per_id']   = $id;
            $map['a.aid']      = array('not in',$aid);

            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field($v['copy_field'])
                    ->where($map)
                    ->select();      
            if(!empty($res)){
                foreach($res as $key => $val){
                    $res[$key]['system']  = $v['system'];
                    $res[$key]['mod']     = $v['mod_name'];
                    $res[$key]['modname'] = $v['toptitle'];
                }
            }
            $sub = array_merge($sub,$res);
        }
        return $sub;
    }
    // 已签收数据
    public function getIsSign(){
        $page       = I('post.page_num');
        $page       = $page?$page:1;
        $search     = I('post.search');
        $arr        = $this->getSearchTable($search);
        $mod        = array();
        $idArr['yxhb'] = session('yxhb_id');
        $idArr['kk']   = session('kk_id');
        // sql 重构
        foreach($arr as $k => $v){
            $map = array(
                'a.app_stat'                      => 1,
                "{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],
                'a.mod_name'                      => $v['mod_name']
            );
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field("a.aid")
                    ->where($map)
                    ->select();  
            $aid = '';
            foreach($res as $val){
                $aid .= "or b.aid={$val['aid']} ";
            };
            // $aid 退审记录
            if($k != 0) $sql .= ' UNION all ';
            $userId =  $idArr[$v['system']];
            $sql .=  " select 
                            {$v['copy_field']},{$k} 
                        from {$v['table_name']}   
                        inner join {$v['system']}_appflowproc b on {$v['table_name']}.{$v['id']} = b.aid
                        where  
                            b.mod_name='{$v['mod_name']}'
						AND
                            b.per_id = {$userId}
                        AND (
                            b.app_stat = 1
                            OR b.app_stat = 2
                            {$aid}
                        )";
        }
        if(empty($sql)) return '';
        $sql = "select * from($sql)a GROUP BY aid,`0` ORDER BY date desc limit ".(($page-1)*20).",20";
        $res = M()->query($sql);  
        foreach($res as $k => $v){
            $key = $v['0'];
            $res[$k]['system']  = $arr[$key]['system'];
            $res[$k]['mod']     = $arr[$key]['mod_name'];
            $res[$k]['modname'] = $arr[$key]['toptitle'];
        }
        return $res;
    }
     /**
     * 我的提交记录
     * @param int $limit 1-待签收记录 ''-已签收记录
     */
    public function SubmitData($limit=''){
        $page   = I('post.page_num');
        $page   = $page?$page:1;
        $search = I('post.search');
        $arr    = $this->getSearchTable($search);
        $name   = session('name');
        $mod    = array();
        $eq     = empty($limit)?'!=':'=';
        $unpass = empty($limit)?'=':'!=';
        $and    = empty($limit)?'or':'and';
        // sql 重构
        foreach($arr as $k => $v){
            $map = array(
                'a.app_stat'                      => 1,
                "{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],
                'a.mod_name'                      => $v['mod_name']
            );
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field("a.aid")
                    ->where($map)
                    ->select();  
            $aid = '';
            foreach($res as $val){
                $aid .= "{$and} {$v['id']}{$unpass}{$val['aid']} ";
            };
            if($k != 0) $sql .= ' UNION all ';
            $userId =  $idArr[$v['system']];
            $sql .=  "select 
                        {$v['copy_field']},{$k} 
                    from 
                        {$v['table_name']} 
                    where 
                        {$v['submit']['name']}='{$name}' 
                    AND ( {$v['stat']}{$eq}{$v['submit']['stat']} {$aid}) {$v['map']}";
        }
        if(empty($sql)) return '';
        $sql = "select * from($sql)a GROUP BY aid,`0` ORDER BY date desc";
        if(empty($limit)){
            $sql .= ' LIMIT '.(($page-1)*20).',20';
        }
        $res = M()->query($sql);  
        foreach($res as $k => $v){
            $key = $v['0'];
            $res[$k]['system']  = $arr[$key]['system'];
            $res[$k]['mod']     = $arr[$key]['mod_name'];
            $res[$k]['modname'] = $arr[$key]['toptitle'];
        }
        $res = $this->reData($res);
        $res = D('Html')->getMyApproveHtml($res,2); 
        return $res;
    }
        /**
     * 推送数据
     * @param int $which 1-抄送 2-推送
     * @param int $limit 1-未读 ''-已读
     */
    public function PushAndCopyData($which,$limit=''){
        $page   = I('post.page_num');
        $page   = $page?$page:1;
        $search = I('post.search');
        $arr    = $this->getSearchTable($search);
        $wx_id  = session('wxid');
        
        $eq     = empty($limit)?'':'!';
        // sql 重构
        foreach($arr as $k => $v){
            if($k != 0) $sql .= ' UNION all ';
            $userId =  $idArr[$v['system']];
            $sql .=  " select 
                            {$v['copy_field']},{$k} 
                        from {$v['table_name']}   
                        inner join {$v['system']}_appcopyto b on {$v['table_name']}.{$v['id']} = b.aid
                        where  
                            b.mod_name='{$v['mod_name']}'
						AND
                            b.stat!=0 and b.type={$which} 
                        AND 
                            FIND_IN_SET('{$wx_id}',`copyto_id`) and {$eq}FIND_IN_SET('{$wx_id}',`readed_id`)
                        ";
        }
        if(empty($sql)) return '';
        $sql = "select * from($sql)a GROUP BY aid,`0` ORDER BY date desc";
        if(empty($limit)){
            $sql .= ' LIMIT '.(($page-1)*20).',20';
        }
        $res = M()->query($sql); 
        foreach($res as $k => $v){
            $key = $v['0'];
            $res[$k]['system']  = $arr[$key]['system'];
            $res[$k]['mod']     = $arr[$key]['mod_name'];
            $res[$k]['modname'] = $arr[$key]['toptitle'];
        }
        return $res;
    }
    #END 数据 ---------------------------

    #START 数据重构 ---------------------------
    public function reData($data){
        $result = array();
        foreach($data as $k => $v){
            if( empty($v['applyer']) ) continue;
            $res       = D(ucfirst($v['system']).$v['mod'], 'Logic')->sealNeedContent($v['aid']);
            $appStatus = D($v['system'].'Appflowproc')->getWorkFlowStatus($v['mod'], $v['aid']);
            $arr = array(
                'first_title'    => $res['first_title'],
                'second_title'   => $res['second_title'],
                'third_title'    => $res['third_title'],
                'first_content'  => $res['first_content'],
                'second_content' => $res['second_content'],
                'second_color'   => $res['second_color'],
                'third_content'  => $res['third_content'],
                'date'           => $v['date'],
                'system'         => $v['system'],
                'mod'            => $v['mod'],
                'aid'            => $v['aid'],
                'stat'           => $res['stat'],
                'toptitle'       => $v['modname'],
                'applyer'        => $res['applyerName'],
                'apply'          => $appStatus,
            );
            if(!empty($res['fourth_title'])){
                $arr['fourth_title'] = $res['fourth_title'];
                $arr['fourth_content'] = $res['fourth_content'];
            }
            $result[] = $arr;
        }
        return $result;
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
            $index = array_search($sys[$idx],$tmp);
            unset($tmp[$index]);
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
    // 签收排除数组
    public function qs_sql(){
        $sql = '';
        $mod_array = D('Msgdata')->QsArray();
        foreach($mod_array as $val){
            $sql .= " and `mod_name` = '{$val['pro_mod']}'";
        }
        return $sql;
    }

    // 签收排除数组
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
    #END 数据重构 ---------------------------

    #START 全读 ---------------------------
    // 抄送全读 
    public function copyRead(){
        $wx_id = session('wxid'); 
        $copySql = "SELECT * from (
                        select 
                            `aid`,`readed_id`,`mod_name`,`time`,1 
                        from 
                            kk_appcopyto 
                        where 
                            FIND_IN_SET('{$wx_id}',`copyto_id`) 
                        and !FIND_IN_SET('{$wx_id}',`readed_id`) 
                        and type=1  
                        and stat!=0 {$this->qs_sql_or()}
                    UNION ALL
                        select 
                            `aid`,`readed_id`,`mod_name`,`time`,2 
                        from 
                            yxhb_appcopyto
                        where 
                            FIND_IN_SET('{$wx_id}',`copyto_id`)
                        and !FIND_IN_SET('{$wx_id}',`readed_id`) 
                        and type=1 and stat!=0 {$this->qs_sql_or()}) a 
                    GROUP BY aid order by time desc "; 
        $copy = M()->query($copySql);
        foreach($copy as $k => $v){
            // 查询数据
            $system = $v[1] == 1?'kk':'yxhb';
            $copyTo = D($system.'Appcopyto');
            $copyTo->readCopytoApply($v['mod_name'],$v['aid'] ,null,1);
        }
        $this->ajaxReturn('success');
    }
    //  推送全读
    public function pushRead(){
        $wx_id = session('wxid'); 
        $copySql = "SELECT * from (
                        select 
                            `aid`,`readed_id`,`mod_name`,`time`,1 
                        from 
                            kk_appcopyto 
                        where 
                            FIND_IN_SET('{$wx_id}',`copyto_id`) 
                        and !FIND_IN_SET('{$wx_id}',`readed_id`) 
                        and type=2  and stat!=0 {$this->qs_sql_or()}
                    UNION ALL
                        select 
                            `aid`,`readed_id`,`mod_name`,`time`,2 
                        from 
                            yxhb_appcopyto 
                        where 
                            FIND_IN_SET('{$wx_id}',`copyto_id`) 
                        and !FIND_IN_SET('{$wx_id}',`readed_id`) 
                        and type=2 and stat!=0 {$this->qs_sql_or()}) a 
                    GROUP BY aid order by time desc "; 
        $copy = M()->query($copySql);
        foreach($copy as $k => $v){
            // 查询数据
            $system = $v[1] == 1?'kk':'yxhb';
            $copyTo = D($system.'Appcopyto');
            $copyTo->readCopytoApply($v['mod_name'],$v['aid'] ,null,2);
        }
        $this->ajaxReturn('success');
    }
    #END 全读 ----------------------
}
