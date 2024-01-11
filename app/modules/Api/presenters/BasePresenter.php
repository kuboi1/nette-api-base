<?php

namespace App\Modules\Api\Presenters;

use App\ApiModule\Responses\BadRequestResponse;
use App\ApiModule\Responses\ForbiddenRequestResponse;
use App\Model\Repositories\AuthenticationRepository;
use App\services\ConfigService;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;

class BasePresenter extends Presenter
{
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

    private function getJsonData(): \stdClass
    {
        if ($this->getHttpRequest()->getRawBody()) {
            return json_decode($this->getHttpRequest()->getRawBody());
        } else {
            return json_decode('{}');
        }
    }
}