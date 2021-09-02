<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptyIdFieldException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptyMainObjectException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperRelationException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperTooManyMainObjectException;
use TRMEngine\DataMapper\Interfaces\TRMIdDataMapperInterface;

/**
 * Расширение класса DataMapper,
 * с описанием всех отношений между sub-объектами внутри,
 * используется понятие "главного" объекта, т.е. объекта,
 * на который никто не ссылается, например,
 * в структуре данных товар и его производитель,
 * товар имеет ссылку на объект производителя,
 * соответственно объект товара будет определен как "главный",
 * именно его Id вернет getIdFieldName
 *
 * @author Sergey Kolesnikov <trm@mail.ru>
 */
class TRMIdDataMapper extends TRMDataMapper implements TRMIdDataMapperInterface
{
  /**
   * @var array - массив с ID-полем для "главного" объекта (на который нет ссылок)
   */
  protected $IdFieldName = array();


  /**
   * @return array - массив array("имя главного объекта", "имя его ID-поля")
   */
  public function getIdFieldName()
  {
    if (empty($this->IdFieldName)) {
      $this->generateIdFieldName();
    }
    return $this->IdFieldName;
  }

  /**
   * @param array $IdFieldName - массив array("имя главного объекта", "имя его ID-поля")
   */
  public function setIdFieldName(array $IdFieldName)
  {
    $this->IdFieldName = $IdFieldName;
  }

  /**
   * @return array - возвращает массив array(имя объекта, имя поля) 
   * для поля содержащего ID главного объекта, т.е. объекта без обратных ссылок на него
   * 
   * @throws TRMDataMapperEmptyMainObjectException
   * @throws TRMDataMapperTooManyMainObjectException
   * @throws TRMDataMapperEmptyIdFieldException
   */
  public function generateIdFieldName()
  {
    $this->IdFieldName = array();
    // получаем массив объектов без ссылок на них, т.е. главные объекты
    $MainObjects = $this->getObjectsNamesWithoutBackRelations();
    // если массив пуст или таких объектов больше 1, то выбрасываем исключение
    if (empty($MainObjects)) {
      throw new TRMDataMapperEmptyMainObjectException();
    }
    if (count($MainObjects) > 1) {
      throw new TRMDataMapperTooManyMainObjectException();
    }

    $MainObject = $MainObjects[0];

    $ObjectsIds = $this->DataArray[$MainObject]->getPriFields();
    if (empty($ObjectsIds)) {
      throw new TRMDataMapperEmptyIdFieldException("Объект-таблица: {$MainObject}...");
    }
    $ObjectId = $ObjectsIds[0];

    // сохраняем информацию об ID для объекта
    $this->IdFieldName = array($MainObject, $ObjectId);
    return $this->IdFieldName;
  }

  /**
   * @param string $LookingObjectName - имя проверяемого объекта
   * @param string $LookingFieldName - имя проверяемого поля на предмет ссылающихся на него других полей
   * 
   * @return array - возвращает массив содержащий ссылающиеся поля на проверяемое поле $LookingObjectName => $LookingFieldName,
   * массив вида array( $ObjectName1 => array(0=>$FieldName1, 1=>$FieldName2, ...), $ObjectName2 => ... )
   */
  public function getBackRelationFor($LookingObjectName, $LookingFieldName)
  {
    $FieldsArray = array();
    foreach ($this->DataArray as $ObjectName => $Object) {
      foreach ($Object as $FieldName => $Field) {
        // если у очередного поля есть секция Relatin (RELATION_INDEX)
        // проверяем ссылается ли она на проверяемое поле
        if (
          !empty($Field->Relation)
          && $Field->Relation[TRMDataMapper::OBJECT_NAME_INDEX] == $LookingObjectName
          && $Field->Relation[TRMDataMapper::FIELD_NAME_INDEX] == $LookingFieldName
        ) {
          $FieldsArray[$ObjectName][] = $FieldName;
        }
      }
    }
    return $FieldsArray;
  }

  /**
   * сортирует порядок объектов в массиве $this->DataArray,
   * таким образом, что сначала идут объекты, на которые есть ссылки, но которые ни на кого не ссылаются,
   * и дальше в такой последоватенльности, 
   * что бы ссылающиеся объекты располагались дальше, чем те, на которые они ссылаются,
   * обратная сортировка функция sortObjectsForReverseRelationOrder
   */
  public function sortObjectsForRelationOrder()
  {
    return uksort($this->DataArray, array($this, "compareTwoTablesRelation"));
  }

  /**
   * сортирует порядок объектов в массиве $this->DataArray,
   * таким образом, что бы ссылающиеся объекты располагались раньше, чем те, на которые они ссылаются,
   * обратная сортировка функции sortObjectsForRelationOrder
   */
  public function sortObjectsForReverseRelationOrder()
  {
    return uksort($this->DataArray, array($this, "compareTwoTablesReverseRelation"));
  }

  /**
   * функция для сортировка ключей массива $this->DataArray,
   * т.е. для сортировка по именам таблиц, основываясь на наличии Relation и ссылок одной таблицы на другу,
   * если одна таблица ссылается на другую, значит она больше другой, 
   * и другая должна идти в порядке обработки первее...
   * в данном случае:
   * если из $Table1Name есть ссылка на $Table2Name, то вернется +1, т.е. $Table1Name > $Table2Name
   * если на $Table1Name есть ссылка из $Table2Name, то вернется -1, т.е. $Table1Name < $Table2Name
   * еслии таблицы не связаны друг с другом, то вернется 0,  т.е. $Table1Name == $Table2Name
   * 
   * @param string $Table1Name - первый сравниваемый ключ - имя таблицы 1
   * @param string $Table2Name - второй сравниваемый ключ - имя таблицы 1
   * @return int - 0 - порядок одинаковый, 
   * +1 $Table1Name больше $Table2Name, и $Table2Name должна идти раньше (сортировка по возрастанию),
   * -1 $Table2Name больше $Table1Name, и $Table1Name должна идти раньше
   */
  private function compareTwoTablesRelation($Table1Name, $Table2Name)
  {
    // количество ссылок в 1-м объекте
    $Relation1 = 0;
    // количество ссылок во 2-м объекте
    $Relation2 = 0;
    // проверяем ссылается ли таблица 1 на таблицу 2
    foreach ($this->DataArray[$Table1Name] as $FieldName => $Field) {
      if (!empty($Field->Relation)) {
        $Relation1++;
        if ($Field->Relation[TRMDataMapper::OBJECT_NAME_INDEX] == $Table2Name) {
          // число >0, 1-я таблица сссылается на 2-ю, $Table1Name > $Table2Name, 
          // таблица 2 должна обновляться раньше, что бы обновились поля для связи
          // это нужно, например, когда добавляется новая запись с автоинкрементным полем, на которое есть ссылка,
          // перед добавлением записи поле пустое и у ссылающейся таблицы, естественно, тоже!
          // а после добавления мы уже имеем inserted_id и новое значение поля auto_increment,
          // значение которого должны занести в Relation-поле ссылающейся таблицы
          return +1;
        }
      }
    }
    // если ссылок из Т1 на Т2 не найдено, проверяем наоборот, ссылки из Т2 на Т1
    // проверяем ссылается ли таблица 1 на таблицу 2
    foreach ($this->DataArray[$Table2Name] as $FieldName => $Field) {
      if (!empty($Field->Relation)) {
        $Relation2++;
        if ($Field->Relation[TRMDataMapper::OBJECT_NAME_INDEX] == $Table1Name) {
          // число <0, 2-я таблица сссылается на 1-ю, $Table1Name < $Table2Name, 
          // таблица 1 должна обновляться раньше, что бы обновились поля для связи
          return -1;
        }
      }
    }

    // если объекты не ссылаются друг на друга, 
    // то сравнивается кол-во ссылок в одном и другом объекте
    if ($Relation1 > $Relation2) {
      return +1;
    }
    if ($Relation1 < $Relation2) {
      return -1;
    }

    // если ничего не найдено, значит таблицы идентичны
    // с точки зрения порядка обновления
    return 0;
  }
  /**
   * функция для сортировка ключей массива $this->DataArray,
   * т.е. для сортировка по именам таблиц, основываясь на наличии Relation и ссылок одной таблицы на другу,
   * если одна таблица ссылается на другую, значит она больше другой, 
   * и другая должна идти в порядке обработки первее...
   * в данном случае:
   * если из $Table1Name есть ссылка на $Table2Name, то вернется -1, т.е. $Table1Name < $Table2Name
   * если на $Table1Name есть ссылка из $Table2Name, то вернется +1, т.е. $Table1Name > $Table2Name
   * еслии таблицы не связаны друг с другом, то вернется 0,  т.е. $Table1Name == $Table2Name
   * 
   * @param string $Table1Name - первый сравниваемый ключ - имя таблицы 1
   * @param string $Table2Name - второй сравниваемый ключ - имя таблицы 1
   * 
   * @return int - 0 - порядок одинаковый, 
   * -1 $Table1Name меньше $Table2Name, и $Table2Name должна идти после (сортировка по возрастанию),
   * +1 $Table2Name меньше $Table1Name, и $Table1Name должна идти после
   */
  private function compareTwoTablesReverseRelation($Table1Name, $Table2Name)
  {
    return (-1 * $this->compareTwoTablesRelation($Table1Name, $Table2Name));
  }

  /**
   * Как правило в объекте данных один внутренний объект (таблица для случая с БД) играет роль главного,
   * например, товар - главный, а производитель, единица измерения - это вспомогательные объекты,
   * главный объект использует, т.е. ссылается на вспомогательные, 
   * но вспомогательные не могут использовать - ссылаться на главный объект,
   * таких объектов (главных без ссылок на них) может быть несколько,
   * эта функция возвращает массив со всеми именами объектов без обратных ссылок на них
   * 
   * @return array - возвращает массив, содержащий имена объектов, на которые нет ссылок внутри DataMapper
   * @throws TRMDataMapperRelationException - если таких объектов не обнаружится, 
   * то выбрасывается исключение, в данной версии циклические ссылки не допустимы!
   */
  public function getObjectsNamesWithoutBackRelations()
  {
    // получаем все имена объектов внутри SafetyFields
    // меняем ключи со значением местами, 
    // таким образом получаем пустой массив с ключами как у SafetyFieldsArray
    $ObjectsNamesArray = array_flip($this->getArrayKeys());

    foreach ($this->DataArray as $Object) {
      foreach ($Object as $Field) {
        // если у очередного поля есть секция Relation (ссылка на другое поле другого объекта)
        // то удаляем элемента массива $ObjectsNamesArray с именем объекта, 
        // на который идет ссылка
        if (!empty($Field->Relation)) {
          unset($ObjectsNamesArray[$Field->Relation[TRMDataMapper::OBJECT_NAME_INDEX]]);
          if (empty($ObjectsNamesArray)) {
            throw new TRMDataMapperRelationException(__METHOD__);
          }
        }
      }
    }

    // возвращаем массив из оставшихся ключей. т.е. из оставшихся имен объектов!!!
    return array_keys($ObjectsNamesArray);
  }
}
