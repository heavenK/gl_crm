<?php
// 节点模型
class Tenders_notice_extModel extends RelationModel {
	
	// 数据表名（不包含表前缀）
	protected $pk = 'pid';
	
	
    protected $_validate	=	array(
        );

    public $_auto		=	array(
		array('planWriteTime','strtotime',self::MODEL_BOTH,'function'), 
        array('planInnerTime','strtotime',self::MODEL_BOTH,'function'),
		array('planVerifyTime','strtotime',self::MODEL_BOTH,'function'),
        );
}
