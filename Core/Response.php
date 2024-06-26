<?php

namespace Core;

class Response
{
    private static string $responseBody = '';
    public const RESPONSE_CONTENT_TYPE_HTML = 'text/html';
    public const RESPONSE_CONTENT_TYPE_JSON = 'application/json';
    private static string $contentType = self::RESPONSE_CONTENT_TYPE_HTML;

    public function setContentType(string $type): void
    {
        self::$contentType = $type;
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

    public function redirect(string $url): void
    {
        header("location:$url");
        exit;
    }




    public function sendAndExit(mixed $data = null)
    {
        if ($data)
            $this->setResponseBody($data);

        header('Content-Type: ' . self::$contentType);
        echo self::$responseBody;
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