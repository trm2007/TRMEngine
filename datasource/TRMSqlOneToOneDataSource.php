<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataMapper\TRMSafetyFields;

/**
 * ����� ��� ��������� � ��������� ����� ������ �� ������� �� - TableName,
 * ��������� �������� �� ������ ������ �� ������ ����-�-������
 */
class TRMSqlOneToOneDataSource extends TRMSqlDataSource
{

public function __construct(TRMSafetyFields $SafetyFields)
{
    parent::__construct($SafetyFields);
    $this->StartPosition = null;
    $this->Count = 1;
}

    /*
public function __construct()//$MainTableName, $MainIdName, array $SecondTableArray = null, $MainAlias = null )
{
    parent::__construct($MainTableName, $MainIdName, $SecondTableArray, $MainAlias);

    $this->StartPosition = null;
    $this->Count = 1;
}
 * 
 */


/**
 * ��������� ����� ������ � ��
 *
 * @return int|boolean - ���� ��� ������ ������, �� ��� ����� ������ ������������ �� ����� ����-����������, ����� 0, 
 * � ������ ������ - false, ��������� ������ ������� ��������� ����� === false, ����� �� ������ � �����!
 */
function add()
{
    if( false === parent::add() )
    {
            return false;
    }
    return $this->LastId;
}

} // TRMSqlOneToOneDataSource