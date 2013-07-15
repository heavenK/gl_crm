<?php
// 人员状态
class User_statusModel extends CommonModel {


    public $_auto		=	array(

		array('username','getName',self::MODEL_INSERT,'callback'), 
        array('pubdate','time',self::MODEL_INSERT,'function'),
		array('active','1'),
        );


    public function getName() {
        
		return $_SESSION['loginUserName'];
		
    }
	
}
