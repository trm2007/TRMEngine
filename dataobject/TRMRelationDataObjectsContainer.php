<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * класс контейнер объектов данных, используется для состаных объектов,
 * например, для составного продукта с зависимостями - группа, производитель, единица измерения и т.д.
 */
abstract class TRMRelationDataObjectsContainer extends TRMDataObjectsContainer implements TRMIdDataObjectInterface
{
/**
 * @var array - массив зависимостей, каждый элемент массива - это поименованный элемент с подмассивом
 * (..., "ObjectName" => array( "TypeName" => type, "RelationFieldName" =>fieldname ), ... )
 */
protected $DependenciesArray = array();


/**
 * помещает объект данных в массив под номером $Index, сохраняется только ссылка, объект не клонируется!!!
 * если по данному индексу была ранее установлена зависимость,
 * а устанавливаемый объект имеет тип отличный от указанного в зависимости, 
 * то имя связующего поля обнуляется...
 * 
 * @param string $Index - номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMDataObjectInterface $do - добавляемый объект
 */
/*
public function setDataObject($Index, TRMDataObjectInterface $do)
{
    $ClassName = get_class($do);
    if( isset($this->DependenciesArray[$Index]["TypeName"]) && $this->DependenciesArray[$Index]["TypeName"] !== $ClassName)
    {
        $this->DependenciesArray[$Index]["TypeName"] = $ClassName;
        $this->DependenciesArray[$Index]["RelationFieldName"] = null;
    }
    parent::setDataObject($Index, $do);
}
 * 
 */

/**
 * помещает объект данных с именем $Index в массив-контейнер зависимостей, сохраняется только ссылка, объект не клонируется!!!
 * 
 * @param string $Index - имя/номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMIdDataObjectInterface $do - добавляемый объект
 * @param string $FieldName - имя поля основного объекта, по которому связывается зависимость
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $FieldName )
{
    $this->DependenciesArray[$Index] = strval($FieldName); 
    /*array(
        "TypeName"=> get_class($do),
        "RelationFieldName" => strval($FieldName),
    );
    parent::setDataObject($Index, $do);
     * 
     */
    
    
    $this->setDataObject($Index, $do);
}

/**
 * возвращает объект с именем $Index из массива-контейнера зависимостей
 * 
 * @param string $Index - имя/номер-индекс объекта в контейнере
 * 
 * @return TRIdMDataObject - имя поля в главном объекте, по которому связан вспомогающий под индексом $Index
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

/**
 * создает в контейнере пустые объекты 
 * с типами перечисленными в массиве зависимостей $DependenciesArray,
 * при этом каждый из этих объект должен иметь конструктор без параметров!!!
 * Пустые объекты могут пригодится, например, 
 * для приема данных и инициализации методом setOwnData из внешнего источника
 */
/*
public function initEmptyContainer()
{
    foreach ($this->DependenciesArray as $ObjectName => $ObjectConfig )
    {
        if( !isset($this->ObjectsArray[$ObjectName]) )
        {
            $this->setDataObject($ObjectName, new $ObjectConfig["TypeName"]);
        }
    }
}
 * 
 */


/****************************************************************************
 * реализация интерфейса TRMIdDataObjectInterface
 ****************************************************************************/
public function getId()
{
    return $this->MainDataObject->getId();
}

public function getIdFieldName()
{
    return $this->MainDataObject->getIdFieldName();
}

public function resetId()
{
    $this->MainDataObject->resetId();
}

public function setId($id)
{
    $this->MainDataObject->setId($id);
}

public function setIdFieldName($IdFieldName)
{
    $this->MainDataObject->setIdFieldName($IdFieldName);
}


} // TRMRelationDataObjectsContainer