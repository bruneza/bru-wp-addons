<?php

namespace ElementorPro\Modules\QueryControl\Controls;

use Elementor\Controls_Manager;
use ElementorPro\Core\Utils;
use ElementorPro\Modules\QueryControl\Module as Query_Module;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Group_BRU_Query extends Group_Control_Query
{

    public static function get_type()
    {
        return 'bru-query';
    }

    /**
     * Build the group-controls array
     * Note: this method completely overrides any settings done in Group_Control_Posts
     * @param string $name
     *
     * @return array
     */
    protected function init_fields_by_name($name)
    {
        $fields = parent::init_fields_by_name($name);

        $tabs_wrapper = $name . '_query_args';
        $include_wrapper = $name . '_query_include';

        // TAXONOMY

        // $fields['related_taxonomies']['condition']['post_type'][] = 'bru-query';
        $fields['include_authors']['condition']['post_type!'][] = 'bru-query';
        $fields['exclude_authors']['condition']['post_type!'][] = 'bru-query';
        $fields['avoid_duplicates']['condition']['post_type!'][] = 'bru-query';

        $fields['post_type']['options']['bru-query'] = esc_html__('BRU Query', 'inoventyk');
        $fields['include']['options']['include_taxonomies'] = esc_html__('Taxonomy', 'inoventyk');

        $ino_cpt = [
            'label' => esc_html__('Post Type', 'inoventyk'),
            'type' => Controls_Manager::SELECT,
            'options' => Utils::get_public_post_types(),
            'condition' => [
                'include' => 'include_taxonomies',
                'post_type' => [
                    'bru-query'
            ],
            ],
            'separator' => 'before',
        ];

        $ino_taxonomies = [
            'label' => esc_html__('Taxonomy', 'inoventyk'),
            'type' => Controls_Manager::SELECT2,
            'options' => $this->get_supported_taxonomies($ino_cpt),
            'label_block' => true,
            'multiple' => true,
            'condition' => [
                'include' => 'include_taxonomies',
                'post_type' => [
                    'bru-query'
                ],
            ],
            'tabs_wrapper' => $tabs_wrapper,
            'inner_tab' => $include_wrapper,
        ];


        $fields = \Elementor\Utils::array_inject($fields, 'include_term_ids', ['selected_cpt' => $ino_cpt]);
        $fields = \Elementor\Utils::array_inject($fields, 'include_term_ids', ['include_taxonomy_slugs' => $ino_taxonomies]);

        return $fields;
    }

    protected function get_supported_taxonomies($post_type = null)
    {
        $supported_taxonomies = [];

        if(!isset($post_type))
        $public_types = $post_type;
        else
        $public_types = Utils::get_public_post_types();

        if(is_array($public_types)){
        foreach ($public_types as $type => $title) {
            $taxonomies = get_object_taxonomies($type, 'objects');

            foreach ($taxonomies as $key => $tax) {
                if (!array_key_exists($key, $supported_taxonomies)) {
                    $label = $tax->label;

                    if (in_array($tax->label, $supported_taxonomies)) {
                        $label = $tax->label . ' (' . $tax->name . ')';
                    }
                    $supported_taxonomies[$key] = $label;
                }
            }
        }
    }else{
        $taxonomies = bru_get_taxonomies($post_type);

        foreach ($taxonomies as $key => $tax) {
            if (!array_key_exists($key, $supported_taxonomies)) {
                $label = $tax->label;

                if (in_array($tax->label, $supported_taxonomies)) {
                    $label = $tax->label . ' (' . $tax->name . ')';
                }
                $supported_taxonomies[$key] = $label;
            }
        }
    }
        return $supported_taxonomies;
    }

    protected static function init_presets()
    {
        parent::init_presets();
        static::$presets['related'] = [
            'related_fallback',
            'fallback_ids',
        ];
    }
}
