<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_Edit extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();

    $contest = $this->_getContestModel()->prepareContest($this->_findContest());
    $contest = $this->_getContestModel()->getAndMergeAttachmentIntoContest($contest);

    $this->_assertCanEditContest($contest);

    $attachmentParams = $this->_getContestAttachmentHandler()->getAttachmentParams();

    $attachmentParams['content_data'] = array(
      'content_type' => 'lfc',
      'photo_contest_id' => $contest['photo_contest_id']
    );

    $allPostUserGroupIds = explode(',', $contest['post_user_group_ids']);
    $allVoteUserGroupIds = explode(',', $contest['vote_user_group_ids']);

    $viewParams = array(
      'allPostUserGroups'        => in_array(-1, $allPostUserGroupIds),
      'selectedPostUserGroupIds' => $allPostUserGroupIds,
      'allVoteUserGroups'        => in_array(-1, $allVoteUserGroupIds),
      'selectedVoteUserGroupIds' => $allVoteUserGroupIds,
      'userGroups'               => $this->_getAllUserGroups(),
      'contest'                  => $contest,
      'attachmentParams'         => $attachmentParams,
      'attachmentConstraints'    => $this->_getContestAttachmentHandler()->getAttachmentConstraints()
    );

    return $this->responseView(
      'FotoContest_ViewPublic_Contest_Edit',
      'lfc_contest_edit',
      $viewParams
    );
  }
}
