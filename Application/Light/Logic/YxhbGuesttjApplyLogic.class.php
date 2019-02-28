<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 环保客户调价查看
 * @author 
 */

class YxhbGuesttjApplyLogic extends Model {

    protected $trueTableName = 'yxhb_guest_tj';  // 实际表名（查询基本信息） yxhb_appflowproc 查询时间、审核意见、审批状态   yxhb_appflowtable查询关注点
    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array(
            'id' => $id,
        );
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
            'value'=>'环保客户调价',
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'申请日期：',
            'value'=>$res['date'],
            'type'=>'date',
            'color' => 'black'
        );

//        $result['content'][] = array('name'=>'客户调价：',
//            'value'=>'查看客户调价',
//            'type'=>'date',
//            'color' => 'black'
//        );

        $result['imgsrc'] = '';
        $result['applyerID'] =  $res['applyuser'];                                               //申请者的id
        $result['applyerName'] = $this->get_name($res['applyuser']);                            //申请者的姓名
        $result['stat'] = $this->transStat($res['id']);                                        //审批状态
        return $result;
    }

    // 状态值转换
    public function transStat($id){
        $map = array(
            'aid'=>$id,
            'mod_name'=>'GuesttjApply'
        );
        $res = M('yxhb_appflowproc')->field('app_stat')->where($map)->select();
        $stat = array();
        foreach ($res as $value){
            $stat[] = $value['app_stat'];
        }
        if (in_array(0, $stat))  return 2; //审批中
        if (in_array(1, $stat))  return 2; //退审 先2 后1
        return 1;                                  //审批通过
    }


    //查询申请者的姓名
    public function get_name($id){
        $map = array(
            'id'=>$id,
        );
        $res= M("yxhb_boss")->field('name')->where($map)->find();
        return $res['name'];
    }



}