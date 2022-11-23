<?php

namespace BRU_Addons\Helper;

use wpdb;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

if (!class_exists('query_helper')) :
    class query_helper
    {

        private $settings;
        private $args = array(
            'posts_per_page'         =>  -1,
            'order'                  => 'DESC',
            'orderby'                => 'date',
        );

        private static $instance = null;

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
        public function __construct($settings = null)
        {
            // ANCHOR: Process settings
            $this->settings = $this->processSettings($settings);
            $this->args = $this->processArgs();
        }

        public function processSettings($settings = null)
        {
            if (!isset($this->settings)) {
                $defSettings = [
                    'x_post_type' => 'post',
                    'x_posts_per_page' => -1,
                    'x_terms' => null,
                    'x_outputs' => null,
                    'x_taxonomy' => null,
                    'x_ignore' => null,
                    'x_show' => 'all',
                    'x_skip_nothumbnail' => false,

                ];
            } else $defSettings = $this->settings;

            if (isset($settings)) {
                foreach ($settings as $key => $val) {
                    if (isset($settings[$key]) && !empty($settings[$key])) {
                        $sanitize_settings[$key] = $val;
                    }
                }
            }

            $settings = $sanitize_settings;

            $settings = wp_parse_args($settings, $defSettings);

            return $settings;
        }

        public function setArgs($args = null)
        {
            $this->args = wp_parse_args($args, $this->args);
        }


        private function processArgs()
        {
            $settings = $this->settings;

            if (is_string($settings)) {
                $postType = $settings;
                $NumofPosts = -1;
            } else if (is_array($settings)) {
                $postType   =  $settings['x_post_type'];
                $NumofPosts =  $settings['x_posts_per_page'];
                $terms      =  $settings['x_terms'];
                $display      =  $settings['x_show'];
                $output     =  $settings['x_outputs'];
            }

            // ANCHOR: MAnage Display
            if ($display == 'first_term') {
                $terms = [$terms[0]];
                $tax_query = true;
            } else if ($display == 'all') {
                $tax_query = false;
                $terms = null;
            } else {
                $terms =  $terms;
                $tax_query = true;
            }

            // ANCHOR: Process Tax Query Args
            $args = [
                'post_type' => $postType,
                'posts_per_page' => $NumofPosts,
            ];




            $termArgs = array();
            if (isset($terms) && is_array($terms) && $tax_query) {
                foreach ($terms as $id) {
                    $taxonomy = get_term($id)->taxonomy;
                    $value = get_term($id)->term_id;

                    array_push($termArgs, array(
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $value,
                    ));
                }

                $args['tax_query'] = $termArgs;
            }
            // ANCHOR: Process If posts have thumbnail images
            if ($settings['x_skip_nothumbnail']) {
                $args['meta_query'] = array(
                    array(
                        'key' => '_thumbnail_id',
                        'compare' => 'EXISTS'
                    ),
                );
            }
            $args = wp_parse_args($args, $this->args);
            return $args;
        }

        public function getPosts($settings = null, $output  = null)
        {
            if (isset($settings)) $settings = $this->processSettings($settings);
            // $args = wp_parse_args(['post_type' => 'kura_workshops','posts_per_page' => 5]);
            $args = $this->processArgs();
            $query = new \WP_Query($args);

            if (!$query->have_posts()) return null;

            if (empty($output) || !isset($output)) return $query;

            if (empty($output == 'posts')) return get_posts($args);

            else return $query;

            return false;
        }


        public function jsonPostsExport($args= null)
        {
            
            $data = array();
            if(isset($args)){
                if(isset($args['terms'])){
                    if(is_array($args['terms'])){
                    foreach($args['terms'] as $key => $tid){
                        $term = get_term($tid);
                        $termArgs ['tax_query'][$key] = [
                            'taxonomy' => $term->taxonomy,
                            'field' => 'term_id',
                            'terms' => $tid,
                        ];
                        $this->args = wp_parse_args($termArgs, $this->args);
                    }
                } else if ( is_string($args['terms'])){
                    if($args['terms'] == 'all') unset($this->args['terms']['tax_query']);
                }
            }
            }

            $postData = $this->getPosts();

            while ($postData->have_posts()) {
                $post = $postData->the_post();
                $post_id = get_the_ID();
                $innerdata['id'] = $post_id;
                $innerdata['title'] = get_the_title();
                $innerdata['title'] = get_the_title();
                $innerdata['thumbnail'] = get_the_post_thumbnail_url();
                $innerdata['post-link'] = get_permalink();

                array_push($data, $innerdata);
            }

            return json_encode($data);
        }

        public function helper_tester()
        {
            return $this->settings;
        }
    }
endif; // End if class_exists check.