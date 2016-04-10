<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ViewPublic_ContestEntry_Add extends XenForo_ViewPublic_Base
{
  public function renderHtml()
  {
    $this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
      $this, 'description'
    );
  }
}
