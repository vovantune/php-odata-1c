<?php
declare(strict_types=1);

namespace OData\Test\Query;

use ArtSkills\TestSuite\AppTestCase;
use OData\Query\Grammar;

class GrammarTest extends AppTestCase
{
    /** Подготовка и экранирования условия отбора */
    public function testPrepareCondition()
    {
        self::assertEquals("guid'39aef735-9a42-11e8-9429-a8a795c008e0'", Grammar::prepareCondition('39aef735-9a42-11e8-9429-a8a795c008e0'));
        self::assertEquals('true', Grammar::prepareCondition(true));
        self::assertEquals('false', Grammar::prepareCondition(false));
        self::assertEquals("'string'", Grammar::prepareCondition('string'));
        self::assertEquals("datetime'2019-06-04T09:13:18'", Grammar::prepareCondition(new \DateTime('2019-06-04 09:13:18')));
    }
}