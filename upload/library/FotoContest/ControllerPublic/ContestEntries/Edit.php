<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_ContestEntries_Edit extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();

    $viewingUser  = XenForo_Visitor::getInstance()->toArray();
    $contestEntry = $this->_findContestEntry();

    $contestEntryModel = $this->_getContestEntryModel();
    $contestEntry      = $contestEntryModel->prepareContestEntry($contestEntry);
    $contest           = $this->_findContest($contestEntry['photo_contest_id']);
    $contest           = $this->_getContestModel()->prepareContest($contest);

    $this->_assertCanEditContestEntry($contestEntry, $contest, $viewingUser);

    $this->_assertContestEntriesAllowed($contest);

    $constraints = $this->_getContestEntryAttachmentHandler()->getAttachmentConstraints();
    $constraints['count'] = $contest['max_images_per_entry'];

    // Were only fetching attachments so can use XF attachment model
    $attachmentModel = $this->_getAttachmentModel();
    $attachments     = $attachmentModel->getAttachmentsByContentId(
      'lfc_entry', $contestEntry['photo_contest_entry_id']
    );

    $attachmentParams = $this->_getContestEntryAttachmentHandler()->getAttachmentParams(
      array('photo_contest_entry_id' => $contestEntry['photo_contest_entry_id'])
    );

    $attachmentParams['attachments'] = $attachmentModel->prepareAttachments($attachments);

    $viewParams = array(
      'contest'               => $contest,
      'contestEntry'          => $contestEntry,
      'attachmentParams'      => $attachmentParams,
      'attachmentConstraints' => $constraints,
      'attachmentButtonKey'   => $contest['photo_contest_id']
    );

    return $this->responseView(
      'FotoContest_ViewPublic_ContestEntry_Edit',
      'lfc_contest_entry_edit',
      $viewParams
    );
  }
}
