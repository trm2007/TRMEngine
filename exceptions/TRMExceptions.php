<?php
namespace TRMEngine\Exceptions;

class TRMException extends \Exception {}

//выбрасывается при неверной авторизации
class AuthException extends TRMException {}

//выбрасывается конструктором, если объект не может быть создан
class ObjectCreateException extends TRMException {}

//выбрасывается если не указан Controller
class NoControllerException extends TRMException {}
//выбрасывается, если не указан Action
class NoActionException extends TRMException {}

/**
 * выбрасывается, если репозиторий не смог получить объект
 */
class TRMRepositoryGetObjectException extends TRMException {}

/**
 * выбрасывается при ошибке SQL-запроса
 */
class TRMSqlQueryException extends TRMException {}

/**
 * выбрасывается, если не наден маршрут
 */
class TRMExceptionPathNotFound extends TRMException
{
    public function __construct( $URL, $Code = 404 )
    {
        parent::__construct("Обращение по неверному адресу: {$URL}", $Code );
    }
}

/**
 * Класс исключения, которое выбрасывается, если не найден контроллер для текущего URI
 *
 * @author TRM
 */
class TRMExceptionControllerNotFound extends TRMException
{
    public function __construct( $ControllrrName, $Code = 404 )
    {
        parent::__construct("Не найден модуль [" . $ControllrrName . "]", $Code );
    }
} // TRMExceptionControllerNotFound

/**
 * Класс исключения, которое выбрасывается, если не найден Action в контроллере для текущего URI
 *
 * @author TRM
 */
class TRMExceptionActionNotFound extends TRMException
{
    public function __construct( $ControllrrName, $ActionName, $Param, $Code = 404 )
    {
        parent::__construct( "В модуле [" . $ControllrrName . "] не найден метод [" 
                            . $ActionName
                            . "]<br>   для обработки [" 
                            . $Param .  "]<br>",
                $Code );
    }
} // TRMExceptionControllerNotFound


class TRMMustStartOtherAction extends TRMException
{
    /**
     * @var string - имя функции-Action, которая должна быть запушена
     */
    protected $ActionName = "";
        public function __construct( $ActionName )
    {
            parent::__construct();
            $this->ActionName = $ActionName;
    }
    
    public function getActionName()
    {
        return $this->ActionName;
    }
} // TRMMustStartOtherAction