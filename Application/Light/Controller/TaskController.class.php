<?php
namespace Light\Controller;

class TaskController extends \Think\Controller {
   
    private $titleArr;
    private $urlHead = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx133a00915c785dec&redirect_uri=http%3a%2f%2fwww.fjyuanxin.top';

    private $urlEnd = '&response_type=code&scope=snsapi_base&state=YUANXIN#wechat_redirect';
    public function _initialize(){
        define('WXID',is_login());
        if( !WXID ){
        $url = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        $realURL = __SELF__;
        $redirectURL = $this->urlHead.urlencode($realURL).$this->urlEnd;
        // 微信登录判断
        $state = I('get.state');
        $system = I('get.system');
        A('Base')->pc_to_web_login();
        // 是否来自微信
        if ($state == 'YUANXIN') {
            $User = D($system.'Boss');
            if($User->loginWX()){
                // $this->redirect('WeChat/Index/index');
                $this->redirect($realURL);
            } else {
                $this->error('用户不存在或已被禁用！', $redirectURL, 5 );
            }
        }
        // 还没登录 跳转到登录页面
        // $this->error ( '登录过期，自动重新登录！', $redirectURL, 0 );
            redirect($redirectURL);
        }
    }

    public function __construct(){
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
        $this->titleArr = array(
            array('title' => '我的代办','url' => U('Light/Task/commission'),'on' => '' , 'logo' => 'icon-biaoqian'),
            array('title' => '任务查看','url' => U('Light/Task/look'),      'on' => '' , 'logo' => 'icon-wendang'),
            array('title' => '配置中心','url' => U('Light/Task/config'),    'on' => '' , 'logo' => 'icon-shezhi'),
        );

    }
    // 桌面式菜单
    public function desktopMenu(){
        $this->display("Task/desktopMenu");
    }

    // 我的代办
   public function commission(){
       $this->titleArr[0]['on'] = 'weui-bar__item_on';
       $this->assign('bottom',$this->titleArr);
       $this->display("Task/commission");
   }
   // 已完成事项
   public function over(){
        $this->titleArr[0]['on'] = 'weui-bar__item_on';
        $this->assign('bottom',$this->titleArr);
        $this->display("Task/over");
   }

   // 草稿
   public function draftPage(){
    $this->titleArr[0]['on'] = 'weui-bar__item_on';
    $this->assign('bottom',$this->titleArr);
    $this->display("Task/draftPage");
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
        $name = session('name');
        $map = array(
            'name' => $name,
            'stat' => 1
        );
        $avatar = M('wx_info')->where($map)->find();
        if($avatar['avatar']) $avatar['avatar'] = str_replace('http:','',$avatar['avatar']);
        $head   = $avatar['avatar'] ?$avatar['avatar']:'Public/assets/i/defaul.png';
        $this->assign('avatar',$head);
        $this->assign('name',$name);
        $this->assign('bottom',$this->titleArr);
        $this->display("Task/config");
    }
    // 功能定位配置
    public function configOfAction(){
        $this->titleArr[2]['on'] = 'weui-bar__item_on';
        $data = M('yx_task_fuc')->where(array('stat' => 1))->select();
        $this->assign('action',$data);
        $this->assign('bottom',$this->titleArr);
        $this->display("Task/config_action");
    }
    // 修改草稿
    public function change()
    {
        $id = I('get.taskid');
        // 无任务ID 调整返回
        if(!$id) $this->redirect('Light/Task/draftPage');
        $data = M('yx_task')->where(array('id' => $id))->find();
        $fixed = $this->getPartInfo($data['part']);
        $this->assign('data',$data);
        $this->assign('fixed',$fixed);
        $this->display("Task/change");
    }

    // 待处理信息 已完成信息查看
    public function taskLook(){
        $id = I('get.taskid');
        // 无任务ID 调整返回
        if(!$id) $this->redirect('Light/Task/commission');
        $mod_name = 'task';
        $system = 'kk';
        $data = M('yx_task')->where(array('id' => $id))->find();
        $fixed = $this->getPartInfo($data['part']);
        // 评论标记为已读
        D($system.'Appflowcomment')->readCommentApply($mod_name, $id);
        $comment_list = D($system.'Appflowcomment')->contentComment($mod_name, $id);
        $this->assign('comment_list', $comment_list);
        
        $this->assign('data',$data);
        $this->assign('fixed',$fixed);
        $this->display("Task/taskLook");
    }

    // 获取参与者信息
    public function getPartInfo($part){
        $partArr = explode(',',$part);
        $partArr = array_filter($partArr);
        $res = array();
        $str = '';
        foreach($partArr as $v){
            $map = array(
                'stat' => 1,
                'wxid' => $v
            );
            $data = M('wx_info')->where($map)->find();
            $temp = array(
                'url'  => $data['avatar'],
                'name' => $data['name']
            );
            $res[] = $temp;
            $str .= $v.',';
        }
        return array(
            'copydata' => $res,
            'fiexd_copy_id' => $str
        );
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
        $data2 = M('yx_task_fuc')->where($map)->select();
        $this->ajaxReturn(array('code' => 200,'data' => array($data,$data2)));
    }

    // 获取2级及3级
    public function getTwoData(){
        $reid = I('post.mod');
        $sql = 'select name,id from kk_menu where id in(select level from kk_menu where reid='.$reid.' group by level) ORDER BY level';
        $data = M()->query($sql);
        $two = M('kk_menu')->field('id,name,level')->where(array('reid' => $reid))->select();
        $three = array();
        $temp = array();       
        foreach($two as $v){
            $three[$v['level']][] = $v;
        }
        $this->ajaxReturn(array('code' => 200,'data' => array($data,$three)));
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
            'tjr'        => session('name')
        );
        $res = M('yx_task')->add($save);
        if($res) $this->ajaxReturn(array('code' => 200,'msg' => ''));
        $this->ajaxReturn(array('code' => 404,'msg' => '保存草稿失败'));
    }

    // 草稿修改
    public function draftSave(){
        $id      = I('post.id');
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
        );
        $res = M('yx_task')->where(array('id' => $id))->save($save);
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

        if( !$user || !$title || !$content){
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
            'tjr'        => session('name')
        );
        $res = M('yx_task')->add($save);
        
        if($res){
            D('WxMessage','Model')->taskSendMessage($res);
            $this->ajaxReturn(array('code' => 200,'msg' => ''));
        } 
        $this->ajaxReturn(array('code' => 404,'msg' => '保存草稿失败'));
    }
    // 草稿正式提交
    public function func_to_build_save(){
        $id      = I('post.id');
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
            'submittime' => date('Y-m-d H:i:s',time()),
        );
        $res = M('yx_task')->where(array('id' => $id))->save($save);
        
        if($res){
            D('WxMessage','Model')->taskSendMessage($id);
            $this->ajaxReturn(array('code' => 200,'msg' => ''));
        } 
        $this->ajaxReturn(array('code' => 404,'msg' => '保存草稿失败'));
    }
    // 待处理信息
    public function commissionData(){
        $page = I('post.page');
        $page = $page ? $page:1;
        $stat = I('post.stat');
        $name = session('name');
        $wxid = session('wxid');
        $map = array(
            'a.stat'   => array(array('eq',2),array('eq',3),'or') ,
            '_complex' => array(
                '_logic' => 'or',
                'a.tjr'  => $name,
                'a.part' => array('like',"%{$wxid}%")
            )
        );
        //b.`name` as func,c.`name` as modone,
        $field = "a.id,a.part,a.createtime,a.submittime,a.tjr,a.content,a.title,a.stat";
        $data = M('yx_task a')
                // ->join("yx_task_fuc b on a.`function` = b.id")
                // ->join("yx_task_mod c on a.`mod` = c.id")
                ->field($field)
                ->where($map)
                ->order('a.submittime DESC')
                ->limit((($page-1)*20).",20")
                ->select();
        foreach($data as $k=>$v){
            $data[$k]['submittime'] = date('m-d',strtotime($v['submittime'])); 
        }
        $this->ajaxReturn(array('code' => 200,'data' => $data));
    }

    // 已完成信息
    public function overData(){
        $page = I('post.page');
        $page = $page ? $page:1;
        $stat = I('post.stat');
        $name = session('name');
        $wxid = session('wxid');
        $map = array(
            'a.stat'   => 4 ,
            '_complex' => array(
                '_logic' => 'or',
                'a.tjr'  => $name,
                'a.part' => array('like',"%{$wxid}%"),
            )
        );
        $feild = "a.id,b.`name` as func,a.part,c.`name` as modone,a.createtime,a.submittime,a.tjr,a.content,a.title";
        $data = M('yx_task a')
                ->join("yx_task_fuc b on a.`function` = b.id")
                ->join("yx_task_mod c on a.`mod` = c.id")
               
                ->field($field)
                ->where($map)
                ->order('a.submittime DESC')
                ->limit((($page-1)*20).",20")
                ->select();
        foreach($data as $k=>$v){
            $data[$k]['submittime'] = date('m-d',strtotime($v['submittime'])); 
        }
        $this->ajaxReturn(array('code' => 200,'data' => $data));
    }

    //草稿信息
    public function draftData(){
        $page = I('post.page');
        $page = $page ? $page:1;
        $stat = I('post.stat');
        $name = session('name');
        $feild = "a.id,a.createtime,a.submittime,a.tjr,a.content,a.title";
        $data = M('yx_task a')
                ->where(array('a.stat' => 1 , 'a.tjr' =>$name))
                ->field($field)
                ->order('a.submittime DESC')
                ->limit((($page-1)*20).",20")
                ->select();
        foreach($data as $k=>$v){
            $data[$k]['submittime'] = date('m-d',strtotime($v['submittime'])); 
        }
        $this->ajaxReturn(array('code' => 200,'data' => $data));
    }

    
} 


