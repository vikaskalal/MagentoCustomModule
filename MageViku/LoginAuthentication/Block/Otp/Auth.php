<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageViku\LoginAuthentication\Block\Otp;

use Magento\Backend\Block\Template;

/**
 * @api
 */
class Auth extends Template
{
    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $this->jsLayout['components']['tfa-otp-auth']['postUrl'] =
            $this->getUrl('*/*/authpost');

        $this->jsLayout['components']['tfa-otp-auth']['successUrl'] =
            $this->getUrl($this->_urlBuilder->getStartupPageUrl());

        return parent::getJsLayout();
    }
}