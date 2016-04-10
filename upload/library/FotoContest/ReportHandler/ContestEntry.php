<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ReportHandler_ContestEntry extends XenForo_ReportHandler_Abstract
{
  /**
   * Gets report details from raw array of content (eg, a post record).
   *
   * @see XenForo_ReportHandler_Abstract::getReportDetailsFromContent()
   */
  public function getReportDetailsFromContent(array $content)
  {
    /* @var $contestEntryModel FotoContest_Model_ContestEntry */
    $contestEntryModel = XenForo_Model::create('FotoContest_Model_ContestEntry');
    $contestEntry      = $contestEntryModel->getContestEntryById($content['photo_contest_entry_id']);

    if (!$contestEntry)
    {
      return array(false, false, false);
    }

    return array(
      $content['photo_contest_entry_id'],
      $content['user_id'],
      array(
        'user_id' => $contestEntry['user_id'],
        'username' => $contestEntry['username'],
        'title' => $contestEntry['title']
      )
    );
  }

  /**
   * Gets the visible reports of this content type for the viewing user.
   *
   * @see XenForo_ReportHandler_Abstract:getVisibleReportsForUser()
   */
  public function getVisibleReportsForUser(array $reports, array $viewingUser)
  {
    foreach ($reports AS $reportId => $report)
    {
      if (!$viewingUser['permissions']['lfc']['editAnyEntry'])
      {
        unset($reports[$reportId]);
      }
    }
    return $reports;
  }

  /**
   * Gets the title of the specified content.
   *
   * @see XenForo_ReportHandler_Abstract:getContentTitle()
   */
  public function getContentTitle(array $report, array $contentInfo)
  {
    return new XenForo_Phrase('lfc_contest_entry_for_x', array(
      'title' => $contentInfo['title']
    ));
  }

  /**
   * Gets the link to the specified content.
   *
   * @see XenForo_ReportHandler_Abstract::getContentLink()
   */
  public function getContentLink(array $report, array $contentInfo)
  {
    return XenForo_Link::buildPublicLink(
      'photo-contest-entries', array(
        'photo_contest_entry_id' => $report['content_id']
      )
    );
  }

  /**
   * A callback that is called when viewing the full report.
   *
   * @see XenForo_ReportHandler_Abstract::viewCallback()
   */
  public function viewCallback(XenForo_View $view, array &$report, array &$contentInfo)
  {
    return $view->createTemplateObject('lfc_report_contest_entry_content', array(
      'report' => $report,
      'content' => $contentInfo
    ));
  }
}
