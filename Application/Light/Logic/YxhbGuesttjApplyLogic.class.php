<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbGuesttjApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_guest_tj';

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

        $result['content'][] = array('name'=>'客户调价：',
                                    'value'=>'查看客户调价',
                                    'type'=>'date',
                                    'color' => '#337ab7'
                                );
        $result['mydata'] = $this->getTjData($id);

        $result['imgsrc'] = '';
        $result['applyerID'] =  $res['applyuser'];                                               //申请者的id
        $result['applyerName'] = D('YxhbBoss')->getNameFromID($res['applyuser']);               //申请者的姓名
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

    public function getTjData($aid){
        $field = 'b.*';
        $map = array(
            'a.id' => $aid,
        );
        $data = M('yxhb_guest_tj a')
                ->join('yxhb_tj b on a.relationid = b.relationid')
                ->field($field)
                ->where($map)
                ->order('tj_client desc,tj_bzfs desc,tj_cate')
                ->select();
        $guest = $this->getAllGuestName();  
        $temp = array();
        foreach($data as $k => $vo){
            $vo['g_name'] = $guest[$vo['tj_client']];
            $bzfs         = $vo['tj_bzfs'] == '袋装'?'(袋)':'(散)';
            $vo['cate']   = $vo['tj_cate'].$bzfs;
            // 当前价格
            $vo['now'] = bcsub($vo['tj_dj'],$vo['delta_dj'],2);
            // 调整后价格
            $color = (int) $vo['delta_dj']>0? 'red':'green';
            $arrow = (int) $vo['delta_dj']>0? '&uarr;':'&darr;';
            $tmpl  = "<span style='color:".$color."'>(".$vo['delta_dj']."{$arrow})</span>";
            $vo['dj'] = $vo['tj_dj'].$tmpl;
            $temp[$vo['tj_client']]['g_name']  = $guest[$vo['tj_client']];  
            $temp[$vo['tj_client']]['date']    = '调价日期：'.$vo['tj_stday'];  
            $temp[$vo['tj_client']]['child'][] = $vo;    
        }
        return $temp;
    }

    public function getAllGuestName(){
        $data = M('yxhb_guest2')->select();
        $temp = array('无此客户');
        foreach($data  as $val){
            $temp[$val['id']] = $val['g_name'];
        }
        return $temp;
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
    public function getDescription($id){
        $res = $this->record($id);
        $result[] = array('name'=>'系统类型：',
            'value'=>'环保客户调价',
            'type'=>'string'
        );
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['dtime'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['date'])), 
                                     'type'=>'date'
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
        return $this->field(true)->where($map)->getField('jbr');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $temp = array(
            array('title' => '申请时间' , 'content' => date('Y-m-d',strtotime($res['date'])) ),
            array('title' => '调价日期' , 'content' => date('Y-m-d',strtotime($res['date']))  ),
            array('title' => '相关说明' , 'content' => '无' ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $this->transStat($res['id']),
            'applyerName'    => D('YxhbBoss')->getNameFromID($res['applyuser']),
        );
        return $result;
    }
    
}