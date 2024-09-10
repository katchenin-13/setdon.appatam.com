<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Passport\Badge\BadgeInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JwtAuthenticator implements AuthenticatorInterface
{
    private JWTTokenManagerInterface $jwtTokenManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(JWTTokenManagerInterface $jwtTokenManager, TokenStorageInterface $tokenStorage)
    {
        $this->jwtTokenManager = $jwtTokenManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function supports(Request $request): ?bool
    {
        // Check if the request contains an Authorization header with Bearer token
        return $request->headers->has('Authorization');
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
            new UserBadge($user->getUsername()), // Assuming the user has a `getUsername()` method
            new Badge()
        );
    }

    public function createToken(Request $request, Passport $passport): ?TokenInterface
    {
        // Create a token to be used in the application (e.g., a JWT or Symfony token)
        // Here you may need to create a custom Token class that matches your application's needs
        // For example:
        // return new UsernamePasswordToken($passport->getUser(), null, 'main', $passport->getUser()->getRoles());

        // Example placeholder implementation
        return new TokenInterface(); // Replace this with actual implementation
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Handle successful authentication
        return null;
    }

    public function onAuthenticationFailure(Request $request, \Throwable $exception): ?Response
    {
        // Handle authentication failure (return an error response)
        return new Response('Authentication Failed', Response::HTTP_UNAUTHORIZED);
    }
}
