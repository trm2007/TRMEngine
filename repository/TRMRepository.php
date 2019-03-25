<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\TRMDataObject;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Repository\Exeptions\TRMRepositoryNoDataObjectException;
use TRMEngine\Repository\Exeptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

// use TRMEngine\Repository\Exeptions\TRMRepositoryGetObjectException;

abstract class TRMRepository implements TRMRepositoryInterface
{
/**
 * @var TRMDataSourceInterface - �������� ������ - ������ ��� ������ � ������� � ���������� ���������, � ������ ������ � ��
 */
protected $DataSource = null;
/**
 * @var TRMDataObjectInterface - ������ �� ������� ������
 */
protected $CurrentObject = null;

/**
 * @var string - ��� ���� ������, � �������� �������� ������ ��������� ������ Repository
 */
protected $ObjectTypeName = TRMDataObject::class; //"TRMDataObject";

/**
 * @param string $objectclassname - ��� ������ ��� ��������, �� ������� �������� ���� Repository
 */
public function __construct($objectclassname)
{
    $this->ObjectTypeName = (string)$objectclassname;
}

/**
 * ��������� ������ � ����������� � ������� � �������
 * 
 * @param TRMDataObjectInterface $object - ������ ������
 * 
 * @throws TRMRepositoryUnknowDataObjectClassException
 */
public function setObject(TRMDataObjectInterface $object)
{
    if( !is_a($object, $this->ObjectTypeName) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( get_class($object) . " ����������� " . get_class($this) );
    }
    $this->CurrentObject = $object;
    // $do = $this->CurrentObject->getDataObject();
    
    $this->DataSource->linkData( $object );
//    $this->DataSource->clear();
}

/**
 * ���������� ������ �� ������� ������, � ������� �������� Repository
 * 
 * @return TRMDataObjectInterface
 */
public function getObject()
{
    return $this->CurrentObject;
}

/**
 * �������� ����������� �� ������ ������, ��� ������ �� �����������, ������ ������ ����� � ������������!!!
 */
public function unlinkObject()
{
    $this->CurrentObject = null;
}

/**
 * @param TRMDataSourceInterface $datasource - �������� ������ - ������ ��� ������ � ������� � ���������� ���������, � ������ ������ � ��
 */
public function setDataSource(TRMDataSourceInterface $datasource)
{
    $this->DataSource = $datasource;
}

/**
 * @return TRMDataSourceInterface - �������� ������ - ������ ��� ������ � ������� � ���������� ���������, � ������ ������ � ��
 */
public function getDataSource()
{
    return $this->DataSource;
}

/**
 * ������������� ������� ��� WHERE ������ SQL-������� ��� ������� �� ��,
 * 
 * @param string $objectname - ��� �������, ���������� ���� ��� ���������
 * @param string $fieldname - ��� ���� ��� ���������
 * @param string|numeric|boolean $data - ������ ��� ���������
 * @param string $operator - �������� ��������� (=, !=, >, < � �.�.), ����������� =
 * @param string $andor - ��� ������� ����� ���� �������� OR ��� AND ? �� ��������� AND
 * @param integer $quote - ����� �� ����� � ��������� ����� �����, �� ��������� ����� - TRMSqlDataSource::TRM_AR_QUOTE
 * @param string $alias - ����� ��� ������� �� ������� ������������ ����
 * @param integer $dataquote - ���� ����� �������� ������������ ��������� ��� �������, 
 * �� ���� �������� ������� ���� - TRMSqlDataSource::TRM_AR_NOQUOTE
 * 
 * @return self - ���������� ��������� �� ����, ��� ���� ����������� ������ ����� ���������:
 * $this->setWhereCondition(...)->setWhereCondition(...)->setWhereCondition(...)...
 */
public function setWhereCondition($objectname, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE )
{
    $this->DataSource->addWhereParam($objectname, $fieldname, $data, $operator, $andor, $quote, $alias, $dataquote);
    return $this;
}

/**
 * ���������� ������� �������, ��������������� ��������� ��������� ��� ���������� ����
 * 
 * @param string $objectname - ��� ������� ��� ������ �� �������� ����
 * @param string $fieldname - ��� ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�.
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getBy($objectname, $fieldname, $value, $operator = "=")
{
    $this->DataSource->clearParams();
    $this->DataSource->addWhereParam($objectname, $fieldname, $value, $operator);
    return $this->getAll();
}

/**
 * ���������� ������� ���� �������,
 * ���� ����� ��� $this->DataSource ���� ����������� �����-�� �������, �� ��� ����� ������������ ��� �������,
 * �������� ��������� �������, ���������� ���������� �������, ��� ������� WHERE
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������, 
 * ������ ����� ���� ������, ���� �� �� �������� ������ ������, ��� ���� ������� ������ �� ���������
 */
public function getAll()
{
    if( null === $this->CurrentObject )
    {
        $this->setObject(new $this->ObjectTypeName);
    }
    if( !$this->DataSource->getDataFrom() )
    {
        $this->CurrentObject->clear();
    }
/*
    if( !$this->DataSource->getDataFrom() )
    {
        throw new TRMRepositoryGetObjectException( __METHOD__ . " ������ [{$this->ObjectTypeName}] �������� �� �������!");
//        return null;
    }
 * 
 */

    return $this->CurrentObject;
}

/**
 * ��������� ������ � ��������� ������
 * 
 * @param TRMDataObjectInterface $object - ������, ������ �������� ����� ��������� � �����������,
 * ���� ������ ��� ���������� �����, �� ����� �������� null, ����� ����� �������� ����� ������������� ������
 * 
 * @return boolean
 * 
 * @throws TRMRepositoryNoDataObjectException
 */
public function save(TRMDataObjectInterface $object = null)
{
    if( null !== $object )
    {
        $this->setObject($object);
    }
    if( null === $this->CurrentObject )
    {
        throw new TRMRepositoryNoDataObjectException( "�� ���������� ������ � ������� � ����������� " . get_class($this) );
    }
    return $this->update();
}

/**
 * 
 * @return boolean
 * 
 * @throws TRMRepositoryNoDataObjectException
 */
public function update()
{
    if( null === $this->CurrentObject )
    {
        throw new TRMRepositoryNoDataObjectException( __METHOD__ );
    }
    return $this->DataSource->update();
}

/**
 * 
 * @return boolean
 * 
 * @throws TRMRepositoryNoDataObjectException
 */
public function insert()
{
    if( null === $this->CurrentObject )
    {
        throw new TRMRepositoryNoDataObjectException( __METHOD__ );
    }
    return $this->DataSource->insert();
}

/**
 * 
 * @return boolean
 * 
 * @throws TRMRepositoryNoDataObjectException
 */
public function delete()
{
    if( null === $this->CurrentObject )
    {
        throw new TRMRepositoryNoDataObjectException( __METHOD__ );
    }
    return $this->DataSource->delete();
}


} // TRMRepository
