<?php

namespace TRMEngine\DataSource\Interfaces;

use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Exceptions\TRMSqlQueryException;

/**
 *  ����������� �����, ����� ��� ���� ������� ��������� ������� �� ������� �� - TableName
 */
interface TRMDataSourceInterface
{
/**
 * ������������� � ����� ������ �������� ������� - StartPosition
 * � ����� ���������� ������� �������� - Count
 *
 * @param int $Count - � ����� ������ �������� �������
 * @param int $StartPosition - ����� ���������� ������� ��������
 */
public function setLimit( $Count , $StartPosition = null );
/**
 * ������ ������ ���������� �� �����, ������ �������� ���������
 *
 * @param array $orderfields - ������ �����, �� ������� ����������� - array( fieldname1 => "ASC | DESC", ... )
 */
public function setOrder( array $orderfields );
/**
 * ������� ���������� WHERE ������� � ������ �������� �������
 */
public function clear();

/**
 * ������� ���������� ��� WHERE-������� � SQL-�������
 */
public function clearParams();
/**
 * ��������� �������� ��� ������� WHERE � �������
 * 
 * @param string $tablename - ��� ������� ��� ����, ������� ����������� � �������
 * @param string $fieldname - ��� ���� ��� ���������
 * @param string|numeric|boolean $data - ������ ��� ���������
 * @param string $operator - �������� ��������� (=, !=, >, < � �.�.), ����������� =
 * @param string $andor - ��� ������� ����� ���� �������� OR ��� AND ? �� ��������� AND
 * @param integer $quote - ����� �� ����� � ��������� ����� �����, �� ��������� ����� - TRMSqlDataSource::NEED_QUOTE
 * @param string $alias - ����� ��� ������� �� ������� ������������ ����, ���� �� �����, �� ����� ��������� � ������� ������� �������
 * @param integer $dataquote - ���� ����� �������� ������������ ��������� ��� �������, 
 * �� ���� �������� ������� ���� - TRMSqlDataSource::NOQUOTE, 
 * �� ��������� � �������� - TRMSqlDataSource::NEED_QUOTE
 * 
 * @return $this
 */
public function addWhereParam($tablename, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE);
/**
 * ��������� ������� � ������ WHERE-�������
 * 
 * @param string $tablename - ��� ������� ��� ����, ������� ����������� � �������
 * @param array $params - ������ � ����������� ���������� �������<br>
 * array(
 * "key" => $fieldname,<br>
 * "value" => $data,<br>
 * "operator" => $operator,<br>
 * "andor" => $andor,<br>
 * "quote" => $quote,<br>
 * "alias" => $alias,<br>
 * "dataquote" => $dataquote );
 * 
 * @return $this
 */
public function addWhereParamFromArray($tablename, array $params);
/**
 * ��������� ������ ���������� � ��� ��������������
 *
 * @param string $tablename - ��� ������� ��� �������� ��������������� ���������
 * @param array - ���������, ������������ � �������, ��� ������� ���� ���������� ID-������ 
 * ��� ������ ������������ � ������� array( $fieldname => array(value, operator, andor, quote, alias, dataquote), ...)
 * ������������� �������� array(..., $fieldname => array(value), ...)
 */
public function generateParamsFrom($tablename, array $params);
/**
 * ��������� ������ ���������� � $query
 * 
 * @param string $query - ������ SQL-�������
 * 
 * @return \mysqli_result - ������-��������� ���������� �������
 * @throws TRMSqlQueryException - � ������ ���������� ���������� ������� ������������� ����������
 */
public function executeQuery($query);
/**
 * ��������� ������ �� �� ��������� ������, ������� ���������� ������� makeSelectQuery
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, ��� �������� ����������� ������� �� ��
 *
 * @return \mysqli_result - ���������� ����������� ����� �� ��
 * @throws TRMSqlQueryException - � ������ ���������� ���������� ������� ������������� ����������
 */
public function getDataFrom(TRMSafetyFields $SafetyFields);
/**
 * ��������� ������ �� �� ��������� ������, ������� ���������� ������� makeSelectQuery
 * ��������� ���������� ��������� � ��������� ������
 *
 * @return int - ���������� ����������� ����� �� ��
 * @throws TRMSqlQueryException - � ������ ���������� ���������� ������� ������������� ����������
 */
//public function addDataFrom();
/**
 * ��������� ������ � ������� �� ������� �� ��������� ��������-������ $DataCollection,
 * ���� ������ ��� ��� � �������, �.�. ��� ID ��� ������� ������, �� ��������� ��,
 * � ������ ������ ���������� INSERT ... ON DUPLICATE KEY UPDATE ...
 *
 * @param TRMSafetyFields $SafetyFields - DataMapper, ��� �������� ����������� ������� �� ��
 * @param TRMDataObjectsCollection $DataCollection - ��������� � ��������� ������
 * 
 * @return boolean - ���� ���������� ������ �������, �� ������ true, ����� - false
 */
public function update(TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection);
/**
 * ��������� ����� ������ � ��, 
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, ��� �������� ����������� ������� �� ��
 * 
 * @return boolean - ���� ���������� ������ �������, �� ������ true, ����� - false
 */
public function insert( TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection );
/**
 * ������� ������ ��������� �� ������ ��,
 * �� �������� ������� ��������� ������, ������� ������������� �������� ������������ ID-����,
 * ���� ������ ���, �� ������������ �� ���������� �������� �� ���� ����� 
 * (��������� ��� ������, ������� ���� ���� TRM_AR_UPDATABLE_FIELD) � ��������� ������ ���������,
 * ��� �� �������� ������ �� �������� ������, 
 * ���� � ��� ����� ���� �� ���� ���� ��������� ��� �������������� - TRM_AR_UPDATABLE_FIELD
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, ��� �������� ����������� ������� �� ��
 * @param TRMDataObjectsCollection $DataCollection - ��������� � ��������� ������
 * 
 * @return boolean - ���������� ��������� ������� DELETE
 */
public function delete(TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection);

} // TRMDataSourceInterface