<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_AttachmentHandler_ContestEntry extends XenForo_AttachmentHandler_Abstract
{
  public static $maxUploadCountLimit = 1;

  protected $_contestEntryModel = null;

  protected $_contentIdKey = 'photo_contest_entry_id';

  protected $_contentRoute = 'photo-contest-entries';

  protected $_contentTypePhraseKey = 'lfc_entry';

  protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
  {
    if ($contentData)
    {
      $contestEntryId = $contentData['photo_contest_entry_id'];
      if (!empty($contestEntryId))
      {
        $contestEntry = $this->_getContestEntryModel()->getContestEntryById($contestEntryId);
        if ($contestEntry)
        {
          return $this->_getContestEntryModel()->canEditContestEntry($contestEntry, $null, $viewingUser);
        }
      }

    }
    return XenForo_Visitor::getInstance()->hasPermission('lfc', 'postEntry');
  }

  protected function _canViewAttachment(array $attachment, array $viewingUser)
  {
    return true;
  }

  public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db){}

  protected function _getContestEntryModel()
  {
    if (!$this->_contestEntryModel)
    {
      $this->_contestEntryModel = XenForo_Model::create('FotoContest_Model_ContestEntry');
    }

    return $this->_contestEntryModel;
  }

  public function getAttachmentConstraints()
  {
    $options = XenForo_Application::get('options');

    return array(
      'extensions' => preg_split('/\s+/', trim($options->lfcAttachmentExtensions)),
      'size'       => $options->lfcAttachmentMaxFileSize * 1024,
      'width'      => $options->lfcAttachmentMaxDimensions['width'],
      'height'     => $options->lfcAttachmentMaxDimensions['height'],
      'count'      => self::$maxUploadCountLimit
    );
  }

  public function getAttachmentParams($contentData = array())
  {
    $params = array(
      'hash' => md5(uniqid('', true)),
      'content_type' => 'lfc_entry',
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
