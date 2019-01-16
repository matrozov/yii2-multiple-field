# Валидаторы

* [ArrayValidator](validators.md#user-content-arrayvalidator)
* [ModelValidator](validators.md#user-content-modelvalidator)
* [KeyValidator](validators.md#user-content-keyvalidator)
* [KeyValueValidator](validators.md#user-content-keyvaluevalidator)
* [KeyArrayValidator](validators.md#user-content-keyarrayvalidator)
* [KeyModelValidator](validators.md#user-content-keymodelvalidator)

## ArrayValidator

Валидатор полей вложенного ассоциативного массива в указанное поле.

```php
'my-field' => [ // Ассоциативный массив
    'field1' => 'value1',
    'field1' => 'value2',
    ...
]
```

Конфигурация валидатора выглядит следующим образом:

```php
[['my-field'], ArrayValidator::class, 'rules' => [
    [['field1', 'field2'], 'required'],
    [['field1'], 'string', 'max' => 255],
    [['field2'], 'integer'],
    ...
]],
```

Где для каждого поля мы можем указать один или более валидаторов.

## ModelValidator

Валидатор полей вложенного ассоциативного массива в указанное поле через указанную модель.
Является, по сути, интерпретацией ArrayValidator, где правила валидации описаны отдельной моделью.

```php
'my-field' => [ // Ассоциативный массив или экземпляры модели
    'field1' => 'value1',
    'field2' => 'value2',
    ...
]
```

Конфигурация валидатора выглядит следующим образом:

```php
[['my-field'], ModelValidator::class, 'model' => MyFieldModel::class],
```

При этом в указанной модели описаны rules вида:

```php
[['field1', 'field2'], 'required'],
[['field1'], 'string', 'max' => 255],
[['field2'], 'integer'],
...
```

Стоит отметить, что после успешной валидации валидатор преобразует значение исходного поля в экземпляр указанной модели.

## KeyValidator

Валидатор ключей переданного ассоциативного массива без валидации его значений. 

```php
'my-field' => [ // Ассоциативный массив
    'key1' => ...,
    'key2' => ...,
    ...
];
```

Конфигурация валидатора выглядит следующим образом:

```php
[['my-field'], KeyValidator::class, 'keyRules' => [
    ['integer', 'max' => 100],
    ...
], 'keyIsIndexed' => true],
```

Правила валидации записываются аналогично виду стандартного
[each](https://www.yiiframework.com/doc/api/2.0/yii-validators-eachvalidator)-валидатора с указанием только правил валидации.
Параметр **keyIsIndexed** (по умолчанию = false) проверяет, являются ли переданные ключи последовательными числами начинающимися с 0.

## KeyValueValidator

Валидатор однотипный полей ассоциативного массива. По сути, это массовый стандартный
[each](https://www.yiiframework.com/doc/api/2.0/yii-validators-eachvalidator)-валидатор, позволяющий указать сразу
несколько правил для валидации. Валидатор является наследником KeyValidator и позволяет, в том числе, валидировать и ключи.

```php
'my-field' => [ // Ассоциативный массив значений
    'key1' => 'value1',
    'key2' => 'value2',
    ...    
];
```

Конфигурация валидатора выглядит следующим образом:

```php
[['my-field'], KeyValueValidator::class, 'keyRules' => [
    ['integer', 'max' => 100],
    ...
], 'rules' => [
    ['string', 'max' => 255],
    ...
]]
```

## KeyArrayValidator

Валидатор массива однотипных ассоциативных массивов. Является, по сути, объединением KeyValidator, для валидации ключей
и ArrayValidator, для валидации массива полей. 

```php
[ // Ассоциативный массив массивов
    'key1' => [ // Ассоциативный массив значений
        'field1' => 'value1',
        'field2' => 'value2',
        ...
    ],
    'key2' => [ // Ассоциативный массив значений
        'field1' => 'value1',
        'field2' => 'value2',
        ...
    ],
    ...    
];
```

Конфигурация валидатора выглядит следующим образом:

```php
[['my-field'], KeyArrayValidator::class, 'keyRules' => [
    ['integer', 'max' => 100],
    ...
], 'rules' => [
    [['field1', 'field2'], 'required'],
    [['field1'], 'string', 'max' => 255],
    [['field2'], 'integer'],
    ...
]]
```

## KeyModelValidator

Валидатор массива однотипных ассоциативных массивов через указанную модель. Как и KeyArrayValidator, KeyModelValidator
является, по сути, объединением KeyValidator, для валидации ключей и ModelValidator, для валидации массива полей через модель.

```php
'my-field' => [ // Ассоциативный массив массивов или экземпляров моделей
    'key1' => [ // Ассоциативный массив или экземпляры модели
        'field1' => 'value1',
        'field2' => 'value2',
        ...
    ],
    'key2' => [ // Ассоциативный массив или экземпляры модели
        'field1' => 'value1',
        'field2' => 'value2',
        ...
    ],
    ...    
];
```

Конфигурация валидатора выглядит следующим образом:

```php
[['my-field'], KeyArrayValidator::class, 'keyRules' => [
    ['integer', 'max' => 100],
    ...
], 'model' => MyFieldModel::class]]
```

При этом в указанной модели описаны rules вида:

```php
[['field1', 'field2'], 'required'],
[['field1'], 'string', 'max' => 255],
[['field2'], 'integer'],
...
```

Стоит отметить, что после успешной валидации валидатор преобразует значение исходного поля в экземпляр указанной модели.
