<?php

namespace App\ApiModule\Responses;

use Nette\Application\Response;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\Json;

class BadRequestResponse implements Response
{
    private ?string $reason;
    private \stdClass $payload;

    public function __construct(
        string $reason = null
    )
    {
        $this->reason = $reason;

        $this->payload = $this->generatePayload();
    }

    function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        $httpResponse->setCode(IResponse::S400_BadRequest, $this->reason);
        $httpResponse->setContentType('application/json', 'utf-8');
        echo Json::encode($this->payload);
    }

    private function generatePayload(): \stdClass
    {
        $payload = new \stdClass();
        $payload->responseType = 'BadRequestResponse';
        $payload->code = IResponse::S400_BadRequest;
        $payload->reason = $this->reason ?? '';

        return $payload;
    }
}