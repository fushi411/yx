<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbSalesRebatesApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_salesrebates';

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
                                     'value'=>'环保',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>D('yxhb_guest2')->getName($res['clientid']),
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'起始日期：',
                                     'value'=>$res['stday'],
                                     'type'=>'date'
                                    );
        $result['content'][] = array('name'=>'结束日期：',
                                     'value'=>$res['enday'],
                                     'type'=>'date'
                                    );
        $result['content'][] = array('name'=>'销&nbsp;&nbsp;售&nbsp;&nbsp;员：',
                                     'value'=>D('yxhb_boss')->getuserName($res['applyuser']),
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'返利类型：',
                                     'value'=>$res['type'],
                                     'type'=>'string'
                                    );
        $result['content'][] = array('name'=>'品&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;种：',
                                     'value'=>$res['cate'],
                                     'type'=>'string'
                                    );
        // $result['content'][] = array('name'=>'销&nbsp;&nbsp;售&nbsp;&nbsp;员：',
        //                              'value'=>$res['sales'],
        //                              'type'=>'string'
        //                             );
        // $result['content'][] = array('name'=>'备注：',
        //                              'value'=>$res['notice'],
        //                              'type'=>'text'
        //                             );
        $result['imgsrc'] = '';
        $result['applyerID'] = $res['applyuser'];
        $result['applyerName'] = D('yxhb_boss')->getusername($res['applyuser']);
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
        // $map = array('id' => $id);
        // return $this->field(true)->where($map)->setField('stat',0);
    }

    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        // $map = array('id' => $id);
        // return $this->field(true)->where($map)->getField('jbr');
    }
    
}