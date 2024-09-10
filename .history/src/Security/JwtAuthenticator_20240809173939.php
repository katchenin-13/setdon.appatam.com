<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\Badge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JwtAuthenticator implements AuthenticatorInterface
{
    private JWTTokenManagerInterface $jwtTokenManager;

    public function __construct(JWTTokenManagerInterface $jwtTokenManager)
    {
        $this->jwtTokenManager = $jwtTokenManager;
    }

    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !preg_match('/^Bearer\s+(?<token>.+)$/i', $authorizationHeader, $matches)) {
            throw new AuthenticationException('Invalid or missing authorization header.');
        }

        $token = $matches['token'];

        try {
            $user = $this->jwtTokenManager->parse($token);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid JWT token.');
        }

        return new Passport(
            new UserBadge($user->getUsername()),
            new Badge()
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // JWT authentication doesn't require redirection on success
        return null;
    }

    public function onAuthenticationFailure(Request $request, \Throwable $exception): ?Response
    {
        // Handle authentication failure (return an error response)
        return new Response('Authentication Failed', Response::HTTP_UNAUTHORIZED);
    }
}
