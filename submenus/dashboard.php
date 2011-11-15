<?php
function connectionsShowDashboardPage()
{
	/*
	 * Check whether user can view the Dashboard
	 */
	if (!current_user_can('connections_view_dashboard'))
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
	?>
		<div class="wrap">
			<div id="icon-connections" class="icon32">
		        <br>
		    </div>
			
			<h2>Connections : Dashboard</h2>
			
			<div id="dashboard-widgets-wrap">
				
				<div class="metabox-holder" id="dashboard-widgets">
					
					<div style="width: 49%;" class="postbox-container">
						<?php do_meta_boxes($connections->pageHook->dashboard, 'left', NULL); ?>
					</div>
					
					<div style="width: 49%;" class="postbox-container">
						<?php do_meta_boxes($connections->pageHook->dashboard, 'right', NULL); ?>
					</div>
					
				</div>
				
			</div>
								
		</div>
		
		<?php
			$attr = array(
						 'action' => '',
						 'method' => 'get'
						 );
			
			$form->open($attr);
			
			wp_nonce_field('howto-metaboxes-general');
			wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
			
			$form->close();
		?>
		
		
		<div class="clear"></div>
		
		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('<?php echo $connections->pageHook->dashboard ?>');
		});
		//]]>
		</script>
	<?php
	}
}
?>