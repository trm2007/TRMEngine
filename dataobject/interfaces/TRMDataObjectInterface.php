<?php

namespace TRMEngine\DataObject\Interfaces;

/**
 * ����� ��������� ��� �������� ������
 */
interface TRMDataObjectInterface extends Countable, Iterator
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
 * ���������� ������ � ���������� ������
 *
 * @param integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param string $fieldname - ��� ���� (�������), � ������� ���������� ������ ��������
 * @param mixed $value - ���� ������������ ��������
 */
public function setData( $rownum, $fieldname, $value );
/**
 * �������� ������ �� ���������� ������
 *
 * @parm integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param string $fieldname - ��� ���� (�������), �� �������� ���������� ������ ��������
 *
 * @retrun mixed|null - ���� ��� ������ � ����� ������� ������ ��� ��� ���� � ����� ������ �������� null, ���� ����, �� ������ ��������
 */
public function getData( $rownum, $fieldname );

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
 * @param &array $fieldnames - ������ �� ������ � ������� ����������� �����
 *
 * @return boolean - ���� ������� ���� � ����������� ��������, �� ������������ true, ����� false
 */
public function presentDataIn( $rownum, array &$fieldnames );

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
 * @return string - ���������� ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��
 */
public function getIdFieldName();

/**
 * @param string $IdFieldName - ������������� ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��
 */
public function setIdFieldName($IdFieldName) ;

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
 * ���������� �������� ���������� � ���� $fieldname
 * 
 * @param string $fieldname - ��� ����
 * @return mixed|null - ���� ���� �������� � ���� $fieldname, �� �������� ��� ��������, ���� null,
 */
public function getFieldValue( $fieldname );

} // TRMIdDataObjectInterface


/**
 * ��������� ��� ��������� ��������,
 * � ������� ���� ������� ������ ������, � ��������� ��������������� (��������)
 */
interface TRMDataObjectsContainerInterface extends TRMDataObjectInterface
{
/**
 * @return TRMDataObjectInterface - ���������� ������� (����������� ��� 0-� ������� � �������) ������ ������
 */
public function getMainDataObject();
/**
 * ������������� ������� ������ ������,
 * 
 * @param TRMDataObjectInterface $do - ������� ������ ������
 */
public function setMainDataObject(TRMDataObjectInterface $do);
/**
 * �������� ������ ������ � ������ ��� ������� $Index, ����������� ������ ������, ������ �� �����������!!!
 * 
 * @param string $Index - �����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMDataObjectInterface $do - ����������� ������
 */
public function setDataObject($Index, TRMDataObjectInterface $do);
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

} // TRMDataObjectsContainerInterface


/**
 * ��������� ��� �������� ������, � ������� ���� �������� (������ � ��������� ���� ������ �� ������ ��������),
 * ��������, � ������� ������ ����� ���� ������ �� ������,
 * � ��������� ����������� ������ �� �����, � �������� �� ����������� � �.�...
 */
interface TRMParentedDataObjectInterface extends TRMDataObjectInterface
{
/**
 * @return string - ��� �������� ������ ������� ����������� Id ��������
 */
function getParentIdFieldName();
/**
 * @param string $ParentIdFieldName - ��� �������� ������ ������� ����������� Id ��������
 */
function setParentIdFieldName($ParentIdFieldName);
/**
 * @return TRMIdDataObjectInterface - ���������� ������ ��������
 */
function getParentDataObject();
/**
 * @param TRMIdDataObjectInterface $ParentDataObject - ������������� ������ ��������, 
 */
function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject);

} // TRMParentedDataObjectInterface