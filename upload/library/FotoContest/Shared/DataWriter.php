<?php
/**
 * @package Luqman_FotoContest
 * @author Luqman
 */
abstract class FotoContest_Shared_DataWriter extends XenForo_DataWriter
{

  public function getId()
  {
    return $this->get($this->_getPrimaryKey());
  }

  protected function _getPrimaryKey()
  {
    return $this->_getAutoIncrementField($this->_getPrimaryTable());
  }

  protected function _getExistingData($data)
  {
    if (!$id = $this->_getExistingPrimaryKey($data))
    {
      return false;
    }

    return array(
      $this->_getPrimaryTable() => $this->_getModel()->getById($id)
    );
  }

  protected function _getUpdateCondition($tableName)
  {
    $primaryKey = $this->_getPrimaryKey();
    return $primaryKey . ' = ' . $this->_db->quote($this->getExisting($primaryKey));
  }

}
