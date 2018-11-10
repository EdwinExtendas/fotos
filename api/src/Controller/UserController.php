<?php

namespace App\Controller;

use App\Security\ApiAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
use App\Service\FirebaseAuthentication;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;


/**
 * Class UserController
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class UserController extends Controller
{
    /**
     * @Route(
     *     name="login",
     *     path="/login"
     * )
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \RuntimeException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function loginAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password']))
        {
            return new JsonResponse(
                ['message' => 'No correct parameters'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $auth = new FirebaseAuthentication();
        $tokens = $auth->getSecureFirebaseToken(
            $data['email'],
            $data['password']
        );

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                "email" => $data['email']
            ]);

        if (!$user) {
            throw new BadCredentialsException('Account doesn\'t have permissions');
        }

        $roles = $user->getRoles();
        if (!\in_array(User::ROLE_ADMIN, $roles, true))
        {
            throw new BadCredentialsException('Account doesn\'t have permissions');
        }

        $response = new JsonResponse([
            'success' => true
        ]);

        $domain = $this->container->getParameter('cors_cookie_set_domain');

        // set the HttpOnly cookie with the token
        $response->headers->setCookie(
            new Cookie(
                ApiAuthenticator::AUTH_TOKEN_COOKIE,
                $tokens[ApiAuthenticator::AUTH_TOKEN_COOKIE],
                0,
                '/',
                $domain
            )
        );

        // set the HttpOnly cookie with the refresh_token
        $response->headers->setCookie(
            new Cookie(
                ApiAuthenticator::AUTH_REFRESH_TOKEN_COOKIE,
                $tokens[ApiAuthenticator::AUTH_REFRESH_TOKEN_COOKIE],
                0,
                '/',
                $domain
            )
        );

        $user_data = [
            'email' => $user->getEmail(),
        ];

        // set the user data cookie
        $response->headers->setCookie(
            new Cookie(
                'user',
                json_encode($user_data),
                0,
                '/',
                $domain,
                false,
                false
            )
        );

        return $response;
    }
}