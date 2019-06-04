<?php
declare(strict_types=1);

namespace OData\Query;


use SaintSystems\OData\Query\Builder;

class Grammar extends \SaintSystems\OData\Query\Grammar
{
    /** @inheritDoc */
    protected $selectComponents = [
        'entitySet',
        'entitySetPostfix',
        'entityKey',
        'count',
        'queryString',
        'properties',
        'wheres',
        'expands',
        //'search',
        'orders',
        'skip',
        'take',
        'totalCount',
    ];

    /** @inheritDoc */
    protected function whereBasic(Builder $query, $where) {
        return $where['column'] . ' ' . $this->getOperatorMapping($where['operator']) . ' ' . static::prepareCondition($where['value']);
    }

    /**
     * Отбор по виртуальным таблицам, проведение документов
     *
     * @param Builder $query
     * @param string $entitySetPostfix
     *
     * @return string
     */
    protected function compileEntitySetPostfix(Builder $query, ?string $entitySetPostfix) {
        return (string)$entitySetPostfix;
    }

    /**
     * Экранируем и преобразуем условия отбора в перевариваемый 1С формат
     * @param mixed $value
     * @return mixed
     */
    public static function prepareCondition($value) {
        // (ex. Microsoft.OData.SampleService.Models.TripPin.PersonGender'Female')
        if (is_string($value) && preg_match("/^([\w]+\.)+([\w]+)(\'[\w]+\')$/", $value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value === true ? 'true' : 'false';
        } else if (is_string($value)) {
            if (is_uuid($value)) { // 39aef735-9a42-11e8-9429-a8a795c008e0
                return "guid'" . $value . "'";
            } else {
                return "'" . $value . "'";
            }
        } else if ($value instanceof \DateTime) {
            return "datetime'" . static::formatDate($value) . "'";
        }
        return $value;
    }

    /**
     * Преобразуем дату в необходимый формат
     * @param \DateTime $dateTime
     * @return string|null
     */
    public static function formatDate(\DateTime $dateTime): ?string {
        return $dateTime->format('Y-m-d') . 'T' . $dateTime->format('H:i:s');
    }

    /**
     * Исправлен косяк с массивами where()->orWhere()
     * @inheritDoc
     */
    protected function whereNested(Builder $query, $where) {
        $partWhere = $this->compileWheres($where['query']);
        return '(' . str_replace(['&$filter=', '$filter='], '', $partWhere) . ')';
    }
}