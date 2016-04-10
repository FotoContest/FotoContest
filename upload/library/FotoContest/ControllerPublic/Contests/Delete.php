<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_Delete extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();

    $contestModel = $this->_getContestModel();
    $contest      = $contestModel->prepareContest($this->_findContest());

    $hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
    $deleteType = ($hardDelete ? 'hard' : 'soft');

    $this->_assertCanDeleteContest($contest, $deleteType);

    if ($this->isConfirmedPost()) // delete the contest
    {
      $options = array(
        'reason' => $this->_input->filterSingle('reason', XenForo_Input::STRING)
      );

      $contestModel->deleteContest(
        $contest,
        $deleteType,
        $options
      );

      return $this->responseRedirect(
        XenForo_ControllerResponse_Redirect::SUCCESS,
        XenForo_Link::buildPublicLink('photo-contests')
      );
    }
    else // show a delete confirmation dialog
    {
      return $this->responseView(
        'FotoContest_ViewPublic_Contest_Delete',
        'lfc_contest_delete',
        array(
          'contest' => $contest,
          'canHardDelete' => $contestModel->canDeleteContest($contest, 'hard')
        )
      );
    }
  }
}
