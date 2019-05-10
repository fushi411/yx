<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/19 0019
 * Time: 下午 2:1
 */

 /**
  * 获取某个全部审批流程
  * @param string $mod_name 流程名
  *  @return array 
  */
 function GetAppFlow($system,$mod_name)
{
    if(!$mod_name) return false;
    $res = M($system.'_appflowtable a')
    ->field('a.pro_name,b.id,a.`condition`,b.name,b.avatar,a.stage_id,a.auth_id')
    ->join($system.'_boss b on a.per_id = b.id')
    ->where(array('a.pro_mod' => $mod_name, 'a.stat' =>1))
    ->order('a.stage_id,a.date')
    ->select();
    $flow = array();
    $wxid = session('wxid');
    $flag = 'defalt';
    foreach($res as $v){
        if(empty($v['auth_id'])) $flow['defalt'][]=$v;
        $auth = explode(',',$v['auth_id']);
        if(in_array($wxid,$auth)){
            $flow['auth'][] = $v;
            $flag = 'auth';
        }
    }
    $res = $flow[$flag];
    $temp    = array();
    $proInfo = array();
    if(!empty($res)){
        foreach($res as $k=>$v){
            $k = $v['stage_id']-1;
            $temp[$k][] = $v;
        }
        foreach ($temp as $key => $value) {
            $idx = end($value);
            $id  = $idx['id'];
            foreach($value as $k => $val){
                $sign = count($value) < 2?'':1;
                if($id == $val['id']) $sign = '';
                $proInfo[] = array(
                                    "pro_name"  => $val["pro_name"],
                                    "id"        => $val["id"],
                                    "condition" => $val["condition"],
                                    "name"      => $val["name"],
                                    "avatar"    => $val["avatar"],
                                    'sign'      => $sign
                                ); 
            }
        }
    }
    return $proInfo;
}




 /**
  * 获取某个流程的推送人员
  * @param string $mod_name 流程名
  *  @return array 
  */
  function GetPush($system,$mod_name)
  {
        if(!$mod_name) return false;
        $data = array(
            'receviers' => '',
            'data'      => array()
        );
        // 查询推送的人员
//        $res = M($system.'_appflowtable')
//              ->field('condition')
//              ->where(array('pro_mod' => $mod_name.'_push', 'stat' => 1))
//              ->find();
//        // 为空，返回空数组
//        if(empty($res)) return $data;
//        $data['receviers'] = $res['condition'];
//        $pushArr = json_decode($res['condition'],true);
//		$push_id = $pushArr['push'];
//        $tempStr = explode(',',$push_id);

          $res = M($system.'_pushlist')
              ->field('push_name')
              ->where(array('pro_mod' => $mod_name, 'stat' => 1))
              ->find();
          // 为空，返回空数组
          if(empty($res)) return $data;
          if($res['push_name']=='""') return $data;
          $data['receviers'] = $res['push_name'];
          $res = trim($res['push_name'],'"');
          $tempStr = explode(',',$res);
        
        // where 条件拼接
        foreach($tempStr as $k => $v){
            if($k != 0) $where .=' or ';
            $where .= "wxid = '{$v}'";
        }
        $res = M($system.'_boss')
            ->field('name,wxid,avatar')
            ->where($where)
            ->select();
       $temp = $res;
        foreach($temp as $k => $v){
            $temp[$k]['sortwxid'] = strtolower($v['wxid']); 
        }
        $top  = array('ChenBiSong','csh','csl');
        $array = array('','','');

        $temp  = list_sort_by($temp,'sortwxid','asc');
      
        foreach($temp as $v){
            if($v['wxid'] == $top[0]){ $array[0] = $v;continue;}
            if($v['wxid'] == $top[1]){ $array[1] = $v;continue;}
            if($v['wxid'] == $top[2]){ $array[2] = $v;continue;}
            $array[] = $v;
        }
        $data['data'] = array_filter($array);
        return $data;
  }

      /**
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据 "$post_data = array( 'name' => '零售')"
     * @return string
     */
    function send_post($url, $post_data) {
        $postdata = http_build_query($post_data);
        $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type:application/x-www-form-urlencoded',
                    'content' => $postdata,
                    'timeout' => 15 * 60 // 超时时间（单位:s）
                )
            );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result,true);
    }

    /**
     * 小写金额转化大写金额
     */
    function cny($ns) { 
        static $cnums=array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖"), 
            $cnyunits=array("圆","角","分"), 
            $grees=array("拾","佰","仟","万","拾","佰","仟","亿"); 
        list($ns1,$ns2)=explode(".",$ns,2); 
        $ns2=@array_filter(array($ns2[1],$ns2[0])); 
        $ret=array_merge($ns2,array(implode("",_cny_map_unit(str_split($ns1),$grees)),"")); 
        $ret=implode("",array_reverse(_cny_map_unit($ret,$cnyunits))); 
        return str_replace(array_keys($cnums),$cnums,$ret); 
    }
    
    function _cny_map_unit($list,$units) { 
        $ul=count($units); 
        $xs=array(); 
        foreach (array_reverse($list) as $x) { 
            $l=count($xs); 
            if ($x!="0" || !($l%4)) $n=($x=='0'?'':$x).(@$units[($l-1)%$ul]); 
            else $n=is_numeric($xs[0][0])?$x:''; 
            array_unshift($xs,$n); 
        } 
        return $xs; 
    }

    // 手机号码判断
    function isMobile()
    { 
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        {
            return true;
        } 
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA']))
        { 
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        } 
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT']))
        {
            $clientkeywords = array ('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
                ); 
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
                return true;
            } 
        } 
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT']))
        { 
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            } 
        } 
        return false;
    } 