<?php
namespace GetResponse\GetResponseIntegration\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package GetResponse\GetResponseIntegration\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    CONST ENABLE    = 'getresponse_getresponseintegration/general/enable';
    CONST API_KEY   = 'getresponse_getresponseintegration/general/api_key';

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Context $context, ScopeConfigInterface $scopeConfig)
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getEnable()
    {
        return $this->_scopeConfig->getValue(self::ENABLE);
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->_scopeConfig->getValue(self::API_KEY);
    }
}