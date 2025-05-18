<?php

namespace App\DTO\Traits;

use App\Exceptions\Dto\AggregateAttributeMissingException;
use App\Exceptions\Dto\PivotAttributeMissingException;
use App\Exceptions\Dto\PivotMissingException;
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
     * @param string|array $relations Название или список названий отношений, которые должны быть предварительно
     *     загружены.
     *
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
     * @param string|array $relations Название или список названий отношений, которые должны быть загружены и не равны
     *     null.
     *
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
     * Гарантирует, что указанные (в том числе вложенные) отношения модели загружены.
     *
     * Метод проверяет каждую часть отношения, переданного в точечной нотации (например,
     * 'productVariant.product.previewImage'), и выбрасывает исключение, если хотя бы одно из них не было
     * предварительно загружено. Повторяющиеся части путей отношений проверяются только один раз, чтобы избежать
     * дублирующих проверок при вложенных связях.
     *
     * @param Model $model Модель Eloquent, у которой проверяются отношения.
     * @param string|array $relations Отношение или список отношений (в том числе вложенных через точку), которые
     *     должны быть загружены.
     *
     * @return void
     * @throws RequiredRelationMissingException Если хотя бы одно из указанных отношений не загружено.
     */
    private static function checkRequireAllRelationPaths(Model $model, string|array $relations): void
    {
        $relations = (array)$relations;
        $checkedPaths = [];

        foreach ($relations as $relationPath) {
            $paths = explode('.', $relationPath);
            $currentModel = $model;
            $currentPath = '';

            foreach ($paths as $path) {
                $currentPath = ltrim("$currentPath.$path", '.');

                if (!in_array($currentPath, $checkedPaths, true)) {
                    self::checkRequireRelations($currentModel, $path);
                    $checkedPaths[] = $currentPath;
                }

                $currentModel = $currentModel->$path;

                if ($currentModel instanceof \Illuminate\Support\Collection && $currentModel->isNotEmpty()) {
                    $currentModel = $currentModel->first();
                }

                if (!$currentModel instanceof Model) {
                    break;
                }
            }
        }
    }

    /**
     * Гарантирует, что указанные (в том числе вложенные) отношения модели загружены и не равны null.
     *
     * Метод проверяет каждую часть отношения, переданного в точечной нотации (например,
     * 'productVariant.product.previewImage'), и выбрасывает исключение, если хотя бы одно из них не было
     * предварительно загружено или является null. Повторяющиеся части путей отношений проверяются только один раз,
     * чтобы избежать дублирующих проверок при вложенных связях.
     *
     * @param Model $model Модель Eloquent, у которой проверяются отношения.
     * @param string|array $relations Отношение или список отношений (в том числе вложенных через точку), которые
     *     должны быть загружены.
     *
     * @return void
     * @throws RequiredRelationMissingException Если хотя бы одно из указанных отношений не загружено.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     */
    private static function checkRequireNotNullAllRelationPaths(Model $model, string|array $relations): void
    {
        $relations = (array)$relations;
        $checkedPaths = [];

        foreach ($relations as $relationPath) {
            $paths = explode('.', $relationPath);
            $currentModel = $model;
            $currentPath = '';

            foreach ($paths as $path) {
                $currentPath = ltrim("$currentPath.$path", '.');

                if (!in_array($currentPath, $checkedPaths, true)) {
                    self::checkRequireNotNullRelations($currentModel, $path);
                    $checkedPaths[] = $currentPath;
                }

                $currentModel = $currentModel->$path;

                if ($currentModel instanceof \Illuminate\Support\Collection && $currentModel->isNotEmpty()) {
                    $currentModel = $currentModel->first();
                }

                if (!$currentModel instanceof Model) {
                    break;
                }
            }
        }
    }

    /**
     * Гарантирует, что отношение модели было предварительно загружено и является экземплярами Collection.
     *
     * @param Model $model Модель Eloquent, у которой проверяются отношения.
     * @param string|array $relations Название или список названий отношений, которые должны быть загружены и
     *     представлять собой коллекции.
     *
     * @return void
     * @throws RequiredRelationMissingException Если отношение не было предварительно загружено.
     * @throws RelationIsNullException Если загруженное отношение равно null.
     * @throws RelationIsNotCollectionException Если загруженное отношение не является коллекцией
     *     Illuminate\Support\Collection.
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

    /**
     * Гарантирует, что агрегатные атрибуты (например, поля из withCount/withSum) были предзагружены в модель.
     *
     * @param Model $model Модель Eloquent, у которой проверяется наличие агрегатных атрибутов.
     * @param string|array $aggregates Название или список названий агрегатных полей (например, variants_count,
     *     variants_min_price).
     *
     * @return void
     * @throws AggregateAttributeMissingException Если указанный агрегат отсутствует в загруженных атрибутах модели.
     */
    private static function checkRequireAggregateAttributes(Model $model, string|array $aggregates): void
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
     * Гарантирует, что указанные атрибуты pivot-таблицы присутствуют в модели.
     *
     * @param Model $model Модель Eloquent, у которой проверяется наличие pivot-атрибутов.
     * @param string|array $pivots Название или список названий pivot-полей.
     *
     * @return void
     * @throws PivotMissingException Если отсутствует объект pivot.
     * @throws PivotAttributeMissingException Если хотя бы один указанный атрибут отсутствует в pivot.
     */
    private static function checkRequirePivotAttributes(Model $model, string|array $pivots): void
    {
        if (!$model->relationLoaded('pivot') || !$model->pivot) {
            throw new PivotMissingException("Pivot relation is missing on model [" . get_class($model) . "] while constructing [" . static::class . "].");
        }

        $pivots = (array)$pivots;
        $pivotAttributes = $model->pivot->getAttributes();

        foreach ($pivots as $attribute) {
            if (!array_key_exists($attribute, $pivotAttributes)) {
                throw new PivotAttributeMissingException("Pivot attribute [$attribute] is missing on model [" . get_class($model) . "] while constructing [" . static::class . "].");
            }
        }
    }
}
