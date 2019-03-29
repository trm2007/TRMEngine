<?php

namespace TRMEngine\Repository;

use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\TRMDataObject;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlCollectionDataSource;
use TRMEngine\DataSource\TRMSqlDataSource;
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
 * @var string - ��� ���� ������, � �������� �������� ������ ��������� ������ Repository
 */
protected $ObjectTypeName = TRMDataObject::class; //"TRMDataObject";

/**
 * @var TRMDataObjectsCollection - ��������� �������� , 
 * ���������� ��� ��������� ������ ������ �� ������� getBy,
 * getOne - ���� �������� ���������, �� ������ ����� ��������!
 */
protected $CollectionToGet;
/**
 * @var TRMDataObjectsCollection - ��������� �������� , 
 * ����������� � �����������, ������� ����� �������� ��� �������� � ���������� ��������� DataSource
 */
protected $CollectionToUpdate;
/**
 * @var TRMDataObjectsCollection - ��������� �������� , 
 * ����������� � �����������, ������� ����� �������� ��� �������� � ���������� ��������� DataSource
 */
protected $CollectionToInsert;
/**
 * @var TRMDataObjectsCollection - ��������� �������� , 
 * ������� ������������ � �������� �� ����������� ��������� DataSource
 */
protected $CollectionToDelete;


/**
 * @param string $objectclassname - ��� ������ ��� ��������, �� ������� �������� ���� Repository
 */
public function __construct($objectclassname)
{
    $this->ObjectTypeName = (string)$objectclassname;
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
 * ���������� ������� ����� ������, 
 * ���� ����� ��� $this->DataSource ���� ����������� �����-�� �������, �� ��� ����� ������������ ��� �������,
 * �������� ��������� �������, ���������� ���������� �������, ��� ������� WHERE
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getOne()
{
    $this->DataSource->setLimit( 1 );
    
    $this->getAll();
    if( !$this->CollectionToGet->count() ) { return null; }
    
    $this->CollectionToGet->rewind();
    
    return $this->CollectionToGet->current();
}

/**
 * ���������� ������� ����� ������, 
 * ��������������� ���������� �������� ��� ���������� ����.
 * ���� � ���������� ��������� (��) ���� ��������� �������, ������������� �������,
 * �� ���-����� �������� ������ ���� ������.
 * ��� ������������� ����� ������� ����� ������� � ����������������,
 * ������� �� DataSource ������ ��� ������ ������� (����),
 * ���� ����� ������� �� ���������� �������� ����� ������� getOne();
 * 
 * @param string $objectname - ��� ������� ��� ������ �� �������� ����
 * @param string $fieldname - ��� ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�.
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getOneBy($objectname, $fieldname, $value, $operator = "=")
{
    $this->DataSource->clearParams();
    $this->DataSource->addWhereParam($objectname, $fieldname, $value, $operator);
    $this->DataSource->setLimit( 1 );
    
    $this->getAll();
    if( !$this->CollectionToGet->count() ) { return null; }
    
    $this->CollectionToGet->rewind();
    
    return $this->CollectionToGet->current();
}

/**
 * ���������� ������� �������, ��������������� ���������� �������� ��� ���������� ����
 * 
 * @param string $objectname - ��� ������� ��� ������ �� �������� ����
 * @param string $fieldname - ��� ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�.
 * 
 * @return TRMDataObjectsCollection - ������, ����������� ������� �� ���������
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
 * @return TRMDataObjectsCollection - ��������� � ���������, ������������ ������� �� ����������� ���������, 
 * ��������� ����� ���� ������, ���� �� �� �������� ������ ������, ��� ���� ������� ������ �� ���������
 */
public function getAll()
{
    $this->CollectionToGet->clearCollection();

    // � ������ ���������� ������� DataSource->getDataFrom() ����������� ����������
    $result = $this->DataSource->getDataFrom();
    // ���� � ������ ��� ������, ������������ ������ ���������
    if( !$result->num_rows ) { return $this->CollectionToGet; }

    // �� ������ ������ ������������ ���������� ��������� ������ ������
    while( $Row = $result->fetch_row() )
    {
        $this->CollectionToGet[] = $this->getDataObjectFromDataArray($Row);
    }
    
    return $this->CollectionToGet;
}
/**
 * @param array $DataArray - ������ � �������, �� ������� ����� ������ ������
 * 
 * @return TRMDataObjectInterface - ��������� ������ ������, ������� ������������ ���� ��������� �����������
 */
protected function getDataObjectFromDataArray(array $DataArray)
{
    $DataObject = new $this->ObjectTypeName;
    $k = 0;
    // ����������� ���������� ������ � ����������� �������� DataMapper-�
    foreach( $this->SafetyFields as $TableName => $TableState )
    {
        foreach( array_keys($TableState[TRMDataMapper::FIELDS_INDEX]) as $FieldName )
        {
            $DataObject->setData(0, $TableName, $FieldName, $DataArray[$k++]);
        }
    }
    return $DataObject;
}

/**
 * ��������� ������ � ��������� ������,
 * � ������ ���������� �������� $this->update($DataObject),
 * ������� ��������� ����� � ��������� ���������,
 * ����������� ������ ������ ������� � ��������� ���������� ����� ������ doUpdate();
 * 
 * @param TRMDataObjectInterface $DataObject - ������, ������ �������� ����� ��������� � �����������,
 */
public function save(TRMDataObjectInterface $DataObject)
{
    return $this->update($DataObject);
}

/**
 * @param TRMDataObjectInterface $DataObject - ������, ������� ����� �������� � ��������� �����������
 */
public function update(TRMDataObjectInterface $DataObject )
{
    foreach( $this->CollectionToUpdate as $CurrentObject )
    {
        // ���� ��������� �� ���� ������ ��� ���� � ���������,
        // �� ��������� ������ �������
        if( $DataObject === $CurrentObject )
        {
            return;
        }
    }
    $this->CollectionToUpdate->addDataObject($DataObject);
    
}

public function doUpdate()
{
    if( $this->CollectionToUpdate->count() )
    {
        $this->DataSource->update( $this->CollectionToUpdate );
    }
    $this->CollectionToUpdate->clearCollection();
}

/**
 * ��������� ������ � ���������������� ��������� ��� ���������� ������� � DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - ������, ������� ����� �������� � ��������� �����������
 */
public function insert(TRMDataObjectInterface $DataObject )
{
    foreach( $this->CollectionToInsert as $CurrentObject )
    {
        // ���� ��������� �� ���� ������ ��� ���� � ���������,
        // �� ��������� ������ �������
        if( $DataObject === $CurrentObject )
        {
            return;
        }
    }
    $this->CollectionToInsert->addDataObject($DataObject);
}
/**
 * ���������� ����������� ����� ������ ����������� ������ � ���������� ��������� DataSource
 */
public function doInsert()
{
    if( $this->CollectionToInsert->count() )
    {
        $this->DataSource->update( $this->CollectionToInsert );
    }
    $this->CollectionToInsert->clearCollection();
}

/**
 * ��������� ������ � ���������������� ��������� ��� ����������� �������� � DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - ������, ������� ����� �������� � ��������� �����������
 */
public function delete(TRMDataObjectInterface $DataObject)
{
    foreach( $this->CollectionToDelete as $CurrentObject )
    {
        // ���� ��������� �� ���� ������ ��� ���� � ���������,
        // �� ��������� ������ �������
        if( $DataObject === $CurrentObject )
        {
            return;
        }
    }
    $this->CollectionToDelete->addDataObject($DataObject);
}
/**
 * ����������� ���������� �������� ������ ������� ��������� �� ����������� ��������� DataSource
 */
public function doDelete()
{
    if( $this->CollectionToDelete->count() )
    {
        $this->DataSource->delete( $this->CollectionToDelete );
    }
    $this->CollectionToDelete->clearCollection();
}

/**
 * ��� ������, ������� ���� ��������� � ��������� ��� �������, ���������� � �������� 
 * ����� ���������� ���������, ��������� � �������, ������������� �� ������������ ��������� DataSource. 
 * ���������� ������� doInsert, ����� - , ����� - doDelete !
 */
public function doAll()
{
    $this->doInsert();
    $this->doUpdate();
    $this->doDelete();
}

} // TRMRepository
