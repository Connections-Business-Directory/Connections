<?php
function connectionsShowRolesPage()
{
	/*
	 * Check whether user can edit roles
	 */
	if (!current_user_can('connections_change_roles'))
	{
		wp_die('<p id="error-page" style="-moz-background-clip:border;
				-moz-border-radius:11px;
				background:#FFFFFF none repeat scroll 0 0;
				border:1px solid #DFDFDF;
				color:#333333;
				display:block;
				font-size:12px;
				line-height:18px;
				margin:25px auto 20px;
				padding:1em 2em;
				text-align:center;
				width:700px">You do not have sufficient permissions to access this page.</p>');
	}
	else
	{
		global $connections, $wp_roles;
		
		$form = new cnFormObjects();
		
		$connections->displayMessages();
		
	?>
		<div class="wrap cn-roles">
			<div id="icon-connections" class="icon32">
		        <br>
		    </div>
			
			<h2>Connections : Roles &amp; Capabilities</h2>
			
			<?php 
				$attr = array(
							 'action' => 'admin.php?connections_process=true&process=role&action=update',
							 'method' => 'post',
							 );
				
				$form->open($attr);
				$form->tokenField('update_role_settings');
			?>
				
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				
				<div class="inner-sidebar" id="side-info-column">
					<div id="submitdiv" class="postbox">
						<h3 class="hndle">
							<span>Save Changes or Reset</span>
						</h3>
						
						<div class="inside">
							
							<div id="minor-publishing">
								<label for="reset_all_roles">
									<input type="checkbox" id="reset_all_roles" name="reset_all" value="true">
									Reset All Role Capabilities
								</label>
							</div>
							
							<div id="major-publishing-actions">
								<div id="publishing-action">
									<input class="button-primary" type="submit" value="Update" name="save" />
								</div>
								<div class="clear"></div>
							</div>
						</div>
						
					</div>
				</div>
			
				<div class="has-sidebar" id="post-body">
					<div class="has-sidebar-content" id="post-body-content">
						<?php
							$editable_roles = get_editable_roles();
							
							foreach( $editable_roles as $role => $details )
							{
								$name = translate_user_role($details['name'] );	
								
								// the admininistrator should always have all capabilities
								if ($role == 'administrator') continue;
								
								$capabilies = $connections->options->getDefaultCapabilities();
								
								echo '<div class="postbox">';
								
								echo '<h3 class="hndle"><span>' , $name , '</span></h3>';
								
								echo '<div class="inside">';
								
								foreach ($capabilies as $capability => $capabilityName)
								{
									// if unregistered users are permitted to view the entry list there is no need for setting this capability
									if ($capability == 'connections_view_public' && $connections->options->getAllowPublic() == true) continue;
									
									echo '<label for="' . $role . '_' . $capability . '">';
									echo '<input type="hidden" name="roles[' . $role . '][capabilities][' . $capability . ']" value="false" />';
									echo '<input type="checkbox" id="' . $role . '_' . $capability . '" name="roles[' . $role . '][capabilities][' . $capability . ']" value="true" '; 
									
									if ($connections->options->hasCapability($role, $capability)) echo 'CHECKED ';
									// the admininistrator should always have all capabilities
									if ($role == 'administrator') echo 'DISABLED ';
									echo '/> ' . $capabilityName . '</label>' . "\n";
									
								}
								
								echo '<label for="' . $role . '_reset_capabilities">';
								echo '<input type="checkbox" id="' . $role . '_reset_capabilities" name="reset[' . $role . ']" value="' . $name . '" ';
								echo '/> Reset ' . $name . ' Capabilities</label>' . "\n";
								
								echo '</div>';
								echo '</div>';
							}
						?>
					</div>
				</div>
			</div>
			
			<?php $form->close(); ?>
			
		
		</div>
		<div class="clear"></div>
		
	<?php }
}
?>