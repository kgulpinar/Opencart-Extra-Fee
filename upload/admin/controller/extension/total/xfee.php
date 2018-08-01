<?php 
class ControllerExtensionTotalXfee extends Controller { 
	private $error = array(); 
	 
	public function index() { 
		$this->language->load('extension/total/xfee');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('total_xfee', $this->request->post);
		
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true));
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_none'] = $this->language->get('text_none');
		
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_cost'] = $this->language->get('entry_cost');
		$data['entry_cost'] = $this->language->get('entry_cost');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_tax'] = $this->language->get('entry_tax');
		$data['text_edit'] = $this->language->get('text_edit');
		
		$data['tab_fee'] = $this->language->get('tab_fee');
		$data['tab_general'] = $this->language->get('tab_general');
		$data['text_all'] = $this->language->get('text_all');
		$data['entry_order_total'] = $this->language->get('entry_order_total');
		
		
		$data['entry_payment'] = $this->language->get('entry_payment');
		$data['entry_shipping'] = $this->language->get('entry_shipping');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_order_max_total'] = $this->language->get('entry_order_max_total');
					
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

   		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true)
			);
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/total/xfee', 'user_token=' . $this->session->data['user_token'], true)
			);
		
		$data['action'] = $this->url->link('extension/total/xfee', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true);

		if (isset($this->request->post['total_xfee_fees'])) {
			$data['total_xfee_fees'] = $this->request->post['total_xfee_fees'];
		} else {
			$data['total_xfee_fees'] = $this->config->get('total_xfee_fees');
		}	
		 
		if (isset($this->request->post['total_xfee_status'])) {
			$data['total_xfee_status'] = $this->request->post['total_xfee_status'];
		} else {
			$data['total_xfee_status'] = $this->config->get('total_xfee_status');
		}
		if (isset($this->request->post['xfee_sort_order'])) {
			$data['total_xfee_sort_order'] = $this->request->post['total_xfee_sort_order'];
		} else {
			$data['total_xfee_sort_order'] = $this->config->get('total_xfee_sort_order');
		}						

		$this->load->model('localisation/tax_class');
		
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		$this->load->model('localisation/geo_zone');
		
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$data['language_id']=$this->config->get('config_language_id');
		
		$shipping_mods=array();
		$xshipping_installed=false;
		$result=$this->db->query("select * from " . DB_PREFIX . "extension where type='shipping'");
		if($result->rows){
		  foreach($result->rows as $row){
		     $shipping_mods[$row['code']]=$this->getModuleName($row['code'],$row['type']);  
			 if($row['code']=='xshippingpro')$xshipping_installed=true;
		  }
		}
		
		$data['shipping_mods'] = $shipping_mods;

		if ($xshipping_installed) {
            
            /*For X-Shipping Pro*/
            $xshippingpro_methods=array();
            $this->load->model('extension/xshippingpro/xshippingpro');
            $xshippingpro= $this->model_extension_xshippingpro_xshippingpro->getData();
            foreach($xshippingpro as $single_method) {
                $no_of_tab = $single_method['tab_id'];
                $method_data = $single_method['method_data'];
                $method_data = @unserialize(@base64_decode($method_data));
                if(!is_array($method_data)) $method_data = array();

                if(!isset($method_data['name']))$method_data['name']=array();
                if(!is_array($method_data['name']))$method_data['name']=array();
                $method_name = (!isset($method_data['name'][$data['language_id']]) || !$method_data['name'][$data['language_id']]) ? 'Untitled Method '.$no_of_tab : $method_data['name'][$data['language_id']]; 
                $code = 'xshippingpro'.'.xshippingpro'.$no_of_tab;
                $xshippingpro_methods[$code]=$method_name;
            }


            $data['xshippingpro_methods'] = $xshippingpro_methods;
            /*End of X-shipping Pro*/
        }


		
		
		$payment_mods=array();
		$xpayment_methods = array();
		$xpayment_installed=false;
		$result=$this->db->query("select * from " . DB_PREFIX . "extension where type='payment'");
		if($result->rows){
		  foreach($result->rows as $row){
		     $payment_mods[$row['code']]=$this->getModuleName($row['code'],$row['type']); 
		     if($row['code']=='xpayment') $xpayment_installed=true; 
		  }
		}
		
		$data['payment_mods'] = $payment_mods;

		 /*For X-Payment*/
        if($xpayment_installed) {

            $this->load->model('extension/payment/xpayment');
            $xpayment= $this->model_extension_payment_xpayment->getData();
            foreach($xpayment as $single_method) {
                $no_of_tab = $single_method['tab_id'];
                $method_data = $single_method['method_data'];
                $method_data = @unserialize(@base64_decode($method_data));
                if(!is_array($method_data)) $method_data = array();

                if(!isset($method_data['name']))$method_data['name']=array();
                if(!is_array($method_data['name']))$method_data['name']=array();
                $method_name = (!isset($method_data['name'][$data['language_id']]) || !$method_data['name'][$data['language_id']]) ? 'Untitled Method '.$no_of_tab : $method_data['name'][$data['language_id']]; 
                $code = 'xpayment'.$no_of_tab;
                $xpayment_methods[$code]=$method_name;
            }
        }

        $data['xpayments'] = $xpayment_methods;
        /*End of X-Payment*/

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		 $this->response->setOutput($this->load->view('extension/total/xfee', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/xfee')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
	
	function getModuleName($code,$type)
	{
	   if(!$code) return '';
	   
	   $this->language->load('extension/'.$type.'/'.$code);
	   return $this->language->get('heading_title');
	}
}
?>