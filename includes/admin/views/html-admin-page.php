
<div class="wrap electrosuite_reseller about-wrap"> <?php // Added about-wrap class for potential style reuse ?>

	<?php // --- START: Common Header Elements (Moved from Welcome::intro) --- ?>
    <h1><?php echo esc_html( ElectroSuite_Reseller()->title_name ); /* Use main title */ ?></h1>

    <div class="about-text electrosuite-reseller-about-text">
        <?php
            // Optional: Add a general welcome message or description here if needed
            // Example: esc_html_e( 'Manage your ElectroSuite Reseller settings and view system status.', 'electrosuite-reseller' );
        ?>
    </div>

    <div class="electrosuite-reseller-badge"><?php printf( __( 'Version %s', 'electrosuite-reseller' ), esc_html( ElectroSuite_Reseller()->version ) ); ?></div>

    <div class="electrosuite-reseller-social-links">
        <?php if ( ! empty( ElectroSuite_Reseller()->facebook_page ) ) : ?>
        <a class="facebook_link" href="https://www.facebook.com/<?php echo esc_attr( ElectroSuite_Reseller()->facebook_page ); ?>" target="_blank" rel="noopener noreferrer">
            <span class="dashicons dashicons-facebook-alt"></span>
        </a>
        <?php endif; ?>

        <?php if ( ! empty( ElectroSuite_Reseller()->twitter_username ) ) : ?>
        <a class="twitter_link" href="https://twitter.com/<?php echo esc_attr( ElectroSuite_Reseller()->twitter_username ); ?>" target="_blank" rel="noopener noreferrer">
            <span class="dashicons dashicons-twitter"></span>
        </a>
        <?php endif; ?>

        <?php /* Add other social links like Google+ if needed and property exists */ ?>
        <?php /* if ( ! empty( ElectroSuite_Reseller()->google_plus_id ) ) : ?>
        <a class="googleplus_link" href="https://plus.google.com/<?php echo esc_attr( ElectroSuite_Reseller()->google_plus_id ); ?>" target="_blank" rel="noopener noreferrer">
            <span class="dashicons dashicons-googleplus"></span>
        </a>
        <?php endif; */ ?>
    </div><!-- .electrosuite-reseller-social-links -->

    <?php // Removed social sharing buttons block from intro - can be added back within specific tabs if desired ?>

	<?php // --- END: Common Header Elements --- ?>


	<?php // --- START: Tab Navigation --- ?>
	<h2 class="nav-tab-wrapper">
		<?php
        if ( ! empty( $tabs ) && is_array( $tabs ) ) {
            foreach ( $tabs as $tab_id => $tab_label ) {
                $tab_url = admin_url( 'admin.php?page=' . rawurlencode( ELECTROSUITE_RESELLER_PAGE ) . '&tab=' . rawurlencode( $tab_id ) );
                $active_class = ( $current_tab === $tab_id ) ? ' nav-tab-active' : '';
                echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . esc_attr( $active_class ) . '">' . esc_html( $tab_label ) . '</a>';
            }
        }
		?>
	</h2>
	<?php // --- END: Tab Navigation --- ?>


    <?php // --- START: Main Content Area --- ?>
    <div class="electrosuite-reseller-main-content">
	    <?php do_action('electrosuite_reseller_page_header'); // General hook before tab content ?>

        <?php do_action( 'electrosuite_reseller_main_page_tab_' . $current_tab ); // Hook for specific tab content ?>

	    <?php do_action('electrosuite_reseller_page_footer'); // General hook after tab content ?>
    </div>
    <?php // --- END: Main Content Area --- ?>


    <?php // --- START: Old View/Section Logic Removed --- ?>
    <?php /*
	<ul class="subsubsub"> Removed </ul>
	<br class="clear" /> Removed
	<?php do_action('electrosuite_reseller_page_' . $view . '_header'); ?> Removed
	<?php if( empty($view) ) $view = 'default'; echo '<h3>You are viewing section ' . $view . '</h3>'; ?> Removed
	<p class="about-description">...</p> Removed
	<p>...</p> Removed
	<p>...</p> Removed
	<?php echo $page_content; ?> Removed
	<?php do_action('electrosuite_reseller_page_' . $view . '_footer'); ?> Removed
    */ ?>
	<?php // --- END: Old View/Section Logic Removed --- ?>

</div>