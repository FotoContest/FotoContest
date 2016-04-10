<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_AlertHandler_ContestEntry extends XenForo_AlertHandler_Abstract
{
  public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
  {
    $contestEntryModel = $model->getModelFromCache('FotoContest_Model_ContestEntry');
    $contestEntries = $contestEntryModel->getContestEntriesByIds($contentIds);

    $prepare =  $model->getModelFromCache('FotoContest_Model_Prepare_ContestEntry');
    $contestEntries = $prepare->mergeWithContests($contestEntries);
    $contestEntries = $prepare->removeRunningContestEntries($contestEntries);

    return $contestEntries;
  }

  protected function _getDefaultTemplateTitle($contentType, $action)
  {
    return 'lfc_contest_entry_alert_' . $action;
  }
}
