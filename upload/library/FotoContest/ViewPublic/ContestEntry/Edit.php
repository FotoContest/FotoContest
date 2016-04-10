<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ViewPublic_ContestEntry_Edit extends XenForo_ViewPublic_Base
{
  public function renderHtml()
  {
    $this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
      $this, 'description', $this->_params['contestEntry']['description']
    );
  }
}

