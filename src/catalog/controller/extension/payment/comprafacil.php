<?php
class ControllerExtensionPaymentComprafacil extends Controller {
    
    private $_reference = "";
    private $_entity = "";
    private $_value = "";
    private $_cferror = "";
    
    private $cf_username;
    private $cf_password;
    private $cf_mode;
    private $cf_entity;
    
    private $key;
    private $order;
    

    public function index() {
        
        $data['text_loading'] = $this->language->get('text_loading');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['continue'] = $this->url->link('checkout/success');
    
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/comprafacil.tpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/payment/comprafacil.tpl', $data);
        } else {
            return $this->load->view('extension/payment/comprafacil.tpl', $data);
        }

    }
    
    public function confirm() {
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `comprafacil` (`id` int(11) NOT NULL AUTO_INCREMENT,`reference` text NOT NULL,`entity` text NOT NULL,`value` float(10,2) NOT NULL,`status` tinyint(1) DEFAULT '0',`key` text,`orderID` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;");
        $this->order = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$this->session->data['order_id'] . "'");
        
        $this->load->model('checkout/order');
        $data = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $this->order->row['total'] = $data['total'];   
        // Call Comprafacil reference - BEGIN
        if (!class_exists('soapclient')){
            ControllerExtensionPaymentComprafacil::CallWithoutSoap(); 
        }else{
            ControllerExtensionPaymentComprafacil::CallWithSoap();     
        }
        // Call Comprafacil reference - END
        
        // BACKUP QUERY
        $this->db->query("INSERT INTO comprafacil (`reference`, `entity`, `value`, `status`, `key`, `orderID`) VALUES ('".$this->_reference."', '".$this->_entity."', ".$this->_value.", 0, '".$this->key."', ".$this->session->data["order_id"].")");
        /* EMAIL IT
        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');             
        $mail->setTo($this->order->row['email']);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name'));
        $subject = "Pedido de Compra";
        $message = "TESTE"; 
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
        $mail->send();*/
        //$this->load->model('checkout/order');

        if ($this->session->data['payment_method']['code'] == 'comprafacil') {
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);
        }

    }
    
    function CallWithSoap(){ 
        global $order;
        global $log;
        try 
        {
            
            $this->key = md5(rand(0,10000).time());
            $origem = HTTP_SERVER.'comprafacil.php?order='.$this->key;

            $this->cf_username = $this->config->get('comprafacil_username');
            $this->cf_password = $this->config->get('comprafacil_password');
            $this->cf_mode = $this->config->get('comprafacil_mode');
            $this->cf_entity = $this->config->get('comprafacil_entity');

            if($this->cf_mode == 1){
                if($this->cf_entity=="10241"){
                    $wsURL = "https://hm.comprafacil.pt/SIBSClickTESTE/webservice/ClicksmsV4.asmx?WSDL";    
                }else{
                    $wsURL = "https://hm.comprafacil.pt/SIBSClick2TESTE/webservice/ClicksmsV4.asmx?WSDL";    
                }
            }else if($this->cf_entity=="10241"){
                $wsURL = "https://hm.comprafacil.pt/SIBSClick/webservice/ClicksmsV4.asmx?WSDL";
            }else if($this->cf_entity=="11249"){
                $wsURL = "https://hm.comprafacil.pt/SIBSClick2/webservice/ClicksmsV4.asmx?WSDL";
            }
            
            $order_total = $this->order->row['total'];
            $order_total = number_format($this->order->row['total'],2,".","");
            
            $parameters = array(
                "origem" => $origem,
                "IDCliente" => $this->cf_username,
                "password" => $this->cf_password,
                "valor" => $order_total,
                "informacao" => $this->order->row['shipping_address_1'],
                "nome" => "",
                "morada" => "",
                "codPostal" => "",
                "localidade" => "l",
                "NIF" => "",
                "RefExterna" => "",
                "telefoneContacto" => "",
                "email" => $this->order->row['email'],
                "IDUserBackoffice" => -1
                );


            $client = new SoapClient($wsURL);
            $res = $client->SaveCompraToBDValor2 ($parameters); 


            if ($res->SaveCompraToBDValor2Result)
            {
                $this->_entity = $res->entidade;
                $this->_value = $res->valorOut;
                $this->_reference = $res->referencia;
                $this->_cferror = "";
                return true;
            }
            else
            {
                $this->_cferror = $res->error;
                return false;
            }
        }
        catch (Exception $e){
            $this->_cferror = $e->getMessage();
            return false;
        }

  } 

    function CallWithoutSoap(){
        global $order;
        global $log;
        $this->key = md5(rand(0,10000).time());
        $origem = HTTP_SERVER.'comprafacil.php?order='.$this->key;
        $nusoapath = DIR_APPLICATION.'nusoap/lib/nusoap.php';
        require_once $nusoapath;

        $IDUserBackoffice="-1";

        $action='http://hm.comprafacil.pt/SIBSClick/webservice/SaveCompraToBDValor2';
        if($this->cf_mode == 1){
            if($this->cf_entity=="10241"){
                $serverpath = "https://hm.comprafacil.pt/SIBSClickTESTE/webservice/ClicksmsV4.asmx?WSDL";    
            }else{
                $serverpath = "https://hm.comprafacil.pt/SIBSClick2TESTE/webservice/ClicksmsV4.asmx?WSDL";    
            }
        }else if($this->cf_entity=="10241"){
            $serverpath = "https://hm.comprafacil.pt/SIBSClick/webservice/ClicksmsV4.asmx?WSDL";
        }else if($this->cf_entity=="11249"){
            $serverpath = "https://hm.comprafacil.pt/SIBSClick2/webservice/ClicksmsV4.asmx?WSDL";
        }


        $client = new soapclient($serverpath);

        $order_total = $this->order->row['total'];
        $order_total = number_format($this->order->row['total'],2,".","");

        $msg=$client->serializeEnvelope('<SaveCompraToBDValor2 xmlns="http://hm.comprafacil.pt/SIBSClick/webservice/"><origem>'.$origem.'</origem><IDCliente>'.$this->cf_username.'</IDCliente><password>'.$this->cf_password.'</password><valor>'.$order_total.'</valor><informacao>'.$this->order->row['shipping_address_1'].'</informacao><nome></nome><morada></morada><codPostal></codPostal><localidade></localidade><NIF></NIF><RefExterna></RefExterna><telefoneContacto></telefoneContacto><email>'.$this->order->row['email'].'</email><IDUserBackoffice>'.$IDUserBackoffice.'</IDUserBackoffice></SaveCompraToBDValor2>','',array(),'document', 'literal');

        $response = $client->send($msg,$action);

        $result = $response['SaveCompraToBDValor2Result'];
        $res = false;

        if($result == "true"){
            $this->_reference=$response['referencia'];
            $this->_entity=$response['entidade'];
            $this->_value=$response['valorOut'];
            $this->_cferror=$response['error'];   
            $res = true;
        }else{
            $this->_cferror=$response['error'];

        }
        return $res;
    }
    
}
?>