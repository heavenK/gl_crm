<?php
// 流程模型
class FlowModel extends CommonModel {

    protected $_validate	=	array(
        array('roleid','checkNode','节点已经存在',0,'callback'),
        );

    public function checkNode() {
        $map['tid']	 =	 $_POST['tid'];
		$map['roleid']	 =	 $_POST['roleid'];

        $result	=	$this->where($map)->find();
        if($result) {
            return false;
        }else{
            return true;
        }
    }
	
}
