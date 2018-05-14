<?php
namespace Light\Controller;
use Think\Controller;

class SeekController extends Controller 
{
    private $titleArr;

    public function __construct(){
        parent::__construct();
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
        //dump($this->titleArr);
        $this->display('Seek/myApprove');
    }
    /**
     * 提交记录
     */
    public function mySubmit(){
        $this->titleArr[1]['on'] = 'weui-bar__item_on';
        $this->assign('titleArr',$this->titleArr);
        $this->display('Seek/mySubmit');
    }
    /**
     * 抄送我的
     */
    public function copyToMe(){
        $this->titleArr[2]['on'] = 'weui-bar__item_on';
        $this->assign('titleArr',$this->titleArr);
        $this->display('Seek/copyToMe');
    }
   
    
    /**
     * 抄送未读数量
     */
    public function getCopyCount(){
        $wx_id = session('wxid'); 
        //抄送未读  yxhb - kk
        $copeSql = "SELECT count(1) as count from (
            select * from kk_appcopyto where copyto_id like '%{$wx_id}%' and readed_id not like '%{$wx_id}%' and type=1
            union ALL
            select * from yxhb_appcopyto where copyto_id like '%{$wx_id}%' and readed_id not like '%{$wx_id}%' and type=1)a";
        $copyRes = M()->query($copeSql);
        $copy_count =$copyRes['count'];
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
            array( 'titile' => '环保临时额度' , 'system' => 'yxhb' ,'mod_name' =>'TempCreditLineApply','table_name' =>'yxhb_tempcreditlineconfig','id'=>'id'),
            array( 'titile' => '环保信用额度' , 'system' => 'yxhb' ,'mod_name' =>'CreditLineApply'    ,'table_name' =>'yxhb_creditlineconfig'    ,'id'=>'aid'),
            array( 'titile' => '建材临时额度' , 'system' => 'kk'   ,'mod_name' =>'TempCreditLineApply','table_name' =>'kk_tempcreditlineconfig'  ,'id'=>'id'),
            array( 'titile' => '建材信用额度' , 'system' => 'kk'   ,'mod_name' =>'CreditLineApply'    ,'table_name' =>'kk_creditlineconfig'      ,'id'=>'aid'),
        );
        return $appArr;
    }
}
