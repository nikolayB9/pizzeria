<?php

namespace App\DTO\Traits;

use App\Exceptions\Dto\AggregateAttributeMissingException;
use App\Exceptions\Dto\RelationIsNotCollectionException;
use App\Exceptions\Dto\RelationIsNullException;
use App\Exceptions\Dto\RequiredRelationMissingException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait RequiresPreload
{
    /**
     * Гарантирует, что отношение модели было предварительно загружено.
     *
     * @param Model $model Модель Eloquent, у которой проверяются отношения.
     * @param string|array $relations Название или список названий отношений, которые должны быть предварительно загружены.
     * @return void
     * @throws RequiredRelationMissingException Если отношение не было предварительно загружено.
     */
    private static function checkRequireRelations(Model $model, string|array $relations): void
    {
        $relations = (array)$relations;

        foreach ($relations as $relation) {
            if (!$model->relationLoaded($relation)) {
                throw new RequiredRelationMissingException("Relation [$relation] must be preloaded on model [" . get_class($model) . "] before constructing [" . static::class . "].");
            }
        }
    }

    /**
     * Гарантирует, что отношение модели было предварительно загружено и не равно null.
     *
     * @param Model $model Модель Eloquent, у которой проверяются отношения.
     * @param string|array $relations Название или список названий отношений, которые должны быть загружены и не равны null.
     * @return void
     * @throws RequiredRelationMissingException Если отношение не было предварительно загружено.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     */
    private static function checkRequireNotNullRelations(Model $model, string|array $relations): void
    {
        $relations = (array)$relations;

        self::checkRequireRelations($model, $relations);

        foreach ($relations as $relation) {
            if (is_null($model->$relation)) {
                throw new RelationIsNullException("Expected not-null relation [$relation] on model [" . get_class($model) . "] before constructing [" . static::class . "].");
            }
        }
    }

    /**
     * Гарантирует, что агрегатные атрибуты (например, поля из withCount/withSum) были предзагружены в модель.
     *
     * @param Model $model Модель Eloquent, у которой проверяется наличие агрегатных атрибутов.
     * @param string|array $aggregates Название или список названий агрегатных полей (например, variants_count, variants_min_price).
     * @return void
     * @throws AggregateAttributeMissingException Если указанный агрегат отсутствует в загруженных атрибутах модели.
     */
    private static function checkRequirePreloads(Model $model, string|array $aggregates): void
    {
        $aggregates = (array)$aggregates;
        $attributes = $model->getAttributes();

        foreach ($aggregates as $attribute) {
            if (!array_key_exists($attribute, $attributes)) {
                throw new AggregateAttributeMissingException("Aggregate attribute [$attribute] must be preloaded on model [" . get_class($model) . "] before constructing [" . static::class . "].");
            }
        }
    }

    /**
     * Гарантирует, что отношение модели было предварительно загружено и является экземплярами Collection.
     *
     * @param Model $model Модель Eloquent, у которой проверяются отношения.
     * @param string|array $relations Название или список названий отношений, которые должны быть загружены и представлять собой коллекции.
     * @return void
     * @throws RequiredRelationMissingException Если отношение не было предварительно загружено.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     * @throws RelationIsNotCollectionException Если загруженное отношение не является коллекцией Illuminate\Support\Collection.
     */
    private static function checkRequireCollectionInRelations(Model $model, string|array $relations): void
    {
        $relations = (array)$relations;

        self::checkRequireNotNullRelations($model, $relations);

        foreach ($relations as $relation) {
            if (!$model->$relation instanceof Collection) {
                throw new RelationIsNotCollectionException("Relation [$relation] on model [" . get_class($model) . "] must be an instance of Collection before constructing [" . static::class . "].");
            }
        }
    }
}
