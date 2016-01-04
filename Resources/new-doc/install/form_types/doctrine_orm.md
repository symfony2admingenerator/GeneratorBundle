# Doctrine ORM form types

[Go back to Table of contents][back-to-index]
[Go back to General configuration][back-to-general-config]

These are the default conversions made by the admingenerator for Doctrine ORM. Note that the values are different for the normal forms and the filter forms.

### Form conversion table

Database type | Form type
------------ | -------------
timestamp | datetime
vardatetime | datetime
datetimetz | datetime
date | date
time | time
decimal | number
float | number
int | integer
integer | integer
int_id | integer
bigint | integer
smallint | integer
id | text
custom_id | text
string | text
text | textarea
document | document
collection | collection
hash | collection
boolean | checkbox

### Filter form conversion table

Database type | Filter form type
------------ | -------------
datetime | datetime
vardatetime | datetime
datetimetz | datetime
date | date
time | time
decimal | number
float | number
integer | number
bigint | number
smallint | number
string | text
text | text
entity | entity
collection | collection
array | text
boolean | choice

[back-to-index]: ../../documentation.md
[back-to-general-config]: ../general-configuration.md