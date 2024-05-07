<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml catalog category action attribute update
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace MageViku\CategoryAttribute\Block\Adminhtml\Catalog\Category\Edit\Action;

use MageViku\CategoryAttribute\Helper\Category\Edit\Action\Attribute as ActionAttribute;

/**
 * @api
 * @since 100.0.2
 */
class Attribute extends \Magento\Backend\Block\Widget
{
    /**
     * Adminhtml catalog category edit action attribute
     *
     * @var ActionAttribute
     */
    protected $_helperActionAttribute = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ActionAttribute $helperActionAttribute
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ActionAttribute $helperActionAttribute,
        array $data = []
    ) {
        $this->_helperActionAttribute = $helperActionAttribute;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'back_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                    'categoryattribute/category/updatecategorylist',
                    ['store' => $this->getRequest()->getParam('store', 0)]
                ) . '\')',
                'class' => 'back'
            ]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Reset'),
                'onclick' => 'setLocation(\'' . $this->getUrl('categoryattribute/*/*', ['_current' => true]) . '\')',
                'class' => 'reset'
            ]
        );

        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#cat-attributes-edit-form']],
                ]
            ]
        );
    }

    /**
     * Retrieve block attributes update helper
     *
     * @return ActionAttribute|null
     */
    protected function _getHelper()
    {
        return $this->_helperActionAttribute;
    }

    /**
     * Retrieve back button html code
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve cancel button html code
     *
     * @return string
     */
    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve save button html code
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        $helper = $this->_helperActionAttribute;
        return $this->getUrl('*/*/action_attribute_save', ['store' => $helper->getSelectedStoreId()]);
    }

    /**
     * Get validation url
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/action_attribute_validate', ['_current' => true]);
    }
}
