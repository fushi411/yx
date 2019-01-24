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
}