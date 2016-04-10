<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_WidgetRenderer_LatestContests extends WidgetFramework_WidgetRenderer
{

  protected function _getConfiguration()
  {
    return array(
      'name' => new XenForo_Phrase('lfc_widget_latest_contests'),
      'options' => array(
        'limit' => XenForo_Input::UINT,
        'orderField' => XenForo_Input::STRING,
        'orderDirection' => XenForo_Input::STRING,
        'featured' => XenForo_Input::BOOLEAN
      ),
      'useCache'     => true,
      'cacheSeconds' => 3600,
    );
  }

  protected function _getOptionsTemplate()
  {
    return 'lfc_widget_latest_contests_options';
  }

  protected function _validateOptionValue($optionKey, &$optionValue)
  {
    if ('limit' == $optionKey) {
      if (empty($optionValue)) $optionValue = 5;
    }

    if ('orderField' == $optionKey) {
      if (empty($optionValue)) $optionValue = 'created_at';
    }

    if ('orderDirection' == $optionKey) {
      if (empty($optionValue)) $optionValue = 'DESC';
    }

    if ('featured' == $optionKey) {
      if (empty($optionValue)) $optionValue = false;
    }

    return true;
  }


  public function renderOptions(XenForo_ViewRenderer_Abstract $viewRenderer, array &$templateParams)
  {
    if (empty($templateParams['widget']['options']['orderField']))
    {
      $templateParams['widget']['options']['orderField'] = 'created_at';
    }

    if (empty($templateParams['widget']['options']['orderDirection']))
    {
      $templateParams['widget']['options']['orderDirection'] = 'DESC';
    }

    parent::renderOptions($viewRenderer, $templateParams);
  }

  protected function _getRenderTemplate(array $widget, $positionCode, array $params)
  {
    return 'lfc_widget_latest_contests';
  }

  protected function _render(array $widget, $positionCode, array $params, XenForo_Template_Abstract $template)
  {
    if (!XenForo_Visitor::getInstance()->hasPermission('lfc', 'view')){
      return;
    }

    if (empty($widget['options']['orderField']))
    {
      $widget['options']['orderField'] = 'created_at';
    }

    if (empty($widget['options']['orderDirection']))
    {
      $widget['options']['orderDirection'] = 'DESC';
    }

    if (empty($widget['options']['featured']))
    {
      $widget['options']['featured'] = false;
    }

    $contests     = array();
    $core         = WidgetFramework_Core::getInstance();
    $contestModel = $core->getModelFromCache('FotoContest_Model_Contest');
    $conditions   = array('contest_state' => 'visible');

    if ($widget['options']['featured']){
      $conditions['is_featured'] = true;
    }

    $contests = $contestModel->getContests($conditions,
      array(
        'limit' => $widget['options']['limit'],
        'order' => $widget['options']['orderField'],
        'direction' => $widget['options']['orderDirection']
      )
    );

    if (empty($contests)){
      return;
    }

    $contests = $contestModel->getAndMergeAttachmentsIntoContests($contests);
    $contests = $contestModel->prepareContests($contests);

    $template->setParam('contests', $contests);

    return $template->render();
  }

}
