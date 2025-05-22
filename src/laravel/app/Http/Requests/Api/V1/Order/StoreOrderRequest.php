<?php

namespace App\Http\Requests\Api\V1\Order;

use App\DTO\Api\V1\Order\CreateOrderInputDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения этого запроса.
     *
     * @return bool True, если пользователь авторизован.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'delivery_time' => ['required', 'date_format:H:i'],
            'comment' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Преобразует валидированные данные в DTO для создания заказа.
     *
     * @return CreateOrderInputDto Объект DTO.
     */
    public function toDto(): CreateOrderInputDto
    {
        $data = $this->validated();

        return new CreateOrderInputDto(
            delivery_time: $data['delivery_time'],
            comment: $data['comment'],
        );
    }
}
