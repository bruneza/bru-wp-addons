<?php

namespace BRU_Addons\CPT\CONF;


// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * Custom Post Type Class
 */

class Post_type_init
{

	public $cpt_name;
	public $singular;
	public $plural;
	public $slug;
	public $options;
	public $taxonomies;
	public $taxonomy_settings;
	public $filters;
	public $columns;
	public $custom_populate_columns;
	public $sortable;
	public $textdomain = 'bruneza';


	/**
	 * Constructor
	 */
	function __construct($cpt_names, $options = array())
	{

		// ANCHOR : Check if post type names is a string or an array.
		if (is_array($cpt_names)) {
			$names = array(
				'singular',
				'plural',
				'slug'
			);

			// ANCHOR : Set the post type name.
			$this->cpt_name = $cpt_names['cpt_name'];

			// ANCHOR : Cycle through possible names.
			foreach ($names as $name) {
				if (isset($cpt_names[$name])) {
					$this->$name = $cpt_names[$name];
				} else {
					$method = 'get_' . $name;
					$this->$name = $this->$method();
				}
			}
		} else {
			$this->cpt_name = $cpt_names;
			$this->slug = $this->get_slug();
			$this->plural = $this->get_plural();
			$this->singular = $this->get_singular();
		}

		$this->options = $options;

		$this->add_action('init', array(&$this, 'register_post_type'));
		$this->add_action('init', array(&$this, 'register_taxonomies'));
		$this->add_filter('manage_edit-' . $this->cpt_name . '_columns', array(&$this, 'add_admin_columns'));
		$this->add_action('manage_' . $this->cpt_name . '_posts_custom_column', array(&$this, 'populate_admin_columns'), 10, 2);
		$this->add_action('restrict_manage_posts', array(&$this, 'add_taxonomy_filters'));
	}

	/**
	 * Get
	 *
	 * Helper function to get an object variable.
	 *
	 * @param string $var The variable you would like to retrieve.
	 * @return mixed Returns the value on success, boolean false whe it fails.
	 */
	function get($var)
	{
		if ($this->$var) {
			return $this->$var;
		} else {
			return false;
		}
	}

	/**
	 * Set
	 *
	 * Helper function used to set an object variable. Can overwrite existsing
	 * variables or create new ones. Cannot overwrite reserved variables.
	 *
	 * @param mixed $var The variable you would like to create/overwrite.
	 * @param mixed $value The value you would like to set to the variable.
	 */

	function set($var, $value)
	{
		$reserved = array(
			'config',
			'cpt_name',
			'singular',
			'plural',
			'slug',
			'options',
			'taxonomies'
		);

		if (!in_array($var, $reserved)) {
			$this->$var = $value;
		}
	}

	/**
	 * Add Action
	 *
	 * Helper function to add add_action WordPress filters.
	 *
	 * @param string $action Name of the action.
	 * @param string $function Function to hook that will run on action.
	 * @param integet $priority Order in which to execute the function, relation to other functions hooked to this action.
	 * @param integer $accepted_args The number of arguments the function accepts. 
	 */
	function add_action($action, $function, $priority = 10, $accepted_args = 1)
	{
		add_action($action, $function, $priority, $accepted_args);
	}

	/**
	 * Add Filter
	 *
	 * Create add_filter WordPress filter.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/add_filter
	 *
	 * @param  string  $action           Name of the action to hook to, e.g 'init'.
	 * @param  string  $function         Function to hook that will run on @action.
	 * @param  int     $priority         Order in which to execute the function, relation to other function hooked to this action.
	 * @param  int     $accepted_args    The number of arguements the function accepts.
	 */
	function add_filter($action, $function, $priority = 10, $accepted_args = 1)
	{
		add_filter($action, $function, $priority, $accepted_args);
	}

	/**
	 * Get slug
	 *
	 * Creates an url friendly slug.
	 *
	 * @param  string $name Name to slugify.
	 * @return string $name Returns the slug.
	 */
	function get_slug($name = null)
	{
		if (!isset($name)) {

			$name = $this->cpt_name;
		}

		$name = strtolower($name);
		$name = str_replace(" ", "-", $name);
		$name = str_replace("_", "-", $name);

		return $name;
	}

	/**
	 * Get plural
	 *
	 * Returns the friendly plural name.
	 *
	 *    ucwords      capitalize words
	 *    strtolower   makes string lowercase before capitalizing
	 *    str_replace  replace all instances of _ to space
	 *
	 * @param  string $name The slug name you want to pluralize.
	 * @return string the friendly pluralized name.
	 */
	function get_plural($name = null)
	{
		if (!isset($name)) {
			$name = $this->cpt_name;
		}
		return $this->get_human_friendly($name) . 's';
	}

	/**
	 * Get singular
	 *
	 * Returns the friendly singular name.
	 *
	 *    ucwords      capitalize words
	 *    strtolower   makes string lowercase before capitalizing
	 *    str_replace  replace all instances of _ to space
	 *
	 * @param string $name The slug name you want to unpluralize.
	 * @return string The friendly singular name.
	 */
	function get_singular($name = null)
	{
		if (!isset($name)) {
			$name = $this->cpt_name;
		}
		return $this->get_human_friendly($name);
	}

	/**
	 * Get human friendly
	 *
	 * Returns the human friendly name.
	 *
	 *    ucwords      capitalize words
	 *    strtolower   makes string lowercase before capitalizing
	 *    str_replace  replace all instances of hyphens and underscores to spaces
	 *
	 * @param string $name The name you want to make friendly.
	 * @return string The human friendly name.
	 */
	function get_human_friendly($name = null)
	{
		if (!isset($name)) {
			$name = $this->cpt_name;
		}
		return ucwords(strtolower(str_replace("-", " ", str_replace("_", " ", $name))));
	}

	/**
	 * Register Post Type
	 *
	 * @see http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function register_post_type()
	{

		// ANCHOR : Friendly post type names.
		$plural   = $this->plural;
		$singular = $this->singular;
		$slug     = $this->slug;

		// ANCHOR : Default labels.
		$labels = array(
			'name'               => sprintf(__('%s', $this->textdomain), $plural),
			'singular_name'      => sprintf(__('%s', $this->textdomain), $singular),
			'menu_name'          => sprintf(__('%s', $this->textdomain), $plural),
			'all_items'          => sprintf(__('%s', $this->textdomain), $plural),
			'add_new'            => __('Add New', $this->textdomain),
			'add_new_item'       => sprintf(__('Add New %s', $this->textdomain), $singular),
			'edit_item'          => sprintf(__('Edit %s', $this->textdomain), $singular),
			'new_item'           => sprintf(__('New %s', $this->textdomain), $singular),
			'view_item'          => sprintf(__('View %s', $this->textdomain), $singular),
			'search_items'       => sprintf(__('Search %s', $this->textdomain), $plural),
			'not_found'          => sprintf(__('No %s found', $this->textdomain), $plural),
			'not_found_in_trash' => sprintf(__('No %s found in Trash', $this->textdomain), $plural),
			'parent_item_colon'  => sprintf(__('Parent %s:', $this->textdomain), $singular)
		);

		// ANCHOR : Default options.
		$defaults = array(
			'labels' => $labels,
			'public' => true,
			'rewrite' => array(
				'slug' => $slug,
			)
		);
		$options = array_replace_recursive($defaults, $this->options);
		$this->options = $options;
		if (!post_type_exists($this->cpt_name)) {
			register_post_type($this->cpt_name, $options);
		}
	}

	/**
	 * Register taxonomy
	 *
	 * @see http://codex.wordpress.org/Function_Reference/register_taxonomy
	 *
	 * @param string $taxonomy_name The slug for the taxonomy.
	 * @param array  $options Taxonomy options.
	 */
	function register_taxonomy($taxonomy_names, $options = array())
	{

		// ANCHOR : Post type defaults to $this post type if unspecified.
		$post_type = $this->cpt_name;

		// ANCHOR : An array of the names required excluding taxonomy_name.
		$names = array(
			'singular',
			'plural',
			'slug'
		);

		if (is_array($taxonomy_names)) {
			$taxonomy_name = $taxonomy_names['taxonomy_name'];
			foreach ($names as $name) {
				if (isset($taxonomy_names[$name])) {
					$$name = $taxonomy_names[$name];
				} else {
					$method = 'get_' . $name;
					$$name = $this->$method($taxonomy_name);
				}
			}
		} else {
			$taxonomy_name = $taxonomy_names;
			$singular = $this->get_singular($taxonomy_name);
			$plural   = $this->get_plural($taxonomy_name);
			$slug     = $this->get_slug($taxonomy_name);
		}

		// ANCHOR : Default labels.
		$labels = array(
			'name'                       => sprintf(__('%s', $this->textdomain), $plural),
			'singular_name'              => sprintf(__('%s', $this->textdomain), $singular),
			'menu_name'                  => sprintf(__('%s', $this->textdomain), $plural),
			'all_items'                  => sprintf(__('All %s', $this->textdomain), $plural),
			'edit_item'                  => sprintf(__('Edit %s', $this->textdomain), $singular),
			'view_item'                  => sprintf(__('View %s', $this->textdomain), $singular),
			'update_item'                => sprintf(__('Update %s', $this->textdomain), $singular),
			'add_new_item'               => sprintf(__('Add New %s', $this->textdomain), $singular),
			'new_item_name'              => sprintf(__('New %s Name', $this->textdomain), $singular),
			'parent_item'                => sprintf(__('Parent %s', $this->textdomain), $plural),
			'parent_item_colon'          => sprintf(__('Parent %s:', $this->textdomain), $plural),
			'search_items'               => sprintf(__('Search %s', $this->textdomain), $plural),
			'popular_items'              => sprintf(__('Popular %s', $this->textdomain), $plural),
			'separate_items_with_commas' => sprintf(__('Seperate %s with commas', $this->textdomain), $plural),
			'add_or_remove_items'        => sprintf(__('Add or remove %s', $this->textdomain), $plural),
			'choose_from_most_used'      => sprintf(__('Choose from most used %s', $this->textdomain), $plural),
			'not_found'                  => sprintf(__('No %s found', $this->textdomain), $plural),
		);

		// ANCHOR : Default options.
		$defaults = array(
			'labels' => $labels,
			'hierarchical' => true,
			'rewrite' => array(
				'slug' => $slug
			)
		);

		$options = array_replace_recursive($defaults, $options);
		$this->taxonomies[] = $taxonomy_name;
		$this->taxonomy_settings[$taxonomy_name] = $options;
	}



	/**
	 * Register taxonomies
	 *
	 * Cycles through taxonomies added with the class and registers them.
	 */
	function register_taxonomies()
	{

		if (is_array($this->taxonomy_settings)) {
			foreach ($this->taxonomy_settings as $taxonomy_name => $options) {
				if (!taxonomy_exists($taxonomy_name)) {
					register_taxonomy($taxonomy_name, $this->cpt_name, $options);
				} else {
					register_taxonomy_for_object_type($taxonomy_name, $this->cpt_name);
				}
			}
		}
	}



	/**
	 * Add admin columns
	 *
	 * Adds columns to the admin edit screen. Function is used with add_action
	 *
	 * @param array $columns Columns to be added to the admin edit screen.
	 * @return array
	 */
	function add_admin_columns($columns)
	{
		if (!isset($this->columns)) {
			$columns = array(
				'cb' => '<input type="checkbox" />',
				'title' => __('Title', $this->textdomain)
			);
			if (is_array($this->taxonomies)) {
				foreach ($this->taxonomies as $tax) {
					$taxonomy_object = get_taxonomy($tax);
					$columns[$tax] = sprintf(__('%s', $this->textdomain), $taxonomy_object->labels->name);
				}
			}
			if (post_type_supports($this->cpt_name, 'comments')) {

				$columns['comments'] = '<img alt="Comments" src="' . site_url() . '/wp-admin/images/comment-grey-bubble.png">';
			}
			$columns['date'] = __('Date', $this->textdomain);
		} else {
			$columns = $this->columns;
		}

		return $columns;
	}

	/**
	 * Populate admin columns
	 *
	 * Populate custom columns on the admin edit screen.
	 *
	 * @param string $column The name of the column.
	 * @param integer $post_id The post ID.
	 */
	function populate_admin_columns($column, $post_id)
	{
		global $post;
		switch ($column) {
			case (taxonomy_exists($column)):

				// ANCHOR : Get the taxonomy for the post
				$terms = get_the_terms($post_id, $column);
				if (!empty($terms)) {
					$output = array();
					foreach ($terms as $term) {
						$output[] = sprintf(
							'<a href="%s">%s</a>',
							esc_url(add_query_arg(array('post_type' => $post->post_type, $column => $term->slug), 'edit.php')),
							esc_html(sanitize_term_field('name', $term->name, $term->term_id, $column, 'display'))
						);
					}
					echo join(', ', $output);
				} else {
					$taxonomy_object = get_taxonomy($column);
					printf(__('No %s', $this->textdomain), $taxonomy_object->labels->name);
				}

				break;
			case 'post_id':
				echo $post->ID;
				break;

			case (preg_match('/^meta_/', $column) ? true : false):
				$x = substr($column, 5);

				$meta = get_post_meta($post->ID, $x);
				echo join(", ", $meta);

				break;
			case 'icon':
				$link = esc_url(add_query_arg(array('post' => $post->ID, 'action' => 'edit'), 'post.php'));
				if (has_post_thumbnail()) {
					echo '<a href="' . $link . '">';
					the_post_thumbnail(array(60, 60));
					echo '</a>';
				} else {
					echo '<a href="' . $link . '"><img src="' . site_url('/wp-includes/images/crystal/default.png') . '" alt="' . $post->post_title . '" /></a>';
				}

				break;
			default:
				if (isset($this->custom_populate_columns) && is_array($this->custom_populate_columns)) {
					if (isset($this->custom_populate_columns[$column]) && is_callable($this->custom_populate_columns[$column])) {
						$this->custom_populate_columns[$column]($column, $post);
					}
				}

				break;
		}
	}

	/**
	 * Filters
	 *
	 * User function to define which taxonomy filters to display on the admin page.
	 *
	 * @param array $filters An array of taxonomy filters to display.
	 */
	function filters($filters = array())
	{
		$this->filters = $filters;
	}

	/**
	 *  Add taxtonomy filters
	 *
	 * Creates select fields for filtering posts by taxonomies on admin edit screen.
	 */
	function add_taxonomy_filters()
	{
		global $typenow;
		global $wp_query;
		if ($typenow == $this->cpt_name) {
			if (is_array($this->filters)) {
				$filters = $this->filters;
			} else {
				$filters = $this->taxonomies;
			}

			if (!empty($filters)) {
				foreach ($filters as $tax_slug) {
					$tax = get_taxonomy($tax_slug);
					$args = array(
						'orderby' => 'name',
						'hide_empty' => false
					);
					$terms = get_terms($tax_slug, $args);
					if ($terms) {
						printf(' &nbsp;<select name="%s" class="postform">', $tax_slug);
						printf('<option value="0">%s</option>', sprintf(__('Show all %s', $this->textdomain), $tax->label));
						foreach ($terms as $term) {
							if (isset($_GET[$tax_slug]) && $_GET[$tax_slug] === $term->slug) {

								printf('<option value="%s" selected="selected">%s (%s)</option>', $term->slug, $term->name, $term->count);
							} else {

								printf('<option value="%s">%s (%s)</option>', $term->slug, $term->name, $term->count);
							}
						}
						print('</select>&nbsp;');
					}
				}
			}
		}
	}

	/**
	 * Columns
	 *
	 * Choose columns to be displayed on the admin edit screen.
	 *
	 * @param array $columns An array of columns to be displayed.
	 */
	function columns($columns)
	{
		if (isset($columns)) {
			$this->columns = $columns;
		}
	}

	/**
	 * Populate columns
	 *
	 * Define what and how to populate a speicific admin column.
	 *
	 * @param string $column_name The name of the column to populate.
	 * @param function $function An anonyous function to run when populating the column.
	 */
	function populate_column($column_name, $function)
	{
		$this->custom_populate_columns[$column_name] = $function;
	}

	/**
	 * Sortable
	 *
	 * Define what columns are sortable in the admin edit screen.
	 *
	 * @param array $columns An array of columns that are sortable.
	 */
	function sortable($columns = array())
	{
		$this->sortable = $columns;
		$this->add_filter('manage_edit-' . $this->cpt_name . '_sortable_columns', array(&$this, 'make_columns_sortable'));
		$this->add_action('load-edit.php', array(&$this, 'load_edit'));
	}

	/**
	 * Make columns sortable
	 *
	 * Internal function that adds user defined sortable columns to WordPress default columns.
	 *
	 * @param array $columns Columns to be sortable.
	 *
	 */
	function make_columns_sortable($columns)
	{
		foreach ($this->sortable as $column => $values) {
			$sortable_columns[$column] = $values[0];
		}
		$columns = array_merge($sortable_columns, $columns);
		return $columns;
	}

	/**
	 * Load edit
	 *
	 * Sort columns only on the edit.php page when requested.
	 *
	 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/request
	 */
	function load_edit()
	{
		$this->add_filter('request', array(&$this, 'sort_columns'));
	}

	/**
	 * Sort columns
	 *
	 * Internal function that sorts columns on request.
	 *
	 * @see load_edit()
	 *
	 * @param array $vars The query vars submitted by user.
	 * @return array A sorted array.
	 */
	function sort_columns($vars)
	{
		foreach ($this->sortable as $column => $values) {
			$meta_key = $values[0];
			if (taxonomy_exists($meta_key)) {
				$key = "taxonomy";
			} else {
				$key = "meta_key";
			}
			if (isset($values[1]) && true === $values[1]) {
				$orderby = 'meta_value_num';
			} else {
				$orderby = 'meta_value';
			}
			if (isset($vars['post_type']) && $this->cpt_name == $vars['post_type']) {
				if (isset($vars['orderby']) && $meta_key == $vars['orderby']) {
					$vars = array_merge(
						$vars,
						array(
							'meta_key' => $meta_key,
							'orderby' => $orderby
						)
					);
				}
			}
		}
		return $vars;
	}

	/**
	 * Set menu icon
	 *
	 * Use this function to set the menu icon in the admin dashboard. Since WordPress v3.8
	 * dashicons are used. For more information see @link http://melchoyce.github.io/dashicons/
	 *
	 * @param string $icon dashicon name
	 */
	function menu_icon($icon = "dashicons-admin-page")
	{
		if (is_string($icon) && stripos($icon, "dashicons") !== false) {
			$this->options["menu_icon"] = $icon;
		} else {
			$this->options["menu_icon"] = "dashicons-admin-page";
		}
	}

	/**
	 * Set textdomain
	 *
	 * @param string $textdomain Textdomain used for translation.
	 */
	function set_textdomain($textdomain)
	{
		$this->textdomain = $textdomain;
	}
}
