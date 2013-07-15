<?php
//	获取当前用户状态
function getUser_status($username){
	$User = M("User_status");
	
	$condition['active'] = 1;
	$condition['username'] = $username;
	
	$now = strtotime(date("Y-m-d"));
	$condition['pubdate'] = array('egt',$now);
	
	$status = $User->where($condition)->find();

	if($status) return '<a tabindex="-1"  onclick="submitForm(2)" href="###">回来</a>';
	else return '<a tabindex="-1"  onclick="submitForm(1)" href="###">离开</a>';
}


//	通过状态ID来获取状态名
function getStatus($val){
	$Status = M("status");
	
	$condition['sid'] = $val;
	$status = $Status->where($condition)->find();

	if($status) return $status['sname'];
	else return "状态信息错误";
}

//	通过状态ID来获取项目类型
function getTypes($val){
	$Type = M("type");
	
	$condition['tid'] = $val;
	$type = $Type->where($condition)->find();

	if($type) return $type['tname'];
	else return "类型信息错误";
}

//	获取全部用户信息
function getAllUser($val = 1){
	$User = D("user");
	
	$condition['status'] = $val;
	$users = $User->where($condition)->select();

	foreach($users as $user){
		$str .= '<option value="'.$user['id'].'">'.$user['nickname'].'</option>';
	}

	if($users) return $str;
	else return '<option value="0">获取失败</option>';
}

//	获取全部类型信息
function getAllType(){
	$Type = D("type");
	
	$types = $Type->select();

	foreach($types as $type){
		$str .= '<option value="'.$type['tid'].'">'.$type['tname'].'</option>';
	}

	if($types) return $str;
	else return '<option value="0">获取失败</option>';
}

//	获取全部角色信息
function getAllRole(){
	$Role = D("role");
	
	$condition['status'] = 1;
	
	$roles = $Role->where($condition)->select();

	foreach($roles as $role){
		$str .= '<option value="'.$role['id'].'">'.$role['name'].'</option>';
	}

	if($roles) return $str;
	else return '<option value="0">获取失败</option>';
}

//	通过ID来获取角色信息
function getRole($rid){
	$Role = M("Role");
	
	$condition['id'] = $rid;
	$role = $Role->where($condition)->find();

	if($role) return $role['name'];
	else return "无";
}

//	通过用户ID来获取真实姓名
function getNickname($uid){
	$User = M("user");
	
	$condition['id'] = $uid;
	$user = $User->where($condition)->find();

	if($user) return $user['nickname'];
	else return "无";
}

//	通过用户名来获取ID
function getUID($username){
	$User = M("user");
	
	$condition['nickname'] = $username;
	$user = $User->where($condition)->find();

	if($user) return $user['id'];
	else return "0";
}

//	全局上传函数，返回保存路径
function uploadFiles($pid){
	import('ORG.Net.UploadFile');
    $upload = new UploadFile();// 实例化上传类
    $upload->maxSize  = 10000000 ;// 设置附件上传大小
    $upload->allowExts  = array('doc', 'docx', 'xls', 'xlsx', 'txt', 'pdf', 'jpg', 'jpeg', 'png', 'gif');// 设置附件上传类型
	$upload->autoSub = true;
	$upload->subType = 'date';
	$upload->dateFormat = 'Y/m';
    $upload->savePath =  './Public/Uploads/';// 设置附件上传目录
	$upload->saveRule = date("d") . "-" . $pid ."-". time();	//	定义命名规则
    if(!$upload->upload()) {	// 上传错误提示错误信息
        //dump($upload->getErrorMsg());
		//exit;
		return false;
    }else{// 上传成功
        $info = $upload->getUploadFileInfo();
		return $info[0]['savename'];
    }

}

//	记录日志
function writeLogs($pid, $pass=0, $content='', $kind='oprate', $active=0, $roleid=0){
	$Logs = D("Logs");
	
	$datas = array();
	$datas['pid'] = $pid;
	$datas['pass'] = $pass;
	$datas['content'] = $content;
	$datas['kind'] = $kind;
	$datas['active'] = $active;
	$datas['roleid'] = $roleid;
	
	//	审核事件需要检验是否存在多次审核。
	if($kind == 'verify'){
		$condition['pid'] = $pid;
		$condition['kind'] = $kind;
		$condition['active'] = 1;
		$condition['roleid'] = $roleid;
		
		$log = $Logs->where($condition)->find();
		
		if($log)	{
			$log['active'] = 0;
			$Logs->save($log);
		}
	}
	
	if($pass == 'no_pass'){
		$con['pid'] = $pid;
		$con['kind'] = $kind;
		$con['active'] = 1;
		
		$data['active'] = 0;
		
		$Logs->where($con)->data($data)->save();
	}
	
	
	$datas = $Logs->create($datas);

	if(!$datas) return false;
	
	$list = $Logs->add($datas);
	
	//	处理附件
	$r = uploadFiles($list);
	if($r){
		$att_date['id'] = $list;
		$att_date['attachment'] = $r;
		$Logs->save($att_date);
	}
	
	
	//	每个操作都会产生消息
	if($kind == 'verify'){
		$Project = D("Project");
		$pro = $Project->where("`pid` = ".$pid)->find();
		$uid = $pro['principal'];
		$type = $pro['type'];
		
		
		$Mes = D("Messages");
		$m_data = array();
		$m_data['pid'] = $pid;
		
		$old_mes = $Mes->where($m_data)->find();
		
		if($old_mes) $Mes->where($m_data)->delete();
		
		//	如果审核通过，则发消息给下一审核角色。
		if($pass == 1){
			
			
			$Flow = D("flow");
			$flow = $Flow->where("`tid` = ".$type . " AND `roleid` = ".$roleid)->find();
			
			if($flow)	{	//	如果已经进入定制流程，则正常查找下一审核角色
				$new_order = $flow['order'] + 1;
				$flow = $Flow->where("`tid` = ".$type . " AND `order` = ".$new_order)->find();
				$new_roleid = $flow['roleid'];
			}elseif($roleid == 8){	//	如果当前审核人为负责人，则进去定制第一审核角色
				$flow = $Flow->where("`tid` = ".$type . " AND `order` = 1")->find();
				$new_roleid = $flow['roleid'];
			}else{	//	如果当前人为编写人，则请求负责人进行审核
				$kind = 'confirm';
				$new_roleid = 8;
				
			}
	
			$m_data['uid'] = $uid;
			$m_data['kind'] = $kind;
			$m_data['roleid'] = $new_roleid;
			$m_data['content'] = "编号为'".$pro['sn']."'，名称为《".$pro['pname']."》的项目需要您进行审核确认！";
		}else{		// 否则发消息给项目发布人，通知其修改。
			
			$m_data['uid'] = getUID($pro['username']);
			$m_data['kind'] = "info";
			$m_data['content'] = "您发布的编号为'".$pro['sn']."'，名称为《".$pro['pname']."》的项目审核没有通过，请修改后重新提交！";
		}

		$m_data = $Mes->create($m_data);
		$Mes->add($m_data);
	}
	
	
	
	if(!$list) return false;
	else return true;
}

//	获取当前项目的审核状态
function getVerify($pid, $type){
	$verify = array();
	
	$Flow = D("flow");
	$f_condition['tid'] = $type;
	$flow = $Flow->where($f_condition)->order('`order` asc')->select();
	
	//	编写人和负责人是固定流程
	$verify_first[0]['roleid'] = 7;
	$verify_first[0]['role'] = "编写人";
	$verify_first[0]['order'] = 1;
	$verify_first[1]['roleid'] = 8;
	$verify_first[1]['role'] = "负责人";
	$verify_first[1]['order'] = 2;
	
	foreach($flow as $key => $val){
		$verify_second[$key+2]['roleid'] = $val['roleid'];
		$verify_second[$key+2]['role'] = getRole($val['roleid']);
		$verify_second[$key+2]['order'] = $val['order']+2;
	}
	
	$verify = $verify_first + $verify_second;
	
	
	$Logs = D("logs");
	$l_condition['pid'] = $pid;
	$l_condition['kind'] = "verify";
	$l_condition['active'] = 1;
	$logs = $Logs->where($l_condition)->order('`pubdate` asc')->field('username')->select();
	
	foreach($logs as $key => $log){
		$verify[$key]['username'] = $log['username'];
	}
	
	return $verify;
}

function getPass($pass){
	
	if($pass == 1) return "审核通过";
	else return "审核不通过";
}

//	通过PID构造访问链接。
function getUrl($pid, $kind="verify"){
	$Pro = D("Project");
	$pro = $Pro->getByPid($pid);
	
	if($kind == 'verify')	return "__APP__/Project/verify/action/show/type/".$pro['type']."/pid/".$pid;
}


//	word 转pdf需要用的函数
function MakePropertyValue($name,$value,$osm){ 
    $oStruct = $osm->Bridge_GetStruct("com.sun.star.beans.PropertyValue"); 
    $oStruct->Name = $name; 
    $oStruct->Value = $value; 
    return $oStruct; 
} 
function word2pdf($doc_url, $output_url){ 
    //Invoke the OpenOffice.org service manager 
        $osm = new COM("com.sun.star.ServiceManager") or die ("Please be sure that OpenOffice.org is installed.\n"); 
    //Set the application to remain hidden to avoid flashing the document onscreen 
        $args = array(MakePropertyValue("Hidden",true,$osm)); 
    //Launch the desktop 
        $top = $osm->createInstance("com.sun.star.frame.Desktop"); 
    //Load the .doc file, and pass in the "Hidden" property from above 
	
	try
	 {
		$oWriterDoc = $top->loadComponentFromURL($doc_url,"_blank", 0, $args); 
		//Set up the arguments for the PDF output 
		$export_args = array(MakePropertyValue("FilterName","writer_pdf_Export",$osm)); 
		//Write out the PDF 
		$oWriterDoc->storeToURL($output_url,$export_args); 
		$oWriterDoc->close(true); 
		return true;
	 }
	
	//捕获异常
	catch(Exception $e)
	 {
	 	return false;
	 }
	
    
} 

//	获取滚动新闻
function getRollnews(){
	$News = M("News");

	$r_news = $News->where("`type` = 'roll'")->order("`pubdate` desc")->find();

	return $r_news['title'];
}

//	获取新闻类型
function getNewstype($type){
	
	if($type == 'news') return "新闻";
	elseif($type == 'roll') return "滚动新闻";
}
?>