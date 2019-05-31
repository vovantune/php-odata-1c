# Библиотека для доступу из PHP к 1С по протоколу OData
За основу взята и доработана [saintsystems/odata-client-php](https://github.com/saintsystems/odata-client-php)

Базовая документация по ней: https://github.com/saintsystems/odata-client-php/wiki/Example-calls

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
            ->where('DeletionMark', '=', false)
            ->get();
foreach ($measures as $rec) {
    print ($rec->НаименованиеПолное) . "\n";
}
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

### Получение записи по ID
```php
$entity = $oDataClient->from('Catalog_КлассификаторЕдиницИзмерения')
            ->find('96289644-7af3-11e9-92fa-000c2979dd34');
var_export($entity->НаименованиеПолное);
```

### Отбор по регистрам
Например, получаем остатки по складу:
```php
$storeRemains = $oDataClient->from("AccountingRegister_Хозрасчетный/Balance(AccountCondition='Account/Code eq '43' or Account/Code eq '41.01'')")
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

### Комбинированный отбор
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

## Базовая информация по 1С
Стоит иметь ввиду, что реализация со стороны 1С отличается от стандарта.

### Настройка Apache для публикации 1С
https://infostart.ru/public/646384/

### Описание формата
* https://webhamster.ru/mytetrashare/index/mtb207/1472577217o3jfv2ydn4 
* https://master1c8.ru/platforma-1s-predpriyatie-8/rukovodstvo-razrabottchika/glava-17-mehanizm-internet-servisov/standartny-interfeys-odata-registr-nakopleniya/ 
* [https://its.1c.ru/db/v8310doc#bookmark:dev:TI000001358](https://its.1c.ru/db/v8310doc#bookmark:dev:TI000001358) 
* https://forum.infostart.ru/forum15/topic214553/ 