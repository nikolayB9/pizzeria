<?php

namespace App\DTO\Admin\Order;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginatedOrderListDto
{
    /** @param OrderListItemDto[] $list */
    public function __construct(
        public array             $list,
        public PaginationMetaDto $meta,
    )
    {
    }

    /**
     * Создаёт DTO из Laravel-пагинатора.
     *
     * @param LengthAwarePaginator $paginator Пагинатор с коллекцией заказов.
     *
     * @return self
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            list: OrderListItemDto::collection($paginator->getCollection()),
            meta: PaginationMetaDto::fromPaginator($paginator),
        );
    }
}
