<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * класс контейнер объектов данных, используется для состаных объектов,
 * например, для составного продукта с зависимостями - группа, производитель, единица измерения и т.д.
 * есть главный объект и зависимые объекты
 */
abstract class TRMRelationDataObjectsContainer extends TRMDataObjectsContainer implements TRMIdDataObjectInterface
{
/**
 * @var array - массив зависимостей, каждый элемент массива - это поименованный элемент с подмассивом
 * (..., "ObjectName" => array( "TypeName" => type, "RelationFieldName" =>fieldname ), ... )
 */
protected $DependenciesArray = array();


/**
 * помещает объект данных с именем $Index в массив-контейнер зависимостей, сохраняется только ссылка, объект не клонируется!!!
 * 
 * @param string $Index - имя/номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMIdDataObjectInterface $do - добавляемый объект
 * @param string $FieldName - имя поля основного объекта, по которому связывается зависимость
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $ObjectName, $FieldName )
{
    $this->DependenciesArray[$Index] = array( strval($ObjectName), strval($FieldName) ); 
    
    $this->setDataObject($Index, $do);
}

/**
 * возвращает объект с именем $Index из массива-контейнера зависимостей
 * 
 * @param string $Index - имя/номер-индекс объекта в контейнере
 * 
 * @return array - имя поля в главном объекте, по которому связан вспомогающий под индексом $Index
 */
public function getDependence($Index)
{
    return isset($this->DependenciesArray[$Index]) ? $this->DependenciesArray[$Index] : null;
}

/**
 * @return array - массив с зависимостями вида - array(..., "ObjectName" => array( "TypeName" => type, "RelationFieldName" =>fieldname ), ... )
 */
public function getDependenciesArray()
{
    return $this->DependenciesArray;
}

/****************************************************************************
 * реализация интерфейса TRMIdDataObjectInterface
 ****************************************************************************/
public function getId()
{
    return $this->MainDataObject->getId();
}
public function setId($id)
{
    $this->MainDataObject->setId($id);
}
public function resetId()
{
    $this->MainDataObject->resetId();
}

public function getIdFieldName()
{
    return $this->MainDataObject->getIdFieldName();
}
public function setIdFieldName(array $IdFieldName)
{
    $this->MainDataObject->setIdFieldName($IdFieldName);
}


} // TRMRelationDataObjectsContainer