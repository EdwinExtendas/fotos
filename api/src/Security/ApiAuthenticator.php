<?php

namespace App\Security;

use App\Entity\User;
use App\Service\FirebaseAuthentication;
use Doctrine\DBAL\Exception\NonUniqueFieldNameException;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\Auth\Token\Exception\ExpiredToken;
use Firebase\Auth\Token\Exception\InvalidToken;
use GuzzleHttp\Exception\ClientException;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Lcobucci\JWT\Token;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Exception\ConflictingHeadersException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class ApiAuthenticator
 *
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class ApiAuthenticator implements AuthenticatorInterface
{
    public const AUTH_TOKEN_HEADER = 'X-AUTH-TOKEN';
    public const AUTH_TOKEN_COOKIE = 'token';
    public const AUTH_REFRESH_TOKEN_COOKIE = 'refresh_token';

    private $em;
    private $firebase_authentication;
    private $domain;

    public function __construct(String $domain, EntityManagerInterface $em, FirebaseAuthentication $firebase_authentication)
    {
        $this->domain = $domain;
        $this->em = $em;
        $this->firebase_authentication = $firebase_authentication;
    }

    /**
     * Called when authentication is needed, but it's not sent
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException|null $auth_exception
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request, AuthenticationException $auth_exception = null)
    {
        $data = [
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Called on every request.
     * Returns the Firebase user if it passes the token authentication
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array|mixed
     * @throws \Symfony\Component\HttpFoundation\Exception\ConflictingHeadersException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function getCredentials(Request $request)
    {
        $token = $this->getToken($request);

        if (null === $token)
        {
            throw new ConflictingHeadersException('Header: Token not found.');
        }

        $service_account = ServiceAccount::fromArray($this->firebase_authentication->fromJsonFile());

        $firebase = (new Factory)
            ->withServiceAccount($service_account)
            ->create();

        try {
            $verified_id_token = $firebase->getAuth()->verifyIdToken($token);
        } catch (AuthException $e) {
            throw new HttpException(498, sprintf('[%s] %s', \get_class($e), $e->getMessage()));
        } catch (ExpiredToken $e) {
            // we need to go to a function that can return a response
            throw new AuthenticationException('Token expired',497);
        } catch (InvalidToken $e) {
            throw new HttpException(498, sprintf('[%s] %s', \get_class($e), $e->getMessage()));
        }
        return [
            'token' => $verified_id_token,
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return null|string
     */
    private function getToken(Request $request)
    {
        if($request->headers->has(self::AUTH_TOKEN_HEADER)) {
            return $request->headers->get(self::AUTH_TOKEN_HEADER);
        }

        if($request->cookies->has(self::AUTH_TOKEN_COOKIE)) {
            return $request->cookies->get(self::AUTH_TOKEN_COOKIE);
        }

        return null;
    }

    /**
     * Returns the User object if it exists, else create one and return that
     * @param mixed $credentials
     * @param \Symfony\Component\Security\Core\User\UserProviderInterface $user_provider
     * @return \App\Entity\User|null|object|\Symfony\Component\Security\Core\User\UserInterface
     * @throws \OutOfBoundsException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function getUser($credentials, UserProviderInterface $user_provider)
    {
        $token = $credentials['token'];

        if (!$token instanceof Token)
        {
            return null;
        }

        //check if we have the user
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(
                ['user_id' => $token->getClaim('user_id')]
            );

        if (!$user)
        {
           return null;
        }

        return $user;
    }

    /**
     * you can do extra authentication here,
     * i didn't use it because it only returns a boolean and i needed a value.
     * @param mixed $credentials
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * Error handler.
     * It doesn't work in getCredentials for some reason...
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     * @return null|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $response = new JsonResponse(
            ['message' => strtr($exception->getMessageKey(), $exception->getMessageData())],
            498
        );

        if ($exception->getCode() === 497 && $request->cookies->has(self::AUTH_REFRESH_TOKEN_COOKIE))
        {
            $response = new JsonResponse(
                ['message' => strtr($exception->getMessageKey(), $exception->getMessageData())],
                497
            );

            try {
                $token = $this->firebase_authentication->refreshSecureFirebaseToken(
                    $request->cookies->get(self::AUTH_REFRESH_TOKEN_COOKIE)
                );
            } catch (ClientException $e) {
                return new Response($e->getMessage(), 400);
            }

            $response->headers->clearCookie(self::AUTH_TOKEN_COOKIE);
            $response->headers->setCookie(
                new Cookie(
                    self::AUTH_TOKEN_COOKIE,
                    $token,
                    0,
                    '/',
                    $this->domain
                )
            );
        }

        return $response;
    }

    /**
     * You can do extra stuff if the authentication is successful here
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param string $providerKey
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $provider_key)
    {
        return null;
    }

    /**
     * Not using this
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     * Shortcut to create a PostAuthenticationGuardToken for you, if you don't really
     * care about which authenticated token you're using.
     *
     * @param UserInterface $user
     * @param string        $providerKey
     *
     * @return PostAuthenticationGuardToken
     */
    public function createAuthenticatedToken(UserInterface $user, $providerKey)
    {
        return new PostAuthenticationGuardToken(
            $user,
            $providerKey,
            $user->getRoles()
        );
    }
}