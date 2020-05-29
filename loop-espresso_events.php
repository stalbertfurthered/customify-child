<?php
/**
 * This template will display The Loop that displays your events
 * DO NOT COPY THIS TEMPLATE TO YOUR THEMES FOLDER
 *
 * @package     Event Espresso
 * @author      Seth Shoultes
 * @copyright   (c) 2008-2013 Event Espresso  All Rights Reserved.
 * @license     http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @link        http://www.eventespresso.com
 * @version     4+
 */
    /* if (apply_filters('FHEE__archive_espresso_events_template__show_header', true)) : ?>
        <header class="page-header">
            <h1 class="page-title">
                <?php
                if (is_day()) :
                    printf(__('Today\'s Courses: %s', 'event_espresso'), get_the_date());
                elseif (is_month()) :
                    printf(
                        __('Courses This Month: %s', 'event_espresso'),
                        get_the_date(_x('F Y', 'monthly archives date format', 'event_espresso'))
                    );
                elseif (is_year()) :
                    printf(
                        __('Courses This Year: %s', 'event_espresso'),
                        get_the_date(_x('Y', 'yearly archives date format', 'event_espresso'))
                    );
                else :
                    echo apply_filters(
                        'FHEE__archive_espresso_events_template__upcoming_events_h1',
                        __('Upcoming Courses', 'event_espresso')
                    );
                endif;
                ?>
            </h1>

        </header><!-- .page-header -->

        <?php
    endif;
    */

    $global_taxonomy = 'espresso_event_categories';

    function furthered_format_users($taxonomy, $slug, $matching_users) {
        if (is_tax($taxonomy))
        {
            // WP_User_Query arguments

            if (!empty($matching_users)) {
                foreach ($matching_users as $user) {
                    do_action('furthered_format_member',$user->ID);
                }
            }
        }
    }

    function furthered_format_courses($taxonomy, $category_id, $name, $slug){
        
        $args = array(
            'post_type'         => 'espresso_events',
            'tax_query'         => array(
                array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $category_id)
                )
            );
        
        //  print_r($category_id);
        // print_r($args);

        $my_query = new WP_Query($args);

        if( $my_query->have_posts() ) {
            printf('<a name="%s" />',$slug);
            ?>
                <?php while ( $my_query->have_posts() ) : $my_query->the_post();
                    $found_items = true;
                    espresso_get_template_part('content', 'espresso_events-shortcode');
                endwhile; // end of loop ?>
            <?php
        }

        wp_reset_query();
    }

    function furthered_format_category($taxonomy, $category_id, $name, $slug) {

        $args = array(
            'post_type'         => 'espresso_events',
            'tax_query'         => array(
                array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $category_id)
                )
            );
        
        //  print_r($category_id);
        // print_r($args);

        $my_query = new WP_Query($args);

        $has_courses = $my_query->have_posts();
        wp_reset_query();

        $args = array(
            'role__in'           => array('Subscriber','Administrator','Contributor','Editor')
        );

        // The User Query
        $user_query = new WP_User_Query( $args );
        $all_users = $user_query->get_results();

        $matching_users = array();

        if (!empty($all_users)) {
            foreach ($all_users as $user ) { 
                $stack = wp_list_pluck( wp_get_object_terms( $user->ID, $taxonomy ), 'slug' );
                $found_items = true;
                if (in_array($slug, $stack)) {
                    $matching_users[] = $user;
                }
            }  
        }

        if ($matching_users || $has_courses){
            ?>
            <div class="category-subsection">
            <?php if (function_exists('z_taxonomy_image')) echo z_taxonomy_image($category_id, "thumbnail"); ?>
            <div class="h2"><?php echo $name; // Group name (taxonomy) ?></div>
            <div class="course-category-items">
            <?php
            furthered_format_courses($taxonomy, $category_id, $name, $slug);
            furthered_format_users($taxonomy, $slug, $matching_users);
            ?>
            </div>
            </div>
            <?php
        }
    }

    function furthered_format_subcategory_links($tax_terms, $use_anchors = false){
        ?><div class="categories-container"><?php
        foreach ($tax_terms as $tax_term) {
            // furthered_format_category($global_taxonomy, $tax_term->term_id, $tax_term->name, $tax_term->slug); ?>
            <div class="category-subsection">
                <div class="image-block">
                    <?php if (function_exists('z_taxonomy_image')) {
                        if ($use_anchors) {
                            ?><a href="#<?php echo $tax_term->slug; ?>"><?php
                        } else {
                            ?><a href="/event-category/<?php echo $tax_term->slug; ?>"><?php
                        }
                        echo z_taxonomy_image($tax_term->term_id, "thumbnail");
                        ?></a><?php
                     } ?>
                </div>
                <h4 class="category-title"><a href="/event-category/<?php echo $tax_term->slug; ?>"><?php echo $tax_term->name; // Group name (taxonomy) ?></a></h4>
            </div>
            <?php
        }
        ?></div><?php
    }

    $found_items = false;
    $queried_object = get_queried_object();
    if (is_tax())
    {
        $term_id = $queried_object->term_id; 
    }
    else
    {
        $term_id = 0;
    }

    // allow other stuff
    do_action('AHEE__archive_espresso_events_template__before_loop');

    $tax_terms = get_terms( array(
        'taxonomy' => $global_taxonomy,
        'hide_empty' => false,
        'parent' => $term_id
        ));
  
    // print_r($tax_terms);

    if ($tax_terms) {
        if (is_tax($global_taxonomy))
        {
            $sub_categories = array();
            foreach ($tax_terms as $tax_term) {
                $args = array(
                    'post_type'         => 'espresso_events',
                    'tax_query'         => array(
                        array(
                        'taxonomy' => $tax_term->name,
                        'field' => 'term_id',
                        'terms' => $tax_term->ID)
                        )
                    );
                
                //  print_r($category_id);
                // print_r($args);
        
                $my_query = new WP_Query($args);
        
                if( $my_query->have_posts() ) {
                    $sub_categories[] = $tax_term;
                }

                wp_reset_query();
            }

            if ($sub_categories)
            {
                ?><h2>Subcategories</h2><?php
                furthered_format_subcategory_links($sub_categories, true);
            }
                 
            wp_reset_query();

            foreach ($tax_terms as $tax_term) {
                furthered_format_category($global_taxonomy, $tax_term->term_id, $tax_term->name, $tax_term->slug);
            }
        } else {
            furthered_format_subcategory_links($tax_terms);
        } // end foreach #tax_terms
    } // end if tax_terms
    else
    {
        // print_r($queried_object);
        furthered_format_category($global_taxonomy, $queried_object->term_id, $queried_object->name, $queried_object->slug);
    }

    ?> 
    <hr />
    <?php
    // Start the Loop.
    // Previous/next page navigation.
    // espresso_pagination();
    // allow moar other stuff
    do_action('AHEE__archive_espresso_events_template__after_loop');

    if (!$found_items) {
    // If no content, include the "No posts found" template.
        espresso_get_template_part('content', 'none');
    }
