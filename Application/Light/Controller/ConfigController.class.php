<?php
namespace Light\Controller;

class ConfigController extends BaseController {

    public function Dispenser(){
        $robot = I('get.robot');
        switch($robot){
            case 'attention': 
                        $this->attentionPage(); 
                break;
            case 'attentionApi': 
                        $this->attentionApi(); 
                break;    
            case 'explain': 
                        $this->explainPage(); 
                break;
            case 'explainApi': 
                        $this->explainApi(); 
                break;  
            case 'fylx': 
                        $this->fylxPage(); 
                break;
            case 'fylxList': 
                        $this->fylxListPage(); 
                break;
            case 'fylxApi': 
                        $this->fylxApi(); 
                break; 
            case 'addgys': 
                    $this->addGysPage(); 
            break;
            case 'addgysApi': 
                    $this->addGysApi(); 
            break;
            case 'viewProTitlePage': 
                    $this->viewProTitlePage(); 
            break;
            case 'changeViewProTitle': 
                    $this->changeViewProTitle(); 
            break;
            case 'delViewPro': 
                    $this->delViewPro(); 
            break;
            case 'proConfig': 
                    $this->proConfigPage(); 
            break;
            case 'ConfigSubmit': 
                    $this->ConfigSubmit(); 
            break;
            case 'viewPushPage': 
                    $this->viewPushPage(); 
            break;
            case 'changePushTitle': 
                    $this->changePushTitle(); 
            break;
            case 'delPush': 
                    $this->delPush(); 
            break;
            case 'pushConfig': 
                    $this->pushConfigPage(); 
            break;
            case 'pushConfigSubmit': 
                    $this->pushConfigSubmit(); 
            break;
        }
    }
    
    /**
     * 注意事项配置
     */
    protected function attentionPage(){
        $system  = I('get.system');
        $modname = I('get.modname');
        $data = explode('_',$modname);
        $name = end($data) == 'config'?'操作说明':'注意事项';
        $data = $this->HasAttention($system,$modname);
        $this->assign('data',$data);
        $this->assign('name',$name);
        $this->display('Config/attention');
    }

    /**
     * 修改
     */
    protected function attentionApi(){
        $content = I('post.text');
        $system  = I('post.system');
        $modname = I('post.modname');
        $switch  = I('post.switch');
        $man     = session('name');
       
        $data = array(
            'mod'     => $modname,
            'system'  => $system,
            'man'     => $man,
            'content' => $content,
            'stat'    => $switch == 'on'?1:0
        );
        $hasAtten = $this->HasAttention($system,$modname);
        if( empty($hasAtten)){
            $res = M('yx_config_attention')->add($data);
        }else{
            $res = M('yx_config_attention')->where(array('id' => $hasAtten['id']))->save($data);
        }
        $retuenData = $res !== false ?array('code' => 200):array('code' => 404 ,'msg' => '插入失败'); 
        $this->ajaxReturn($retuenData);
    }

    /**
     * 获取是否有此配置
     * @param string $system
     * @param string $modname
     * @return array
     */
    public function HasAttention($system,$modname){
        $map = array(
            'system' => $system,
            'mod'    => $modname
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
    public function ActiveAttention($system,$modname){
        $map = array(
            'system' => $system,
            'mod'    => $modname,
            'stat'   => 1
        );
        $data = M('yx_config_attention')->where($map)->find();
        return $data;
    }
    // 注意事项 END==================================== 

    // 相关说明 ====================================
     /**
     * 注意事项配置
     */
    protected function explainPage(){
        $system  = I('get.system');
        $modname = I('get.modname');
        $data = $this->HasExplain($system,$modname);
        $this->assign('data',$data);
        $this->display('Config/explain');
    }

    /**
     * 获取是否有此配置
     * @param string $system
     * @param string $modname
     * @return array
     */
    public function HasExplain($system,$modname){
        $map = array(
            'system' => $system,
            'mod'    => $modname
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
        $data = M('yx_config_explain')->where($map)->find();
        return $data;
    }

    /**
     * 修改
     */
    protected function explainApi(){
        $content = I('post.text');
        $system  = I('post.system');
        $modname = I('post.modname');
        $switch  = I('post.switch');
        $mustfill= I('post.mustfill');
        $man     = session('name');
       
        $data = array(
            'mod'     => $modname,
            'system'  => $system,
            'man'     => $man,
            'content' => $content,
            'stat'    => $switch == 'on'?1:0,
            'mustfill'=> $mustfill == 'on'?1:0,
        );
        $hasAtten = $this->HasExplain($system,$modname);
        if( empty($hasAtten)){
            $res = M('yx_config_explain')->add($data);
        }else{
            $res = M('yx_config_explain')->where(array('id' => $hasAtten['id']))->save($data);
        }
        $retuenData = $res !== false ?array('code' => 200):array('code' => 404 ,'msg' => '插入失败'); 
        $this->ajaxReturn($retuenData);
    }
    // 相关说明 END====================================

    // 费用类型 STAR====================================
    // 费用类型列表
    protected function fylxListPage(){
        $system  = I('get.system');
        $data = $this->Hasfylx($system);
        $this->assign('system',$system);
        $this->assign('data',$data);
        $this->display('Config/fylxList');
    }
    // 费用类型修改
    protected function fylxPage(){
        $system = I('get.system');
        $id     = I('get.id');
        $type   = I('get.type');
        if(!empty($id)){
            $data   = $this->Hasfylx($system,$id);
            $data   = $data[0]; 
            $this->assign('data',$data);
        }
        $this->assign('type',$type);
        $this->display('Config/fylx');
    }

    /**
     * 获取是否有此配置
     * @param string $system
     * @param string $id
     * @return array
     */
    public function Hasfylx($system,$id){
        $map = array(
            'stat' => 1,
        );
        if(!empty($id)) $map['id'] = $id;
        $fylx = M("{$system}_fylx")->where($map)->select();
        return $fylx;
    }
    
    // 费用类型 删除、修改、增加
    public function fylxApi(){
        $type   = I('post.type');
        if(empty($type)){
            $this->changeFyle();
        }else{
            $this->addFylx();
        }
    }

    // 删除、修改
    public function changeFyle(){
        $system = I('post.system');
        $id     = I('post.id');
        if(empty($system) || empty($id)) $this->ajaxReturn(array('code' => 404 ,'msg' => '参数错误，请联系信息中心')); 
        
        $name   = I('post.name');
        $switch = I('post.switch');
        $data   = array(
            'fy_name' => $name,
            'stat' => $switch == 'off'?1:0,
        );
        $res = M($system.'_fylx')->where(array('id' => $id))->save($data);
        $retuenData = $res !== false ?array('code' => 200):array('code' => 404 ,'msg' => '插入失败'); 
        $this->ajaxReturn($retuenData);
    }

    // 增加费用类型
    public function addFylx(){
        $system = I('post.system');
        $name   = I('post.name');
        $map    = array(
            'fy_name' => $name,
        );
        $data   = M($system.'_fylx')->where($map)->find();
        if(!empty($data)){
            if($data['stat'] == 1) $this->ajaxReturn(array('code' => 404 ,'msg' => '已有费用类型重复')); 
            $res = M($system.'_fylx')->where($map)->setField('stat',1);
        }else {
            $res = M($system.'_fylx')->add($map);
        }
        $retuenData = $res !== false ?array('code' => 200):array('code' => 404 ,'msg' => '插入失败'); 
        $this->ajaxReturn($retuenData);
    }

    // 费用类型 END====================================
    // 添加供应商
    protected function addGysPage(){
        $system = I('get.system');
        $this->assign('system',$system);
        $this->display('Config/addgys');
    }

    public function addGys(){

    }
    // 流程可视化，标题设置
    protected function viewProTitlePage(){
        $system = I('get.system');
        $mod    = I('get.mod');
        $id     = I('get.id');
        $name   = array(
            'title' => '',
            'id'    => '',
        );
        if($id) $name = M('yx_config_viewpro')->where("`system`='$system' and `mod`='$mod' and `id`='$id' and `stat`=1")->find();
        $this->assign('title',$name['title']);
        $this->assign('mod',$mod);
        $this->assign('id',$name['id']);
        $this->assign('system',$system);
        $this->display('Config/viewProTitlePage');
    }

   

    // 修改标题
    public function changeViewProTitle(){
        $system = I('post.system');
        $mod    = I('post.mod');
        $id     = I('post.id');
        $title  = I('post.title');
        if(empty($system)||empty($mod)||empty($title)) $this->ajaxReturn(array('code' => 404 ,'msg' => '修改失败','data' => '参数错误'));
        $name = M('yx_config_viewpro')->where("`system`='$system' and `mod`='$mod' and `id`='$id' and `stat`=1")->find();
        $data = array(
            'system' => $system,
            'mod'    => $mod,
            'stat'   => 1,
            'datetime' => date('Y-m-d H:i:s'),
            'man'   => session('name'),
            'title' => $title,
        );
        if(empty($name)){
            $res = M('yx_config_viewpro')->add($data);
        }else{
            $res = M('yx_config_viewpro')->where("`id`='$id'")->save($data);
        }
        $retuenData = $res !== false ?array('code' => 200):array('code' => 404 ,'msg' => '插入失败'); 
        $this->ajaxReturn($retuenData);
    }
    // 删除对应的id
    public function delViewPro(){
        $id     = I('post.id');
        $system = I('post.system');
        if(empty($id)) $this->ajaxReturn(array('code' => 404 ,'msg' => '修改失败','data' => '参数错误'));
        $config = M('yx_config_viewpro')->where(array('id' => $id))->setField('stat', 0);
        $table  = M($system.'_appflowtable')->where(array('view_id' => $id))->setField('stat', 0);
        $this->ajaxReturn(array('code' => 200,'config' => $config,'table' => $table));
    }

    // 推送标题添加
    protected function viewPushPage(){
        $system = I('get.system');
        $mod    = I('get.mod');
        $id     = I('get.id');
        $aid     = I('get.aid');
        $name   = array(
            'title' => '',
            'id'    => '',
        );
        $map  = array(
            'pro_mod'   => $mod,
            'id'        => $id,
            'stat'      => 1,
        );
        if($id) $name = M($system.'_pushlist')->where($map)->find();
        $this->assign('title',$name['pro_name']);
        $this->assign('mod',$mod);
        $this->assign('aid',$aid);
        $this->assign('id',$name['id']);
        $this->assign('system',$system);
        $this->display('Config/pushTitle');
    }

     // 修改标题
     public function changePushTitle(){
        $system = I('post.system');
        $mod    = I('post.mod');
        $id     = I('post.id');
        $title  = I('post.title');
        if(empty($system)||empty($mod)||empty($title)) $this->ajaxReturn(array('code' => 404 ,'msg' => '修改失败','data' => '参数错误'));

        $map  = array(
            'pro_mod'   => $mod,
            'id'        => $id,
            'stat'      => 1,
        );
        $name = M($system.'_pushlist')->where($map)->find();
        $data = array(
            'pro_mod'       => $mod,                      //模块名
            'pro_name'      => $title,           //相关说明
            'stage_name'    => '推送',
            'date'          => date('Y-m-d H:i:s'),
            'condition'     => '',                        //条件
            'ranges'        => '',
            'type'          => 2,
            'stat'          => 1,
            'rule'          => '',
        );
        
        if(empty($name)){
            $res = M($system.'_pushlist')->add($data);
        }else{
            $res = M($system.'_pushlist')->where("`id`='$id'")->save($data);
        }
        $retuenData = $res !== false ?array('code' => 200):array('code' => 404 ,'msg' => '插入失败'); 
        $this->ajaxReturn($retuenData);
    }

    // 删除对应的id
    public function delPush(){
        $id     = I('post.id');
        $system = I('post.system');
        if(empty($id)) $this->ajaxReturn(array('code' => 404 ,'msg' => '修改失败','data' => '参数错误'));
        $config = M($system.'_pushlist')->where(array('id' => $id))->setField('stat', 0);
        $this->ajaxReturn(array('code' => 200,'config' => $config));
    }

    // 推送设置
    public function pushConfigPage(){
        $system = I('get.system');
        $mod    = I('get.mod');
        $id     = I('get.id');
        $aid     = I('get.aid');
        // 获取推送人员
        $arr = M($system.'_pushlist')->where(array('stat' => 1 , 'pro_mod' => $mod,'id' => $id))->field('id,pro_name,pro_mod,push_name')->select();
        $temp = array();
        $html = D('Html');
        $boss = D(ucfirst($system).'Boss');
        foreach($arr as $k => $v){
            $user = trim($v['push_name'],'"');
            $user = explode(',',$user);
            $user = array_filter($user);
            $userArr = array();
            foreach ($user as $val) {
                $vavtar = $boss->getAvatarFromWX($val);
                $userArr[] = array(
                     'wxid'   => $val,
                     'name'   => $boss->getNameFromWX($val),
                     'avatar' => $vavtar?$vavtar:'Public/assets/i/defaul.png',
                 );
            }
            $v['push_name'] = trim($v['push_name'],'"');
            $v['html'] = $html->fiexdCopyHtml($userArr);
            $temp = $v;
        }
        // 注意事项
        $detailModel = D('YxDetailAuth');
        $atten       = $detailModel->ActiveAttention($system,'Push_config');
        $this->assign('data',$temp);
        $this->assign('atten',$atten);
        $this->assign('id',$id);
        $this->assign('aid',$aid);
        $this->assign('modname',$mod);
        $this->assign('system',$system);
        $this->display('Config/PushConfig');
    } 

    // 推送设置接口
    public function pushConfigSubmit(){

    }

    // 流程设置
    public function proConfigPage(){
        $system = I('get.system');
        $mod    = I('get.mod');
        $id     = I('get.id');
        $aid     = I('get.aid');
        // 获取所有流程
        $data = D(ucfirst($system).'Appflowtable')->getProStep($id);
        // 固定抄送
        $copy = M('yx_config_viewpro')->where(array('id' => $id))->order('id desc')->find();
        $fiexd_copy = D(ucfirst($system).'Appcopyto')->getFiexdCopyHtml($copy['fiexd_copy_id']);
        $fiexd_copy['wxid'] = $copy['fiexd_copy_id'];
        // 权限人员
        $auth = D(ucfirst($system).'Appcopyto')->getFiexdCopyHtml($data[1]['auth_id']);
        // 其他同条件流程 人员
        $other_auth = D(ucfirst($system).'Appflowtable')->getOtherProBycondition($copy['condition'],$id,$mod);
        //dump($auth);
        // 注意事项
        $detailModel= D('YxDetailAuth');
        $atten      = $detailModel->ActiveAttention('kk','CostMoney_config');
        $this->assign('name',$copy['title']);
        $this->assign('copy',$fiexd_copy);
        $this->assign('data',$data);
        $this->assign('modname',$mod);
        $this->assign('auth',$auth);
        $this->assign('other_auth',$other_auth);
        $this->assign('atten',$atten);
        $this->assign('id',$id);
        $this->assign('aid',$aid);
        $this->assign('system',$system);
        $this->display('Config/proConfig');
    }

    // 流程设置提交
    public function ConfigSubmit()
    {
        $system  = I('post.system');
        $mod     = I('post.mod');
        $id      = I('post.id');

        $pro_arr = I('post.pro_arr');
        $copy_id = I('post.copy_id');
        $copy_id = explode(',',trim($copy_id,','));
        $copy_id = implode(',',$copy_id);
        $auth_id = I('post.auth_id');
        $auth = explode(',',trim($auth_id,','));
        $auth = implode(',',$auth);
        
        if(empty($auth)) $this->ajaxReturn(array('code' => 404,'msg' => '请选择申请人员'));
        if(empty($id)) $this->ajaxReturn(array('code' => 404,'msg' => '无效提交'));
        $table = D(ucfirst($system).'Appflowtable');
        $pro_id   = $table->getProIdByViewid($id);
        //$this->ajaxReturn(array('code' => 404,'msg' => '请选择申请人员','data' => $pro_id,$id,2));
        $view = M('yx_config_viewpro')->where(array('id' => $id))->find();
        M('yx_config_viewpro')->where(array('id' => $id))->setField('fiexd_copy_id', $copy_id);
        M($system.'_appflowtable')->where(array('view_id' => $id))->setField('stat', 0);
        $flow  = array();
        $data  = M('yx_config_title')->where(array('mod_system' => $system,'name' =>$mod))->find();
        
        $boss  = D(ucfirst($system).'Boss');
        $role_id  = 1;
        $last_key = end(array_keys($pro_arr));
        // 权限人员
        
        foreach($pro_arr as $k => $v){
            $wx = explode(',',trim($v,','));
            $wx = array_filter($wx);
            $stage_name = count($wx)>1?'会审':($k == $last_key?'审批':'审核');
            $stage_next = $k == $last_key?0:$k+2;

            foreach($wx as $val){
                // 0 签收 1 免签
                $sign = 0;
                if($mod == 'CostMoney' && $stage_next == 0) $sign = 1;
                $flow[] = array(
                    'pro_name'   => $data['mod_show_name'],
                    'pro_mod'    => $mod,
                    'view_id'    => $id,
                    'pro_id'     => $pro_id,
                    'per_name'   => $boss->getNameFromWX($val),
                    'per_id'     => $boss->getIDFromWX($val),
                    'role_id'    => $role_id,
                    'stage_id'   => $k+1,
                    'stage_name' => $stage_name,
                    'stage_next' => $stage_next,
                    'date'       => date('Y-m-d H:i:s'),
                    'condition'  => $view['condition']?$view['condition']:'',
                    'stat'       => 1,
                    'type'       => 0,
                    'rank'       => 0,
                    'auth_id'    => $auth,
                    'sign'       => $sign,
                );
                $role_id++;
            } 
        }
        $res = M($system.'_appflowtable')->addAll($flow);
        // 权限关联
        $user = D('YxDetailAuth')->getAuthWxid($system,$mod);
        $group = D('YxDetailAuth')->getAuthGroup($system,$mod);
        $auth = explode(',',$auth);
        $access = array();
        foreach($auth as $v){
            if(in_array($v,$user)) continue;
            foreach($group as $val){
                $access[] = array(
                    'uid' => $v,
                    'group_id' => $val['id'],
                );
            }
        }
        if(!empty($access)) M('auth_group_access')->addAll($access);
        $this->ajaxReturn(array('code' => $res?200:404,'msg' => '测试','table' => $flow));
    }
}