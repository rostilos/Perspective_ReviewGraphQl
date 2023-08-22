<?php

declare(strict_types=1);

namespace Perspective\ReviewGraphQl\Model\Resolver;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Perspective\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Perspective\Review\Model\ConfigManager;
use Perspective\ReviewGraphQl\Mapper\ReviewsDataMapper;

/**
 * Create product review resolver
 */
class ProductReviewList implements ResolverInterface
{

    /**
     * @var ReviewsDataMapper
     */
    private ReviewsDataMapper $reviewsDataMapper;

    /**
     * @var ReviewCollectionFactory
     */
    private ReviewCollectionFactory $collectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var ConfigManager
     */
    private ConfigManager $configManager;

    /**
     * @param ReviewCollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ReviewsDataMapper $reviewsDataMapper
     * @param ConfigManager $configManager
     */
    public function __construct(
        ReviewCollectionFactory     $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        ReviewsDataMapper           $reviewsDataMapper,
        ConfigManager               $configManager,
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->reviewsDataMapper = $reviewsDataMapper;
        $this->configManager = $configManager;
    }

    /**
     * Resolve product review ratings
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array
     *
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException|LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ): array {
        if (!$this->configManager->isEnabled()) {
            throw new GraphQlAuthorizationException(__('Review module is disabled.'));
        }

        $productId = $args['product_id'];

        if (!$productId) {
            throw new GraphQlAuthorizationException(__('Product ID is missing.'));
        }

        $reviewsCollection = $this->getReviewsCollection($productId);
        $customerId = null;

        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customerId = (int)$context->getUserId();
        }

        $items = [];
        foreach ($reviewsCollection->getItems() as $item) {
            $reviewCustomerInfo = null;
            if ($customerId) {
                $reviewCustomerInfo = $this->getUserInfo($customerId);
            }
            $items[] = $this->reviewsDataMapper->map($item, $reviewCustomerInfo);
        }


        return ['items' => $items];
    }

    /**
     * Retrieve reviews collection for current product
     *
     * @param int $productId
     * @return AbstractCollection
     */
    public function getReviewsCollection(int $productId): AbstractCollection
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('product_id', $productId)
            ->setOrder('created_at', 'DESC');
    }

    /**
     *  Retrieve User Info
     *
     * @param int $customerId
     * @return CustomerInterface|null
     * @throws LocalizedException
     */
    public function getUserInfo(int $customerId): ?CustomerInterface
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $customer;
    }
}
