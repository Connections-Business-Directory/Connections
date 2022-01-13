<?php
/**
 * @var array $atts
 */

?>
<div class="cn-table-row cn-table-header">

	<div class="cn-table-cell">
	</div>

	<div class="cn-table-cell">
		<?php _ex( 'Name', 'team block table header', 'connections' ); ?>
	</div>

	<?php if ( $atts['displayExcerpt'] ) : ?>
		<div class="cn-table-cell cn-team-member-excerpt">
			<?php _ex( 'Excerpt', 'team block table header', 'connections' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $atts['displayPhone'] ) : ?>
		<div class="cn-table-cell cn-team-member-phone">
			<?php _ex( 'Phone', 'team block table header', 'connections' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $atts['displayEmail'] ) : ?>
		<div class="cn-table-cell cn-team-member-email">
			<?php _ex( 'Email', 'team block table header', 'connections' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $atts['displaySocial'] ) : ?>
		<div class="cn-table-cell cn-team-member-social-media">
			<?php _ex( 'Social Media', 'team block table header', 'connections' ); ?>
		</div>
	<?php endif; ?>

</div>
