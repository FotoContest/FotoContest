<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Listener_InitDependencies
{
  public static function init(XenForo_Dependencies_Abstract $dependencies, array $data)
  {
    $dependencies->preloadTemplate('lfc_moderator_bar');
    XenForo_Template_Helper_Core::$helperCallbacks['lfc_thumb'] = array(
      'FotoContest_Shared_Helper_Thumbnail', 'thumbnail'
    );
  }
}
