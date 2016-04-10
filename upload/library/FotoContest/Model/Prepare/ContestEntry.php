<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Model_Prepare_ContestEntry extends Xenforo_Model
{
  public function mergeWithContests(array $contestEntries)
  {
    $contestIds = array();
    foreach ($contestEntries as $contestEntry)
    {
      $contestIds[] = $contestEntry['photo_contest_id'];
    }

    $contestModel = $this->getModelFromCache('FotoContest_Model_Contest');
    $contests     = $contestModel->getContestsByIds($contestIds);

    foreach ($contestEntries as &$contestEntry)
    {
      $contestEntry['contest'] = $contests[$contestEntry['photo_contest_id']];
    }

    return $contestEntries;
  }

  public function removeRunningContestEntries(array $contestEntries)
  {
    foreach ($contestEntries as $key => $contestEntry) {
      if ($contestEntry['contest']['contest_closed'] === 0) {
        unset($contestEntries[$key]);
      }
    }

    return $contestEntries;
  }
}
