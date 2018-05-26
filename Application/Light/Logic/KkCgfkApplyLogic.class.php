<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkCgfkApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_cgfksq';

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
        $clientname = M('kk_gys')->field('g_name')->where(array('id' => $res['gys']))->find();

        $color = '#f12e2e';
        
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>$res['zd_date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请单号：',
                                     'value'=>$res['dh'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'应付额度：',
                                     'value'=>100000,
                                     'type'=>'number',
                                     'color' => $color
                                    );
        $result['content'][] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['fkje'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请人员：',
                                     'value'=>$res['rdy'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>$res['notice'],
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIDFromName($res['rdy']);
        $result['applyerName'] = $res['rdy'];
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
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $clientname = M('kk_gys')->field('g_name')->where(array('id' => $res['gys']))->find();
        $result[] = array('name'=>'申请日期：',
                                     'value'=>$res['zd_date'],
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请单号：',
                                     'value'=>$res['dh'],
                                     'type'=>'string'
                                    );        
        $result[] = array('name'=>'应付余额：',
                                     'value'=>'1000',
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['fkje'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['rdy'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['zy'],
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
        return $this->field(true)->where($map)->getField('rdy');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $result = array(
            'sales'   => $res['rdy'],
            'approve' => number_format($res['fkje'],2,'.',',')."元",
            'notice'  => $res['zy'],
            'date'    => $res['zd_date'],
            'stat'    => $res['stat']
        );
        return $result;
    }
}