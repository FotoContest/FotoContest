<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Listener_TemplateHook
{
  public static function init($name, &$contents, $params, XenForo_Template_Abstract $template)
  {
    $name = str_replace('_', '', $name);
    if (method_exists(__CLASS__, $name)){
      self::$name(
        $contents,
        $params,
        $template
      );
    }
  }

  public static function moderatorBar(&$contents, $params, XenForo_Template_Abstract $template)
  {
    $templateParams = $template->getParams();
    $session        = $templateParams['session'];

    if ($session['lfcModerationCounts']['total'])
    {
      $contents = $template->create(
        'lfc_moderator_bar',
        array('session' => $session)
      )->render();
    }
  }

  public static function accountAlertsExtra(&$contents, $params, $template)
  {
    $contents .= $template->create('lfc_alert_preferences', $template->getParams())->render();
  }

  public static function searchFormTabs(&$contents, $params, $template)
  {
    $contents .= $template->create('lfc_search_form_tabs', $template->getParams())->render();
  }

  public static function threadViewPagenavBefore(&$contents, $params, $template)
  {
    if ($params['thread']['discussion_type'] == 'lfc_entry')
    {
      $contestEntryModel = XenForo_Model::create('FotoContest_Model_ContestEntry');

      $contents .= $template->create('lfc_contest_entry_view_tabs',
        $template->getParams() + array(
          'contestEntry' => $contestEntryModel->getContestEntryByThreadId($params['thread']['thread_id']),
          'selectedTab'  => 'discussion',
          'jsTabs'       => false
        )
      );
    }
  }

  public static function templatePostRender($templateName, &$contents, array &$containerData, XenForo_Template_Abstract $template)
  {
    if ($template instanceof XenForo_Template_Admin && $templateName == 'tools_rebuild')
    {
      $contents .= $template->create(
        'lfc_tools_rebuild',
        $template->getParams()
      )->render();
    }
  }

  public static function userCriteriaContent(&$contents, $params, $template)
  {
    $contents .= $template->create(
      'lfc_user_criteria_content',
      $template->getParams()
    )->render();
  }
}
