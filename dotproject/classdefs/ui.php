<?php

/*
	Application User Interface class
*/
class CAppUI {
	var $state;		// generic array for holding the state of anything
// current user parameters
	var $user_id;
	var $user_first_name;
	var $user_last_name;
	var $user_company;
	var $user_department;
	
	function CAppUI() {
		$this->state = array();

		$this->user_id = -1;
		$this->user_first_name = '';
		$this->user_last_name = '';
		$this->user_company = 0;
		$this->user_department = 0;
	}

	function setState( $label, $tab ) {
		$this->state[$label] = $tab;
	}

	function getState( $label ) {
		return array_key_exists( $label, $this->state) ? $this->state[$label] : 0;
	}

	function login( $username, $password ) {
		GLOBAL $secret, $debug;
		$sql = "
		SELECT
			user_id, user_first_name, user_last_name, user_company, user_department
		FROM users,  permissions
		WHERE user_username = '$username'
			AND user_password = password('$password') 
			AND users.user_id = permissions.permission_user
			AND permission_value <> 0
		";

		if ($debug) {
			echo "DEBUGGING:<br>SQL=<pre><font color=blue>$psql</font></pre>";
		}
		if( !db_loadObject( $sql, $this, __LINE__ ) ) {
			return false;
		}
		$this->secret = md5( $this->user_first_name.$secret.$this->user_last_name );
		
	// legacy cookies
		$this->logout();
		setcookie( "user_cookie", $this->user_id );
		setcookie( "thisuser", "$this->user_id|$this->user_first_name|$this->user_last_name|$this->user_company|$this->user_department" );

		return true;
	}

	function logout() {
	// legacy cookies
		setcookie( 'user_cookie', '', time() - 3600 );
		setcookie( 'thisuser', '', time() - 3600 );
	}

	function doLogin() {
		return ($this->user_id < 0) ? true : false;
	}
}

/*
	Tabbed box class
*/
class CTabBox {
	var $tabs=NULL;
	var $active=NULL;
	var $baseHRef=NULL;
	var $baseInc;

	function CTabBox( $baseHRef='', $baseInc='.', $active=0 ) {
		$this->tabs = array();
		$this->active = $active;
		$this->baseHRef = ($baseHRef ? "$baseHRef&" : "?");
		$this->baseInc = $baseInc;
	}

	function add( $file, $title ) {
		$this->tabs[] = array( $file, $title );
	}

	function show( $extra='' ) {
		reset( $this->tabs );
		$s = '';
	// tabbed / flat view options
		$s .= '<table border="0" cellpadding="2" cellspacing="0" width="98%"><tr><td nowrap="nowrap">';
		$s .= '<a href="'.$this->baseHRef.'tab=0">tabbed</a> : ';
		$s .= '<a href="'.$this->baseHRef.'tab=-1">flat</a>';
		$s .= '</td>'.$extra.'</tr></table>';
		echo $s;

		if ($this->active < 0) {
		// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="98%">';
			foreach ($this->tabs as $v) {
				echo "<tr><td><b>$v[1]</b></td></tr>";
				echo "<tr><td>";
				include "$this->baseInc/$v[0].php";
				echo "</td></tr>";
			}
			echo '</table>';
		} else {
		// tabbed view
			$s = '<table width="98%" border=0 cellpadding="3" cellspacing=0><tr>';
			foreach( $this->tabs as $k => $v ) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$s .= '<td nowrap="nowrap" class="tabsp"><img src="./images/shim.gif" height=1 width=1></td>';
				$s .= '<td nowrap="nowrap" class="'.$class.'"><a href="'.$this->baseHRef.'tab='.$k.'">'.$v[1].'</a></td>';
			}
			$s .= '<td nowrap="nowrap" class="tabsp" width="100%">&nbsp;</td>';
			$s .= '</tr><tr><td width="100%" colspan="99" class="tabox">';
			echo $s;
			require $this->baseInc.'/'.$this->tabs[$this->active][0].'.php';
			echo '</td></tr></table>';
		}
	}
}

?>