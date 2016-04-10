<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Listener_WidgetFrameworkReady
{
  public static function init(array &$renderers)
  {
    $renderers[] = 'FotoContest_WidgetRenderer_LatestContests';
    $renderers[] = 'FotoContest_WidgetRenderer_LatestEntries';
  }
}
