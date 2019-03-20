<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataSource\TRMSqlDataSource;

/**
 *  ����� ��� ��������� � ��������� ������ ������� �� ������� �� - TableName
 */
class TRMSqlCollectionDataSource extends TRMSqlDataSource
{

/**
 * ������������� � ����� ������ �������� ������� - StartPosition
 * � ����� ���������� ������� �������� - Count
 *
 * @param int - � ����� ������ �������� �������
 * @param int - ����� ���������� ������� ��������
 */
public function setLimit( $Count , $StartPosition = null )
{
    $this->StartPosition = $StartPosition;
    $this->Count = $Count;
}

/**
 * ������ ������ ���������� �� �����, ������ �������� ���������
 *
 * @param array - ������ �����, �� ������� ����������� - array( fieldname1 => "ASC | DESC", ... )
 */
public function setOrder( array $orderfields )
{
    $this->OrderFields = array();

    $this->addOrder( $orderfields );
}

/**
 * ������������� ���� ��� ����������
 *
 * @param type $orderfieldname
 * @param type $asc
 */
public function setOrderField( $orderfieldname, $asc = 1 )
{
    $this->OrderFields[$orderfieldname] = ( ($asc == 1) ? "ASC" : "DESC");
}

/**
 * ��������� ���� � ������ ����������, ���� ��� ����, �� ������ �������� ����������������
 *
 * @param array - ������ �����, �� ������� ����������� - array( fieldname1 => "ASC | DESC", ... )
 */
public function addOrder( array $orderfields )
{
    foreach( $orderfields as $field => $order )
    {
            if( !isset($order) || $order == "" ) { $order = "ASC"; }
            $order = trim(strtoupper($order));
            if( $order == "ASC" || $order == "DESC" )
            {
                    $this->OrderFields[$field] = $order;
            }
    }
}

/**
 * ������� ��� ������ �� ������� TableName, ��������������� �������� �����
 *
 * @param array - ������ ��� ������ �������� - array( FieldName => FieldValue )
 *
 * @return boolean - ���������� ��������� ���������� �������, � ������ ������ true
 */
/*
public function deleteAllFor(array $val)
{
    $querystrings = "";
    foreach( $val as $FieldName => $FieldValue )
    {
            if( null === $FieldValue )
            {
                    TRMLib::dp( __METHOD__ . " ������� NULL � �������� ���� ��� ��������!" );
                    return false;
            }
            $querystrings .= "`" . $FieldName . "` = '" . $FieldValue . "' AND ";
    }
    $querystrings = "DELETE FROM `{$this->TableName}` WHERE " . rtrim($querystrings, "AND ") . " ;";
    return TRMDBObject::$newlink->query($querystrings);
}
 * 
 */


} // TRMSqlCollectionDataSource