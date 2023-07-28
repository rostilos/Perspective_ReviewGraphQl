<?php

declare(strict_types=1);

namespace Perspective\ReviewGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Perspective\ReviewGraphQl\Model\Review\AddReviewToProduct;

use Perspective\Review\Model\ConfigManager;

/**
 * Create product review resolver
 */
class CreateProductReview implements ResolverInterface
{

    /**
     * @var AddReviewToProduct
     */
    private $addReviewToProduct;


    /**
     * @var ConfigManager
     */
    private $configManager;


    /**
     * @param AddReviewToProduct $addReviewToProduct
     * @param ConfigManager $configManager
     */
    public function __construct(
        AddReviewToProduct $addReviewToProduct,
        ConfigManager      $configManager,
    )
    {

        $this->addReviewToProduct = $addReviewToProduct;
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
     * @return array[]|Value|mixed
     *
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    )
    {
        if (!$this->configManager->isEnabled()) {
            throw new GraphQlAuthorizationException(__('Creating product reviews are not currently available.'));
        }

        $input = $args['input'];
        $customerId = null;

        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customerId = (int)$context->getUserId();
        }

        if (!$customerId && !$this->configManager->isGuestReviewsAllowed()) {
            throw new GraphQlAuthorizationException(__('Guest customers aren\'t allowed to add product reviews.'));
        }

        $productId = $input['product_id'];
        $data = [
            'detail' => $input['detail'],
        ];


        return $this->addReviewToProduct->execute($data, $productId, $customerId);
    }
}
