<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageViku\LoginAuthentication\Model\Exception;

use MageViku\LoginAuthentication\Api\Exception\NotificationExceptionInterface;

/**
 * @inheritDoc
 */
class NotificationException extends \RuntimeException implements NotificationExceptionInterface
{

}