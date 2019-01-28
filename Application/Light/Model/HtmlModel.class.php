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
            $left    = $k!=0?'margin-left:8px':'';
            $avatar  = empty($val['avatar']) ? $this->avatar:$val['avatar'];
            $ispar   = '<span style="border-radius: 3px;position: relative;top: -2.3em;right: -1.4em;width: 5px;height: 5px;background-color: #333333"></span>';
            $nopar   = '<span class="glyphicon glyphicon-arrow-right" style="position: relative;top: -3.3em;right: -1.8em;font-size: 14px;"></span>';
            $parhtml = $val['parallel'] ? $ispar:$nopar;
            if($k == $last_key) $parhtml = '';
            $html .=  "<li class='weui-uploader__file wk-select__user' style='min-height:70px;width: 45px; margin-bottom: 0;margin-right: 0;{$left}'>
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
    public function getMyApproveHtml($data,$type){
        $html = '';
        if( empty($data) || !is_array($data)) return $html;
        foreach( $data as $k=>$v ){
            $url   = U('Light/Apply/applyInfo',array('system' => $v['system'],'modname' => $v['mod'] ,'aid' => $v['aid']));
            $date  = date('m/d',strtotime($v['date']));
            list($lable,$pro) = $type == 1 ? $this->pro_stat($v['stat'],$v['apply']) : $this->sign_stat($v['stat'],$v['apply']);
            $html .= "<a href='{$url}'
                class='weui-cell weui-cell_access weui-cell_link' style='text-decoration: none;'>
                <div class='weui-media-box weui-media-box_text' style='width: 100%;padding-left: 0px;padding-right: 0px;'>
                    <div class='weui-flex' style='align-items: center;'>
                        <div class='weui-flex__item'>
                            <h4 class='weui-media-box__title'>{$v['applyer']}的{$v['toptitle']}</h4>
                        </div>
                        <div class='weui-flex__item'><span class='label {$lable}' style='margin-left: 1em;padding-top: 0.3em;padding-bottom: 0.2em'>{$pro}</span></div>
                        <div class='weui-flex__item' style='text-align: right;'>
                            <h5>{$date}</h5>
                        </div>
                    </div>
                    <p class='weui-media-box__desc ' style='margin-bottom: 0px;'><span>{$v['first_title']}：</span><span>{$v['first_content']}</span></p>
                    <p class='weui-media-box__desc ' style='margin-bottom: 0px;'><span>{$v['second_title']}：</span><span>{$v['second_content']}</span></p>
                    <p class='weui-media-box__desc ' style='margin-bottom: 0px;'><span>{$v['third_title']}：</span><span>{$v['third_content']}</span></p>
                </div>
            </a>";
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

}