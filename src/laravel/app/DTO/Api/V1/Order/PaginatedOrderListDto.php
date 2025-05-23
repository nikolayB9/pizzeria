<?php

namespace App\DTO\Api\V1\Order;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginatedOrderListDto
{
    /** @param OrderListItemDto[] $data */
    /** @param array<string, mixed> $meta */
    public function __construct(
        public array $data,
        public array $meta,
    )
    {
    }

    /**
     * Создаёт DTO из Laravel-пагинатора.
     *
     * @param LengthAwarePaginator $paginator Пагинатор с заказами пользователя.
     *
     * @return self
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            data: OrderListItemDto::collection($paginator->getCollection()),
            meta: PaginationMetaDto::fromPaginator($paginator)->toArray(),
        );
    }
}
