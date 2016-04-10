<?php

class FotoContest_Shared_Search_DataHandler_Abstract extends XenForo_Search_DataHandler_Abstract
{
  protected $_modelCache = array();

  protected function _getContentType()
  {
    return $this->_getStructureModel()->getContentType();
  }

  protected function _getContentKeyName()
  {
    return $this->_getStructureModel()->getContentKeyName();
  }

  protected function _getSearchResultsTemplate()
  {
    return $this->_getStructureModel()->getSearchResultsTemplate();
  }

  protected function _getPrimaryKey($data)
  {
    return $data[$this->_getContentKeyName()];
  }

  protected function _getTitle($data)
  {
    return $data['title'];
  }

  protected function _getDescription($data)
  {
    return $data['description'];
  }

  protected function _getMetaData($data)
  {
    return array();
  }

  protected function _insertIntoIndex(
    XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
  {
    $indexer->insertIntoIndex(
      $this->_getContentType(),
      $this->_getPrimaryKey($data),
      $this->_getTitle($data),
      $this->_getDescription($data),
      $data['updated_at'],
      $data['user_id'],
      $data['thread_id'],
      $this->_getMetaData($data)
    );
  }

  protected function _updateIndex(
    XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
  {
    $indexer->updateIndex(
      $this->_getContentType(),
      $this->_getPrimaryKey($data),
      $fieldUpdates
    );
  }

  protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
  {
    $contentIdsToDelete = array();
    $contentIdKey = $this->_getContentKeyName();

    foreach ($dataList as $data)
    {
      $contentIdsToDelete[] = $data[$contentIdKey];
    }

    $indexer->deleteFromIndex(
      $this->_getContentType(),
      $contentIdsToDelete
    );
  }

  public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
  {
    $contentIds = $this->_getContentModel()->getIdsInRange(
      $lastId, $batchSize
    );

    if (!$contentIds)
    {
      return false;
    }

    $this->quickIndex($indexer, $contentIds);

    return max($contentIds);
  }

  public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
  {
    $content = $this->_getContentModel()->getByIds($contentIds);
    $contentIds = array();

    foreach ($content as $dataId => $data)
    {
      $contentIds[] = $dataId;
      $this->insertIntoIndex($indexer, $data);
    }

    return $contentIds;
  }

  public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
  {
    return $this->_getContentModel()->getByIds($ids);
  }

  public function canViewResult(array $result, array $viewingUser)
  {
    return true;
  }

  public function prepareResult(array $result, array $viewingUser)
  {

    return $result;
  }

  public function getResultDate(array $result)
  {
    return $result['updated_at'];
  }

  public function renderResult(XenForo_View $view, array $result, array $search)
  {
    return $view->createTemplateObject(
      $this->_getSearchResultsTemplate(),
      $this->_getSearchResultsData(
        $result, $search
      )
    );
  }

  protected function _getSearchResultsData($result, $search)
  {
    return array(
      'item'   => $result,
      'search' => $search,
    );
  }

  public function getSearchContentTypes()
  {
    return array($this->_getContentType());
  }

  public function getSearchFormControllerResponse(
    XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, array $viewParams)
  {
    $structure = $this->_getStructureModel();

    return $controller->responseView(
      $structure->getSearchFormClass(),
      $structure->getSearchFormTemplate(),
      $viewParams
    );
  }

  protected function _getStructureModel()
  {
    return $this->getModelFromCache($this->_structureModel);
  }

  protected function _getContentModel()
  {
    return $this->getModelFromCache($this->_contentModel);
  }

  protected function _getPermissionModel()
  {
    return $this->getModelFromCache($this->_permissionModel);
  }

  public function getModelFromCache($class)
  {
    if (!isset($this->_modelCache[$class]))
    {
      $this->_modelCache[$class] = XenForo_Model::create($class);
    }

    return $this->_modelCache[$class];
  }
}
