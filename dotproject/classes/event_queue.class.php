<?php // $Id$
if (!defined('DP_BASE_DIR')){
  die('You should not access this file directly');
}

/**
 * Event handling queue class.
 *
 * The event queue uses the table event_queue to manage
 * event notifications and other timed events, as well as
 * outgoing emails.
 *
 * @author Copyright 2005, the dotProject team.
 */
class EventQueue {

	var $table = 'event_queue'; /**< Table to use for events, always 'event_queue' */
	var $update_list = array(); /**< List of events to update */
	var $delete_list = array(); /**< List of events to delete */
	var $event_count = 0; /**< Counter containing number of events to execute */

	/** EventQueue constructor */
	function EventQueue()
	{
	}

	/**
	 * Add an event to the queue.
	 *
	 * The callback can either be the name of a global function or the
	 * name of a class
	 * @param $callback function to call when this event is due.
	 * @param $args Arguments to pass to the callback
	 * @param $module module, or originator of the event
	 * @param $type type of event (to allow searching)
	 * @param $id id of originating event.
	 * @param $date Seconds since 1970 to trigger event.
	 * @param $repeat_interval seconds to repeat
	 * @param $repeat_count number of times to repeat
	 * @return queue id as integer
	 * @author gregorerhardt
	 */
	function add($callback, &$args, $module, $sysmodule = false, $id = 0, $type = '', $date = 0, $repeat_interval = 0, $repeat_count = 1)
	{
		global $AppUI;

		if (! isset($AppUI))
			$user_id = 0;
		else
			$user_id = $AppUI->user_id;

		if (is_array($callback)) {
			list($class, $method) = $callback;
			if (is_object($class))
				$class = get_class($class);
			$caller = $class . '::' . $method;
		} else {
			$caller = $callback;
		}

		$q = new DBQuery;
		$q->addTable($this->table);
		$q->addInsert('queue_owner', $user_id);
		$q->addInsert('queue_start', $date);
		$q->addInsert('queue_callback', $caller);
		$q->addInsert('queue_data', serialize($args));
		$q->addInsert('queue_repeat_interval', $repeat_interval);
		$q->addInsert('queue_repeat_count', $repeat_count);
		$q->addInsert('queue_module', $module);
		$q->addInsert('queue_type', $type);
		$q->addInsert('queue_origin_id', $id);
		if ($sysmodule)
			$q->addInsert('queue_module_type', 'system');
		else
			$q->addInsert('queue_module_type', 'module');
		if ($q->exec())
			$return =  db_insert_id();
		else
			$return =  false;
		$q->clear();
		return $return;
	}

	/**
	 * Remove the event from the queue. 
	 * @param $id Event ID
	 */
	function remove($id)
	{
		$q = new DBQuery;
		$q->setDelete($this->table);
		$q->addWhere("queue_id = '$id'");
		$q->exec();
		$q->clear();
	}

	/**
	 * Find a queue record (or records) matching the supplied parameters
	 * 
	 * @param $module Module that the event is associated with.
	 * @param $type Type(?)
	 * @param $id Queue origin id
	 * @return Associative array of queue records
	 */
	function find($module, $type, $id = null)
	{
		$q = new DBQuery;
		$q->addTable($this->table);
		$q->addWhere("queue_module = '$module'");
		$q->addWhere("queue_type = '$type'");
		if (isset($id))
			$q->addWhere("queue_origin_id = '$id'");
		return $q->loadHashList('queue_id');
	}

	/** Execute an event queue entry
	 *
	 * This involves resolving the
	 * method to execute and passing the arguments to it.
	 * @param &$fields Reference to the event queue entry to process
	 * @return The return result of the event method called.
	 */
	function execute(&$fields)
	{
		global $AppUI;

		if (isset($fields['queue_module_type'])
		&& $fields['queue_module_type'] == 'system')
			include_once $AppUI->getSystemClass($fields['queue_module']);
		else
			include_once $AppUI->getModuleClass($fields['queue_module']);

		$args = unserialize($fields['queue_data']);
		if (strpos($fields['queue_callback'], '::') !== false) {
			list($class, $method) = explode('::', $fields['queue_callback']);
			if (!class_exists($class)) {
				dprint(__FILE__, __LINE__, 2, "Cannot process event: Class $class does not exist");
				return false;
			}
			$object = new $class;
			if (!method_exists($object, $method)) {
				dprint(__FILE__, __LINE__, 2, "Cannot process event: Method $class::$method does not exist");
				return false;
			}
			return $object->$method($fields['queue_module'], $fields['queue_type'], $fields['queue_origin_id'], $fields['queue_owner'], $args);
		} else {
			$method = $fields['queue_callback'];
			if (!function_exists($method)) {
				dprint(__FILE__, __LINE__, 2, "Cannot process event: Function $method does not exist");
				return false;
			}
			return $method($fields['queue_module'], $fields['queue_type'], $fields['queue_origin_id'], $fields['queue_owner'], $args);
		}
	}

	/** Scan and execute events to be processed in the queue
	 *
	 * Scans the queue for entries that are older than current date.
	 * If it finds one it tries to execute the attached function.
	 * If successful, the entry is removed from the queue, or if
	 * it is a repeatable event the repeat time is added to the
	 * start time and the repeat count (if set) is decremented.
	 */
	function scan()
	{
		$q = new DBQuery;
		$q->addTable($this->table);
		$now = time();
		$q->addWhere('queue_start < ' . $now);
		$rid = $q->exec();

		$this->event_count = 0;
		for ($rid; ! $rid->EOF; $rid->moveNext()) {
			if (($res = $this->execute($rid->fields)) !== false ) {
				$this->update_event($rid->fields, $res);
				$this->event_count++;
			}
		}
		$q->clear();

		$this->commit_updates();
	}

	/** Add an event to the list of events to update or delete
	 * @param &$fields Event to process
	 * @param $flag Boolean to indicate whether to delete only this instance of a repeating event
	 * @author gregorerhardt
	 */
	function update_event(&$fields, $flag)
	{	
		if ($flag === true && $fields['queue_repeat_interval'] > 0 && ($fields['queue_repeat_count'] > 0 || $fields['queue_repeat_count'] == '-8') ) {
			/**
			** changed to actual time + interval because there could emerge the situation
			** where dotproject isn't used (and no EventQueue->scan() is done) for a longer time
			** (e.g. over night) and then in case of short repeat cycles (1 min) the event_queue is pushed 
			** on every dp call because the updated queue_start times are light years behind the actual time since 1970.
			*/
			$fields['queue_start'] = time()+$fields['queue_repeat_interval'];
			// only decrease counter if event isn't repeated infinitely
			if ($fields['queue_repeat_count'] != -8) {
				$fields['queue_repeat_count']--;
			}
			$this->update_list[] = $fields;
		} else {
			$this->delete_list[] = $fields['queue_id'];
		}
	}

	/** Commit the pending changes to events in the queue
	 */
	function commit_updates()
	{
		$q = new DBQuery;
		if (count($this->delete_list)) {
			$q->setDelete($this->table);
			$q->addWhere("queue_id in (" . implode(',', $this->delete_list) . ")");
			$q->exec();
			$q->clear();
		}
		$this->delete_list = array();

		foreach ($this->update_list as $fields) {
			$q->addTable($this->table);
			$q->addUpdate('queue_repeat_count', $fields['queue_repeat_count']);
			$q->addUpdate('queue_start', $fields['queue_start']);
			$q->addWhere('queue_id = ' . $fields['queue_id']);
			$q->exec();
			$q->clear();
		}
		$this->update_list = array();
	}

}
?>