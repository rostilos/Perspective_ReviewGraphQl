<?php

declare(strict_types=1);

namespace Perspective\ReviewGraphQl\Model\Review;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Perspective\Review\Model\ReviewFactory;

/**
 * Adding a review to specific product
 */
class AddReviewToProduct
{

    /**
     * @var ReviewFactory
     */
    private ReviewFactory $reviewFactory;

    /**
     * @param ReviewFactory $reviewFactory
     */
    public function __construct(
        ReviewFactory $reviewFactory,
    ) {
        $this->reviewFactory = $reviewFactory;
    }

    /**
     * Add review to product
     *
     * @param array $data
     * @param int $productId ,
     * @param int|null $customerId
     *
     * @return array
     *
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(array $data, int $productId, ?int $customerId): array
    {
        $review = $this->reviewFactory->create()->setData($data);
        $review->unsetData('review_id');

        try {
            $review->setProductId($productId)
                ->setCreatedAt(date('Y-m-d H:i:s'))
                ->setUserId($customerId)
                ->save();

            return ['review' => $review, 'message' => 'Review Added'];
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__('Something went wrong.'));
        }
    }
}
