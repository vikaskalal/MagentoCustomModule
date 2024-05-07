<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageViku\LoginAuthentication\Model\ResourceModel\LoginAuthenticationOtp;

/**
 * Class Collection
 * @package Cinovic\Otplogin\Model\ResourceModel\Otp
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageViku\LoginAuthentication\Model\LoginAuthenticationOtp', 'MageViku\LoginAuthentication\Model\ResourceModel\LoginAuthenticationOtp');
    }
}