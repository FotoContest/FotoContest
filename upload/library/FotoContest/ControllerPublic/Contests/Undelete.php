<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_Undelete extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();

    $contestModel = $this->_getContestModel();
    $contest      = $contestModel->prepareContest($this->_findContest());

    $this->_assertCanUnDeleteContest($contest);

    if ($this->isConfirmedPost()) // undelete the contest
    {

      $dw = XenForo_DataWriter::create('FotoContest_DataWriter_Contest', XenForo_DataWriter::ERROR_SILENT);
      $dw->setExistingData($contest);
      $dw->set('contest_state', 'visible');
      $dw->save();

      return $this->responseRedirect(
        XenForo_ControllerResponse_Redirect::SUCCESS,
        XenForo_Link::buildPublicLink('photo-contests', $contest)
      );
    }
    else // show a undelete confirmation dialog
    {
      return $this->responseView(
        'FotoContest_ViewPublic_Contest_Undelete',
        'lfc_contest_undelete',
        array(
          'contest' => $contest
        )
      );
    }
  }
}
