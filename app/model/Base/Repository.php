<?php

namespace App\Model\Base;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

abstract class Repository implements IRepository
{
    public const
        COL_ID = 'id';

    public static string $table;

    public function __construct(
        private readonly Explorer $database
    )
    {}

    public function findAll(): Selection
    {
        return $this->database->table(self::$table);
    }

    /**
     * @return ActiveRow[]
     */
    public function fetchAll(): array
    {
        return $this->findAll()->fetchAll();
    }

    public function findById(int $id): Selection
    {
        return $this->findAll()->where(self::COL_ID, $id);
    }

    public function fetchById(int $id): ActiveRow|null
    {
        return $this->findById($id)->fetch();
    }

    public function upsert(array $values): ActiveRow|int
    {
        if ($values[self::COL_ID]) {
            return $this->findById($values[self::COL_ID])->update($values);
        } else {
            return $this->findAll()->insert($values);
        }
    }

    public function delete(int $id): void
    {
        $this->findById($id)->delete();
    }
}