<?php

namespace App\DTO\Api\V1\Order;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationMetaDto
{
    public function __construct(
        public int     $current_page,
        public ?string $next_page_url,
        public ?string $prev_page_url,
    )
    {
    }

    /**
     * Создаёт DTO с метаданными пагинации из Laravel-пагинатора.
     *
     * @param LengthAwarePaginator $paginator Пагинатор с метаданными.
     *
     * @return self
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            current_page: $paginator->currentPage(),
            next_page_url: $paginator->nextPageUrl(),
            prev_page_url: $paginator->previousPageUrl(),
        );
    }

    /**
     * Преобразует DTO в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'current_page' => $this->current_page,
            'next_page_url' => $this->next_page_url,
            'prev_page_url' => $this->prev_page_url,
        ];
    }
}
