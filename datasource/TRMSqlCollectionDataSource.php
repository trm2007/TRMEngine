<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataSource\TRMSqlDataSource;

/**
 *  класс для получения и обработки группы записей из таблицы БД - TableName
 */
class TRMSqlCollectionDataSource extends TRMSqlDataSource
{

/**
 * устанавливает с какой записи начинать выборку - StartPosition
 * и какое количество записей выбирать - Count
 *
 * @param int - с какой записи начинать выборку
 * @param int - какое количество записей выбирать
 */
public function setLimit( $Count , $StartPosition = null )
{
    $this->StartPosition = $StartPosition;
    $this->Count = $Count;
}

/**
 * задает массив сортировки по полям, старые значения удаляются
 *
 * @param array - массив полей, по которым сортируется - array( fieldname1 => "ASC | DESC", ... )
 */
public function setOrder( array $orderfields )
{
    $this->OrderFields = array();

    $this->addOrder( $orderfields );
}

/**
 * Устанавливает поле для сортировки
 *
 * @param type $orderfieldname
 * @param type $asc
 */
public function setOrderField( $orderfieldname, $asc = 1 )
{
    $this->OrderFields[$orderfieldname] = ( ($asc == 1) ? "ASC" : "DESC");
}

/**
 * добавляем поля в массив сортировки, если уже есть, то старые значения перезаписываются
 *
 * @param array - массив полей, по которым сортируется - array( fieldname1 => "ASC | DESC", ... )
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
 * удаляет все записи из таблицы TableName, удовлетворяющие значению полей
 *
 * @param array - массив для поиска значения - array( FieldName => FieldValue )
 *
 * @return boolean - возвращает результат выполнения запроса, в случае успеха true
 */
/*
public function deleteAllFor(array $val)
{
    $querystrings = "";
    foreach( $val as $FieldName => $FieldValue )
    {
            if( null === $FieldValue )
            {
                    TRMLib::dp( __METHOD__ . " Передан NULL в значении поля для удаления!" );
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