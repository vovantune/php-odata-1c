<?php
declare(strict_types=1);

namespace OData\Query;

use Illuminate\Support\Collection;
use OData\ODataException;
use SaintSystems\OData\Entity;

class Builder extends \SaintSystems\OData\Query\Builder
{
    /**
     * Запрос к виртуальным таблицам, проведение записей
     * @var null|string
     */
    public $entitySetPostfix = null;

    /**
     * Создаём объект
     *
     * @param array $fieldValues
     * @return Entity|null
     * @throws ODataException
     */
    public function create(array $fieldValues) {
        $svFields = $this->_prepareFieldValues($fieldValues);
        $result = $this->post($svFields);
        if (!empty($result)) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * Обновляем объект
     *
     * @param string $guid
     * @param array $fieldValues
     * @return Entity|null
     * @throws ODataException
     */
    public function update(string $guid, array $fieldValues) {
        if (!is_uuid($guid)) {
            throw new ODataException('Аргумент $guid не guid формата');
        }
        $svFields = $this->_prepareFieldValues($fieldValues);
        $result = $this->whereKey($guid)
            ->patch($svFields);
        if (!empty($result)) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * Удаляем путём пометки DeletionMark для объекта@param string $guid
     * @return Collection
     *
     * @return true
     * @throws ODataException
     */
    public function delete($guid = null) {
        if (empty($guid)) {
            throw new ODataException('Аргумент $guid обязателен');
        }
        $this->update($guid, [
            'DeletionMark' => true,
        ]);
        return true;
    }

    /**
     * Подгатавливаем массив параметров к передаче в 1С
     *
     * @param array $fieldValues
     * @return array
     * @throws ODataException
     */
    private function _prepareFieldValues(array $fieldValues): array {
        if (empty($fieldValues)) {
            throw new ODataException('Аргумент $fieldValues обязателен к заполнению');
        }

        $result = [];
        foreach ($fieldValues as $fieldName => $fieldValue) {
            if (!is_string($fieldName)) {
                throw new ODataException('Имя параметра ' . $fieldName . ' не является строкой, что не допустимо');
            }

            if (is_array($fieldValue)) {
                throw new ODataException('Значение параметра ' . $fieldName . ' является массивом, что не допустимо');
            }
            $svValue = $fieldValue;
            if (is_object($fieldValue)) {
                if ($fieldName instanceof \DateTime) {
                    $svValue = Grammar::formatDate($fieldValue);
                } else {
                    throw new ODataException('Значение параметра ' . $fieldName . ' является объектом ' . get_class($fieldValue) . ', что не допустимо');
                }
            }
            $result[$fieldName] = $svValue;
        }
        return $result;
    }

    /**
     * Провести документ в 1С
     *
     * @param string $guid
     */
    public function postDocument(string $guid) {
        $this->entitySetPostfix = "(guid'" . $guid . "')/Post()";
        $this->get();
    }

    /**
     * Отменить проведение в 1С
     *
     * @param string $guid
     */
    public function unPostDocument(string $guid) {
        $this->entitySetPostfix = "(guid'" . $guid . "')/Unpost()";
        $this->get();
    }

    /**
     * Обращение к виртуальной таблице остатков
     *
     * @param array|null $conditions ['Параметр' => 'Условие', ...], также при отборе по дате можно передавать класс DateTime
     * @return static
     */
    public function balance(?array $conditions = null): self {
        $this->entitySetPostfix = "/Balance" . ($conditions ? '(' . $this->_buildRegisterConditions($conditions) . ')' : '');
        return $this;
    }

    /**
     * Обращение к виртуальной таблице оборотов
     *
     * @param array|null $conditions ['Параметр' => 'Условие', ...], также при отборе по дате можно передавать класс DateTime
     * @return static
     */
    public function turnovers(?array $conditions = null): self {
        $this->entitySetPostfix = "/Turnovers" . ($conditions ? '(' . $this->_buildRegisterConditions($conditions) . ')' : '');
        return $this;
    }

    /**
     * Обращение к виртуальной таблице остатков и оборотов
     *
     * @param array|null $conditions ['Параметр' => 'Условие', ...], также при отборе по дате можно передавать класс DateTime
     * @return static
     */
    public function balanceAndTurnovers(?array $conditions = null): self {
        $this->entitySetPostfix = "/BalanceAndTurnovers" . ($conditions ? '(' . $this->_buildRegisterConditions($conditions) . ')' : '');
        return $this;
    }

    /**
     * @inheritDoc
     * Сортировка с приведением направления к нижнему регистру
     */
    public function order($properties = []) {
        $order = is_array($properties) && count(func_get_args()) === 1 ? $properties : func_get_args();

        if (!(isset($order[0]) && is_array($order[0]))) {
            $order = [$order];
        }

        $this->orders = $this->buildOrders($order);

        return $this;
    }

    /** @inheritDoc */
    private function buildOrders($orders = []) {
        $_orders = [];

        foreach ($orders as &$order) {
            $column = isset($order['column']) ? $order['column'] : $order[0];
            $direction = isset($order['direction']) ? $order['direction'] : (isset($order[1]) ? strtolower($order[1]) : 'asc');

            array_push($_orders, [
                'column'    => $column,
                'direction' => $direction,
            ]);
        }

        return $_orders;
    }

    /**
     * Формируем условия отбора для регистров накопления
     * @param array $conditions
     * @return string
     */
    private function _buildRegisterConditions(array $conditions) {
        $result = [];
        foreach ($conditions as $fieldName => $fieldFilter) {
            $result[] = $fieldName . '=' . Grammar::prepareCondition($fieldFilter);
        }
        return implode(',', $result);
    }
}