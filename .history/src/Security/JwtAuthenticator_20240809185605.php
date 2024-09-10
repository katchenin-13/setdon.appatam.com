{# <?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
            new UserBadge($user->getUsername()),// Assuming the user has a `getUsername()` method
            new Badge()
        );
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        // Here, we create a token that is compatible with Symfony's security system.
        // For example, using a UsernamePasswordToken for the Symfony security system.
        return new UsernamePasswordToken(
            $passport->getUser(),
            null,
            $firewallName,
            $passport->getUser()->getRoles() // Assumes the user has a method `getRoles()`
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // No redirection required for JWT
    }

    public function onAuthenticationFailure(Request $request, \Throwable $exception): ?Response
    {
        return new Response('Authentication Failed', Response::HTTP_UNAUTHORIZED);
    }
} #}
