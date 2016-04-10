<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_AddContestEntry extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();

    $contest = $this->_findContest();
    $contest = $this->_getContestModel()->prepareContest($contest);
    $this->_assertContestEntriesAllowed($contest);

    $viewingUser = XenForo_Visitor::getInstance()->toArray();
    $this->_assertCanPostContestEntry($contest, $viewingUser);

    $constraints = $this->_getContestEntryAttachmentHandler()->getAttachmentConstraints();
    $constraints['count'] = $contest['max_images_per_entry'];

    $viewParams = array(
      'contest'               => $contest,
      'attachmentParams'      => $this->_getContestEntryAttachmentHandler()->getAttachmentParams(),
      'attachmentConstraints' => $constraints,
      'attachmentButtonKey'   => $contest['photo_contest_id']
    );

    return $this->responseView(
      'FotoContest_ViewPublic_ContestEntry_Add',
      'lfc_contest_entry_add',
      $viewParams
    );
  }
}
