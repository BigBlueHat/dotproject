<?php /* FORUMS $Id$ */

require_once( $AppUI->getSystemClass( 'libmail' ) );

class CForum {
	var $forum_id = NULL;
	var $forum_project = NULL;
	var $forum_status = NULL;
	var $forum_owner = NULL;
	var $forum_name = NULL;
	var $forum_create_date = NULL;
	var $forum_last_date = NULL;
	var $forum_last_id = NULL;
	var $forum_message_count = NULL;
	var $forum_description = NULL;
	var $forum_moderated = NULL;

	function CForum() {
		// empty constructor
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return "CForum::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		if ($this->forum_id === NULL) {
			return 'forum_id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return "CForum::store-check failed<br />$msg";
		}
		if( $this->forum_id ) {
			$ret = db_updateObject( 'forums', $this, 'forum_id', false ); // ! Don't update null values
			if($this->forum_name) {
				// when adding messages, this functon is called without first setting 'forum_name'
				addHistory("Updated forum '" . $this->forum_name . "'");
			}
		} else {
			$this->forum_create_date = db_datetime( time() );
			$ret = db_insertObject( 'forums', $this, 'forum_id' );
			addHistory("Added new forum '" . $this->forum_name . "'");
		}
		if( !$ret ) {
			return "CForum::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM forums WHERE forum_id = $this->forum_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
		$sql = "DELETE FROM forum_messages WHERE message_forum = $this->forum_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			addHistory("Deleted forum '". $this->forum_name . "'");
			return NULL;
		}
	}
}

class CForumMessage {
	var $message_id = NULL;
	var $message_forum = NULL;
	var $message_parent = NULL;
	var $message_author = NULL;
	var $message_editor = NULL;
	var $message_title = NULL;
	var $message_date = NULL;
	var $message_body = NULL;
	var $message_published = NULL;

	function CForumMessage() {
		// empty constructor
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return "CForumMessage::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		if ($this->message_id === NULL) {
			return 'message_id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return "CForumMessage::store-check failed<br />$msg";
		}
		if( $this->message_id ) {
			$ret = db_updateObject( 'forum_messages', $this, 'message_id', false ); // ! Don't update null values
		} else {
			$this->message_date = db_datetime( time() );
			$new_id = db_insertObject( 'forum_messages', $this, 'message_id' ); ## TODO handle error now
			echo db_error(); ## TODO handle error better

			$sql = "SELECT count(message_id),
			MAX(message_date)
			FROM forum_messages
			WHERE message_forum = $this->message_forum";

			$res = db_exec( $sql );
			echo db_error(); ## TODO handle error better
			$reply = db_fetch_row( $res );

			//update forum descriptor
			$forum = new CForum();
			$forum->forum_id = $this->message_forum;
			$forum->forum_message_count = $reply[0];
			$forum->forum_last_date = $reply[1];
			$forum->forum_last_id = $this->message_id;

			$forum->store(); ## TODO handle error now

			return $this->sendWatchMail( false );
		}

		if( !$ret ) {
			return "CForumMessage::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM forum_messages WHERE message_id = $this->message_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

	function sendWatchMail( $debug=false ) {
		GLOBAL $AppUI, $debug;
		$subj_prefix = $AppUI->_('forumEmailSubj');
		$body_msg = $AppUI->_('forumEmailBody');

		$sql = "
		SELECT DISTINCT user_email, user_id, user_first_name, user_last_name
		FROM users, forum_watch
		WHERE user_id = watch_user
			AND (watch_forum = $this->message_forum OR watch_topic = $this->message_parent)
		";
	##echo "<pre>$sql</pre>";##
		if (!($res = db_exec( $sql ))) {
			return;
		}
		if (db_num_rows( $res ) < 1) {
			return;
		}

		$mail = new Mail;
		$mail->Subject( "$subj_prefix $this->message_title", isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "");

		$body = "$body_msg";
		$body .= "\n{$AppUI->cfg['base_url']}/index.php?m=forums&a=viewer&forum_id=$this->message_forum";
		$body .= "\n\n$this->message_title";
		$body .= "\n\n$this->message_body";
		
		$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : ""  );
		$mail->From( $AppUI->_('forumEmailFrom') );

		while ($row = db_fetch_assoc( $res )) {
			if ($mail->ValidEmail( $row['user_email'] )) {
				$mail->To( $row['user_email'], true );
				$mail->Send();
				//echo '<textarea cols=80 rows=15>';print_r($mail);echo '</textarea>';die;
			}
		}
		return;
	}
}
?>
