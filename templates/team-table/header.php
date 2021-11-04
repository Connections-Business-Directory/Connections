<?php
/**
 * @var array $atts
 */

?>
<div class="cn-table-row cn-table-header">

	<div class="cn-table-cell">
	</div>

	<div class="cn-table-cell">
		Name
	</div>

	<?php if ( $atts['displayExcerpt'] ) : ?>
		<div class="cn-table-cell cn-team-member-excerpt">
			Excerpt
		</div>
	<?php endif; ?>

	<?php if ( $atts['displayPhone'] ) : ?>
		<div class="cn-table-cell cn-team-member-phone">
			Phone
		</div>
	<?php endif; ?>


	<?php if ( $atts['displayEmail'] ) : ?>
		<div class="cn-table-cell cn-team-member-email">
			Email
		</div>
	<?php endif; ?>


	<?php if ( $atts['displaySocial'] ) : ?>
		<div class="cn-table-cell cn-team-member-social-media">
			Social Media
		</div>
	<?php endif; ?>

</div>
