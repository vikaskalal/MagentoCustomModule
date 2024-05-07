<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageViku\CategoryAttribute\Block\Adminhtml\Catalog\Category\Edit\Action\Attribute;

/**
 * Adminhtml catalog product edit action attributes update tabs block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('cat_attributes_update_tabs');
        $this->setDestElementId('cat-attributes-edit-form');
        $this->setTitle(__('Category Information'));
    }
}
