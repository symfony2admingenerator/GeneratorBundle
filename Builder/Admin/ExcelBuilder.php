<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for list actions
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Bob van de Vijver
 */
class ExcelBuilder extends ListBuilder
{
  /**
   * (non-PHPdoc)
   * @see Admingenerator\GeneratorBundle\Builder.BaseBuilder::getYamlKey()
   */
  public function getYamlKey()
  {
    return 'excel';
  }

  public function getFileName(){
    if(null === ($filename = $this->getVariable('filename'))){
      $filename = 'admin_export_'. str_replace(' ', '_', strtolower($this->getGenerator()->getFromYaml('builders.list.params.title')));
    }
    return $filename;
  }

  public function getFileType(){
    if(null === ($filetype = $this->getVariable('filetype'))){
      $filetype = 'Excel2007';
    }
    return $filetype;
  }

  public function getDateTimeFormat(){
    if(null === ($dateTimeFormat = $this->getVariable('datetime_format'))){
      $dateTimeFormat = 'Y-m-d H:i:s';
    }
    return $dateTimeFormat;
  }
}
