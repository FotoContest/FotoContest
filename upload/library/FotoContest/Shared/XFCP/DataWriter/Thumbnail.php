<?php
/**
 * @package Luqman_FotoContest
 * @author Luqman
 */

class FotoContest_Shared_XFCP_DataWriter_Thumbnail extends XFCP_FotoContest_Shared_XFCP_DataWriter_Thumbnail
{
  public function _postSave()
  {
    parent::_postSave();

    $attachmentHash = $this->getExtraData(self::DATA_ATTACHMENT_HASH);

    if ($attachmentHash)
    {
      $this->associateAttachment($attachmentHash);
    }
  }

  public function associateAttachment($attachmentHash)
  {
    $rows = $this->_db->update('xf_attachment', array(
      'content_type' => $this->getContentType(),
      'content_id' => $this->getId(),
      'temp_hash' => '',
      'unassociated' => 0
    ), 'temp_hash = ' . $this->_db->quote($attachmentHash));

    $this->set('attach_count',
      $this->get('attach_count') + $rows,
      '',
      array('setAfterPreSave' => true)
    );

    $this->_updateAttachCount();
  }

  protected function _updateAttachCount()
  {
    $this->_db->update($this->_getPrimaryTable(), array(
      'attach_count' => $this->get('attach_count')
    ), $this->_getPrimaryKey() . ' = ' . $this->_db->quote($this->getId()));
  }

  protected function _postDelete()
  {
    parent::_postDelete();

    $this->_deleteAttachments();
  }

  /**
   * Deletes the attachments associated with this contest entry.
   */
  protected function _deleteAttachments()
  {
    $this->getModelFromCache('XenForo_Model_Attachment')
      ->deleteAttachmentsFromContentIds(
        $this->getContentType(),
        array($this->getId())
      );
  }
}
