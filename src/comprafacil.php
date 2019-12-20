<?php
// Version
define('VERSION', '2.3.0.0');

// Configuration
require_once('config.php');
   
// Install 
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');
// Application Classes
require_once(DIR_SYSTEM . 'library/cart/customer.php');
require_once(DIR_SYSTEM . 'library/cart/affiliate.php');
require_once(DIR_SYSTEM . 'library/cart/currency.php');
require_once(DIR_SYSTEM . 'library/cart/tax.php');
require_once(DIR_SYSTEM . 'library/cart/weight.php');
require_once(DIR_SYSTEM . 'library/cart/length.php');
require_once(DIR_SYSTEM . 'library/cart/cart.php');
require_once(DIR_APPLICATION . 'controller/extension/payment/comprafacil.php');
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Database 
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

// Settings
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0' OR store_id = '" . (int)$config->get('config_store_id') . "' ORDER BY store_id ASC");

foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], unserialize($setting['value']));
	}
}

//$order = mysql_real_escape_string($_REQUEST['order']);
$order = $_GET['order'];
if (!$order){
	echo 'error: order not found';
	die();
}

$cfLog = $db->query("SELECT * FROM comprafacil WHERE `key` = '" . $order . "'");

if ($cfLog->row['status'] == 1){
    echo 'error: status at 1';
    die();
}
if (!$cfLog->row['reference']){
    echo 'error: reference not found';
    die();
}

//VERIFY PAY - BEGIN
$username = $config->get('comprafacil_username');
$password = $config->get('comprafacil_password');
$debugMode = $config->get('comprafacil_mode');
$cfentity = $config->get('comprafacil_entity');

if (!class_exists('soapclient')){
    require_once DIR_APPLICATION.'nusoap/lib/nusoap.php';
    $action='http://hm.comprafacil.pt/SIBSClick/webservice/getInfoCompra';
    if($debugMode == 1){
        if($cfentity=="10241"){
            $serverpath = "https://hm.comprafacil.pt/SIBSClickTESTE/webservice/ClicksmsV4.asmx?WSDL";    
        }else{
            $serverpath = "https://hm.comprafacil.pt/SIBSClick2TESTE/webservice/ClicksmsV4.asmx?WSDL";    
        }
    }else if($cfentity=="10241"){
        $serverpath = "https://hm.comprafacil.pt/SIBSClick/webservice/ClicksmsV4.asmx?WSDL";
    }else if($cfentity=="11249"){
        $serverpath = "https://hm.comprafacil.pt/SIBSClick2/webservice/ClicksmsV4.asmx?WSDL";
    }

    $client = new soapclient($serverpath);

    $msg=$client->serializeEnvelope('<getInfoCompra xmlns="http://hm.comprafacil.pt/SIBSClick/webservice/"><IDCliente>'.$username.'</IDCliente><password>'.$password.'</password><referencia>'.$cfLog->row["reference"].'</referencia></getInfoCompra>','',array(),'document', 'literal');

    $response = $client->send($msg,$action);

    $result=$response['getInfoCompraResult'];

    if($result == "true"){
        if($response['pago'] == "true"){
            echo 'pago';
        }else{
            echo 'error: not payed';
            die();
        }
    }else{
        echo 'error: soap call';
        die();
    }
}else{
    try{
        if($debugMode == 1){
            if($cfentity=="10241"){
                $wsURL = "https://hm.comprafacil.pt/SIBSClickTESTE/webservice/ClicksmsV4.asmx?WSDL";    
            }else{
                $wsURL = "https://hm.comprafacil.pt/SIBSClick2TESTE/webservice/ClicksmsV4.asmx?WSDL";    
            }
        }else if($cfentity=="10241"){
            $wsURL = "https://hm.comprafacil.pt/SIBSClick/webservice/ClicksmsV4.asmx?WSDL";
        }else if($cfentity=="11249"){
            $wsURL = "https://hm.comprafacil.pt/SIBSClick2/webservice/ClicksmsV4.asmx?WSDL";
        }
        
        $parameters = array(
            "IDCliente" => $username,
            "password" => $password,
            "referencia" => $cfLog->row["reference"]
        );


        $client = new SoapClient($wsURL);
        $res = $client->getInfoCompra($parameters); 
        if ($res->getInfoCompraResult){
            if($res->pago == "true"){
                echo 'pago';
            }else{
                echo 'error: not payed';
                die();
            }
        }else{
            echo 'error: soap call';
            die();
        }
    }catch (Exception $e){
        echo 'error';
        die();
    }
}

//VERIFY PAY - END
$db->query("UPDATE comprafacil SET status = 1 WHERE `key` = '" . $order . "'");

$order_status_id = 2;
$order_id = $cfLog->row['orderID'];

$db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

$db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', date_added = NOW()");

?>