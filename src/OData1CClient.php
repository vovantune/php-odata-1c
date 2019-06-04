<?php
declare(strict_types=1);

namespace OData;

use OData\Query\Builder;
use OData\Query\Grammar;
use SaintSystems\OData\ODataClient;

/**
 * Адаптированный под 1С провайдер
 *
 * @method Builder from(string $entitySet)
 */
class OData1CClient extends ODataClient
{
    const ODATA_POSTFIX = 'odata/standard.odata';

    /**
     * @inheritDoc
     *
     * @param string $dbUrl HTTP Путь к базе 1С
     * @param string $username
     * @param string $password
     */
    public function __construct($dbUrl, $username, $password) {
        if (strripos($dbUrl, self::ODATA_POSTFIX) !== (strlen($dbUrl) - strlen(self::ODATA_POSTFIX))) {
            $dbUrl .= ($dbUrl[strlen($dbUrl) - 1] !== '/' ? '/' : '') . self::ODATA_POSTFIX;
        }

        $httpProvider = new HttpProvider();

        parent::__construct($dbUrl, function ($request) use ($username, $password) {
            $request->headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
        }, $httpProvider);

        $this->setQueryGrammar(new Grammar());
    }

    /**
     * @inheritDoc
     *
     * Заменяем Builder на свой
     */
    public function query()
    {
        return new Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }
}