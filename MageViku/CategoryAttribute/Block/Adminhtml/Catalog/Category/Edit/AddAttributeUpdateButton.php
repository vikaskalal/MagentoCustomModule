<?php
/*
 *  MageViku
 *
 *  NOTICE OF LICENSE
 *
 * This source file is subject to the MageViku.com license that is
 * available through the world-wide-web at this URL:
 * http://MageViku.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   MageViku
 * @package    MageViku_CategoryAttribute
 * @copyright  Copyright (c) MageViku (http://www.MageViku.com/)
 * @license    http://www.MageViku.com/LICENSE-1.0.html
 *
 */

namespace MageViku\CategoryAttribute\Block\Adminhtml\Catalog\Category\Edit;

use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * class AddAttributeUpdateButton
 *
 * Add category attribute update button in category edit.
 */
class AddAttributeUpdateButton extends AbstractCategory implements ButtonProviderInterface
{
    /**
     * Get button data.
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $category = $this->getCategory();
        if ($category && $category->getId()) {
            return $this->getButton();
        }
        return [];
    }

    /**
     * Get button
     *
     * @return array
     */
    public function getButton(): array
    {
        return [
            'id' => 'cat_update_attribute',
            'label' => __('Update Attributes'),
            'on_click' => "setLocation('" . $this->getCatAttributeUpdateUrl() . "')",
            'class' => 'action-secondary action-event-edit',
            'sort_order' => 30
        ];
    }

    /**
     * Get AsLowAs Price Url
     *
     * @return string
     */
    public function getCatAttributeUpdateUrl(): string
    {
        return $this->getUrl(
            'categoryattribute/category/updatecategorylist/',
            [
                '_current' => true,
                '_query' => ['isAjax' => null]
            ]
        );
    }
}
