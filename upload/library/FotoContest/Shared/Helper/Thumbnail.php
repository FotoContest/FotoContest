<?php

class FotoContest_Shared_Helper_Thumbnail
{
  public static function getExternalDataPath(array $attachment)
  {
    $externalDataPath = XenForo_Helper_File::getExternalDataPath();
    $contentId = $attachment['content_id'];

    return sprintf('%s/%s/%d/%d',
      $externalDataPath,
      $attachment['content_type'],
      floor($contentId / 1000),
      $contentId
    );
  }

  public static function getThumbnailFilePath(array $attachment, $size)
  {
    $externalDataPath = self::getExternalDataPath($attachment);

    return sprintf('%s/%s/%s.jpg',
      $externalDataPath,
      $size,
      $attachment['file_hash']
    );
  }

  public static function getAttachmentThumbnailUrl(array $attachment, $size)
  {
    $contentId = $attachment['content_id'];
    return sprintf('%s/%s/%d/%d/%s/%s.jpg',
      XenForo_Application::$externalDataUrl,
      $attachment['content_type'],
      floor($contentId / 1000),
      $contentId,
      $size,
      $attachment['file_hash']
    );
  }

  public static function thumbnail($attachment, $size)
  {
    $thumbFilepath = self::getThumbnailFilePath($attachment, $size);

    if (!file_exists($thumbFilepath))
    {
      $thumbnail = self::getExternalDataPath($attachment);
      $attachmentModel = Xenforo_Model::create('XenForo_Model_Attachment');
      $original = $attachmentModel->getAttachmentDataFilePath($attachment);

      $directory = dirname($thumbFilepath);
      $thumbnail = basename($thumbFilepath);

      self::process($original, $directory, $thumbnail, $size);
    }

    return self::getAttachmentThumbnailUrl($attachment, $size);
  }

  public static function process($original, $directory, $thumbnail, $size)
  {
    $vendor = XenForo_Application::getInstance()->getRootDir();
    require_once(
      $vendor . '/library/FotoContest/Shared/Vendor/easyphpthumbnail.class.php'
    );

    XenForo_Helper_File::createDirectory($directory, true);

    $thumb = new easyphpthumbnail();
    $thumb->Thumbsize = $size;
    $thumb->Thumblocation = $directory . '/';
    $thumb->Thumbprefix = '';
    $thumb->Thumbfilename = $thumbnail;

    if (strpos($size, 'C') !== FALSE)
    {
      $thumb->Cropimage = array(3, 0, 0, 0, 0, 0);
    }

    $thumb->Createthumb($original, 'file');
  }
}
