<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_LikeHandler_ContestEntry extends XenForo_LikeHandler_Abstract
{
  public function incrementLikeCounter($contentId, array $latestLikes, $adjustmentAmount = 1)
  {
    $writer = XenForo_DataWriter::create('FotoContest_DataWriter_ContestEntry');
    $writer->setExistingData($contentId);
    $writer->set('likes', $writer->get('likes') + $adjustmentAmount);
    $writer->set('like_users', $latestLikes);
    $writer->save();
  }

  public function getContentData(array $contentIds, array $viewingUser)
  {
    $contestEntryModel = XenForo_Model::create('FotoContest_Model_ContestEntry');
    $contestEntries = $contestEntryModel->getContestEntriesByIds($contentIds);

    $prepare = XenForo_Model::create('FotoContest_Model_Prepare_ContestEntry');
    $contestEntries = $prepare->mergeWithContests($contestEntries);
    $contestEntries = $prepare->removeRunningContestEntries($contestEntries);

    return $contestEntries;
  }

  public function getListTemplateName()
  {
    return 'news_feed_item_lfc_entry_like';
  }
}
