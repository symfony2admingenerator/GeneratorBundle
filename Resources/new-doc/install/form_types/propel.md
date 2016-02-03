# Propel form types

[Go back to Table of contents][back-to-index]
[Go back to General configuration][back-to-general-config]

These are the default conversions made by the admingenerator for Propel. Note that the values are different for the normal forms and the filter forms.

### Form conversion table

Database type | Form type
------------ | -------------
TIMESTAMP | Symfony\Component\Form\Extension\Core\Type\DateTimeType
BU_TIMESTAMP | Symfony\Component\Form\Extension\Core\Type\DateTimeType
DATE | Symfony\Component\Form\Extension\Core\Type\DateType
BU_DATE | Symfony\Component\Form\Extension\Core\Type\DateType
TIME | Symfony\Component\Form\Extension\Core\Type\TimeType
FLOAT | Symfony\Component\Form\Extension\Core\Type\NumberType
REAL | Symfony\Component\Form\Extension\Core\Type\NumberType
DOUBLE | Symfony\Component\Form\Extension\Core\Type\NumberType
DECIMAL | Symfony\Component\Form\Extension\Core\Type\NumberType
TINYINT | Symfony\Component\Form\Extension\Core\Type\IntegerType
SMALLINT | Symfony\Component\Form\Extension\Core\Type\IntegerType
INTEGER | Symfony\Component\Form\Extension\Core\Type\IntegerType
BIGINT | Symfony\Component\Form\Extension\Core\Type\IntegerType
NUMERIC | Symfony\Component\Form\Extension\Core\Type\IntegerType
CHAR | Symfony\Component\Form\Extension\Core\Type\TextType
VARCHAR | Symfony\Component\Form\Extension\Core\Type\TextType
LONGVARCHAR | Symfony\Component\Form\Extension\Core\Type\TextareaType
BLOB | Symfony\Component\Form\Extension\Core\Type\TextareaType
CLOB | Symfony\Component\Form\Extension\Core\Type\TextareaType
CLOB_EMU | Symfony\Component\Form\Extension\Core\Type\TextareaType
model | Symfony\Bridge\Propel1\Form\Type\ModelType
collection | Symfony\Component\Form\Extension\Core\Type\CollectionType
PHP_ARRAY | Symfony\Component\Form\Extension\Core\Type\CollectionType
ENUM | Symfony\Component\Form\Extension\Core\Type\ChoiceType
BOOLEAN | Symfony\Component\Form\Extension\Core\Type\CheckboxType
BOOLEAN_EMU | Symfony\Component\Form\Extension\Core\Type\CheckboxType

### Filter form conversion table

Database type | Filter form type
------------ | -------------
TIMESTAMP | Symfony\Component\Form\Extension\Core\Type\DateTimeType
BU_TIMESTAMP | Symfony\Component\Form\Extension\Core\Type\DateTimeType
DATE | Symfony\Component\Form\Extension\Core\Type\DateType
BU_DATE | Symfony\Component\Form\Extension\Core\Type\DateType
TIME | Symfony\Component\Form\Extension\Core\Type\TimeType
FLOAT | Symfony\Component\Form\Extension\Core\Type\NumberType
REAL | Symfony\Component\Form\Extension\Core\Type\NumberType
DOUBLE | Symfony\Component\Form\Extension\Core\Type\NumberType
DECIMAL | Symfony\Component\Form\Extension\Core\Type\NumberType
TINYINT | Symfony\Component\Form\Extension\Core\Type\NumberType
SMALLINT | Symfony\Component\Form\Extension\Core\Type\NumberType
INTEGER | Symfony\Component\Form\Extension\Core\Type\NumberType
BIGINT | Symfony\Component\Form\Extension\Core\Type\NumberType
NUMERIC | Symfony\Component\Form\Extension\Core\Type\NumberType
CHAR | Symfony\Component\Form\Extension\Core\Type\TextType
VARCHAR | Symfony\Component\Form\Extension\Core\Type\TextType
LONGVARCHAR | Symfony\Component\Form\Extension\Core\Type\TextType
BLOB | Symfony\Component\Form\Extension\Core\Type\TextType
CLOB | Symfony\Component\Form\Extension\Core\Type\TextType
CLOB_EMU | Symfony\Component\Form\Extension\Core\Type\TextType
model | Symfony\Bridge\Propel1\Form\Type\ModelType
collection | Symfony\Component\Form\Extension\Core\Type\CollectionType
PHP_ARRAY | Symfony\Component\Form\Extension\Core\Type\TextType
ENUM | Symfony\Component\Form\Extension\Core\Type\TextType
BOOLEAN | Symfony\Component\Form\Extension\Core\Type\ChoiceType
BOOLEAN_EMU | Symfony\Component\Form\Extension\Core\Type\ChoiceType


[back-to-index]: ../../documentation.md
[back-to-general-config]: ../general-configuration.md