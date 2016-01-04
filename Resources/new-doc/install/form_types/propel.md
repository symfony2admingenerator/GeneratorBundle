# Propel form types

[Go back to Table of contents][back-to-index]
[Go back to General configuration][back-to-general-config]

These are the default conversions made by the admingenerator for Propel. Note that the values are different for the normal forms and the filter forms.

### Form conversion table

Database type | Form type
------------ | -------------
TIMESTAMP | datetime
BU_TIMESTAMP | datetime
DATE | date
BU_DATE | date
TIME | time
FLOAT | number
REAL | number
DOUBLE | number
DECIMAL | number
TINYINT | integer
SMALLINT | integer
INTEGER | integer
BIGINT | integer
NUMERIC | integer
CHAR | text
VARCHAR | text
LONGVARCHAR | textarea
BLOB | textarea
CLOB | textarea
CLOB_EMU | textarea
model | model
collection | collection
PHP_ARRAY | collection
ENUM | choice
BOOLEAN | checkbox
BOOLEAN_EMU | checkbox

### Filter form conversion table

Database type | Filter form type
------------ | -------------
TIMESTAMP | datetime
BU_TIMESTAMP | datetime
DATE | date
BU_DATE | date
TIME | time
FLOAT | number
REAL | number
DOUBLE | number
DECIMAL | number
TINYINT | number
SMALLINT | number
INTEGER | number
BIGINT | number
NUMERIC | number
CHAR | text
VARCHAR | text
LONGVARCHAR | text
BLOB | text
CLOB | text
CLOB_EMU | text
model | model
collection | collection
PHP_ARRAY | text
ENUM | text
BOOLEAN | choice
BOOLEAN_EMU | choice


[back-to-index]: ../../documentation.md
[back-to-general-config]: ../general-configuration.md