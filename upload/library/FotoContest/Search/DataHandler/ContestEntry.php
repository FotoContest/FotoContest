<?php

class FotoContest_Search_DataHandler_ContestEntry extends FotoContest_Shared_Search_DataHandler_Abstract
{
  protected $_contentModel    = 'FotoContest_Model_ContestEntry';
  protected $_permissionModel = 'FotoContest_Model_Permission_ContestEntry';
  protected $_structureModel  = 'FotoContest_Structure_ContestEntry';

  public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
  {
    $entries = $this->_getContentModel()->getByIds($ids);
    $entries = $this->_getContentModel()->getAndMergeAttachmentsIntoContestEntries(
      $entries
    );

    $entries = $this->getModelFromCache(
      'FotoContest_Model_Prepare_ContestEntry'
    )->mergeWithContests($entries);

    return $entries;
  }

  public function canViewResult(array $result, array $viewingUser)
  {
    return $this->_getPermissionModel()->canViewResult($result, $viewingUser);
  }
}
