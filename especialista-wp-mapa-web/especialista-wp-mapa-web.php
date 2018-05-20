<?php
/**
 * @package EspecialistaWPMapaWeb
 * @version 1.0
 */
/*
Plugin Name: Especialista WP Mapa Web
Plugin URI: https://especialistawp.com/especialistas-wordpress-mapa-web/
Description: Este plugin permite crear mapas webs para tu Wordpress a través del código corto [mapaweb].
Version: 1.0
Author: especialistawp.com
Author URI: https://especialistawp.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

//Shortcodes
function especialista_wp_mapa_web($params = array(), $content) {
	$html = "<h2>".__("Páginas", "especialista_wp_mapa_web")."</h2>"; 
	$html .= especialista_wp_mapa_pages_recursive(0, 0);

	foreach (get_option("_especialistas_wp_mapa_web_taxonomies") as $taxonomy) {
		if($taxonomy == 'category') {
			$html .= "<h2>".__("Categorías", "especialista_wp_mapa_web")."</h2>";
			$html .= "<ul class='webmap'>";
			$html .= wp_list_categories(array('hide_title_if_empty' => true, 'title_li' => "", "echo" => false));
			$html .= "</ul>";
		} else {
			if($taxonomy == 'post_tag') $html .= "<h2>".__("Etiquetas", "especialista_wp_mapa_web")."</h2>";
			$html .= "<ul class='webmap'>";
			foreach (get_terms($taxonomy, array('hide_empty' => false)) as $term) {
				$html .= "<li><a href='".get_term_link($term->term_id, $taxonomy)."'>".$term->name."</a></li>";
				//$html .= get_the_term_list("", $taxonomy, '<ul class="webmap"><li>', ',</li><li>', '</li></ul>' );
			}
			$html .= "</ul>";
		}
	}

	if(get_option("_especialistas_wp_mapa_web_posts") == 1) {
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => -1,
			'post_status' => 'publish',
		);
		$posts = get_posts($args); 
		if (count($posts) > 0) {
			$html .= "<h2>".__("Posts", "especialista_wp_mapa_web")."</h2>"; 
			$html .= "<ul class='webmap'>";
			foreach($posts as $post) { 
				$html .= "<li><a href='".get_the_permalink($post->ID)."'>".get_the_title($post->ID)."</a></li>";
			}
			$html .= "</ul>";
		}
	}
	//print_r(get_option("_especialistas_wp_mapa_web_other_posts"));
	foreach (get_option("_especialistas_wp_mapa_web_other_posts") as $custom_post_type) {
		$args = array(
			'post_type' => $custom_post_type,
			'posts_per_page' => -1,
			'post_status' => 'publish',
		);
		$posts = get_posts($args);  
		if (count($posts) > 0) { 
			$html .= "<h2>".$custom_post_type."</h2>"; 
			$html .= "<ul class='webmap'>";
			foreach($posts as $post) { 
				$html .= "<li><a href='".get_the_permalink($post->ID)."'>".get_the_title($post->ID)."</a></li>";
			}
			$html .= "</ul>";
		} 
		
	}

	return $html;
}
add_shortcode('mapaweb', 'especialista_wp_mapa_web');

//Administrador 
add_action( 'admin_menu', 'especialista_wp_mapa_web_plugin_menu' );
function especialista_wp_mapa_web_plugin_menu() {
	add_options_page( __('Mapa Web', 'especialista_wp_mapa_web'), __('Mapa Web', 'especialista_wp_mapa_web'), 'manage_options', 'especialista_wp_mapa_web', 'especialista_wp_mapa_web_page_settings');
}

function especialista_wp_mapa_web_page_settings() { 
	$show_post_types = get_post_types();
	unset($show_post_types['attachment']);
	unset($show_post_types['revision']);
	unset($show_post_types['nav_menu_item']);
	unset($show_post_types['custom_css']);
	unset($show_post_types['customize_changeset']);
	unset($show_post_types['oembed_cache']);
	unset($show_post_types['post']);
	unset($show_post_types['page']);

	$show_taxonomies = get_taxonomies();
	unset($show_taxonomies['nav_menu']);
	unset($show_taxonomies['link_category']);
	unset($show_taxonomies['post_format']);
	unset($show_taxonomies['category']);
	unset($show_taxonomies['post_tag']);

	?><h1><?php _e("Mapa Web", "especialista_wp_mapa_web"); ?></h1><?php 
	if(isset($_REQUEST['send']) && $_REQUEST['send'] != '') { 
		?><p style="border: 1px solid green; color: green; text-align: center;"><?php _e("Datos guardados correctamente.", "especialista_wp_mapa_web"); ?></p><?php
		update_option('_especialistas_wp_mapa_web_excludes', sanitize_text_field( $_POST['_especialistas_wp_mapa_web_excludes'] ));
		update_option('_especialistas_wp_mapa_web_taxonomies', $_POST['_especialistas_wp_mapa_web_taxonomies']);
		update_option('_especialistas_wp_mapa_web_posts', $_POST['_especialistas_wp_mapa_web_posts']);
		update_option('_especialistas_wp_mapa_web_other_posts', $_POST['_especialistas_wp_mapa_web_other_posts']);
	} ?>
	<form method="post">
		<h2><?php _e("Posts", "especialista_wp_mapa_web"); ?>:</h2>
		<input type="checkbox" name="_especialistas_wp_mapa_web_posts" value="1"<?php if(get_option("_especialistas_wp_mapa_web_posts") == 1) echo " checked='checked'"; ?> /> <?php _e("Mostrar posts", "especialista_wp_mapa_web"); ?></br></br>
		<input type="text" name="_especialistas_wp_mapa_web_excludes" value="<?php echo get_option("_especialistas_wp_mapa_web_excludes"); ?>" placeholder='<?php _e("separados por comas", "especialista_wp_mapa_web"); ?>' /> <?php _e("IDs de post y páginas excluidas", "especialista_wp_mapa_web"); ?><br/><br/>
		<h3><?php _e("Otros posts", "especialista_wp_extractor"); ?>:</h3>
		<?php foreach ($show_post_types as $custom_post_type) { ?>
			<input type="checkbox" name="_especialistas_wp_mapa_web_other_posts[]" value="<?php echo $custom_post_type; ?>"<?php if(in_array($custom_post_type, get_option("_especialistas_wp_mapa_web_other_posts"))) echo " checked='checked'"; ?> /> <?php echo $custom_post_type; ?></br></br>
		<?php } ?>
		<h3><?php _e("Categorías y etiquetas", "especialista_wp_extractor"); ?>:</h3>
		<input type="checkbox" name="_especialistas_wp_mapa_web_taxonomies[]" value="category"<?php if(in_array("category", get_option("_especialistas_wp_mapa_web_taxonomies"))) echo " checked='checked'"; ?> /> <?php _e("Categorías", "especialista_wp_mapa_web"); ?></br></br>
		<input type="checkbox" name="_especialistas_wp_mapa_web_taxonomies[]" value="post_tag"<?php if(in_array("post_tag", get_option("_especialistas_wp_mapa_web_taxonomies"))) echo " checked='checked'"; ?> /> <?php _e("Etiquetas", "especialista_wp_mapa_web"); ?></br></br>
		<h3><?php _e("Otras taxonomías", "especialista_wp_extractor"); ?>:</h3>
		<?php foreach ($show_taxonomies as $taxonomy) { ?>
			<input type="checkbox" name="_especialistas_wp_mapa_web_taxonomies[]" value="<?php echo $taxonomy; ?>"<?php if(in_array($taxonomy, get_option("_especialistas_wp_mapa_web_taxonomies"))) echo " checked='checked'"; ?> /> <?php echo $taxonomy; ?></br></br>
		<?php } ?>
		<input type="submit" name="send" class="button button-primary" value="<?php _e("Guardar", "especialista_wp_mapa_web"); ?>" />
	</form>
	<?php
}

function especialista_wp_mapa_pages_recursive($parentId, $lvl){
	$args=array('child_of' => $parentId, 'parent' => $parentId, 'sort_column' => 'menu_order');
	$pages = get_pages($args);
	$html = "";
	if ($pages) {
		if ($lvl == 0) $html .= "<ul class='webmap'>\n\r";
		else $html .= "<ul>\n\r";
		$lvl ++;
		foreach ($pages as $page) {
			if (!in_array($page->ID, explode(",", get_option("_especialistas_wp_mapa_web_excludes")))) $html .= "<li><a href='".get_permalink($page->ID)."'>".$page->post_title."</a>";
			$html .= especialista_wp_mapa_pages_recursive($page->ID, $lvl);
			$html .= "</li>\n\r";
		}
		$html .= "</ul>\n\r";
	}
	return $html;
}
