<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Exceptions\TRMDataObjectContainerNoMainException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMRelationDataObjectsContainerInterface;

/**
 * ����� ��������� �������� ������, ������������ ��� ��������� ��������.
 * 
 * ������������ 
 * 1. ��� ��� ��������� ��������-�����,
 * ��������, ��� ���������� �������� �� ����� ��������������� ����������� � ���������,
 * ������� ������� �� ID-�������� �������
 * (��������� �������������, ������������� ��������, ���.����������� ��� ������ � �.�.)
 * 
 * 2. ��� � ��� ��������� ������������ (��� ������� � ��������� ������� ���� ������ ���� �����������),
 * ����� ���� ������� ������ � �������-�����������,
 * �� ������� ������� ������ ������� � ������ � ���� ����� �� ID.
 * ���� ����������� �������� ����������� ����������, ��������,
 * ������������� ����� �� ������� �� ������, 
 * �� ����� ������ ����� ���� ID_vendor � ������� �� ������������� �� ��� ID...
 */
class TRMDataObjectsContainer implements TRMDataObjectsContainerInterface
{
/**
 * @var TRMIdDataObjectInterface - �������� ������ � ���������� ��������������� ID,
 * �� ����������� ID ������� � ���������� ����������� � ������� ��������
 */
protected $MainDataObject;
/**
 * @var array(TRMDataObjectsCollection) - ������ � ����������� �������� ������, ����������� �������� ������, 
 * �������� ��������� �������������, ���.�����������, ���������, ������ � �.�.
 */
protected $ChildCollectionsArray = array();
/**
 * @var array(TRMIdDataObjectInterface) - ������ �������� ������, ����������� �������� ������, 
 * �������� ��������� �������������, ���.�����������, ���������, ������ � �.�.
 */
protected $DependenciesObjectsArray = array();
/**
 * @var array - ������ ������������, 
 * ������ ������� ������� - ��� ������������� ������� � �����������,
 * ���������� ��� ���-������� � ������� ������� � ��� ���� ����� ���-�������
 * ��� ����� � ID-�����������
 * (..., "ObjectIndex" => array( "RelationSubObjectName" => type, "RelationFieldName" =>fieldname ), ... )
 */
protected $DependenciesFieldsArray = array();

/**
 * @var integer - ������� ������� ���������, ��� ���������� ���������� ��������� - Iterator
 */
private $Position = 0;


/**
 * @return TRMIdDataObjectInterface - ���������� ������� (����������� ��� 0-� ������� � �������) ������ ������
 */
public function getMainDataObject()
{
    return $this->MainDataObject;
}

/**
 * ������������� ������� ������ ������,
 * 
 * @param TRMIdDataObjectInterface $do - ������� ������ ������
 */
public function setMainDataObject(TRMIdDataObjectInterface $do)
{
    $this->MainDataObject = $do;
}


/**
 * �������� ������ ������ � ������ $Index � ������-��������� ������������, 
 * ����������� ������ ������, ������ �� �����������!!!
 * 
 * @param string $Index - ���/�����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMIdDataObjectInterface $do - ����������� ���������, ��� ��������
 * @param string $ObjectName - ��� ���-������� � ������� �������, �� �������� ����������� �����������
 * @param string $FieldName - ��� ���� ��������� ���-������� � ������� �������, 
 * �� �������� ����������� ����� ������������
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $ObjectName, $FieldName )
{
    $this->DependenciesFieldsArray[$Index] = array( strval($ObjectName), strval($FieldName) ); 
    
    $this->DependenciesObjectsArray[$Index] = $do;
}

/**
 * ���������� ������ � ������� ����� ����������� � �������� $Index
 * 
 * @param string $Index - ���/�����-������ ������� � ����������
 * 
 * @return array - ��� ���-������� � ���� � ���-������� �������� �������, 
 * �� �������� ����������� ����� � ID ����������� ��� �������� $Index
 */
public function getDependenceField($Index)
{
    return isset($this->DependenciesFieldsArray[$Index]) ? $this->DependenciesFieldsArray[$Index] : null;
}
/**
 * 
 * @return array(TRMIdDataObjectInterface) - ���������� ������ 
 * �� ����� ������������ ��� �������� ������� �� ����������
 */
public function getDependenciesObjectsArray()
{
    return $this->DependenciesObjectsArray;
}

/**
 * ���������� ������ ����������� � �������� $Index �� ���������� ��������
 * 
 * @param string $Index - ���/�����-������ ������� � ����������
 * 
 * @return TRMIdDataObjectInterface - ��������� � ��������� ������, ����������� � ����������
 */
public function getDependenceObject($Index)
{
    if( !isset($this->DependenciesObjectsArray[$Index]) )
    {
        throw new TRMDataObjectSContainerWrongIndexException( get_class($this) . " - " . __METHOD__ . " - " . $Index );
    }
    return $this->DependenciesObjectsArray[$Index];
}

/**
 * 
 * @param string $Index - ������ ������� � ����������
 * @return bool - ���� ������ � ���������� ��� ���� �������� ������������ ��� ��������� �� ��������,
 * ��������, ������ ������������� ��� ������, �� �������� true, ���� ����������� �� ����������, �� - false
 */
public function isDependence($Index)
{
    return key_exists($Index, $this->DependenciesFieldsArray);
}

/**
 * @return array - ������ �������� � ������������� ����:
 * array("ObjectName" => array( "RelationSubObjectName" => type, "RelationFieldName" =>fieldname ), ... )
 */
public function getDependenciesFieldsArray()
{
    return $this->DependenciesFieldsArray;
}

/**
 * ������� ������ � ���. ��������� ������,
 * ��� �� � ���� �������� �������� ������ �� ���� ������������ ���������
 */
public function clearDependencies()
{
    $this->DependenciesFieldsArray = array();
    $this->DependenciesObjectsArray = array();
}


/**
 * 
 * @param \TRMEngine\DataObject\TRMDataObjectsCollection $Collection - ���������, 
 * ��� ������� ������� ������� ����� ���������� ��������� ������ ������ ����������
 */
public function setParentFor( TRMDataObjectsCollection $Collection, \TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface $Parent)
{
    foreach( $Collection as $Object )
    {
        $Object->setParentDataObject($Parent);
    }
}

/**
 * �������� ��������� �������� ������ ������ � ������ ��� ������� $Index, 
 * ����������� ������ ������, ������� �� �����������!!!
 * 
 * @param string $Index - �����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMDataObjectsCollection $Collection - ����������� ������-���������
 */
public function setChildCollection($Index, TRMDataObjectsCollection $Collection) // ��� TRMParentedDataObject, �� ����� ������ ��� ��� �������� ������
{
    $this->ChildCollectionsArray[$Index] = $Collection;
    $this->setParentFor($Collection, $this);
}

/**
 * ���������� ������ �� ���������� ��� ������� $Index
 * 
 * @param integer $Index - ����� ������� � ����������
 * 
 * @return TRMDataObjectInterface - ������ �� ����������
 */
public function getChildCollection($Index)
{
    if( isset($this->ChildCollectionsArray[$Index]) ) { return $this->ChildCollectionsArray[$Index]; }
    return null;
}

/**
 * @return array - ���������� ������ �������� ������, ����������� �������� ������
 */
public function getChildCollectionsArray()
{
    return $this->ChildCollectionsArray;
}

/**
 * ������� ������ � ���. ��������� ������,
 * ��� �� � ���� �������� �������� ������ �� ���� ������������ ���������
 */
public function clearChildCollectionsArray()
{
    // ��� ��� � ������� �������� ������ �� �������� �������, �� ��� �� ��������� ��� ����������� �������,
    // ������� ������� ������������� ��� ������� ������� ������ �������� � null, 
    // ����� ��� �� ��������� �� ��������� �� �������� ��� �������
    foreach( $this->ChildCollectionsArray as $Collection )
    {
        $this->setParentFor( $Collection, null );
    }
    $this->ChildCollectionsArray = array();
}

/**
 * @return array - ������ ������ �� ���� ��������� ���� :
 * array(
 * "Main" => ������ �������� �������,
 * "Children" => array(
 *      "NameOfChild1" => ������ ������� ��������� �������,
 *      "NameOfChild2" => ������ ������� ��������� �������,
 *      "NameOfChild3" => ������ �������� ��������� �������,
 * ...
 *      )
 * )
 */
public function getOwnData()
{
    $arr = array( 
        "Main" => $this->MainDataObject->getOwnData(), 
        "Children" => array() );
    
    foreach ($this->ChildCollectionsArray as $Name => $Child)
    {
        if( $Child->count() )
        {
            $arr["Children"][$Name] = $Child->getOwnData();
        }
    }

    return $arr;
}

/**
 * 
 * @param array $data  - ������ �� ���� ��������� ���� :
 * array(
 * "Main" => ������ �������� �������,
 * "Children" => array(
 *      "NameOfChild1" => ������ ������� ��������� �������,
 *      "NameOfChild2" => ������ ������� ��������� �������,
 *      "NameOfChild3" => ������ �������� ��������� �������,
 * ...
 *      )
 * ), ��� ���� � ������� $this->ChildCollectionsArray - ��� ������ ���� �������������������� �������, 
 * �������������� �����, ��� �� ������� ������, � ������ $this->MainDataObject ���� ������ ���� ������
 * 
 * @throws TRMDataObjectContainerNoMainException - � ������� ������ ���� �� ����������� ������ � ������� ����� ���������� - Main, ����� ������������� ����������
 * // ���� �����-�� �� ������ �� ����� � ������� $data, �� ������������� ����������
 */
public function setOwnData(array $data)
{
    // �������� ����� ������� ������ ���� ����������� ������
    if( !isset($data["Main"]) )
    {
        throw new TRMDataObjectContainerNoMainException( __METHOD__ );
    }
    // ��� ��������� ����� ���� �������
    /*
    if( !isset($data["Children"]) )
    {
        throw new Exception( __METHOD__ . " �������� ������ ������! ���������� ���� Children!");
    }
     */
    $this->MainDataObject->setOwnData($data["Main"]);

    foreach( $this->ChildCollectionsArray as $Name => $Child )
    {
        if( !isset($data["Children"][$Name]) )
        {
            // ���� ����� ������ �� ���������, �� ����������
            continue;
            // throw new Exception( __METHOD__ . " �������� ������ ������! ���������� ����� ������� - {$Name} � ������� Children!");
        }
        $Child->setOwnData( $data["Children"][$Name] );
    }
}


/**
 * ���������� ������ ������ ��� ���������-�������� �������!!!
 */
public function getDataArray()
{
    return $this->MainDataObject->getDataArray();
}

/**
 * ������������� ������ ������ � �������� �������
 * @param array $data
 */
public function setDataArray(array $data)
{
    $this->MainDataObject->setDataArray($data);
}
/**
 * ���������� ������ ������ ��� ���������-�������� �������!!!
 * @parm integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param string $objectname - ��� ������� � ������ � ������� $rownum, ��� �������� ���������� ������
 * @param string $fieldname - ��� ���� (�������), �� �������� ���������� ������ ��������
 *
 * @retrun mixed|null - ���� ��� ������ � ����� ������� ������ ��� ��� ���� � ����� ������ �������� null, ���� ����, �� ������ ��������
 */
public function getData($rownum, $objectname, $fieldname)
{
    return $this->MainDataObject->getData($rownum, $objectname, $fieldname);
}
/**
 * ������������� ������ ������ � �������� �������
 * @param integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param string $objectname - ��� ������� � ������ � ������� $rownum, ��� �������� ��������������� ������
 * @param string $fieldname - ��� ���� (�������), � ������� ���������� ������ ��������
 * @param mixed $value - ���� ������������ ��������
 */
public function setData($rownum, $objectname, $fieldname, $value)
{
    $this->MainDataObject->setData($rownum, $objectname, $fieldname, $value);
}

/**
 * ������������ ������ � ������ ��������� �������!!!
 * ��� �������� ����� ���������� � ������� ������� ��������� ��������
 * @param array $data
 */
public function mergeDataArray(array $data)
{
    $this->MainDataObject->mergeDataArray($data);
}

/**
 * ��������� ������� ������ ������ � �������� �������!!!
 * @param integer  $rownum
 * @param string $objectname - ��� ������� � ������ � ������� $rownum, ��� �������� ����������� ����� ������
 * @param array $fieldname
 */
public function presentDataIn($rownum, $objectname, array &$fieldname)
{
    $this->MainDataObject->presentDataIn($rownum, $objectname, $fieldname);
}


/****************************************************************************
 * ���������� ���������� TRMIdDataObjectInterface
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

static public function getIdFieldName()
{
    $type = static::getMainDataObjectType();
    return $type::getIdFieldName();
}
static public function setIdFieldName(array $IdFieldName)
{
    $type = static::getMainDataObjectType();
    $type::setIdFieldName($IdFieldName);
}
static public function getMainDataObjectType()
{
    return static::$MainDataObjectType;
}

/**
 * ���������� �������� ���������� � ���� $fieldname ������� $objectname
 * 
 * @param string $objectname - ��� �������, ��� �������� ���������� ������
 * @param string $fieldname - ��� ����
 * @return mixed|null - ���� ���� �������� � ���� $fieldname, �� �������� ��� ��������, ���� null,
 */
public function getFieldValue($objectname, $fieldname)
{
    $this->MainDataObject->getData(0, $objectname, $fieldname);
}
/**
 * ������������� �������� � ���� $fieldname ������� $objectname
 * 
 * @param string $objectname - ��� �������, ��� �������� ���������� ������
 * @param string $fieldname - ��� ����
 * @param mixed -  ��������, ������� ������� ���� ����������� � ���� $fieldname ������� $objectname
 */
public function setFieldValue($objectname, $fieldname, $value)
{
    $this->MainDataObject->setData(0, $objectname, $fieldname, $value);
}

/**
 * ���������� ���������� Countable,
 * ���������� ���������� �������� � ��������� �������� �������� ������
 */
public function count()
{
    return count($this->ChildCollectionsArray);
}


/**
 * ���������� ���������� Iterator,
 * ���������� ������� ������ �� �������-��������� � ��������� ���������
 */
public function current()
{
    return current($this->ChildCollectionsArray);
}

/**
 * 
 * @return mixed - ���������� ��������-��� �������� ������� (�����) ��� ��������� � ��������� ��������� ������,
 * ����� ���� ��������� ��� ���������
 */
public function key()
{
    return key($this->ChildCollectionsArray);
}

/**
 * ������������ ���������� ���������-������� �� ��������� ������� ������� � ��������� ���������
 */
public function next()
{
    next($this->ChildCollectionsArray);
    ++$this->Position;
}

/**
 * ������������� ���������� ������� ������� � ������ - ���������� ���������� Iterator
 */
public function rewind()
{
    reset($this->ChildCollectionsArray);
    $this->Position = 0;
}

/**
 * ���� ������� ��������� ��� ����� ������� �������, ������ � ���� �������� ��� ������ ���,
 * $this->Position ������ ������ ���� < count($this->ChildCollectionsArray)
 * 
 * @return boolean
 */
public function valid()
{
    return ($this->Position < count($this->ChildCollectionsArray));
}


} // TRMDataObjectsContainer