<?php
namespace Light\Controller;
use Think\Controller;

class BaseController extends Controller {

    private $urlHead = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx133a00915c785dec&redirect_uri=http%3a%2f%2fwww.fjyuanxin.com';

    private $urlEnd = '&response_type=code&scope=snsapi_base&state=YUANXIN#wechat_redirect';/**
	 * 判断是否登录
	 */
	public function _initialize(){
    // // 判断用户是否登陆
    // $id = session('user_auth.id');
    // if(!$id){
    //     $this->success('请先登录',U('Login/index'),2);
    //     exit;
    // }
    // 获取当前用户ID
    $this->pc_to_web_login();
    define('WXID',is_login());
    if( !WXID ){
      $url = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
      $realURL = __SELF__;
      $redirectURL = $this->urlHead.urlencode($realURL).$this->urlEnd;
      // 微信登录判断
      $state = I('get.state');
      $system = I('get.system');
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
    //检测权限
    //动态配置用户表
    // C('DB_PREFIX', $system);
    $rule  = $this->getRule();

    if ( !$this->checkRule($rule,array('in','1,2,3,4')) ){
        $this->error('无访问权限!'.$rule);
    }
  }

  /**
   * 确认使用的rule
   * @return string rule auth检测用，地址 
   */
  private function getRule(){
    if(strtolower(CONTROLLER_NAME) == 'view'){
        $system   = I('system');
        $mod_name = I('modname');
        return  strtolower(MODULE_NAME.'/'.$system.'/'.$mod_name);
    }elseif(strtolower(CONTROLLER_NAME) == 'api'){
        $mod_name = I('modname');
        if(!$mod_name) $mod_name = $_GET['modname'];
        return  strtolower(MODULE_NAME.'/'.$mod_name.'/'.ACTION_NAME);
    }

    return strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
  } 

  /**
   * 权限检测
   * @param string  $rule    检测的规则
   * @param string  $mode    check模式
   * @return boolean
   */
  final protected function checkRule($rule, $type=AuthRuleModel::RULE_URL, $mode='url'){
      static $Auth    =   null;
      if (!$Auth) {
          $Auth       =   new \Think\Auth();
      }
      if(!$Auth->check($rule,WXID,$type,$mode)){
          return false;
      }
      return true;
  }

 /**
   * PC转web 自动登录
   * @return boolean
   */
  private function pc_to_web_login(){
    $sessid = cookie('PHPSESSID');
    $system = I('get.systempath');
    $wxid   = I('get.wxid');
    if($system ){
        $path   = "/www/web/default/{$system}/data/sessions/sess_{$sessid}";
        $cont   = file_get_contents($path);
        
        $cont   = explode(';',$cont);
        $cont   = array_filter($cont);
        $tmp    = array();
        foreach ($cont as $k => $v) {
            if( empty($v) ) continue;
            $v   = explode('|',$v); 
            $key = $v[0];
            $arr = explode(':',$v[1]);
            $val = $arr[2];
            $tmp[$key] = trim($val,'"'); 
        }
    
        $wxid = str_replace('OvSQq4967K','',$tmp['VioomaUserID']);
        if($system == 'sngl'){
            $main_system = 'kk';
        }else{
            $main_system = 'yxhb';
        }
        $user =  M($main_system.'_boss')->field("id")->where(array('boss' => $wxid))->find();
    }elseif($wxid){
        $main_system = I('get.system');
        $user =  M($main_system.'_boss')->field("id")->where(array('wxid' => $wxid))->find();
    }
    if(empty($user)) return;
    $id = $user['id'];
    D($main_system.'Boss')->login($id);
    return true;
}
}