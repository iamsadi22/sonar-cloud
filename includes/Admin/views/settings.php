<?php
/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_tab_label = isset( $tabs[ $creator_lms_current_tab ] ) ? $tabs[ $creator_lms_current_tab ] : '';

?>

<div class="wrap creator-lms-settings-wrapper">
	<?php do_action( 'woocommerce_before_settings_' . $creator_lms_current_tab ); ?>

	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php

			foreach ( $tabs as $slug => $label ) {
				echo '<a href="' . esc_html( admin_url( 'admin.php?page=crlms-settings&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $creator_lms_current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
			}

			do_action( 'creator_lms_settings_tabs' );

			?>
		</nav>

		<?php
			do_action( 'creator_lms_sections_' . $creator_lms_current_tab );

			do_action( 'creator_lms_settings_' . $creator_lms_current_tab );
		?>

		<p class="submit">
			<?php wp_nonce_field( 'creator-lms-settings' ); ?>
			<button name="save" class="button-primary crlms-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'creator-lms' ); ?>"><?php esc_html_e( 'Save changes', 'creator-lms' ); ?></button>
		</p>
	</form>

	<?php do_action( 'creator-lms_after_settings_' . $creator_lms_current_tab ); ?>
</div>
