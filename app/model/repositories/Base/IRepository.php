<?php

namespace App\Model\Repositories\Base;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

interface IRepository
{
    public function selectAll(): Selection;
    /** @return ActiveRow[] */
    public function fetchAll(): array;
    public function selectById(int $id): Selection;
    public function fetchById(int $id): ActiveRow|null;
    public function upsert(array $values): ActiveRow|int;
    public function delete(int $id): void;
}