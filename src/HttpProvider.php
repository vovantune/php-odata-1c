<?php
declare(strict_types=1);

namespace OData;

use GuzzleHttp\Exception\BadResponseException;
use SaintSystems\OData\Exception\ODataException;
use SaintSystems\OData\GuzzleHttpProvider;
use SaintSystems\OData\HttpMethod;
use SaintSystems\OData\HttpRequestMessage;

class HttpProvider extends GuzzleHttpProvider
{
    const JSON_FORMAT_ARG = '$format=json';

    /** @inheritDoc */
    public function send(HttpRequestMessage $request) {
        $options = [
            'headers' => $request->headers,
            'stream' =>  $request->returnsStream,
            'timeout' => $this->timeout,
        ];

        if ($request->method == HttpMethod::POST || $request->method == HttpMethod::PUT || $request->method == HttpMethod::PATCH) {
            $options['body'] = $request->body;
        }

        $url = preg_replace_callback('%[^:/?#&=\.]+%usD', function ($args) {
            return rawurlencode($args[0]);
        }, str_replace('?&$', '?$', $request->requestUri));
        if (!strstr($url, self::JSON_FORMAT_ARG)) {
            $url .= (strstr($url, '?') ? '&' : '?') . self::JSON_FORMAT_ARG;
        }

        try {
            $result = $this->http->request(
                $request->method,
                $url,
                $options
            );
        } catch (BadResponseException $exception) {
            $response = $exception->getResponse();
            $contents = $response->getBody()->getContents();
            switch ($response->getStatusCode()) {
                case 400:
                    $responseJson = json_decode($contents, true);
                    if ($responseJson) {
                        throw new ODataException($responseJson['odata.error']['message']['value'], $responseJson['odata.error']['code']);
                    } else {
                        throw new \Exception($contents);
                    }

                case 500:
                    $responseJson = json_decode($contents, true);
                    if ($responseJson) {
                        throw new ODataException($responseJson['odata.error']['message']['value'], $responseJson['odata.error']['code']);
                    } else {
                        throw new \Exception($contents);
                    }

                default:
                    throw new \Exception($contents);
            }
        }

        return $result;
    }
}