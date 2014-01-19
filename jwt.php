<?php
namespace kuralab;

/**
 * Json Web Token Class
 */
class JWT
{
  private $header;
  private $payload;
  private $signature;

  private $headerArray  = array();
  private $payloadArray = array();

  public function __construct( $idToken = null )
  {
    if ( !is_null( $idToken ) && is_string( $idToken ) ) {
      $explodedIdToken = explode( '.', $idToken );
      if ( count( $explodedIdToken ) == 3 ) {
        $this->header    = $explodedIdToken[0];
        $this->payload   = $explodedIdToken[1];
        $this->signature = $explodedIdToken[2];
        $this->headerArray  = json_decode( base64_decode( $this->header ), true );
        if ( $this->headerArray == null ) {
        }
        $this->payloadArray = json_decode( base64_decode( $this->payload ), true );
        if( $this->payloadArray == null ) {
        }
      }
    }
  }

  public function encode()
  {
  }

  public function decode()
  {
    if ( is_null( $this->headerArray ) ||
      is_null( $this->payloadArray ) || is_null( $this->signature ) ) {
      return null;
    }
    $result = array(
      $this->headerArray,
      $this->payloadArray,
      $this->signature,
    );
    return $result;
  }

  public function getHeader( $key = null )
  {
    if ( is_string( $key ) && array_key_exists( $key, $this->headerArray ) ) {
      return $this->headerArray[$key];
    }
    return $this->headerArray;
  }

  public function getPayload( $key = null )
  {
    if ( is_string( $key ) && array_key_exists( $key, $this->payloadArray ) ) {
      return $this->payloadArray[$key];
    }
    return $this->payloadArray;
  }

  public function verify( $issuer, $audience, $nonce, $secret )
  {
    /**
     * check header
     */
    if ( $this->headerArray['typ'] != 'JWT' ) {
      throw new \Exception( 'unexpected type' );
    }

    if ( $this->headerArray['alg'] == null ) {
      throw new \Exception( 'algorithm is null' );
    }

    /**
     * check payload
     */
    // iss
    if ( $this->payloadArray['iss'] != $issuer ) {
      throw new \Exception( 'invalid iss' );
    }

    // aud
    if ( $this->payloadArray['aud'] != $audience ) {
      throw new \Exception( 'invalid aud' );
    }

    // exp
    if ( $this->payloadArray['exp'] < time() ) {
      throw new \Exception( 'expired id token' );
    }

    // iat
    if ( time() - $this->payloadArray['iat'] > 600 ) {
      throw new \Exception( 'expired iat' );
    }

    // nonce
    if ( $this->payloadArray['nonce'] != $nonce ) {
      throw new \Exception( 'invalid nonce' );
    }

    /**
     * check signature
     */
    $sig = $this->generateSignature(
      $this->header,
      $this->payload,
      $this->headerArray['alg'],
      $secret
    );
    if ( $this->signature != $sig ) {
      throw new \Exception( 'signature error' );
    }
  }

  private function generateSignature( $header, $payload, $algorithm, $secret )
  {
    $supportedAlgorithm = array(
      'HS256' => 'sha256',
      'HS382' => 'sha384',
      'HS512' => 'sha512',
    );

    if ( !array_key_exists( $algorithm, $supportedAlgorithm ) ) {
      throw new Exception( 'unsupported algorithm' );
    }

    $hash = hash_hmac(
      $supportedAlgorithm[$algorithm],
      $header . '.' . $payload,
      $secret,
      true
    );

    $encodedHash = base64_encode( $hash );
    $signature = str_replace( array( '=' ), array( '' ), 
    str_replace( array( '+', '/' ), array( '-', '_' ), $encodedHash ) );

    return $signature;
  }
}
