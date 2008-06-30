<?php
/**
 * Provide a Zend_Table instance that can add permissions
 * to appropriate calls.
 */
abstract class DP_Policy_Table extends DP_Zend_Table implements DP_Policy_Interface
{
	/**
	 * Get a DP_Policy_Table object of the parent of this object.
	 * @return object DP_Policy_Table instance of the parent of this object.
	 */
	public function getParent()
	{
		return null;
	}

	/**
	 * Return a list of members of this object
	 * @param int $id
	 * @return array of members of this instance.
	 */
	public function getMembers($id)
	{
		return null;
	}

	/**
	 * Return an indication of the membership status of the current user.
	 * @param int $id
	 * @return boolean true if current logged in user is a member of this object
	 */
	public function isMember($id)
	{
		// TODO: Check the fields in this
		$user = Zend_Auth::getIdentity();
		// Determine if the current user is a member of the id provided
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from('memberships', array('COUNT(*) as member_count'));
		$select->where('parent_id = ?', $id);
		$select->where('parent_type = ?', $this->_name);
		$select->where('child_id IN ('. implode(',', $user['memberships']) . ')');
		$count = $select->query()->fetchColumn();
		return $count;
	}

	/**
	 * Return an indication if the current user is the owner of this object.
	 * @param int id
	 * @return boolean true if current logged in is owner of this object
	 */
	public function isOwner($id)
	{
		// TODO: Check the fields in this
		$user = Zend_Auth::getIdentity();
		// Determine if the current user is a member of the id provided
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from('memberships', array('COUNT(*) as member_count'));
		$select->where('parent_id = ?', $id);
		$select->where('parent_type = ?', $this->_name);
		$select->where('child_id IN ('. implode(',', $user['memberships']) . ')');
		$select->where('is_owner');
		$count = $select->query()->fetchColumn();
		return $count;
	}

	/**
	 * Return the current policy for this class of object
	 *
	 * @return array
	 */
	public function getPolicy()
	{
		return DP_Policy::getPolicy($this->_name);
	}

	/**
	 * Determine if the user has the required permissions for the identified object.
	 *
	 * @param int $id object id to check
	 * @param int $level level of access required.
	 * @return boolean true if the user has the required permission
	 */
	public function hasPermission($id, $level = DP_Policy::ACCESS_VIEW)
	{
		if ($this->isOwner($id)) {
			return DP_Policy::getInstance()->hasPermission($this->_name, DP_Policy::GROUP_OWNER, $level);
		}
		if ($this->isMember($id)) {
			return DP_Policy::getInstance()->hasPermission($this->_name, DP_Policy::GROUP_MEMBER, $level);
		}
		return DP_Policy::getInstance()->hasPermission($this->_name, DP_Policy::GROUP_NON_MEMBER, $level);
	}

	/**
	 * Return true if the user can view the indicated object.
	 *
	 * @param int $id object id to check
	 * @return boolean true if the user can view this object
	 */
	public function canView($id)
	{
		return $this->hasPermission($id, DP_Policy::ACCESS_VIEW);
	}

	/**
	 * Return true if the user can edit the indicated object.
	 *
	 * @param int $id object id to check
	 * @return boolean true if the user can edit this object
	 */
	public function canEdit($id)
	{
		return $this->hasPermission($id, DP_Policy::ACCESS_EDIT);
	}

	/**
	 * Return true if the user can add children to the indicated object.
	 *
	 * @param int $id object id to check
	 * @return boolean true if the user can add children to this object
	 */
	public function canAddChild($id)
	{
		return $this->hasPermission($id, DP_Policy::ACCESS_CHILD);
	}

	/**
	 * Don't allow insert if we don't have permission
	 *
	 * @param array $data
	 * @return ?
	 */
	public function insert(array $data)
	{
		// We can't be a member of this object yet as it doesn't exist,
		// so we need to determine the parent objects.
		$parent = $this->getParent();
		$parent_id = $this->getParentId();
		if ($parent && $parent_id && ! $parent->canAddChild($parent_id)) {
			throw new DP_Policy_Exception('insert');
			return false;
		}
		return parent::insert($data);
	}

	/**
	 * Similar constraint on update
	 */
	public function update(array $data, $where)
	{
		// The where clause should indicate the key that needs to change, alternatively
		// look for the key in the data
		if (! $this->canEdit($this->findIdInWhere($where))) {
			throw new DP_Policy_Exception('update');
			return false;
		}
		return parent::update($data, $where);
	}

	public function delete($where)
	{
		if (! $this->canEdit($this->findIdInWhere($where))) {
			throw new DP_Policy_Exception('delete');
			return false;
		}
		return parent::delete($where);
	}

	public function find($key)
	{
		if (! $this->canView($key)) {
			throw new DP_Policy_Exception('find');
			return false;
		}
		return parent::find($key);
	}

	/** 
	 * Utility function to figure out the ID in a where statement.
	 */
	protected function findIdInWhere($where)
	{
		$where = (array)$where;
		foreach ($where as $key => $val) {
			if (is_int($key)) {
				if (preg_match('/\b'. $this->_primary . '\b`?\s*=\s*[\'"]?(\w+)[\'"]\b/', $val, $matched)) {
					return $matched[1];
				}
			} else {
				if (strpos($this->_primary, $key) !== FALSE) {
					return $val;
				}
			}
		}
		return false;
	}


	/**
	 *
	 */
	public function addPolicy(Zend_Db_Select $select, $level) 
	{
		// Grab the policy for this object type
		$policy = DP_Policy::getPolicy($this->_name);
		// Now we need to reconstruct the query to add memberships
		$memberships = Zend_Auth::getInstance()->getIdentity()->getMemberships();
		// There are 8 possible conditions
		// Member  Non-Member   Owner
		//   -        -           -  // Don't return any rows
		//   -        -           +  // Only return where we are owner
		//   -        +           -  // Silly, but only return if we are not the owner and not a member
		//   -        +           +  // Silly, only return if we are a non-member or the owner
		//   +        -           -  // Only return if we are a member, but not the owner
		//   +        -           +  // Only return if we are a member or the owner
		//   +        +           -  // Return all rows
		//   +        +           +  // Return all rows, but safe to add the owner restriction.

		// Handle Owner separately
		$owner = $policy[DP_Policy::GROUP_OWNER] & $level == $level;
		$member = $policy[DP_Policy::GROUP_MEMBER] & $level == $level;
		$nonmember = $policy[DP_Policy::GROUP_NON_MEMBER] & $level == $level;

		if ($owner) {
			$select->where(array('(SELECT count(*) FROM memberships WHERE parent_id = ' . $this->_name . '.' . $this->_primary . ' AND parent_type = ? AND child_id IN (' . implode($memberships) . ') AND child_type = \'people\' AND is_owner) > 0' => $this->_name));
		} else {
			$select->where(array('(SELECT count(*) FROM memberships WHERE parent_id = ' . $this->_name . '.' . $this->_primary . ' AND parent_type = ? AND child_id IN (' . implode($memberships) . ') AND child_type = \'people\' AND is_owner) = 0' => $this->_name));
		}
		// If both member and non member are the same, we just need to either return all or no rows.
		if ($member == $nonmember) {
			if (! $member) {
				$select->where('1=0');
			}
		} else if ($member) {
			// Return only if we are a member
			$select->where(array('(SELECT count(*) FROM memberships WHERE parent_id = ' . $this->_name . '.' . $this->_primary . ' AND parent_type = ? AND child_id IN (' . implode($memberships) . ')) > 0' => $this->_name));
		} else {
			// Return only those we are not a member of.
			$select->where(array('(SELECT count(*) FROM memberships WHERE parent_id = ' . $this->_name . '.' . $this->_primary . ' AND parent_type = ? AND child_id IN (' . implode($memberships) . ')) = 0' => $this->_name));
		}

		return $select;
	}
}
?>
