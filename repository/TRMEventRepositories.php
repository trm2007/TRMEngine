<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;
use TRMEngine\Repository\TRMDataObjectsContainerRepository;

/**
 * ��������� ������������ ��� ��������-����������� ������,
 * �������� �� ������ ��������� ����������, 
 * ��� �����������, ������� ������� �� �������� ������� 
 * (��� ����� ��������� ������ - � ������� ������� �������-����������),
 * ������ ������������� �� �������
 */
abstract class TRMEventRepositories extends TRMDataObjectsContainerRepository
{
/**
 * @var string - ��� �������, ������� ������������ ������������ ��� ��������� �������
 */
protected $GetEventName = "";
/**
 * @var string - ��� �������, ������� ������������ ������������ ��� ���������� �������
 */
protected $UpdateEventName = "";
/**
 * @var string - ��� �������, ������� ������������ ������������ ��� �������� �������
 */
protected $DeleteEventName = "";
/**
 * @var array - ������ � ������������ ��� ������� ���.������� � ��������� �������-����������
 */
protected $RepositoriesArray = array();


/**
 * ��� �������� ����������� ���������� ������� ������ �������� ����� �������, 
 * ������� ����� �������������� ��� ����������� 3-� ������� - ���������/����������/��������
 * 
 * @param string $objectclassname - ��� ������ ��� ��������, �� ������� �������� ���� Repository
 * @param string $GetEventName - ��� �������, ������� ������������ ������������ ��� ��������� �������
 * @param string $UpdateEventName - ��� �������, ������� ������������ ������������ ��� ���������� �������
 * @param string $DeleteEventName - ��� �������, ������� ������������ ������������ ��� �������� �������
 */
public function __construct($objectclassname, $GetEventName, $UpdateEventName, $DeleteEventName)
{
    parent::__construct($objectclassname);
    $this->GetEventName = $GetEventName;
    $this->UpdateEventName = $UpdateEventName;
    $this->DeleteEventName = $DeleteEventName;
}

/**
 * ������ ������� ������, � ������� ����� �������� �����������, 
 * ������ ������, ������ �� ���������� � ��� ���������, ���� ���������� ������ ������� �� ��, ����� � �������� �������,
 * ��� ���� ���� ������ ���. ������������ ��������������� ��� ���.������� ������ ��������� �������!
 * ��������! 
 * �� ������ ���������� ������ 2018-08-20 ������ ��������� ��� �� � ���������� ���� ��� ������� ����������� ����,
 * ��������� �� ������� ������� ������ ������ � ��������� ����������� ������� ��� ���� ����������!!!
 * 
 * @param TRMDataObjectInterface $DataObjectsContainer - ������� ������, � ������� ����� �������� �����������,
 * ������ ���� ���� - TRMDataObjectsContainerInterface
 */
public function setObject(TRMDataObjectInterface $DataObjectsContainer)
{
    parent::setObject($DataObjectsContainer);
    // ��� ������������� ������f ������ ���� ������� ��� ����������� ��� �������� ��������,
    // ��� ��� ��� ����� ������������ �������, ������������ ������ ������������ � ���������, 
    // �������� ��� ���������� ����� ����������
    $this->setRepositoryArrayForContainer();
}

/**
 * ���� ������ ���. ������������ ��������������� ��� ���.������� ������ ��������� �������
 */
protected function setRepositoryArrayForContainer()
{
    $this->RepositoriesArray = array();

    foreach( $this->DataObjectsContainer as $DataObject )
    {
        // �������� ����������� ��� �������� �������...
        $rep = TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor($DataObject);
        // ������������� ������� ������ ��� ����������� �����������
        $rep->setObject($DataObject);
        // ��������� ���������� ������ � ������ (����������� ������ ������, ������� ������� ���������� ����� �� ����� �������������� � ����� ��������� ������� ������ ��� ��.)
        $this->RepositoriesArray[] = $rep;
    }
}

/**
 * ���������� ������� �������� �������, ���������������� ���������� �������� ��� ���������� ����,
 * � ��������� ���� �����������, ��� ������� ����� ������, 
 * ��������� ������ �� ���� ����� ����������� ������� TRMCommonEvent
 * 
 * @param string $fieldname - ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�., ����������� "="
 * 
 * @return TRMDataObjectsContainerInterface - ������-���������, ����������� ������� �� ���������
 */
public function getBy($fieldname, $value, $operator = "=")
{
    // � ������������ parent::getBy ���������� ������ �� ��������� ��� �������� ����� ���������� �������
    parent::getBy($fieldname, $value, $operator);

    if( !empty($this->GetEventName) )
    {
        // ����������� ���� ������������, ��� ������� ������� ������ �� ���������
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->GetEventName // ��� ������� (��� ���)
                    )
                );
    }

    return $this->DataObjectsContainer;
}

/**
 * ��������� ������ ������ � ��� ����������� � ��, 
 * ���� ��� ��������� �� ������� updateComplexProductDBEvent
 * 
 * @return boolean
 */
public function update()
{
    if( !$this->getMainRepository()->update() ) { return false; }

    if( !empty($this->UpdateEventName) )
    {
        // ����������� ���� ������������, ��� ��������  ������ ������ �� �� - ������� deleteComplexProductDBEvent
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->UpdateEventName // ��� ������� (��� ���)
                    )
                );
    }
    return true;
}

/**
 * ������� ������ ������ � ��������� ��� ����������� �� ����������,
 * �������� ������� deleteComplexProductDBEvent
 * @return boolean
 */
public function delete()
{
    if( !empty($this->DeleteEventName) )
    {
        // ����������� ���� ������������, ��� ������ ����� ������ �� �� - ������� deleteComplexProductDBEvent
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->DeleteEventName // ��� ������� (��� ���)
                    )
                );
    }

    return $this->getMainRepository()->delete();
}


} // TRMEventRepositoiesContainer