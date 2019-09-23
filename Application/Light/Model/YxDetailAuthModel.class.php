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

    /**
     * 获取是有效配置
     * @param string $system
     * @param string $modname
     * @return array
     */
    public function ActiveExplain($system,$modname){
        $map = array(
            'system' => $system,
            'mod'    => $modname,
            'stat'   => 1
        );
        $explain = M('yx_config_explain')->where($map)->find();
        if(empty($explain)) return $explain;
        $data    = explode('&lt;br&gt;',$explain['content']);
        $data    = array_filter($data);
        $row     = count($data);
        $explain['row']     = $row>=3?$row:3;
        $explain['content'] = implode("\n",$data);
        return $explain;
         
    }
    

    // auth权限
    public function authGetBmId($system){
        if(empty($system)) return false;
        $wxid   = session('wxid');
        $data   = M('yx_bm_access')->where(array('wx_id' => $wxid,'main' => 1))->find();
        return $data['bm_id'];
    }

    // 获取所有的权限
    public function getAuthWxid($system,$type){
        $reArr = array();
        $auth_group = $this->getAuthGroup($system,$type);
        if(empty($auth_group)) return $reArr;  // ---无部门
        # 权限人员获取
        $groupStr   = '';
        $where      = '';
        $leaguerStr = ''; 
        foreach ($auth_group as $key => $value) {
            if($key != 0) $where.=' or ';
            $where .= 'group_id = '.$value['id']; 
        }
        $leaguer = M('auth_group_access a')
                    ->field('b.name,a.uid')
                    ->join($system.'_boss b on a.uid=b.wxid')
                    ->where($where)
                    ->group('a.uid')
                    ->select();
        if(empty($leaguer))return $reArr;
        $user = array();
        foreach($leaguer as $k=>$v){
            $leaguerStr .= $v['name'].' ';
            $user[] = $v['uid'];
        }
        return $user;
    }

    // 获取权限所在功能组
    public function getAuthGroup($system,$type){
        $reArr = array();
        $map = array(
            'title' => array('like',$system.$type.'|%'),
            'status' => 1,
        );
        $res = M('auth_rule')->field('id')->where($map)->find();
        if(empty($res)) return $reArr; // ---都无权限
        # 拥有权限分组
        $map = array(
            'rules'  => array('like',"%{$res['id']}%"),
            'status' => 1,
            'id'     => array('neq','2'),
        );
        $group = M('auth_group')->field('id,title,rules')->where($map)->select();
        $auth_group = array(); // 是否在这个权限组中
        foreach($group as  $v){
            $temp = explode(',',$v['rules']);
            if(in_array($res['id'],$temp) && $v['id'] !='7' ) $auth_group[] = $v; 
        }
        return $auth_group;
    }

    // 更新申请人员
    public function updateAuth(){
        $data = M('yx_config_title')->where('stat=1 or stat=2')->select();
        $res  = M('auth_group')->where('id>=18')->select();
        $auth = array(); 
        foreach($data as $val){
            foreach($res as $v){
                if($val['mod_title'] != $v['title'])continue;
                $uid = M('auth_group_access')->where(array('group_id' => $v['id']))->select();
                $auth_id = '';
                foreach($uid as $idval){
                    $auth_id .= ','.$idval['uid'];
                }
                $flag = M($val['mod_system'].'_appflowtable')->where(array('pro_mod' => $val['name'],'stat' => 1))->setField('auth_id',trim($auth_id,','));
                $auth[] = array(
                    'system' => $val['mod_system'],
                    'mod' => $val['name'],
                    'group' => $v['id'],
                    'auth' => trim($auth_id,','),
                );
            }
        } 
        return $auth;
    }

    /**
     * 获取最后审批权限人员 ---seek
     */
    public function getBatchAuth($mod){
        if(!is_array($mod)) return array();
        $pro = array();
        foreach($mod as $v){
            $pro[] = array('eq',$v);
        }
        $pro[] = 'OR'; 
        $map = array(
            'pro_mod' => $pro,
            'stat'    => 1,
            'stage_next' => 0,
        );
        $union = array(
            'table' => 'yxhb_appflowtable',
            'where' => $map,
            'field' => 'per_name',
        );
        $query = M('kk_appflowtable')
                ->union($union)
                ->field('per_name')
                ->where($map)
                ->select(false);
        $data = M()->query("select * from ({$query})t GROUP BY per_name");
        $res = array();
        foreach($data as $v){
            $res[] = $v['per_name'];
        }
        return $res;
    }
}