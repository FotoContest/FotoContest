<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_Add extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $contest = array(
      'posting_opens_on'     => XenForo_Application::$time,
      'max_entry_count'      => 1,
      'max_votes_count'      => 1,
      'max_winners_count'    => 1,
      'max_images_per_entry' => 1,
      'hide_entries'         => 1,
      'is_featured'          => 0
    );

    $this->_assertCanPostContest();

    return $this->responseView(
      'FotoContest_ViewPublic_Contest_Add',
      'lfc_contest_add',
      array(
        'allPostUserGroups'        => true,
        'selectedPostUserGroupIds' => array(),
        'allVoteUserGroups'        => true,
        'selectedVoteUserGroupIds' => array(),
        'userGroups'               => $this->_getAllUserGroups(),
        'contest'                  => $contest,
        'attachmentParams'         => $this->_getContestAttachmentHandler()->getAttachmentParams(),
        'attachmentConstraints'    => $this->_getContestAttachmentHandler()->getAttachmentConstraints()
      )
    );
  }
}
