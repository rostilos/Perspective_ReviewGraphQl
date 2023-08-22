<?php
declare(strict_types=1);

namespace Perspective\ReviewGraphQl\Mapper;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;

/**
 * Converts the review data from review object to an associative array
 */
class ReviewsDataMapper
{
    /**
     * Mapping the review data
     *
     * @param DataObject $reviewItem
     * @param CustomerInterface|null $customerInfo
     * @return array
     */
    public function map(DataObject $reviewItem, ?CustomerInterface $customerInfo): array
    {
        return [
            'detail' => $reviewItem->getData('detail'),
            'created_at' => $reviewItem->getData('created_at'),
            'customer' => [
                'firstname' => $customerInfo ? $customerInfo->getFirstname() : null,
                'lastname' => $customerInfo ? $customerInfo->getLastname() : null
            ],
        ];
    }
}
