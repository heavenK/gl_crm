<?php
// 节点模型
class Tenders_noticeModel extends RelationModel {
	
	// 数据表名（不包含表前缀）
	protected $pk = 'pid';
	
	
    protected $_validate	=	array(
        );

    public $_auto		=	array(
		array('recTime','strtotime',self::MODEL_BOTH,'function'), 
        array('endTime','strtotime',self::MODEL_BOTH,'function'),
        );
}
