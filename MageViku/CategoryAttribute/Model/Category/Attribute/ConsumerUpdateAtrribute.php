<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace MageViku\CategoryAttribute\Model\Category\Attribute;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\TemporaryStateExceptionInterface;
use Magento\Framework\Bulk\OperationInterface;

/**
 * Consumer for export message.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerUpdateAtrribute extends \Magento\Catalog\Model\Category
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Bulk\OperationManagementInterface
     */
    private $operationManagement;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Bulk\OperationManagementInterface $operationManagement,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        EntityManager $entityManager
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->operationManagement = $operationManagement;
        $this->entityManager = $entityManager;
    }

    /**
     * Process
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @return void
     */
    public function process(\Magento\AsynchronousOperations\Api\Data\OperationInterface $operation)
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            if ($data && !empty($data['category_ids']) && !empty($data['attributes'])){
                $this->updateAttributes($data['category_ids'], $data['attributes'], $data['store_id']);
            }
        } catch (\Zend_Db_Adapter_Exception $e) {
            $this->logger->critical($e->getMessage());
            if ($e instanceof \Magento\Framework\DB\Adapter\LockWaitException
                || $e instanceof \Magento\Framework\DB\Adapter\DeadlockException
                || $e instanceof \Magento\Framework\DB\Adapter\ConnectionException
            ) {
                $status = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
                $errorCode = $e->getCode();
                $message = $e->getMessage();
            } else {
                $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                $errorCode = $e->getCode();
                $message = __(
                    'Sorry, something went wrong during category attributes update. Please see log for details.'
                );
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            $status = ($e instanceof TemporaryStateExceptionInterface)
                ? OperationInterface::STATUS_TYPE_RETRIABLY_FAILED
                : OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __('Sorry, something went wrong during category attributes update. Please see log for details.');
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }

    /**
     * Execute
     *
     * @param array $data
     *
     * @return void
     */
    private function updateAttributes(array $categoryIds, array $attrData, $storeId): void
    {
        if (empty($categoryIds) && empty($attrData)) {
            return;
        }
        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($categoryIds);

        foreach ($categories as $category) {
            foreach ($attrData as $attributeCode => $value) {
                $category->setData($attributeCode, $value);
            }
            $category->setStoreId($storeId)->save();
        }
    }
}
