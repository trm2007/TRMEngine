<?php

namespace TRMEngine\Repository;

use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException;
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
    if( !class_exists($objectclassname) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $objectclassname );
    }
    $this->ObjectTypeName = $objectclassname;
    
    $this->CollectionToInsert = new TRMDataObjectsCollection();
    $this->CollectionToUpdate = new TRMDataObjectsCollection();
    $this->CollectionToDelete = new TRMDataObjectsCollection();
}

/**
 * @return TRMDataMapper
 */
function getDataMapper()
{
    return $this->DataMapper;
}
/**
 * @param TRMDataMapper $DataMapper
 */
function setDataMapper(TRMDataMapper $DataMapper)
{
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
public function addCondition($objectname, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE )
{
    $this->DataSource->addWhereParam($objectname, $fieldname, $data, $operator, $andor, $quote, $alias, $dataquote);
    return $this;
}
/**
 * ������� ������� ��� ������� (� SQL-�������� ������ WHERE)
 */
public function clearCondition()
{
    $this->DataSource->clearParams();
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

    // � ������ ���������� ������� DataSource->getDataFrom() ����������� ����������
    $result = $this->DataSource->getDataFrom( $this->DataMapper );
    // ���� � ������ ��� ������, ������������ ������ ���������
    if( !$result->num_rows ) { return null; }

    // ������ ��������� ������ ���� ������,
    // �� ��� ��������� ������ ������
    return $this->getDataObjectFromDataArray($result->fetch_row(), $DataObject);
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
    $this->clearCondition();
    $this->addCondition($objectname, $fieldname, $value);
    
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
    $this->clearCondition();
    $this->addCondition($objectname, $fieldname, $value);
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
    if( isset($Collection) )
    {
        $NewGetCollection = $Collection;
    }
    else
    {
        $NewGetCollection = new TRMDataObjectsCollection();
    }

    // � ������ ���������� ������� DataSource->getDataFrom() ����������� ����������
    $result = $this->DataSource->getDataFrom($this->DataMapper);
    // ���� � ������ ��� ������, ������������ ������ ���������
    if( !$result->num_rows ) { return null; }

    // �� ������ ������ ������������ ���������� ��������� ������ ������
    while( $Row = $result->fetch_row() )
    {
        // � ��������� ������ ����������� ����� ������
        $NewGetCollection->addDataObject( $this->getDataObjectFromDataArray($Row) );
    }

    return $NewGetCollection;
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
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� ���������� ��������� ��������� ����������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doUpdate ����� �������� ���������,
 * ��� �� �� ��������� ���������� � ������� 2 ����!
 */
public function doUpdate( $ClearCollectionFlag = true )
{
    if( $this->CollectionToUpdate->count() )
    {
        $this->DataSource->update( $this->DataMapper, $this->CollectionToUpdate );

        if( $ClearCollectionFlag ) { $this->CollectionToUpdate->clearCollection(); }
    }
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
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� �������� ��������� ��������� ��������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doDelete ����� �������� ���������,
 * ��� �� �� ��������� �������� � ������� 2 ����!
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� ���������� ��������� ��������� ����������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doInsert ����� �������� ���������,
 * ��� �� �� ��������� ������� � ������� 2 ����!
 */
public function doInsert( $ClearCollectionFlag = true )
{
    if( $this->CollectionToInsert->count() )
    {
        $this->DataSource->insert( $this->DataMapper, $this->CollectionToInsert );

        if( $ClearCollectionFlag ) { $this->CollectionToInsert->clearCollection(); }
    }
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
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� �������� ��������� ��������� ��������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doDelete ����� �������� ���������,
 * ��� �� �� ��������� �������� � ������� 2 ����!
 */
public function doDelete( $ClearCollectionFlag = true )
{
    if( $this->CollectionToDelete->count() )
    {
        $this->DataSource->delete( $this->DataMapper, $this->CollectionToDelete );

        if( $ClearCollectionFlag ) { $this->CollectionToDelete->clearCollection(); }
    }
}

/**
 * ��� ������, ������� ���� ��������� � ��������� ��� �������, ���������� � �������� 
 * ����� ���������� ���������, ��������� � �������, ������������� �� ����������� ��������� DataSource. 
 * ���������� ������� doInsert, ����� - doUpdate, ����� - doDelete !
 */
public function doAll()
{
    $this->doInsert();
    $this->doUpdate();
    $this->doDelete();
}


} // TRMRepository
