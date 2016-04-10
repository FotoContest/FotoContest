<?php

class FotoContest_Shared_Deferred_Thumbnail_Create extends XenForo_Deferred_Abstract
{
  public function execute(array $deferred, array $data, $targetRunTime, &$status)
  {
    $data = array_merge(array(
      'batch' => 100,
      'position' => 0
    ), $data);

    $structureModel = $this->_getStructureModel();
    $model = XenForo_Model::create($structureModel->getModelClass());
    $attachmentModel = XenForo_Model::create('XenForo_Model_Attachment');
    $startTime = microtime(true);
    $dataIds = $model->getIdsInRange($data['position'], $data['batch']);

    if (sizeof($dataIds) == 0)
    {
      return false;
    }

    foreach ($dataIds AS $dataId)
    {
      $data['position'] = $dataId;

      $attachments = $attachmentModel->getAttachmentsByContentId(
        $structureModel->getContentType(), $dataId
      );

      foreach ($attachments as $attachment)
      {
        foreach ($structureModel->getThumbnailSizes() as $size)
        {
          FotoContest_Shared_Helper_Thumbnail::thumbnail($attachment, $size);
        }
      }

      if ($targetRunTime && microtime(true) - $startTime > $targetRunTime)
      {
        break;
      }
    }

    $actionPhrase = new XenForo_Phrase('rebuilding');
    $typePhrase = new XenForo_Phrase(
      $structureModel->getThumbnailRebuildPhrase()
    );

    $status = sprintf('%s... %s (%s)',
      $actionPhrase,
      $typePhrase, XenForo_Locale::numberFormat($data['position'])
    );

    return $data;
  }

  protected function _getStructureModel()
  {
    return XenForo_Model::create($this->_structureModel);
  }

  public function canCancel()
  {
    return true;
  }
}
