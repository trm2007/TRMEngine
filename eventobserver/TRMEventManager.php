<?php

namespace TRMEngine\EventObserver;

/**
 * ����� EventManager ��� �������� � ���������� �������
 * ������ ����������� ��� Singleton ������
 * ���������� ���������� IObservable
 */
class TRMEventManager // extends TRMSingleton // implements IObservable
{
/**
 * @var array - ������ ����������� ������������ ��� ������� ������� ���� $Observers[EventType][array(IObserver, method)]
 */
protected $Observers = array();

/**
 * @var array - ������ ���� ������������������-������������� �������
 */
protected $RegisteredEvents = array();

/**
 * ��������� ������������-�����������
 * @param IObserver $o - ������ ������������, ������� ������������� � ����������� � ����������� �������
 * @param string $eventtype  - ��� �������, � ������� ������������� ������ �����������
 * @param string $handler - �����, ������� ���������� � ������� $o, ����� ��������� ������� $eventtype, �� ��������� = handle
 * @return boolean - � ������ ��������� ���������� ������������ ������������ true, 
 * �����, ���� ���� ����������� �� ����� ���� ��������, ��� ����� ������� ���, �� �������� false
 */
public function addObserver($o, $eventtype, $handler = "handle")
{
   $ObserverMethod = array( "Object" => $o, "MethodName" => $handler );
    // ���� ��� ������������ � ����� ��������� ��� � ������� ����������� �� ������ �������, �� ���������� true
    if( isset($this->Observers[$eventtype]) && is_array($this->Observers[$eventtype]) && in_array($ObserverMethod, $this->Observers[$eventtype]) )
    {
        return true;
    }
    // ����� ��������� � ������
    $this->Observers[$eventtype][] = $ObserverMethod;
    // ���� ������� ��� �� � ������� �������������, ��������� ���
    if( !in_array($eventtype, $this->RegisteredEvents) )
    {
        $this->RegisteredEvents[] = $eventtype;
    }

    return true;
}

/**
 * ������� ������������-�����������
 * 
 * @param IObserver $o - ��������� �����������
 */
public function removeObserver($o)
{
    if(!is_array($this->Observers) )
    {
        return false;
    }
    foreach( $this->Observers as $EventKey => $EventObservers )
    {
        if(is_array($EventObservers) )
        {
            foreach($EventObservers as $k => $ObserverItem)
            {
                if( $ObserverItem["Object"] == $o )
                {
                    unset( $this->Observers[$EventKey][$k] );
                }
            }
        }
    }
    return true;
}

/**
 * ��������� ����������� �� ������� �������������
 * 
 * @param TRMCommonEvent  $event - ������ �������, � ������� ���������� ���������� ������������� �������������
 * @return boolean - ���� ������� �� ����������������, �� ������ false, ����� true
 */
public function notifyObservers(TRMCommonEvent  $event)
{
    $EventType = $event->getType();
    if( !in_array($EventType, $this->RegisteredEvents) )
    {
        return false;
    }
    if(is_array($this->Observers[$EventType]) )
    {
        foreach($this->Observers[$EventType] as $Observer)
        {
            $Observer["Object"]->{$Observer["MethodName"]}($event);
        }
    }
    return true;
}

} // TRMEventManager