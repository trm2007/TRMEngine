<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

/**
 * ��������� ��������� Repository
 */
class TRMRepositoryManager
{
/**
 * @var array - ������ ������������ ����� �������� (���������) �� �������� ������� (Repository)
 */
protected $RepositoryNameArray = array();


/**
 * ������������� ������ ����������� ����� �������� (���������) �� �������� ������� (Repository) -
 * array( $objectclassname => $repositoryclassname, ... )
 * 
 * @param array $arr - ������ ����������� ����� �������� (���������) �� �������� ������� (Repository)
 */
public function setRepositoryNameArray(array $arr)
{
    foreach($arr as $objectclassname => $repositoryclassname)
    {
        $this->addRepositoryName($objectclassname, $repositoryclassname);
    }
}

/**
 * ��������� �������������� ������ ����������� ��� �������� ������ $objectclassname,
 * ���� ��� $objectclassname ����� ��� ���������� ����������, �� ����� ������ � ���������� �����!
 * 
 * @param string $objectclassname - ��� ������ ��������, ��� �������� ������������� �����������
 * @param string $repositoryclassname - ��� ������ ������� Repository
 */
public function addRepositoryName($objectclassname, $repositoryclassname)
{
    if( !class_exists($repositoryclassname) )
    {
        throw new \Exception( "�� ������ ����� ����������� {$repositoryclassname} ��� �������� ��� {$objectclassname}!");
    }
    $this->RepositoryNameArray[$objectclassname] = $repositoryclassname;
}

/**
 * ���������� ������ Repository ��� �������� ��� $objectclassname
 * 
 * @param string $objectclassname - ��� ���� ��������, ��� ������� ����� �������� ������ ���������
 * @return TRMRepositoryInterface
 */
public function getRepository($objectclassname)
{
    if( !$objectclassname )
    {
        throw new \Exception("����������� ������ ��� �������� {$objectclassname}!");
    }
    if( !isset($this->RepositoryNameArray[$objectclassname]) )
    {
        if( !class_exists($objectclassname."Repository") )
        {
            ob_start();
            \TRMEngine\Helpers\TRMLib::ap($this->RepositoryNameArray);
            $debinf = ob_get_clean();
            throw new \Exception( $debinf . "�� ������ ����� ����������� ��� �������� ��� {$objectclassname}!");
        }
        $this->RepositoryNameArray[$objectclassname] = $objectclassname."Repository";
    }
    return TRMDIContainer::getStatic($this->RepositoryNameArray[$objectclassname], array($objectclassname));
}

/**
 * ���������� ������ Repository ��� ������� ������ $object,
 * ��� ���� ������������� ������ $object ��� ������ ������ ��� �����������
 * 
 * @param TRMDataObjectInterface $object - ������, ��� �������� ����� �������� ������ ���������
 * @return TRMRepositoryInterface
 */
public function getRepositoryFor(TRMDataObjectInterface $object)
{
    $r = $this->getRepository( get_class($object) );
    $r->setObject($object);
    return $r;
}


} // TRMRepositoryManager