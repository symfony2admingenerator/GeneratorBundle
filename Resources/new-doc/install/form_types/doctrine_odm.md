# Doctrine ODM form types

[Go back to Table of contents][back-to-index]
[Go back to General configuration][back-to-general-config]

These are the default conversions made by the admingenerator for Doctrine ODM. Note that the values are different for the normal forms and the filter forms.

### Form conversion table

Database type | Form type
------------ | -------------
datetime | datetime
vardatetime | datetime
datetimetz | datetime
date | date
time | time
decimal | number
float | number
integer | integer
bigint | integer
smallint | integer
string | text
text | textarea
entity | entity
collection | collection
array | collection
boolean | checkbox

### Filter form conversion table

Database type | Filter form type
------------ | -------------
datetime | datetime
timestamp | datetime
vardatetime | datetime
datetimetz | datetime
date | date
time | time
decimal | number
float | number
int | number
integer | number
int_id | number
bigint | number
smallint | number
id | text
custom_id | text
string | text
text | text
document | model
collection | collection
hash | text
boolean | choice

[back-to-index]: ../../documentation.md
[back-to-general-config]: ../general-configuration.md