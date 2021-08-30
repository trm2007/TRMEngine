# TRMEngine

Небольшой фреймворк TRMEngine.

Пример использования:

```php
$GlobalRequest = Request::createFromGlobals();
/**
 * @var TRMDIContainer
 */
$DIC = new TRMDIContainer();

// добавляем в контейнер объект Symfony\Component\HttpFoundation\Request
$DIC->set( $GlobalRequest );

$app = new TRMApplication( new TRMPathDispatcher($DIC), $DIC );
// обработчик исключений на самом верхнем уровне
$app->pipe( new ExceptionHandlerMiddleware() );
// далее стартуем с добавления спец. заголовка
$app->pipe( new StartMiddleware() );
// начинаем отсчет времени выполнения скрипта
$app->pipe( new ProfilerMiddleware() );

// получаем отклик (response) выполнения приложения
$Response = $app->handle( $app->getDIContainer()->get(Request::class) );

$Response->send();

```
