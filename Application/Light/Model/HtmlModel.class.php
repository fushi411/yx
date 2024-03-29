<?php
namespace Light\Model;
use Think\Model;
/**
 * 客户信息
 */

class HtmlModel extends Model
{
    // 虚拟模型
    protected $autoCheckFields = false;
    protected $avatar = 'Public/assets/i/defaul.png';
    /**
     * 审批流程html
     */
    public function getProHtml($data){
        $html = '';
        if( empty($data) || !is_array($data)) return $html;
        $arr      = array_slice($data,-1,1,true);
        $last_key = key($arr);
        foreach ($data as $k => $val) {
            // $k!=0?'margin-left:8px':
            $left    = '';
            $avatar  = empty($val['avatar']) ? $this->avatar:$val['avatar'];
            $ispar   = '<span style="border-radius: 3px;position: relative;top: -2.3em;right: -1.4em;width: 5px;height: 5px;background-color: #333333"></span>';
            $nopar   = '<span class="glyphicon glyphicon-arrow-right" style="position: relative;top: -3.3em;right: -1.8em;font-size: 14px;color:#f12e2e;"></span>';
            $parhtml = $val['parallel'] ? $ispar:$nopar;
            if($k == $last_key) $parhtml = '';
            $html .=  "<li class='weui-uploader__file wk-select__user' style='min-height:70px;width: 45px; margin-bottom: 0;margin-right: 8px;{$left}'>
                        <img src='{$avatar}' class='weui-selet__user' style='margin-right: 6px;margin-top:10px; width: 2em; height: 2em;'>
                        <span style='margin-right: 6px;font-size: 12px;'> {$val['name']}</span>{$parhtml}
                       </li>";
        }
        return $html;
    }

    /**
     * 我的审批html
     * @param int $type 1-审批 2-签收
     */
    public function getMyApproveHtml($data,$type,$unready){
        $html = '';
        if( empty($data) || !is_array($data)) return $html;
        $dot = '';
        if($unready){
            $dot = '<div class="weui-flex__item">
                <span class="weui-badge weui-badge_dot" style="margin-left: 5px;margin-right: 5px;margin-bottom: 8px;"></span>
            </div>';
        }

        foreach( $data as $k=>$v ){
            $url   = U('Light/Apply/applyInfo',array('system' => $v['system'],'modname' => $v['mod'] ,'aid' => $v['aid']));
            $date  = date('m/d',strtotime($v['date']));
            
            list($lable,$pro) = $type == 1 ? $this->pro_stat($v['stat'],$v['apply']) : $this->sign_stat($v['stat'],$v['apply']);
            if($v['app_name'] == '转审') $pro = '转审';
            $content = '';
            foreach($v['content'] as $val){
                $content .= empty($val)?'':"<p class='weui-media-box__desc ' style='margin-bottom: 0px;'><span>{$val['title']}：</span><span {$val['color']}>{$val['content']}</span></p>";
            }
            
            $id    = $v['mod'].$v['aid']; 
            $html .= "
            <label for='$id' style='width:100%;'>
                <a href='{$url}' target='_blank'
                    class='weui-cell weui-cell_access weui-cell_link' style='text-decoration: none;'>
                        <div class='weui-cell__hd' style='width: 32px;height: 25px;display:none;'>
                            <input type='checkbox' name='checkbox1' id='$id'  class='weui-check' data-applyUser='{$v['applyer']}' data-system='{$v['system']}' data-aid='{$v['aid']}' data-mod='{$v['mod']}'>
                            <i class='weui-icon-checked'></i>
                        </div>
                        <div class='weui-media-box_text' style='width: 100%;padding:5px 0px;position: relative;'>
                            <div class='weui-flex' style='align-items: center;'>
                                <div class='weui-flex__item'>
                                    <h4 class='weui-media-box__title'>{$v['applyer']}的{$v['toptitle']}</h4>
                                </div>
                                <div class='weui-flex__item'><span class='label {$lable}' style='margin-left: 1em;padding-top: 0.3em;padding-bottom: 0.2em;font-weight:300;'>{$pro}</span></div>
                                {$dot}
                                <div class='weui-flex__item' style='text-align: right;'>
                                    <h5>{$date}</h5>
                                </div>
                            </div>
                            {$content}
                        </div>   
                </a>
            </label>";
        }
        return $html;
    }

    // 审批状态转化
    public function pro_stat($stat,$apply){
        $res = array('','');
        if($stat == 1) $res = array('label-success','已通过');  
        if($stat == 2 && $apply['stat'] == 2) $res = array('label-success','已通过');  
        if($stat == 2 && $apply['stat'] == 1) $res = array('label-danger','已退审');  
        if($stat == 2 && $apply['stat'] == 0) $res = array('label-primary','审批中');
        if($stat == 2 && $apply['stat'] == -1) $res = array('label-primary','审批中');   
        if($stat == 0) $res = array('label-default','已撤销');  
        return $res;
    }

    //签收状态转化
    public function sign_stat($stat){
        $res = array('','');
        if($stat == 1) $res = array('label-success','已签收');  
        if($stat == 2 ) $res = array('label-primary','签收中');  
        if($stat == 3 ) $res = array('label-danger','已拒收');  
        if($stat == 0) $res = array('label-default','已撤销');  
        return $res;
    }

    // 固定抄送
    public function fiexdCopyHtml($data){
        $html = '';
        if( empty($data) || !is_array($data)) return $html;
        $arr      = array_slice($data,-1,1,true);
        $last_key = key($arr);
        foreach ($data as $k => $val) {
            $left    = 'margin-right:8px';
            $avatar  = empty($val['avatar']) ? $this->avatar:$val['avatar'];
            if($k == $last_key) $parhtml = '';
            $html .=  "<li class='weui-uploader__file wk-select__user' id='{$val['wxid']}' style='min-height:70px;width: 45px; margin-bottom: 0;{$left}'>
                        <img src='{$avatar}' class='weui-selet__user' style='margin-right: 6px;margin-top:10px; width: 2em; height: 2em;'>
                        <span style='margin-right: 6px;font-size: 12px;'> {$val['name']}</span>
                        <span class='weui-badge' style='position: relative;top: -5.2em;right: -1.2em;padding: 1px 3px;line-height: 1;'>X</span>
                       </li>";
        }
        return $html;
    }

    // 固定抄送
    public function fiexdCopyHtmls($data, $class, $width = 45, $height = 70){
        $html = '';
        if( empty($data) || !is_array($data)) return $html;
        $arr      = array_slice($data,-1,1,true);
        $last_key = key($arr);
        foreach ($data as $k => $val) {
            $size = '5.2';
            if (strlen($val['name']) > 15) {
                $size = '6.2';
            }

            $left    = 'margin-right:8px';
            $avatar  = empty($val['avatar']) ? $this->avatar:$val['avatar'];
            if($k == $last_key) $parhtml = '';
            $html .=  "<li class='weui-uploader__file wk-select__user' id='{$val['wxid']}' style='min-height:{$height}px;width: {$width}px; margin-bottom: 0;{$left}'>
                        <img src='{$avatar}' class='weui-selet__user' style='margin-right: 6px;margin-top:10px; width: 2em; height: 2em;'>
                        <span style='margin-right: 6px;font-size: 12px;'> {$val['name']}</span>
                        <span class='{$class}' style=' vertical-align: middle;font-size: 12px;text-align: center;color: #fff;background-color: #f43530;border-radius: 18px;min-width: 8px;display: inline-block;position: relative;top: -{$size}em;right: -1.2em;padding: 1px 3px;line-height: 1;'>X</span> 
                       </li>";
        }
        return $html;
    }

    // 审批流程配置
    public function getProConfigHtml($data){
        $html = '';
        if( empty($data) || !is_array($data)) return $html;
        $arr      = array_slice($data,-1,1,true);
        $last_key = key($arr);
        foreach ($data as $k => $val) {
            $left    = $k!=0?'margin-left:8px':'';
            $avatar  = empty($val['avatar']) ? $this->avatar:$val['avatar'];
            $ispar   = '<span style="border-radius: 3px;position: relative;top: -2.3em;right: 1.7em;width: 5px;height: 5px;background-color: #333333"></span>';
            $nopar   = '<span class="glyphicon glyphicon-arrow-right" style="position: relative;top: -3.3em;right: -1.8em;font-size: 14px;color:#f12e2e;"></span>';
            $parhtml = $val['parallel'] ? $ispar:$nopar;
            if($k == 0) $parhtml = '';
            $html .=  "<li class='weui-uploader__file wk-select__user' id='{$val['wxid']}' style='min-height:70px;width: 45px; margin-bottom: 0;margin-right: 0;{$left}'>
                        <img src='{$avatar}' class='weui-selet__user' style='margin-right: 6px;margin-top:10px; width: 2em; height: 2em;'>
                        <span style='margin-right: 6px;font-size: 12px;'> {$val['name']}</span>
                        <span class='weui-badge' style='position: relative;top: -5.2em;right: -1.2em;padding: 1px 3px;line-height: 1;'>X</span>
                       </li>";
        }
        return $html;
    }

    // 无审批流程html
    public function noAppProHtml(){
        return "<li style='font-size: 28px;line-height: 60px;color: #f12e2e;'>无审批流程</li>";
    }
     // 推送显示
     public function PushHtml($data){
        $html = '';
        if( empty($data) || !is_array($data)) return $html;
        $arr      = array_slice($data,-1,1,true);
        $last_key = key($arr);
        foreach ($data as $k => $val) {
            $left    = 'margin-right:8px';
            $avatar  = empty($val['avatar']) ? $this->avatar:$val['avatar'];
            if($k == $last_key) $parhtml = '';
            $html .=  "<li class='weui-uploader__file wk-select__user' id='{$val['wxid']}' style='min-height:70px;width: 45px; margin-bottom: 0;{$left}'>
                        <img src='{$avatar}' class='weui-selet__user' style='margin-right: 6px;margin-top:10px; width: 2em; height: 2em;'>
                        <span style='margin-right: 6px;font-size: 12px;'> {$val['name']}</span>
                       </li>";
        }
        return $html;
    }
}