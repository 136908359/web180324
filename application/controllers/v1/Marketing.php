<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Marketing extends CI_Controller {
	function __construct() {
		parent::__construct();
        $this->load->helper('url');
        $this->load->helper('template');
        $this->load->library('pagination');
        $this->load->model("marketing_model");
	}
	
	// 增加推广员
	public function add_user( ) {
		$post = $this->input->post();
		$data = array('levels'=>$this->marketing_model->get_level_list());
		if ( !$post ) {
			$uid = $this->input->get('uid');
			if ( $uid ) {
				$user = $this->marketing_model->get_user_info($uid);
				$data = array_merge($data, $user);
			}
			output('marketing/add_user.phtml', $data);
			return;
		}
		$uid = $this->input->post('uid');
		$this->marketing_model->add_user($post);
		redirect("v1/marketing/user_list?uid_list=$uid");		
	}
	
	// 推广员名单
	public function user_list( ) {
		$data = $this->input->get();
		$s = @$data['uid_list'];
		$page = @$data['page'];

		$uid_list = array();
		if ( $s ) {
			$uid_list = (array)explode(";",$s);
		}
		$users = $this->marketing_model->get_user_list($uid_list,$page);
		$data = array_merge($data,$users);
		$data['pages'] = create_page_links('/v1/marketing/user_list',$page,@$users['total_rows'],$this->input->get());

		$data['levels'] = $this->marketing_model->get_level_list();
		output('marketing/user_list.phtml', $data);
	}

	// 提现清单
	public function draw_cash_list( ) {
		$data = $this->input->get();
		$page = @$data['page'];

		$data['list'] = $this->marketing_model->draw_cash_list($page);
		$data['pages'] = create_page_links('/v1/marketing/draw_cash_list',$page,@$users['total_rows'],$this->input->get());
		output('marketing/draw_cash_list.phtml', $data);
	}

	// 审核推广员提现
	public function check_draw_cash() {
		$args = $this->input->get();
		
		$id = $args["id"];
		$code = $args['status'];
		$msg = $this->marketing_model->check_draw_cash($id, $code==1);
		echo $msg;
	}
}
?>
