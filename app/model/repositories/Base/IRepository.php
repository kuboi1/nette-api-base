<?php

namespace App\Model\Repositories\Base;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

interface IRepository
{
    public function selectAll(): Selection;
    /** @return ActiveRow[] */
    public function fetchAll(): array;
    public function selectByPrimary(int $primaryKey): Selection;
    public function fetchByPrimary(int $primaryKey): ActiveRow|null;
    public function upsert(array $values): ActiveRow|int;
    public function delete(int $primaryKey): void;
}