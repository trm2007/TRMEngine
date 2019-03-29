<?php

namespace TRMEngine\Repository\Events;

/**
 * Используется для передачи события от репозитория контейнера к подписчикам,
 * в массиве Data объекта-событие могут быть указаны 
 * обрабатываемый контейнер и главный объект
 */
class TRMRepositoryEvents extends \TRMEngine\EventObserver\TRMCommonEvent
{
const MAIN_OBJECT_INDEX = "MainObject";
const CONTAINER_OBJECT_INDEX = "ContainerObject";
}
