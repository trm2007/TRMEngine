<?php

namespace TRMEngine\DataObject\Interfaces;

/**
 * ����� ��������� ��� �������� ������
 */
interface TRMDataObjectInterface extends \Countable, \Iterator
{
/**
 * ���������� ���� ������ � �������, �������� ��������,
 * ��� ��� ������ ���������� �� �������� ( ������ PHP 5.3 ) !!!
 *
 * @return array
 */
public function getDataArray();

/**
 * ������ ������ ��� ����� ������� DataArray, ������ ������ ���������.
 * ������������ ������ ���������� ������� ���������,
 * ��� ��� ������������ ������ ��������� ������, ���� ��������� �� ����� ������!!!
 *
 * @param array $data - ������ � �������, � ������� ���������� �������� �������, 
 * ��� ��� ������ ���������� �� �������� ( ������ PHP 5.3 ) !!! 
 */
public function setDataArray( array $data );

/**
 * "���������" ��� ������� � �������, �������� �� ������������ �� ����������,
 * ��� ������������� ����� ������ ����� ���� ���������� � ������������ ��������, 
 * �� ������ ���� ��������� � ������ ������-������ ������ ����� ��������� ������
 *
 * @param array $data - ������ ��� ����������
 */
public function mergeDataArray( array $data );
/**
 * �������� ������ �� ���������� ������
 *
 * @parm integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param string $objectname - ��� ������� � ������ � ������� $rownum, ��� �������� ���������� ������
 * @param string $fieldname - ��� ���� (�������), �� �������� ���������� ������ ��������
 *
 * @retrun mixed|null - ���� ��� ������ � ����� ������� ������ ��� ��� ���� � ����� ������ �������� null, ���� ����, �� ������ ��������
 */
public function getData( $rownum, $objectname, $fieldname );
/**
 * ���������� ������ � ���������� ������
 *
 * @param integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param string $objectname - ��� ������� � ������ � ������� $rownum, ��� �������� ��������������� ������
 * @param string $fieldname - ��� ���� (�������), � ������� ���������� ������ ��������
 * @param mixed $value - ���� ������������ ��������
 */
public function setData( $rownum, $objectname, $fieldname, $value );

/**
 * @return array - ���������� ������, ����������� ������ ��� ������� ����������
 */
public function getOwnData();
/**
 * ������������� ������, ����������� ������ ��� ������� ����������, 
 * ������ �������� ��� ���������
 * 
 * @param array $data - ������ � �������, � ������� ���������� �������� ������� 
 */
public function setOwnData( array $data );
/**
 * ��������� ������� ������ � ����� � ������� �� ������ $fieldnames � ������ � ������� $rownum
 *
 * @param integer $rownum - ����� ������, � ������� ���������� ��������, �� ���������� ������ ������, ������ � 0
 * @param string $objectname - ��� ������� � ������ � ������� $rownum, ��� �������� ����������� ����� ������
 * @param &array $fieldnames - ������ �� ������ � ������� ����������� �����
 *
 * @return boolean - ���� ������� ���� � ����������� ��������, �� ������������ true, ����� false
 */
public function presentDataIn( $rownum, $objectname, array &$fieldnames );

/**
 * ������ �� ���� ������� �������� ���� $FieldName �� ����� �������� $FieldValue, ���� ��������� ������
 *
 * @param string $ObjectName - ��� �������, � ������� �������� �������� 
 * @param string $FieldName - ��� ����-�������
 * @param mixed $FieldValue - ����� ��������
 */
public function changeAllValuesFor($ObjectName, $FieldName, $FieldValue);

} // TRMDataObjectInterface


/**
 * ���������, ������� ������ ������������� ��� ������� ������,
 * � ������� ���� �����-���� �������������, ��� ������� ��� ID-�������
 *
 * @author TRM

 */
interface TRMIdDataObjectInterface extends TRMDataObjectInterface
{
/**
 * @return array - ���������� ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��,
 * ������������ ������ IdFieldName = array( ��� �������, ��� ID-���� � ������� )
 */
static public function getIdFieldName();

/**
 * @param array $IdFieldName - ������������� ��� �������� ��� �������������� �������, 
 * ������ ��������� � ������ ID-���� �� ��,
 * ���������� ������ IdFieldName = array( ��� �������, ��� ID-���� � ������� )
 */
static public function setIdFieldName( array $IdFieldName ) ;

/**
 * ���������� ��� ������� �������� �������������� - Id
 * ��� ����� ��� ���������� ����� ������ ��� �������� getIdFieldName()
 *
 * @return int|null - ID-�������
 */
public function getId();

/**
 * ������������� ��� ������� �������� ���� ������� ���������� �����!!!
 * ��� ����� ��� ���������� ����� ������ ��� �������� getIdFieldName()
 *
 * @param mixed - ID-�������
 */
public function setId($id);

/**
 * �������� ID-�������
 * ������������ setId(null);
 */
public function resetId();

/**
 * ���������� �������� ���������� � ���� $fieldname ������� $objectname
 * 
 * @param string $objectname - ��� �������, ��� �������� ���������� ������
 * @param string $fieldname - ��� ����
 * @return mixed|null - ���� ���� �������� � ���� $fieldname, �� �������� ��� ��������, ���� null,
 */
public function getFieldValue( $objectname, $fieldname );
/**
 * ������������� �������� � ���� $fieldname ������� $objectname
 * 
 * @param string $objectname - ��� �������, ��� �������� ���������� ������
 * @param string $fieldname - ��� ����
 * @param mixed -  ��������, ������� ������� ���� ����������� � ���� $fieldname ������� $objectname
 */
public function setFieldValue( $objectname, $fieldname, $value );

} // TRMIdDataObjectInterface


/**
 * ��������� ��� �������� ������, � ������� ���� �������� (������ � ��������� ���� ������ �� ������ ��������),
 * ��������, � ������� ������ ����� ���� ������ �� ������,
 * � ��������� ����������� ������ �� �����, � �������� �� ����������� � �.�...
 */
interface TRMParentedDataObjectInterface extends TRMDataObjectInterface
{
/**
 * @return array - ��� �������� ������ ������� ����������� Id ��������
 */
static public function getParentIdFieldName();
/**
 * @param array $ParentIdFieldName - ��� �������� ������ ������� ����������� Id ��������
 */
static public function setParentIdFieldName(array $ParentIdFieldName);
/**
 * @return TRMIdDataObjectInterface - ���������� ������ ��������
 */
function getParentDataObject();
/**
 * @param TRMIdDataObjectInterface $ParentDataObject - ������������� ������ ��������, 
 */
function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject);

} // TRMParentedDataObjectInterface


/**
 * ��������� ��� ��������� ��������,
 * � ������� ���� ������� ������ ������, � ��������� ��������������� (��������)
 */
interface TRMDataObjectsContainerInterface extends TRMIdDataObjectInterface
{
/**
 * @return TRMIdDataObjectInterface - ���������� ������� (����������� ��� 0-� ������� � �������) ������ ������
 */
public function getMainDataObject();
/**
 * ������������� ������� ������ ������,
 * 
 * @param TRMIdDataObjectInterface $do - ������� ������ ������
 */
public function setMainDataObject(TRMIdDataObjectInterface $do);
/**
 * �������� ������ ������ � ������ ��� ������� $Index, ����������� ������ ������, ������ �� �����������!!!
 * 
 * @param string $Index - �����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMDataObjectInterface $do - ����������� ������
 */
//public function setDataObject($Index, TRMDataObjectInterface $do);
/**
 * ���������� ������ �� ���������� ��� ������� $Index
 * 
 * @param integer $Index - ����� ������� � ����������
 * 
 * @return TRMDataObjectInterface - ������ �� ����������
 */
public function getDataObject($Index);
/**
 * @return array - ���������� ������ �������� ������, ����������� �������� ������
 */
public function getObjectsArray();

/**
 * �������� ������ ������ � ������ $Index � ������-��������� ������������, 
 * ����������� ������ ������, ������ �� �����������!!!
 * 
 * @param string $Index - ���/�����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMIdDataObjectInterface $do - ����������� ������
 * @param string $ObjectName - ��� ���-������� � ������� �������, �� �������� ����������� �����������
 * @param string $FieldName - ��� ���� ��������� ���-������� � ������� �������, 
 * �� �������� ����������� ����� ������������
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $ObjectName, $FieldName );

/**
 * ���������� ������ � ������ $Index �� �������-���������� ������������
 * 
 * @param string $Index - ���/�����-������ ������� � ����������
 * 
 * @return array - ��� ���-������� � ���� � ���-������� �������� �������, 
 * �� �������� ����������� ����� � ID ����������� ��� �������� $Index
 */
public function getDependence($Index);

/**
 * 
 * @param string $Index - ������ ������� � ����������
 * @return bool - ���� ������ � ���������� ��� ���� �������� ������������ ��� ��������� �� ��������,
 * ��������, ������ ������������� ��� ������, �� �������� true, ���� ����������� �� ����������, �� - false
 */
public function isDependence($Index);

/**
 * ���������� ��� �������� ������� � ����������, 
 * ������ ��� ����� ��� �������� � ���������� ������ ������������ IdFieldName
 */
static public function getMainDataObjectType();

} // TRMDataObjectsContainerInterface


interface TRMRelationDataObjectsContainerInterface extends TRMDataObjectsContainerInterface
{

} // TRMRelationDataObjectsContainerInterface
