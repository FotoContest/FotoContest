<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Deferred_Contest extends XenForo_Deferred_Abstract
{
  public function execute(array $deferred, array $data, $targetRunTime, &$status)
  {
    $contestEntryModel = XenForo_Model::create('FotoContest_Model_ContestEntry');
    $contestEntries    = $contestEntryModel->getContestEntriesInContest(
      $data['photo_contest_id']
    );

    foreach ($contestEntries as $contestEntry)
    {
      $dw = XenForo_DataWriter::create(
        'FotoContest_DataWriter_ContestEntry',
        XenForo_DataWriter::ERROR_SILENT
      );

      if ($dw->setExistingData($contestEntry))
      {
        $dw->delete();
      }
    }

    return false;
  }

  public function canCancel()
  {
    return true;
  }
}
