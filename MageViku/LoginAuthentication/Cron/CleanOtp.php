<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageViku\LoginAuthentication\Cron;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class CleanOtp
{
    protected ResourceConnection $connection;
    private LoggerInterface $logger;

    /**
     * @param ResourceConnection $connection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $connection,
        LoggerInterface    $logger
    )
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * Delete old row of stored otp
     *
     * @return $this|int
     */
    public function execute()
    {
        try {
            $connection = $this->connection->getConnection();
            $tableName = $connection->getTableName('login_authentication_otp');
            $whereConditions = 'created_at < NOW() - INTERVAL 10 DAY';

            $deletedRows = $connection->delete($tableName, $whereConditions);
            $this->logger->info(__('Deleted login_authentication_otp row %1',$deletedRows));
            return $deletedRows;
        } catch (\Exception $e){
            $this->logger->warning(__('Unable to delete otp data %1',$e->getMessage()));
        }
        return $deletedRows;
    }
}