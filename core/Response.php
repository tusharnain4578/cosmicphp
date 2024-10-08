<?php

namespace Core;

use Core\Services\Session;

class Response
{
    private Session $session;
    private static string $responseBody = '';
    public const RESPONSE_CONTENT_TYPE_HTML = 'text/html';
    public const RESPONSE_CONTENT_TYPE_JSON = 'application/json';
    private static string $contentType = self::RESPONSE_CONTENT_TYPE_HTML;
    private static Response $sharedResponse;
    private static int $statusCode = 200;
    public function __construct()
    {
        $this->session = session();
    }
    public static function getInstance(bool $shared = false): Response
    {
        if ($shared)
            return self::$sharedResponse ??= new Response;
        return new Response;
    }
    public function setContentType(string $type): void
    {
        self::$contentType = $type;
    }
    public function setStatusCode(int $statusCode): self
    {
        self::$statusCode = $statusCode;
        return $this;
    }
    public function getContentType(): string
    {
        return self::$contentType;
    }

    public function json(array|string $data): self
    {
        $this->setContentType(self::RESPONSE_CONTENT_TYPE_JSON);
        self::$responseBody = is_string($data) ? $data : json_encode($data);
        return $this;
    }

    public function html(string $htmlContent): self
    {
        self::$responseBody = $htmlContent;
        $this->setContentType(self::RESPONSE_CONTENT_TYPE_HTML);
        return $this;
    }

    public function view(string $view, array $data = []): self
    {
        $viewObj = new View;
        self::$responseBody = $viewObj->render(view: $view, data: $data);
        $this->setContentType(self::RESPONSE_CONTENT_TYPE_HTML);
        return $this;
    }


    public function redirect(string $url, array $flash = []): void
    {
        if (!empty($flash))
            $this->session->setFlash($flash);
        header("location:$url");
        exit;
    }
    public function redirectBack(array $flash = []): void
    {
        if (!empty($flash))
            $this->session->setFlash($flash);
        $this->redirect(request()->getPreviousUrl());
    }

    public function send(mixed $data = null)
    {
        if ($data)
            $this->setResponseBody($data);

        header('Content-Type: ' . self::$contentType);
        http_response_code(self::$statusCode);
        echo self::$responseBody;
    }

    public function exit(mixed $data = null)
    {
        $this->send($data);
        exit;
    }

    public function setResponseBody(string|array|Response $data): self
    {
        if (is_string($data)) // string
            $this->html(htmlContent: $data);
        else if (is_array($data))
            $this->json(data: $data);
        else // Response Object
        {
            // do nothing, because the response data is static, and will be same as other objects
        }

        return $this;
    }

    public function getResponseBody(): string
    {
        return self::$responseBody;
    }

    /**
     * Only work when response body is of type application/json
     */
    public function appendResponseHtml(string $content): void
    {
        if (self::$contentType === Response::RESPONSE_CONTENT_TYPE_HTML) {
            self::$responseBody .= $content;
        }
    }
}