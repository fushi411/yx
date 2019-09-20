<?php
/**
 * qxc
 * 人员自定义模型
 */
namespace Light\Model;
use Think\Model;

class YxhbUserDeployModel extends Model
{

    public function upUserConfig($id)
    {
        $dataInfo =  $this->where(array('id'=>intval($id)))->find();
        if (empty($dataInfo['column_ids'])) {
            return true;//没有选择部门
        }

        $erp  = M('wx_info')->where(array('stat' => 1))->group('wxid')->select();//获取当前所有人员以及所归属的部门

        $saveData = array();
        $columnId = trim($dataInfo['column_ids'], '"');
        $columnArrs = explode(',', $columnId);
        $boss = M('yxhb_boss');
        foreach ($columnArrs as $columnId) {
            $columnIdArr = array();
            $columnIdsArr = D('Department')->extractData($columnId, $erp);//获取当前栏目下的所有成员
            foreach ($columnIdsArr as $columnWxId){
                $columnInfo = $boss->where(array('wxid'=>$columnWxId))->field('id')->select();
                $columnIdArr[] = $columnInfo[0]['id'];
            }

            $saveData = array_merge($saveData, $columnIdArr);
        }

        if (!empty($dataInfo['exclude_ids'])) {//是否有剔除人员
            $excludeId = trim($dataInfo['exclude_ids'], '"');
            $excludeData = explode(',', $excludeId);
            $saveData = array_diff($saveData, $excludeData);//剔除掉剔除人员
        }

        $bossId = trim($dataInfo['boss_ids'], '"');
        $bossData = explode(',', $bossId);

        $saveData = array_unique(array_merge($bossData, $saveData));//合并数组在去重
        $thanArr = array_diff($saveData, $bossData);//如果人员没有变化则不需要更新数据
        if (!empty($thanArr)) {
            $config = M('yxhb_user_deploy')->where(array('id' => $id))->setField(
                array('boss_ids' => implode(',', $saveData),
                    'last_at' => date('Y-m-d H:i:s')
                ));
        }
        return true;
    }
}