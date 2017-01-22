<?php
namespace kuralab\jose;

use kuralab\base64url\Base64url;

/**
 * Json Web Token Class
 */
class JsonWebToken
{
    private static $supportedAlgorithm = array(
        'HS256' => 'sha256',
        'HS382' => 'sha384',
        'HS512' => 'sha512',
        'RS256' => 'sha256',
        'RS382' => 'sha384',
        'RS512' => 'sha512',
    );

    private $header;
    private $payload;
    private $signature;

    private $headerArray  = array();
    private $payloadArray = array();

    public function __construct($idToken = null)
    {
        if (!is_null($idToken) && is_string($idToken)) {
            $explodedIdToken = explode('.', $idToken);
            if (count($explodedIdToken) == 3) {
                $this->header    = $explodedIdToken[0];
                $this->payload   = $explodedIdToken[1];
                $this->signature = $explodedIdToken[2];
                $this->headerArray  = json_decode(Base64url::decode($this->header), true);
                if ($this->headerArray == null) {
                    throw new \UnexpectedValueException('unexpected header');
                }
                $this->payloadArray = json_decode(Base64url::decode($this->payload), true);
                if ($this->payloadArray == null) {
                    throw new \UnexpectedValueException('unexpected payload');
                }
            }
        }
    }

    public function encode($algorithm, $issuer, $audience, $expiration, $nonce, $secret)
    {
        if (!array_key_exists($algorithm, self::$supportedAlgorithm)) {
            throw new \Exception('unsupported algorithm');
        }
        $headerArray = array(
            'alg' => $algorithm,
            'typ' => 'JWT'
        );
        $payloadArray = array(
            'iss'   => $issuer,
            'aud'   => $audience,
            'exp'   => $expiration,
            'iat'   => $this->getCurrentTime(),
            'nonce' => $nonce
        );
        $header  = Base64url::encode(json_encode($headerArray));
        $payload = Base64url::encode(json_encode($payloadArray));
        $signature = $this->generateSignature(
            $header,
            $payload,
            $algorithm,
            $secret
        );
        return implode('.', array( $header, $payload, $signature ));
    }

    public function decode()
    {
        if (is_null($this->headerArray) ||
            is_null($this->payloadArray) || is_null($this->signature)) {
            return null;
        }
        $result = array(
            'header'    => $this->headerArray,
            'payload'   => $this->payloadArray,
            'signature' => $this->signature,
        );
        return $result;
    }

    public function getHeader($key = null)
    {
        if (is_string($key) && array_key_exists($key, $this->headerArray)) {
            return $this->headerArray[$key];
        }
        return $this->headerArray;
    }

    public function getPayload($key = null)
    {
        if (is_string($key) && array_key_exists($key, $this->payloadArray)) {
            return $this->payloadArray[$key];
        }
        return $this->payloadArray;
    }

    public function verify($issuer, $audience, $nonce, $secret, $permitedAlgorithm = array(), $iatLimit = 600)
    {
        /**
         * check header
         */
        if ($this->headerArray['typ'] != 'JWT') {
            throw new \Exception('unexpected type');
        }

        if ($this->headerArray['alg'] == null) {
            throw new \Exception('algorithm is null');
        }

        if (!in_array($this->headerArray['alg'], $permitedAlgorithm)) {
            throw new \Exception('alg type is not permit');
        }

        /**
         * check payload
         */
        // iss
        if ($this->payloadArray['iss'] != $issuer) {
            throw new \Exception('invalid iss');
        }

        // aud
        if ($this->payloadArray['aud'] != $audience) {
            throw new \Exception('invalid aud');
        }

        // exp
        if ($this->payloadArray['exp'] < $this->getCurrentTime()) {
            throw new \Exception('expired id token');
        }

        // iat
        if ($this->getCurrentTime() - $this->payloadArray['iat'] > $iatLimit) {
            throw new \Exception('expired iat');
        }

        // nonce
        if ($this->payloadArray['nonce'] != $nonce) {
            throw new \Exception('invalid nonce');
        }

        /**
         * check signature
         */
        if (preg_match('/^HS/', $this->headerArray['alg'])) {
            $sig = $this->generateSignature(
                $this->header,
                $this->payload,
                $this->headerArray['alg'],
                $secret
            );
            if ($this->signature != $sig) {
                throw new \Exception('signature error');
            }
        } elseif (preg_match('/^RS/', $this->headerArray['alg'])) {
            $publicKey = openssl_pkey_get_public($secret);
            $result = openssl_verify(
                $this->header . '.' . $this->payload,
                Base64url::decode($signature),
                $publicKey,
                self::$supportedAlgorithm[$this->headerArray['alg']]
            );

            if ($result != 1) {
                throw new \Exception('signature error');
            }
        } else {
            throw new \Exception('unsupported algorithm');
        }
    }

    private function generateSignature($header, $payload, $algorithm, $secret)
    {
        if (!array_key_exists($algorithm, self::$supportedAlgorithm)) {
            throw new \Exception('unsupported algorithm');
        }

        if (preg_match('/^HS/', $algorithm)) {
            $signature = hash_hmac(
                self::$supportedAlgorithm[$algorithm],
                $header . '.' . $payload,
                $secret,
                true
            );
        } elseif (preg_match('/^RS/', $algorithm)) {
            $signature = $this->encryptRsa(
                self::$supportedAlgorithm[$algorithm],
                $header . '.' . $payload,
                $secret
            );
        } else {
            throw new \Exception('unsupported algorithm');
        }

        return Base64url::encode($signature);
    }

    private function encryptRsa($algorithm, $data, $secret)
    {
        $privateKeyId = openssl_pkey_get_private($secret);
        openssl_sign($data, $signature, $privateKeyId, $algorithm);
        openssl_free_key($privateKeyId);

        return $signature;
    }

    public function getCurrentTime()
    {
        return time();
    }
}
