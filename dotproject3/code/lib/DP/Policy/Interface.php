<?php
/**
 * Interface for file consistancy to allow us to determine the parent
 * object for this object.
 */

interface DP_Policy_Interface
{
	/**
	 * Determine the parent membership
	 */
	public function getParent();

	/**
	 * Get a list of all members
	 */
	public function getMembers();

	/**
	 * Return just if the logged in user is a member
	 */
	public function isMember();

	/**
	 * Return indication if the logged in user is the owner
	 */
	public function isOwner();
	/**
	 * Return the policy
	 */
	public function getPolicy();

}
?>
