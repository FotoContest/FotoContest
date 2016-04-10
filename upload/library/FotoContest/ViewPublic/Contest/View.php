<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ViewPublic_Contest_View extends XenForo_ViewPublic_Base
{
  public function renderHtml()
  {
    $bbCodeParser = XenForo_BbCode_Parser::create(
      XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this))
    );

    $this->_params['contest']['descriptionParsed'] = new XenForo_BbCode_TextWrapper(
      $this->_params['contest']['description'], $bbCodeParser
    );
  }
}

