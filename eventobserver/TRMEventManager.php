<?php

namespace TRMEngine\EventObserver;

/**
 * класс EventManager для подписки и публикации событий
 * должен создаваться как Singleton объект
 * реализация интерфейса IObservable
 */
class TRMEventManager // extends TRMSingleton // implements IObservable
{
/**
 * @var array - массив добавленных наблюдателей для каждого события вида $Observers[EventType][array(IObserver, method)]
 */
protected $Observers = array();

/**
 * @var array - массив имен зарегистрированных-отслеживаемых событий
 */
protected $RegisteredEvents = array();

/**
 * добавляем обозревателя-наблюдателя
 * @param IObserver $o - объект обозревателя, который заинтересован в уведомлении о наступлении события
 * @param string $eventtype  - имя события, в котором заинтересован данный наблюдатель
 * @param string $handler - метод, который вызывается у объекта $o, когда наступает событие $eventtype, по умолчанию = handle
 * @return boolean - в случае успешного добавления Обозревателя возвращается true, 
 * иначе, если этот наблюдатель не можкт быть добавлен, или такго события нет, то вернется false
 */
public function addObserver($o, $eventtype, $handler = "handle")
{
   $ObserverMethod = array( "Object" => $o, "MethodName" => $handler );
    // если это обозреватель и метод обработки уже в массиве подписчиков на данное событие, то возвращаем true
    if( isset($this->Observers[$eventtype]) && is_array($this->Observers[$eventtype]) && in_array($ObserverMethod, $this->Observers[$eventtype]) )
    {
        return true;
    }
    // иначе добавляем в массив
    $this->Observers[$eventtype][] = $ObserverMethod;
    // если событие еще не в массиве отслеживаемых, добавляем его
    if( !in_array($eventtype, $this->RegisteredEvents) )
    {
        $this->RegisteredEvents[] = $eventtype;
    }

    return true;
}

/**
 * убираем обозревателя-наблюдателя
 * 
 * @param IObserver $o - удаляемый наблюдатель
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
 * оповещаем подписанных на событие обозревателей
 * 
 * @param TRMCommonEvent  $event - объект события, о котором необходимо оповестить подписавшихся обозревателей
 * @return boolean - если событие не зарегистрировано, то вернет false, иначе true
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