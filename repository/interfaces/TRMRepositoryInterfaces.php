<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataSource\TRMSqlDataSource;

/**
 * интерфейс для объектов репозитория, используемых в системе TRMEngine
 */
interface TRMRepositoryInterface
{
/**
 * @return boolean - значение условия сохранения параметров запроса после его выполнения
 */
public function getKeepQueryParams();
/**
 * @param boolean $KeepQueryParams - после каждого запроса на получение коллекции (getAll, getBy) все параметры запроса обнуляются,
 * очищаются поля сортировки, количество выбираемых значений, условия,
 * НО устанавливая KeepQueryParams в TRUE очистка переметров производится не будет
 */
public function setKeepQueryParams($KeepQueryParams);
/**
 * устанавливает условие для WHERE секции SQL-запроса при выборке из БД,
 * 
 * @param string $objectname - имя объекта, содержащее поле для сравнения
 * @param string $fieldname - имя поля для сравнения
 * @param string|numeric|boolean $data - данные для сравнения
 * @param string $operator - оператор сравнения (=, !=, >, < и т.д.), поумолчанию =
 * @param string $andor - что ставить перед этим условием OR или AND ? по умолчанию AND
 * @param integer $quote - нужно ли брать в апострофы имена полей, по умолчанию нужно - TRMSqlDataSource::TRM_AR_QUOTE
 * @param string $alias - альяс для таблицы из которой сравнивается поле
 * @param integer $dataquote - если нужно оставить сравниваемое выражение без кавычек, 
 * то этот аргумент доложен быть - TRMSqlDataSource::NOQUOTE
 * 
 * @return self - возвращает указатель на себя, это дает возможность писать такие выражения:
 * $this->setWhereCondition(...)->setWhereCondition(...)->setWhereCondition(...)...
 */
public function addCondition(
        $objectname, 
        $fieldname, 
        $data, 
        $operator = "=", 
        $andor = "AND", 
        $quote = TRMSqlDataSource::NEED_QUOTE, 
        $alias = null, 
        $dataquote = TRMSqlDataSource::NEED_QUOTE );
/**
 * очищает условия для выборки (в SQL-запросах секция WHERE)
 */
public function clearCondition();
/**
 * очищает все параметры для запроса (выборки),
 * условия выборки, количество выбираемых значений, поля сортировки...
 */
public function clearQueryParams();
/**
 * устанавливает с какой записи начинать выборку - StartPosition
 * и какое количество записей выбирать - Count
 *
 * @param int $Count - какое количество записей выбирать
 * @param int $StartPosition - с какой записи начинать выборку
 */
public function setLimit( $Count , $StartPosition = null );

/**
 * Производит выборку одной записи, 
 * если ранее для $this->DataSource были установлены какие-то условия, то они будут использованы для выборки,
 * например начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getOne(TRMDataObjectInterface $DataObject = null);
/**
 * Производит выборку одной записи, 
 * удовлетворяющих указанному значению для указанного поля.
 * Если в постоянном хранилище (БД) есть несколько записей, удовлтворящих запросу,
 * то все-равно вернется только один объект.
 * Все установленные ранее условия будут очищены и проигнорированны,
 * выборка из DataSource только под одному условию (полю),
 * если нужна выборка по нескольким условиям нужна функция getOne();
 * 
 * @param string $objectname - имя объекта для поиска по значению поля
 * @param string $fieldname - имя поля, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getOneBy($objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null);
/**
 * Производит выборку всех записей,
 * если ранее для $this->DataSource были установлены какие-то условия, то они будут использованы для выборки,
 * например начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @param TRMDataObjectsCollectionInterface $Collection - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectsCollectionInterface - коллекция с объектами, заполненными данными из постоянного хранилища, 
 * коллекция может быть пустой, если из БД вернулся пустой запрос, при этом никаких ошибок не возникает
 */
public function getAll(TRMDataObjectsCollectionInterface $Collection = null);

/**
 * Производит выборку записей, удовлетворяющих указанному значению одного поля,
 * целесообразно применять, если нужно сделать выборку по одному полю 
 * без сложных WHERE запросов
 * 
 * @param string $objectname - имя объекта для поиска по значению поля
 * @param string $fieldname - имя поля, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param TRMDataObjectsCollectionInterface $Collection - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null);
/**
 * создает новый объект,
 * заполняет значениями по умолчанию из DataMapper,
 * 
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return \TRMEngine\Repository\TRMDataObjectInterface - новый объект
 */
public function getNewObject( TRMDataObjectInterface $DataObject = null );
/**
 * Сохраняет объект в хранилище данных
 * 
 * @param TRMDataObjectInterface $DataObject - объект, данные которого нужно сохранить в репозитории
 */
public function save(TRMDataObjectInterface $DataObject);
/**
 * обновляет или добавляет (если у объекта не установлено значение в уникальном поле или в поле первичного ключа) данные объекта в хранилище
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function update(TRMDataObjectInterface $DataObject);
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию сохраняемых
 */
public function updateCollection(TRMDataObjectsCollectionInterface $Collection );
/**
 * фактически обновляет объекты из подготовительной коллекции,
 * в случае работы с БД отправляет SQL-серверу UPDATE-запрос
 * 
 * @param bool $ClearCollectionFlag - если нужно после обновления сохранить коллекцию обновленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doUpdate нужно очистить коллекцию,
 * что бы не повторять обновление в будущем 2 раза!
 */
public function doUpdate( $ClearCollectionFlag = true );

/**
 * Добавляет объект в подготовительную коллекцию для дальнейшей вставки в DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function insert( TRMDataObjectInterface $DataObject );
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию вставляемых
 */
public function insertCollection( TRMDataObjectsCollectionInterface $Collection );
/**
 * производит фактический вызов метода добавляения данных в постоянное хранилище DataSource
 * 
 * @param bool $ClearCollectionFlag - если нужно после обновления сохранить коллекцию добавленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doInsert нужно очистить коллекцию,
 * что бы не повторять вставку в будущем 2 раза!
 */
public function doInsert( $ClearCollectionFlag = true );

/**
 * добавляет данные объекта в хранилище, 
 * как првило используется INSERT ... ON DUPLICATE KEY UPDATE,
 * нужно смотреть реализацию
 */
//function insert();
/**
 * удаляет все данные об объекте из хранилища
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function delete(TRMDataObjectInterface $DataObject);
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию удаляемых
 */
public function deleteCollection( TRMDataObjectsCollectionInterface $Collection );
/**
 * производит фактичесоке удаление данных объетов коллекции из постоянного хранилища DataSource
 * 
 * @param bool $ClearCollectionFlag - если нужно после удаления сохранить коллекцию удаленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doDelete нужно очистить коллекцию,
 * что бы не повторять удаление в будущем 2 раза!
 */
public function doDelete( $ClearCollectionFlag = true );


} // TRMRepositoryInterface


/**
 * интерфейс для объектов репозитория, используемых в системе TRMEngine
 */
interface TRMIdDataObjectRepositoryInterface extends TRMRepositoryInterface
{
/**
 * получает данные объекта из хранилища по ID,
 * никакие условия кроме выборки по ID не срабатывают и удаляются!
 * 
 * @param scalar $id - идентификатор (Id) объекта
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getById($id, TRMDataObjectInterface $DataObject = null);

/**
 * @return array - array(имя суб-объекта, имя поля) для ID у обрабатываемых данным репозиторием объектов
 */
public function getIdFieldName();

/**
 * @param array $IdFieldName - array(имя суб-объекта, имя поля) 
 * для ID у обрабатываемых данным репозиторием объектов
 */
//public function setIdFieldName( array $IdFieldName );


} // TRMIdDataObjectRepositoryInterface