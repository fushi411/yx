<?php
namespace Light\Model;
use Think\Model;
/**
 * 客户信息
 */

class YxDetailAuthModel extends Model
{
    /**
     * 获取模块详情查看权限
     * @param string $mod 模块名
     */
    public function getAuthArray($mod){
        if(!$mod) return array();
        $field = 'wxid,type';
        $map = array(
            'mod'  => array(array('eq','all'),array('eq',$mod),'or'),
            'stat' => 1
        );
        $data = $this->field($field)->where($map)->select();
        $look    = array();
        $delete  = array();
        foreach( $data as $k => $v){
             if( $v['type'] == 1)$res[] = $v['wxid'];
             if( $v['type'] == 2)$delete[] = $v['wxid'];
        }
        $res    = array_unique($res);
        $delete = array_unique($delete);
        $flag = 2;
        if(empty($delete) ){
            $flag = 2;
        }else{
            if(in_array(session('wxid'),$delete)) $flag = 1;
        }
        return array($res,$flag);
    }   

    /**
     * 获取模块详情查看权限
     * @param string $mod 模块名
     */
    public function getAuthArrayForTest($mod){
        if(!$mod) return array();
        $field = 'wxid,type';
        $map = array(
            'mod'  => array(array('eq','all'),array('eq',$mod),'or'),
            'stat' => 1
        );
        $data = $this->field($field)->where($map)->select();
        $look    = array();
        $delete  = array();
        foreach( $data as $k => $v){
             if( $v['type'] == 1)$res[] = $v['wxid'];
             if( $v['type'] == 2)$delete[] = $v['wxid'];
        }
        $res    = array_unique($res);
        $delete = array_unique($delete);
        $flag = 2;
        if(empty($delete) && in_array(session('wxid',$delete))){
            $flag = 1;
        }
        return array($res,$flag);
    }   
}