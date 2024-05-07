<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageViku\CategoryAttribute\Controller\Adminhtml\Category\Action;

use Magento\Backend\App\Action;

/**
 * Adminhtml catalog product action attribute update controller
 */
abstract class Attribute extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::update_attributes';

    /**
     * @var \MageViku\CategoryAttribute\Helper\Category\Edit\Action\Attribute
     */
    protected $_attributeHelper;

    /**
     * @param Action\Context $context
     * @param \MageViku\CategoryAttribute\Helper\Category\Edit\Action\Attribute $attributeHelper
     */
    public function __construct(
        Action\Context $context,
        \MageViku\CategoryAttribute\Helper\Category\Edit\Action\Attribute $attributeHelper
    ) {
        parent::__construct($context);
        $this->_attributeHelper = $attributeHelper;
    }

    /**
     * Validate selection of categories for mass update
     *
     * @return boolean
     */
    protected function _validateCategories()
    {
        $error = false;
        $categoryIds = $this->_attributeHelper->getCategoryIds();
        if (!is_array($categoryIds)) {
            $error = __('Please select categories for attributes update.');
        }
        if ($error) {
            $this->messageManager->addErrorMessage($error);
        }

        return !$error;
    }
}
