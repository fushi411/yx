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

    /**
     *  设置注意事项以及相关说明 校验设置权限
     *  @return boolean
     */
    public function CueAuthCheck(){
        $wxid = session('wxid');
        $auth = $this->GetDetaiAuth();
        return in_array($wxid,$auth)?true:false;
    }


    /**
     * 获取全部权限人员
     * @return array $authArray;
     */
    public function GetDetaiAuth(){
        $map  = array(
            'mod'  => 'all',
            'type' => 1,
            'stat' => 1,
        ); 
        $data = $this->field('wxid')->where($map)->select();
        $authArray = array();
        foreach( $data as $v){
            $authArray[] = $v['wxid'];
        }
        return $authArray;
    }

        /**
     * 获取是有效配置
     * @param string $system
     * @param string $modname
     * @return array
     */
    public function ActiveAttention($system,$modname){
        $map = array(
            'system' => $system,
            'mod'    => $modname,
            'stat'   => 1
        );
        $data = M('yx_config_attention')->where($map)->find();
        return $data;
    }
    
}