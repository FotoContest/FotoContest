<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_AttachmentHandler_Contest extends XenForo_AttachmentHandler_Abstract
{
  protected $_contestModel = null;

  protected $_contentIdKey = 'photo_contest_id';

  protected $_contentRoute = 'photo-contests';

  protected $_contentTypePhraseKey = 'lfc';

  protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
  {
    if ($contentData)
    {
      $contestId = $contentData['photo_contest_id'];

      if (!empty($contestId))
      {
        $contest = $this->_getContestModel()->getContestById($contestId);
        if ($contest)
        {
          return $this->_getContestModel()->canEditContest($contest, $null, $viewingUser);
        }
      }
    }

    return XenForo_Visitor::getInstance()->hasPermission('lfc', 'postContest');;
  }

  protected function _canViewAttachment(array $attachment, array $viewingUser)
  {
    return true;
  }

  public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
  {

  }

  protected function _getContestModel()
  {
    if (!$this->_contestModel)
    {
      $this->_contestModel = XenForo_Model::create('FotoContest_Model_Contest');
    }

    return $this->_contestModel;
  }

  public function getAttachmentConstraints()
  {
    $options = XenForo_Application::get('options');

    return array(
      'extensions' => preg_split('/\s+/', trim($options->lfcAttachmentExtensions)),
      'size'       => $options->lfcAttachmentMaxFileSize * 1024,
      'width'      => $options->lfcAttachmentMaxDimensions['width'],
      'height'     => $options->lfcAttachmentMaxDimensions['height'],
      'count'      => 1
    );
  }

  public function getAttachmentParams($contentData = array())
  {
    $params = array(
      'hash' => md5(uniqid('', true)),
      'content_type' => 'lfc',
    );

    if (empty($contentData))
    {
      return $params + array('content_data' => array());
    }
    else
    {
      return $params + array('content_data' => $contentData);
    }
  }
}
