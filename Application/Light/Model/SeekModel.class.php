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
    /**
     * 审批数组
     */
    public function getAppTable(){
        $data = M('yx_config_title')->where(array('stat' => 1))->select();
        $result = array();
        foreach($data as $val){
            $field = "{$val['mod_table']}.{$val['mod_field_aid']}     as aid,
                      {$val['mod_table']}.{$val['mod_field_appler']}  as applyer,
                      {$val['mod_table']}.{$val['mod_field_date']}    as date,
                      {$val['mod_table']}.{$val['mod_field_approve']} as approve, 
                      {$val['mod_table']}.{$val['mod_field_notice']}  as notice,
                      {$val['mod_table']}.{$val['mod_field_stat']}    as stat";
            $temp = array(
                'title'      => $val['mod_title']     ,  
                'search'     => $val['mod_search']    , 
                'toptitle'   => $val['mod_title'] , 
                'system'     => $val['mod_system']    , 
                'mod_name'   => $val['name']          , 
                'table_name' => $val['mod_table']     , 
                'id'         => $val['mod_id']        , 
                'stat'       => $val['mod_stat']      , 
                'submit'     => array('name' => $val['mod_name'] ,'stat' => (int)($val['mod_stat_value'] ) ),
                'copy_field' => $field                ,
            );
            if($val['map']){
                $temp['map'] = $val['map'];
            }
            $result[] = $temp;
        }
        return $result;
    }
    /**
     * 审批 同表操作
     * @param array $data 没用
     * @param string $cost null-全选,1-排除费用开支，2-只有费用开支
     */
    public function arrayMerge($data,$cost){
        $map  = array('stat' => array(1,2,'or'));
        if($cost == 1)$map['name'] = array('neq','CostMoney');
        if($cost == 2)$map['name'] = array('eq','CostMoney');
        $data = M('yx_config_title')->where($map)->select();
        $result = array();
        foreach($data as $val){
            $field = "{$val['mod_table']}.{$val['mod_field_aid']}     as aid,
                      {$val['mod_table']}.{$val['mod_field_appler']}  as applyer,
                      {$val['mod_table']}.{$val['mod_field_date']}    as date,
                      {$val['mod_table']}.{$val['mod_field_approve']} as approve,
                      {$val['mod_table']}.{$val['mod_field_notice']}  as notice,
                      {$val['mod_table']}.{$val['mod_field_stat']}    as stat";
            $temp = array(
                'title'      => $val['mod_title']     , 
                'search'     => $val['mod_search']    , 
                'toptitle'   => $val['mod_title'] , 
                'system'     => $val['mod_system']    , 
                'mod_name'   => $val['name']          , 
                'table_name' => $val['mod_table']     , 
                'id'         => $val['mod_id']        , 
                'stat'       => $val['mod_stat']      , 
                'submit'     => array('name' => $val['mod_name'] ,'stat' => (int)($val['mod_stat_value'] ) ),
                'copy_field' => $field                ,
            );
            if($val['map']){
                $temp['map'] = $val['map'];
            }
            $result[] = $temp;
        }
        return $result;
    }

    
    //  签收数组
    public function configSign(){
        $data = M('yx_config_title')->where(array('stat' => 3))->select();
        $result = array();
        foreach($data as $val){
            $field = "{$val['mod_table']}.{$val['mod_field_aid']} as aid,{$val['mod_table']}.{$val['mod_field_appler']} as date,{$val['mod_table']}.{$val['mod_field_date']} as state,{$val['mod_table']}.{$val['mod_field_approve']} as applyer";
            $temp = array(
                'title'      => $val['mod_title'] , 
                'search'     => $val['mod_search'] , 
                'toptitle'   => $val['mod_title'] , 
                'system'     => $val['mod_system'] , 
                'mod_name'   => $val['name'] , 
                'table_name' => $val['mod_table'] , 
                'id'         => $val['mod_id'] , 
                'stat'       => $val['mod_stat'] , 
                'submit'     => array('name' => $val['mod_name'] ,'stat' => (int)($val['mod_stat_value'] ) ),
                'copy_field' => $field
            );
            if($val['map']){
                $temp['map'] = $val['map'];
            }
            $result[] = $temp;
        }
        return $result;
    }
    /**
     * 不带系统 模块名
     * @param  string $mod 模块名
     * @param  string $sy  系统
     * @return string $title 标题
     */
    public function getTitle($mod,$sy){
        $map = array(
            'mod_system' => $sy,
            'name'       => $mod,
            'stat'       => array('neq',0),
        );
        $data = M('yx_config_title')->field('mod_show_name')->where($map)->find();
        $title = $data['mod_show_name'];
        return $title;
    }

    /**
     * 带系统 模块名
     * @param  string $mod 模块名
     * @param  string $sy  系统
     * @return string $title 标题
     */
    public function getModname($mod,$sy){
        $map = array(
            'mod_system' => $sy,
            'name'       => $mod,
        );
        $data = M('yx_config_title')->field('mod_title')->where($map)->find();
        $title = $data['mod_title'];
        return $title;
    }

    /**
     * 带系统 模块名
     * @param  string $mod 模块名
     * @param  string $sy  系统
     * @return string $title 标题
     */
    public function getConfig($mod,$sy,$column){
        $map = array(
            'mod_system' => $sy,
            'name'       => $mod,
        );
        $data = M('yx_config_title')->field($column)->where($map)->find();
        $res = $data[$column];
        return $res;
    }
}