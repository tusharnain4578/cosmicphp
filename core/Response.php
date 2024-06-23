<?php

namespace Core;

class Response
{
    private string $responseBody = '';
    public const RESPONSE_CONTENT_TYPE_HTML = 'text/html';
    public const RESPONSE_CONTENT_TYPE_JSON = 'application/json';


    public function setContectType(string $type): void
    {
        header('Content-Type: ' . $type);
    }

    public function json(array $data): self
    {
        $this->setContectType(self::RESPONSE_CONTENT_TYPE_JSON);
        $this->responseBody = json_encode($data);
        return $this;
    }

    public function html(string $htmlContent): self
    {
        $this->responseBody = $htmlContent;
        $this->setContectType(self::RESPONSE_CONTENT_TYPE_HTML);
        return $this;
    }

    public function view(string $view, array $data = []): self
    {
        $viewObj = new View;
        $this->responseBody = $viewObj->render(view: $view, data: $data);
        $this->setContectType(self::RESPONSE_CONTENT_TYPE_HTML);
        return $this;
    }



    public function send($data = null)
    {
        if ($data) {
            if (is_array($data)) {
                $this->json(data: $data);
            } elseif (is_string($data)) {
                $this->html(htmlContent: $data);
            } else if ($data instanceof Response) {
                $data->send(); // it is not causing the recursion, if there was $data param here, then i will cause recursion
            }
        }


        if ($this->responseBody)
            echo $this->responseBody;

        exit; // good bye!
    }

}