# Excel builder configuration

[go back to Table of contents][back-to-index]

-----

The Excel builder extends the [List builder](list-builder), which means that when the Excel export is executed, the 
available data is exactly the same as shown in the List view at that moment (but not paginated). Filters and scopes are 
applied as expected.

> **Note**: As the Excel builder extends the List builder, it is required to have the list builder in your admin 
configuration.

### Requirements

To use the Excel export make sure you have installed the recommended dependency `liuggio/excelbundle` and enabled it in 
your `AppKernel.php` (`new Liuggio\ExcelBundle\LiuggioExcelBundle()`). Without this bundle enabled, the ExcelAction 
will not work.

### Parameters

The Excel export has the following parameters available:

```yaml
builders:
  excel:
    params:
	  display: ~
	  filename: ~
	  filetype: ~
	  datetime_format: ~
	  fields: ~
	  export: ~
```

#### Export

This key allows to export several excel files in different formats.

```yaml
builders:
    excel:
        params: 
            export:  
                full:
                    credentials:     'hasRole("ROLE_A")'        
                    show_button:     false
                    icon:            fa-files-o 
                    label:           Full report
                    filename:        full-report.xlsx
                    filetype:        Excel2007
                    datetime_format: Y-m-d H:i:s
                    display:
                        -            id
                        -            title
                        -            code
                        -            guid
                        -            note

                short:
                    credentials:     'hasRole("ROLE_B")'        
                    show_button:     true
                    icon:            fa-files-o 
                    label:           Show report
                    filename:        Short-repot.xls
                    filetype:        Excel5
                    datetime_format: d.m.Y
                    fields:          
                        title:
                            label:   Product name
                    display:
                        -            id
                        -            code
                        -            title

```

You can customize everything includes columns, format, filename, title and even credentials.
Also you can setup autogeneration of export buttons on list template via parameter `show_button`.
It also auto-generates routes for each export key (if key is not found use defaults – `display`):

*  /excel
*  /excel/full
*  /excel/short

#### Show button

`show_button` __default__: `true` __type__: `boolean`

```yaml
show_button: true
```

This will display button for each key (report) registered under `export` parameters key.

#### Display

`display` __default__: `~` __type__: `array`

Works as in the [List builder](list-builder), you can specify an array of fields that need to be exported in the Excel 
file. When you need to export a related object, you can specify a getter by using the 'dot' notation. For example:

```yaml
display: [registration_date, person.fullname]
```

This will export the registration dates (from our admingenerator managed object) and the full name of the person related
to the object.

#### File name

`filename` __default__: `admin_export_{list title}` __type__: `string`

Specify the export filename. When null 'admin_export_{list title}' is used.

#### File type

`filetype` __default__: `Excel2007` __type__: `string`

Default Excel2007. See the [excelbundle documention](https://github.com/liuggio/excelbundle#not-only-excel5) for the 
possible options.

#### Datetime format

`datetime_format` __default__: `Y-m-d H:i:s` __type__: `string`

Specify the DateTime format to be used in the Excel export. Default is `Y-m-d H:i:s`.

#### Fields

`fields` __default__: `~` __type__: `array`

The Excel builders uses the field label as headers in the export. Because of this, the headers can be overwritten just 
like the label can be overwritten in any other builder. When you use object associations for the export, you will need 
to specify the label as it is not autoguessed. For example (the field fullname from the related person object):

```yaml
fields:
  person.fullname:
    label: Full name
```

[back-to-index]: ../documentation.md
[list-builder]: builder-list.md