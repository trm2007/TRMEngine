<?php

return array(
/**
 * TRMEngine\
 */
"TRMEngine\TRMErrorHandler" => __DIR__ . "/../TRMErrorHandler.php",
"TRMEngine\TRMApplication" => __DIR__ . "/../TRMApplication.php",
"TRMEngine\TRMDBObject" => __DIR__ . "/../TRMDBObject.php",

"TRMEngine\Exceptions\TRMException" => __DIR__ . "/../exceptions/TRMExceptions.php",
"TRMEngine\Exceptions\TRMObjectCreateException" => __DIR__ . "/../exceptions/TRMExceptions.php",
"TRMEngine\Exceptions\TRMSqlQueryException" => __DIR__ . "/../exceptions/TRMExceptions.php",
"TRMEngine\Exceptions\TRMConfigFileException" => __DIR__ . "/../exceptions/TRMExceptions.php",
"TRMEngine\Exceptions\TRMConfigArrayException" => __DIR__ . "/../exceptions/TRMExceptions.php",


/**
 * TRMEngine\PathFinder
 */
"TRMEngine\PathFinder\TRMPathFinder" => __DIR__ . "/../pathfinder/TRMPathFinder.php",
"TRMEngine\PathFinder\TRMPathDispatcher" => __DIR__ . "/../pathfinder/TRMPathDispatcher.php",

"TRMEngine\PathFinder\Exceptions\TRMControllerNotFoundedException" => __DIR__ . "/../pathfinder/exceptions/TRMPathFinderExceptions.php",
"TRMEngine\PathFinder\Exceptions\TRMActionNotFoundedException" => __DIR__ . "/../pathfinder/exceptions/TRMPathFinderExceptions.php",
"TRMEngine\PathFinder\Exceptions\TRMPathNotFoundedException" => __DIR__ . "/../pathfinder/exceptions/TRMPathFinderExceptions.php",

    
/** 
 * TRMEngine\View
 */
"TRMEngine\View\TRMView" => __DIR__ . "/../view/TRMView.php",

    
/** 
 * TRMEngine\Controllers
 */
"TRMEngine\Controller\TRMController" => __DIR__ . "/../controller/TRMController.php",
"TRMEngine\Controller\TRMLoginController" => __DIR__ . "/../controller/TRMLoginController.php",

"TRMEngine\Controller\Exceptions\TRMNoControllerException" => __DIR__ . "/../controller/exceptions/TRMControllerExceptions.php",
"TRMEngine\Controller\Exceptions\TRMNoActionException" => __DIR__ . "/../controller/exceptions/TRMControllerExceptions.php",
"TRMEngine\Controller\Exceptions\TRMMustStartOtherActionException" => __DIR__ . "/../controller/exceptions/TRMControllerExceptions.php",


/** 
 * TRMEngine\DataObject\ 
 */
"TRMEngine\DataArray\Interfaces\InitializibleFromArray" => __DIR__ . "/../dataobject/interfaces/TRMDataArrayInterface.php",
"TRMEngine\DataArray\Interfaces\TRMDataArrayInterface" => __DIR__ . "/../dataobject/interfaces/TRMDataArrayInterface.php",
"TRMEngine\DataObject\Interfaces\TRMDataObjectInterface" => __DIR__ . "/../dataobject/interfaces/TRMDataObjectInterface.php",
"TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface" => __DIR__ . "/../dataobject/interfaces/TRMDataObjectInterface.php",
"TRMEngine\DataObject\Interfaces\TRMParentedDataObjectInterface" => __DIR__ . "/../dataobject/interfaces/TRMDataObjectInterface.php",
"TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface" => __DIR__ . "/../dataobject/interfaces/TRMDataObjectsContainerInterface.php",
"TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface" => __DIR__ . "/../dataobject/interfaces/TRMDataObjectsCollectionInterface.php",
"TRMEngine\DataObject\Interfaces\TRMParentedCollectionInterface" => __DIR__ . "/../dataobject/interfaces/TRMDataObjectsCollectionInterface.php",

"TRMEngine\DataArray\TRMDataArray" => __DIR__ . "/../dataobject/TRMDataArray.php",
"TRMEngine\DataObject\TRMDataObject" => __DIR__ . "/../dataobject/TRMDataObject.php",
"TRMEngine\DataObject\TRMIdDataObject" => __DIR__ . "/../dataobject/TRMIdDataObject.php",
"TRMEngine\DataObject\TRMParentedDataObject" => __DIR__ . "/../dataobject/TRMParentedDataObject.php",
"TRMEngine\DataObject\TRMDataObjectsContainer" => __DIR__ . "/../dataobject/TRMDataObjectsContainer.php",
"TRMEngine\DataObject\TRMDataObjectsCollection" => __DIR__ . "/../dataobject/TRMDataObjectsCollection.php",
"TRMEngine\DataObject\TRMTypedCollection" => __DIR__ . "/../dataobject/TRMTypedCollection.php",
"TRMEngine\DataObject\TRMParentedCollection" => __DIR__ . "/../dataobject/TRMParentedCollection.php",

"TRMEngine\DataObject\Exceptions\TRMDataObjectException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectContainerException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectContainerNoMainException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongIndexException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongDependenceTypeException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongDependenceObjectException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongIndexException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",
"TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongTypeException" => __DIR__ . "/../dataobject/exceptions/TRMDataObjectExceptions.php",


/** 
 * TRMEngine\Repository\ 
 */
"TRMEngine\Repository\Interfaces\TRMRepositoryInterface" => __DIR__ . "/../repository/interfaces/TRMRepositoryInterfaces.php",
"TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface" => __DIR__ . "/../repository/interfaces/TRMRepositoryInterfaces.php",

"TRMEngine\Repository\TRMRepository" => __DIR__ . "/../repository/TRMRepository.php",
"TRMEngine\Repository\TRMIdDataObjectRepository" => __DIR__ . "/../repository/TRMIdDataObjectRepository.php",
"TRMEngine\Repository\TRMParentedDataObjectRepository" => __DIR__ . "/../repository/TRMParentedDataObjectRepository.php",
"TRMEngine\Repository\TRMObserverParentedDataObjectRepository" => __DIR__ . "/../repository/TRMObserverParentedDataObjectRepository.php",
"TRMEngine\Repository\TRMDataObjectsContainerRepository" => __DIR__ . "/../repository/TRMDataObjectsContainerRepository.php",
"TRMEngine\Repository\TRMRepositoryManager" => __DIR__ . "/../repository/TRMRepositoryManager.php",

"TRMEngine\Repository\Events\TRMRepositoryEvents" => "/repository/TRMRepositoryEvents.php",

"TRMEngine\Repository\Exceptions\TRMRepositoryGetObjectException" => __DIR__ . "/../repository/exceptions/TRMRepositoryExceptions.php",
"TRMEngine\Repository\Exceptions\TRMRepositoryNoDataObjectException" => __DIR__ . "/../repository/exceptions/TRMRepositoryExceptions.php",
"TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException" => __DIR__ . "/../repository/exceptions/TRMRepositoryExceptions.php",


/**
 * TRMEngine\DataMapper\
 */
"TRMEngine\DataMapper\Interfaces\TRMDataMapperInterface" => __DIR__ . "/../datamapper/interfaces/TRMDataMapperInterface.php",
"TRMEngine\DataMapper\Interfaces\TRMParentedDataMapperInterface" => __DIR__ . "/../datamapper/interfaces/TRMDataMapperInterface.php",

"TRMEngine\DataMapper\TRMFieldMapper" => __DIR__ . "/../datamapper/TRMFieldMapper.php",
"TRMEngine\DataMapper\TRMObjectMapper" => __DIR__ . "/../datamapper/TRMObjectMapper.php",
"TRMEngine\DataMapper\TRMDataMapper" => __DIR__ . "/../datamapper/TRMDataMapper.php",
"TRMEngine\DataMapper\TRMParentedDataMapper" => __DIR__ . "/../datamapper/TRMParentedDataMapper.php",
"TRMEngine\DataMapper\TRMSafetyFields" => __DIR__ . "/../datamapper/TRMSafetyFields.php",

"TRMEngine\DataMapper\Exceptions\TRMDataMapperExceptions" => __DIR__ . "/../datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperNotStringFieldNameException" => __DIR__ . "/../datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptySafetyFieldsArrayException" => __DIR__ . "/../datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperRelationException" => __DIR__ . "/../datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptyMainObjectException" => __DIR__ . "/../datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperTooManyMainObjectException" => __DIR__ . "/../datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptyIdFieldException" => __DIR__ . "/../datamapper/exceptions/TRMDataMapperExceptions.php",
"TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptyParentIdFieldException" => __DIR__ . "/../datamapper/exceptions/TRMDataMapperExceptions.php",


/**
 * TRMEngine\DataSource\
 */
"TRMEngine\DataSource\Interfaces\TRMDataSourceInterface" =>  __DIR__ . "/../datasource/interfaces/TRMDataSourceInterface.php",

"TRMEngine\DataSource\TRMSqlDataSource" =>  __DIR__ . "/../datasource/TRMSqlDataSource.php",

"TRMEngine\DataSource\Exceptions\TRMDataSourceSQLInsertException" => __DIR__ . "/../datasource/exceptions/TRMDataSourceExceptions.php",
"TRMEngine\DataSource\Exceptions\TRMDataSourceNoUpdatebleFieldsException" => __DIR__ . "/../datasource/exceptions/TRMDataSourceExceptions.php",
"TRMEngine\DataSource\Exceptions\TRMDataSourceSQLEmptyTablesListException" => __DIR__ . "/../datasource/exceptions/TRMDataSourceExceptions.php",
"TRMEngine\DataSource\Exceptions\TRMDataSourceSQLNoSafetyFieldsException" => __DIR__ . "/../datasource/exceptions/TRMDataSourceExceptions.php",
"TRMEngine\DataSource\Exceptions\TRMDataSourceWrongTableSortException" => __DIR__ . "/../datasource/exceptions/TRMDataSourceExceptions.php",


/**
 * TRMEngine\DiContainer
 */
"TRMEngine\DiContainer\Interfaces\TRMSimpleFactoryInterface" =>  __DIR__ . "/../dicontainer/interfaces/TRMFactoryInterface.php",
"TRMEngine\DiContainer\Interfaces\TRMStaticFactoryInterface" =>  __DIR__ . "/../dicontainer/interfaces/TRMFactoryInterface.php",

"TRMEngine\DiContainer\TRMStaticFactory" =>  __DIR__ . "/../dicontainer/TRMStaticFactory.php",
"TRMEngine\DiContainer\TRMDIContainer" => __DIR__ . "/../dicontainer/TRMDIContainer.php",

"TRMEngine\DiContainer\Exceptions\TRMDiExceptions" => __DIR__ . "/../dicontainer/exceptions/TRMDiContainerExceptions.php",
"TRMEngine\DiContainer\Exceptions\TRMDiCanNotCreateObjectException" => __DIR__ . "/../dicontainer/exceptions/TRMDiContainerExceptions.php",
"TRMEngine\DiContainer\Exceptions\TRMDiClassNotFoundedException" => __DIR__ . "/../dicontainer/exceptions/TRMDiContainerExceptions.php",
"TRMEngine\DiContainer\Exceptions\TRMDiNoDefaultArgsException" => __DIR__ . "/../dicontainer/exceptions/TRMDiContainerExceptions.php",
"TRMEngine\DiContainer\Exceptions\TRMDiNotPublicConstructorException" => __DIR__ . "/../dicontainer/exceptions/TRMDiContainerExceptions.php",


/**
 * TRMEngine\PipeLine
 */
"TRMEngine\PipeLine\Interfaces\RequestHandlerInterface" => __DIR__ . "/../pipeline/interfaces/TRMPipeLineInterface.php",
"TRMEngine\PipeLine\Interfaces\MiddlewareInterface" => __DIR__ . "/../pipeline/interfaces/TRMPipeLineInterface.php",

"TRMEngine\PipeLine\TRMPipeLine" => __DIR__ . "/../pipeline/TRMPipeLine.php",
"TRMEngine\PipeLine\TRMNext" => __DIR__ . "/../pipeline/TRMNext.php",
"TRMEngine\PipeLine\TRMRequestHandlerMiddleware" => __DIR__ . "/../pipeline/TRMRequestHandlerMiddleware.php",
"TRMEngine\PipeLine\TRMCallableMiddleware" => __DIR__ . "/../pipeline/TRMCallableMiddleware.php",
"TRMEngine\PipeLine\TRMPathMiddlewareDecorator" => __DIR__ . "/../pipeline/TRMPathMiddlewareDecorator.php",
"TRMEngine\PipeLine\TRMNoPathMiddlewareDecorator" => __DIR__ . "/../pipeline/TRMNoPathMiddlewareDecorator.php",
    
"TRMEngine\PipeLine\Exceptions\TRMMiddlewareBadResponseException" => __DIR__ . "/../pipeline/exceptions/TRMPipeLineExceptions.php",


/**
 * TRMEngine\Middlewares\
 */
"TRMEngine\Middlewares\TRMCookiesAuthMiddleware" =>  __DIR__ . "/../middlewares/TRMCookiesAuthMiddleware.php",


/**
 * TRMEngine\EventObserver
 */
"TRMEngine\EventObserver\Interfaces\TRMEventInterface" => __DIR__ . "/../eventobserver/interfaces/TRMEventInterface.php",

//"IObserver" => __DIR__ . "/../eventobserver/iobserver.php",
//"IObservable" => __DIR__ . "/../eventobserver/iobserver.php",
"TRMEngine\EventObserver\TRMCommonEvent" => __DIR__ . "/../eventobserver/TRMCommonEvent.php",
"TRMEngine\EventObserver\TRMEventManager" => __DIR__ . "/../eventobserver/TRMEventManager.php",


/**
 * TRMEngine\Helpers
 */    
"TRMEngine\Helpers\TRMLib" => __DIR__ . "/../libs/TRMLib.php",
"TRMEngine\Helpers\TRMState" => __DIR__ . "/../libs/TRMState.php",


/**
 * TRMEngine\File
 */
"TRMEngine\File\TRMFile" => __DIR__ . "/../file/TRMFile.php",


/**
 * TRMEngine\Cookies
 */
"TRMEngine\Cookies\TRMAuthCookie" => __DIR__ . "/../cookie/TRMAuthCookie.php",
"TRMEngine\Cookies\TRMCookie" => __DIR__ . "/../cookie/TRMCookie.php",

"TRMEngine\Cookies\Exceptions\TRMAuthCookieException" => __DIR__ . "/../cookie/exceptions/TRMCookieExceptions.php",
"TRMEngine\File\TRMStringsFile" => __DIR__ . "/../file/TRMStringsFile.php",


/**
 * TRMEngine\XMLParser
 */
"TRMEngine\XMLParser\TRMXMLToSQLParser" => __DIR__ . "/../libs/TRMXMLToSQLParser.php",


/**
 * TRMEngine\Cache\
 */
"TRMEngine\Cache\TRMCache" => __DIR__ . "/../cache/TRMCache.php",


/**
 * TRMEngine\EMail\
 */
"TRMEngine\EMail\TRMEMail" => __DIR__ . "/../email/TRMEMail.php",

"TRMEngine\EMail\Exceptions\TRMEMailExceptions" => __DIR__ . "/../email/exceptions/TRMEMailExceptions.php",
"TRMEngine\EMail\Exceptions\TRMEMailSendingExceptions" => __DIR__ . "/../email/exceptions/TRMEMailExceptions.php",
"TRMEngine\EMail\Exceptions\TRMEMailWrongRecepientExceptions" => __DIR__ . "/../email/exceptions/TRMEMailExceptions.php",
"TRMEngine\EMail\Exceptions\TRMEMailWrongThemeExceptions" => __DIR__ . "/../email/exceptions/TRMEMailExceptions.php",
"TRMEngine\EMail\Exceptions\TRMEMailWrongBodyExceptions" => __DIR__ . "/../email/exceptions/TRMEMailExceptions.php",


/**
 * TRMEngine\Image\
 */
"TRMEngine\Image\TRMImage" => __DIR__ . "/../image/TRMImage.php",

"TRMEngine\Image\Exceptions\TRMImageExceptions" => __DIR__ . "/../image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageNoDestImageException" => __DIR__ . "/../image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongBMPException" => __DIR__ . "/../image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongPNGException" => __DIR__ . "/../image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongGIFException" => __DIR__ . "/../image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongWBMPException" => __DIR__ . "/../image/exceptions/TRMImageExceptions.php",
"TRMEngine\Image\Exceptions\TRMImageWrongJPEGException" => __DIR__ . "/../image/exceptions/TRMImageExceptions.php",


);


