<?php

namespace App\DTO\Admin\Order;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationMetaDto
{
    /** @param array<int, string> $page_urls */
    public function __construct(
        public int     $current_page,
        public int     $last_page,
        public int     $per_page,
        public int     $total,
        public ?string $next_page_url,
        public ?string $prev_page_url,
        public array   $page_urls,
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
        $pageUrls = [];
        for ($i = 1; $i <= $paginator->lastPage(); $i++) {
            $pageUrls[$i] = $paginator->url($i);
        }

        return new self(
            current_page: $paginator->currentPage(),
            last_page: $paginator->lastPage(),
            per_page: $paginator->perPage(),
            total: $paginator->total(),
            next_page_url: $paginator->nextPageUrl(),
            prev_page_url: $paginator->previousPageUrl(),
            page_urls: $pageUrls,
        );
    }
}
