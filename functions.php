<?php
/**
 * Espresso Template functions
 *
 * @ package		Event Espresso
 * @ author		Seth Shoultes
 * @ copyright	(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license		http://venueespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link			http://www.eventespresso.com
 * @ version		4+
 */
define( 'EE_THEME_FUNCTIONS_LOADED', TRUE );

if ( ! function_exists( 'espresso_pagination' ) ) {
	/**
	 *    espresso_pagination
	 *
	 * @access    public
	 * @return    void
	 */
	function espresso_pagination() {
		global $wp_query;
		$big = 999999999; // need an unlikely integer
		$pagination = paginate_links(
			array(
				'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'       => '?paged=%#%',
				'current'      => max( 1, get_query_var( 'paged' ) ),
				'total'        => $wp_query->max_num_pages,
				'show_all'     => true,
				'end_size'     => 10,
				'mid_size'     => 6,
				'prev_next'    => true,
				'prev_text'    => __( '&lsaquo; PREV', 'event_espresso' ),
				'next_text'    => __( 'NEXT &rsaquo;', 'event_espresso' ),
				'type'         => 'plain',
				'add_args'     => false,
				'add_fragment' => ''
			)
		);
		echo ! empty( $pagination ) ? '<div class="ee-pagination-dv ee-clear-float">' . $pagination . '</div>' : '';
	}
}

function example_enqueue_styles() {
	
	// enqueue parent styles
	wp_register_style('customify-child-style', get_stylesheet_directory_uri() .'/style.css', array('customify-style','elementor-frontend'));
	wp_enqueue_style('customify-child-style');
	
}

function customify_pro_activated_is_true() {
	return $true;
}

class Customify_Pro
{
	public function is_enabled_module($s) {
		return $true;
	}
}

function output_if_set($tag, $value){
	if (isset($value)) {
		echo "<$tag>$value</$tag>";
	}
}


function furthered_format_member_imp($userId) {
	$auser = get_userdata($userId);
	$auser->membership_level = pmpro_getMembershipLevelForUser($userId);
	$metadata = new stdClass();

	$metadataArray = get_user_meta( $userId);

	$attr = array_map( function( $a ){ return $a[0]; }, $metadataArray);

	foreach($attr as $key=>$value)
	{
		$metadata->$key = $value;
	}

	$auser->metadata = $metadata;

	$userUrl = $auser->metadata->user_url;

	if (!preg_match('/^https?:\/\//i', $userUrl, $output_array))
		{
			$userUrl = "http://".$userUrl;
		}

	$userUrl = strip_tags(
		stripslashes(
		  filter_var($userUrl, FILTER_VALIDATE_URL)
		)
	  );

	$displayOnWebsite = $auser->metadata->display_on_website.""; ?>
	<div class="user-entry" id="user-entry-<?php echo $auser->ID; ?>">
		<user-logo><?php echo get_avatar( $auser->ID, '96' ); ?></user-logo>
		<user-info>
			<user-title><?php
				if ($userUrl) { ?> <a href="<?php echo $userUrl; ?>"><?php }

				echo $auser->metadata->company;

				if ($userUrl) { ?> </a> <?php } ?>
			</user-title>
			<?php output_if_set("user-description", $auser->metadata->description) ?>
		</user-info>
		<user-contact>
			<?php
			if (strpos($displayOnWebsite,"_rep_name") > 0) {?>
			<contact-name><?php echo strip_tags($auser->metadata->bfirstname." ".$auser->metadata->blastname);?></contact-name>
			<?php }

			if (strpos($displayOnWebsite,"_mailing_address") > 0) {?>
			<user-address><a href="https://www.google.ca/maps/place/<?php echo urlencode(
			$auser->metadata->pmpro_baddress1.",".
			($auser->metadata->pmpro_bcity ?? "St.Albert").",".
			($auser->metadata->pmpro_bstate ?? "AB").",".
			($auser->metadata->pmpro_bzipcode ?? "T8N"));?>"><i class="fa fas fa-location-arrow fa-fw"></i>Directions</a></user-address>
			<?php }

			if (strpos($displayOnWebsite,"_bphone") > 0 && isset($auser->metadata->pmpro_bphone)) {?>
			<user-phone><a href="tel:<?php echo urlencode($auser->metadata->pmpro_bphone); ?>"><i class="fa fas fa-phone fa-fw"></i>Call</a></user-phone>
			<?php }

			if (strpos($displayOnWebsite,"_main_email") > 0 && isset($auser->metadata->pmpro_bemail)) {?>
			<user-email><a href="<?php echo "/contact-a-member/?".$auser->ID; ?>"><i class="fa fas fa-envelope fa-fw"></i>E-Mail</a></user-email>
			<?php } ?>
		</user-contact>
	</div><div class="clear">&nbsp;</div><?php 
}


add_action('furthered_format_member','furthered_format_member_imp');

add_action('wp_enqueue_scripts', 'example_enqueue_styles');
add_filter('customify/is_pro_activated', 'customify_pro_activated_is_true');

// Adds support for editor color palette.
add_theme_support('editor-color-palette', array(
    array(
        'name' => __('Light gray', 'genesis-sample'),
        'slug' => 'light-gray',
        'color' => '#f5f5f5',
    ),
    array(
        'name' => __('Medium gray', 'genesis-sample'),
        'slug' => 'medium-gray',
        'color' => '#999',
    ),
    array(
        'name' => __('Dark gray', 'genesis-sample'),
        'slug' => 'dark-gray',
        'color' => '#333',
    ),
));