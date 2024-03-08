<?php

namespace App\Model\Repositories\Base;

use App\Model\Types\Base\DataType;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * @template T of DataType
 */
abstract class Repository implements IRepository
{
    public const string
        TABLE = 'table',

        COL_ID = 'id',
        COL_DATE_CREATED = 'date_created',
        COL_DATE_UPDATED = 'date_updated',

        JSON_DATA = 'data',
        JSON_DATA_ROWS = 'rows',
        JSON_DATA_TRANSLATIONS = 'translations';

    public function __construct(
        protected readonly Explorer $database
    )
    {}

    protected abstract function mapToDataType(ActiveRow $row): DataType;

    public function selectAll(): Selection
    {
        return $this->selectTable(static::TABLE);
    }

    public function selectTable(string $table): Selection
    {
        return $this->database->table($table);
    }

    /**
     * @return ActiveRow[]
     */
    public function fetchAll(): array
    {
        return $this->selectAll()->fetchAll();
    }

    public function selectByPrimary(int|string $primaryKey): Selection
    {
        return $this->selectAll()->where(self::COL_ID, $primaryKey);
    }

    public function fetchByPrimary(int|string $primaryKey): ActiveRow|null
    {
        return $this->selectByPrimary($primaryKey)->fetch();
    }

    public function upsert(array $values, int|string|null $primaryKey = null): ActiveRow|int
    {
        if ($primaryKey) {
            return $this->selectByPrimary($primaryKey)->update($values);
        } else {
            return $this->selectAll()->insert($values);
        }
    }

    public function delete(int|string $primaryKey): void
    {
        $this->selectByPrimary($primaryKey)->delete();
    }

    /**
     * @return T|null
     */
    public function getByPrimary(int|string $primaryKey, ?string $locale = null)
    {
        $row = $this->fetchByPrimary($primaryKey);

        if (!$row) {
            return null;
        }

        return $this->mapToDataType($row);
    }

    /**
     * @return T[]
     */
    public function getAll(?string $locale = null): array
    {
        $rows = $this->fetchAll();

        return array_values(array_map([$this, 'mapToDataType'], $rows));
    }
}