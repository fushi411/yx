<?php
namespace Light\Logic;
use Think\Controller;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 新增备案客户逻辑模型
 * @author 
 */

class KkCeShiLogic extends Controller {
    // 实际表名
    protected $trueTableName = 'kk_pushlist';
    private $PageArr = '';
    private $user = '';

    public function __construct(){
        parent::__construct();
        $this->today = date('Y-m-d',time());
        $model = D('Msgdata');
        $mod_name = I('modname');
        $viewtype = I('viewtype');
        $this->PageArr = $model->GetMessage($mod_name,$viewtype);
        $this->user = session('name');
    }

   //
    public function push(){
        $system   = I('get.system');
        $viewtype = I('viewtype');
        $mod_name = I('get.modname');
        $push  = $this->GetPush($system,$mod_name);
        $this -> assign('system', $system);
        $this -> assign('modname', $mod_name);
        $this->assign('push',$push['data']);
        $this -> display($mod_name.'/'.ucfirst($system).$viewtype.'View');
    }

    function GetPush($system,$mod_name)
    {
        if(!$mod_name) return false;
        $data = array(
            'receviers' => '',
            'data'      => array()
        );
        // 查询推送的人员
        $res = M('kk_pushlist')
            ->field('push_name')
            ->where(array('pro_mod' => $mod_name, 'stat' => 1))
            ->find();
        // 为空，返回空数组
        if(empty($res)) return $data;
        $data['receviers'] = $res['push_name'];
        $pushArr = json_decode($res['push_name'],true);
        if(empty($pushArr)) return $data;
        $tempStr = explode(',',$pushArr);
        // where 条件拼接
        foreach($tempStr as $k => $v){
            if($k != 0) $where .=' or ';
            $where .= "wxid = '{$v}'";
        }
        $res = M($system.'_boss')
            ->field('name,wxid,avatar')
            ->where($where)
            ->select();
        $temp = $res;
        foreach($temp as $k => $v){
            $temp[$k]['sortwxid'] = strtolower($v['wxid']);
        }
        $top  = array('ChenBiSong','csh','csl');
        $array = array('','','');

        $temp  = list_sort_by($temp,'sortwxid','asc');

        foreach($temp as $v){
            if($v['wxid'] == $top[0]){ $array[0] = $v;continue;}
            if($v['wxid'] == $top[1]){ $array[1] = $v;continue;}
            if($v['wxid'] == $top[2]){ $array[2] = $v;continue;}
            $array[] = $v;
        }
        $data['data'] = array_filter($array);
        return $data;
    }
    
}