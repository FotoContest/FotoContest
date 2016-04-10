<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_View extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $viewingUser       = XenForo_Visitor::getInstance()->toArray();
    $contest           = $this->_findContest();
    $contest           = $this->_getContestModel()->prepareContest($contest);
    $contestEntryModel = $this->_getContestEntryModel();

    $permissions = $viewingUser['permissions']['lfc'];
    $modAny      = $permissions['editAnyEntry'] && $permissions['deleteAnyEntry'];

    $order = array(
      'order' => $contest['entry_order']
    );

    if ($contest['isClosedToVotes'])
    {
      $order = array(
        'order' => 'most_likes',
      );
    }

    $contestEntriesHide = $contest['hide_entries'];
    if ($contest['isOpenToVotes'] || $contest['user_id'] == $viewingUser['user_id'] || $modAny)
    {
      $contestEntriesHide = 0;
    }

    $filterByUserId = 0;
    $hasEntries = $contestEntryModel->countContestEntriesForContestAndUser(
      $contest, $viewingUser
    );

    if ($contestEntriesHide && $hasEntries)
    {
      $contestEntriesHide = 0;
      $filterByUserId = $viewingUser['user_id'];
    }

    $contestEntries = $contestEntryModel->getContestEntriesInContest(
      $contest['photo_contest_id'],
      array(
        'deleted'   => $viewingUser['permissions']['lfc']['viewDeleted'],
        'moderated' => $modAny || $contest['user_id'] == $viewingUser['user_id'],
        'user_id'   => $filterByUserId
      ),
      array(
        'join'       => FotoContest_Model_ContestEntry::FETCH_USER | FotoContest_Model_ContestEntry::FETCH_WINNER,
        'likeUserId' => $viewingUser['user_id'],
        'byUserId'   => $viewingUser['user_id']
      ) + $order
    );

    $contest        = $this->_getContestModel()->prepareContest($contest);
    $contestEntries = $contestEntryModel->getAndMergeAttachmentsIntoContestEntries($contestEntries);
    $contestEntries = $contestEntryModel->prepareContestEntries($contestEntries);

    $this->_getContestEntryPermissionModel()->canViewLikesCountForEntries(
      $contestEntries,
      $contest,
      $viewingUser
    );

    $this->getModelFromCache(
      'FotoContest_Model_Permission_Entry_CanLike'
    )->executeMulti($contest, $contestEntries, $viewingUser);

    $inlineModOptions = array();
    foreach ($contestEntries AS &$contestEntry)
    {
      $inlineModOptions += $this->getModelFromCache(
        'FotoContest_Model_Permission_ContestEntry'
      )->inlineModPermissions($contestEntry);
    }

    $viewParams = array(
      'contest'            => $contest,
      'contestEntries'     => $contestEntries,
      'contestEntriesHide' => $contestEntriesHide,
      'canViewAttachments' => true,
      'canPostEntry'       => $viewingUser['permissions']['lfc']['postEntry'],
      'inlineModOptions'   => $inlineModOptions
    );

    return $this->responseView(
      'FotoContest_ViewPublic_Contest_View',
      'lfc_contest_view',
      $viewParams
    );
  }
}
