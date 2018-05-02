<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbforTestLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_tempcreditlineconfig';

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
        // $res = $this->record($id);
        $result = array();
        $result['content'][] = array('name'=>'执行日期：',
                                     'value'=>'2018-02-27',
                                     'type'=>'date'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>'ABC',
                                     'type'=>'string'
                                    );

        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>'噢噢噢噢',
                                     'type'=>'text'
                                    );
        $result['imgsrc'] = '';
        $result['applyerID'] = '10';
        $result['applyerName'] = '魏锴';
        $result['stat'] = 1;
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

     /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $clientname = M('yxhb_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $result[] = array('name'=>'申请日期：',
                                     'value'=>$res['date'],
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_khjc'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'客户余额：',
                                     'value'=>number_format($res['ye'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'已有临额：',
                                     'value'=>number_format($res['ed'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['line'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申&nbsp;&nbsp;请&nbsp;&nbsp;人：',
                                     'value'=>$res['sales'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['notice'],
                                     'type'=>'text'
                                    );
        return $result;
    }
}