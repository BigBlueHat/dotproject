function toggle_users(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "inline" : "none";
}

<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($showEditCheckbox) {
?>
function checkAll(project_id) {
        var f = eval( 'document.assFrm' + project_id );
        var cFlag = f.master.checked ? false : true;

        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == cFlag && e.name != 'master')
                {
                         e.checked = !e.checked;
                }
        }
}

function chAssignment(project_id, rmUser, del) {
        var f = eval( 'document.assFrm' + project_id );
        var fl = f.add_users.length-1;
        var c = 0;
        var a = 0;

        f.hassign.value = "";
        f.htasks.value = "";

        // harvest all checked checkboxes (tasks to process)
        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == true && e.name != 'master')
                {
                         c++;
                         f.htasks.value = f.htasks.value +","+ e.value;
                }
        }

        // harvest all selected possible User Assignees
        for (fl; fl > -1; fl--){
                if (f.add_users.options[fl].selected) {
                        a++;
                        f.hassign.value = "," + f.hassign.value +","+ f.add_users.options[fl].value;
                }
        }

        if (del == true) {
                        if (c == 0) {
                                 alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
                        } else if (a == 0 && rmUser == 1){
                                alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
                        } else {
                                if (confirm( '<?php echo $AppUI->_('Are you sure you want to unassign the User from Task(s)?', UI_OUTPUT_JS); ?>' )) {
                                        f.del.value = 1;
                                        f.rm.value = rmUser;
                                        f.project_id.value = project_id;
                                        f.submit();
                                }
                        }
        } else {

                if (c == 0) {
                        alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
                } else {

                        if (a == 0) {
                                alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
                        } else {
                                f.rm.value = rmUser;
                                f.del.value = del;
                                f.project_id.value = project_id;
                                f.submit();

                        }
                }
        }
}
<?php } ?>