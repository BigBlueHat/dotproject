<?php  // $Id$
global $AppUI, $do_write_cfg, $dPrunLevel, $Installer, $tab;
if (!$Installer->cfgFileCreated && !empty($Installer->various[do_write_cfg])) {
?>
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="100%" align="center">
        <tr>
                <td colspan="2" class="item"> Your configuration file or directory is not writable,
                or there was a problem creating the configuration file. You'll have to
                create or edit the file ./includes/config.php and fill in (copy & paste) the following code by hand:
                </td>
        </tr>
<tr>
        <td colspan="2" align="center">
                <textarea rows="60" cols="120" name="cfgcode" onclick="javascript:this.form.cfgcode.focus();this.form.cfgcode.select();"><?php echo htmlspecialchars( $Installer->cfgFilePrepare() );?></textarea>
        </td>
</tr>
</table>
<?php
}
else {
echo '<form name="cfgFrm" action="index.php?m=install&tab='.$tab.'" method="post">';
?>
<input type="hidden" name="dosql" value="do_write_cfg" />
<input type="hidden" name="various[dummy]" value="NULL" />
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="100%" align="center">
        <tr>
            <td class="title" colspan="2"><?php echo $AppUI->_('Database Setup Results'); ?></td>
        </tr>
<?php if ($Installer->dbConfigured) { ?>
        <?php if ($Installer->dbCreated) { ?>
        <tr>
            <td class="item" colspan="2"><?php echo $AppUI->_('dbCreatedValues'); ?>:</td>
        </tr>
        <?php } else { ?>
        <tr>
            <td class="item" colspan="2"><?php echo $AppUI->_('dbConfiguredValues'); ?>:</td>
        </tr>
        <?php } ?>
	 <tr>
            <td class="item" width="20%"><?php echo $AppUI->_('Database Type'); ?></td>
            <td align="left"><input disabled class="button" type="text"  name="pd[dbtype]" value="<?php echo $Installer->cfg['dbtype'];?>" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Database Host'); ?></td>
            <td align="left"><input disabled class="button" type="text"  name="pd[dbhost]" value="<?php echo $Installer->cfg['dbhost'];?>" /></td>
        </tr>
         <tr>
            <td class="item"><?php echo $AppUI->_('Database Name'); ?></td>
            <td align="left"><input disabled class="button" type="text"  name="pd[dbname]" value="<?php echo $Installer->cfg['dbname'];?>" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Database User'); ?></td>
            <td align="left"><input disabled class="button" type="text"  name="pd[dbuser]" value="<?php echo $Installer->cfg['dbuser'];?>" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Database User Password'); ?></td>
            <td align="left"><input disabled class="button" type="text"  name="pd[dbpass]" value="<?php echo $Installer->cfg['dbpass'];?>" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Database Port'); ?></td>
            <td align="left"><input disabled class="button" type="text"  name="pd[dbport]" value="<?php echo $Installer->cfg['dbport'];?>" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Database Persistent Connection'); ?></td>
            <td align="left"><input disabled type="checkbox" name="pd[dbpersist]" value="true" <?php echo ($Installer->cfg['dbpersist']==true) ? 'checked="checked"' : ''; ?>" /></td>
        </tr>
<?php } else { ?>
 	<tr>
            <td class="error" colspan="2">It seems that there has not been created a database yet. It is strongly recommended that you install and configure
	    a database before.</td>
        </tr>
<?php } ?>
        <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="title" colspan="2"><?php echo $AppUI->_('Important Settings'); ?></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Root Directory'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[root_dir]" value="<?php echo $Installer->cfg['root_dir']; ?>" title="The Root Directory for dotProject" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Base URL'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[base_url]" value="<?php echo $Installer->cfg['base_url']; ?>" title="" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Site Domain'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[site_domain]" value="<?php echo $Installer->cfg['site_domain']; ?>" title="" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Page Title'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[page_title]" value="<?php echo $Installer->cfg['page_title']; ?>" title="The Title shown in your Browser and in the dotProject Head" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Organisation Name'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[company_name]" value="<?php echo $Installer->cfg['company_name']; ?>" title="The Name of your Organization. It is also the Title for the Login Screen." /></td>
        </tr>
         <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="title" colspan="2"><?php echo $AppUI->_('Additional Settings'); ?></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Host Locale'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[host_locale]" value="<?php echo $Installer->cfg['host_locale']; ?>" title="The Language the Login Screen will be in" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Currency Symbol'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[currency_symbol]" value="<?php echo $Installer->cfg['currency_symbol']; ?>" title="Define your localized Currency Symbol. Use '#8364;' preceded by '&' for the EURO sign. Check http://www.w3.org/TR/html401/sgml/entities.html for more info." /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Daily Working Hours'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[daily_working_hours]" value="<?php echo $Installer->cfg['daily_working_hours']; ?>" title="Sets the number of 'working' hours in a day." /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Start Hour of Day'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[cal_day_start]" value="<?php echo $Installer->cfg['cal_day_start']; ?>" title="Sets the Start Hour of Day View in Calendar." /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('End Hour of Day'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[cal_day_end]" value="<?php echo $Installer->cfg['cal_day_end']; ?>" title="Sets the End Hour of Day View in Calendar." /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Hour Incremention')."&nbsp;".$AppUI->_('[min]'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[cal_day_increment]" value="<?php echo $Installer->cfg['cal_day_increment']; ?>" title="Sets the Subdivision Fineness in Day View in Calendar. Data must be entered in minutes" /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('Working Days'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[cal_working_days]" value="<?php echo $Installer->cfg['cal_working_days']; ?>" title="Sets the Days of Week the Organization is working. 0 = Sunday." /></td>
        </tr>
        <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="title" colspan="2"><?php echo $AppUI->_('Settings for Advanced Users'); ?></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('User Interface Style'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[host_style]" value="<?php echo $Installer->cfg['host_style']; ?>" title="Sets the default User Interface Style. Available is 'default' and 'classic'." /></td>
        </tr>
        <tr>
            <td class="item"><?php echo $AppUI->_('JPGraphLibrary Locale'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[jpLocale]" value="<?php echo $Installer->cfg['jpLocale']; ?>" title="Sets the locale for the jpGraph library (used for GANTT Charts). Leave blank if you experience problems." /></td>
        </tr>

          <tr>
            <td class="item"><?php echo $AppUI->_('Check Legacy Passwords'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[check_legacy_passwords]" value="true" <?php echo ($Installer->cfg['check_legacy_passwords']==true) ? 'checked="checked"' : ''; ?> title="ONLY REQUIRED FOR UPGRADES prior to and including version 1.0 alpha 2!" /></td>
          </tr>
             <tr>
            <td class="item"><?php echo $AppUI->_('Show Other Users Tasks'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[show_all_tasks]" value="true" <?php echo ($Installer->cfg['show_all_tasks']==true) ? 'checked="checked"' : ''; ?> title="Enable if you want to be able to see other users' tasks." /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Show all Task Assignees'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[show_all_task_assignees]" value="true" <?php echo ($Installer->cfg['show_all_task_assignees']==true) ? 'checked="checked"' : ''; ?> title="Enable if you want the task lists to show all assignees names. Disable if you only want to display the first assignee and then a count of the rest while the full list is still available on a mouse over." /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Enable GANTT Charts'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[enable_gantt_charts]" value="true" <?php echo ($Installer->cfg['enable_gantt_charts']==true) ? 'checked="checked"' : ''; ?> title="Enable if you want to support GANTT Charts." /></td>
          </tr>
             <tr>
            <td class="item"><?php echo $AppUI->_('Log Changes'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[log_changes]" value="true" <?php echo ($Installer->cfg['log_changes']==true) ? 'checked="checked"' : ''; ?> title="Enable if you want to log changes using the history module." /></td>
          </tr>
           </tr>
             <tr>
            <td class="item"><?php echo $AppUI->_('Check Task Dates'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[check_tasks_dates]" value="true" <?php echo ($Installer->cfg['check_tasks_dates']==true) ? 'checked="checked"' : ''; ?> title="Enable if you want to check task's start and end dates. Disable if you want to be able to leave start or end dates empty." /></td>
          </tr>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Enable Relink of Tickets'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[relink_tickets_kludge]" value="true" <?php echo ($Installer->cfg['relink_tickets_kludge']==true) ? 'checked="checked"' : ''; ?> title="Set to true if you need to be able to relink tickets to an arbitrary parent." /></td>
          </tr>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Restrict Task Time Editing'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[restrict_task_time_editing]" value="true" <?php echo ($Installer->cfg['restrict_task_time_editing']==true) ? 'checked="checked"' : ''; ?> title="Set to true if you want only to enable task owner, project owner or sysadmin to edit already created task time related information." /></td>
          </tr>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Warn if Translation Unavailable'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[locale_warn]" value="true" <?php echo ($Installer->cfg['locale_warn']==true) ? 'checked="checked"' : ''; ?> title="Warn when a translation is not found (for developers and tranlators)!" /></td>
          </tr>
           <tr>
            <td class="item"><?php echo $AppUI->_('Locale Warning String'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[locale_alert]" value="<?php echo $Installer->cfg['locale_alert']; ?>" size="true" title="The string appended to untranslated string or unfound keys." /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Check overallocation'); ?></td>
            <td align="left"><input type="checkbox" name="pd[check_overallocation]" value="true" <?php echo ($Installer->cfg['check_overallocation']==true) ? 'checked="checked"' : ''; ?> title="Set to true if you want to activate the user overallocation feature" /></td>
          </tr>
           </tr>
             <tr>
            <td class="item"><?php echo $AppUI->_('Debug'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[debug]" value="true" <?php echo ($Installer->cfg['debug']==true) ? 'checked="checked"' : ''; ?> title="Set debug to true to help analyse errors." /></td>
          </tr>
          <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="title" colspan="2"><?php echo $AppUI->_('File Parsers for Indexing Information (Advanced Users)'); ?></td>
        </tr>
         <tr>
            <td class="item"><?php echo $AppUI->_('Default'); ?></td>
            <td align="left"><input class="button" type="text" name="ft[default]" value="<?php echo $Installer->ft['default']; ?>" title="" /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('M$ Word'); ?></td>
            <td align="left"><input class="button" type="text" name="ft[application_msword]" value="<?php echo $Installer->ft['application/msword']; ?>" title="" /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Text/HTML'); ?></td>
            <td align="left"><input class="button" type="text" name="ft[text_html]" value="<?php echo $Installer->ft['text/html']; ?>" /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('PDF'); ?></td>
            <td align="left"><input class="button" type="text" name="ft[application_pdf]" value="<?php echo $Installer->ft['application/pdf']; ?>" title="" /></td>
          </tr>
          <tr>
          <tr>
            <td align="left"><input class="button" type="submit" name="various[do_save_cfg]" value="<?php echo $AppUI->_('Save for this Session');?>" /></td>
            <td align="right"><input class="button" type="submit" name="various[do_write_cfg]" value="<?php echo $AppUI->_('Write File');?>" /></td>
          </tr>
        </table>
</form>
<?php }
$Installer->various[do_save_cfg] = false;
$Installer->various[do_write_cfg] = false;
?>