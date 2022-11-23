<?php

namespace BRU_Addons\CPT;


// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

class register_Cpts
{
    private static $instance = null;

    /**
     * Variables for event Custom Post Type
     */

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */

    //use custom fields
    public function __construct()
    {
        require_once BRU_DIR . '/includes/CPT/CONF/init-cpt-class.php';

        /*$cpt = new \BRU_Addons\CPT\CONF\Post_type_init(
                array(
                    'cpt_name' => 'portfolio',
                    'singular'       => __('Portfolio', 'bruneza'),
                    'plural'         => __('Portfolios', 'bruneza'),
                    'slug'           => 'portfolio'
                ),
                array(
                    'supports'  => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'comments'),
                    'menu_icon' => 'dashicons-portfolio'
                )
            );

            $cpt->register_taxonomy(array(
                'taxonomy_name' => 'portfolio_tags',
                'singular'      => __('Portfolio Tag', 'bruneza'),
                'plural'        => __('Portfolio Tags', 'bruneza'),
                'slug'          => 'portfolio-tag'
            ));*/

        // ANCHOR - CUstom fields
        /*  add_filter('bru_meta_boxes', [$this, 'register_cFields']);
            add_action('init', [$this, 'initiate_cmb']);*/
    }

    /**
     * Register Custom Fields
     *
     * @param  mixed $meta_boxes
     * @return array
     * 
     */
    public function register_cFields(array $meta_boxes)
    {
        $prefix = '_bru_';

        $meta_boxes['portfolio_cmb'] = array(
            'id'         => 'portfolio',
            'title'      => __('Portfolio', 'bruneza'),
            'post_types'      => array('portfolio',), // Post type
            'context'    => 'normal',
            'priority'   => 'high',
            'show_names' => true, // Show field names on the left
            'fields'     => array(
                array(
                    'label' => __('Event URL', 'bruneza'),
                    'id'   => $prefix . 'url',
                    'placeholder' => __('Event URL', 'bruneza'),
                    'type' => 'url',
                ),
                array(
                    'label' => __('Event Date', 'bruneza'),
                    'placeholder' => __('Event Date', 'bruneza'),
                    'id'   => $prefix . 'date',
                    'type' => 'date',
                )
            ),
        );

        return $meta_boxes;
    }


    public function initiate_cmb()
    {

        if (!class_exists('Fields')) {
            require_once BRU_DIR . '/includes/CPT/cpt-fields-class.php';

            $meta_boxes = array();
            $meta_boxes = apply_filters('bru_meta_boxes', $meta_boxes);
            foreach ($meta_boxes as $meta_box) {
                $cmb_box = new \BRU_Addons\CPT\CONF\Fields($meta_box);
            }
        }
    }
}
