<?php
// 节点模型
class Cost_noticeModel extends RelationModel {
	
	// 数据表名（不包含表前缀）
	protected $pk = 'pid';
	
	
    protected $_validate	=	array(
        );

	//	自动完成
    public $_auto		=	array(
		array('recTime','strtotime',self::MODEL_BOTH,'function'), 
        array('endTime','strtotime',self::MODEL_BOTH,'function'),
		array('planLook','strtotime',self::MODEL_BOTH,'function'), 
        array('planFinish','strtotime',self::MODEL_BOTH,'function'),
        );
}
