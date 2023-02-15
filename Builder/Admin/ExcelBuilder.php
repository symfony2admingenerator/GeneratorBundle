<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

use Admingenerator\GeneratorBundle\Generator\Column;

/**
 * This builder generates php for list actions
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Bob van de Vijver
 */
class ExcelBuilder extends ListBuilder
{
  protected ?array $export = null;

  public function getYamlKey(): string
  {
    return 'excel';
  }

  public function getFileName($key = null): string
  {
    if (null === ($filename = $this->getVariable('filename'))) {
      $filename = 'admin_export_'. str_replace(' ', '_', strtolower($this->getGenerator()->getFromYaml('builders.list.params.title'))). '.xlsx';
    }

    return $this->getExportParamsForKey($key, 'filename', $filename);
  }

  public function getFileType($key = null): string
  {
    if (null === ($filetype = $this->getVariable('filetype'))) {
      if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet'))
      {
        $filetype = 'Xlsx';
      } else {
        $filetype = 'Excel2007';
      }
    }

    return $this->getExportParamsForKey($key, 'filetype', $filetype);
  }

  public function getDateTimeFormat($key = null): string
  {
    if (null === ($dateTimeFormat = $this->getVariable('datetime_format'))) {
      $dateTimeFormat = 'Y-m-d H:i:s';
    }

    return $this->getExportParamsForKey($key, 'datetime_format', $dateTimeFormat);
  }

  /**
   * Return a list of columns from excel export
   */
  public function getExport(): array
  {
      if (null === $this->export) {
          $this->export = [];
          $this->fillExport();
      }

      return $this->export;
  }

  protected function fillExport(): void
  {
      $export = $this->getVariable('export', []);
      if (!count($export)) return;

      foreach ($export as $keyName => $columns ) {
          $params = [];
          $this->export[$keyName] = []; 
          if (isset($columns['display'])) {
              $params = $columns['fields'] ?? [];
              $columns = $columns['display'];
          } 
          foreach ($columns as $columnName) {
              $column = $this->createColumn($columnName);
              $this->setUserColumnConfiguration($column);              
              $this->setUserExcelColumnConfiguration($column, $params);              
              $this->export[$keyName][$columnName] = $column;
          }
      }
  }

  protected function setUserExcelColumnConfiguration(Column $column, array $optionsFields): void
  {
      if (!count($optionsFields)) return;

      $options = array_key_exists($column->getName(), $optionsFields) ?
          $optionsFields[$column->getName()] : array();

      foreach ($options as $option => $value) {
          $column->setProperty($option, $value);
      }
  }

  public function getExportCredentials($key = null): string
  {
    return $this->getExportParamsForKey($key, 'credentials', null);
  }

}