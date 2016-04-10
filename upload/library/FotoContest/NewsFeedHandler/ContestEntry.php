<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_NewsFeedHandler_ContestEntry extends XenForo_NewsFeedHandler_Abstract
{
  /**
   * @var FotoContest_Model_ContestEntry
   */
  protected $_contestEntryModel;

  /**
   * Fetches the requested entries from the database.
   *
   * @param array         $contentIds
   * @param XenForo_Model_NewsFeed  $model
   * @param array         $viewingUser
   *
   * @return  array
   */
  public function getContentByIds(array $contentIds, $model, array $viewingUser)
  {
    return $this->_getContestEntryModel()->getContestEntriesByIds($contentIds, array(
      'join'  => FotoContest_Model_ContestEntry::FETCH_USER
    ));
  }

  /**
   * Returns news feed item if the user can view it.
   *
   * @param array   $item
   * @param array   $content
   * @param array   $viewingUser
   *
   * @return  bool
   */
  public function canViewNewsFeedItem(array $item, $content, array $viewingUser)
  {
    return XenForo_Model::create('XenForo_Model_UserProfile')->canViewFullUserProfile($item, $null, $viewingUser);
  }

  /**
   * @return  FotoContest_Model_ContestEntry
   */
  protected function _getContestEntryModel()
  {
    if (!$this->_contestEntryModel)
    {
      $this->_contestEntryModel = XenForo_Model::create('FotoContest_Model_ContestEntry');
    }

    return $this->_contestEntryModel;
  }

}
