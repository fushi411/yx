<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 建材客户调价查看
 * @author 
 */

class KkGuesttjApplyfmhLogic extends Model {

    protected $trueTableName = 'kk_tj_fmh';  // 实际表名（查询基本信息）      kk_appflowproc 查询时间、审核意见、审批状态   kk_appflowtable查询关注点
    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array(
            'pid' => $id,
        );
        return $this->field(true)->where($map)->find();
    }

    public function getTableName()
    {
        return $this->trueTableName;
    }

    //详情(点击查看之后显示)
    public function recordContent($id)
    {
        $res = $this->record($id);
        $result = array();
        $result['content'][] = array('name'=>'系统类型：',
            'value'=>'粉煤灰客户调价',
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'申请日期：',
            'value'=>date('Y-m-d',strtotime($res['tj_da'])),
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'客户调价：',
            'value'=>'查看客户调价',
            'type'=>'date',
            'color' => '#337ab7'
        );
        $result['mydata'] = $this->getTjData($id);
//        foreach ($html as $value){
//            $result['content'][] = array('name'=>'审批信息：',
//                'value'=>$value,
//                'type'=>'string',
//                'color' => 'black'
//            );
//        }
//        $result['abc'] = 'view_guest_tj_info.php?id='.$res['id'].'&type=view';//把路径传递出去
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIdFromName($res['rdy']);  //申请者的id
        $result['applyerName'] =  $res['rdy'];                            //申请者的姓名
        $result['stat'] = $this->transStat($res['tj_stat']);              //审批状态

        return $result;
    }

    public function getAllGuestName(){
        $data = M('kk_guest2_fmh')->select();
        $temp = array('无此客户');
        foreach($data  as $val){
            $temp[$val['id']] = $val['g_name'];
        }
        return $temp;
    }
    public function getTjData($aid){
        $map = array(
            'pid' => $aid,
        );

        $data = M('kk_tj_fmh')
                ->where($map)
                ->order('tj_client desc,tj_bzfs desc,tj_cate')
                ->select();
        $guest = $this->getAllGuestName();  
        $temp = array();
        foreach($data as $k => $vo){
            $vo['g_name'] = $guest[$vo['tj_client']];
            $bzfs         = $vo['tj_bzfs'] == '袋装'?'(袋)':'(散)';
            $vo['cate']   = $vo['tj_cate'].$bzfs;
            // 当前价格
            $vo['now'] = bcsub($vo['tj_dj'],$vo['delta_dj'],2);
            // 调整后价格
            $color = (int) $vo['delta_dj']>0? 'red':'green';
            $arrow = (int) $vo['delta_dj']>0? '&uarr;':'&darr;';
            $tmpl  = "<span style='color:".$color."'>(".$vo['delta_dj']."{$arrow})</span>";
            $vo['dj'] = $vo['tj_dj'].$tmpl;
            $temp[$vo['tj_client']]['g_name']  = $guest[$vo['tj_client']];  
            $temp[$vo['tj_client']]['date']    = '调价日期：'.$vo['tj_stday'];  
            $temp[$vo['tj_client']]['child'][] = $vo;    
        }
        return $temp;
    }
    // 状态值转换
    public function transStat($stat){
        $statArr = array(
            0 => 0,
            1 => 2,
            2 => 1,
        );
        return $statArr[$stat];
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('tj_stat',0);
    }

    /**
     * 详情html生成
     * @param array   $data 配比数据
     * @return string $html
     */
    public function makeDeatilHtml($res,$res2){
        $data = array();
        $key = 0;
        foreach ($res as $value){
            $stat = '';                             //审批状态
            if($value['app_stat'] == 0){
                $stat = '审批中';
            }else if($value['app_stat'] == 1){
                $stat = '审批拒绝';
            }else if($value['app_stat'] == 2){
                $stat = '审批通过';
            }
            $html = "<input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+人：{$value['per_name']}'>
                     <input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+时间：{$value['approve_time']} '>
                     <input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+关注点：{$res2[$key]['point']} '>
                     <input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+意见：{$value['app_word']} '>
                     <input class='weui-input' type='text' style='color: black;'  readonly value='{$value['app_name']}+结果：$stat'>";
            $data[] = $html;
            $key++;
        }
        return $data;
    }
    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result[] = array('name'=>'系统类型：',
            'value'=>'建材客户调价',
            'type'=>'string'
        );
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['dtime'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['date'])), 
                                     'type'=>'date'
                                    );
        return $result;
    }
    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->getField('jbr');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $temp = array(
            array('title' => '申请时间' , 'content' => date('Y-m-d',strtotime($res['tj_da'])) ),
            array('title' => '调价日期' , 'content' => date('Y-m-d',strtotime($res['tj_stday'])) ),
            array('title' => '相关说明' , 'content' => '无'  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $this->transStat($res['tj_stat']),
            'applyerName'    => $res['rdy'],
        );
        return $result;
    }

    
}