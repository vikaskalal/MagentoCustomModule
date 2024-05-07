<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageViku\CategoryAttribute\Helper\Category\Edit\Action;

use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection ;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Adminhtml catalog product action attribute update helper.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Attribute extends \Magento\Backend\Helper\Data
{

    /**
     * Array of same attributes for selected products
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $_attributes;

    /**
    * @var CategoryCollection
     */
    protected $_categoryFactory;

    /**
     * Selected Category for mass-update
     *
     * @var CategoryCollection
     */
    protected $_category;

    /**
     * Excluded from batch update attribute codes
     *
     * @var array
     */
    protected $_excludedAttributes = ['url_key','url_path'];

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
    * @param \Magento\Framework\App\Helper\Context $context
    * @param \Magento\Framework\App\Route\Config $routeConfig
    * @param \Magento\Framework\Locale\ResolverInterface $locale
    * @param \Magento\Backend\Model\UrlInterface $backendUrl
    * @param \Magento\Backend\Model\Auth $auth
    * @param \Magento\Backend\App\Area\FrontNameResolver $frontNameResolver
    * @param \Magento\Framework\Math\Random $mathRandom
    * @param \Magento\Eav\Model\Config $eavConfig
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Backend\Model\Session $session
    *  @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Route\Config $routeConfig,
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\App\Area\FrontNameResolver $frontNameResolver,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollection,
        \Magento\Backend\Model\Session $session
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryCollection;
        $this->_session = $session;
        parent::__construct($context, $routeConfig, $locale, $backendUrl, $auth, $frontNameResolver, $mathRandom);
    }

    /**
     * Return category collection with selected categories
     *
     * category collection didn't load
     *
     * @return CollectionFactory
     */
    public function getCategories()
    {
        if ($this->_category === null) {
            $categoryIds = $this->getCategoryIds();

            if (!is_array($categoryIds)) {
                $categoryIds = [0];
            }

            $this->_category = $this->_categoryFactory->create()->setStoreId(
                $this->getSelectedStoreId()
            )->addIdFilter(
                $categoryIds
            );
        }

        return $this->_category;
    }

    /**
     * Return selected store id from request
     *
     * @return integer
     */
    public function getSelectedStoreId()
    {
        return (int)$this->_getRequest()->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
    }

    /**
     * Return collection of same attributes for selected category without unique
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->_eavConfig->getEntityType(
                \Magento\Catalog\Model\Category::ENTITY
            )->getAttributeCollection()->addIsNotUniqueFilter()->setInAllAttributeSetsFilter(
                $this->getCategorySetIds()
            );

            if ($excludedAttributes = $this->getExcludedAttributes()) {
                $this->_attributes->addFieldToFilter('attribute_code', ['nin' => $excludedAttributes]);
            }
            $this->_attributes->getSelect()->order('is_user_defined', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
            $this->_attributes->getSelect()->order('main_table.attribute_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        }
        return $this->_attributes;
    }

    /**
     * Return array of attribute sets by selected categories
     *
     * @return array
     */
    public function getCategorySetIds()
    {
        $categories = $this->getCategories();
        $attributeSetIds = [];
        if ($categories && $categories->getSize()){
            // Get attribute set IDs for each category.
            foreach ($categories as $category) {
                if (!isset($attributeSetIds[$category->getAttributeSetId()])){
                    $attributeSetIds[$category->getAttributeSetId()] = $category->getAttributeSetId();
                }
            }
        }
        return $attributeSetIds;
    }

    /**
     * Gets website id.
     *
     * @param int $storeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreWebsiteId($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getWebsiteId();
    }

    /**
     * Set array of selected category ids
     *
     * @param array $categoryIds
     *
     * @return void
     */
    public function setCategoryIds($categoryIds)
    {
        $this->_session->setCategoryIds($categoryIds);
    }

    /**
     * Return array of selected category ids from post form.
     *
     * @return array|null
     */
    public function getCategoryIds()
    {
        return $this->_session->getCategoryIds();
    }

    /**
     * Retrieve excluded attributes.
     *
     * @return array
     */
    public function getExcludedAttributes(): array
    {
        return $this->_excludedAttributes;
    }
}
