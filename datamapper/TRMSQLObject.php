<?php

namespace TRMEngine\DataMapper;

/**
 * расширяет класс TRMObjectMapper для применения к таблицам SQL,
 * может сам генерировать строку с именами полей объекта
 */
class TRMSQLObject extends TRMObjectMapper
{
  /**
   * формирует часть SQL-запроса со списком полей, которые выбираются из таблиц
   *
   * @param boolean $AddTableNameFlag - флаг, показывающий, что нужно к именам полей через точку добавлять имя таблицы
   * 
   * @return string - строка со списком полей
   */
  private function generateFieldsString($AddTableNameFlag = true)
  {
    if (empty($this->DataArray)) {
      return "";
    }
    $FieldStr = "";
    $TableName = empty($this->Alias) ? $this->Name : $this->Alias;
    foreach ($this->DataArray as $FieldName => $Field) {
      // если установлен флаг, показывающий, что нужно к именам полей через точку добавлять имя таблицы
      if ($AddTableNameFlag) {
        $FieldStr .= "`" . $TableName . "`.";
      }

      if ($Field->Quote == TRMFieldMapper::TRM_FIELD_NEED_QUOTE) {
        $FieldStr .= "`" . $FieldName . "`";
      } else {
        $FieldStr .= $FieldName;
      }

      if (!empty($Field->Alias)) {
        $FieldStr .= " AS " . $Field->Alias;
      }
      $FieldStr .= ",";
    }
    return rtrim($FieldStr, ",");
  }
}