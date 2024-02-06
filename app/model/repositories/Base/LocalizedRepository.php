<?php

namespace App\Model\Repositories\Base;

use App\Model\ModelUtils;
use App\Model\Types\Base\DataType;
use App\Model\Utils;
use App\Services\LocaleService;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * @template T of DataType
 */
abstract class LocalizedRepository extends Repository
{
    private const string DEFAULT_LOCALE = 'en';

    public const string
        TABLE_TRANSLATION = 'table_translation',

        COL_TRANSLATION_PARENT_ID = 'parent_id',
        COL_TRANSLATION_LOCALE = 'locale';

    protected static array $translationCols;

    public function __construct(
        protected readonly Explorer $database
    )
    {
        parent::__construct($database);
    }

    public function selectAllTranslations(): Selection
    {
        return $this->database->table(static::TABLE_TRANSLATION);
    }

    public function selectAllWithTranslations(string $locale): Selection
    {
        return $this->selectTableWithTranslation(
            static::TABLE,
            static::TABLE_TRANSLATION,
            static::$translationCols,
            $locale
        );
    }

    public function selectTableWithTranslation(
        string $table,
        string $translationTable,
        array $translationCols,
        string $locale
    ): Selection
    {
        $colDefs = array_merge(
            [
                ['*', $table],
                [self::COL_TRANSLATION_LOCALE, $translationTable, true]
            ],
            array_map(
                fn($col) => [$col, $translationTable, true],
                $translationCols
            )
        );

        return $this->selectTable($table)
            ->select(ModelUtils::createQuery(...$colDefs))
            ->where(ModelUtils::createQuery([self::COL_TRANSLATION_LOCALE, $translationTable, true]), $locale);
    }

    /**
     * @return ActiveRow[]
     */
    public function fetchAllWithTranslations(string $locale): array
    {
        return $this->selectAllWithTranslations($locale)->fetchAll();
    }

    public function upsertWithTranslations(array $values, array $translations, ?int $id = null): ActiveRow|int
    {
        $row = $this->upsert($values, $id);

        if ($id) {
            foreach ($translations as $locale => $data) {
                $this->selectAllTranslations()
                    ->where(static::COL_TRANSLATION_PARENT_ID, $id)
                    ->where(self::COL_TRANSLATION_LOCALE, $locale)
                    ->update($data);
            }
        } else {
            foreach ($translations as $locale => $data) {
                $this->selectAllTranslations()->insert(
                    [
                        static::COL_TRANSLATION_PARENT_ID => $row->getPrimary(),
                        self::COL_TRANSLATION_LOCALE => $locale
                    ] + $data
                );
            }
        }

        return $row;
    }

    #[\Override] public function delete(int $id): void
    {
        $this->selectAllTranslations()->delete();

        parent::delete($id);
    }

    /**
     * @return T|null
     */
    #[\Override] public function getById(int $id, ?string $locale = null)
    {
        $row = $this->selectAllWithTranslations($locale ?? self::DEFAULT_LOCALE)
            ->wherePrimary($id)
            ->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapToDataType($row);
    }

    /**
     * @return T[]
     */
    #[\Override] public function getAll(?string $locale = null): array
    {
        $rows = $this->fetchAllWithTranslations($locale ?? self::DEFAULT_LOCALE);

        return array_values(array_map([$this, 'mapToDataType'], $rows));
    }
}