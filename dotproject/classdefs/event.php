<?php

require_once 'classdefs/date.php';
require_once 'functions/lib.DB.php';

class dotEvent
{
	var $event_id = NULL;
	var $name = NULL;
	var $sdate = NULL;
	var $edate = NULL;
	var $stime = NULL;
	var $etime = NULL;
	var $parent = NULL;
	var $description = NULL;
	var $times_recuring = NULL;
	var $recurs = NULL;
	var $remind = NULL;

  	function dotEvent()
  	{
	}

	function getDefault()
	{
		$obj = new dotEvent();
		$obj->name = "Event summary";
		$tmpTimestamp = time();
		// set default values for start & end dates
		$date = new Date();
		$date->addHours(1);
		$date->setMinutes(0);
		$obj->sdate = $date->toString( DATE_FORMAT );
		$obj->stime = $date->toString( TIME_FORMAT );
		$date->addHours(1);
		$obj->edate = $date->toString( DATE_FORMAT );
		$obj->etime = $date->toString( TIME_FORMAT );
		print_r($obj);
		$obj->remind = 1800;
		return $obj;
	}
	
	function Load( $oid )
	{
		$obj = new dotEvent();
		$ret = DB_loadHash( "SELECT * FROM events WHERE event_id=$oid", $hash );
		$obj->name = $hash['event_title'];
		$obj->description = $hash['event_description'];
		$obj->parent = $hash['event_parent'];
		$obj->times_recuring = $hash['event_times_recuring'];
		$obj->recurs = $hash['event_recurs'];
		$obj->remind = $hash['event_remind'];

		// set start and end date and time values
		$obj->sdate = strftime( "%d/%m/%Y", $obj->event_start_date );
		$obj->stime = strftime( "%H:M", $obj->event_start_date );
		$obj->edate = strftime( "%d/%m/%Y", $obj->event_end_date );
		$obj->etime = strftime( "%H:M", $obj->event_end_date );
		AppLog( "events/event/$obj->event_id", 'load', $ret );
		return $obj;
	}
	
	function Check( $hash )
	{
		if( $this->name == '' )
			return "Event title is required";
		if( $this->remind )
		return NULL; // object is ok
	}

	function makeDate( &$field, $strdate, $strtime )
	{
		$date = new Date();
		$date->setDate( $y,$m,$d );
		if( DATE_FORMAT == '%d/%m/%Y' )
			list( $d,$m,$y)  = split( "/", $strdate );
		elseif( DATE_FORMAT == '%m/%d/%Y' )
			list( $d,$m,$y)  = split( "/", $strdate );
		else
			die( "SYSTEM ERROR : unknown DATE_FORMAT " . DATE_FORMAT );

		list( $h,$m ) = split( ":", $strtime );
		$date->setTime( $h, $m );
		$field = $date->getTimestamp();
	}
	
	function Bind( $hash )
	{
		is_array($hash) or die( "dotEvent.Bind : hash expected" );
		isset( $hash['name'] ) && $this->name = $hash['name'];
		isset( $hash['description'] ) && $this->description = $hash['description'];

		$this->makeDate( $this->start_date, $hash['sdate'], $hash['stime'] );
		$this->makeDate( $this->end_date, $hash['edate'], $hash['etime'] );
		
		isset( $hash['description'] ) && $this->description = $hash['event_description'];
		switch( $hash['recurs'] ) {
			case 'H':
				$this->times_recuring = 3600; break;
			case 'D':
				$this->times_recuring = 86400; break;
			case 'W':
				$this->times_recuring = 86400*7; break;
			case 'W2':
				$this->times_recuring = 86400*14; break;
			case 'M':
				$this->times_recuring = 86400*31; break; // TODO fixme
				
			default:
				$this->times_recuring = 0; break;
		}
		if( $this->recurs ) {
			if( intval( $hash['times_recuring'] ) > 0 )
				$this->times_recuring = $hash['times_recuring'];
			else
				$this->times_recuring = 1;
		}
		if( intval( $hash['remind'] ) > 0 )
			$this->remind = $hash['remind'];
		else
			$this->remind = 0;
		
		// TODO : safety checks that return error message in case of error
		
		return NULL;
	}

	
	function Store()
	{
			
		if( $this->event_id ) {
			$ret = DB_updateObject( 'events', $this, 'event_id' );
			AppLog( "calendar/event/$this->event_id", 'update', $ret );
			if( ! $ret )
				return DB_Error();
			else
				return "Event updated";
		} else {
			$ret = DB_insertObject( 'events', $this, 'event_id' );
			AppLog( "calendar/event/$this->event_id", 'insert', $ret );
			if( ! $ret )
				return DB_Error();
			else
				return "Event added";
		}
	}

	function Delete()
	{
		$ret = DB_delete( 'events', 'event_id', $this->event_id );
		AppLog( "calendar/event/$this->event_id", 'delete', $ret );
		if( ! $ret )
			return DB_Error();
		else
			return "Event deleted";
	}	
}

?>
