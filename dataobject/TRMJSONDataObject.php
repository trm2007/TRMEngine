<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * класс для преобразования массива данных из объекта типа TRMDataObject в JSON-объект
 *
 * @author TRM 2018.07.22
 */
class TRMJSONDataObject
{
/**
 * преобразует массив данных объекта TRMDataObject в JSON-объект
 * 
 * @param TRMDataObject $do
 * @return string - JSON
 */
public function __invoke(TRMDataObjectInterface $do)
{
    return json_encode($do->getDataArray());
}


} // TRMJSONDataObject
