<?php

namespace App\Service;

use App\Security\ApiAuthenticator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Class FirebaseAuthentication
 *
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class FirebaseAuthentication
{
    private $file_path;

    public function __construct()
    {
        $this->file_path =  __DIR__ . '/../../google-service-account.json';
    }

    /**
     * @param String $email
     * @param String $password
     * @return mixed
     * @throws \RuntimeException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function getSecureFirebaseToken(String $email, String $password)
    {
        $json = $this->fromJsonFile();

        $client = new \GuzzleHttp\Client(
            [
                'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword',
                'handler' => \GuzzleHttp\HandlerStack::create(),
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]
        );

        $response = $client->post(
            '?key=' . $json['api_key'],
            [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                    'returnSecureToken' => true
                ]

            ]
        );

        $result = json_decode($response->getBody()->getContents(), true);
        if (!isset($result['idToken'], $result['refreshToken'])) {
            throw new BadRequestHttpException('Incorrect login details');
        }

        return [
            ApiAuthenticator::AUTH_TOKEN_COOKIE => $result['idToken'],
            ApiAuthenticator::AUTH_REFRESH_TOKEN_COOKIE => $result['refreshToken']
        ];
    }

    /**
     * @param $refresh_token
     * @return bool
     * @throws \RuntimeException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function refreshSecureFirebaseToken($refresh_token)
    {
        $json = $this->fromJsonFile();

        $client = new \GuzzleHttp\Client(
            [
                'base_uri' => 'https://securetoken.googleapis.com/v1/token',
                'handler' => \GuzzleHttp\HandlerStack::create(),
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]
        );

        $response = $client->post(
            '?key=' . $json['api_key'],
            [
                'json' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refresh_token
                ]

            ]
        );

        $result = json_decode($response->getBody()->getContents(), true);
        if (!isset($result['id_token'])) {
            throw new BadRequestHttpException('Refresh token didn\t work');
        }

        return $result['id_token'];
    }

    /**
     * @param string $json
     * @param bool $assoc
     * @return mixed
     * @throws \http\Exception\InvalidArgumentException
     */
    public function fromJson(string $json, bool $assoc = true)
    {
        $data = \json_decode($json, $assoc);
        if (JSON_ERROR_NONE !== json_last_error())
        {
            throw new InvalidArgumentException(
                'json_decode error: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * @return mixed
     * @throws \http\Exception\InvalidArgumentException
     */
    public function fromJsonFile()
    {
        if (!file_exists($this->file_path))
        {
            throw new InvalidArgumentException(sprintf('%s does not exist.', $this->file_path));
        }

        if (is_link($this->file_path))
        {
            $this->file_path = (string)realpath($this->file_path);
        }

        if (!is_file($this->file_path))
        {
            throw new InvalidArgumentException(sprintf('%s is not a file.', $this->file_path));
        }

        if (!is_readable($this->file_path))
        {
            throw new InvalidArgumentException(sprintf('%s is not readable.', $this->file_path));
        }

        $json_string = file_get_contents($this->file_path);

        return $this->fromJson($json_string);
    }
}