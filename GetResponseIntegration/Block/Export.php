<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Export
 * @package GetResponse\GetResponseIntegration\Block
 */
class Export extends \Magento\Framework\View\Element\Template
{
    public $stats;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Export constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager)
    {
        parent::__construct($context);

        $this->_objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function getCustomers()
    {
        $customers = $this->_objectManager->get('Magento\Customer\Model\Customer');
        return $customers->getCollection();
    }

    /**
     * @return mixed
     */
    public function getActiveCustoms()
    {
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection()->addFieldToFilter('active_custom', true);
    }

    /**
     * @return mixed
     */
    public function getDefaultCustoms()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection($storeId, 'id_shop');
    }

    /**
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->getClient()->getCampaigns(['sort' => ['name' => 'asc']]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCampaign($id)
    {
        return $this->getClient()->getCampaign($id);
    }

    /**
     * @return mixed
     */
    public function getAccountFromFields()
    {
        return $this->getClient()->getAccountFromFields();
    }

    /**
     * @param $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsSubject($lang)
    {
        return $this->getClient()->getSubscriptionConfirmationsSubject($lang);
    }

    /**
     * @param $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsBody($lang)
    {
        return $this->getClient()->getSubscriptionConfirmationsBody($lang);
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $settings->load($storeId, 'id_shop')->getData();
    }

    /**
     * @return mixed
     */
    public function getAutomations()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');
        return $settings->getCollection()
            ->addFieldToFilter('id_shop', $storeId);
    }

    /**
     * @param $category_id
     * @return mixed
     */
    public function getCategoryName($category_id)
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Model\Category');
        return $_categoryHelper->load($category_id)->getName();
    }

    /**
     * @return mixed
     */
    public function getStoreCategories()
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Helper\Category');
        $categories = $_categoryHelper->getStoreCategories(true, false, true);

        return $categories;
    }

    /**
     * @param $category \Magento\Catalog\Helper\Category|\Magento\Catalog\Model\Category
     */
    public function getSubcategories($category)
    {
        if ($category->hasChildren()) {
            $childrenCategories = $category->getChildren();
            foreach ($childrenCategories as $childrenCategory) {
                $string = '';
                for ($i = $childrenCategory->getLevel(); $i > 2; $i--) {
                    $string .= '-';
                }
                echo '<option value="' . $childrenCategory->getEntityId() . '"> ' .
                    $string . ' ' . $childrenCategory->getName() . '</option>';
                $this->getSubcategories($childrenCategory);
            }
        }
    }

    /**
     * @return array
     */
    public function getAutoresponders()
    {
        $params = ['query' => ['triggerType' => 'onday', 'status' => 'active']];
        $result = $this->getClient()->getAutoresponders($params);
        $autoresponders = [];

        if (!empty($result)) {
            foreach ($result as $autoresponder) {
                if (isset($autoresponder->triggerSettings->selectedCampaigns[0])) {
                    $autoresponders[$autoresponder->triggerSettings->selectedCampaigns[0]][] = [
                        'name' => $autoresponder->name,
                        'subject' => $autoresponder->subject,
                        'dayOfCycle' => $autoresponder->triggerSettings->dayOfCycle
                    ];
                }
            }
        }

        return $autoresponders;
    }

    /**
     * @return mixed
     */
    public function getStoreLanguage()
    {
        return $this->_scopeConfig->getValue('general/locale/code');
    }

    /**
     * @return bool|int
     */
    public function checkApiKey()
    {
        if (empty($this->getApiKey())) {
            return 0;
        }

        $response = $this->getClient()->ping();

        if (isset($response->accountId)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $model = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $model->load($storeId, 'id_shop')->getApiKey();
    }

    /**
     * @return GetResponseAPI3
     */
    public function getClient()
    {
        $settings = $this->getSettings();
        return new GetResponseAPI3($settings['api_key'], $settings['api_url'], $settings['api_domain']);
    }
}
