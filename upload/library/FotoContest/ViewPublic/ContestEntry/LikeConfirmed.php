<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ViewPublic_ContestEntry_LikeConfirmed extends XenForo_ViewPublic_Base
{
  public function renderJson()
  {
    $contestEntry = $this->_params['contestEntry'];

    $viewParams = array(
      'contestEntry' => $contestEntry,
      'likesUrl' => XenForo_Link::buildPublicLink('photo-contest-entries/likes', $contestEntry)
    );

    $output = $this->_renderer->getDefaultOutputArray(
      get_class($this),
      $viewParams,
      'lfc_contest_entry_like_button'
    );

    $output += FotoContest_ViewPublic_Helper_Like::getLikeViewParams($this->_params['liked']);

    return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
  }
}
