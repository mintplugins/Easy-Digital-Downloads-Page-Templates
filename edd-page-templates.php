<?php
/*
Plugin Name: Easy Digital Downloads - Page Templates
Plugin URI: http://moveplugins.com
Description: This plugin allows Downloads created by EDD to use Page Templates
Author: Move Plugins, Hiroaki Miyashita
Version: 1.0
Author URI: http://moveplugins.com
*/

/*  Copyright 2014 Move Plugins

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class edd_page_templates {

	function edd_page_templates() {
		add_action( 'admin_init', array(&$this, 'admin_init') );
		add_action( 'save_post', array(&$this, 'save_post') );
		add_filter( 'template_include', array(&$this, 'template_include') );		
		add_action( 'template_redirect', array(&$this, 'template_redirect') );		
		add_filter( 'body_class', array(&$this, 'body_classes') );
	}
	
	function admin_init() {
		
		add_meta_box( 'pagetemplatediv', __('Page Template', 'custom-post-type-page-template'), array(&$this, 'meta_box'), 'download', 'side', 'core');
			
	}

	function meta_box($post) {
		$template = get_post_meta($post->ID, '_wp_page_template', true);
		?>
		<label class="screen-reader-text" for="page_template"><?php _e('Page Template', 'custom-post-type-page-template') ?></label><select name="page_template" id="page_template">
		<option value='default'><?php _e('Default Template', 'custom-post-type-page-template'); ?></option>
		<?php page_template_dropdown($template); ?>
		</select>
		<?php
	}

	function save_post( $post_id ) {
		if ( !empty($_POST['page_template']) ) :
			if ( $_POST['page_template'] != 'default' ) :
				update_post_meta($post_id, '_wp_page_template', $_POST['page_template']);
			else :
				delete_post_meta($post_id, '_wp_page_template');
			endif;
		endif;
	}

	function template_include($template) {
		global $wp_query, $post;

		if ( is_singular() && !is_page() ) :
			$id = get_queried_object_id();
			$new_template = get_post_meta( $id, '_wp_page_template', true );
			if ( $new_template && file_exists(get_query_template( 'page', $new_template )) ) :
				$wp_query->is_page = 1;
				$templates[] = $new_template;
				return get_query_template( 'page', $templates );
			endif;
		endif;
		return $template;
	}
	
	function template_redirect() {
		global $wp_query;
		
		if ( is_singular() && !is_page() ) :
			wp_cache_delete($wp_query->post->ID, 'posts');
			$GLOBALS['post']->post_type = 'page';
			wp_cache_add($wp_query->post->ID, $GLOBALS['post'], 'posts');
		endif;
	}

	function body_classes( $classes ) {
		if ( is_singular() && is_page_template() ) :
			$classes[] = 'page-template';
			$classes[] = 'page-template-' . sanitize_html_class( str_replace( '.', '-', get_page_template_slug( get_queried_object_id() ) ) );			
		endif;
		return $classes;
	}

}
global $edd_page_templates;
$edd_page_templates = new edd_page_templates();
?>