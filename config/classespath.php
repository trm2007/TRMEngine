<?php
return array(
/**
 * TRMEngine\
 */
"TRMEngine\TRMErrorHandler" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/TRMErrorHandler.php",
"TRMEngine\TRMApplication" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/TRMApplication.php",
"TRMEngine\TRMDBObject" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/TRMDBObject.php",

"TRMEngine\Exceptions\TRMException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/exceptions/TRMExceptions.php",
"TRMEngine\Exceptions\TRMObjectCreateException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/exceptions/TRMExceptions.php",
"TRMEngine\Exceptions\TRMSqlQueryException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/exceptions/TRMExceptions.php",
"TRMEngine\Exceptions\TRMConfigFileException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/exceptions/TRMExceptions.php",
"TRMEngine\Exceptions\TRMConfigArrayException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/exceptions/TRMExceptions.php",


/**
 * TRMEngine\PathFinder
 */
"TRMEngine\PathFinder\TRMPathFinder" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pathfinder/TRMPathFinder.php",
"TRMEngine\PathFinder\TRMPathDispatcher" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pathfinder/TRMPathDispatcher.php",

"TRMEngine\PathFinder\Exceptions\TRMControllerNotFoundedException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pathfinder/exceptions/TRMPathFinderExceptions.php",
"TRMEngine\PathFinder\Exceptions\TRMActionNotFoundedException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pathfinder/exceptions/TRMPathFinderExceptions.php",
"TRMEngine\PathFinder\Exceptions\TRMPathNotFoundedException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pathfinder/exceptions/TRMPathFinderExceptions.php",

    
/** 
 * TRMEngine\View
 */
"TRMEngine\View\TRMView" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/view/TRMView.php",

    
/** 
 * TRMEngine\Controllers
 */
"TRMEngine\Controller\TRMController" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/controller/TRMController.php",
"TRMEngine\Controller\TRMLoginController" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/controller/TRMLoginController.php",

"TRMEngine\Controller\Exceptions\TRMNoControllerException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/controller/exceptions/TRMControllerExceptions.php",
"TRMEngine\Controller\Exceptions\TRMNoActionException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/controller/exceptions/TRMControllerExceptions.php",
"TRMEngine\Controller\Exceptions\TRMMustStartOtherActionException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/controller/exceptions/TRMControllerExceptions.php",


/** 
 * TRMEngine\DataObject\ 
 */
"TRMEngine\DataArray\Interfaces\InitializibleFromArray" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/interfaces/TRMDataArrayInterface.php",
"TRMEngine\DataArray\Interfaces\TRMDataArrayInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/interfaces/TRMDataArrayInterface.php",
"TRMEngine\DataObject\Interfaces\TRMDataObjectInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/interfaces/TRMDataObjectInterface.php",
"TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/interfaces/TRMDataObjectInterface.php",
"TRMEngine\DataObject\Interfaces\TRMParentedDataObjectInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/interfaces/TRMDataObjectInterface.php",
"TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/interfaces/TRMDataObjectsContainerInterface.php",
"TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/interfaces/TRMDataObjectsCollectionInterface.php",
"TRMEngine\DataObject\Interfaces\TRMParentedCollectionInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/interfaces/TRMDataObjectsCollectionInterface.php",

"TRMEngine\DataArray\TRMDataArray" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/TRMDataArray.php",
"TRMEngine\DataObject\TRMDataObject" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/TRMDataObject.php",
"TRMEngine\DataObject\TRMIdDataObject" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/TRMIdDataObject.php",
"TRMEngine\DataObject\TRMParentedDataObject" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/TRMParentedDataObject.php",
"TRMEngine\DataObject\TRMDataObjectsContainer" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/TRMDataObjectsContainer.php",
"TRMEngine\DataObject\TRMDataObjectsCollection" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/TRMDataObjectsCollection.php",
"TRMEngine\DataObject\TRMTypedCollection" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/TRMTypedCollection.php",
"TRMEngine\DataObject\TRMParentedCollection" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/TRMParentedCollection.php",

"TRMEngine\DataObject\Exceptions\TRMDataObjectException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectContainerException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectContainerNoMainException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongIndexException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongDependenceTypeException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongDependenceObjectException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongIndexException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongTypeException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dataobject/exceptions/TRMDataObjectExceptions.php",


/** 
 * TRMEngine\Repository\ 
 */
"TRMEngine\Repository\Interfaces\TRMRepositoryInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/interfaces/TRMRepositoryInterfaces.php",
"TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/interfaces/TRMRepositoryInterfaces.php",

"TRMEngine\Repository\TRMRepository" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/TRMRepository.php",
"TRMEngine\Repository\TRMIdDataObjectRepository" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/TRMIdDataObjectRepository.php",
"TRMEngine\Repository\TRMParentedDataObjectRepository" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/TRMParentedDataObjectRepository.php",
"TRMEngine\Repository\TRMObserverParentedDataObjectRepository" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/TRMObserverParentedDataObjectRepository.php",
"TRMEngine\Repository\TRMDataObjectsContainerRepository" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/TRMDataObjectsContainerRepository.php",
"TRMEngine\Repository\TRMRepositoryManager" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/TRMRepositoryManager.php",

"TRMEngine\Repository\Events\TRMRepositoryEvents" => "/repository/TRMRepositoryEvents.php",

"TRMEngine\Repository\Exceptions\TRMRepositoryGetObjectException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/exceptions/TRMRepositoryExceptions.php",
"TRMEngine\Repository\Exceptions\TRMRepositoryNoDataObjectException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/exceptions/TRMRepositoryExceptions.php",
"TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/repository/exceptions/TRMRepositoryExceptions.php",


/**
 * TRMEngine\DataMapper\
 */
"TRMEngine\DataMapper\Interfaces\TRMDataMapperInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datamapper/interfaces/TRMDataMapperInterface.php",

"TRMEngine\DataMapper\TRMFieldMapper" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datamapper/TRMFieldMapper.php",
"TRMEngine\DataMapper\TRMObjectMapper" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datamapper/TRMObjectMapper.php",
"TRMEngine\DataMapper\TRMSafetyFields" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datamapper/TRMSafetyFields.php",
"TRMEngine\DataMapper\TRMDataMapper" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datamapper/TRMDataMapper.php",

"TRMEngine\DataMapper\Exceptions\TRMDataMapperNotStringFieldNameException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptySafetyFieldsArrayException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperRelationException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datamapper/exceptions/TRMDataMapperExceptions.php",


/**
 * TRMEngine\DataSource\
 */
"TRMEngine\DataSource\Interfaces\TRMDataSourceInterface" =>  TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datasource/interfaces/TRMDataSourceInterface.php",

"TRMEngine\DataSource\TRMSqlDataSource" =>  TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datasource/TRMSqlDataSource.php",

"TRMEngine\DataSource\Exceptions\TRMDataSourceSQLInsertException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datasource/exceptions/TRMDataSourceExceptions.php",
"TRMEngine\DataSource\Exceptions\TRMDataSourceNoUpdatebleFieldsException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datasource/exceptions/TRMDataSourceExceptions.php",
"TRMEngine\DataSource\Exceptions\TRMDataSourceSQLEmptyTablesListException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datasource/exceptions/TRMDataSourceExceptions.php",
"TRMEngine\DataSource\Exceptions\TRMDataSourceSQLNoSafetyFieldsException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datasource/exceptions/TRMDataSourceExceptions.php",
"TRMEngine\DataSource\Exceptions\TRMDataSourceWrongTableSortException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/datasource/exceptions/TRMDataSourceExceptions.php",


/**
 * TRMEngine\DiContainer
 */
"TRMEngine\DiContainer\TRMDIContainer" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dicontainer/TRMDIContainer.php",

"TRMEngine\DiContainer\Exceptions\TRMDiCanNotCreateObjectException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dicontainer/exceptions/TRMDiContainerExceptions.php",
"TRMEngine\DiContainer\Exceptions\TRMDiClassNotFoundedException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dicontainer/exceptions/TRMDiContainerExceptions.php",
"TRMEngine\DiContainer\Exceptions\TRMDiNoDefaultArgsException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dicontainer/exceptions/TRMDiContainerExceptions.php",
"TRMEngine\DiContainer\Exceptions\TRMDiNotPublicConstructorException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/dicontainer/exceptions/TRMDiContainerExceptions.php",


/**
 * TRMEngine\PipeLine
 */
"TRMEngine\PipeLine\Interfaces\RequestHandlerInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pipeline/interfaces/TRMPipeLineInterface.php",
"TRMEngine\PipeLine\Interfaces\MiddlewareInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pipeline/interfaces/TRMPipeLineInterface.php",

"TRMEngine\PipeLine\TRMPipeLine" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pipeline/TRMPipeLine.php",
"TRMEngine\PipeLine\TRMNext" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pipeline/TRMNext.php",
"TRMEngine\PipeLine\TRMRequestHandlerMiddleware" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pipeline/TRMRequestHandlerMiddleware.php",
"TRMEngine\PipeLine\TRMCallableMiddleware" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pipeline/TRMCallableMiddleware.php",
"TRMEngine\PipeLine\TRMPathMiddlewareDecorator" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pipeline/TRMPathMiddlewareDecorator.php",

"TRMEngine\PipeLine\Exceptions\TRMMiddlewareBadResponseException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/pipeline/exceptions/TRMPipeLineExceptions.php",


/**
 * TRMEngine\Middlewares\
 */
"TRMEngine\Middlewares\TRMCookiesAuthMiddleware" =>  TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/middlewares/TRMCookiesAuthMiddleware.php",


/**
 * TRMEngine\EventObserver
 */
"TRMEngine\EventObserver\Interfaces\TRMEventInterface" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/eventobserver/interfaces/TRMEventInterface.php",

//"IObserver" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/eventobserver/iobserver.php",
//"IObservable" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/eventobserver/iobserver.php",
"TRMEngine\EventObserver\TRMCommonEvent" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/eventobserver/TRMCommonEvent.php",
"TRMEngine\EventObserver\TRMEventManager" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/eventobserver/TRMEventManager.php",


/**
 * TRMEngine\Helpers
 */    
"TRMEngine\Helpers\TRMLib" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/libs/TRMLib.php",
"TRMEngine\Helpers\TRMState" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/libs/TRMState.php",


/**
 * TRMEngine\File
 */
"TRMEngine\File\TRMFile" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/file/TRMFile.php",


/**
 * TRMEngine\Cookies
 */
"TRMEngine\Cookies\TRMAuthCookie" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/cookie/TRMAuthCookie.php",
"TRMEngine\Cookies\TRMCookie" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/cookie/TRMCookie.php",

"TRMEngine\Cookies\Exceptions\TRMAuthCookieException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/cookie/TRMCookieExceptions.php",
"TRMEngine\File\TRMStringsFile" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/file/TRMStringsFile.php",


/**
 * TRMEngine\XMLParser
 */
"TRMEngine\XMLParser\TRMXMLToSQLParser" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/libs/TRMXMLToSQLParser.php",


/**
 * TRMEngine\Cache\
 */
"TRMEngine\Cache\TRMCache" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/cache/TRMCache.php",


/**
 * TRMEngine\EMail\
 */
"TRMEngine\EMail\TRMEMail" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/email/TRMEMail.php",

"TRMEngine\EMail\Exceptions\TRMEMailExceptions" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/email/exceptions/TRMEMailExceptions.php",
"TRMEngine\EMail\Exceptions\TRMEMailSendingExceptions" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/email/exceptions/TRMEMailExceptions.php",
"TRMEngine\EMail\Exceptions\TRMEMailWrongRecepientExceptions" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/email/exceptions/TRMEMailExceptions.php",
"TRMEngine\EMail\Exceptions\TRMEMailWrongThemeExceptions" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/email/exceptions/TRMEMailExceptions.php",
"TRMEngine\EMail\Exceptions\TRMEMailWrongBodyExceptions" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/email/exceptions/TRMEMailExceptions.php",


/**
 * TRMEngine\Image\
 */
"TRMEngine\Image\TRMImage" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/image/TRMImage.php",

"TRMEngine\Image\Exceptions\TRMImageExceptions" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageNoDestImageException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongBMPException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongPNGException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongGIFException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongWBMPException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongJPEGException" => TRMEngine\TRMAutoLoader::$TRMENGINE_PATH . "/image/exceptions/TRMImageExceptions.php",


);