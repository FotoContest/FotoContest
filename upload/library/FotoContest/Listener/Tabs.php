<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Listener_Tabs
{
  public static function init(array &$extraTabs, $selectedTabId)
  {
    $position = XenForo_Application::get('options')->lfcNavigationTabPosition;

    if ($position == 'hidden')
    {
      return;
    }

    if (XenForo_Visitor::getInstance()->hasPermission('lfc', 'view'))
    {
      $extraTabs['lfc'] = array(
        'title'         => new XenForo_Phrase('lfc_contests'),
        'href'          => XenForo_Link::buildPublicLink('photo-contests'),
        'position'      => $position,
        'linksTemplate' => 'lfc_tab_links'
      );
    }
  }
}
