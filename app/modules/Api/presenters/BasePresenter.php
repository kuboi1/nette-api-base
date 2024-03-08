<?php

namespace App\Modules\Api\Presenters;

use App\ApiModule\Responses\BadRequestResponse;
use App\ApiModule\Responses\ForbiddenRequestResponse;
use App\Model\Repositories\AuthenticationRepository;
use App\Model\Repositories\Base\LocalizedRepository;
use App\Model\Repositories\Base\Repository;
use App\services\ConfigService;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;

class BasePresenter extends Presenter
{
    protected Repository|LocalizedRepository $repository;
    protected bool $isLocalized;

    protected string $baseUrl;
    protected \stdClass $jsonData;

    private readonly AuthenticationRepository $authenticationRepository;
    private readonly ConfigService $configService;

    public function injectRepository(
        AuthenticationRepository $authenticationRepository,
        ConfigService $configService
    ): void
    {
        $this->authenticationRepository = $authenticationRepository;
        $this->configService = $configService;
    }

    protected function startup()
    {
        parent::startup();
        $this->baseUrl = substr($this->getHttpRequest()->getUrl()->getBaseUrl(), 0, -1);
        $this->jsonData = $this->getJsonData();

        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
    }

    /**
     * @throws AbortException
     */
    #[NoReturn] public function actionDefault(string $locale): void
    {
        $this->sendResponse(new BadRequestResponse('No api request specified'));
    }

    /**
     * @throws ForbiddenRequestException
     */
    protected function authenticateRequest(): bool
    {
        $apiId = $this->jsonData->{AuthenticationRepository::PARAM_AUTH}->{AuthenticationRepository::PARAM_AUTH_API_ID} ?? null;
        $apiKey = $this->jsonData->{AuthenticationRepository::PARAM_AUTH}->{AuthenticationRepository::PARAM_AUTH_API_KEY} ?? null;

        // No API key param
        if (!$apiKey) {
            throw new ForbiddenRequestException('API key is missing');
        }

        // Master Key is enabled, remove this to disable it
        if ($apiKey === $this->configService->getApiMasterKey()) {
            return true;
        }

        // No API id param
        if (!$apiId) {
            throw new ForbiddenRequestException('API id is missing');
        }

        // Check authentication
        if (
            $this->authenticationRepository->isValidAuth($apiId, $apiKey, $this->getHttpRequest()->getRemoteAddress())
        ) {
            return true;
        }

        throw new ForbiddenRequestException('Authentication failed');
    }

    /**
     * @throws AbortException
     */
    protected function getAllAction(string $locale): void
    {
        try {
            $this->authenticateRequest();

            $this->sendJson($this->repository->getAll($locale));
        } catch (ForbiddenRequestException $e) {
            $this->sendResponse(new ForbiddenRequestResponse($e->getMessage()));
        } catch (BadRequestException $e) {
            $this->sendResponse(new BadRequestResponse($e->getMessage()));
        }
    }

    /**
     * @throws AbortException
     */
    protected function upsertAction(): void
    {
        try {
            $this->authenticateRequest();

            if ($this->isLocalized) {
                $this->upsertLocalized();
            } else {
                $this->upsert();
            }
        } catch (ForbiddenRequestException $e) {
            $this->sendResponse(new ForbiddenRequestResponse($e->getMessage()));
        } catch (BadRequestException $e) {
            $this->sendResponse(new BadRequestResponse($e->getMessage()));
        }
    }

    /**
     * @throws BadRequestException
     */
    private function upsert(): void
    {
        $rows = $this->jsonData->{Repository::JSON_DATA}->{Repository::JSON_DATA_ROWS} ?? null;

        if (!$rows) {
            throw new BadRequestException('Missing upsert data');
        }

        // Upsert rows
        foreach ($rows as $row) {
            $this->repository->upsert($row);
        }
    }

    /**
     * @throws BadRequestException
     */
    private function upsertLocalized(): void
    {
        $rows = $this->jsonData->{Repository::JSON_DATA}->{Repository::JSON_DATA_ROWS} ?? null;
        $translations = $this->jsonData->{Repository::JSON_DATA}->{Repository::JSON_DATA_TRANSLATIONS} ?? null;

        if (!$rows && !$translations) {
            throw new BadRequestException('Missing upsert data');
        }

        // Upsert rows
        foreach ($rows as $row) {
            if ($translations && isset($translations[$row[Repository::COL_ID]])) {
                $this->repository->upsertWithTranslations($row, $translations[$row[Repository::COL_ID]]);
            } else {
                $this->repository->upsert($row);
            }
        }
    }

    private function getJsonData(): \stdClass
    {
        if ($this->getHttpRequest()->getRawBody()) {
            return json_decode($this->getHttpRequest()->getRawBody());
        } else {
            return json_decode('{}');
        }
    }
}