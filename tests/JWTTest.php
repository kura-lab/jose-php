<?php
require_once('../src/jwt.php');

use \kuralab\JWT;

class StackTest extends PHPUnit_Framework_TestCase
{
  public function testExcute()
  {
    $client_id  = 'client123';
    $secret     = 'xxxyyyzzz';
    $nonce      = 'aaabbbccc';

    $jwt1 = new JWT();
    $encodeJwt = $jwt1->encode(
      'HS256',
      'https://example.com',
      $client_id,
      1390318758,
      $nonce,
      $secret
    );

    $jwt2 = new JWT( $encodeJwt );
    $decodedJwt = $jwt2->decode();
    $this->assertEquals( 'JWT', $decodedJwt['header']['typ'] );
    $this->assertEquals( 'HS256', $decodedJwt['header']['alg'] );
    $this->assertEquals( 'https://example.com', $decodedJwt['payload']['iss'] );
    $this->assertEquals( 1390318758, $decodedJwt['payload']['exp'] );
    $this->assertEquals( $nonce, $decodedJwt['payload']['nonce'] );
  }
}
