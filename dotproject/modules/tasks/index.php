<?php
//        task index

$project_id = isset( $HTTP_GET_VARS['project_id'] ) ? $HTTP_GET_VARS['project_id'] : 0;
$project_id = isset( $HTTP_COOKIE_VARS['cookie_project'] ) ? $HTTP_COOKIE_VARS['cookie_project'] : $project_id;
$project_id = isset( $HTTP_GET_VARS['cookie_project'] ) ? $HTTP_GET_VARS['cookie_project'] : $project_id;
$project_id = isset( $HTTP_POST_VARS['cookie_project'] ) ? $HTTP_POST_VARS['cookie_project'] : $project_id;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
        echo '<script language="javascript">
        window.location="./index.php?m=help&a=access_denied";
        </script>
';
}

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name,
        count(t1.task_id) as total_tasks,
        sum(t1.task_duration*t1.task_precent_complete)/sum(t1.task_duration) as project_precent_complete
from permissions, projects
left join tasks t1 on projects.project_id = t1.task_project
where project_active <> 0
        and permission_user = $user_cookie
        and permission_value <> 0
        and (
                (permission_grant_on = 'all')
                or (permission_grant_on = 'projects' and permission_item = -1)
                or (permission_grant_on = 'projects' and permission_item = project_id)
                )
group by project_id
order by project_name
";
//echo "<pre>$psql</pre>";
$prc = mysql_query($psql);
echo mysql_error();
$pnums = mysql_num_rows($prc);

$projects = array();
for ($x=0; $x < $pnums; $x++) {
        $z = mysql_fetch_array( $prc, MYSQL_ASSOC );
        $projects[$z["project_id"]] = $z;
}

// get any specifically denied tasks
$dsql = "
select task_id
from tasks, permissions
where
permission_user = $user_cookie
and permission_grant_on = 'tasks'
and permission_item = task_id
and permission_value = 0
";
$drc = mysql_query($dsql);
echo mysql_error();
$deny = array();
while ($row = mysql_fetch_array( $drc, MYSQL_NUM )) {
        $deny[] = $row[0];
}

// pull tasks
$tsql = "
SELECT tasks.task_id, task_parent, task_name, task_start_date, task_end_date,
        task_priority, task_precent_complete, task_duration, task_order, task_project,
        project_name
from tasks, user_tasks
left join projects on project_id = task_project
where project_active <> 0"
.($project_id ? "\nand task_project = $project_id" : '')
."
        and task_project = projects.project_id
        and user_tasks.user_id = $user_cookie
        and user_tasks.task_id = tasks.task_id"
."
order by project_id, task_order
";
//echo "<pre>$tsql</pre>";

$ptrc = mysql_query($tsql);
$nums = mysql_num_rows($ptrc);
echo mysql_error();
$orrarr[] = array("task_id"=>0, "order_up"=>0, "order"=>"");

//pull the tasks into an array
for ($x=0; $x < $nums; $x++) {
        $row = mysql_fetch_array( $ptrc, MYSQL_ASSOC );
        $projects[$row['task_project']]['tasks'][] = $row;
}

//This kludgy function echos children tasks as threads

function showtask( &$a, $level=0 ) {
        global $done;
        $done[] = $a['task_id']; ?>
        <TR bgcolor="#f4efe3">
        <TD><A href="./index.php?m=tasks&a=addedit&task_id=<?php echo $a["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a></td>
        <TD align="right"><?php echo intval($a["task_precent_complete"]);?>%</td>
        <TD>
        <?php if ($a["task_priority"] < 0 ) {
                echo "<img src='./images/icons/low.gif' width=13 height=16>";
        } else if ($a["task_priority"] > 0) {
                echo "<img src='./images/icons/" . $a["task_priority"] .".gif' width=13 height=16>";
        }?>
        </td>
        <TD width=90%>

        <?php if ($level == 0) { ?>
        <img src="./images/icons/updown.gif" width="10" height="15" border=0 usemap="#arrow<?php echo $a["task_id"];?>">
        <map name="arrow<?php echo $a["task_id"];?>"><area coords="0,0,10,7" href=<?php echo "./index.php?m=tasks&a=reorder&task_project=" . $a["task_project"] . "&task_id=" . $a["task_id"] . "&order=" . $a["task_order"] . "&w=u";?>>
        <area coords="0,8,10,14" href=<?php echo "./index.php?m=tasks&a=reorder&task_project=" . $a["task_project"] . "&task_id=" . $a["task_id"] . "&order=" . $a["task_order"] . "&w=d";?>></map>

        <?php } else {
                for ($y=0; $y < $level; $y++) {
                        if ($y+1 == $level) {
                                echo "<img src=./images/corner-dots.gif width=16 height=12  border=0>";
                        } else {
                                echo "<img src=./images/shim.gif width=16 height=12  border=0>";
                        }
                }
        }?>

        <A href="./index.php?m=tasks&a=view&task_id=<?php echo $a["task_id"];?>"><?php echo $a["task_name"];?></a></td>
        <TD nowrap><?php echo fromDate(substr($a["task_start_date"], 0, 10));?></td>
        <TD>
        <?php if ($a["task_duration"] > 24 ) {
                $dt = "day";
                $dur = $a["task_duration"] / 24;
        } else {
                $dt = "hour";
                $dur = $a["task_duration"];
        }
        if ($dur > 1) {
                $dt.="s";
        }
        echo $dur . " " . $dt ;
        ?>
        </td>
        <TD nowrap><?php echo fromDate(substr($a["task_end_date"], 0, 10));?></td>
        </tr>
<?php }

function findchild( &$tarr, $parent, $level=0 ){
        GLOBAL $projects;
        $level = $level+1;
        $n = count( $tarr );
        for ($x=0; $x < $n; $x++) {
                if($tarr[$x]["task_parent"] == $parent && $tarr[$x]["task_parent"] != $tarr[$x]["task_id"]){
                        showtask( $tarr[$x], $level );
                        findchild( $tarr, $tarr[$x]["task_id"], $level);
                }
        }
}
?>

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form action="<?php echo $REQUEST_URI;?>" method="post" name="pickProject">
<TR>
        <TD><img src="./images/icons/tasks.gif" alt="Tasks" border="0" width="44" height="38"></td>
        <TD nowrap>
                <span class="title">Tasks: <?php if($project_id == 0){echo "All";} else {echo @$projects[$project_id]["project_name"];} ;?></span>
        </td>
        <TD align="right" width="100%">
                Project:
                <select name="cookie_project" onChange="document.pickProject.submit()" style="font-size:8pt;font-family:verdana;">
                        <option value="0" <?php if($project_id == 0)echo " selected" ;?> >all
<?php
        reset($projects);
        while ( list($key, $val) = each($projects) ) {
                echo "<option value=" . $val["project_id"];
                if ($val["project_id"] == $project_id) {
                        echo " selected";
                }
                echo ">" . $val['project_name'];
} ?>
                </select><br>
                <?php include ("./includes/create_new_menu.php");?>
        </td>
</tr>
</form>
</TABLE>

<?php if(isset($message))echo $message;?>
<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
<TR style="border: outset #eeeeee 2px;">
        <TD class="mboxhdr" width="10">id</td>
        <TD class="mboxhdr" width="20">work</td>
        <TD class="mboxhdr" width="15" align="center">p</td>
        <TD class="mboxhdr" width=200>task name</td>
        <TD class="mboxhdr">start date</td>
        <TD class="mboxhdr">duration&nbsp;&nbsp;</td>
        <TD class="mboxhdr">finish date</td>
</tr>
<?php
//echo '<pre>'; print_r($projects); echo '</pre>';
reset( $projects );
while (list( $k, ) = each( $projects ) ) {
        $p = &$projects[$k];
        $tnums = count( $p['tasks'] );
// don't show project if it has no tasks
        if ($tnums) {
//echo '<pre>'; print_r($p); echo '</pre>';
?>
<TR>
        <TD colspan=9  bgcolor="#f4efe3">
                <table width="100%" border=0>
                <tr>
                        <TD nowrap style="border: outset #eeeeee 2px;" bgcolor="<?php echo $p["project_color_identifier"];?>">
                                <A href="./index.php?m=projects&a=view&project_id=<?php echo $k;?>">
                                <span style='color:<?php echo bestColor( $p["project_color_identifier"] ); ?>;text-decoration:none;'><B><?php echo $p["project_name"];?></b></span></a>
                        </td>
                        <TD width="<?php echo (101 - intval($p["project_precent_complete"]));?>%">
                                <?php echo (intval($p["project_precent_complete"]));?>%
                        </td>
                </tr>
                </table>
        </td>
</tr>
<?php
                $done = array();
                for ($i=0; $i < $tnums; $i++) {
                        $t = $p['tasks'][$i];
                        if ($t["task_parent"] == $t["task_id"]) {
                                showtask( $t );
                                findchild( $p['tasks'], $t["task_id"] );
                        }
                }
        // check that any 'orphaned' user tasks are also display
                for ($i=0; $i < $tnums; $i++) {
                        if ( !in_array( $p['tasks'][$i]["task_id"], $done )) {
                                showtask( $p['tasks'][$i], 1 );
                        }
                }
        }
}
?>
</TABLE>
<TABLE height="100%">
        <TR><TD>&nbsp; </TD></TR>
</TABLE>