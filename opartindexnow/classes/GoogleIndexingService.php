<?php

/**
 * 2007-2025 Olivier CLEMENCE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to Olivier CLEMENCEso we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Olivier CLEMENCE to newer
 * versions in the future. If you wish to customize Olivier CLEMENCE for your
 * needs please refer to Olivier CLEMENCE for more information.
 *
 * @author    Olivier CLEMENCE
 * @copyright 2007-2022 Olivier CLEMENCE
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Olivier CLEMENCE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class GoogleIndexingService
{
    private $serviceAccountPath;

    public function __construct()
    {
        $this->serviceAccountPath = _PS_ROOT_DIR_ . '/service_account_file.json';
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function getAccessToken()
    {

        if(!Configuration::get('GOOGLE_INDEXING_CONSENT')){
            return null;
        }
        
        if (!file_exists($this->serviceAccountPath)) {
            return null;
        }

        $jsonKey = json_decode(file_get_contents($this->serviceAccountPath), true);
        if (!$jsonKey) {
            return null;
        }

        $header = ["alg" => "RS256", "typ" => "JWT"];
        $now = time();
        $claims = [
            "iss" => $jsonKey['client_email'],
            "scope" => "https://www.googleapis.com/auth/indexing",
            "aud" => "https://oauth2.googleapis.com/token",
            "exp" => $now + 3600,
            "iat" => $now
        ];

        $jwtHeader = $this->base64UrlEncode(json_encode($header));
        $jwtPayload = $this->base64UrlEncode(json_encode($claims));
        $signatureInput = $jwtHeader . '.' . $jwtPayload;

        $privateKey = openssl_get_privatekey($jsonKey['private_key']);
        if (!$privateKey) {
            return null;
        }

        openssl_sign($signatureInput, $signature, $privateKey, 'sha256WithRSAEncryption');
        openssl_free_key($privateKey);

        $jwtSignature = $this->base64UrlEncode($signature);
        $jwt = $signatureInput . '.' . $jwtSignature;

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query([
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]),
                'ignore_errors' => true,
            ]
        ]);

        $response = file_get_contents('https://oauth2.googleapis.com/token', false, $context);
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    public function notifyUrl($url, $type = 'URL_UPDATED')
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $body = json_encode([
            'url' => $url,
            'type' => $type,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAuthorization: Bearer $accessToken\r\n",
                'content' => $body,
                'ignore_errors' => true,
            ]
        ]);

        $response = file_get_contents('https://indexing.googleapis.com/v3/urlNotifications:publish', false, $context);
        return json_decode($response, true);
    }
}
