<?php

class FotoContest_Structure_ContestEntry extends FotoContest_Shared_Structure
{
  protected function _getStructure()
  {
    return array(
      'table' => 'xf_lfc_photo_contest_entry',
      'key'=> 'photo_contest_entry_id',
      'contentType' => 'lfc_entry',

      'searchResultsTemplate' => 'lfc_search_result_contest_entry',
      'searchFormClass' => 'FotoContest_ViewPublic_Search_ContestEntry',
      'searchFormTemplate' => 'lfc_search_form_contest_entry',

      'modelClass' => 'FotoContest_Model_ContestEntry',

      'thumbnailRebuildPhrase' => 'lfc_photo_contest_entry_thumbnails'
    );
  }

  public function getThumbnailSizes()
  {
    $options = XenForo_Application::get('options');

    return array(
      $options->lfcThumbnailEntrySmall,
      $options->lfcThumbnailEntryMedium
    );
  }

  public function getPrepareConditions(&$sqlConditions, $conditions, $fetchOptions)
  {
    $db = XenForo_Application::get('db');
    $table = $this->getTableName();

    if (isset($conditions['entry_state']) && !empty($conditions['entry_state']))
    {
      $sqlConditions[] = $table . '.entry_state = ' . $db->quote($conditions['entry_state']);
    }

    if (isset($conditions['contest_closed']))
    {
      $sqlConditions[] = 'xf_lfc_photo_contest.contest_closed = ' . $db->quote($conditions['contest_closed']);
    }
  }
}
