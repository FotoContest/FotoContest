<?php

class FotoContest_Shared_Include_Attachment extends XenForo_Model
{
  public function hasManyAttachments(&$records, $type)
  {
    if (empty($records)){
      return;
    }

    $model = $this->_getAttachmentModel();

    if (empty($records) == false)
    {
      $contentIds  = array_keys($records);
      $attachments = $model->getAttachmentsByContentIds($type, $contentIds);

      $model->prepareAttachments($attachments);
    }

    $this->_setupAttachments($records);

    foreach ($attachments as $attachment)
    {
      $contentId = $attachment['content_id'];
      $records[$contentId]['attachments'][] = $attachment;
    }

    foreach ($records as &$record)
    {
      $record['attachment'] = current($record['attachments']);
    }
  }

  protected function _setupAttachments(&$records)
  {
    foreach ($records as &$record)
    {
      $record['attachments'] = array();
    }
  }

  protected function _getAttachmentModel()
  {
    return $this->getModelFromCache('XenForo_Model_Attachment');
  }
}
