<?php

namespace Core\Security;

use Core\Services\Session;
use Core\Utilities\Rex;

class Csrf
{
    private string $tokenName;

    private bool $regenerateToken;

    public bool $redirectBackOnFailure;

    private int $expire;
    private array $csrfData;

    private Session $session;
    private ?string $requestToken = null;

    private bool $isTokenVerified = false;

    private const string CSRF_SESSION_KEY = '__csrf_data';


    public function __construct()
    {
        $this->session = session();
        $this->session->start(); // already running check is inside the Session's start method.
        $this->csrfData = $this->session->get(self::CSRF_SESSION_KEY, []);
        $this->tokenName = env('security.csrf.token_name', 'csrf_token');
        $this->expire = env('security.csrf.expire', 0);
        $this->regenerateToken = env('security.csrf.regenerate_token', false);
        $this->redirectBackOnFailure = env('security.csrf.redirect_back_on_failure', false);

        $this->initRequestToken();
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

    private function initRequestToken()
    {
        $this->requestToken = request()->inputPost($this->tokenName) ?? '';
        request()->removeVar($this->tokenName());
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

        if (($expire > 0) && ($generatedAt + $expire) < Rex::timestamp()) {
            return false;
        }

        $this->isTokenVerified = $token === $this->requestToken;

        if ($this->isTokenVerified && $this->regenerateToken)
            $this->generateNewToken();

        return $this->isTokenVerified;
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