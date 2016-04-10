<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_ModerationQueue_Save extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertPostOnly();

    $queue = $this->_input->filterSingle('queue', XenForo_Input::ARRAY_SIMPLE);

    $this->_getModerationQueueModel()->saveModerationQueueChanges($queue);

    return $this->responseRedirect(
      XenForo_ControllerResponse_Redirect::SUCCESS,
      XenForo_Link::buildPublicLink('photo-contests-moderation')
    );
  }
}
