<?php /* $Id$ */
##
## Calendar classes
##
//require_once( "{$AppUI->cfg['root_dir']}/classdefs/date.php" );

class CMonthCalendar {
    var $this_month;
    var $prev_month;
    var $next_month;
    var $prev_year;
    var $next_year;

    var $styleTitle;
    var $styleMain;

    var $callback;

    var $showHeader;
    var $showArrows;
    var $showDays;
    var $showWeek;
    var $showEvents;

    var $shDayNames = array( "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" );
    var $tiMoNames = array( 'J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D' );
    
    var $dayFunc;
    var $weekFunc;

    function CMonthCalendar( $dateObj=null ) {
        if ($dateObj) {
            $this->setDate( $dateObj );
        }
        $this->classes = array();
        $this->callback = '';
        $this->showTitle = true;
        $this->showArrows = true;
        $this->showDays = true;
        $this->showWeek = true;
        $this->showEvents = true;
        
        $this->styleTitle = '';
        $this->styleMain = '';

        $this->dayFunc = '';
        $this->weekFunc = '';

        $this->events = array();
    }
// setting functions
    function setDate( $dateObj ) {
        $this->this_month = $dateObj;

        $this->prev_month = $this->this_month;
        $this->prev_month->addMonths( -1 );

        $this->next_month = $this->this_month;
        $this->next_month->addMonths( +1 );

        $this->prev_year = $this->this_month;
        $this->prev_year->addYears( -1 );

        $this->next_year = $this->this_month;
        $this->next_year->addYears( +1 );
    }

    function setStyles( $title, $main ) {
        $this->styleTitle = $title;
        $this->styleMain = $main;
    }

    function setLinkFunctions( $day='', $week='' ) {
        $this->dayFunc = $day;
        $this->weekFunc = $week;
    }

    function setCallback( $function ) {
        $this->callback = $function;
    }

    function setEvents( $e ) {
        $this->events = $e;
    }
// drawing functions
    function show() {
        $s = '';
        if ($this->showTitle) {
            $s .= $this->_drawTitle();
        }
        $s .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\" class=\"" . $this->styleMain . "\">\n";

        if ($this->showDays) {
            $s .= $this->_drawDays();
        }
        
        $s .= $this->_drawMain();

        $s .= "</table>\n";

        return $s;
    }

    function _drawTitle() {
        $url = $_SERVER['SCRIPT_NAME'] . ($_SERVER['QUERY_STRING'] ? "?{$_SERVER['QUERY_STRING']}&" : '?');

        //$s = '<table border="0" cellspacing="0" cellpadding="3" width="100%" class="'.$this->styleTitle.'">';
        $s = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"3\" width=\"100%\" class=\"" . $this->styleTitle . "\">\n";
        $s .= "    <tr>\n";

        if ($this->showArrows) {
            $href = $url.'uts='.$this->prev_month->getTimestamp().($this->callback ? '&callback='.$this->callback : '');
            $s .= "        <td align=\"left\">";
            $s .= '<a href="'.$href.'"><img src="./images/prev.gif" width="16" height="16" alt="previous month" border="0" /></a>';
            $s .= "</td>\n";

        }
        
        $s .= "        <th width=\"99%\" align=\"center\">";
        $s .= $this->this_month->toString( "%B %Y" );
        $s .= "</th>\n";

        if ($this->showArrows) {
            $href = $url.'uts='.$this->next_month->getTimestamp().($this->callback ? '&callback='.$this->callback : '');
            $s .= "        <td align=\"right\">";
            $s .= '<a href="'.$href.'"><img src="./images/next.gif" width="16" height="16" alt="next month" border="0" /></a>';
            $s .= "</td>\n";
        }

        $s .= "    </tr>\n";
        $s .= "</table>\n";

        return $s;
    }

    function _drawDays() {
        $s = $this->showWeek ? "        <th>&nbsp;</th>\n" : "";
        $day = new CDate();
        $day->setFormat( "%a" );
        $day->setWeekday( LOCALE_FIRST_DAY );
        for ($i=LOCALE_FIRST_DAY; $i < 7 + LOCALE_FIRST_DAY; $i++) {
//      for ($i=0; $i < 7; $i++) {
            $s .= "        <th width=\"14%\">".$day->toString()."</th>\n";
            $day->addDays(1);
        }
        return "    <tr>\n$s    </tr>\n";
    }

    function _drawMain() {
        GLOBAL $AppUI;

        $show_day = $this->this_month;
        $show_day->setDay( 1 );
        $show_day->setFormat( $AppUI->getPref( 'SHDATEFORMAT' ) );

    // pre-pad the calendar
        $pad = $this->this_month->getStartSpaces();
        $p = '';
        for ($i=0; $i < $pad; $i++) {
            $p .= "        <td class=\"empty\">&nbsp;</td>\n";
        }
        $w = $this->showWeek ? $this->_drawWeek( $show_day ) : '';
        $s = $p ? "    <tr>\n$w$p" : "";

    // fill the calendar
        $n = $this->this_month->daysInMonth();
        for ($i=0; $i < $n; $i++) {
            $day = $show_day->getWeekday();
            $class = 'day';
            //if ($show_day->D == $this->this_month->D) {
            if ($show_day->isToday()) {
                $class = 'today';
            } else if ($day < 1 || $day > 5) {
                $class = 'weekend';
            }
            // start new row
            if ($day == LOCALE_FIRST_DAY) {
                $w = $this->showWeek ? $this->_drawWeek( $show_day ) : '';
                $s .= "    <tr>\n$w";
            }
            $s .= "        <td class=\"$class\">";
            if ($this->dayFunc) {
                $href = "javascript:$this->dayFunc(".$show_day->getTimestamp().",'".$show_day->toString()."')";
                $s .= "<a href=\"$href\" class=\"$class\">".$show_day->D."</a>";
            } else {
                $s .= $show_day->D;
            }
            if ($this->showEvents) {
                $s .= $this->_drawEvents( $show_day->D );
            }
            $s .= "</td>\n";

            // finish a row
            if ($day == 6 + LOCALE_FIRST_DAY) {
                $s .= "    </tr>\n";
            }
            $show_day->addDays( 1 );
        }

        // post-pad the calendar
        $pad = 7 - (($pad + $this->this_month->daysInMonth()) % 7);
        if ($pad < 7) {
            for ($i=0; $i < $pad; $i++) {
                $s .= "        <td class=\"empty\">&nbsp;</td>\n";
            }
            $s .= "    </tr>\n";
        }
    /*
    */
        // print it
        return $s;
    }

    function _drawWeek( $dateObj ) {
        $href = "javascript:$this->weekFunc(".$dateObj->getTimestamp().",'".$dateObj->toString()."')";
        $w = "        <td class=\"week\">";
        $w .= $this->dayFunc ? "<a href=\"$href\">" : '';
        $w .= '<img src="./images/view.week.gif" width="16" height="15" border="0" alt="Week View" /></a>';
        $w .= $this->dayFunc ? "</a>" : '';
        $w .= "</td>\n";
        return $w;
    }

    function _drawEvents( $day ) {
        $s = '';
        if (!isset( $this->events[$day] )) {
            return '';
        }
        $events = $this->events[$day];
        foreach ($events as $e) {
            $href = isset($e['href']) ? $e['href'] : null;
            $alt = isset($e['alt']) ? $e['alt'] : null;

            $s .= "<br />\n";
            $s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
            $s .= "{$e['text']}";
            $s .= $href ? '</a>' : '';
        }
        return $s;
    }
}

##
## CEvent Class
##
class CEvent {
    var $event_id = NULL;
    var $event_title = NULL;
    var $event_start_date = NULL;
    var $event_end_date = NULL;
    var $event_parent = NULL;
    var $event_description = NULL;
    var $event_times_recuring = NULL;
    var $event_recurs = NULL;
    var $event_remind = NULL;
    var $event_icon = NULL;
    var $event_owner = NULL;
    var $event_project = NULL;
    var $event_private = NULL;

    function CEvent() {
        // empty constructor
    }

    function check() {
        if (!$this->event_private) {
            $this->event_private = '0';
        }
        // TODO
        return NULL; // object is ok
    }

    function bind( $hash ) {
        if (!is_array( $hash )) {
            return get_class( $this )."::bind failed";
        }
        bindHashToObject( $hash, $this );
    }

    function store() {
        $msg = $this->check();
        if( $msg ) {
            return get_class( $this )."::store-check failed";
        }
        if( $this->event_id ) {
            $ret = db_updateObject( 'events', $this, 'event_id', false );
        } else {
            $ret = db_insertObject( 'events', $this, 'event_id' );
        }
        if( !$ret ) {
            return get_class( $this )."::store failed <br />" . db_error();
        } else {
            return NULL;
        }
    }

    function delete() {
        $sql = "DELETE FROM events WHERE event_id = $this->event_id";
        if (!db_exec( $sql )) {
            return db_error();
        } else {
            return NULL;
        }
    }
}
?>