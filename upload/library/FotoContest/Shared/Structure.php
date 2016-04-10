<?php

abstract class FotoContest_Shared_Structure
{
  protected $_structure = array();

  abstract protected function _getStructure();

  public function __construct()
  {
    $this->_structure = $this->_getStructure();
  }

  public function getTableName()
  {
    return $this->_structure['table'];
  }

  public function getContentKeyName()
  {
    return $this->_structure['key'];
  }

  public function getContentType()
  {
    return $this->_structure['contentType'];
  }

  public function getSearchResultsTemplate()
  {
    return $this->_structure['searchResultsTemplate'];
  }

  public function getSearchFormClass()
  {
    return $this->_structure['searchFormClass'];
  }

  public function getSearchFormTemplate()
  {
    return $this->_structure['searchFormTemplate'];
  }

  public function getModelClass()
  {
    return $this->_structure['modelClass'];
  }

  public function getThumbnailRebuildPhrase()
  {
    return $this->_structure['thumbnailRebuildPhrase'];
  }

  public function getThumbnailSizes()
  {
    return array();
  }

  public function getPrepareConditions(&$sqlConditions, $conditions, $fetchOptions)
  {

  }
}
