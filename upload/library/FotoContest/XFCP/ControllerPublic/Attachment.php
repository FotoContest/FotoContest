<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_XFCP_ControllerPublic_Attachment extends XFCP_FotoContest_XFCP_ControllerPublic_Attachment
{
  /**
   * @return FotoContest_Model_Attachment || XenForo_Model_Attachment
   */
  protected function _getAttachmentModel()
  {
    $contentType = $this->_input->filterSingle('content_type', XenForo_Input::STRING);

    if ($contentType == 'lfc_entry')
    {
      $contestId       = $this->_input->filterSingle('key', XenForo_Input::UINT);
      $contestModel    = $this->getModelFromCache('FotoContest_Model_Contest');
      $contest         = $contestModel->getContestById($contestId);

      if ($contest)
      {
        FotoContest_AttachmentHandler_ContestEntry::$maxUploadCountLimit = $contest[
          'max_images_per_entry'
        ];
      }
    }

    if (in_array($contentType, array('lfc', 'lfc_entry')))
    {
      return $this->getModelFromCache('FotoContest_Model_Attachment');
    }

    return parent::_getAttachmentModel();
  }
}
