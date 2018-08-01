<?php
class ModelExtensionTotalXfee extends Model {

	public function getTotal($total) {
	
	    $this->language->load('extension/total/xfee');
		
		$shipping_method=isset($this->session->data['shipping_method']['code'])?$this->session->data['shipping_method']['code']:'';
		$payment_method=isset($this->session->data['payment_method']['code'])?$this->session->data['payment_method']['code']:'';
		
		if(isset($this->session->data['default']['shipping_method']['code'])) $shipping_method = $this->session->data['default']['shipping_method']['code'];
		if(isset($this->session->data['default']['payment_method']['code'])) $payment_method = $this->session->data['default']['payment_method']['code'];
		
		$order_info='';
        if(isset($this->session->data['order_id'])){
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        }
        
        if(isset($this->request->get['order_id'])){
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->request->get['order_id']);
        }
        
        
       /*
	   if($order_info){
            $currency_code=$order_info['currency_code']; 
        } */
		
		/* For manual order insertion */
		if(isset($_POST['payment_code']) && !empty($_POST['payment_code']))$payment_method=$_POST['payment_code'];
		if(isset($_POST['shipping_code']) && !empty($_POST['shipping_code']))$shipping_method=$_POST['shipping_code'];
		
		
		$address = array();
		if(isset($this->session->data['shipping_address'])) $address = $this->session->data['shipping_address'];
		
		if(!isset($address['country_id'])) $address['country_id'] = 0;
		if(!isset($address['zone_id'])) $address['zone_id'] = 0;

		$total_xfee_fees = $this->config->get('total_xfee_fees');

		if (!is_array($total_xfee_fees) || !$total_xfee_fees) return array(); 

		$default_order = (int)$this->config->get('sub_total_sort_order')+1;
		
		
		if ($this->cart->getSubTotal()) {
			
		 	
		  for($i=1;$i<=12;$i++) {
	           $xfee_total=(float)$total_xfee_fees['total'.$i];
		       if(empty($xfee_total))$xfee_total=0;
		       
		       $xfee_total_max=(float)$total_xfee_fees['total_max'.$i];
		       
			   
			   if(!$total_xfee_fees['name'.$i]) continue;
			   if($xfee_total>$this->cart->getSubTotal()) continue;
			   if($xfee_total_max && $xfee_total_max<$this->cart->getSubTotal()) continue;
			   
			  
			   if($total_xfee_fees['payment'.$i] && $total_xfee_fees['payment'.$i]!=$payment_method) continue;
			   
			   if($total_xfee_fees['shipping'.$i] && $total_xfee_fees['shipping'.$i].'.'.$total_xfee_fees['shipping'.$i]!=$shipping_method && $total_xfee_fees['shipping'.$i]!=$shipping_method) continue;
				
               if($total_xfee_fees['geo_zone_id'.$i] && $address) {
			      
                   $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id='".(int)$total_xfee_fees['geo_zone_id'.$i]."' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')"); 
                   if ($query->num_rows==0) continue;
                                       
                }
	          
				$tax_vat=0;
				if ($total_xfee_fees['tax_class_id'.$i]) {
					$tax_rates = $this->tax->getRates($total_xfee_fees['cost'.$i], $total_xfee_fees['tax_class_id'.$i]);
					
					foreach ($tax_rates as $tax_rate) {
						if (!isset($total['taxes'][$tax_rate['tax_rate_id']])) {
							$total['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
							$tax_vat+=$tax_rate['amount'];
						} else {
							$total['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
							$tax_vat+=$tax_rate['amount'];
						}
					}
				}
				
				$total['totals'][] = array( 
					'code'       => 'xfee',
					'title'      => $total_xfee_fees['name'.$i],
					'value'      => $total_xfee_fees['cost'.$i],
					'sort_order' => !$total_xfee_fees['sort_order'.$i] ? $default_order : (int)$total_xfee_fees['sort_order'.$i]
				);
				
				$total['total'] += $total_xfee_fees['cost'.$i];
		  
		   }
		}
	}
}
?>