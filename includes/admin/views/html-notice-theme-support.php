<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div id="message" class="updated electrosuite-reseller-message">
	<p><?php _e( sprintf( '<strong>%s has not been tested with your theme.</strong> &#8211; If you encounter layout issues, please read our integration guide or choose a theme that has been tested.', ElectroSuite_Reseller()->name, ElectroSuite_Reseller()->name ), ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( apply_filters( 'electrosuite_reseller_theme_docs_url', ElectroSuite_Reseller()->doc_url . 'theme-compatibility-intergration/', 'theme-compatibility' ) ); ?>" class="button-primary"><?php _e( 'Theme Integration Guide', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></a> <a class="skip button-primary" href="<?php echo esc_url( add_query_arg( 'hide_electrosuite_reseller_theme_support_check', 'true' ) ); ?>"><?php _e( 'Hide this notice', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></a></p>
</div>