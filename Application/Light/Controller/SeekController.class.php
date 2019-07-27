<?php
namespace Light\Controller;
use Think\Controller;

class SeekController  extends BaseController  
{
    private $titleArr;
    private $flag;
    private $titlename;
    public function __construct(){
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
        $cost = I('get.cost');
        $this->flag = $cost?$cost:'';
        $this->titlename = $cost?'我的费用':'我的审批';
        $this->titleArr = array(
            array('title' => '待我审批','url' => U('Light/Seek/myApprove',array('cost'=>$this->flag)),'on' => '','unRead' => $this->getApproveCount()),//
            array('title' => '我的提交','url' => U('Light/Seek/mySubmit' ,array('cost'=>$this->flag)),'on' => '','unRead' => 0),
            array('title' => '推送我的','url' => U('Light/Seek/pushToMe' ,array('cost'=>$this->flag)),'on' => '','unRead' => $this->getPushCount()),
            array('title' => '抄送我的','url' => U('Light/Seek/copyToMe' ,array('cost'=>$this->flag)),'on' => '','unRead' => $this->getCopyCount())//
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
                $data = $this->SubmitData();
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
     * 我的审批
     */
    public function myApprove(){
        $this->titleArr[0]['on'] = 'weui-bar__item_on';
        $this->assign('titleArr',$this->titleArr);  
        $data = $this->noApprove();
        $this->assign('title',$this->titlename);
        $this->assign('cost',$this->flag);
        $this->assign('noApprove',$data);
        $this->display('Seek/myApprove');
    }
    /**
     * 提交记录
     */
    public function mySubmit(){
        
        $this->titleArr[1]['on'] = 'weui-bar__item_on';
        // 未审批记录
        $data = $this->SubmitData(1); 
        $this->assign('title',$this->titlename);
        $this->assign('cost',$this->flag);
        $this->assign('noApprove',$data);
        $this->assign('titleArr',$this->titleArr);  
        $this->display('Seek/mySubmit');
    }

    /**
     * 抄送我的  
     *  - 未读抄送渲染，已读使用接口 
     */
    public function copyToMe(){
        $this->titleArr[3]['on'] = 'weui-bar__item_on';
        $data = $this->unReadCopyData(); 
        $this->assign('title',$this->titlename);
        $this->assign('cost',$this->flag);
        $this->assign('noApprove',$data);
        $this->assign('titleArr',$this->titleArr);  
        $this->display('Seek/copyToMe');
    }
    /**
     * 推送我的  
     *  - 未读推送渲染，已读使用接口 
     */
    public function pushToMe(){
        //header("Content-type: text/html; charset=utf-8"); 
        $this->titleArr[2]['on'] = 'weui-bar__item_on';
        $data = $this->unReadPushData(); 
        $this->assign('title',$this->titlename);
        $this->assign('cost',$this->flag);
        $this->assign('noApprove',$data);
        $this->assign('titleArr',$this->titleArr); 
        $this->display('Seek/pushToMe');
    } 

    #STAR  一键全读 ---------------------------------------
    //   抄送全读
    public function copyReadApi(){
        $this->allRead(1);
        return  'success';
    }

    // 推送全读
    public function pushReadApi(){
        $this->allRead(2);
        return 'success';
    }

    // 全读功能
    public function allRead($id){
        $wx_id = session('wxid'); 
        $pushSql = "SELECT * from (
                    SELECT  
                        `aid`,`readed_id`,`mod_name`,`time`,1 
                    FROM 
                        `yxhb_appcopyto` 
                    WHERE 
                        FIND_IN_SET('{$wx_id}',`copyto_id`) 
                        AND !FIND_IN_SET('{$wx_id}',`readed_id`) 
                        and type={$id} 
                        AND `stat` <> 0 {$this->qs_sql()}
                UNION ALL
                    SELECT  
                        `aid`,`readed_id`,`mod_name`,`time`,2 
                    FROM 
                        `kk_appcopyto` 
                    WHERE 
                        FIND_IN_SET('{$wx_id}',`copyto_id`) 
                        AND !FIND_IN_SET('{$wx_id}',`readed_id`) 
                        and type={$id} AND `stat` <> 0 {$this->qs_sql()}
                ) a GROUP BY aid order by time desc";
                
        $push = M()->query($pushSql);   
        foreach($push as $k => $v){
            // 查询数据
            $system = $v[1] == 1?'yxhb':'kk';
            $copyTo = D($system.'Appcopyto');
            $copyTo->readCopytoApply($v['mod_name'],$v['aid'] ,null,$id);
        }
    }

    //  签收排除数组
    public function qs_sql(){
        $sql = '';
        $mod_array = D('Msgdata')->QsArray();
        foreach($mod_array as $val){
            $sql .= " and `mod_name` <> '{$val['pro_mod']}'";
        }
        $cost = $this->flag?' and `mod_name` = "CostMoney"':' and `mod_name` <> "CostMoney"';
        $sql .= $cost;
        return $sql;
    }
    #END  一键全读 ---------------------------------------
    
    #START 未读数量 ------------------------
    // 未审批
    public function getApproveCount(){
        $res = $this->noApproveData();
        return count($res);
    }
    
    // 推送未读
    public function getPushCount(){
        $res = $this->PushAndCopyData(2,1);
        return count($res);
    }

    // 抄送未读
    public function getCopyCount(){
        $res = $this->PushAndCopyData(1,1);
        return count($res);
    }
    #END 未读数量 ------------------------

    #START 未读数据 --------------------
    // 未审批数据
    public function noApproveData(){
        $tab = $this->getAppTable();
        $tab = $this->arrayMerge($tab);
        $sub = array();
        foreach ($tab as $k => $v ) {
            $wh = $v['map']?$v['map']:'';
            $id = session($v['system'].'_id');
            $map = array(
                'a.app_stat'                      => 1,
                "{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],
                'a.mod_name'                      => $v['mod_name']
            );
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']} {$wh}")
                    ->field("a.aid")
                    ->where($map)
                    ->select();  
            $aid = '';
            foreach($res as $val){
                $aid .= ",{$val['aid']}";
            };
            // $aid 未退审记录
            $aid               = trim($aid,',');
            $map['a.app_stat'] = 0;
            $map['a.per_id']   = $id;
            $map['a.aid']      = array('not in',$aid);

            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']} {$wh}")
                    ->field($v['copy_field'].',a.app_name')
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
        $temp = array();
        foreach($sub as $val){
            $k = $val['app_name'] == '转审'?0:1;
            $temp[$k][] = $val; 
        }  
        if(empty($temp[0])) $sub = $temp[1];
        else $sub = array_merge($temp[0],$temp[1]);
        return $sub;
    }
    public function noApprove(){
        $sub = $this->noApproveData();
        $sub = list_sort_by($sub,'date','desc');
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,1,1); 
        return $res;
    }

    // 已审批记录数据
    public function approve(){
        $page       = I('post.page_num');
        $page       = $page?$page:1;
        $search     = I('post.search');
        $arr        = $this->getSearchTable($search);
        $mod        = array();
        $idArr['yxhb'] = session('yxhb_id');
        $idArr['kk']   = session('kk_id');
        // sql 重构
        foreach($arr as $k => $v){
            $wh = $v['map']?$v['map']:'';
            $map = array(
                'a.app_stat'                      => 1,
                "{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],
                'a.mod_name'                      => $v['mod_name']
            );
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']} {$wh}")
                    ->field("a.aid")
                    ->where($map)
                    ->select();  
            $aid = '';
            foreach($res as $val){
                $aid .= "or b.aid={$val['aid']} ";
            };
            // $aid 未退审记录
            if($k != 0) $sql .= ' UNION all ';
            $userId           =  $idArr[$v['system']];

            $sql .=  " select 
                            {$v['copy_field']},{$k} 
                        from {$v['table_name']}   
                        inner join {$v['system']}_appflowproc b on {$v['table_name']}.{$v['id']} = b.aid
                        where  
                            b.mod_name='{$v['mod_name']}'
						AND
                            b.per_id = {$userId}
                            {$wh}
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
        $res = $this->reData($res);
        $res = D('Html')->getMyApproveHtml($res,1); 
        return $res;
    }

    /**
     * 我的提交记录
     * @param int $limit 1-待审批记录 ''-已审批记录
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
            $wh = $v['map']?$v['map']:'';
            $map = array(
                'a.app_stat'                      => 1,
                "{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],
                'a.mod_name'                      => $v['mod_name']
            );
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']} {$wh}")
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
                    AND ( {$v['stat']}{$eq}{$v['submit']['stat']} {$aid})  {$v['map']} {$wh}";
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
        $res = D('Html')->getMyApproveHtml($res,1); 
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
    // 推送未读
    public function unReadPushData(){
        $sub = $this->PushAndCopyData(2,1);
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,1,1); 
        return $res;
    }
    // 已读推送
    public function pushToApi(){
        $sub = $this->PushAndCopyData(2);
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,1); 
        return $res;
    }
    // 推送未读
    public function unReadCopyData(){
        $sub = $this->PushAndCopyData(1,1);
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,1,1); 
        return $res;
    }
    // 已读推送
    public function copyToApi(){
        $sub = $this->PushAndCopyData(1);
        $res = $this->reData($sub);
        $res = D('Html')->getMyApproveHtml($res,1); 
        return $res;
    }
    #END 未读数据 --------------------

    #START 数据重构 ---------------------------
    public function reData($data){
        $result = array();
        foreach($data as $k => $v){
            if( empty($v['applyer']) ) continue;
            $res       = D(ucfirst($v['system']).$v['mod'], 'Logic')->sealNeedContent($v['aid']);
            $appStatus = D($v['system'].'Appflowproc')->getWorkFlowStatus($v['mod'], $v['aid']);
            $arr = array(
                'content'    => $res['content'],
                'date'       => $v['date'],
                'system'     => $v['system'],
                'mod'        => $v['mod'],
                'aid'        => $v['aid'],
                'stat'       => $res['stat'],
                'toptitle'   => $v['modname'],
                'applyer'    => $res['applyerName'],
                'apply'      => $appStatus,
                'app_name'   => $v['app_name'],
            );
            $result[] = $arr;
        }
        return $result;
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
#END 数据重构 ---------------------------
######## END => 我的审批 ##########################

######## START => 配置 ##########################
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
