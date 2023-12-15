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
    public const
        TABLE = 'table',

        COL_ID = 'id',
        COL_DATE_CREATED = 'date_created',
        COL_DATE_UPDATED = 'date_updated';

    public function __construct(
        protected readonly Explorer $database
    )
    {}

    protected abstract function mapToDataType(ActiveRow $row): DataType;

    public function selectAll(): Selection
    {
        return $this->database->table(static::TABLE);
    }

    /**
     * @return ActiveRow[]
     */
    public function fetchAll(): array
    {
        return $this->selectAll()->fetchAll();
    }

    public function selectById(int $id): Selection
    {
        return $this->selectAll()->where(self::COL_ID, $id);
    }

    public function fetchById(int $id): ActiveRow|null
    {
        return $this->selectById($id)->fetch();
    }

    public function upsert(array $values): ActiveRow|int
    {
        if ($values[self::COL_ID]) {
            return $this->selectById($values[self::COL_ID])->update($values);
        } else {
            return $this->selectAll()->insert($values);
        }
    }

    public function delete(int $id): void
    {
        $this->selectById($id)->delete();
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