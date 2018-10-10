<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbCostMoneyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_feefy';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->find();
    }

    public function getTableName()
    {
        return $this->trueTableName;
    }

    public function recordContent($id)
    {
        $res = $this->record($id);
        $result = array();

        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'环保矿粉物料配置',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['sb_date'])) ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
 
        $result['content'][] = array('name'=>'生产时间：',
                                     'value'=> $res['pruduct_date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $ratio = M('yxhb_assay')->where('id='.$res['rid'])->find();
        $no   = str_replace('生产线',' ' ,$ratio['scx']);
        $no   = $no.date('Ymd H:i',strtotime($ratio['date'])+$ratio['hour']*3600); 
        $result['content'][] = array('name'=>'配比通知：',
                                     'value'=>$no ,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'配比详情：',
                                     'value'=> "<span  onclick=ratioDetail({$res['rid']}) id='ratio_btn_look' style='color: #337ab7;cursor: pointer;'>点击查看详情</span>" ,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'生产品种：',
                                     'value'=>$res['product'],
                                     'type'=>'number',
                                     'color' => 'black;'
                                    );

        $result['content'][] = array('name'=>'入库库号：',
                                     'value'=>$res['ku'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $html = $this->makeHtml($res['data']);
        $result['content'][] = array('name'=>'配置详情：',
                                     'value'=>$html,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );    
        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>$res['info']?$res['info']:'无',
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        
        $result['imgsrc'] = '';
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['tjr']);
        $result['applyerName'] = $res['tjr'];
        $result['stat'] = $res['stat'];
        return $result;
    }

    /**
     * 发货详情html生成
     * @param array   $data 配比数据
     * @return string $html 
     */
    public function makeHtml($data){
        $data = json_decode($data,true);
        foreach( $data as $v){
            $html.="<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='{$v['pd']}'>";
            if(count($v['data']) == 1){
                $html .= "<input class='weui-input' type='text' style='color: black;padding: 3px 0 0 10px;'  readonly value='{$v['data'][0]['name']}'>";
            }else{
                $html .= "<input class='weui-input' type='text' style='color: black;padding: 3px 0 0 10px;'  readonly value='{$v['data'][0]['name']}：{$v['data'][1]['name']}'>";
                $html .= "<input class='weui-input' type='text' style='color: black;padding: 3px 0 0 10px;'  readonly value='分配比例：{$v['data'][0]['value']}比{$v['data'][1]['value']}'>";
            }
        }
        return $html;
    } 

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',0);
    }

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['sb_date'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'生产时间：',
                                     'value'=>date('Y-m-d',strtotime($res['pruduct_date'])),
                                     'type'=>'date'
                                    );
    
        $ratio = M('yxhb_assay')->where('id='.$res['rid'])->find();
        $no   = str_replace('生产线',' ' ,$ratio['scx']);
        $no   = $no.date('Ymd H:i',strtotime($ratio['date'])+$ratio['hour']*3600); 
        $result[] = array('name'=>'配比通知：',
                                     'value'=> $no ,
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'生产品种：',
                                     'value'=>$res['product'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'入库库号：',
                                     'value'=>$res['ku'],
                                     'type'=>'number'
                                    );

        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['tjr'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['info']?$res['info']:'无',
                                     'type'=>'text'
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
        return $this->field(true)->where($map)->getField('tjr');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res    = $this->record($id);
        $result = array(
            'sales'   => $res['tjr'],
            'title2'  => '入库库号',
            'approve' => $res['ku'],
            'notice'  => $res['info'],
            'date'    => $res['sb_date'],
            'title'   => '生产品种',
            'name'    => $res['product'], 
            'modname' => 'KfMaterielApply',
            'stat'    => $res['stat'],
        );
        return $result;
        return $result;
    }

    /**
     * 矿粉物料配置
     */
    public function submit(){
    

        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }

    /**
     * 获取 费用类型，付款方式，用款部门
     */
    public function getSomeData(){
        return array(
            'fylx' => $this->getFylx(),
            'fkfs' => $this->getFkfs(),
            'bm'   => $this->getSection()
        );
    }

    /**
     * 获取费用类型
     */
    public function getFylx(){
        $tmp = array();
        $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->order('id asc')->select();
        return $fylx;
    }
    /**
     * 获取付款方式
     */
    public function getFkfs(){
        $tmp = array();
        $fkfs = M('yxhb_fkfs')->field('id as val,fk_name as name')->order('id asc')->select();
        return $fkfs;
    }

    /**
     * 用款部门
     */
    public function getSection(){
        $tmp = array();
        $bm  = M('yxhb_bm')->field('id as val,bm as name')->order('id asc')->select();
        return $bm;
    }

     /**
     * 附件上传
     */
    public function fjsc(){
        $uploader = new \Think\Upload\Driver\Local;
        if(!$uploader){
            E("不存在上传驱动");
        }
        // 生成子目录名
        $savePath = date('Y-m-d')."/";

        // 生成文件名
        $img_str = I('post.imagefile');
        $order = I('post.order');
        $img_header = substr($img_str, 0, 23);
        // echo $img_header;exit();
        if (strpos($img_header, 'png')) {
            $output_file = uniqid('comment_').'_'.$order.'.png';
        }else{
            $output_file = uniqid('comment_').'_'.$order.'.jpg';
        }
        //  $base_img是获取到前端传递的src里面的值，也就是我们的数据流文件
        $base_img = I('post.imagefile');
        if (strpos($img_header, 'png')) {
            $base_img = str_replace('data:image/png;base64,', '', $base_img);
        }else{
            $base_img = str_replace('data:image/jpeg;base64,', '', $base_img);
        }

        //  设置文件路径和文件前缀名称
        $rootPath = "/www/web/default/yxhb/upload/fy/";
        /* 检测上传根目录 */
        if(!$uploader->checkRootPath($rootPath)){
            $error = $uploader->getError();
            return $error;
        }
        /* 检查上传目录 */
        if(!$uploader->checkSavePath($savePath)){
            $error = $uploader->getError();
            return $error;
        }
        $path = $rootPath.$savePath.$output_file;
        //  创建将数据流文件写入我们创建的文件内容中
        file_put_contents($path, base64_decode($base_img));
        $val['path'] = $path;
        $val['output_file'] = $savePath.$output_file;
        $res[] = $val;
        return $res;
    }
}