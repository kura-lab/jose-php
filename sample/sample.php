<?php
require_once('../src/JsonWebToken.php');

use \kuralab\jose\JsonWebToken as JWT;

// initialize parameters
$algorithm  = 'HS256';
$audience   = 'https://example.com';
$clientId   = 'YOUR_CLIENT_ID';
$secret     = 'YOUR_SECRET';
$expiration = time() + 30 * 24 * 60 * 60; // 30 days
$nonce      = 'abc123';

// encode jwt
$jwtObj = new JWT();
$encodedJwt = $jwtObj->encode(
  $algorithm,
  $audience,
  $clientId,
  $expiration,
  $nonce,
  $secret
);
echo "=== Encoded Json Web Token ===\n";
print_r( $encodedJwt );
echo "\n\n";

try {

  // verify and decode jwt
  $jwtObj = new JWT( $encodedJwt );
  $jwtObj->verify(
    $audience,
    $clientId,
    $nonce,
    $secret
  );
  $decodedJwt = $jwtObj->decode();
  echo "=== Decoded Json Web Token ===\n";
  print_r( $decodedJwt );

} catch ( Exception $e ) {
  var_dump( $e );
}
