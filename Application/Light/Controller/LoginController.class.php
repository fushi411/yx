<?php
namespace Light\Controller;

class LoginController extends \Think\Controller {
	public function index() {
        $this->display ();
	}
    /**
     * 用户登录
     * 
     */
    public function login($username = null, $password = null){
        if(IS_POST){
            /* 获取用户数据 */
            $username = I('post.username');
            $verify = I('post.verify');
            $password = md5(I('post.password'));
            $user = M('yxhb_boss')->where("boss='".$username."'")->find();
            if(!$this->check_verify($verify)){
                $uid = -3; //验证码错误
            } elseif(is_array($user)){
                /* 验证用户密码 */
                if($password === $user['password']) {
                    // $this->updateLogin($user['id']); //更新用户登录信息
                    $uid = $user['id']; //登录成功，返回用户ID
                } else {
                    $uid = -2; //密码错误
                }
            } else {
                $uid = -1; //用户不存在或被禁用
            }
            //登录成功
            if(0 < $uid){ 
                /* 登录用户 */
                $User = D('yxhb_boss');
                if($User->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success('登录成功！', U('Light/Index/index'));
                } else {
                    $this->error('登录失败！', U('Light/Login/index'));
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或密码错误！'; break; //系统级别禁用
                    case -2: $error = '用户不存在或密码错误！'; break;
                    case -3: $error = '验证码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if(is_login()){
                $this->redirect('Light/Index/index');
            }else{
                $this->error('非法登录！', U('Light/Login/index'));
            }
        }
    }
	public function logout() {

		session(null);

		$this->success ( '已注销登录！', U ( "Light/Login/index" ) );
	}

    public function verify(){
        $config =    array(
            'fontSize'    =>    20,    // 验证码字体大小
            'length'      =>    4    // 验证码位数
        );
        $verify = new \Think\Verify($config);
        $verify->entry();
    }

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    public function crontab()
    {
        if(get_client_ip(0) != '0.0.0.0') return '无权限操作';
        $seek =  D('Seek');
        $tab = $seek->getAppTable();
        $arr = array();
        foreach ($tab as $k => $v ) {
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']} b on a.aid=b.{$v['id']}")
                    ->field("a.aid,a.per_id,a.mod_name,a.per_name, 1 as {$v[system]}")
                    ->where(array('a.app_stat' => 0,'b.stat' => $v['submit']['stat'],'a.mod_name' => $v['mod_name'] ))
                    ->select();
            $arr = array_merge($res,$arr);
        }
 
        $this->systemUrge($arr);
    }

    /**
     * 系统自动催审
     * @param array $urgeData 催审名单 
     */
    private function systemUrge($urgeData){
        # 为空的情况，不做处理
        if(empty($urgeData)) return 0;
        # 不为空，遍历发送信息
        
        foreach($urgeData as $val){
            # 系统选择
            $system = $val['kk'] ?'kk':'yxhb';
            $mod_name = $val['mod_name'];
            $logic = D(ucfirst($system).$mod_name, 'Logic');
            $res = $logic->recordContent($val['aid']);
            $this->sendApplyCardMsg($mod_name, $val['aid'], $val['per_id'], $res['applyerID'], $system);
            // 自动评论
            $data['aid'] = $val['aid'];
            $boss = D($system.'_boss')->getWXFromID($val['per_id']);
            // $ctoid = $res['per_id'];
            $data['comment_to_id'] = $boss;
            $data['mod_name'] = $mod_name;
            $data['per_id'] = 9999;
            $data['per_name'] = '系统定时任务';
            $data['app_word'] = "系统向{$val['per_name']}发起了自动催审（每日9:30和15:30各一次）";
            $data['app_stat'] = 1;
            $data['time'] = date('Y-m-d H:i:s');
            $commentRes = M($system.'_appflowcomment')->add($data);
        }
    }   

    protected function sendApplyCardMsg($flowName, $id, $pid, $applyerid, $system, $type='' )
    {
        $systemName = array('kk'=>'建材', 'yxhb'=>'环保');
      // 微信发送
        $flowTable = M($system.'_appflowtable');
        $mod_cname = $flowTable->getFieldByProMod($flowName, 'pro_name');
        $mod_cname = str_replace('表','',$mod_cname);
        $title = $systemName[$system].$mod_cname.'(催审)';
        $url = "http://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$flowName;
        //crontab(CLI模式)无法正确生产URL
        // if (PHP_SAPI=='cli') {
        //   $detailsURL = str_replace('_PHP_FILE_', '/WE/index.php', $detailsURL);
        // }
        $boss = D($system.'_boss');
        $proName = $boss->getusername($pid);

        $subName = $boss->getusername($applyerid);
        $applyerName='('.$subName.'提交)';
       
        $boss = D($system.'_boss')->getWXFromID($applyerid);
        switch ($type) {
          case 'pass':
            $description = "您有一个流程已审批通过".$applyerName;
            $receviers = "wk|HuangShiQi|".$boss;
            break;
          case 'refuse':
            $description = "您有一个流程被拒绝".$applyerName;
            $receviers = "wk|HuangShiQi|".$boss;
            break;
          case 'other':
            $description = "您有一个流程需要处理".$applyerName;
            $receviers = "wk|HuangShiQi|".$boss;
            break;          
          default:
            $description = "您有一个流程需要审批".$applyerName;
            $receviers = "wk|HuangShiQi|".$boss;
            break;
        }
        $agentid = 15;
        $WeChat = new \Org\Util\WeChat;
        $info = $WeChat->sendCardMessage($receviers,$title,$description,$url,$agentid,$flowName,$system);
        return $info;
    }
}