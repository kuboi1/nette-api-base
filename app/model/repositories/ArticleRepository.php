<?php

namespace App\Model\Repositories;

use App\Model\Repositories\Base\LocalizedRepository;
use App\Model\Types\Article;
use App\Model\Types\Base\DataType;
use Nette\Database\Table\ActiveRow;

/**
 * @template-extends LocalizedRepository<Article>
 */
class ArticleRepository extends LocalizedRepository
{
    public const
        TABLE = 'article',
        TABLE_TRANSLATION = 'article_translation',

        COL_IMAGE = 'image',

        COL_TRANSLATION_PARENT_ID = 'article_id',
        COL_TRANSLATION_TITLE = 'title',
        COL_TRANSLATION_TEXT = 'text';

    protected static array $translationCols = [
        self::COL_TRANSLATION_TITLE,
        self::COL_TRANSLATION_TEXT
    ];

    protected function mapToDataType(ActiveRow $row): DataType
    {
        return new Article($row);
    }
}