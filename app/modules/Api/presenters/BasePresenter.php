<?php

namespace App\Modules\Api\Presenters;

use App\ApiModule\Responses\BadRequestResponse;
use App\ApiModule\Responses\ForbiddenRequestResponse;
use App\model\AuthenticationRepository;
use App\services\ConfigService;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;

class BasePresenter extends Presenter
{
    protected string $baseUrl;

    public function __construct(
        private AuthenticationRepository $authenticationRepository,
        private ConfigService $configService
    )
    {
        parent::__construct();
    }

    protected function startup()
    {
        parent::startup();
        $this->baseUrl = substr($this->getHttpRequest()->getUrl()->getBaseUrl(), 0, -1);

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
    public function actionDefault(): void
    {
        try {
            $this->authenticateRequest();

            $this->sendJson($this->getHttpRequest()->getRemoteAddress());
        } catch (ForbiddenRequestException $e) {
            $this->sendResponse(new ForbiddenRequestResponse($e->getMessage()));
        } catch (BadRequestException $e) {
            $this->sendResponse(new BadRequestResponse($e->getMessage()));
        }
    }

    /**
     * @throws ForbiddenRequestException
     */
    protected function authenticateRequest(): bool
    {
        $apiId = $this->getRequest()->getParameter(AuthenticationRepository::PARAM_API_ID);
        $apiKey = $this->getRequest()->getParameter(AuthenticationRepository::PARAM_API_KEY);

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
}