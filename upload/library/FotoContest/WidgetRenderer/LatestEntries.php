<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_WidgetRenderer_LatestEntries extends WidgetFramework_WidgetRenderer
{
  protected $_defaultLimit = 5;
  protected $_defaultThumbSize = '44C';

  protected function _getConfiguration()
  {
    return array(
      'name' => new XenForo_Phrase('lfc_widget_latest_entries'),
      'options' => array(
        'limit' => XenForo_Input::UINT,
        'thumbSize' => XenForo_Input::STRING,
        'includeClosed' => XenForo_Input::BOOLEAN,
        'onlyWinningEntries' => XenForo_Input::BOOLEAN
      ),
      'useCache'     => true,
      'cacheSeconds' => 3600,
    );
  }

  protected function _getOptionsTemplate()
  {
    return 'lfc_widget_latest_entries_options';
  }

  protected function _validateOptionValue($optionKey, &$optionValue)
  {
    if ('limit' == $optionKey)
    {
      if (empty($optionValue))
      {
        $optionValue = $this->_defaultLimit;
      }
    }

    if ('thumbSize' == $optionKey)
    {
      if (empty($optionValue))
      {
        $optionValue = $this->_defaultThumbSize;
      }
    }

    return true;
  }

  public function renderOptions(XenForo_ViewRenderer_Abstract $viewRenderer, array &$templateParams)
  {
    if (empty($templateParams['widget']['options']['limit']))
    {
      $templateParams['widget']['options']['limit'] = $this->_defaultLimit;
    }

    if (empty($templateParams['widget']['options']['thumbSize']))
    {
      $templateParams['widget']['options']['thumbSize'] = $this->_defaultThumbSize;
    }

    parent::renderOptions($viewRenderer, $templateParams);
  }

  protected function _getRenderTemplate(array $widget, $positionCode, array $params)
  {
    return 'lfc_widget_latest_entries';
  }

  protected function _render(array $widget, $positionCode, array $params, XenForo_Template_Abstract $template)
  {
    if (!XenForo_Visitor::getInstance()->hasPermission('lfc', 'view')){
      return;
    }

    $entries         = array();
    $core            = WidgetFramework_Core::getInstance();
    $model           = $core->getModelFromCache('FotoContest_Model_ContestEntry');
    $limit           = $widget['options']['limit'];
    $fetchOptions    = array('entry_state' => 'visible');

    $onlyOpenContest = isset($widget['options']['includeClosed']) && empty($widget['options']['includeClosed']);
    if ($onlyOpenContest){
      $fetchOptions['contest_closed'] = 0;
    }

    $join = FotoContest_Model_ContestEntry::FETCH_CONTEST_VISIBLE;

    $onlyWinningEntries = isset($widget['options']['onlyWinningEntries']) && $widget['options']['onlyWinningEntries'];
    if ($onlyWinningEntries) {
      $join = $join | FotoContest_Model_ContestEntry::FETCH_WINNERS_ONLY;
    }

    $entries = $model->getAll($fetchOptions,
      array(
        'join' => $join,
        'limit' => $limit
      )
    );

    if (empty($entries)){
      return;
    }

    $includeAttachment = $core->getModelFromCache('FotoContest_Shared_Include_Attachment');
    $includeAttachment->hasManyAttachments($entries, 'lfc_entry');

    $includeContests = $core->getModelFromCache('FotoContest_Model_Prepare_ContestEntry');
    $entries = $includeContests->mergeWithContests($entries);

    $template->setParams(array(
        'entries' => $entries,
        'thumbSize' =>  $widget['options']['thumbSize']
      )
    );

    return $template->render();
  }

}
