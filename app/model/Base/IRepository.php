<?php

namespace App\Model\Base;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

interface IRepository
{
    public function findAll(): Selection;
    /** @return ActiveRow[] */
    public function fetchAll(): array;
    public function findById(int $id): Selection;
    public function fetchById(int $id): ActiveRow|null;
    public function upsert(array $values): ActiveRow|int;
    public function delete(int $id): void;
}