<?php
class IndexAction extends Action {

	public function index(){
		
		$Account = M("Accounts");
		
		$where['phone_office'] = array('LIKE','%'.$_REQUEST['num'].'%');
		$where['deleted'] = 0;
		$account = $Account->where($where)->order("id desc")->find();

		if(!$account){
			$Leads = M("Leads");

			$where['phone_work'] = array('LIKE','%'.$_REQUEST['num'].'%');
			$where['deleted'] = 0;
			$leads = $Leads->where($where)->order("id desc")->find();
			
			if($leads)	$conver_url = SUGARCRM."index.php?module=Leads&action=ConvertLead&record=".$leads['id'];
			
			$this->assign('conver_url',$conver_url);
			$this->assign('leads',$leads);
		}

		if(!$leads){
			$account_url = SUGARCRM."index.php?module=Accounts&action=EditView&return_module=Accounts&return_action=index&phone_office=".$_REQUEST['num'];	
			$leads_url = SUGARCRM."index.php?module=Leads&action=EditView&return_module=Leads&return_action=DetailView&phone_work=".$_REQUEST['num'];
			
			$this->assign('account_url',$account_url);
			$this->assign('leads_url',$leads_url);
		}
		
		

		$Youke = M('Datacd','myerp_','DB_CONNECT2');

		$where['telnum'] = array('LIKE','%'.$_REQUEST['num'].'%');
		
		$youke_count = $Youke->where($where)->field("dingdanID")->distinct('dingdanID')->count();
		$youke = $Youke->where($where)->field("dingdanID")->distinct('dingdanID')->order("id desc")->select();
		
		foreach($youke as $key => $val){
			if($key == 0)	$dingdanID = $val['dingdanID'];
			$dingdanID .= ','.$val['dingdanID'];
		}
		
		$Xianlu = M('chanpin_dingdan','myerpview_','DB_CONNECT2');
		
		$wheres['chanpinID']  = array('IN', $dingdanID);
		
		$xianlu_count = $Xianlu->where($wheres)->order("chanpinID desc")->count();
		$xianlus = $Xianlu->where($wheres)->order("chanpinID desc")->limit(0,10)->select();
		
		
		
		$Cdr = M('Cdr','','DB_CONNECT3');

		$where['src'] = $_REQUEST['num'];
		
		$cdr_sum = $Cdr->where($where)->count();
		$cdr = $Cdr->where($where)->order("calldate desc")->limit(0,10)->select();


		$this->assign('account',$account);
		
		$this->assign('xianlu_count',$xianlu_count);
		$this->assign('xianlus',$xianlus);
		$this->assign('cdr_sum',$cdr_sum);
		$this->assign('cdr',$cdr);
		$this->display();
	}











    public function profile() {
        $this->checkUser();
        $User	 =	 M("User");
        $vo	=	$User->getById($_SESSION[C('USER_AUTH_KEY')]);
        $this->assign('vo',$vo);
        $this->display();
    }

    public function verify() {
        $type	 =	 isset($_GET['type'])?$_GET['type']:'gif';
        import("@.ORG.Util.Image");
        Image::buildImageVerify(4,1,$type);
    }
	
	
	public function wordTOpdf() {
		
		set_time_limit(0); 

		
        $pid	 =	 isset($_GET['pid'])?$this->_GET('pid'):$this->error('无法转换!');
		$Pro	 =	 M("Project");
        $vo	=	$Pro->getByPid($pid);
		
		$doc_file = WEB_ROOT."/Public/Uploads/".$vo['attachment']; 
		
		$pdf_path = explode('/',$vo['attachment']);
		
		$output_file = WEB_ROOT."/Public/Uploads/".$pdf_path['0']."/".$pdf_path['1']."/"; 
		
		exec("unoconv -f pdf -o ".$output_file." ".$doc_file);
		
		// windows 用的转换过程
		/*
		$doc_file = "file:///".WEB_ROOT."/Public/Uploads/".$vo['attachment']; 
		$output_file = "file:///".WEB_ROOT."/Public/Uploads/".$vo['attachment'].".pdf"; 
		if(!word2pdf($doc_file,$output_file)) $this->error("您的附件不是doc形式，无法转换！");
		
		*/
		//$this->success('转换成功', "/Public/Uploads/".$vo['attachment'].".pdf");
		
		//php 强制下载PDF文件
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename='.$vo['sn']);
		
		$pdf_file = str_replace(".doc",".pdf",$vo['attachment']);
		
		readfile(WEB_ROOT."/Public/Uploads/".$pdf_file);
    }

    // 修改资料
    public function change() {
        $this->checkUser();
        $User	 =	 D("User");
        if(!$User->create()) {
            $this->error($User->getError());
        }
        $result	=	$User->save();
        if(false !== $result) {
            $this->success('资料修改成功！');
        }else{
            $this->error('资料修改失败!');
        }
    }
	
	//	人员状态
	public function goOut() {
		$this->checkUser();
        $User	 =	 D("User_status");
		
		$condition['username'] = $_SESSION['loginUserName'];
		$res = $User->where($condition)->setField('active',0);
		
		if($this->_request('type') == 2){
			if ($res !== false) { //保存成功
	
				$this->success('修改成功！',cookie('_currentUrl_'));
			} else {
				//失败提示
				$this->error('修改失败!');
			}
			exit;
		}
		
		$data['reason'] = $this->_request('reason');
		
		$data = $User->create($data);
		
        if (false === $data) {
            $this->error($User->getError());
        }
		
		//保存当前数据对象
        $list = $User->add($data);
		
		if ($list !== false) { //保存成功

            $this->success('修改成功！',cookie('_currentUrl_'));
        } else {
            //失败提示
            $this->error('修改失败!');
        }
    }
	
	//	实时消息
	public function getNews(){
		//	我的消息
		$Mes = D("Messages");
		$m_where['roleid&kind'] =array(array("IN", $_SESSION['roleId']),"verify",'_multi'=>true);
		$m_where['kind&uid'] =array("confirm",$_SESSION['authId'],'_multi'=>true);
		$m_where['_logic'] = 'or';
		$m_condition['_complex'] = $m_where;

		$my_ver_count = $Mes->where($m_condition)->count();
		$my_ver_pros = $Mes->where($m_condition)->order("`pubdate` desc")->limit(0,20)->select();
		
		$new_pros = $Mes->where($m_condition)->order("`pubdate` desc")->find();
		
		if(!$new_pros)	{
			echo 2;
			return;
		}
		
/*		if(($this->_request('flag') > 0 && $this->_request('flag') >= $new_pros['pubdate']))	{
			return false;
		}*/
		
		$this->assign('my_ver_count', $my_ver_count);
		$this->assign('my_ver_pros', $my_ver_pros);
		
		if($this->_request('type') == 'getNews')	$this->display();	
		
	}
}