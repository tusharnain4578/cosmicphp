<?php

namespace Core\Security;

use Core\Services\Session;
use Core\Utilities\Rex;

class Csrf
{
    private string $tokenName;

    private int $expire;

    private array $csrfData;

    private Session $session;

    private const string CSRF_SESSION_KEY = '__csrf_data';


    public function __construct()
    {
        $this->session = session();
        $this->session->start(); // already running check is inside the Session's start method.
        $this->csrfData = $this->session->get(self::CSRF_SESSION_KEY, []);
        $this->tokenName = env('session.token_name', 'csrf_token');
        $this->expire = env('session.expire', 0);
    }
    public function getToken(): string
    {
        if (!isset($this->csrfData) or empty($this->csrfData))
            $this->generateNewToken();
        return $this->csrfData['token'];
    }
    private function generateNewToken(): string
    {
        $token = bin2hex(random_bytes(32));

        $this->csrfData = [
            'token' => $token,
            'generated_at' => Rex::timestamp(),
            'expire' => $this->expire
        ];

        $this->session->set(self::CSRF_SESSION_KEY, $this->csrfData);

        return $token;
    }

    private function getRequestToken(): string
    {
        return request()->inputPost($this->tokenName) ?? '';
    }
    public function verifyToken(): bool
    {
        if (!in_array(strtoupper(request()->method()), ['POST']))
            return true;

        if (!$this->csrfData || empty($this->csrfData))
            return false;

        $token = $this->csrfData['token'];
        $generatedAt = $this->csrfData['generated_at'];
        $expire = $this->csrfData['expire'];

        if (($generatedAt + $expire) < Rex::timestamp()) {
            return false;
        }

        $requestToken = $this->getRequestToken();

        request()->removeVar($this->tokenName());
        return $token === $requestToken;
    }
    public function tokenName(): string
    {
        return $this->tokenName;
    }
    public function hash(): string
    {
        return $this->getToken();
    }
    public function field(): string
    {
        return sprintf('<input type="hidden" name="%s" value="%s"/>', $this->tokenName, $this->getToken());
    }


}