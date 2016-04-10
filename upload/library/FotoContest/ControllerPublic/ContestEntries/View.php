<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_ContestEntries_View extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $viewingUser  = XenForo_Visitor::getInstance()->toArray();
    $permissions  = $viewingUser['permissions']['lfc'];
    $modAny       = $permissions['editAnyEntry'] && $permissions['deleteAnyEntry'];

    $contestEntry = $this->_findContestEntry(
      null,
      array(
        'join'       => FotoContest_Model_ContestEntry::FETCH_USER,
        'likeUserId' => $viewingUser['user_id']
      )
    );

    $contest           = $this->_findContest($contestEntry['photo_contest_id']);
    $contest           = $this->_getContestModel()->prepareContest($contest);

    $contestEntryModel = $this->_getContestEntryModel();
    $contestEntry      = $contestEntryModel->prepareContestEntry($contestEntry);
    $contestEntry      = $contestEntryModel->getAndMergeAttachmentsIntoContestEntry(
      $contestEntry
    );

    $this->getModelFromCache(
      'FotoContest_Model_Permission_Entry_CanLike'
    )->execute($contest, $contestEntry, $viewingUser);

    $this->_getContestEntryPermissionModel()->canViewLikesCountForEntry(
      $contestEntry,
      $contest,
      $viewingUser
    );

    $contestOwner       = $contest['user_id'] == $viewingUser['user_id'];
    $entryOwner         = $contestEntry['user_id'] == $viewingUser['user_id'];

    $contestEntryHide = $contest['hide_entries'];
    if ($contestOwner || $entryOwner || $modAny || $contest['isOpenToVotes'])
    {
      $contestEntryHide = 0;
    }

    $attachments = array_values($contestEntry['attachments']);

    $attachment = array();
    if ($attachments)
    {
      $attachment = $attachments[0];
    }

    $threadId = $contestEntry['thread_id'];
    $thread   = null;
    if ($threadId)
    {
      $thread = $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($threadId);
    }

    $viewParams = array(
      'contest'             => $contest,
      'contestEntry'        => $contestEntry,
      'canViewAttachments'  => true,
      'attachment'          => $attachment,
      'multipleEntryImages' => count($contestEntry['attachments']) > 1,
      'thread'              => $thread,
      'contestEntryHide'    => $contestEntryHide
    );

    return $this->responseView(
      'FotoContest_ViewPublic_ContestEntry_View',
      'lfc_contest_entry_view',
      $viewParams
    );
  }
}
