<?php
// 日志模型
class MessagesModel extends CommonModel {


    public $_auto		=	array(

		array('username','getName',self::MODEL_INSERT,'callback'), 
        array('pubdate','time',self::MODEL_INSERT,'function'),
        );


    public function getName() {
        
		return $_SESSION['loginUserName'];
		
    }
	
}
