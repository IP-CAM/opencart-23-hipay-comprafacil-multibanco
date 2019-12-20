<?php 
class ModelExtensionPaymentComprafacil extends Model {
  	public function getMethod($address, $total) {
		$this->load->language('extension/payment/comprafacil');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('comprafacil_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
	
		if ($total > 2500) {
			return false;
		} elseif ($this->config->get('comprafacil_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('comprafacil_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => 'comprafacil',
        		'title'      => $this->language->get('text_title'),
				'terms' 	 => $this->language->get('text_terms'),
        		'sort_order' => $this->config->get('comprafacil_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
}
?>