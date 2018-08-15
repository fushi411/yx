<?php
namespace Light\Model;
use Think\Model;

/**
 * 页面数据逻辑模型
 * @author 
 */

class SeekModel extends Model {
    // 虚拟模型
    protected $autoCheckFields = false;

    public function getAppTable(){
        $appArr = array(
            array(
                'title'      => '环保临时额度' , 
                'search'     => '临时额度',
                'system'     => 'yxhb' ,
                'mod_name'   => 'TempCreditLineApply',
                'table_name' => 'yxhb_tempcreditlineconfig',
                'id'         => 'id' ,
                'submit'     => array('name' => 'sales','stat' => 2),
                'copy_field' => 'yxhb_tempcreditlineconfig.id as aid,yxhb_tempcreditlineconfig.sales as applyer,yxhb_tempcreditlineconfig.date,yxhb_tempcreditlineconfig.line as approve,yxhb_tempcreditlineconfig.notice,yxhb_tempcreditlineconfig.stat'
            ),
            array( 
                'title'      => '环保信用额度' , 
                'search'     => '信用额度',
                'system'     => 'yxhb' ,
                'mod_name'   => 'CreditLineApply'    ,
                'table_name' => 'yxhb_creditlineconfig'    ,
                'id'         => 'aid',
                'submit'     => array('name' => 'sales','stat' => 2),
                'copy_field' => 'yxhb_creditlineconfig.aid,yxhb_creditlineconfig.sales as applyer,yxhb_creditlineconfig.date,yxhb_creditlineconfig.line as approve,yxhb_creditlineconfig.notice,yxhb_creditlineconfig.stat'
            ),
            array( 
                'title'      => '环保采购付款' , 
                'search'     => '采购付款',
                'system'     => 'yxhb' ,
                'mod_name'   => 'CgfkApply'          ,
                'table_name' => 'yxhb_cgfksq'              ,
                'id'         => 'id',
                'submit'     => array('name' => 'rdy','stat' => 3),
                'copy_field' => 'yxhb_cgfksq.id as aid,yxhb_cgfksq.rdy as applyer,yxhb_cgfksq.zd_date as date,yxhb_cgfksq.fkje as approve,yxhb_cgfksq.zy as notice,yxhb_cgfksq.stat'
            ),
            array( 
                'title'      => '建材临时额度' , 
                'search'     => '临时额度',
                'system'     => 'kk'   ,
                'mod_name'   => 'TempCreditLineApply',
                'table_name' => 'kk_tempcreditlineconfig'  ,
                'id'         => 'id' ,
                'submit'     => array('name' => 'sales','stat' => 2),
                'copy_field' => 'kk_tempcreditlineconfig.id as aid,kk_tempcreditlineconfig.sales as applyer,kk_tempcreditlineconfig.date,kk_tempcreditlineconfig.line as approve,kk_tempcreditlineconfig.notice,kk_tempcreditlineconfig.stat'
            ),
            array( 
                'title'      => '建材信用额度' , 
                'search'     => '信用额度',
                'system'     => 'kk'   ,
                'mod_name'   => 'CreditLineApply'    ,
                'table_name' => 'kk_creditlineconfig'      ,
                'id'         => 'aid',
                'submit'     => array('name' => 'sales','stat' => 2),
                'copy_field' => 'kk_creditlineconfig.aid,kk_creditlineconfig.sales as applyer,kk_creditlineconfig.date,kk_creditlineconfig.line as approve,kk_creditlineconfig.notice,kk_creditlineconfig.stat'
            ),
            array( 
                'title'      => '建材采购付款' , 
                'search'     => '采购付款',
                'system'     => 'kk'   ,
                'mod_name'   => 'CgfkApply'          ,
                'table_name' => 'kk_cgfksq'                ,
                'id'         => 'id' ,
                'submit'     => array('name' => 'rdy','stat' => 3),
                'copy_field' => 'kk_cgfksq.id as aid,kk_cgfksq.rdy as applyer,kk_cgfksq.zd_date,kk_cgfksq.fkje as approve,kk_cgfksq.zy  as notice,kk_cgfksq.stat'
            ),
        );
        return $appArr;
    }

    public function configSign(){
        return array(
            array( 
                'title'      => '水泥配比通知' , 
                'search'     => '水泥配比通知',
                'system'     => 'kk' ,
                'mod_name'   => 'SnRatioApply'    ,
                'table_name' => 'kk_zlddtz'    ,
                'id'         => 'id',
                'stat'       => 'STAT',
                'submit'     => array('name' => 'zby','stat' => 2),
                'copy_field' => 'kk_zlddtz.id as aid, kk_zlddtz.jlsj as date,kk_zlddtz.STAT as state ,kk_zlddtz.zby as applyer'
            ),
            array( 
                'title'      => '矿粉配比通知' , 
                'search'     => '矿粉配比通知',
                'system'     => 'yxhb' ,
                'mod_name'   => 'KfRatioApply'    ,
                'table_name' => 'yxhb_assay'    ,
                'id'         => 'id',
                'stat'       => 'state',
                'submit'     => array('name' => 'name','stat' => 2),
                'copy_field' => 'yxhb_assay.id as aid, yxhb_assay.cretime as date,yxhb_assay.state as state ,yxhb_assay.name as applyer'
            ),
            array( 
                'title'      => '复合粉配比通知' , 
                'search'     => '复合粉配比通知',
                'system'     => 'kk' ,
                'mod_name'   => 'FhfRatioApply'    ,
                'table_name' => 'kk_zlddtz_gzf'    ,
                'id'         => 'id',
                'stat'       => 'STAT',
                'submit'     => array('name' => 'zby','stat' => 2),
                'copy_field' => 'kk_zlddtz_gzf.id as aid, kk_zlddtz_gzf.jlsj as date,kk_zlddtz_gzf.STAT as state ,kk_zlddtz_gzf.zby as applyer'
            ),
            array( 
                'title'      => '环保量库库存' , 
                'search'     => '环保量库库存',
                'system'     => 'yxhb' ,
                'mod_name'   => 'LkStockApply'    ,
                'table_name' => 'yxhb_produce_stock'    ,
                'id'         => 'id',
                'stat'       => 'stat',
                'submit'     => array('name' => 'rdy','stat' => 2),
                'copy_field' => 'yxhb_produce_stock.id as aid, yxhb_produce_stock.cretime as date,yxhb_produce_stock.stat as state ,yxhb_produce_stock.rdy as applyer'
            ),
        );
      
    }
}