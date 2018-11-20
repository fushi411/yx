<?php
namespace Light\Controller;

class TaskController extends \Think\Controller {
   
    private $titleArr;

    public function __construct(){
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");

        $this->titleArr = array(
            array('title' => '我的代办','url' => U('Light/Task/commission'),'on' => '' , 'logo' => 'icon-biaoqian'),
            array('title' => '任务查看','url' => U('Light/Task/look'),      'on' => '' , 'logo' => 'icon-wendang'),
            array('title' => '配置中心','url' => U('Light/Task/config'),    'on' => '' , 'logo' => 'icon-shezhi'),
        );
    }
    // 我的代办
   public function commission(){
       $this->titleArr[0]['on'] = 'weui-bar__item_on';
       $this->assign('bottom',$this->titleArr);
       $this->display("Task/commission");
   }
   // 项目查看
   public function look(){
        $this->titleArr[1]['on'] = 'weui-bar__item_on';
        $this->assign('bottom',$this->titleArr);
        $this->display("Task/look");
    }

    // 配置中心
    public function config(){
        $this->titleArr[2]['on'] = 'weui-bar__item_on';
        $this->assign('bottom',$this->titleArr);
        $this->display("Task/config");
    }

    // 代办创建
    public function makeup(){
        $this->display("Task/task");
    }


    // 选择框数据获取
    public function getMod(){
        $map = array(
            'reid' => 0,
            'stat' => 1
        );
        $data = M('yx_task_mod')->where($map)->select();
        $this->ajaxReturn(array('code' => 200,'data' => $data));
    }
    // 获取2级及3级
    public function getTwoData(){
        $reid = I('post.mod');
        $map = array(
            'stat' => 1
        );
        $data = M('yx_task_mod')->where($map)->select();
        $two = array();
        $three = array();
        $temp = array();
        foreach($data as $k => $v){
            if($v['reid'] != $reid) continue;
            $two[] = $v;
            $temp[] = $v['id'];
        }

        foreach($data as $k => $v){
            if( !in_array($v['reid'],$temp) ) continue;
            $three[] = $v;
        }

        $this->ajaxReturn(array('code' => 200,'data' => array($two,$three)));
    }

    // 草稿提交
    public function draft(){
        $mod     = I('post.mod');
        $modTwo  = I('post.modTwo');
        $modThree= I('post.modThree');
        $func    = I('post.func');
        $title   = I('post.title');
        $content = I('post.content');
        $user    = I('post.user');
        $user    = trim($user,',');

        if( !$title && !$content){
            $this->ajaxReturn(array('code' => 404,'msg' => '标题和内容不能为空'));
        } 
        if(!M('yx_task')->autoCheckToken($_POST)) $this->ajaxReturn(array('code' => 404,'msg' => '请勿点击草稿按钮'));
        //  保存草稿
        $save = array(
            'mod'        => $mod,
            'modtwo'     => $modTwo,
            'modthree'   => $modThree,
            'function'   => $func,
            'title'      => $title,
            'content'    => $content,
            'part'       => $user,
            'stat'       => 1,
            'createtime' => date('Y-m-d H:i:s',time()),
            'submittime' => date('Y-m-d H:i:s',time()),
            'tjr'        => '黄时起'
        );
        $res = M('yx_task')->add($save);
        if($res) $this->ajaxReturn(array('code' => 200,'msg' => ''));
        $this->ajaxReturn(array('code' => 404,'msg' => '保存草稿失败'));
    }

    // 正式提交
    public function func_to_build(){
        $mod     = I('post.mod');
        $modTwo  = I('post.modTwo');
        $modThree= I('post.modThree');
        $func    = I('post.func');
        $title   = I('post.title');
        $content = I('post.content');
        $user    = I('post.user');
        $user    = trim($user,',');

        if( !$mod ||!$modTwo ||!$modThree ||!$user || !$title || !$content){
            $this->ajaxReturn(array('code' => 404,'msg' => '请认真检查填入内容'));
        } 
        if(!M('yx_task')->autoCheckToken($_POST)) $this->ajaxReturn(array('code' => 404,'msg' => '请勿点击草稿按钮'));
        //  保存草稿
        $save = array(
            'mod'        => $mod,
            'modtwo'     => $modTwo,
            'modthree'   => $modThree,
            'function'   => $func,
            'title'      => $title,
            'content'    => $content,
            'part'       => $user,
            'stat'       => 2,
            'createtime' => date('Y-m-d H:i:s',time()),
            'submittime' => date('Y-m-d H:i:s',time()),
            'tjr'        => '黄时起'
        );
        $res = M('yx_task')->add($save);
        if($res) $this->ajaxReturn(array('code' => 200,'msg' => ''));
        $this->ajaxReturn(array('code' => 404,'msg' => '保存草稿失败'));
    }
} 


