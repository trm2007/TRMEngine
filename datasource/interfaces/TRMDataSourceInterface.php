<?php

namespace TRMEngine\DataSource\Interfaces;

use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataSource\TRMSqlDataSource;

/**
 *  ����������� �����, ����� ��� ���� ������� ��������� ������� �� ������� �� - TableName
 */
interface TRMDataSourceInterface
{
/**
 * @return TRMSafetyFields - ������ DataMapper ��� �������� ������ ������
 */
function getSafetyFields();
/**
 * @param TRMSafetyFields $SafetyFields - ������ DataMapper ��� �������� ������ ������
 */
function setSafetyFields(TRMSafetyFields $SafetyFields);
/**
 * ������������� ����� � �������� ������,
 * ������ ������ ������ ������������� ��������� TRMDataObjectInterface
 * 
 * @param TRMDataObjectInterface $data - ������, ������ �������� ����� �������� �/��� ���������/������� � ��
 */
public function linkData( TRMDataObjectInterface $data );
/**
 * �������� ��������� �������� auto_increment ���� ��� �������� �������!
 * 
 * @return int - �������� ��� ���� auto_increment ����� �������� ������� ������
 */
//public function getLastId();
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
public function addWhereParam($fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE);
/**
 * ��������� ������� � ������ WHERE-�������
 * 
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
public function addWhereParamFromArray(array $params);
/**
 * ��������� ������ ���������� � ��� ��������������
 *
 * @param array - ���������, ������������ � �������, ��� ������� ���� ���������� ID-������ 
 * ��� ������ ������������ � ������� array( $fieldname => array(value, operator, andor, quote, alias, dataquote), ...)
 * ������������� �������� array(..., $fieldname => array(value), ...)
 */
public function generateParamsFrom(array $params = null);
/**
 * ��������� ������ ���������� � $query
 * 
 * @param string $query - ������ SQL-�������
 * 
 * @return mysqli_result - ������-��������� ���������� �������
 * @throws Exception - � ������ ���������� ���������� ������� ������������� ����������
 */
public function executeQuery($query);
/**
 * ��������� ������ �� �� ��������� ������, ������� ���������� ������� makeSelectQuery
 * �������������� ��������� ������ � �������
 *
 * @return int - ���������� ����������� ����� �� ��
 * @throws Exception - � ������ ���������� ���������� ������� ������������� ����������
 */
public function getDataFrom();
/**
 * ��������� ������ �� �� ��������� ������, ������� ���������� ������� makeSelectQuery
 * ��������� ���������� ��������� � ��������� ������
 *
 * @return int - ���������� ����������� ����� �� ��
 * @throws Exception - � ������ ���������� ���������� ������� ������������� ����������
 */
public function addDataFrom();
/**
 * ���������� ������ ���� TRMSafetyFields - ���������� ��� ������ � ������ �����,
 * � ��� �� ����� ��������� ������ ����� �� ��������� ������ �� ������ ��,
 * ��������� ����� ����� � ��������� � ��������� ��� ������� ��� ������, ���� �����������
 *
 * @throws Exception - ���� �� ������ ��� ������� ������� ������������ ����������
 */
//public function generateSafetyFromDB();
/**
 * ��������� ������ � ������� �� ������� �� �������-������ DataObject,
 * ���� ������ ��� ��� � �������, �.�. ��� ID ��� ������� ������, �� ��������� ��,
 * � ������ ������ ���������� INSERT ... ON DUPLICATE KEY UPDATE ...
 *
 * @return boolean - ���� ���������� ������ �������, �� ������ true, ����� - false
 */
public function update();
/**
 * ������� ������ ��������� �� ������ ��,
 * �� �������� ������� ��������� ������, ������� ������������� �������� ������������ ID-����,
 * ���� ������ ���, �� ������������ �� ���������� �������� �� ���� ����� 
 * (��������� ��� ������, ������� ���� ���� TRM_AR_UPDATABLE_FIELD) � ��������� ������ ���������,
 * ��� �� �������� ������ �� �������� ������, 
 * ���� � ��� ����� ���� �� ���� ���� ��������� ��� �������������� - TRM_AR_UPDATABLE_FIELD
 * 
 * @return boolean - ���������� ��������� ������� DELETE
 */
public function delete();

} // TRMDataSourceInterface