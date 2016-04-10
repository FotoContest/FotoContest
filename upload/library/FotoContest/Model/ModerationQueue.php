<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Model_ModerationQueue extends XenForo_Model_ModerationQueue
{
  public function getModerationQueueEntries()
  {
    return $this->_getDb()->fetchAll('
      SELECT *
      FROM xf_moderation_queue
      WHERE content_type = "lfc_entry"
      ORDER BY content_date
    ');
  }

  /**
   * Counts all moderation queue entries.
   *
   * @return integer
   */
  public function countModerationQueueEntries()
  {
    return $this->_getDb()->fetchOne('
      SELECT COUNT(*)
      FROM xf_moderation_queue
      WHERE content_type = "lfc_entry"
    ');
  }

  public function rebuildModerationQueueCountCache()
  {
    $cache = array(
      'total' => $this->countModerationQueueEntries(),
      'lastModifiedDate' => XenForo_Application::$time
    );

    $this->_getDataRegistryModel()->set('lfcModerationCounts', $cache);
    return $cache;
  }

  public function getModerationQueueHandlers()
  {
    $handlers = array();
    $class = XenForo_Application::resolveDynamicClass('FotoContest_ModerationQueueHandler_ContestEntry');
    $handlers['lfc_entry'] = new $class();
    return $handlers;
  }
}
