<?php
defined( 'ABSPATH' ) || exit;

?>

<div class="crlms-tools">
	<h2><?php echo __( 'Install Sample Data', 'creator-lms' ); ?></h2>
	<p class="crlms-install-actions">
		<a class="button button-primary crlms-tools-sample-data-install"
		   data-installing-text="<?php echo __( 'Installing...', 'creator-lms' ); ?>"
		   href="<?php echo wp_nonce_url( admin_url( 'index.php?page=crlms-tools' ), 'install-sample-course' ); ?>">
			<?php echo __( 'Install', 'creator-lms' ); ?>
		</a>

		<a class="button crlms-tools-sample-data-delete"
		   data-installing-text="<?php echo __( 'Deleting...', 'creator-lms' ); ?>"
		   href="<?php echo wp_nonce_url( admin_url( 'index.php?page=crlms-tools' ), 'delete-sample-course' ); ?>">
			<?php echo __( 'Delete Sample Data', 'creator-lms' ); ?>
		</a>
	</p>
</div>
