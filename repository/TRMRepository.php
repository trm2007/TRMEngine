<?php

namespace TRMEngine\Repository;

use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

/**
 * �������� ����� ��� ����������� �������� ������ �����,
 * ��������� ���������, ���������, ������� � ������ ������ ��� �������� �� ��������� ������ (DataSource).
 * � �������� ������� ������ ���� ������ DataMapper,
 * � ��� �� ����� �������� ������ DataSource (� ������ ������ ������������ SQL � �� MySQL)
 */
abstract class TRMRepository implements TRMRepositoryInterface
{
/**
 * @var TRMDataSourceInterface - �������� ������ - ������ ��� ������ � ������� � ���������� ���������, � ������ ������ � ��
 */
protected $DataSource = null;

/**
 * @var string - ��� ���� ������, � �������� �������� ������ ��������� ������ Repository
 */
protected $ObjectTypeName = ""; //TRMDataObject::class; //"TRMDataObject";
/**
 * @var TRMDataObjectsCollectionInterface - ��������� �������� , 
 * ���������� ��� ��������� ������ ������ �� ������� getBy,
 * getOne - ���� �������� ���������, �� ������ ����� ��������!
 */
protected $GetCollection;
/**
 * @var TRMDataObjectsCollectionInterface - ��������� �������� , 
 * ����������� � �����������, ������� ����� �������� ��� �������� � ���������� ��������� DataSource
 */
protected $CollectionToUpdate;
/**
 * @var TRMDataObjectsCollectionInterface - ��������� �������� , 
 * ����������� � �����������, ������� ����� �������� ��� �������� � ���������� ��������� DataSource
 */
protected $CollectionToInsert;
/**
 * @var TRMDataObjectsCollectionInterface - ��������� �������� , 
 * ������� ������������ � �������� �� ����������� ��������� DataSource
 */
protected $CollectionToDelete;

/**
 * @var TRMDataMapper 
 */
protected $DataMapper;

/**
 * @param string $objectclassname - ��� ������ ��� ��������, �� ������� �������� ���� Repository
 */
public function __construct($objectclassname)
{
    $this->ObjectTypeName = (string)$objectclassname;
    
    $this->GetCollection = new TRMDataObjectsCollection();
    $this->CollectionToInsert = new TRMDataObjectsCollection();
    $this->CollectionToUpdate = new TRMDataObjectsCollection();
    $this->CollectionToDelete = new TRMDataObjectsCollection();
}

function getDataMapper() {
    return $this->DataMapper;
}

function setDataMapper(TRMDataMapper $DataMapper) {
    $this->DataMapper = $DataMapper;
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
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getOne( TRMDataObjectInterface $DataObject = null )
{
    $this->DataSource->setLimit( 1 );

    $this->GetCollection->clearCollection();

    // � ������ ���������� ������� DataSource->getDataFrom() ����������� ����������
    $result = $this->DataSource->getDataFrom();
    // ���� � ������ ��� ������, ������������ ������ ���������
    if( !$result->num_rows ) { return null; }

    // ������ ��������� ������ ���� ������,
    // �� ��� ��������� ������ ������
    $DataObject = $this->getDataObjectFromDataArray($result->fetch_row(), $DataObject);
    $this->GetCollection->addDataObject( $DataObject );

    return $DataObject;
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
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getOneBy($objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null)
{
    $this->DataSource->clearParams();
    $this->DataSource->addWhereParam($objectname, $fieldname, $value);
    
    return $this->getOne( $DataObject );
}

/**
 * ���������� ������� �������, ��������������� ���������� �������� ��� ���������� ����
 * 
 * @param string $objectname - ��� ������� ��� ������ �� �������� ����
 * @param string $fieldname - ��� ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param TRMDataObjectsCollectionInterface $Collection - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectsCollectionInterface - ������, ����������� ������� �� ���������
 */
public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null)
{
    $this->DataSource->clearParams();
    $this->DataSource->addWhereParam($objectname, $fieldname, $value);
    return $this->getAll($Collection);
}

/**
 * ���������� ������� ���� �������,
 * ���� ����� ��� $this->DataSource ���� ����������� �����-�� �������, 
 * �� ��� ����� ������������ ��� �������,
 * ��������, ��������� �������, ���������� ���������� �������, ��� ������� WHERE
 * 
 * @param TRMDataObjectsCollectionInterface $Collection - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectsCollection - ��������� � ���������, ������������ ������� �� ����������� ���������, 
 * ��������� ����� ���� ������, ���� �� �� �������� ������ ������, ��� ���� ������� ������ �� ���������
 */
public function getAll( TRMDataObjectsCollectionInterface $Collection = null )
{
    $this->GetCollection->clearCollection();

    // � ������ ���������� ������� DataSource->getDataFrom() ����������� ����������
    $result = $this->DataSource->getDataFrom();
    // ���� � ������ ��� ������, ������������ ������ ���������
    if( !$result->num_rows ) { return null; }

    // �� ������ ������ ������������ ���������� ��������� ������ ������
    while( $Row = $result->fetch_row() )
    {
        // � ��������� ������ ����������� ����� ������
        $this->GetCollection->addDataObject( $this->getDataObjectFromDataArray($Row) );
    }
    if( isset($Collection) )
    {
        $Collection = $this->GetCollection;
    }

    return $this->GetCollection;
}
/**
 * @param array $DataArray - ������ � �������, �� ������� ����� ������ ������
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ��������� ������ ������, ������� ������������ ���� ��������� �����������
 */
protected function getDataObjectFromDataArray( array $DataArray, TRMDataObjectInterface $DataObject = null )
{
    if( !$DataObject )
    {
        $DataObject = new $this->ObjectTypeName;
    }
    $k = 0;
    // ����������� ���������� ������ � ����������� �������� DataMapper-�
    foreach( $this->DataMapper as $TableName => $TableState )
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
public function save( TRMDataObjectInterface $DataObject)
{
    return $this->update($DataObject);
}

/**
 * @param TRMDataObjectInterface $DataObject - ������, ������� ����� �������� � ��������� �����������
 */
public function update( TRMDataObjectInterface $DataObject )
{
    // ���� ��������� �� ���� ������ ��� ���� � ���������,
    // �� addDataObject ��� ������������ ����� �� ������� ���,
    // ������� ����� �������� �� ��������
    $this->CollectionToUpdate->addDataObject($DataObject);
    
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - ���������, ������� ������� ����� �������� � ��������� �����������
 */
public function updateCollection(TRMDataObjectsCollectionInterface $Collection )
{
    $this->CollectionToUpdate->mergeCollection($Collection);
}
/**
 * ���������� ��������� ������� �� ���������������� ���������,
 * � ������ ������ � �� ���������� SQL-������� UPDATE-������
 */
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
public function insert( TRMDataObjectInterface $DataObject )
{
    // ���� ��������� �� ���� ������ ��� ���� � ���������,
    // �� addDataObject ��� ������������ ����� �� ������� ���,
    // ������� ����� �������� �� ��������
    $this->CollectionToInsert->addDataObject($DataObject);
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - ���������, ������� ������� ����� �������� � ��������� �����������
 */
public function insertCollection(TRMDataObjectsCollectionInterface $Collection )
{
    $this->CollectionToInsert->mergeCollection($Collection);
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
 * @param TRMDataObjectInterface $DataObject - ������, ������� ����� �������� � ��������� ���������
 */
public function delete( TRMDataObjectInterface $DataObject)
{
    // ���� ��������� �� ���� ������ ��� ���� � ���������,
    // �� addDataObject ��� ������������ ����� �� ������� ���,
    // ������� ����� �������� �� ��������
    $this->CollectionToDelete->addDataObject($DataObject);
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - ���������, ������� ������� ����� �������� � ��������� ���������
 */
public function deleteCollection(TRMDataObjectsCollectionInterface $Collection )
{
    $this->CollectionToDelete->mergeCollection($Collection);
}
/**
 * ���������� ����������� �������� ������ ������� ��������� �� ����������� ��������� DataSource
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
