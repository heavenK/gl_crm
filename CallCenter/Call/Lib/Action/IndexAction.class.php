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

	public function members(){
		
		if(!$_SESSION["authenticated_user_id"])	$this->error('请先登录',SUGARCRM.'index.php');
		
		$Youke = M('Datacd','myerp_','DB_CONNECT2');
		
		$where['telnum'] = array('BETWEEN',array('13000000000','19000000000'));
		
		$infos = $Youke->where($where)->order("telnum desc")->select();
		
		
		
		foreach($infos as $key => $val){
			if(!preg_match("/^1\d{2}\d{8}$/",$val['telnum'])) continue; 
			$youke[$val['telnum']]['name'] = $val['name'];
			$youke[$val['telnum']]['sex'] = $val['sex'];
			$youke[$val['telnum']]['zhengjiantype'] = $val['zhengjiantype'];
			$youke[$val['telnum']]['zhengjianhaoma'] = $val['zhengjianhaoma'];
			if($youke[$val['telnum']]['dingdanID'] == '') {
				$youke[$val['telnum']]['dingdanID'] = $val['dingdanID'];
				$youke[$val['telnum']]['price'] = $val['price'];
				$youke[$val['telnum']]['num']++;
			}
			elseif(strstr($youke[$val['telnum']]['dingdanID'] , $val['dingdanID'])) continue;
			else {
				$youke[$val['telnum']]['dingdanID'] .= ','.$val['dingdanID'];
				$youke[$val['telnum']]['price'] += $val['price'];
				$youke[$val['telnum']]['num']++;
			}
		}
		
		
		$i = 0;
		foreach($youke as $key => $val){
			$members[$i]['telnum'] = $key;
			$members[$i]['dingdanID'] = $val['dingdanID'];
			$members[$i]['name'] = $val['name'];
			$members[$i]['sex'] = $val['sex'];
			$members[$i]['zhengjiantype'] = $val['zhengjiantype'];
			$members[$i]['zhengjianhaoma'] = $val['zhengjianhaoma'];
			$members[$i]['price'] = $val['price'];
			$members[$i]['num'] = $val['num'];
			$j = $i;
			
			/*while($j > 0 && $members[$j]['num'] > $members[$j-1]['num'] ) {
				$mid = $members[$j];
				$members[$j] = $members[$j-1];	
				$members[$j-1] = $mid;
				$j--;
				
			}*/
			$i++;
		}
		
		$this->assign('members',$members);
		$this->display();
	}

	public function sendMes(){
		
		if(!$_SESSION["authenticated_user_id"])	$this->error('请先登录',SUGARCRM.'index.php');
		
		$tel_str = array();
		$i = 0;
		$j = 0;
		
		foreach($_REQUEST['telnum'] as $key => $val){
			if($i == 0) $tel_str[$j] = $val;
			else	$tel_str[$j] .= "," . $val;
			
			$i++;
			if($i>99) {
				$j++;
				$i=0;
			}
		}
		
		for($k=0; $k<$j+1; $k++){
			
			$content = iconv("utf-8","gb2312",$_REQUEST['content']);
			$data = array ('apitype' => '2','smsname' => $_REQUEST['smsname'],'smspwd' => $_REQUEST['smspwd'],'mobile' => $tel_str[$k],'content' => $content);
			
			$data = http_build_query($data);
			$opts = array (
			'http' => array (
			'method' => 'POST',
			'header'=> "Content-type: application/x-www-form-urlencoded\r\n" .
			"Content-Length: " . strlen($data) . "\r\n",
			'content' => $data
			)
			);
			$context = stream_context_create($opts);
			$html = file_get_contents('http://dx.91qxt.com/api/api.asp', false, $context);
			echo $html."<br/>";
		}
	}
	 

}