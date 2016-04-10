<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Route_Base implements XenForo_Route_Interface
{
  public $routePrefix = null;
  public $primaryKey  = null;

  public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
  {
    $action = $router->resolveActionWithIntegerParam($routePath, $request, $this->primaryKey);
    $action = $router->resolveActionAsPageNumber($action, $request);
    $class  = 'list';

    if ($action || $routePath)
    {
      $action = str_replace(array('-'), ' ', $action);
      $class = $action ? $action : $routePath;
    }

    $primaryKey = $this->primaryKey;
    if (!$action && $request->$primaryKey)
    {
      $class = 'view';
    }

    $class = str_replace(' ', '', ucwords($class));
    return $router->getRouteMatch(
      $this->getRouteClass() .'_' . $class, 'Run', $this->routePrefix
    );
  }

  public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
  {
    if (isset($extraParams['page']))
    {
      if (strval($extraParams['page']) !== XenForo_Application::$integerSentinel && $extraParams['page'] <= 1)
      {
        unset($extraParams['page']);
      }
    }
    return XenForo_Link::buildBasicLinkWithIntegerParam(
      $outputPrefix, $action, $extension, $data, $this->primaryKey, 'title'
    );
  }

  public function getRouteClass(){
    return str_replace('Route', 'ControllerPublic', get_class($this));
  }

}
