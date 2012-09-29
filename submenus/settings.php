<?php
function connectionsShowSettingsPage()
{
	/*
	 * Check whether user can edit Settings
	 */
	if (!current_user_can('connections_change_settings'))
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
				width:700px">' . __('You do not have sufficient permissions to access this page.', 'connections') . '</p>');
	}
	else
	{
		global $connections;
		
		$connections->displayMessages();
	?>
		<div class="wrap">
			
			<?php 
				$args = array(
					'page_icon' => 'connections',
					'page_title' => 'Connections : ' . __('Settings', 'connections'),
					'tab_icon' => 'options-general'
					);
				
				$connections->settings->form( $connections->pageHook->settings , $args );
			?>
		</div>
		<div class="clear"></div>
	
	<?php }
}
?>