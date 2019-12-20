<?php 
class ControllerExtensionPaymentComprafacil extends Controller {
	private $error = array(); 
	 
	public function index() { 
		$this->load->language('extension/payment/comprafacil');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('comprafacil', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('extension/payment/comprafacil', 'token=' . $this->session->data['token'], 'SSL'));
		}

	
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
				
		$data['entry_cf_username'] = $this->language->get('entry_cf_username');
		$data['entry_cf_password'] = $this->language->get('entry_cf_password');
		$data['entry_cf_mode'] = $this->language->get('entry_cf_mode');
        $data['entry_cf_entity'] = $this->language->get('entry_cf_entity');
        
        $data['entry_cf_no'] = $this->language->get('entry_cf_no');
        $data['entry_cf_yes'] = $this->language->get('entry_cf_yes');
        $data['entry_cf_active'] = $this->language->get('entry_cf_active');
        $data['entry_cf_disabled'] = $this->language->get('entry_cf_disabled');
        
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');


		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/comprafacil', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/payment/comprafacil', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment/comprafacil', 'token=' . $this->session->data['token'], 'SSL');




		if (isset($this->request->post['comprafacil_username'])) {
			$data['comprafacil_username'] = $this->request->post['comprafacil_username'];
		} else {
			$data['comprafacil_username'] = $this->config->get('comprafacil_username');
		}
		
		if (isset($this->request->post['comprafacil_password'])) {
			$data['comprafacil_password'] = $this->request->post['comprafacil_password'];
		} else {
			$data['comprafacil_password'] = $this->config->get('comprafacil_password');
		}
		
		if (isset($this->request->post['comprafacil_mode'])) {
			$data['comprafacil_mode'] = $this->request->post['comprafacil_mode'];
		} else {
			$data['comprafacil_mode'] = $this->config->get('comprafacil_mode');
		}
        
        if (isset($this->request->post['comprafacil_entity'])) {
            $data['comprafacil_entity'] = $this->request->post['comprafacil_entity'];
        } else {
            $data['comprafacil_entity'] = $this->config->get('comprafacil_entity');
        }
		
		if (isset($this->request->post['comprafacil_status'])) {
			$data['comprafacil_status'] = $this->request->post['comprafacil_status'];
		} else {
			$data['comprafacil_status'] = $this->config->get('comprafacil_status');
		}
		
		if (isset($this->request->post['comprafacil_sort_order'])) {
			$data['comprafacil_sort_order'] = $this->request->post['comprafacil_sort_order'];
		} else {
			$data['comprafacil_sort_order'] = $this->config->get('comprafacil_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		

		$this->response->setOutput($this->load->view('extension/payment/comprafacil.tpl', $data));
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/comprafacil')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
				
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>