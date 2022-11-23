<?php

namespace BRU_Addons\CPT\CONF;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

class Fields
{

    private static $instance = null;

    /**
     * Variables for Job Custom Post Type
     */

    private $meta_box = array();

    private static $default_cmb = array(
        'id'         => '',
        'title'      => '',
        'type'       => '',
        'post_types'      => array(), // Post type
        'context'    => 'normal',
        'priority'   => 'high',
        'show_names' => true, // Show field names on the left
        'show_on'    => array('key' => false, 'value' => false), // Specific post IDs or page templates to display this metabox
        'bru_styles' => true, // Include cmb bundled stylesheet
        'fields'     => array(),
    );

    private $nonce_added = false;

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

    public function __construct($meta_box = null)
    {
        if (empty($meta_box))
            $this->meta_box = self::$default_cmb;
        else
            $this->meta_box = $meta_box;

        add_action('admin_menu', array($this, 'add_metaboxes'));
        add_action('add_meta_boxes', array($this, 'add_metaboxes'));
        add_action('save_post', array($this, 'save_fields'));
    }

    /**
     * Fills in empty metabox parameters with defaults
     * 
     * @param  array $meta_box Metabox config array
     * @return array           Modified Metabox config array
     * 
     */
    public static function process_metabox($meta_box)
    {
        return wp_parse_args($meta_box, self::$default_cmb);
    }
    public function process_mb_type($meta_box)
    {
        if (!isset($meta_box['post_types'])) return 'post';

        if (is_string($meta_box['post_types']))
            $type = $meta_box['post_types'];
        // if it's an array of one, extract it
        elseif (is_array($meta_box['post_types'])) {
            $type = $meta_box['post_types'][array_key_first($meta_box['post_types'])];
        } else {
            return null;
        }

        return $type;
    }




    /**
     * ANCHOR: Add metaboxes where fields will go
     */
    public function add_metaboxes()
    {
        $meta_box = $this->meta_box;
        foreach ($meta_box['post_types'] as $cpt_screening) {
            add_meta_box(
                $meta_box['id'],
                __($meta_box['title'], 'bruneza'),
                array($this, 'bru_callback'),
                $cpt_screening,
                $meta_box['context'],
                $meta_box['priority']
            );
        }
    }



    public function bru_callback($post)
    {
        if (!$this->meta_box) return null;
        $meta_box = $this->process_metabox($this->meta_box);
        $object_type = $this->process_mb_type($meta_box);
        $object_id = get_the_ID();

        // Add nonce only once per page.
        wp_nonce_field('metabox_data', 'wp_meta_box_nonce');

        // Use nonce for verification
        do_action('bru_before_table', $meta_box, $object_id, $object_type);
        echo '<h3> Enter Events and portfolio</h3> ';
        echo '<table class="form-table bru_metabox">';
        foreach ($meta_box['fields'] as $meta_field) {

            $label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
            $field_id = 'id="' . $meta_field['id'] . '" ';
            $field_name = 'name="' . $meta_field['id'] . '" ';
            $placeholder = 'placeholder="' . $meta_field['placeholder'] . '" ';
            $field_type = 'type="' . $meta_field['type'] . '" ';
            $style = 'style="width: 100%; margin-bottom:20px" ';


            $meta_value = get_post_meta($object_id, $meta_field['id'], true);
            if (empty($meta_value)) {
                if (isset($meta_field['default'])) {
                    $meta_value = $meta_field['default'];
                }
            }
            $value = 'value="' . $meta_value . '" ';

            // echo '<br>****$object_id ***<br>';
            // print_r($object_id);
            // echo '<br>****$field_id***<br>';
            // print_r($meta_field['id']);
            // echo '<br>*****$value***<br>';
            // print_r($this->nonce_added);
            // echo '<br>******<br>';
            // echo '<br>*****$value***<br>';
            // print_r(get_post_meta($object_id));
            // echo '<br>******<br>';

            echo $label;
            switch ($meta_field['type']) {
                case 'textarea':
                    echo sprintf('<textarea %s id="%s" name="%s" placeholder="%s" style="width: 100%" rows="5">%s</textarea>'),
                    'style="width: 100%"',
                    $meta_field['id'],
                    $meta_field['id'],
                    $meta_field['placeholder'],
                    $meta_value;
                    break;

                default:
                    echo '<input ' . $field_id . $field_name . $field_type . $placeholder . $style . $value . '>';
            }
        }



        echo '</table>';
        do_action('bru_after_table', $meta_box, $object_id, $object_type);
    }


    public function save_fields($post_id)
    {


        if (!isset($_POST['wp_meta_box_nonce']))
            return $post_id;
        $nonce = $_POST['wp_meta_box_nonce'];
        if (!wp_verify_nonce($nonce, 'metabox_data'))
            return $post_id;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        foreach ($this->meta_box['fields'] as $meta_field) {
            if (isset($_POST[$meta_field['id']])) {
                switch ($meta_field['type']) {
                    case 'email':
                        $_POST[$meta_field['id']] = sanitize_email($_POST[$meta_field['id']]);
                        break;
                    case 'text':
                        $_POST[$meta_field['id']] = sanitize_text_field($_POST[$meta_field['id']]);
                        break;
                }
                update_post_meta($post_id, $meta_field['id'], $_POST[$meta_field['id']]);
            } else if ($meta_field['type'] === 'checkbox') {
                update_post_meta($post_id, $meta_field['id'], '0');
            }
        }
    }
}
