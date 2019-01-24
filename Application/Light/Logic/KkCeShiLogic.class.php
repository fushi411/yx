<?php
namespace Light\Logic;
use Think\Controller;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 新增备案客户逻辑模型
 * @author 
 */

class KkCeShiLogic extends BaseController {
    // 实际表名
    protected $trueTableName = 'kk_pushlist';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->find();
    }

    public function getTableName()
    {
        return $this->trueTableName;
    }

    //详情(点击查看之后显示)
    public function recordContent($id)
    {
        $res = $this->record($id);
        $result = array();
        $result['content'][] = array('name'=>'系统类型：',
            'value'=>'建材新增备案客户',
            'type'=>'date',
            'color' => 'black'
        );
        $result['content'][] = array('name'=>'提交时间：',
            'value'=> date('m-d H:i',strtotime($res['dtime'])) ,
            'type'=>'date',
            'color' => 'black'
        );
        $result['content'][] = array('name'=>'申请日期：',
            'value'=>$res['date'],
            'type'=>'date',
            'color' => 'black'
        );
        $result['content'][] = array('name'=>'备案名称：',
            'value'=>$res['name'],
            'type'=>'string',
            'color' => 'black'
        );


        $result['content'][] = array('name'=>'联系人员：',
            'value'=>$res['contacts'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'联系电话：',
            'value'=>$res['telephone'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'相关说明：',
            'value'=>$res['info'],
            'type'=>'text',
            'color' => 'black'
        );
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIDFromName($res['sales']);       //申请人的id
        $result['applyerName'] = $res['sales'];                                 //申请人的姓名
        $result['stat'] = $res['stat'];
        return $result;
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',0);
    }

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    //审批助手显示
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();

        $result[] = array('name'=>'提交时间：',
            'value'=> date('m-d H:i',strtotime($res['dtime'])) ,
            'type'=>'date'
        );

        $result[] = array('name'=>'申请日期：',
            'value'=> $res['date'] ,
            'type'=>'date'
        );

        $result[] = array('name'=>'备案名称：',
            'value'=>$res['name'],
            'type'=>'string'
        );

        $result[] = array('name'=>'联系人员：',
            'value'=>$res['contacts'],
            'type'=>'string'
        );

        $result[] = array('name'=>'联系电话：',
            'value'=>$res['telephone'],
            'type'=>'string'
        );

        $result[] = array('name'=>'相关说明：',
            'value'=>$res['info'],
            'type'=>'text'
        );
        return $result;
    }

    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->getField('salesid');
    }

    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $result = array(
            'sales'   => $res['sales'],         //申请人的姓名
            'title2'  => '备案产品',
            'approve' => iconv('gbk','UTF-8',$res['product']),
            'notice'  => $res['info'],
            'date'    => $res['date'],
            'title'   => '备案名称',
            'name'    => $res['name'],
            'modname' => 'NewGuestApply',
            'stat'    => $res['stat']
        );
        return $result;
    }

   //
    public function push(){
        $system = 'kk';
        $special_page = I('get.special');
        $mod_name = 'CeShi';
        $push  = $this->GetPush($system,$mod_name);
        $this -> assign('push',$push['data']);
        $this->display($mod_name.'/'.$special_page);
        return $push;
    }

    function GetPush($system,$mod_name)
    {
        if(!$mod_name) return false;
        $data = array(
            'receviers' => '',
            'data'      => array()
        );
        // 查询推送的人员
        $res = M($system.'_pushlist')
            ->field('push_name')
            ->where(array('pro_mod' => $mod_name, 'stat' => 1))
            ->find();
        // 为空，返回空数组
        if(empty($res)) return $data;
        $data['receviers'] = $res['push_name'];
        $pushArr = json_decode($res['push_name'],true);
        $push_id = $pushArr['push'];
        $tempStr = explode(',',$push_id);

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