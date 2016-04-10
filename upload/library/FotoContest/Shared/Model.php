<?php

class FotoContest_Shared_Model extends XenForo_Model
{
  const FETCH_USER = 0x01;

  protected $_prepareConditions = array();

  public function getById($id, array $fetchOptions = array())
  {
    $table = $this->_getTableName();
    $primaryKey = $this->_getPrimaryKey();

    $joinOptions = $this->prepareJoinOptions($fetchOptions);

    return $this->_getDb()->fetchRow('
      SELECT
        ' . $table . '.*
        ' . $joinOptions['selectFields'] . '
      FROM
        ' . $table . ' .
      ' . $joinOptions['joinTables'] . '
      WHERE
        ' . $table . '.' . $primaryKey . ' = ?', $id
    );
  }

  public function getByIds(array $ids, array $fetchOptions = array())
  {
    $table = $this->_getTableName();
    $primaryKey = $this->_getPrimaryKey();

    $joinOptions = $this->prepareJoinOptions($fetchOptions);

    if (empty($ids))
    {
      return;
    }

    return $this->fetchAllKeyed('
      SELECT ' . $table . '.*
        ' . $joinOptions['selectFields'] . '
      FROM ' . $table . '
        ' . $joinOptions['joinTables'] . '
      WHERE ' . $table . '.' . $primaryKey . ' IN (' . $this->_getDb()->quote($ids) . ')
    ', $primaryKey);
  }

  public function getAll(array $conditions, array $fetchOptions)
  {
    $joinOptions  = $this->prepareJoinOptions($fetchOptions);
    $orderClause  = $this->prepareOrderOptions($fetchOptions, 'created_at');
    $whereClause  = $this->prepareConditions($conditions, $fetchOptions);
    $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

    $table = $this->_getTableName();
    $primaryKey = $this->_getPrimaryKey();

    return $this->fetchAllKeyed($this->limitQueryResults('
        SELECT
         ' . $table . '.*
          ' . $joinOptions['selectFields'] . '
        FROM
          ' . $table . ' ' . $joinOptions['joinTables'] . '
        WHERE ' . $whereClause . '
        ' . $orderClause . '
      ', $limitOptions['limit'], $limitOptions['offset']
    ), $primaryKey);
  }

  public function getIdsInRange($id, $limit)
  {
    $table = $this->_getTableName();
    $primaryKey = $this->_getPrimaryKey();

    return $this->_getDb()->fetchCol(
      $this->_getDb()->limit('
        SELECT
          ' . $primaryKey . '
        FROM
          ' . $table . '
        WHERE
          ' . $primaryKey . ' > ?
        ORDER BY
          ' . $primaryKey . '
        ', $limit
      ),
      $id
    );
  }

  public function prepareJoinOptions(array $fetchOptions)
  {
    $table = $this->_getTableName();
    $selectFields = '';
    $joinTables = '';

    $db = $this->_getDb();

    if (!empty($fetchOptions['join']))
    {
      if ($fetchOptions['join'] & FotoContest_Shared_Model::FETCH_USER)
      {
        $selectFields .= ', user.username, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar';

        $joinTables .= '
          LEFT JOIN xf_user AS user
            ON (user.user_id = ' . $table. '.user_id)
        ';
      }
    }

    return array(
      'selectFields' => $selectFields,
      'joinTables'   => $joinTables
    );
  }

  public function prepareOrderOptions(array &$fetchOptions, $defaultOrderSql = 'title')
  {
    $choices = array(
      'created_at' => 'created_at'
    );

    if (isset($fetchOptions['order']) == false)
    {
      $fetchOptions['order'] = 'latest';
    }

    if ($fetchOptions['order'] == 'latest')
    {
      $fetchOptions['order'] = 'created_at';
      $fetchOptions['direction'] = 'desc';
    }

    if ($fetchOptions['order'] == 'oldest')
    {
      $fetchOptions['order'] = 'created_at';
      $fetchOptions['direction'] = 'ASC';
    }

    return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
  }

  public function prepareConditions(array $conditions, array &$fetchOptions)
  {
    $db = $this->_getDb();
    $sqlConditions = array();
    $table = $this->_getTableName();

    if (isset($conditions['user_id']) && !empty($conditions['user_id']))
    {
      $sqlConditions[] = $table . '.user_id = ' . $db->quote($conditions['user_id']);
    }

    $this->_getStructureModel()->getPrepareConditions(
      $sqlConditions, $conditions, $fetchOptions
    );

    return $this->getConditionsForClause($sqlConditions);
  }

  protected function _getTableName()
  {
    return $this->_getStructureModel()->getTableName();
  }

  protected function _getPrimaryKey()
  {
    return $this->_getStructureModel()->getContentKeyName();
  }

  protected function _getStructureModel()
  {
    return $this->getModelFromCache($this->_structureModel);
  }
}
