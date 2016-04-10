<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_ContestEntries_Delete extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();

    $contestEntryModel = $this->_getContestEntryModel();
    $contestEntry      = $contestEntryModel->prepareContestEntry($this->_findContestEntry());
    $contest           = $this->_getContestModel()->getContestById($contestEntry['photo_contest_id']);

    $hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
    $deleteType = ($hardDelete ? 'hard' : 'soft');

    $this->_assertCanDeleteContestEntry($contestEntry, $deleteType);

    if ($this->isConfirmedPost()) // delete the contest entry
    {
      $options = array(
        'reason' => $this->_input->filterSingle('reason', XenForo_Input::STRING)
      );

      $contestEntryModel->deleteContestEntry(
        $contestEntry,
        $deleteType,
        $options
      );

      XenForo_Helper_Cookie::clearIdFromCookie(
        $contestEntry['photo_contest_entry_id'],
        'inlinemod_contestEntries'
      );

      return $this->responseRedirect(
        XenForo_ControllerResponse_Redirect::SUCCESS,
        XenForo_Link::buildPublicLink('photo-contests', $contest)
      );
    }
    else // show a delete confirmation dialog
    {
      return $this->responseView(
        'FotoContest_ViewPublic_ContestEntry_Delete',
        'lfc_contest_entry_delete',
        array(
          'contest'       => $contest,
          'contestEntry'  => $contestEntry,
          'canHardDelete' => $contestEntryModel->canDeleteContestEntry($contestEntry, 'hard')
        )
      );
    }
  }
}
