# Библиотека для доступу из PHP к 1С по протоколу OData
За основу взята и доработана [saintsystems/odata-client-php](https://github.com/saintsystems/odata-client-php)

Базовая документация по ней: https://github.com/saintsystems/odata-client-php/wiki/Example-calls

## Настройка 1С

По-умолчанию в 1С отключена публикация всех данных дажи при активированном OData протоколе в публикации.
Для решения данной проблемы ренобходимо от администратора запустить [РедактированиеСоставаСтандартногоИнтерфейсаOData.epf](РедактированиеСоставаСтандартногоИнтерфейсаOData.epf)

## Применение
### Инициализация
```php
$odataServiceUrl = 'путь к опубликованной базе данных';
$username = 'пользователь 1С';
$password = 'пароль 1С';

$oDataClient = new OData1CClient($odataServiceUrl, $username, $password);
```

### Получение массива данных
```php
$measures = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->where('DeletionMark', '=', false) // условие отбора может быть также \DateTime, строкой guid. Главное, что должно соответствовать типу данных в отбираемом поле
            ->get();
foreach ($measures as $rec) {
    print ($rec->НаименованиеПолное) . "\n";
}

// получаем первую запись из списка
$measures = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->where('DeletionMark', '=', false)
            ->first();
```

Получение количества записей для данного запроса:
```php
$total = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->where('DeletionMark', '=', false)
            ->count();
```

Массив со связями на другой объект:
```php
$barcodes = $oDataClient->from('InformationRegister_ШтрихкодыНоменклатуры')
            ->select([
                'Штрихкод',
                'Номенклатура_Key',
                'Номенклатура/Артикул',
                'Номенклатура/Description'
            ])->expand('Номенклатура')
            ->get();

foreach ($barcodes as $bc) {
    print $bc['Штрихкод'] . ' ' . $bc['Номенклатура']['Description'] . "\n";
}
```

#### Пажинация
```php
$result = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->where('DeletionMark', '=', false)
            ->skip(3)
            ->take(2)
            ->get();
```

### Сортировка
Следует иметь ввиду, что последующий вызов order перезаписывает предыдущий, в отличие от where
```php
// сортировка по возрастанию
$result = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->order('Description')
            ->get();

// сортировка по убыванию
$result = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->order('Description', 'desc')
            ->get();

// сортировка по нескольким полям
$result = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->order([['Description', 'desc'], ['Code', 'asc']])
            ->get();
```

### Получение записи по ID
```php
$entity = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->find('96289644-7af3-11e9-92fa-000c2979dd34');
var_export($entity->НаименованиеПолное);
```

### Отбор по регистрам (виртуальным таблицам)
Доступные методы:
* balance(null|array $conditions)
* turnovers(null|array $conditions)
* balanceAndTurnovers(null|array $conditions)

Например, получаем остатки и обороты по складу:
```php
$storeRemains = $oDataClient->from('AccountingRegister_Хозрасчетный')
    ->balanceAndTurnovers([
            'AccountCondition' => "Account/Code eq '43' or Account/Code eq '41.01'", // пока что красивая группировка не реализована, 
                                                                                     // соответственно операторы должны соответствовать стандартку ODate, 
                                                                                     // кавычки всегда одинарные!
            'EndPeriod' => new \DateTime('2019-05-01'), // Автоматически приводит к дате
            'StartPeriod' => new \DateTime('2019-04-01'),
        ])
    ->select([
        'Account/Code',
        'Account/Description',
        'Валюта/Code',
        'Валюта/Description',
        'Организация/Description',
        'Организация/ИНН',
        'ExtDimension1',
        'ExtDimension3',
        'СуммаBalance',
        'КоличествоBalance'
    ])->expand(['Account', 'Валюта', 'Организация', 'ExtDimension1', 'ExtDimension3'])
    ->take(5)
    ->get();
var_export($storeRemains);
```

### Отбор по нескольким условиям
```php
$measures = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
    ->where([
        ['Code', '=', '736'],
        ['Description', '=', 'рул']
    ])
    ->orWhere([
        ['Code', '=', '778'],
        ['Description', '=', 'упак']
    ])
    ->get();
foreach ($measures as $rec) {
    print ($rec->НаименованиеПолное) . "\n";
}
```

### Пометка на удаление
Объект удаляется не полностью, а помечается на удаление:
```php
$oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->delete('ac886490-9a42-11e8-9429-a8a795c008e0');
```
Массовое удаление в 1С недоступно.

### Создание объекта
```php
$result = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->create([
                'Code' => 777,
                'Description' => '777 ед',
                'НаименованиеПолное' => 'Единица 777',
            ]); 
print_r($result); // содержит полную информацию об объекте
```

### Изменение объекта
```php
$result = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->update('ac886490-9a42-11e8-9429-a8a795c008e0', [
                'Code' => 778,
            ]); 
print_r($result); // содержит полную информацию об объекте
```
Массовое изменение в 1С недоступно.

### Проведение и отменая проведения
```php
// проведение
$oDataClient->from('Document_ОприходованиеТоваров')
            ->postDocument('f8c00f9e-7887-11e9-80c8-00155d58132c');
            
// отмена проведения
$oDataClient->from('Document_ОприходованиеТоваров')
            ->unPostDocument('f8c00f9e-7887-11e9-80c8-00155d58132c');
```

## Базовая информация по 1С
Стоит иметь ввиду, что реализация со стороны 1С отличается от стандарта.

### Настройка Apache для публикации 1С
https://infostart.ru/public/646384/

### Описание формата
* https://webhamster.ru/mytetrashare/index/mtb207/1472577217o3jfv2ydn4 
* https://master1c8.ru/platforma-1s-predpriyatie-8/rukovodstvo-razrabottchika/glava-17-mehanizm-internet-servisov/standartny-interfeys-odata-registr-nakopleniya/ 
* [https://its.1c.ru/db/v8310doc#bookmark:dev:TI000001358](https://its.1c.ru/db/v8310doc#bookmark:dev:TI000001358) 
* https://forum.infostart.ru/forum15/topic214553/ 