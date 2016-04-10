<?php
/**
 * @package Luqman_FotoContest
 * @author Luqman
 */

class FotoContest_Shared_XFCP_DataWriter_Search extends XFCP_FotoContest_Shared_XFCP_DataWriter_Search
{
  public function _postSave()
  {
    parent::_postSave();

    $this->_searchIndexData();
  }

  public function _postDelete()
  {
    parent::_postDelete();

    $this->_searchDeleteFromIndex();
  }

  protected function _canIndexForSearch()
  {
    return true;
  }

  protected function _searchIndexData()
  {
    if ($this->_canIndexForSearch())
    {
      $this->_searchInsertOrUpdateIndex();
    } else {
      $this->__searchDeleteFromIndex();
    }
  }

  protected function _searchInsertOrUpdateIndex()
  {
    $dataHandler = $this->_getSearchDataHandler();
    $indexer = new XenForo_Search_Indexer();

    $dataHandler->insertIntoIndex($indexer, $this->getMergedData());
  }

  protected function _searchDeleteFromIndex()
  {
    $dataHandler = $this->_getSearchDataHandler();
    $indexer = new XenForo_Search_Indexer();

    $dataHandler->deleteFromIndex($indexer, $this->getMergedData());
  }

  protected function _getSearchDataHandler()
  {
    return $this->getModelFromCache('XenForo_Model_Search')->getSearchDataHandler(
      $this->getContentType()
    );
  }
}
