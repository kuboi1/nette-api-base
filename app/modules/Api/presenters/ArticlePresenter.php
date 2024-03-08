<?php

namespace App\Modules\Api\Presenters;

use App\Model\Repositories\ArticleRepository;
use Nette\Application\AbortException;

class ArticlePresenter extends BasePresenter
{
    public function __construct(
        ArticleRepository $articleRepository
    )
    {
        parent::__construct();

        $this->isLocalized = true;
        $this->repository = $articleRepository;
    }

    /**
     * @throws AbortException
     */
    public function actionDefault(string $locale): void
    {
        $this->getAllAction($locale);
    }

    /**
     * @throws AbortException
     */
    public function actionUpsert(): void
    {
        $this->upsertAction();
    }
}