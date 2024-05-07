<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageViku\CategoryAttribute\Controller\Adminhtml\Category\Action\Attribute;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Catalog\Model\Product\Filter\DateTime as DateTimeFilter;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageViku\CategoryAttribute\Controller\Adminhtml\Category\Action\Attribute as AttributeAction;

/**
 * Class responsible for saving product attributes.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends AttributeAction implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Bulk\BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var int
     */
    private $bulkSize;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var DateTimeFilter
     */
    private $dateTimeFilter;

    /**
     * @param Action\Context $context
     * @param \MageViku\CategoryAttribute\Helper\Category\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operartionFactory
     * @param \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param int $bulkSize
     * @param TimezoneInterface|null $timezone
     * @param Config|null $eavConfig
     * @param CategoryFactory|null $categoryFactory
     * @param DateTimeFilter|null $dateTimeFilter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Action\Context $context,
        \MageViku\CategoryAttribute\Helper\Category\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement,
        \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operartionFactory,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        int $bulkSize = 100,
        TimezoneInterface $timezone = null,
        Config $eavConfig = null,
        CategoryFactory $categoryFactory = null,
        ?DateTimeFilter $dateTimeFilter = null
    ) {
        parent::__construct($context, $attributeHelper);
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operartionFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->userContext = $userContext;
        $this->bulkSize = $bulkSize;
        $this->timezone = $timezone ?: ObjectManager::getInstance()
            ->get(TimezoneInterface::class);
        $this->eavConfig = $eavConfig ?: ObjectManager::getInstance()
            ->get(Config::class);
        $this->categoryFactory = $categoryFactory ?? ObjectManager::getInstance()->get(CategoryFactory::class);
        $this->dateTimeFilter = $dateTimeFilter ?? ObjectManager::getInstance()->get(DateTimeFilter::class);
    }

    /**
     * Update category attributes
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_validateCategories()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/category/', ['_current' => true]);
        }

        /* Collect Data */
        $attributesData = $this->getRequest()->getParam('attributes', []);

        $storeId = $this->_attributeHelper->getSelectedStoreId();
        $websiteId = $this->_attributeHelper->getStoreWebsiteId($storeId);
        $categoryIds = $this->_attributeHelper->getCategoryIds();
        $attributesData = $this->sanitizeCategoryAttributes($attributesData);;
        try {
            $this->validateCategoryAttributes($attributesData);
            $this->publish($attributesData, $storeId, $websiteId, $categoryIds);
            $this->messageManager->addSuccessMessage(__('Message is added to queue'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the category(s) attributes.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath('catalog/category/', ['_current' => true]);
    }

    /**
     * Sanitize Category attributes
     *
     * @param array $attributesData
     *
     * @return array
     */
    private function sanitizeCategoryAttributes($attributesData)
    {
        foreach ($attributesData as $attributeCode => $value) {
            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Category::ENTITY, $attributeCode);

            if (!$attribute->getAttributeId()) {
                unset($attributesData[$attributeCode]);
                continue;
            }

            if ($attribute->getBackendType() === 'datetime') {
                $attributesData[$attributeCode] = $this->filterDate(
                    $value,
                    $attribute->getFrontendInput() === 'datetime'
                );
            } elseif ($attribute->getFrontendInput() === 'multiselect') {
                // Check if 'Change' checkbox has been checked by admin for this attribute
                $isChanged = (bool)$this->getRequest()->getPost('toggle_' . $attributeCode);
                if (!$isChanged) {
                    unset($attributesData[$attributeCode]);
                    continue;
                }
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $attributesData[$attributeCode] = $value;
            }
        }
        return $attributesData;
    }

    /**
     * Get the date and time value in internal format and timezone
     *
     * @param string $value
     * @param bool $isDatetime
     * @return string|null
     * @throws LocalizedException
     */
    private function filterDate(string $value, bool $isDatetime = false): ?string
    {
        $date = !empty($value) ? $this->dateTimeFilter->filter($value) : null;
        if ($date && $isDatetime) {
            $date = $this->timezone->convertConfigTimeToUtc($date, DateTime::DATETIME_PHP_FORMAT);
        }

        return $date;
    }

    /**
     * Validate Category attributes data.
     *
     * @param array $attributesData
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateCategoryAttributes(array $attributesData): void
    {
        $category = $this->categoryFactory->create();
        $category->setData($attributesData);

        foreach (array_keys($attributesData) as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Category::ENTITY, $attributeCode);
            $attribute->getBackend()->validate($category);
        }
    }

    /**
     * Schedule new bulk
     *
     * @param array $attributesData
     * @param int $storeId
     * @param int $websiteId
     * @param array $categoryIds
     * @throws LocalizedException
     *
     * @return void
     */
    private function publish(
        $attributesData,
        $storeId,
        $websiteId,
        $categoryIds
    ):void {
        $categoryIdsChunks = array_chunk($categoryIds, $this->bulkSize);
        $bulkUuid = $this->identityService->generateId();
        $bulkDescription = __('Update attributes for ' . count($categoryIds) . ' selected categories');
        $operations = [];
        foreach ($categoryIdsChunks as $categoryIdsChunk) {
            if ($attributesData) {
                $operations[] = $this->makeOperation(
                    'Update Category attributes',
                    'category_action_attribute.update',
                    $attributesData,
                    $storeId,
                    $websiteId,
                    $categoryIdsChunk,
                    $bulkUuid
                );
            }
        }

        if (!empty($operations)) {
            $result = $this->bulkManagement->scheduleBulk(
                $bulkUuid,
                $operations,
                $bulkDescription,
                $this->userContext->getUserId()
            );
            if (!$result) {
                throw new LocalizedException(
                    __('Something went wrong while processing the request.')
                );
            }
        }
    }

    /**
     * Make asynchronous operation
     *
     * @param string $meta
     * @param string $queue
     * @param array $dataToUpdate
     * @param int $storeId
     * @param int $websiteId
     * @param array $categoryIds
     * @param int $bulkUuid
     *
     * @return OperationInterface
     */
    private function makeOperation(
        $meta,
        $queue,
        $dataToUpdate,
        $storeId,
        $websiteId,
        $categoryIds,
        $bulkUuid
    ): OperationInterface {
        $dataToEncode = [
            'meta_information' => $meta,
            'category_ids' => $categoryIds,
            'store_id' => $storeId,
            'website_id' => $websiteId,
            'attributes' => $dataToUpdate
        ];
        $data = [
            'data' => [
                'bulk_uuid' => $bulkUuid,
                'topic_name' => $queue,
                'serialized_data' => $this->serializer->serialize($dataToEncode),
                'status' => \Magento\Framework\Bulk\OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];

        return $this->operationFactory->create($data);
    }
}
