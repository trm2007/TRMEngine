<?php

namespace TRMEngine\DataObject\Interfaces;

interface TRMParentedCollectionInterface extends TRMTypedCollectionInterface, TRMParentedDataObjectInterface
{
  // /**
  //  * @param int $Index - индекс запрашиваемого объекта в массиве-коллекции
  //  * 
  //  * @return TRMParentedDataObjectInterface - объект данных
  //  * @throws TRMDataObjectsCollectionWrongIndexException
  //  */
  // public function getDataObject($Index);
  // /**
  //  * устанавливает объект $DataObject в коллекцию под индексом $Index,
  //  * если такой индекс еще не существует, то создаст,
  //  * при этом значение родительского поля будет установлено в Id текущего родителя для коллекции
  //  * 
  //  * @param string $Index - индекс объекта в коллекции
  //  * @param TRMParentedDataObjectInterface $DataObject - устанавливаемый объект
  //  */
  // public function setDataObject($Index, TRMParentedDataObjectInterface $DataObject);
  // /**
  //  * добавдяет объект $DataObject в коллекцию,
  //  * при этом значение родительского поля будет установлено в Id текущего родителя для коллекции
  //  * 
  //  * @param TRMParentedDataObjectInterface $DataObject - добавит это объект в коллекцию
  //  * @param bool $AddDuplicateFlag - если этот флаг установден в false, то в коллекцию не добавятся дубликаты объектов,
  //  * если утсановить в TRUE, то объект добавится как новый,
  //  * даже если он дублирует уже присутсвующий,
  //  * по умолчанию - false (дубли не добавляются)
  //  * 
  //  * @return bool - если объект добавлен в коллекцию, то вернется TRUE, иначе FALSE
  //  */
  // public function addDataObject(TRMParentedDataObjectInterface $DataObject, $AddDuplicateFlag = false);
  // /**
  //  * проверяет, есть ли в коллекции объект,
  //  * точнее ссылка на этот объект
  //  * 
  //  * @param TRMDataObjectInterface $Object
  //  * @return bool
  //  */
  // public function hasDataObject(TRMParentedDataObjectInterface $Object);
}
