<?php

namespace TRMEngine\DataObject\Interfaces;

use TRMEngine\DataArray\Interfaces\TRMDataArrayInterface;

/**
 * ����� ��������� ��� �������� ������
 */
interface TRMDataObjectInterface extends TRMDataArrayInterface
{
/**
 * ��������� ������� ���� � ������ fieldname � sub-������� $objectname
 * 
 * @param string $objectname - ��� sub-�������, ��� �������� ����������� ������� ���� $fieldname
 * @param string $fieldname - ��� �������� ����
 * 
 * @return boolean - ���� ������, ���������� true, ���� ���� ����������� - false
 */
public function fieldExists( $objectname, $fieldname );

/**
 * �������� ������ �� ���������� ������
 *
 * @param string $objectname - ��� sub-������� � ������ � ������� $rownum, ��� �������� ���������� ������
 * @param string $fieldname - ��� ���� (�������), �� �������� ���������� ������ ��������
 *
 * @retrun mixed|null - ���� ��� ������ � ����� ������� ������ ��� ��� ���� � ����� ������ �������� null, ���� ����, �� ������ ��������
 */
public function getData( $objectname, $fieldname );
/**
 * ���������� ������ � ���������� ������
 *
 * @param string $objectname - ��� sub-������� � ������ � ������� $rownum, ��� �������� ��������������� ������
 * @param string $fieldname - ��� ���� (�������), � ������� ���������� ������ ��������
 * @param mixed $value - ���� ������������ ��������
 */
public function setData( $objectname, $fieldname, $value );

/**
 * ��������� ������� ������ � ����� � ������� �� ������ $fieldnames � ������ � ������� $rownum
 *
 * @param string $objectname - ��� sub-�������, ��� �������� ����������� ����� ������
 * @param &array $fieldnames - ������ �� ������ � ������� ����������� �����
 *
 * @return boolean - ���� ������� ���� � ����������� ��������, �� ������������ true, ����� false
 */
public function presentDataIn( $objectname, array &$fieldnames );


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
public function getParentDataObject();
/**
 * @param TRMIdDataObjectInterface $ParentDataObject - ������������� ������ ��������, 
 */
public function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject);

} // TRMParentedDataObjectInterface
