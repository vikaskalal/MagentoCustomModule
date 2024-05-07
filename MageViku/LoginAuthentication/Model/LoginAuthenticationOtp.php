<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageViku\LoginAuthentication\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Otp
 * @package MageViku\LoginAuthentication\Model
 */
class LoginAuthenticationOtp extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageViku\LoginAuthentication\Model\ResourceModel\LoginAuthenticationOtp');
    }
}