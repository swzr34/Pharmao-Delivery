<?php

namespace Pharmao\Delivery\Observer;

class OrderChange implements \Magento\Framework\Event\ObserverInterface
{
    protected $scopeConfig;
    
    public function __construct(\Magento\Framework\HTTP\Client\Curl $curl, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, array $data = []) 
    {
        $this->_curl = $curl;
        $this->scopeConfig = $scopeConfig;
    }
    
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $assignment_code = random_int(1000000000, 9999999999);
        $order = $observer->getEvent()->getOrder();
        
        $shippingAddress = $order->getShippingAddress();
        $street_data = $shippingAddress->getStreet();
        $street_0 = isset($street_data[0]) ? $street_data[0] : '';
        $street_1 = isset($street_data[1]) ? $street_data[1] : '';
        $postCode = $shippingAddress->getPostCode();
        $city = $shippingAddress->getCity();
        $full_address = $street_0 . " " . $street_1 . ", " . $postCode . " ". $city . ", " . "France";
        $access_token = "Bearer " . $this->scopeConfig->getValue('delivery_configuration/general/field_hide', $storeScope);
        $config_status = $this->scopeConfig->getValue('delivery_configuration/general/pharmao_delivery_active_status', $storeScope);
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/status-updated.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        // echo "Config Status == " . $config_status;
        // echo "State == " . $order->getState();
        // echo "Status == " . $order->getStatus();
        // die;

        $logger->info('status : ' . $config_status);
        $logger->info('status : ' . $order->getState());
        
        $data = array (
          'job' => 
          array (
            'assignment_code' => $assignment_code,
            'transport_type' => 'Bike',
            'package_type' => 'small',
            'package_description' => '',
            'comment' => 'this is a test comment',
            'is_within_one_hour' => 1,
            'pickups' => 
            array (
              0 => 
              array (
                'comment' => 'Rentrez dans la pharmacie, allez au comptoir et demander la commande Pharmao Nom: ' .$order->getCustomerFirstname() . " " . $order->getCustomerLastname(),
                'contact' => 
                array (
                  'firstname' => $this->scopeConfig->getValue('delivery_configuration/global_settings/firstname', $storeScope),
                  'phone' => $this->scopeConfig->getValue('delivery_configuration/global_settings/phone', $storeScope),
                ),
                'address' => $this->scopeConfig->getValue('delivery_configuration/global_settings/address', $storeScope) . ", ". $this->scopeConfig->getValue('delivery_configuration/global_settings/postcode', $storeScope) . " " . $this->scopeConfig->getValue('delivery_configuration/global_settings/city', $storeScope) . ", France",
              ),
            ),
            'dropoffs' => 
            array (
              0 => 
              array (
                'comment' => $street_1,
                'address' => $full_address,
                'contact' => 
                array (
                  'firstname' => $order->getCustomerFirstname(),
                  'phone' => $order->getShippingAddress()->getTelephone(),
                  'lastname' => $order->getCustomerLastname(),
                  'email' => $order->getCustomerEmail(),
                ),
              ),
            ),
          ),
        );
        
        $logger->info('data : ' . print_r($data, true));
        $data_json = json_encode($data);
        
        
        if ($order->getState() == $config_status) {
            $validate_url = 'https://delivery-sandbox.pharmao.fr/v1/job/validate';
            $headers = ["Content-Type" => "application/json", "Authorization" => $access_token];
            $this->_curl->setHeaders($headers);
            $this->_curl->post($validate_url, $data_json);
            $response = $this->_curl->getBody();
            
            if ($response) {
                $data = json_decode($response);
                $logger->info('response : ' . $data->data->is_valid);
            } else {
                $data = '';
            }
            
            if (isset($data->data->is_valid) && $data->data->is_valid == true) {
                // echo "Create New Job"; die;
                $creat_job_url = 'https://delivery-sandbox.pharmao.fr/v1/jobs';
                $this->_curl->setHeaders($headers);
                $this->_curl->post($creat_job_url, $data_json);
                $job_response = $this->_curl->getBody();
                $logger->info('create job response : ' . $job_response);   
            }
        }
        
        return $this;
    }
}
?>