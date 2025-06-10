<?php

namespace Api\V1\Cart;

use App\DTO\Api\V1\Cart\CartRawItemDto;
use App\Exceptions\Cart\InvalidCartProductDataException;
use App\Repositories\Api\V1\Cart\CartRepositoryInterface;
use App\Services\Api\V1\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Mockery;
use Tests\TestCase;

class CartTotalPriceCalculationTest extends TestCase
{
    protected const TEST_USER_ID = 123;
    protected const TEST_SESSION_ID = 'test_session_id';
    protected const TEST_PRICE = 1234.0;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testReturnsZeroForEmptyCartWithoutCalculation()
    {
        $cartRepository = Mockery::mock(CartRepositoryInterface::class);
        $cartService = new CartService($cartRepository);

        // Проверяем, что репозиторий НЕ вызывается
        $cartRepository->shouldNotReceive('getTotalPriceByIdentifier');

        // Проверяем логирование
        Log::shouldReceive('info')
            ->once()
            ->with('Расчет общей стоимости пропущен, корзина пуста и флаг расчета выключен', [
                'method' => 'App\Services\Api\V1\CartService::getTotalPrice'
            ]);

        $result = $cartService->getTotalPrice([], false);

        $this->assertSame(0.0, $result); // Строгое сравнение
    }

    public function testCalculatesPriceForArrayProducts()
    {
        $product = new CartRawItemDto(
            product_variant_id: 1,
            price: self::TEST_PRICE,
            qty: 2,
        );

        $cartRepository = Mockery::mock(CartRepositoryInterface::class);
        $cartService = new CartService($cartRepository);

        $totalPrice = $cartService->getTotalPrice([$product]);

        $this->assertSame(self::TEST_PRICE * 2, $totalPrice);
        $cartRepository->shouldNotReceive('getTotalPriceByIdentifier');
    }

    public function testReturnsPriceFromDatabaseForAuthenticatedUser()
    {
        // Мокаем репозиторий
        $cartRepository = Mockery::mock(CartRepositoryInterface::class);
        $cartRepository->shouldReceive('getTotalPriceByIdentifier')
            ->with('user_id', self::TEST_USER_ID)
            ->once()
            ->andReturn(self::TEST_PRICE);

        // Мокаем аутентификацию
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn(self::TEST_USER_ID);

        // Тестируем
        $cartService = new CartService($cartRepository);
        $totalPrice = $cartService->getTotalPrice();

        // Проверяем результат
        $this->assertSame(self::TEST_PRICE, $totalPrice);
    }

    public function testReturnsPriceFromDatabaseForSession()
    {
        $cartRepository = Mockery::mock(CartRepositoryInterface::class);
        $cartRepository->shouldReceive('getTotalPriceByIdentifier')
            ->with('session_id', self::TEST_SESSION_ID) // Ожидаемые аргументы
            ->once()
            ->andReturn(self::TEST_PRICE);

        // Мокаем Auth и Session
        Auth::shouldReceive('check')->andReturn(false);
        Session::shouldReceive('getId')->andReturn(self::TEST_SESSION_ID);

        $cartService = new CartService($cartRepository);
        $totalPrice = $cartService->getTotalPrice();

        $this->assertSame(self::TEST_PRICE, $totalPrice);
    }

    public function testThrowsExceptionForInvalidProductData()
    {
        $validProduct = new CartRawItemDto(
            product_variant_id: 1,
            price: self::TEST_PRICE,
            qty: 2,
        );

        $productWithInvalidQty = new CartRawItemDto(
            product_variant_id: 2,
            price: self::TEST_PRICE,
            qty: 0,
        );

        $cartRepository = Mockery::mock(CartRepositoryInterface::class);
        $cartService = new CartService($cartRepository);

        // Указываем, какое исключение мы ожидаем
        $this->expectException(InvalidCartProductDataException::class);
        // Задаем ожидаемое сообщение исключения
        $this->expectExceptionMessage('Некорректные данные товара. Расчет стоимости невозможен.');

        Log::shouldReceive('error')
            ->once()
            ->with('Ошибка в цене или количестве товара при расчете общей стоимости корзины', [
                'cart_product' => $productWithInvalidQty,
                'method' => 'App\Services\Api\V1\CartService::getTotalPrice'
            ]);

        // Запускаем метод, который потенциально выбросит исключение
        $cartService->getTotalPrice([$validProduct, $productWithInvalidQty]);

        // Также можно проверить, что метод репозитория не был вызван
        $cartRepository->shouldNotReceive('getTotalPriceByIdentifier');
    }
}
