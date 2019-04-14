<?php

namespace TRMEngine\DataObject\Interfaces;

use TRMEngine\DataObject\TRMDataObjectsCollection;

/**
 * интерфейс для составных объектов,
 * у которых есть главный объект данных, и коллекция вспомогательных (дочерних)
 */
interface TRMDataObjectsContainerInterface extends TRMIdDataObjectInterface, \Iterator
{
/**
 * @return TRMIdDataObjectInterface - возвращает главный (сохраненный под 0-м номером в массиве) объект данных
 */
public function getMainDataObject();

/**
 * устанавливает главный объект данных,
 * 
 * @param TRMIdDataObjectInterface $do - главный объект данных
 */
public function setMainDataObject(TRMIdDataObjectInterface $do);

/**
 * помещает объект данных с именем $Index в массив-контейнер зависимостей, 
 * сохраняется только ссылка, объект не клонируется!!!
 * 
 * @param string $Index - имя/номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMIdDataObjectInterface $do - добавляемая коллекция, как дочерняя
 * @param string $ObjectName - имя суб-объекта в главном объекте, по которому связывается зависимость
 * @param string $FieldName - имя поля основного суб-объекта в главном объекте, 
 * по которому установлена связь зависимостью
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $ObjectName, $FieldName );

/**
 * возвращает массив с именами полей зависимости с индексом $Index
 * 
 * @param string $Index - имя/номер-индекс объекта в контейнере
 * 
 * @return array - имя суб-объекта и поля в суб-объекте главного объекта, 
 * по которому установлена связь с ID зависимости под индексом $Index
 */
public function getDependenceField($Index);

/**
 * 
 * @return array(TRMIdDataObjectInterface) - возвращает массив 
 * со всеми зависимосяти для главного объекта из контейнера
 */
public function getDependenciesObjectsArray();

/**
 * возвращает объект зависимости с индексом $Index из контейнера объектов
 * 
 * @param string $Index - имя/номер-индекс объекта в контейнере
 * 
 * @return TRMIdDataObjectInterface - коллекция с объектами данных, сохраненная в контейнере
 */
public function getDependenceObject($Index);

/**
 * 
 * @param string $Index - индекс объекта в контейнере
 * @return bool - если объект в контейнере под этим индексом зафиксирован как зависимый от главного,
 * например, список характеристик для товара, то вернется true, если зависимость не утсанвлена, то - false
 */
public function isDependence($Index);

/**
 * @return array - массив массивов с зависимостями вида:
 * array("ObjectName" => array( "RelationSubObjectName" => type, "RelationFieldName" =>fieldname ), ... )
 */
public function getDependenciesFieldsArray();

/**
 * очищает массив с доп. объектами данных,
 * так же у этих объектов обнуляет ссылку на этот родительский контейнер
 */
public function clearDependencies();

/**
 * 
 * @param TRMDataObjectsCollection $Collection - коллекция, 
 * для каждого объекта которой нужно установить родителем данный объект контейнера
 */
public function setParentFor( TRMDataObjectsCollection $Collection, TRMIdDataObjectInterface $Parent);

/**
 * помещает коллекцию дочерних объект данных в массив под номером $Index, 
 * сохраняется только ссылка, объекты не клонируются!!!
 * 
 * @param string $Index - номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMDataObjectsCollection $Collection - добавляемый объект-коллекция
 */
public function setChildCollection($Index, TRMDataObjectsCollection $Collection);

/**
 * возвращает объект из контейнера под номером $Index
 * 
 * @param integer $Index - номер объекта в контейнере
 * 
 * @return TRMDataObjectInterface - объект из контейнера
 */
public function getChildCollection($Index);

/**
 * @return array - возвращает массив объектов данных, дополняющих основной объект
 */
public function getChildCollectionsArray();

/**
 * очищает массив с доп. объектами данных,
 * так же у этих объектов обнуляет ссылку на этот родительский контейнер
 */
public function clearChildCollectionsArray();


} // TRMDataObjectsContainerInterface


interface TRMRelationDataObjectsContainerInterface extends TRMDataObjectsContainerInterface
{

} // TRMRelationDataObjectsContainerInterface
