<?php

namespace Api\V1\Cart;

use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Helpers\UserHelper;
use Tests\Traits\AssertsDatabaseWithAuth;
use Tests\Traits\HasAuthContext;

class ClearCartTest extends AbstractApiTestCase
{
    use HasAuthContext, AssertsDatabaseWithAuth;

    protected const COUNT_CATEGORIES = 1;
    protected const COUNT_PRODUCTS = 1;
    protected const COUNT_VARIANTS = 3;

    protected Collection $variants;
    protected int $otherUserId;
    protected string $otherSessionId = 'other-session-id';

    protected function setUpTestContext(): void
    {
        $category = CategoryHelper::createCategoryOfType(self::COUNT_CATEGORIES);
        $product = ProductHelper::createProductsWithVariantsForCategories(
            $category,
            self::COUNT_PRODUCTS,
            self::COUNT_VARIANTS
        );

        $this->variants = $product->variants;
    }

    protected function getRoute(mixed $routeParameter = null): string
    {
        return '/api/v1/cart/clear';
    }

    protected function getMethod(): string
    {
        return 'delete';
    }

    protected function getResponse(string $authType,
                                   bool   $createItemsForCurrentUser = true,
                                   bool   $createItemsForOtherUsers = false): TestResponse
    {
        if ($authType === 'session') {
            $this->setSessionId();
            $auth = $this->getAuthField('session');
        } else {
            $this->setUserId();
            $auth = $this->getAuthField('user');
        }

        if ($createItemsForCurrentUser) {
            CartHelper::createFromVariantByIdentifier($this->variants, $auth, true);
        }

        if ($createItemsForOtherUsers) {
            $this->createCartItemsForOtherUsers();
        }

        $route = $this->getRoute();
        $method = $this->getMethod();

        if ($authType === 'session') {
            return $this->withCookie('laravel_session', $this->sessionId)
                ->$method($route, ['Accept' => 'application/json']);
        } else {
            return $this->$method($route, ['Accept' => 'application/json']);
        }
    }

    protected function checkSuccess(TestResponse $response, mixed $data = [], array $meta = [], int $status = 200): void
    {
        parent::checkSuccess($response, $data, $meta, $status);

        $variantIds = $this->variants->pluck('id');

        foreach ($variantIds as $id) {
            $this->assertDatabase([], ['product_variant_id' => $id]);
        }
    }

    protected function getRequestToSessionStart(): string
    {
        return '/api/v1/cart';
    }

    protected function getTableName(): string
    {
        return 'cart';
    }

    private function createCartItemsForOtherUsers(): void
    {
        CartHelper::createFromVariantByIdentifier(
            $this->variants,
            ['field' => 'session_id', 'value' => $this->otherSessionId],
        );

        $otherUser = UserHelper::createUser();
        $this->otherUserId = $otherUser->id;

        CartHelper::createFromVariantByIdentifier(
            $this->variants,
            ['field' => 'user_id', 'value' => $this->otherUserId],
        );
    }

    protected function getDatabaseFieldMapping(): array
    {
        return [
            'product_variant_id' => 'id',
        ];
    }

    public function testClearCartContainingOnlyCurrentUserProductsUsingSessionId(): void
    {
        $response = $this->getResponse('session');

        $this->checkSuccess($response);
    }

    public function testClearCartContainingOnlyCurrentUserProductsUsingUserId(): void
    {
        $response = $this->getResponse('user');

        $this->checkSuccess($response);
    }

    public function testClearCartContainingMixedUserProductsUsingSessionId(): void
    {
        $response = $this->getResponse(
            'session',
            true,
            true
        );

        $this->checkSuccess($response);

        $randomVariantId = $this->variants->random()->id;

        $this->assertDatabaseHas(
            'cart',
            [
                'session_id' => $this->otherSessionId,
                'product_variant_id' => $randomVariantId,
            ],
        );

        $this->assertDatabaseHas(
            'cart',
            [
                'user_id' => $this->otherUserId,
                'product_variant_id' => $randomVariantId,
            ],
        );
    }

    public function testClearCartContainingMixedUserProductsUsingUserId(): void
    {
        $response = $this->getResponse(
            'user',
            true,
            true,
        );

        $this->checkSuccess($response);

        $randomVariantId = $this->variants->random()->id;

        $this->assertDatabaseHas(
            'cart',
            [
                'session_id' => $this->otherSessionId,
                'product_variant_id' => $randomVariantId,
            ],
        );

        $this->assertDatabaseHas(
            'cart',
            [
                'user_id' => $this->otherUserId,
                'product_variant_id' => $randomVariantId,
            ],
        );
    }

    public function testAttemptToClearCartWithNoCurrentUserProductsUsingSessionId(): void
    {
        $response = $this->getResponse(
            'session',
            false,
            true,
        );

        $this->checkSuccess($response);

        $randomVariantId = $this->variants->random()->id;

        $this->assertDatabaseHas(
            'cart',
            [
                'session_id' => $this->otherSessionId,
                'product_variant_id' => $randomVariantId,
            ],
        );

        $this->assertDatabaseHas(
            'cart',
            [
                'user_id' => $this->otherUserId,
                'product_variant_id' => $randomVariantId,
            ],
        );
    }

    public function testAttemptToClearCartWithNoCurrentUserProductsUsingUserId(): void
    {
        $response = $this->getResponse(
            'user',
            false,
            true,
        );

        $this->checkSuccess($response);

        $randomVariantId = $this->variants->random()->id;

        $this->assertDatabaseHas(
            'cart',
            [
                'session_id' => $this->otherSessionId,
                'product_variant_id' => $randomVariantId,
            ],
        );

        $this->assertDatabaseHas(
            'cart',
            [
                'user_id' => $this->otherUserId,
                'product_variant_id' => $randomVariantId,
            ],
        );
    }

    public function testClearEmptyCartUsingSessionId(): void
    {
        $response = $this->getResponse(
            'session',
            false,
        );

        $this->checkSuccess($response);
    }

    public function testClearEmptyCartUsingUserId(): void
    {
        $response = $this->getResponse(
            'user',
            false,
        );

        $this->checkSuccess($response);
    }
}
