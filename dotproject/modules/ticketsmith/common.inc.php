<?php

/* $Id$ */

/* program info */
$program = "Dotproject";
$version = "0.6.3";
$xmailer = "dotproject (http://dotproject.net/)";

/* error handler */
function fatal_error ($reason) {

    die($reason);

}


/* do a MySQL query */
function do_query ($query) {
	$result = @mysql_query($query);
	if (!$result) {
		fatal_error("A database query error has occurred!<br>".mysql_error());
	} else {
		return($result);
	}
	
}

/* get single result value */
function query2result ($query) {

	$result = do_query($query);
	$row = @mysql_result($result, 0);
	return($row);

}

/* get result in numeric array */
function query2array ($query) {

	$result = do_query($query);
	$row = @mysql_fetch_row($result);
	return($row);

}

/* get result in associative array */
function query2hash ($query) {

	$result = do_query($query);
	$row = @mysql_fetch_array($result);
	return($row);
	
}

/* get row of result */
function result2row ($result) {

    $row = @mysql_fetch_row($result);
    return($row);

}

/* get row of result in hash */
function result2hash ($result) {

    $row = @mysql_fetch_array($result);
    return($row);

}

/* find number of rows in query result */
function number_rows ($result) {

    $number_rows = @mysql_num_rows($result);
    return($number_rows);

}

/* put rows from a column into an array */
function column2array ($query) {

    $result = do_query($query);
    while ($row = @mysql_fetch_array($result)) {
        $array[] = $row[0];
    }
    return($array);

}

/* create drop-down box */
function create_selectbox ($name, $options, $selected) {
	$output= "";
	
    $output .= "<select name=\"$name\" onChange=\"document.form.submit()\" class=\"text\">\n";
	while(list($key, $val) = each($options)) {
		$output .= "<option value=\"$key\"";
		if ($key == $selected) {
			$output .= " selected";
		}
		$output .= ">$val\n";
		//$loop++;
	}
	$output .= "</select>\n";
    
    return($output);

}

/* escape special characters */
function escape_string ($string) {
    
    if (!get_magic_quotes_gpc()) {
        $string = addslashes($string);
    }
    return($string);

}

/* format "time ago" date string */
function get_time_ago ($timestamp) {

    $elapsed_seconds = time() - $timestamp;
    
    if ($elapsed_seconds < 60) { // seconds ago
        if ($elapsed_seconds) {
            $interval = $elapsed_seconds;
        }
        else {
            $interval = 1;
        }
        $output = "$interval second";
    }
    elseif ($elapsed_seconds < 3600) { // minutes ago
        $interval = round($elapsed_seconds / 60);
        $output = "$interval minute";
    }
    elseif ($elapsed_seconds < 86400) { // hours ago
        $interval = round($elapsed_seconds / 3600);
        $output = "$interval hour";
    }
    elseif ($elapsed_seconds < 604800) { // days ago
        $interval = round($elapsed_seconds / 86400);
        $output = "$interval day";
    }
    elseif ($elapsed_seconds < 2419200) { // weeks ago
        $interval = round($elapsed_seconds / 604800);
        $output = "$interval week";
    }
    elseif ($elapsed_seconds < 29030400) { // months ago
        $interval = round($elapsed_seconds / 2419200);
        $output = "$interval month";
    }
    else { // years ago
        $interval = round($elapsed_seconds / 29030400);
        $output = "$interval year";
    }
    
    if ($interval > 1) {
        $output .= "s";
    }
    
    $output .= " ago";
    
    return($output);
        
}

/* smart word wrapping */
function smart_wrap ($text, $width) {

    if (function_exists("wordwrap")) {
        if (preg_match("/[^\\n]{100,}/", $text)) {
            $text = wordwrap($text, $width);
        }
    }
    else {
        $text = "Wordwrap unsupported in PHP " . phpversion() . "\n\n";;
        $text .= "Please adjust your Ticketsmith configuration and/or upgrade PHP\n";
    }

    return($text);

}

/* format display field */
function format_field ($value, $type, $ticket = NULL) {

    global $CONFIG;
    global $AppUI;
    switch ($type) {
        case "user":
            if ($value) {
                $output = query2result("SELECT CONCAT_WS(' ',user_first_name,user_last_name) as name FROM users WHERE user_id = '$value'");
            } else {
                $output = "-";
            }
            break;
        case "status":
            $output = create_selectbox("type_toggle", array("Open" => "Open", "Processing" => "Processing", "Closed" => "Closed", "Deleted" => "Deleted"), $value);
            break;
        case "priority_view":
            $priority = $CONFIG["priority_names"][$value];
            $color = $CONFIG["priority_colors"][$value];
            if ($value == 3) {
                $priority = "<strong>$priority</strong>";
            }
            if ($value == 4) {
                $priority = "<blink><strong>$priority</strong></blink>";
            }
            $output = "<font color=\"$color\">$priority</font>";
            break;
        case "priority_select":
            $output = create_selectbox("priority_toggle", $CONFIG["priority_names"], $value);
            break;
        case "assignment":
            $options[0] = "-";
            $query = "SELECT user_id as id, CONCAT_WS(' ',user_first_name,user_last_name) as name FROM users";
            $result = do_query($query);
            while ($row = result2hash($result)) {
                $options[$row["id"]] = $row["name"];
            }
            $output = create_selectbox("assignment_toggle", $options, $value);
            break;
        case "view":
            if ($CONFIG["index_link"] == "latest") {
                $latest_value = query2result("SELECT ticket FROM tickets WHERE parent = '$value' ORDER BY ticket DESC LIMIT 1");
                if ($latest_value) {
                    $value = $latest_value;
                }
            }
            $output = "<a href=index.php?m=ticketsmith&a=view&ticket=$value>";
            $output .= "<img src=images/icons/pencil.gif border=0></a>";
            break;
	case "attach":
	    $output = "<A href=index.php?m=ticketsmith&a=attach&ticket=$value>";
	    $output .= "Link</a>";
	    break;
	case "doattach":
	    $output = "<A href=index.php?m=ticketsmith&a=attach&newparent=$value&dosql=reattachticket&ticket=$ticket>";
	    $output .= "Link</a>";
	    break;
        case "open_date":
            $output = get_time_ago($value);
            if ($CONFIG["warning_active"]) {
                if (time() - $value > $CONFIG["warning_age"] * 3600) {
                    $output = "<font color=\"" . $CONFIG["warning_color"] . "\"><xb>" . $output . "</strong></font>";
                }
            }
            break;
        case "activity_date":
            if (!$value) {
                $output = "<em>none</em>";
            }
            else {
                $output = get_time_ago($value);
            }
            $latest_followup_type = query2result("SELECT type FROM tickets WHERE parent = '$ticket' ORDER BY timestamp DESC LIMIT 1");
            if ($latest_followup_type) {
                $latest_followup_type = preg_replace("/(\w+)\s.*/", "\\1", $latest_followup_type);
                $output .= " [$latest_followup_type]";
            }
            break;
        case "elapsed_date":
            $output = date($CONFIG["date_format"], $value);
            $time_ago = get_time_ago($value);
            $output .= " <em>($time_ago)</em>";
            break;
        case "body":
            $value = htmlspecialchars($value);
            $output = "<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\">\n";
            $output .= "<tr><td bgcolor=\"" . $CONFIG["ticket_color"] . "\">\n<tt><pre>\n";
            $url_find = "/(http|https|ftp|news|telnet|finger)(:\/\/[^ \">\\t\\r\\n]*)/";
            $url_replace = "<a href=\"\\1\\2\" target=\"new\">";
            $url_replace .= "<span style=\"font-size: 10pt;\">\\1\\2</span></a>";
            $value = preg_replace($url_find, $url_replace, $value);
            if ($CONFIG["wordwrap"]) {
                $output .= smart_wrap($value, 72);
            }
            else {
                $output .= $value;
            }
            $output .= "\n</pre></tt>\n</td></tr>\n</table>\n";
            break;
        case "followup":
            $output = "\n<tt>\n";
            $output .= "<textarea name=\"followup\" wrap=\"hard\" cols=\"72\" rows=\"20\">\n";
            $signature = query2result("SELECT user_signature FROM users WHERE user_id = '$AppUI->user_id'");
            if ($signature) {
                $output .= "\n";
                $output .= "-- \n";
                $output .= $signature;
            }
            $output .= "\n\n";
            $output .= "---- Original message ----\n\n";
            if ($CONFIG["wordwrap"]) {
                $value = smart_wrap($value, 70);
            }
            $value = htmlspecialchars($value);
            $lines = explode("\n", $value);
            for ($loop = 0; $loop < count($lines); $loop++) {
                $lines[$loop] = "&gt; " . $lines[$loop];
            }
            $output .= join("\n", $lines);
            $output .= "\n</textarea>\n";
            $output .= "</tt>\n";
            break;
        case "subject":
            $value = preg_replace("/\s*Re:\s*/i", "", $value);
            $value = preg_replace("/(\[\#\d+\])(\w+)/", "\\2", $value);
            $value = "Re: " . $value;
            $value = htmlspecialchars($value);
            @$output .= "<input type=\"text\" name=\"subject\" value=\"$value\" size=\"70\">\n";
            break;
        case "cc":
            $value = htmlspecialchars($value);
            $output = "<input type=\"text\" name=\"cc\" value=\"$value\" size=\"70\">";
            break;
        case "recipient":
            $value = htmlspecialchars($value);
            $output = "<input type=\"text\" name=\"recipient\" value=\"$value\" size=\"70\">";
            break;
        case "original_author":
            if ($value) {
                $value = ereg_replace("\"", "", $value);
                $output = htmlspecialchars($value);
            }
            else {
                $output = "<em>(original ticket author)</em>";
            }
            break;
        case "email":
            if ($value) {
                $value = ereg_replace("\"", "", $value);
                $output = htmlspecialchars($value);
            }
            else {
                $output = "<em>none</em>";
            }
            break;
        default:
            $output = $value ? htmlspecialchars($value) : "<em>none</em>";
    }
    return($output);

}

/* register login stuff */
//session_register("login_id");
//session_register("login_name");

/* figure out parent & type */
if (isset($ticket)) {
    list($ticket_type, $ticket_parent) = query2array("SELECT type, parent FROM tickets WHERE ticket = '$ticket'");
}


?>
