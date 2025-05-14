<?php

namespace Tests\Traits;

namespace Tests\Traits;

trait AssertsDatabaseWithAuth
{
    /**
     * Возвращает имя таблицы для проверки.
     *
     * @return string Имя таблицы в базе данных.
     */
    abstract protected function getTableName(): string;

    /**
     * Возвращает массив с полем авторизации.
     *
     * @return array{field: string, value: int|string} Ассоциативный массив с названием поля и его значением.
     */
    abstract protected function getAuthField(): array;

    /**
     * Возвращает соответствие между полями таблицы и входными данными.
     *
     * @return array<string, string> Массив вида [поле_в_таблице => ключ_во_входных_данных].
     */
    abstract protected function getDatabaseFieldMapping(): array;


    /**
     * Проверяет наличие и/или отсутствие записи в таблице с учётом авторизации и маппинга полей.
     *
     * @param array<string, mixed> $dataHas Данные, которые должны присутствовать в таблице.
     * @param array<string, mixed> $dataMissing Данные, которые должны отсутствовать в таблице.
     * @return void
     */
    protected function assertDatabase(array $dataHas = [], array $dataMissing = []): void
    {
        if (!empty($dataHas)) {
            $this->assertDatabaseHas(
                $this->getTableName(),
                $this->buildDatabaseCriteria($dataHas)
            );
        }

        if (!empty($dataMissing)) {
            $this->assertDatabaseMissing(
                $this->getTableName(),
                $this->buildDatabaseCriteria($dataMissing)
            );
        }
    }

    /**
     * Составляет массив для поиска в базе данных из авторизации и маппинга полей.
     *
     * @param array<string, mixed> $inputData Входные данные, которые нужно сопоставить с полями таблицы.
     * @return array<string, mixed> Массив критериев для поиска в таблице.
     */
    private function buildDatabaseCriteria(array $inputData): array
    {
        $criteria = [];

        foreach ($this->getDatabaseFieldMapping() as $dbField => $inputKey) {
            if (array_key_exists($inputKey, $inputData)) {
                $criteria[$dbField] = $inputData[$inputKey];
            }
        }

        $auth = $this->getAuthField();
        $criteria[$auth['field']] = $auth['value'];

        return $criteria;
    }
}


