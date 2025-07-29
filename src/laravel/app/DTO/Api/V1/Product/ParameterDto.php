<?php

namespace App\DTO\Api\V1\Product;

use App\DTO\Traits\RequiresPreload;
use App\Enums\Parameter\ParameterGroupEnum;
use App\Exceptions\System\Dto\PivotAttributeMissingException;
use App\Exceptions\System\Dto\PivotMissingException;
use App\Models\Parameter;
use Illuminate\Support\Collection;

class ParameterDto
{
    use RequiresPreload;

    public function __construct(
        public int $id,
        public string $name,
        public string $value,
        public ?string $unit,
        public int|bool $is_shared,
        public int|ParameterGroupEnum $group,
    ) {
    }

    /**
     * Создаёт DTO из модели Parameter.
     *
     * @param Parameter $parameter Экземпляр модели с данными pivot таблицы: pivot_value, pivot_is_shared.
     *
     * @return self
     * @throws PivotMissingException Если отсутствует объект pivot.
     * @throws PivotAttributeMissingException Если value или is_shared отсутствуют в pivot.
     */
    public static function fromModel(Parameter $parameter): self
    {
        self::checkRequirePivotAttributes($parameter, ['value', 'is_shared']);

        return new self(
            id: $parameter->id,
            name: $parameter->name,
            value: $parameter->pivot->value,
            unit: $parameter->unit,
            is_shared: (bool)$parameter->pivot->is_shared,
            group: $parameter->group instanceof ParameterGroupEnum
                ? $parameter->group->value
                : $parameter->group,
        );
    }

    /**
     * Преобразует коллекцию моделей в массив DTO.
     *
     * @param Collection $parameters Коллекция моделей Parameter.
     *
     * @return ParameterDto[] Массив DTO.
     */
    public static function collection(Collection $parameters): array
    {
        return $parameters->map(fn($parameter) => self::fromModel($parameter))->toArray();
    }
}
