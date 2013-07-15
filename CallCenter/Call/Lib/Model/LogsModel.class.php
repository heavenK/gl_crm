<?php
// 日志模型
class LogsModel extends CommonModel {

	
    protected $_validate	=	array(
        array('pid','require','项目编号不可以为空'),
        );

    public $_auto		=	array(

		array('username','getName',self::MODEL_INSERT,'callback'), 
        array('pubdate','time',self::MODEL_INSERT,'function'),
        );


    public function getName() {
        
		return $_SESSION['loginUserName'];
		
    }
	
}
