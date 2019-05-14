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
    public function  unReadComments(){
        if(session('wxid') != 'HuangShiQi'){
            if(get_client_ip(0) != '0.0.0.0') return '无权限操作';
        }
        $system = array('kk','yxhb');
        foreach($system as $val){
            $this->PushMsg($val);
        }
    }
    private function PushMsg($system){
        $wx = D('WxMessage');
        $map = array(
            'per_id' => array('not in','8888,9999'),
            'app_word' => array('notlike','%@所有人%'),
            'app_stat' => 1,
            'INSTR(comment_ready,comment_to_id)' => 0,
        );
        $data = M($system.'_appflowcomment')->where($map)->select();
        foreach ($data as $val) {
            $comment = explode(',',$val['comment_to_id']);
            $reads = explode(',',$val['comment_ready']);
            $temp = array();
            foreach($comment as $v){
                if(in_array($v,$reads)) continue;
                $temp[] = $v;
            }
            if(empty($temp))continue;
            $val['comment_to_id'] = implode(',',$temp);
            $wx->comPush($system,$val['mod_name'],$val['aid'],$val['comment_to_id'],$val['per_name']);
        }
    }
    public function crontab()
    { 
        if(session('wxid') != 'HuangShiQi'){
            if(get_client_ip(0) != '0.0.0.0') return '无权限操作';
        }
        $seek =  D('Seek');
        $tab = $seek->getAppTable();
        $tab = $seek->arrayMerge($tab);
        $sub = array();

        foreach ($tab as $k => $v ) {
            // 已退审
            $map = array(
                'a.app_stat'                      => 1,
                "{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],
                'a.mod_name'                      => $v['mod_name']
            );
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field("a.aid")
                    ->where($map)
                    ->select();  
            // $aid 拼接
            $aid = '';
            foreach($res as $val){
                $aid .= ",{$val['aid']}";
            };
            $aid = trim($aid,',');
            // 各模块需要自动催审数据
            $map['a.app_stat'] = 0;
            $map['a.aid']      = array('not in',$aid);
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field("a.aid,a.per_id,a.mod_name,a.per_name")
                    ->where($map)
                    ->select();  
            
            if(!empty($res)){
                foreach($res as $key => $val){
                    $res[$key]['system']  = $v['system'];
                    $res[$key]['mod']     = $v['mod_name'];
                    $res[$key]['modname'] = $v['toptitle'];
                }
            }
            $sub = array_merge($sub,$res);
        }
        $this->systemUrge($sub);
    }

    /**
     * 系统自动催审
     * @param array $urgeData 催审名单 
     */
    private function systemUrge($urgeData){
        # 为空的情况，不做处理
        if(empty($urgeData)) return 0;
        # 数量检测
        $msgNum = $this->getMsgNum($urgeData);
        # 遍历发送信息
        $wx = D('WxMessage');
        foreach($urgeData as $val){
            # 系统选择
            $system   = $val['system'];
            $mod_name = $val['mod_name'];
            $logic    = D(ucfirst($system).$mod_name, 'Logic');
            $boss     = D($system.'_boss')->getWXFromID($val['per_id']);
            $res      = $logic->recordContent($val['aid']);
            $key      = $mod_name == 'CostMoney'?'isCostMoney':'notCostMoney';
            if($msgNum[$key][$boss]['num'] >= 3){
                $wx->autoProMoreSendMessage($val,$msgNum[$key][$boss]);
                $msgNum[$key][$boss]['num'] = 0;
            }elseif($msgNum[$key][$boss]['num']>0 && $msgNum[$key][$boss]['num'] <3){
                $wx->autoProSendMessage($val,$res['applyerName']);
            }

            // 自动评论
            $data['aid']           = $val['aid'];
            $data['comment_to_id'] = $boss;
            $data['mod_name']      = $mod_name;
            $data['per_id']        = 9999;
            $data['per_name']      = '系统定时任务';
            $data['app_word']      = "系统向{$val['per_name']}发起了自动催审（每日9:30和15:30各一次）";
            $data['app_stat']      = 1;
            $data['time']          = date('Y-m-d H:i:s');
            $commentRes            = M($system.'_appflowcomment')->add($data);
        }
    }   

    /**
     * 系统催审个数
     * @param array $urgeData 催审名单 
     */
    private function getMsgNum($urgeData){
        $res = array();
        foreach($urgeData as $k => $v){
            $system = $v['system'];
            $per_id = $v['per_id']; // 当前审批人id
            $key    = $v['mod_name'] == 'CostMoney'?'isCostMoney':'notCostMoney';
            $boss   = D($system.'_boss')->getWXFromID($per_id);
            $res[$key][$boss]['num'] += 1;
            $res[$key][$boss]['data'][$v['mod_name']]['num'] +=1;
            $res[$key][$boss]['data'][$v['mod_name']]['name']  = $v['modname'];
        }
        return $res;
    }

    public function Sign()
    {
        if(session('wxid') != 'HuangShiQi'){
            if(get_client_ip(0) != '0.0.0.0') return '无权限操作';
        }
        $config = $this->config(); 
        $arr    = array();
        $sub    = array();

        foreach ($config as $k => $v ) {
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field('a.app_stat,a.per_id,a.per_name,'.$v['copy_field'])
                    ->where(array("{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],'a.mod_name' => $v['mod_name'] ))
                    ->select();   

            // 退审数组获取
            if(empty($res)) continue;
            foreach($res as $key => $val){
                if($val['app_stat'] == 1) $arr[] = $val['aid']; 
                
                $res[$key]['mod_name'] = $v['mod_name'];
                $res[$key]['kk']       = $v['system'] == 'kk'? 1:0;
                $res[$key][0]          = $k; 
            }
            $tmp = array();
            // 排除退审情况
            foreach($res as $val){
                if(!in_array( $val['aid'],$arr) && $val['app_stat'] == 0) $tmp[] = $val;
            }
            $sub = array_merge($sub,$tmp);
        }
        $this->systemQsUrge($sub);
    }

     /**
     * 系统自动催审
     * @param array $urgeData 催审名单 
     */
    private function systemQsUrge($urgeData){
        # 为空的情况，不做处理
        if(empty($urgeData)) return 0;
        # 不为空，遍历发送信息
        
        foreach($urgeData as $val){
            # 系统选择
            $system = $val['kk'] ?'kk':'yxhb';
            $mod_name = $val['mod_name'];
            $logic = D(ucfirst($system).$mod_name, 'Logic');
            $res = $logic->recordContent($val['aid']);
            $this->sendQsApplyCardMsg($mod_name, $val['aid'], $val['per_id'], $res['applyerID'], $system);
            // 自动评论
            $data['aid'] = $val['aid'];
            $boss = D($system.'_boss')->getWXFromID($val['per_id']);
            // $ctoid = $res['per_id'];
            $data['comment_to_id'] = $boss;
            $data['mod_name'] = $mod_name;
            $data['per_id'] = 9999;
            $data['per_name'] = '系统定时任务';
            $data['app_word'] = "系统向{$val['per_name']}发起了自动催收（每日9:30和15:30各一次）";
            $data['app_stat'] = 1;
            $data['time'] = date('Y-m-d H:i:s');
            
            $commentRes = M($system.'_appflowcomment')->add($data);
        }
    }   

    protected function sendQsApplyCardMsg($flowName, $id, $pid, $applyerid, $system, $type='' )
    {
        $systemName = array('kk'=>'建材', 'yxhb'=>'环保');
      // 微信发送
        $flowTable = M($system.'_appflowtable');
        $mod_cname = $flowTable->getFieldByProMod($flowName, 'pro_name');
        $mod_cname = str_replace('表','',$mod_cname);
        $title = $systemName[$system].$mod_cname.'(催收)';
        if($system == 'kk' && $flowName == 'AddMoneyQtTz') $title = '投资'.$mod_cname.'(催收)';
        $url = "https://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$id."&modname=".$flowName;
        
        $boss = D($system.'_boss');
        $proName = $boss->getusername($pid);

        $subName = $boss->getusername($applyerid);
        $applyerName='('.$subName.'提交)';
       
        $boss = D($system.'_boss')->getWXFromID($pid);
        $h    = intval(date('G'));
        $receviers = $boss;
        //if($h > 8 && $h < 19) $receviers = "HuangShiQi|".$boss;
        
        switch ($type) {
          case 'pass':
            $description = "您有一个流程已签收通过".$applyerName;
            break;
          case 'refuse':
            $description = "您有一个流程被拒绝".$applyerName;
            break;
          case 'other':
            $description = "您有一个流程需要处理".$applyerName;
            break;          
          default:
            $description = "您有一个流程需要签收".$applyerName;
            break;
        }
        $comment_list = D($system.'Appflowcomment')->autoMessageNumber($flowName, $id,$boss);
        $description .= "\n系统发起的第{$comment_list}次催收";
        $agentid = 15;
        $WeChat = new \Org\Util\WeChat;
        
        $info = $WeChat->sendCardMessage($receviers,$title,$description,$url,$agentid,$flowName,$system);
        return $info;
    }
    
    // 基本配置
    protected function config(){
        return D('Seek')->configSign();
    }

    // 微信用户信息回去
    public function getWxInfo(){
        if(get_client_ip(0) != '0.0.0.0') return '无权限操作';
        M('wx_info')->where('stat=1')->save(array('stat'=>0));
        $userInfo = $this->getAllUser($id); 
        $userList = array();
        foreach( $userInfo as $val ){
            // 简称获取
            $length = strlen($val['id']);
            $flag   = 1;
            $jc    = '';
            for($i=0;$i<$length;$i++){
                $asc = ord($val['id'][$i]);
                if($asc>64 && $asc<91 ){
                    $jc.=$val['id'][$i];
                    $flag = 0;
                }
            }
            if($flag){
                $jc = $val['id'];
            }
            // 数据插入
            $userList[] = array(
                'wxid'   => $val['id'],
                'name'   => $val['name'],
                'avatar' => $val['avatar'],
                'stat'   => 1,
                'time'   => time(),
                'jc'     => $jc
            );
        }
        M('wx_info')->addAll($userList);
    }

    protected function getAllUser($id){
        $result = array();
        $Dept = D('department');
        $DeptInfo = $Dept->getWXDeptInfo();
        $childDeptInfo = $Dept->getChildDept($DeptInfo, $id);
        if (empty($childDeptInfo)) {
            $result = array_merge($result,$Dept->getWXDeptUserInfo($id));
        } else {
            foreach($childDeptInfo as $val){
                $result = array_merge($result,$this->getAllUser($val['id']));
            }
        }
        return $result;
    }
   
}