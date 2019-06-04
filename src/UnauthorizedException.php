<?php
declare(strict_types=1);

namespace OData;

use GuzzleHttp\Exception\BadResponseException;

/**
 * Некорректный логин/пароль
 */
class UnauthorizedException extends BadResponseException
{

}