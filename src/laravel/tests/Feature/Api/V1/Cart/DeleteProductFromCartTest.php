<?php

namespace Api\V1\Cart;

use App\Enums\Error\ErrorMessageEnum;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Api\AbstractApiTestCase;
use Tests\Helpers\CartHelper;
use Tests\Helpers\CategoryHelper;
use Tests\Helpers\ProductHelper;
use Tests\Traits\AssertsDatabaseWithAuth;
use Tests\Traits\HasAuthContext;

class DeleteProductFromCartTest extends AbstractApiTestCase
{
    use HasAuthContext, AssertsDatabaseWithAuth;

    protected const COUNT_CATEGORIES = 1;
    protected const COUNT_PRODUCTS = 1;
    protected const COUNT_VARIANTS = 2;

    protected Collection $variants;

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
        return '/api/v1/cart';
    }

    protected function getMethod(): string
    {
        return 'delete';
    }

    protected function getResponse(string $authType, mixed $productData): TestResponse
    {
        if ($authType === 'session') {
            $this->setSessionId();
        } else {
            $this->setUserId();
        }

        if (is_int($productData)) {
            $productData = ['variantId' => $productData];
        }

        $route = $this->getRoute();
        $method = $this->getMethod();

        if ($authType === 'session') {
            return $this->withCookie('laravel_session', $this->sessionId)
                ->$method($route, $productData, ['Accept' => 'application/json']);
        } else {
            return $this->$method($route, $productData, ['Accept' => 'application/json']);
        }
    }

    protected function checkSuccess(TestResponse $response, mixed $data = [], mixed $meta = [], int $status = 200): void
    {
        $meta = $meta === [] ?: ['totalPrice' => $meta];

        parent::checkSuccess($response, $data, $meta, $status);

        $totalPrice = $response->json('meta.totalPrice');
        $this->assertTrue(is_float($totalPrice) || is_int($totalPrice));
    }

    protected function getRequestToSessionStart(): string
    {
        return '/api/v1/cart';
    }

    protected function getTableName(): string
    {
        return 'cart';
    }

    protected function getDatabaseFieldMapping(): array
    {
        return [
            'product_variant_id' => 'id',
            'qty' => 'qty',
        ];
    }

    public function testDeleteOnlyOneProductFromCartUsingSessionId(): void
    {
        $this->setSessionId();

        $variant = $this->variants->random();
        $variantId = $variant->id;

        CartHelper::createFromVariantByIdentifier($variant, $this->getAuthField('session'));

        $response = $this->getResponse('session', $variantId);

        $this->checkSuccess($response, meta: 0.0);
        $this->assertDatabase([], ['id' => $variantId]);
    }

    public function testDeleteOnlyOneProductFromCartUsingUserId(): void
    {
        $this->setUserId();

        $variant = $this->variants->random();
        $variantId = $variant->id;

        CartHelper::createFromVariantByIdentifier($variant, $this->getAuthField('user'));

        $response = $this->getResponse('user', $variantId);

        $this->checkSuccess($response, meta: 0.0);
        $this->assertDatabase([], ['id' => $variantId]);
    }

    public function testDeleteOneOfSeveralProductsFromCartUsingSessionId(): void
    {
        $this->setSessionId();

        CartHelper::createFromVariantByIdentifier($this->variants, $this->getAuthField('session'));

        $variantToDelete = $this->variants->first();
        $variantInCart = $this->variants->last();

        $response = $this->getResponse('session', $variantToDelete->id);

        $this->checkSuccess($response, meta: $variantInCart->price);
        $this->assertDatabase(['id' => $variantInCart->id, 'qty' => 1], ['id' => $variantToDelete->id]);
    }

    public function testDeleteOneOfSeveralProductsFromCartUsingUserId(): void
    {
        $this->setUserId();

        CartHelper::createFromVariantByIdentifier($this->variants, $this->getAuthField('user'));

        $variantToDelete = $this->variants->first();
        $variantInCart = $this->variants->last();

        $response = $this->getResponse('user', $variantToDelete->id);

        $this->checkSuccess($response, meta: $variantInCart->price);
        $this->assertDatabase(['id' => $variantInCart->id, 'qty' => 1], ['id' => $variantToDelete->id]);
    }

    public function testDecreasesQtySingleProductInCartUsingSessionId(): void
    {
        $this->setSessionId();

        $variant = $this->variants->random();

        CartHelper::createFromVariantByIdentifier(
            $variant,
            $this->getAuthField('session'),
            false,
            3
        );

        $response = $this->getResponse('session', $variant->id);

        $totalPrice = round($variant->price * 2, 2);

        $this->checkSuccess($response, meta: $totalPrice);
        $this->assertDatabase(['id' => $variant->id, 'qty' => 2]);
    }

    public function testDecreasesQtySingleProductInCartUsingUserId(): void
    {
        $this->setUserId();

        $variant = $this->variants->random();

        CartHelper::createFromVariantByIdentifier(
            $variant,
            $this->getAuthField('user'),
            false,
            3
        );

        $response = $this->getResponse('user', $variant->id);

        $totalPrice = round($variant->price * 2, 2);

        $this->checkSuccess($response, meta: $totalPrice);
        $this->assertDatabase(['id' => $variant->id, 'qty' => 2]);
    }

    public function testDecreasesQtyWhenMultipleProductsInCartUsingSessionId(): void
    {
        $this->setSessionId();

        $variantToDecrease = $this->variants->first();
        $variantToRemain = $this->variants->last();

        CartHelper::createFromVariantByIdentifier(
            $this->variants,
            $this->getAuthField('session'),
            false,
            2
        );

        $response = $this->getResponse('session', $variantToDecrease->id);

        $expectedTotal = round($variantToDecrease->price + $variantToRemain->price * 2, 2);

        $this->checkSuccess($response, meta: $expectedTotal);
        $this->assertDatabase(['id' => $variantToDecrease->id, 'qty' => 1]);
        $this->assertDatabase(['id' => $variantToRemain->id, 'qty' => 2]);
    }

    public function testDecreasesQtyWhenMultipleProductsInCartUsingUserId(): void
    {
        $this->setUserId();

        $variantToDecrease = $this->variants->first();
        $variantToRemain = $this->variants->last();

        CartHelper::createFromVariantByIdentifier(
            $this->variants,
            $this->getAuthField('user'),
            false,
            2
        );

        $response = $this->getResponse('user', $variantToDecrease->id);

        $expectedTotal = round($variantToDecrease->price + $variantToRemain->price * 2, 2);

        $this->checkSuccess($response, meta: $expectedTotal);
        $this->assertDatabase(['id' => $variantToDecrease->id, 'qty' => 1]);
        $this->assertDatabase(['id' => $variantToRemain->id, 'qty' => 2]);
    }

    public function testDeleteNonExistentProductUsingSessionId(): void
    {
        $nonExistentVariantId = $this->variants->max('id') + 1;

        $response = $this->getResponse('session', $nonExistentVariantId);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testDeleteNonExistentProductUsingUserId(): void
    {
        $nonExistentVariantId = $this->variants->max('id') + 1;

        $response = $this->getResponse('user', $nonExistentVariantId);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testDeleteProductNotInCartUsingSessionId(): void
    {
        $this->setSessionId();

        $variantInCart = $this->variants->first();
        $variantNotInCart = $this->variants->last();

        CartHelper::createFromVariantByIdentifier(
            $variantInCart,
            $this->getAuthField('session'),
        );

        $response = $this->getResponse('session', $variantNotInCart->id);

        $this->checkError($response, 422, 'Товар не найден в корзине.');
        $this->assertDatabase(['id' => $variantInCart->id, 'qty' => 1], ['id' => $variantNotInCart->id]);
    }

    public function testDeleteProductNotInCartUsingUserId(): void
    {
        $this->setUserId();

        $variantInCart = $this->variants->first();
        $variantNotInCart = $this->variants->last();

        CartHelper::createFromVariantByIdentifier(
            $variantInCart,
            $this->getAuthField('user'),
        );

        $response = $this->getResponse('user', $variantNotInCart->id);

        $this->checkError($response, 422, 'Товар не найден в корзине.');
        $this->assertDatabase(['id' => $variantInCart->id, 'qty' => 1], ['id' => $variantNotInCart->id]);
    }

    public function testDeleteProductWithInvalidVariantIdTypeUsingSessionId(): void
    {
        $invalidVariantId = 'string instead of int';

        $response = $this->getResponse('session', ['variantId' => $invalidVariantId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testDeleteProductWithInvalidVariantIdTypeUsingUserId(): void
    {
        $invalidVariantId = 'string instead of int';

        $response = $this->getResponse('session', ['variantId' => $invalidVariantId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testDeleteProductWithWrongVariantIdKeyUsingSessionId(): void
    {
        $existedId = $this->variants->random()->id;

        $wrongKey = 'variant_id';

        $response = $this->getResponse('session', [$wrongKey => $existedId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }

    public function testDeleteProductWithWrongVariantIdKeyUsingUserId(): void
    {
        $existedId = $this->variants->random()->id;

        $wrongKey = 'variant_id';

        $response = $this->getResponse('user', [$wrongKey => $existedId]);

        $this->checkError($response, 422, ErrorMessageEnum::VALIDATION->value);
    }
}
