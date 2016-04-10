<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_List extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $viewingUser = XenForo_Visitor::getInstance()->toArray();

    $viewParams = array(
      'contestsOpen'   => $this->getContests(array('contest_closed' => false)),
      'contestsClosed' => $this->getContests(array('contest_closed' => true)),
      'canPostContest' => $viewingUser['permissions']['lfc']['postContest']
    );

    return $this->responseView(
      'FotoContest_ViewPublic_Contest_List',
      'lfc_contest_list',
      $viewParams
    );
  }

  protected function getContests($conditions = array())
  {
    if (!XenForo_Visitor::getInstance()->hasPermission('lfc', 'viewDeleted')){
      $conditions['contest_state'] = 'visible';
    }

    $contests = $this->_getContestModel()->getContests($conditions, array(
      'order' => 'voting_closes_on',
      'direction' => 'DESC'
    ));

    $contests = $this->_getContestModel()->getAndMergeAttachmentsIntoContests($contests);
    $contests = $this->_getContestModel()->prepareContests($contests);

    return $contests;
  }
}
