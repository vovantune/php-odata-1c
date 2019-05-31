<?php
declare(strict_types=1);

namespace OData\Test;


use ArtSkills\TestSuite\AppTestCase;
use ArtSkills\TestSuite\Mock\MethodMocker;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use OData\OData1CClient;
use SaintSystems\OData\HttpMethod;

class OData1CClientTest extends AppTestCase
{
    /** Отбор данных по разным условиям */
    public function testGet() {
        $odataServiceUrl = 'http://5.10.141.89:55080/Fotin_Fotin_bp_kdnlvcd';
        $username = 'int3';
        $password = 'sVwPD9xW#ds';

        $expOptions = [
            'headers' =>
                [
                    'Content-Type'     => 'application/json',
                    'OData-MaxVersion' => '4.0',
                    'OData-Version'    => '4.0',
                    'Prefer'           => 25,
                    'User-Agent'       => 'odata-sdk-php-0.1.0',
                    'Authorization'    => 'Basic aW50MzpzVndQRDl4VyNkcw==',
                ],
            'stream'  => false,
            'timeout' => 0,
        ];

        MethodMocker::mock(Client::class, 'request')
            ->expectArgsList([
                [
                    new HttpMethod(HttpMethod::GET),
                    $odataServiceUrl . '/odata/standard.odata/Catalog_%D0%9A%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%82%D0%BE%D1%80%D0%95%D0%B4%D0%B8%D0%BD%D0%B8%D1%86%D0%98%D0%B7%D0%BC%D0%B5%D1%80%D0%B5%D0%BD%D0%B8%D1%8F?%24filter=DeletionMark%20eq%20false&$format=json',
                    $expOptions,
                ],
                [
                    new HttpMethod(HttpMethod::GET),
                    $odataServiceUrl . '/odata/standard.odata/Catalog_%D0%9A%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%82%D0%BE%D1%80%D0%95%D0%B4%D0%B8%D0%BD%D0%B8%D1%86%D0%98%D0%B7%D0%BC%D0%B5%D1%80%D0%B5%D0%BD%D0%B8%D1%8F%2896289644-7af3-11e9-92fa-000c2979dd34%29?&$format=json',
                    $expOptions,
                ],
                [
                    new HttpMethod(HttpMethod::GET),
                    $odataServiceUrl . '/odata/standard.odata/Catalog_%D0%9A%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%82%D0%BE%D1%80%D0%95%D0%B4%D0%B8%D0%BD%D0%B8%D1%86%D0%98%D0%B7%D0%BC%D0%B5%D1%80%D0%B5%D0%BD%D0%B8%D1%8F?%24filter=%28Code%20eq%20%27736%27%20and%20Description%20eq%20%27%D1%80%D1%83%D0%BB%27%29%20or%20%28Code%20eq%20%27778%27%20and%20Description%20eq%20%27%D1%83%D0%BF%D0%B0%D0%BA%27%29&$format=json',
                    $expOptions,
                ],
                [
                    new HttpMethod(HttpMethod::GET),
                    $odataServiceUrl . '/odata/standard.odata/Catalog_%D0%9A%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%82%D0%BE%D1%80%D0%95%D0%B4%D0%B8%D0%BD%D0%B8%D1%86%D0%98%D0%B7%D0%BC%D0%B5%D1%80%D0%B5%D0%BD%D0%B8%D1%8F?%24filter=Code%20eq%20%27736%27%20and%20Description%20eq%20%27%D1%80%D1%83%D0%BB%27&$format=json',
                    $expOptions,
                ],
                [
                    new HttpMethod(HttpMethod::GET),
                    $odataServiceUrl . '/odata/standard.odata/AccountingRegister_%D0%A5%D0%BE%D0%B7%D1%80%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BD%D1%8B%D0%B9/Balance%28AccountCondition=%27Account/Code%20eq%20%2743%27%20or%20Account/Code%20eq%20%2741.01%27%27%29?%24select=Account/Code%2CAccount/Description%2C%D0%92%D0%B0%D0%BB%D1%8E%D1%82%D0%B0/Code%2C%D0%92%D0%B0%D0%BB%D1%8E%D1%82%D0%B0/Description%2C%D0%9E%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F/Description%2C%D0%9E%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F/%D0%98%D0%9D%D0%9D%2CExtDimension1%2CExtDimension3%2C%D0%A1%D1%83%D0%BC%D0%BC%D0%B0Balance%2C%D0%9A%D0%BE%D0%BB%D0%B8%D1%87%D0%B5%D1%81%D1%82%D0%B2%D0%BEBalance&%24expand=Account%2C%D0%92%D0%B0%D0%BB%D1%8E%D1%82%D0%B0%2C%D0%9E%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F%2CExtDimension1%2CExtDimension3&%24top=5&$format=json',
                    $expOptions,
                ],
            ])
            ->willReturnValue(new Response());

        // отбор с булевом
        $oDataClient = new OData1CClient($odataServiceUrl, $username, $password);
        $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->where('DeletionMark', '=', false)
            ->get();

        // отбор по ID
        $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->find('96289644-7af3-11e9-92fa-000c2979dd34');

        // несколько условий объединённых скобками
        $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->where([
                ['Code', '=', '736'],
                ['Description', '=', 'рул'],
            ])
            ->orWhere([
                ['Code', '=', '778'],
                ['Description', '=', 'упак'],
            ])
            ->get();

        // отбор И
        $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->where('Code', '=', '736')
            ->where('Description', '=', 'рул')
            ->get();

        // отбор по регистру с ограничением по кол-ву
        $oDataClient->from("AccountingRegister_Хозрасчетный/Balance(AccountCondition='Account/Code eq '43' or Account/Code eq '41.01'')")
            ->select([
                'Account/Code',
                'Account/Description',
                'Валюта/Code',
                'Валюта/Description',
                'Организация/Description',
                'Организация/ИНН'
                , 'ExtDimension1',
                'ExtDimension3',
                'СуммаBalance',
                'КоличествоBalance',
            ])->expand(['Account', 'Валюта', 'Организация', 'ExtDimension1', 'ExtDimension3'])
            ->take(5)
            ->get();
    }
}