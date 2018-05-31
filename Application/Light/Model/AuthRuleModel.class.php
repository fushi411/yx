<?php
namespace Light\Model;
use Think\Model;
/**
 * 权限规则模型
 */
class AuthRuleModel extends Model{
    
    const RULE_URL = 1;
    const RULE_MAIN = 2;
    const RULE_VIEW = 3;
    const RULE_API = 4;
}
