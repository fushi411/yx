<?php
namespace Light\Controller;
use Think\Controller;

class SeekController extends BaseController 
{
    private $titleArr;

    public function __construct(){
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
        $this->titleArr = array(
            array('title' => '待我审批','url' => U('Light/Seek/myApprove'),'on' => '','unRead' => $this->getApproveCount()),
            array('title' => '我的提交','url' => U('Light/Seek/mySubmit'),'on' => '','unRead' => 0),
            array('title' => '抄送我的','url' => U('Light/Seek/copyToMe'),'on' => '','unRead' => $this->getCopyCount())
        );
        
    }

    /**
     * 我的审批
     */
    public function myApprove(){
        $this->titleArr[0]['on'] = 'weui-bar__item_on';
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
        $table_info = $this->getAppTable();
        $yxhb_id    = session('yxhb_id');
        $kk_id      = session('kk_id');
        $Mod        = array('kk' => '', 'yxhb' => '');
        // sql语句构造
        foreach ($table_info as $k => $v) {
            if(!empty($Mod[$v['system']])){
                $Mod[$v['system']] .= ' or ';
            }
             $Mod[$v['system']] .= "mod_name='{$v['mod_name']}'";
        }
       
        $yxhbSql = 'SELECT *,1 from yxhb_appflowproc where per_id='.$yxhb_id.' and (app_stat=1 or app_stat=2) and ('.$Mod['yxhb'].')';
        $kkSql   = 'SELECT *,2 from kk_appflowproc where per_id='.$kk_id.' and (app_stat=1 or app_stat=2) and ('.$Mod['kk'].')';
        $sql     = "select * from ({$yxhbSql} union all {$kkSql}) a order by time desc limit ".(($page-1)*20).",20";
        $approve = M()->query($sql);   

        foreach($approve as $k => $v){
            // 查询数据
            $system = $v[1]==2?'kk':'yxhb';
            $tableInfo = $this->searchTableInfo($system,$v['mod_name']);
            // $res = M($tableInfo['table_name'])->field($tableInfo['copy_field'])->where(array($tableInfo['id'] => $v['aid']))->find();

            $res = D(ucfirst($system).$v['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            $appStatus =D($system.'Appflowproc')->getWorkFlowStatus($v['mod_name'], $v['aid']);
            $arr = array(
                'system'    => $system,
                'systemName'=> $systemName[$system],
                'mod_name'  => $v['mod_name'],
                'title'     => $tableInfo['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($res['date'])),
                'applyer'   => $res['sales'],
                'stat'      => $res['stat'],
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
        $result = array();
        $sub = array();
        foreach ($tab as $k => $v ) {
            $id = session($v['system'].'_id'); 
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field($v['copy_field'])
                    ->where(array('a.app_stat' => 0,"{$v['table_name']}.stat" => 2,'a.mod_name' => $v['mod_name'] , 'a.per_id' => $id))
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
            $res       = D(ucfirst($tab[$v[0]]['system']).$tab[$v[0]]['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            $appStatus =D($tab[$v[0]]['system'].'Appflowproc')->getWorkFlowStatus($tab[$v[0]]['mod_name'], $v['aid']);
            $arr = array(
                'system'    => $tab[$v[0]]['system'],
                'systemName'=> $tab[$v[0]]['title'],
                'mod_name'  => $tab[$v[0]]['mod_name'],
                'title'     => $tab[$v[0]]['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($v['date'])),
                'applyer'   => $v['applyer'],
                'stat'      => $v['stat'],
                'approve'   => $v['approve'],
                'notice'    => $v['notice'],
                'apply'     => $appStatus
            );
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * 提交记录
     */
    public function mySubmit(){
        $this->titleArr[1]['on'] = 'weui-bar__item_on';
        $this->assign('titleArr',$this->titleArr);
        $submit = $this->mySubmitData(1);
        $this->assign('submit',$submit);
        $this->display('Seek/mySubmit');
    }

    /**
     * 获取我的提交记录
     */
    public function mySubmitData($limit=''){
        $page       = I('post.page_num');
        $page       = $page?$page:1;
        $result     = array();
        $table_info = $this->getAppTable();
        if(empty($limit)){
            $stat = 'stat!=2';
        }else{
            $stat = 'stat=2';
        }
        // sql语句构造
        $submit_sql = 'SELECT * from(';
        foreach($table_info as $k =>$v){
            $id = session($v['system'].'_id');
            if($k != 0) $submit_sql .= ' UNION all ';
            $submit_sql .=  " select {$v['copy_field']},{$k} from {$v['table_name']} where salesid='{$id}' and {$stat}";
        }
        $submit_sql .=')a ORDER BY date desc';
        
        if(empty($limit)){
            $submit_sql .= ' LIMIT '.(($page-1)*20).',20';
        }
        $sub = M()->query($submit_sql);
        // 数据重构
        foreach($sub as $k => $v){
            $res       = D(ucfirst($table_info[$v[0]]['system']).$table_info[$v[0]]['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            $appStatus =D($table_info[$v[0]]['system'].'Appflowproc')->getWorkFlowStatus($table_info[$v[0]]['mod_name'], $v['aid']);
            $arr = array(
                'system'    => $table_info[$v[0]]['system'],
                'systemName'=> $table_info[$v[0]]['title'],
                'mod_name'  => $table_info[$v[0]]['mod_name'],
                'title'     => $table_info[$v[0]]['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($v['date'])),
                'applyer'   => $v['applyer'],
                'stat'      => $v['stat'],
                'approve'   => $v['approve'],
                'notice'    => $v['notice'],
                'apply'     => $appStatus
            );
            $result[] = $arr;
        }
        return $result;  
    }

    /**
     * 抄送我的  
     *  - 未读抄送渲染，已读使用接口 
     */
    public function copyToMe(){
        //header("Content-type: text/html; charset=utf-8"); 
        $this->titleArr[2]['on'] = 'weui-bar__item_on';
        // 系统分开 yxhb kk 
        $wx_id = session('wxid'); 
        $copy = array();
        // 环保未读 yxhb 

        $copySql = "SELECT * from (
                     SELECT  `aid`,`readed_id`,`mod_name`,`time`,1 FROM `yxhb_appcopyto` WHERE `copyto_id` LIKE '%{$wx_id}%' AND `readed_id` NOT LIKE '%{$wx_id}%' AND `stat` <> 0
                    UNION ALL
                     SELECT  `aid`,`readed_id`,`mod_name`,`time`,2 FROM `kk_appcopyto` WHERE `copyto_id` LIKE '%{$wx_id}%' AND `readed_id` NOT LIKE '%{$wx_id}%' AND `stat` <> 0
                    ) a GROUP BY aid order by time desc";
                    
        $copy = M()->query($copySql);          

        $copy = $this->dealCopyArr($copy);
        $this->assign('titleArr',$this->titleArr);
        $this->assign('copyto',$copy);
        $this->display('Seek/copyToMe');
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
        }
        $this->ajaxReturn(array('code' => $code , 'data' => $data));
    }
   
    /**
     * 抄送接口
     */
    public function copyToApi(){
        $systemName = array('1'=>'建材', '2'=>'环保');
        $page = I('post.page_num');
        $page = $page?$page:1;
        $wx_id = session('wxid'); 
        $result = array();
        $copySql = "SELECT * from (
            select *,1 from kk_appcopyto where copyto_id like '%{$wx_id}%' and readed_id like '%{$wx_id}%'  and stat!=0 
            UNION ALL
            select *,2 from yxhb_appcopyto where copyto_id like '%{$wx_id}%' and readed_id like '%{$wx_id}%'  and stat!=0 ) a 
            GROUP BY aid order by time desc limit ".(($page-1)*20).",20"; 
        
        $copy = M()->query($copySql);   

        foreach($copy as $k => $v){
            // 查询数据
            $system = $v[1]==1?'kk':'yxhb';
            $tableInfo = $this->searchTableInfo($system,$v['mod_name']);
            // $res = M($tableInfo['table_name'])->field($tableInfo['copy_field'])->where(array($tableInfo['id'] => $v['aid']))->find();

            $res = D(ucfirst($system).$v['mod_name'], 'Logic')->sealNeedContent($v['aid']);
            $appStatus =D($system.'Appflowproc')->getWorkFlowStatus($v['mod_name'], $v['aid']);
            $arr = array(
                'system'    => $system,
                'systemName'=> $systemName[$system],
                'mod_name'  => $v['mod_name'],
                'title'     => $tableInfo['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($res['date'])),
                'applyer'   => $res['sales'],
                'stat'      => $res['stat'],
                'approve'   => $res['approve'],
                'notice'    => $res['notice'],
                'apply'     => $appStatus
            );
            $result[] = $arr;
        }
        return $result;
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
            $appStatus =D($system.'Appflowproc')->getWorkFlowStatus($v['mod_name'], $v['aid']);
            $arr = array(
                'system'    => $system,
                'systemName'=> $systemName[$system],
                'mod_name'  => $v['mod_name'],
                'title'     => $tableInfo['title'],
                'aid'       => $v['aid'],
                'date'      => date('m/d',strtotime($res['date'])),
                'myDate'    => $res['date'],
                'applyer'   => $res['sales'],
                'stat'      => $res['stat'],
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
            select * from kk_appcopyto where copyto_id like '%{$wx_id}%' and readed_id not like '%{$wx_id}%'  and stat!=0 GROUP BY aid
            union ALL
            select * from yxhb_appcopyto where copyto_id like '%{$wx_id}%' and readed_id not like '%{$wx_id}%'  and stat!=0 GROUP BY aid)a ";
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
                    ->where(array('a.app_stat' => 0,'b.stat' => 2,'a.mod_name' => $v['mod_name'] , 'a.per_id' => $id))
                    ->select();
            $count += count($res);
        }
        return $count;
    }
    /**
     * 查询表 -> 用于查询未审批的
     */
    public function getAppTable(){
        $appArr = array(
            array( 'title' => '环保临时额度' , 'system' => 'yxhb' ,'mod_name' =>'TempCreditLineApply','table_name' =>'yxhb_tempcreditlineconfig','id'=>'id' ,'copy_field' =>'yxhb_tempcreditlineconfig.id as aid,yxhb_tempcreditlineconfig.sales as applyer,yxhb_tempcreditlineconfig.date,yxhb_tempcreditlineconfig.line as approve,yxhb_tempcreditlineconfig.notice,yxhb_tempcreditlineconfig.stat'),
            array( 'title' => '环保信用额度' , 'system' => 'yxhb' ,'mod_name' =>'CreditLineApply'    ,'table_name' =>'yxhb_creditlineconfig'    ,'id'=>'aid','copy_field' =>'yxhb_creditlineconfig.aid,yxhb_creditlineconfig.sales as applyer,yxhb_creditlineconfig.date,yxhb_creditlineconfig.line as approve,yxhb_creditlineconfig.notice,yxhb_creditlineconfig.stat'),
            array( 'title' => '建材临时额度' , 'system' => 'kk'   ,'mod_name' =>'TempCreditLineApply','table_name' =>'kk_tempcreditlineconfig'  ,'id'=>'id' ,'copy_field' =>'kk_tempcreditlineconfig.id as aid,kk_tempcreditlineconfig.sales as applyer,kk_tempcreditlineconfig.date,kk_tempcreditlineconfig.line as approve,kk_tempcreditlineconfig.notice,kk_tempcreditlineconfig.stat'),
            array( 'title' => '建材信用额度' , 'system' => 'kk'   ,'mod_name' =>'CreditLineApply'    ,'table_name' =>'kk_creditlineconfig'      ,'id'=>'aid','copy_field' =>'kk_creditlineconfig.aid,kk_creditlineconfig.sales as applyer,kk_creditlineconfig.date,kk_creditlineconfig.line as approve,kk_creditlineconfig.notice,kk_creditlineconfig.stat'),
        );
        return $appArr;
    }

    /**
     * 获取所需查询的模块的表名
     * @param string $mod_name 模块名
     * @return string 对应的表名
     */
    public function searchTableInfo($system,$mod_name=''){
        $tableArr = $this->getAppTable();
        $result = '';
        foreach($tableArr as $k =>$v){
            if($v['system'] == $system && $v['mod_name'] == $mod_name){
                $result = $v;
                //dump($result);
                break;
            }
        }
        return $result;
    }
}
