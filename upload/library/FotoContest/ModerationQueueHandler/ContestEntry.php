<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ModerationQueueHandler_ContestEntry extends XenForo_ModerationQueueHandler_Abstract
{
  public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
  {
    $output            = array();
    $contestEntryModel = XenForo_Model::create('FotoContest_Model_ContestEntry');
    $contestEntries    = $contestEntryModel->getContestEntriesByIds($contentIds);
    $permissions       = $viewingUser['permissions']['lfc'];
    $prepare           = XenForo_Model::create(
      'FotoContest_Model_Prepare_ContestEntry'
    );

    $contestEntries = $prepare->mergeWithContests($contestEntries);

    foreach ($contestEntries AS $contestEntry)
    {
      $modAny  = $permissions['editAnyEntry'] && $permissions['deleteAnyEntry'];
      $contest = $contestEntry['contest'];

      if ($modAny || $contest['user_id'] === $viewingUser['user_id'])
      {
        $output[$contestEntry['photo_contest_entry_id']] = array(
          'message' => 'No message...',
          'user' => array(
            'user_id' => $contestEntry['user_id'],
            'username' => $contestEntry['username']
          ),
          'title' => $contestEntry['title'],
          'link' => XenForo_Link::buildPublicLink('photo-contest-entries', $contestEntry),
          'contentTypeTitle' => new XenForo_Phrase('lfc_entry'),
          'titleEdit' => false
        );
      }
    }

    return $output;
  }

  /**
   * Approves the specified moderation queue entry.
   *
   * @see XenForo_ModerationQueueHandler_Abstract::approveModerationQueueEntry()
   */
  public function approveModerationQueueEntry($contentId, $message, $title)
  {
    $dw = XenForo_DataWriter::create(
      'FotoContest_DataWriter_ContestEntry',
      XenForo_DataWriter::ERROR_SILENT
    );

    $dw->setExistingData($contentId);
    $dw->set('entry_state', 'visible');

    return $dw->save();
  }

  /**
   * Deletes the specified moderation queue entry.
   *
   * @see XenForo_ModerationQueueHandler_Abstract::deleteModerationQueueEntry()
   */
  public function deleteModerationQueueEntry($contentId)
  {
    $dw = XenForo_DataWriter::create(
      'FotoContest_DataWriter_ContestEntry',
      XenForo_DataWriter::ERROR_SILENT
    );

    $dw->setExistingData($contentId);
    $dw->set('entry_state', 'deleted');

    return $dw->save();
  }
}
