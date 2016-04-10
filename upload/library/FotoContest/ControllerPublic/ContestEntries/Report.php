<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_ContestEntries_Report extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();

    $contestEntryModel = $this->_getContestEntryModel();
    $contestEntry      = $this->_findContestEntry();

    if (!$contestEntryModel->canReportContestEntry($contestEntry, $null))
    {
      throw $this->getNoPermissionResponseException();;
    }

    $contest = $this->_findContest($contestEntry['photo_contest_id']);

    if ($this->_request->isPost())
    {
      $message = $this->_input->filterSingle('message', XenForo_Input::STRING);

      if (!$message)
      {
        return $this->responseError(
          new XenForo_Phrase('lfc_please_enter_reason_for_reporting_this_contest_entry')
        );
      }

      $this->assertNotFlooding('report');

      $reportModel = XenForo_Model::create('XenForo_Model_Report');
      $reportModel->reportContent('lfc_entry', $contestEntry, $message);

      $controllerResponse = $this->responseRedirect(
        XenForo_ControllerResponse_Redirect::SUCCESS,
        XenForo_Link::buildPublicLink('photo-contest-entries', $contestEntry)
      );

      $controllerResponse->redirectMessage = new XenForo_Phrase(
        'lfc_thank_you_for_reporting_this_contest_entry'
      );

      return $controllerResponse;
    }
    else
    {
      $viewParams = array(
        'contest'      => $contest,
        'contestEntry' => $contestEntry
      );

      return $this->responseView(
        'FotoContest_ViewPublic_ContestEntry_Report',
        'lfc_contest_entry_report',
        $viewParams
      );
    }
  }
}
