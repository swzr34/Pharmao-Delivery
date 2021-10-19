<?php 
namespace Pharmao\Delivery\Model;

use Magento\Framework\Model\AbstractModel;

class Delivery extends AbstractModel
{
    protected $storeScope;
    
     /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }
    
    public function getConfigData($key, $path = 'general') {
        $configValue = $this->scopeConfig->getValue('delivery_configuration/' . $path . '/' . $key, $this->storeScope);
        return $configValue;
    }
    
    public function getWeightUnit() {
        return $this->scopeConfig->getValue('general/locale/weight_unit', $this->storeScope);
    }
}