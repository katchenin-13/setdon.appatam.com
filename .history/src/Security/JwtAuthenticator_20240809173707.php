// src/Security/JwtAuthenticator.php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Authenticator\Passport\Badge\BadgeInterface;
use Symfony\Component\Security\Core\Authentication\Authenticator\Passport\Badge\Badges;
use Symfony\Component\Security\Core\Authentication\Passport\Badge\Badge;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\Badge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JwtAuthenticator extends AbstractLoginFormAuthenticator
{
private $jwtTokenManager;

public function __construct(JWTTokenManagerInterface $jwtTokenManager)
{
$this->jwtTokenManager = $jwtTokenManager;
}

public function authenticate(Request $request): Passport
{
$token = $request->headers->get('Authorization');

if (!$token) {
throw new \Exception('No token found');
}

$user = $this->jwtTokenManager->parse($token);

return new Passport($user);
}

public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
{
// Handle successful authentication
}

public function onAuthenticationFailure(Request $request, \Throwable $exception): ?Response
{
// Handle authentication failure
}
}