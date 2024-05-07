<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml catalog category edit action attributes update tab block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
declare(strict_types=1);

namespace MageViku\CategoryAttribute\Block\Adminhtml\Catalog\Category\Edit\Action\Attribute\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Block\Adminhtml\Form;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image;
use MageViku\CategoryAttribute\Helper\Category\Edit\Action\Attribute;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Attributes tab block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Attributes extends Form implements TabInterface
{
    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var Attribute
     */
    protected $_attributeAction;

    /**
     * @var array
     */
    private $excludeFields;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CategoryFactory $categoryFactory
     * @param Attribute $attributeAction
     * @param array $data
     * @param array|null $excludeFields
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CategoryFactory $categoryFactory,
        Attribute $attributeAction,
        array $data = [],
        array $excludeFields = null,
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_attributeAction = $attributeAction;
        $this->_categoryFactory = $categoryFactory;
        $this->excludeFields = $excludeFields ?: [];

        parent::__construct($context, $registry, $formFactory, $data);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Prepares form
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm(): void
    {
        $this->setFormExcludedFieldList($this->excludeFields);
        $this->_eventManager->dispatch(
            'adminhtml_catalog_cat_form_prepare_excluded_field_list',
            ['object' => $this]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('fields', ['legend' => __('Attributes')]);
        $attributes = $this->getAttributes();
        /**
         * Initialize category object as form property
         * for using it in elements generation
         */
        $form->setDataObject($this->_categoryFactory->create());
        $this->_setFieldset($attributes, $fieldset, $this->getFormExcludedFieldList());
        $form->setFieldNameSuffix('attributes');
        $this->setForm($form);
    }

    /**
     * Retrieve attributes for category mass update
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAttributes()
    {
        return $this->_attributeAction->getAttributes()->getItems();
    }

    /**
     * Additional element types for category attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return [
            'image' => Image::class,
            'boolean' => Boolean::class
        ];
    }

    /**
     * Custom additional element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        // Add name attribute to checkboxes that correspond to multiselect elements
        $nameAttributeHtml = $element->getExtType() === 'multiple' ? 'name="' . $element->getId() . '_checkbox"' : '';
        $elementId = $element->getId();
        $dataAttribute = "data-disable='{$elementId}'";
        $dataCheckboxName = "toggle_{$elementId}";
        $checkboxLabel = __('Change');
        // @codingStandardsIgnoreStart
        $html = <<<HTML
<span class="attribute-change-checkbox">
    <input type="checkbox" id="$dataCheckboxName" name="$dataCheckboxName"
           class="checkbox" $nameAttributeHtml $dataAttribute />
    <label class="label" for="$dataCheckboxName">
        {$checkboxLabel}
    </label>
</span>
HTML;

        $html .= /* @noEscape */ $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            "toogleFieldEditMode(this, '{$elementId}')",
            "#". $dataCheckboxName
        );

        // @codingStandardsIgnoreEnd
        return $html;
    }

    /**
     * Returns tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Attributes');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Attributes');
    }

    /**
     * Can show tab in tabs
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab not hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
