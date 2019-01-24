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
            case 'fylxApi': 
                        $this->fylxApi(); 
                break;  
        }
    }


    // 注意事项 ====================================
    /**
     * 注意事项配置
     */
    protected function attentionPage(){
        $system  = I('get.system');
        $modname = I('get.modname');
        $data = $this->HasAttention($system,$modname);
        $this->assign('data',$data);
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
    protected function fylxPage(){
        $system  = I('get.system');
        $data = $this->Hasfylx($system);
        $this->assign('data',$data);
        $this->display('Config/fylx');
    }

    /**
     * 获取是否有此配置
     * @param string $system
     * @param string $modname
     * @return array
     */
    public function Hasfylx($system){
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

    // 费用类型 END====================================
}