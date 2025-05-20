<?php

namespace App\Http\Requests\Api\V1\Address;

use App\DTO\Api\V1\Address\UpdateAddressDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserAddressRequest extends FormRequest
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
            'city_id' => ['bail', 'required', 'integer', 'exists:cities,id'],
            'street_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('streets', 'id')->where('city_id', $this->input('city_id'))],
            'house' => ['required', 'string', 'max:255'],
            'entrance' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:255'],
            'flat' => ['nullable', 'string', 'max:255'],
            'intercom_code' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Преобразует валидированные данные в DTO для обновления данных адреса.
     *
     * @return UpdateAddressDto Объект DTO.
     */
    public function toDto(): UpdateAddressDto
    {
        $data = $this->validated();

        return new UpdateAddressDto(
            city_id: $data['city_id'],
            street_id: $data['street_id'],
            house: $data['house'],
            entrance: $data['entrance'] ?? null,
            floor: $data['floor'] ?? null,
            flat: $data['flat'] ?? null,
            intercom_code: $data['intercom_code'] ?? null,
        );
    }
}
