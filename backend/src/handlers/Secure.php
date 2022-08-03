<?php

namespace Api\Handlers;

use Exception;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Escaper;

class Secure
{
    /**
     * getToken creates an access token by using email as param
     *
     * @param [str] $email
     * @return [json]
     */
    public function getToken($email)
    {
        $now     = new \DateTimeImmutable();
        $expires = $now->modify('+1 day')->getTimestamp();
        $key = "example_key";
        $payload = array(
            "iss" => "http://localhost:8080",
            "aud" => "http://localhost:8080",
            "iat" => $now->getTimestamp(),
            "nbf" => $now->getTimestamp(),
            "email" => $email,
            "exp" => $expires
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        // $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        return json_encode(["access token" => $jwt]);
    }

    /**
     * verification method validates the authenticity of the bearer token
     * @param [str] $bearer
     */
    public function verification($bearer)
    {
        //checking if token is valid(expired) or not
        try {
            $key = "example_key";
            $parser      = new Parser();
            $tokenObject = $parser->parse($bearer);
            $now           = new \DateTimeImmutable();
            $expireCheck   = $now->getTimestamp();
            $validator = new Validator($tokenObject, 100);
            $validator->validateExpiration($expireCheck);
            // $decoded = JWT::decode($bearer, new Key($key, 'HS256'));
            return;
        } catch (Exception $e) {
            print_r(json_encode(["error" => $e->getMessage()]));
            die;
        }
    }

    /**
     * sanitizeArray escapes (converts) html entities, if present, in the data to prevent malicious characters
     * @param [array] $arr
     */
    public function sanitizeArray($arr)
    {
        $escaper = new Escaper();
        foreach ($arr as $key => $val) {
            $arr[$key] = $escaper->escapeHtml($val);
        }
        return $arr;
    }
}
