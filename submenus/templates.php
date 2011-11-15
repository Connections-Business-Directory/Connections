<?php
function connectionsShowTemplatesPage()
{
	/*
	 * Check whether user can edit Settings
	 */
	if (!current_user_can('connections_manage_template'))
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
		global $connections;
		
		$form = new cnFormObjects();
		
		$tmplt = new cnTemplate();
		$tmplt->buildCatalog();
		
		( !isset($_GET['type']) ) ? $type = 'all' : $type = esc_attr($_GET['type']);
		$templates = $tmplt->getCatalog($type);
		
		$connections->displayMessages();
	?>
		<div class="wrap">
			<div id="icon-connections" class="icon32">
		        <br>
		    </div>
			
			<h2>Connections : Templates <a class="button add-new-h2" href="http://connections-pro.com/?page_id=419" target="_blank">Get More</a></h2>
			
			<ul class="subsubsub">
				<li><a <?php if ($type === 'all') echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=all">All</a> | </li>
				<li><a <?php if ($type === 'individual') echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=individual">Individual</a> | </li>
				<li><a <?php if ($type === 'organization') echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=organization">Organization</a> | </li>
				<li><a <?php if ($type === 'family') echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=family">Family</a> | </li>
				<li><a <?php if ($type === 'anniversary') echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=anniversary">Anniversary</a> | </li>
				<li><a <?php if ($type === 'birthday') echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=birthday">Birthday</a></li>
			</ul>
			
			<table cellspacing="0" cellpadding="0" id="availablethemes">
				<tbody>
					<tr>
						<td class="current_template">
							<h2>Current Template</h2>
							
							<div id="current-theme">
								<?php
								$currentTemplate = $connections->options->getActiveTemplate($type);
								
								if ( !empty($currentTemplate) )
								{
									$author = '';
									
									if ( isset($currentTemplate->thumbnailPath) )
									{
										echo '<div class="current-template"><img class="template-thumbnail" src="' . $currentTemplate->thumbnailURL . '" /></div>';
									}
									
									if ( isset($currentTemplate->uri) )
									{
										$author = '<a title="Visit author\'s homepage." href="http://' . esc_attr($currentTemplate->uri) . '">' . esc_attr($currentTemplate->author) . '</a>';
									}
									else
									{
										$author = esc_attr($currentTemplate->author);
									}
									
									echo '<h3>', esc_attr($currentTemplate->name), ' ', esc_attr($currentTemplate->version), ' by ', $author, '</h3>';
									echo '<p class="theme-description">', esc_attr($currentTemplate->description), '</p>';
									
									// Remove the current template so it does not show in the available templates.
									unset($templates->{$currentTemplate->slug});
								}
								?>
							</div>
							<div class="clear"></div>
						</td>
						
						<td class="template_instructions" colspan="2">
							<p><strong>Instructions:</strong></p>
							<p>
								By default the <code>[connections]</code> shortcode will show all entries types. To change the template
								used when displaying all entry types, select the "All" tab and activate the template. When the <code>list_type</code>
								shortcode attribute is used to filter the entries based on the entry type, the template for that entry type will be used.
								To change the template used for specific entry type, select the appropriate tab and then activate the template. If multiple
								entry types are specified in the <code>list_type</code> shortcode attribute, the template for the entry type listed first
								will be used to display the entry list.
							</p>
							
							<p>
								The <code>[upcoming_list]</code> shortcode which displays the upcoming anniversaries and birthdays will be displayed with the template
								that is activated under their respective tabs.
							</p>
							
							<p>
								The current active template for each template type can be overridden by using the the <code>template</code> shortcode attribute.
							</p>
						</td>
					</tr>
					
					<tr>
						<td class="install_template" colspan="3">
							<h2>Install Template</h2>
							
							<?php 
							$formAttr = array(
										 'action' => 'admin.php?connections_process=true&process=template&type=' . $type . '&action=install',
										 'method' => 'post',
										 'enctype' => 'multipart/form-data'
										 );
							
							$form->open($formAttr);
							$form->tokenField('install_template');
							?>
							
							<p>
								<label for='template'>Select Template:
									<input type='file' value='' name='template' size='25' />
								</label>
								<input type="submit" value="Install Now" class="button">
							</p>
							
							<?php $form->close(); ?>
						</td>
					</tr>
					
					<tr>
						<td class="current_template" colspan="3">
							<h2>Available Templates</h2>
						</td>
					</tr>
					
					<?php
					$templateNames = array_keys( (array) $templates);
					natcasesort($templateNames);
					
					$table = array();
					$rows = ceil(count( (array) $templates) / 3);
					for ( $row = 1; $row <= $rows; $row++ )
						for ( $col = 1; $col <= 3; $col++ )
							$table[$row][$col] = array_shift($templateNames);
					
					foreach ( $table as $row => $cols )
					{
					?>
						<tr>
							<?php
							foreach ( $cols as $col => $slug )
							{
								$activateTokenURL = NULL;
								$deleteTokenURL = NULL;
								
								$class = array('available-theme');
								if ( $row == 1 ) $class[] = 'top';
								if ( $row == $rows ) $class[] = 'bottom';
								if ( $col == 1 ) $class[] = 'left';
								if ( $col == 3 ) $class[] = 'right';
							?>
								<td class="<?php echo join(' ', $class); ?>">
									<?php
									if ( !isset( $templates->$slug ) ) continue;
									
									$author = '';
									
									if ( isset( $templates->$slug->thumbnailPath ) )
									{
										echo '<div class="center-thumbnail"><img class="template-thumbnail" src="' . $templates->$slug->thumbnailURL . '" /></div>';
									}
									
									if ( isset($templates->$slug->uri) )
									{
										$author = '<a title="Visit author\'s homepage." href="' . esc_attr($templates->$slug->uri) . '">' . esc_attr($templates->$slug->author) . '</a>';
									}
									else
									{
										$author = esc_attr($templates->$slug->author);
									}
									
									echo '<h3>', esc_attr($templates->$slug->name), ' ', esc_attr($templates->$slug->version), ' by ', $author, '</h3>';
									echo '<p class="description">', esc_attr($templates->$slug->description), '</p>';
									echo '<p>Shortcode Override: <code>template="' . $slug . '"</code></p>';
									if ( $templates->$slug->custom === FALSE ) echo '<p>This a supplied template and can not be deleted.</p>';
									?>
									<span class="action-links">
										<?php
										$activateTokenURL = $form->tokenURL( 'admin.php?connections_process=true&process=template&action=activate&type=' . $type . '&template=' . esc_attr($templates->$slug->slug), 'activate_' . esc_attr($templates->$slug->slug) );
										
										if ( $templates->$slug->custom === TRUE )
										{
											$deleteTokenURL = $form->tokenURL( 'admin.php?connections_process=true&process=template&action=delete&type=' . $type . '&template=' . esc_attr($templates->$slug->slug), 'delete_' . esc_attr($templates->$slug->slug) );
										}
										
										?>
										
										<a class="activatelink" href="<?php echo esc_attr($activateTokenURL); ?>" title="Activate '<?php echo esc_attr($templates->$slug->name); ?>'">Activate</a>
									
										<?php
										if ( isset($deleteTokenURL) )
										{
										?>
											 | <a class="deletelink" href="<?php echo esc_attr($deleteTokenURL); ?>" title="Delete '<?php echo esc_attr($templates->$slug->name); ?>'" onclick="return confirm('You are about to delete this theme \'<?php echo esc_attr($templates->$slug->name); ?>\'\n  \'Cancel\' to stop, \'OK\' to delete.');">Delete</a>
										<?php
										}
										?>
									</span>
								</td>
							<?php
							}
							?>
						</tr>
					<?php
					}
					?>
					
					
				</tbody>
			</table>
			
		</div>
	<?php
	}
}
?>