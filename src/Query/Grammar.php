<?php
declare(strict_types=1);

namespace OData\Query;


use SaintSystems\OData\Query\Builder;

class Grammar extends \SaintSystems\OData\Query\Grammar
{
    /** @inheritDoc */
    protected function whereBasic(Builder $query, $where) {
        //$value = $this->parameter($where['value']);
        $value = $where['value'];

        // stringify all values if it has NOT an odata enum syntax
        // (ex. Microsoft.OData.SampleService.Models.TripPin.PersonGender'Female')
        if (!is_string($value) || !preg_match("/^([\w]+\.)+([\w]+)(\'[\w]+\')$/", $value)) {
            if (is_bool($value)) {
                $value = $value === true ? 'true' : 'false';
            } else if (is_string($value)) {
                $value = "'" . $where['value'] . "'";
            }
        }

        return $where['column'] . ' ' . $this->getOperatorMapping($where['operator']) . ' ' . $value;
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