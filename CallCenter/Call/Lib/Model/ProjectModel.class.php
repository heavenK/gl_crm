<?php
// 节点模型
class ProjectModel extends CommonModel {
	
	protected $pk = 'pid';
	
	
    protected $_validate	=	array(
        array('sn','require','项目编号不可以为空'),
		array('pname','require','项目名称不可以为空'),
		array('type',array(0,1,2,3,4,5,6,7),'值的范围不正确！',2,'in'), 
		array('status',array(0,1,2,3,4,5,6,7,8,9,10),'值的范围不正确！',2,'in'), 
		array('sn','getSN','编号已经存在',self::EXISTS_VALIDATE,'callback',self::MODEL_BOTH),
        );

    public $_auto		=	array(

		array('status',1),
		
		array('username','getName',self::MODEL_INSERT,'callback'), 
        array('pubdate','time',self::MODEL_INSERT,'function'),
        array('update','time',self::MODEL_UPDATE,'function'),
        );


    public function getName() {
        
		return $_SESSION['loginUserName'];
		
    }
	
	public function getSN() {
        
		$Pro = D("Project"); 
		
		$wheres['sn'] = $_REQUEST['sn'];
		$wheres['type'] = $_REQUEST['type'];
		
		$res = $Pro->where($wheres)->find();

		if ($res)
			return false;
		else 
			return true;
		
    }
	
	protected $_link = array(
		//	招标通知单
		'tenders_notice'=>array('mapping_type'=>HAS_ONE,'class_name'=>'Tenders_notice','foreign_key'=>'pid'),
		'tenders_notice_ext'=>array('mapping_type'=>HAS_ONE,'class_name'=>'Tenders_notice_ext','foreign_key'=>'pid'),
		
		//	造价通知单
		'cost_notice'=>array('mapping_type'=>HAS_ONE,'class_name'=>'Cost_notice','foreign_key'=>'pid'),
		
		//	招标任务
		'tenders'=>array('mapping_type'=>HAS_ONE,'class_name'=>'Tenders','foreign_key'=>'pid'),
		
		//	造价任务
		'cost'=>array('mapping_type'=>HAS_ONE,'class_name'=>'Cost','foreign_key'=>'pid'),
		
		//	造价子项
		'cost_subitem'=>array('mapping_type'=>HAS_ONE,'class_name'=>'Cost_subitem','foreign_key'=>'pid'),
	);
	
	
}
