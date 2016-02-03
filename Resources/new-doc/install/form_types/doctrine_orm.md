# Doctrine ORM form types

[Go back to Table of contents][back-to-index]
[Go back to General configuration][back-to-general-config]

These are the default conversions made by the admingenerator for Doctrine ORM. Note that the values are different for the normal forms and the filter forms.

### Form conversion table

Database type | Form type
------------ | -------------
timestamp | Symfony\Component\Form\Extension\Core\Type\DateTimeType
vardatetime | Symfony\Component\Form\Extension\Core\Type\DateTimeType
datetimetz | Symfony\Component\Form\Extension\Core\Type\DateTimeType
date | Symfony\Component\Form\Extension\Core\Type\DateType
time | Symfony\Component\Form\Extension\Core\Type\TimeType
decimal | Symfony\Component\Form\Extension\Core\Type\NumberType
float | Symfony\Component\Form\Extension\Core\Type\NumberType
int | Symfony\Component\Form\Extension\Core\Type\IntegerType
integer | Symfony\Component\Form\Extension\Core\Type\IntegerType
int_id | Symfony\Component\Form\Extension\Core\Type\IntegerType
bigint | Symfony\Component\Form\Extension\Core\Type\IntegerType
smallint | Symfony\Component\Form\Extension\Core\Type\IntegerType
id | Symfony\Component\Form\Extension\Core\Type\TextType
custom_id | Symfony\Component\Form\Extension\Core\Type\TextType
string | Symfony\Component\Form\Extension\Core\Type\TextType
text | Symfony\Component\Form\Extension\Core\Type\TextareaType
document | Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType
collection | Symfony\Component\Form\Extension\Core\Type\CollectionType
hash | Symfony\Component\Form\Extension\Core\Type\CollectionType
boolean | Symfony\Component\Form\Extension\Core\Type\CheckboxType

### Filter form conversion table

Database type | Filter form type
------------ | -------------
timestamp | Symfony\Component\Form\Extension\Core\Type\DateTimeType
datetime | Symfony\Component\Form\Extension\Core\Type\DateTimeType
vardatetime | Symfony\Component\Form\Extension\Core\Type\DateTimeType
datetimetz | Symfony\Component\Form\Extension\Core\Type\DateTimeType
date | Symfony\Component\Form\Extension\Core\Type\DateType
time | Symfony\Component\Form\Extension\Core\Type\TimeType
decimal | Symfony\Component\Form\Extension\Core\Type\NumberType
float | Symfony\Component\Form\Extension\Core\Type\NumberType
integer | Symfony\Component\Form\Extension\Core\Type\NumberType
bigint | Symfony\Component\Form\Extension\Core\Type\NumberType
smallint | Symfony\Component\Form\Extension\Core\Type\NumberType
string | Symfony\Component\Form\Extension\Core\Type\TextType
text | Symfony\Component\Form\Extension\Core\Type\TextType
entity | Symfony\Bridge\Doctrine\Form\Type\EntityType
collection | Symfony\Component\Form\Extension\Core\Type\CollectionType
array | Symfony\Component\Form\Extension\Core\Type\TextType
boolean | Symfony\Component\Form\Extension\Core\Type\ChoiceType

[back-to-index]: ../../documentation.md
[back-to-general-config]: ../general-configuration.md