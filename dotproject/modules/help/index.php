<?php /* $Id$ */ ?>
<table width="98%" cellspacing="1" cellpadding="0" border="0">
	<tr>
	<td><img src="./images/icons/dp.gif" alt="" border="0"></td>
		<td nowrap="nowrap" width="100%"><span class="title"><?php echo $AppUI->_('Help');?></span></td>
	</tr>
</table>

<p><?php echo $AppUI->_( 'helpIntro' );?></p>

<table width="98%" cellspacing="1" cellpadding="4" border="0">
<tr>
	<td width="33%" valign="top">
		<p><b><?php echo contextHelp( $AppUI->_( 'Getting Started' ), 'ID_HELP_TUTORIAL' );?></b>
		<ul>
			<li><?php echo contextHelp( $AppUI->_( 'Adding a Company' ), 'ID_HELP_TUT_COMP' );?></li>
			<li><?php echo contextHelp( $AppUI->_( 'Adding a Project' ), 'ID_HELP_TUT_PROJ' );?></li>
			<li><?php echo contextHelp( $AppUI->_( 'Adding a Task' ), 'ID_HELP_TUT_TASK' );?></li>
		</ul>
		</p>
	</td>
	<td width="33%" valign="top">
		<p><b><?php echo contextHelp( $AppUI->_( 'About Modules' ), 'ID_HELP_TUTORIAL' );?></b>
		<ul>
			<li><?php echo contextHelp( $AppUI->_( 'Companies' ), 'ID_HELP_COMPANIES' );?></li>
			<li><?php echo contextHelp( $AppUI->_( 'System' ), 'ID_HELP_SYS_IDX' );?></li>
		</ul>
		</p>
	</td>
	<td width="33%" valign="top">
		<p><b><?php echo $AppUI->_( 'General Concepts' );?></b>
		<ul>
			<li><?php echo contextHelp( $AppUI->_( 'Top Menu' ), 'ID_HELP_GEN_TOPMENU' );?></li>
			<li><?php echo contextHelp( $AppUI->_( 'Left Navigation' ), 'ID_HELP_GEN_LEFTNAV' );?></li>
			<li><?php echo contextHelp( $AppUI->_( 'Context Help' ), 'ID_HELP_GEN_HELP' );?></li>
			<li><?php echo contextHelp( $AppUI->_( 'Saving Your Place' ), 'ID_HELP_GEN_SAVING' );?></li>
			<li><?php echo contextHelp( $AppUI->_( 'Breadcrumbs' ), 'ID_HELP_GEN_CRUMBS' );?></li>
			<li><?php echo contextHelp( $AppUI->_( 'Tabbed Property Boxes' ), 'ID_HELP_GEN_TABS' );?></li>
		</ul>
		</p>
	</td>
</tr>
</table>