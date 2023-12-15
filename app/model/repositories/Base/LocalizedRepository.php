<?php

namespace App\Model\Repositories\Base;

use App\Model\Types\Base\DataType;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * @template T of DataType
 */
abstract class LocalizedRepository extends Repository
{
    public const
        TABLE_TRANSLATION = 'table_translation',

        COL_TRANSLATION_PARENT_ID = 'parent_id',
        COL_TRANSLATION_LOCALE = 'locale';

    protected static array $translationCols;

    public function selectAllTranslations(): Selection
    {
        return $this->database->table(static::TABLE_TRANSLATION);
    }

    public function selectAllWithTranslations(string $locale): Selection
    {
        $select = static::TABLE.'.*';

        $select .= ', :' . static::TABLE_TRANSLATION . '.' . self::COL_TRANSLATION_LOCALE;

        $translationCols = array_map(
            fn($col) => ':' . static::TABLE_TRANSLATION . '.' . $col,
            static::$translationCols
        );
        $select .= ', ' . implode(', ', $translationCols);

        return $this->selectAll()
            ->select($select)
            ->where(self::COL_TRANSLATION_LOCALE, $locale);
    }

    /**
     * @return ActiveRow[]
     */
    public function fetchAllWithTranslations(string $locale): array
    {
        return $this->selectAllWithTranslations($locale)->fetchAll();
    }

    public function upsertWithTranslations(array $values, array $translations): ActiveRow|int
    {
        $row = $this->upsert($values);

        if ($values[self::COL_ID]) {
            return $this->selectAllTranslations()
                ->where(static::COL_TRANSLATION_PARENT_ID, $values[self::COL_ID])
                ->update($translations);
        } else {
            $translations[static::COL_TRANSLATION_PARENT_ID] = $row->{self::COL_ID};
            return $this->selectAllTranslations()->insert($translations);
        }
    }

    /**
     * @return T[]
     */
    public function getAll(?string $locale = null): array
    {
        if (!$locale) {
            return parent::getAll();
        }

        $rows = $this->fetchAllWithTranslations($locale);

        return array_values(array_map([$this, 'mapToDataType'], $rows));
    }
}