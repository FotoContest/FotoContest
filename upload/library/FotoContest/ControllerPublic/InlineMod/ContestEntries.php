<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_InlineMod_ContestEntries extends XenForo_ControllerPublic_InlineMod_Abstract
{
  /**
   * Key for inline mod data.
   *
   * @var string
   */
  public $inlineModKey = 'contestEntries';

  /**
   * @return XenForo_Model_InlineMod_Conversation
   */
  public function getInlineModTypeModel()
  {
    return $this->getModelFromCache('FotoContest_Model_InlineMod_ContestEntry');
  }

  /**
   * Redirect to actionSwitch();
   */
  public function actionRun()
  {
    return $this->actionSwitch();
  }

  /**
   * Thread deletion handler.
   *
   * @return XenForo_ControllerResponse_Abstract
   */
  public function actionDelete()
  {
    if ($this->isConfirmedPost())
    {
      $contestEntryIds = $this->getInlineModIds(false);

      $hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::STRING);
      $options = array(
        'deleteType' => ($hardDelete ? 'hard' : 'soft'),
        'reason' => $this->_input->filterSingle('reason', XenForo_Input::STRING)
      );

      $deleted = $this->getInlineModTypeModel()->deleteContestEntries(
        $contestEntryIds, $options, $errorPhraseKey
      );
      if (!$deleted)
      {
        throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
      }

      $this->clearCookie();

      return $this->responseRedirect(
        XenForo_ControllerResponse_Redirect::SUCCESS,
        $this->getDynamicRedirect(false, false)
      );
    }
    else // show confirmation dialog
    {
      $contestEntryIds = $this->getInlineModIds();
      $redirect       = $this->getDynamicRedirect();

      if (!$contestEntryIds)
      {
        return $this->responseRedirect(
          XenForo_ControllerResponse_Redirect::SUCCESS,
          $redirect
        );
      }

      $handler = $this->getInlineModTypeModel();
      if (!$handler->canDeleteContestEntries($contestEntryIds, 'soft', $errorPhraseKey))
      {
        throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
      }

      $viewParams = array(
        'contestEntryIds' => $contestEntryIds,
        'contestEntryCount' => count($contestEntryIds),
        'canHardDelete' => $handler->canDeleteContestEntries($contestEntryIds, 'hard'),
        'redirect' => $redirect,
      );

      return $this->responseView(
        'FotoContest_ViewPublic_InlineMod_ContestEntry_Delete',
        'lfc_inline_mod_contest_entry_delete',
        $viewParams);
    }
  }

  /**
   * Undeletes the specified contest entries.
   *
   * @return XenForo_ControllerResponse_Abstract
   */
  public function actionUndelete()
  {
    return $this->executeInlineModAction('undeleteContestEntries');
  }

}
