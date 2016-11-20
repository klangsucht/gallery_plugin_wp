<?php
/*
Plugin Name: custom post type modern
Plugin URI: http://modern-war-jetzt.net
Description: custom post types for modern-war-jetzt.net
Version: 1.0
Author: techtalk 3000
Author URI: http://techtalk3000.de/
License: GPLv2
*/

/*
Copyright (c) 2016 techtalk3000

GNU GENERAL PUBLIC LICENSE
   Version 2, June 1991

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA. -->
*/


add_action( 'init', 'add_galerien_type' );

function add_galerien_type() {
	global $wp_rewrite;

	$labels = array(
	'name'               => 'Galerien',
	'singular_name'      => 'Galerie',
	'add_new'            => 'Neue Galerie',
	'add_new_item'       => 'Neue Galerie hinzufügen',
	'edit_item'          => 'Galerie bearbeiten',
	'new_item'           => 'Neue Galerie',
	'view_item'          => 'Galerie zeigen',
	'search_items'       => 'Galerien durchsuchen',
	'not_found'          => 'Keine Galerien gefunden',
	'not_found_in_trash' => 'Keine Galerien im Papierkorb gefunden',
	'parent_item_colon'  => '',
	);
	$args = array(
	'labels'             => $labels,
	'public'             => true,
	'publicly_queryable' => true,
	'show_ui'            => true,
	'show_in_menu'       => true,
	'query_var'          => true,
	'rewrite'            => false,
	'capability_type'    => 'post',
	'has_archive'        => false,
	'hierarchical'       => false,
	'menu_position'      => 5,
	'supports'           => array( 'title', 'thumbnail' ),
	'has_archive'        => true,
	);
	register_post_type( 'galerien', $args );
}

add_action( 'add_meta_boxes', 'galerie_add_custom_box' );

add_action( 'save_post', 'galerie_save_postdata' );

function galerie_add_custom_box() {

	add_meta_box(
		'galerie_beschreibung',
		'Kurze Beschreibung zum Inhalt der Galerie',
		'galerie_beschreibung_custom_box',
		'galerien',
		'normal',
		'high'
	);
	add_meta_box(
		'galerie_bilder',
		'Bilder zur Galerie hinzufügen',
		'galerie_bilder_custom_box',
		'galerien',
		'normal'
	);
}

function galerie_beschreibung_custom_box( $post ) {
	wp_nonce_field( plugin_basename( __FILE__ ), 'galerie_noncename' );

	$galerie_beschreibung_custom_box_stored = get_post_meta( $post->ID );

	?>

<p>
	<textarea name="meta-galerie-beschreibung" id="meta-galerie-beschreibung" style="width: 100%" rows="5"><?php if ( isset( $galerie_beschreibung_custom_box_stored['meta-galerie-beschreibung'] ) ) { echo $galerie_beschreibung_custom_box_stored['meta-galerie-beschreibung'][0];} ?></textarea>
</p>

<?php

}

function galerie_bilder_custom_box() {

		$image_src = '';

		$image_id = get_post_meta( $post->ID, '_image_id', true );
		$image_src = wp_get_attachment_url( $image_id );

		?>
		<img id="galerie_bild" src="<?php echo $image_src ?>" style="max-width:100%;" />
		<input type="hidden" name="upload_image_id" id="upload_image_id" value="<?php echo $image_id; ?>" />
		<p>
			<a title="<?php esc_attr_e( 'Bild hinzugügen' ) ?>" href="#" id="hinzufuegen-galerie-bild"><?php _e( 'Bild hinzufügen' ) ?></a>
			<a title="<?php esc_attr_e( 'Bild entfernen' ) ?>" href="#" id="entferne-galerie-bild" style="<?php echo ( ! $image_id ? 'display:none;' : '' ); ?>"><?php _e( 'Bild entfernen' ) ?></a>
		</p>

		<script type="text/javascript">
		jQuery(document).ready(function($) {

			window.send_to_editor_default = window.send_to_editor;

			$('#hinzufuegen-galerie-bild').click(function(){

				window.send_to_editor = window.attach_image;
				tb_show('', 'media-upload.php?post_id=<?php echo $post->ID ?>&amp;type=image&amp;TB_iframe=true');

				return false;
			});

			$('#entferne-galerie-bild').click(function() {

				$('#upload_image_id').val('');
				$('img').attr('src', '');
				$(this).hide();

				return false;
			});

			window.attach_image = function(html) {

				$('body').append('<div id="temp_image">' + html + '</div>');

				var img = $('#temp_image').find('img');

				imgurl   = img.attr('src');
				imgclass = img.attr('class');
				imgid    = parseInt(imgclass.replace(/\D/g, ''), 10);

				$('#upload_image_id').val(imgid);
				$('#entferne-galerie-bild').show();

				$('img#galerie_bild').attr('src', imgurl);
				try{tb_remove();}catch(e){};
				$('#temp_image').remove();

				window.send_to_editor = window.send_to_editor_default;

			}

		});
		</script>
		<?php

}

function galerie_save_postdata( $post_id ) {

	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST['galerie_noncename'] ) && wp_verify_nonce( $_POST['galerie_noncename'], basename( __FILE__ ) ) ) ? 'true' : 'false';

	if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	if ( isset( $_POST['meta-galerie-beschreibung'] ) ) {
		update_post_meta( $post_id, 'meta-galerie-beschreibung', sanitize_text_field( $_POST['meta-galerie-beschreibung'] ) );
	}

}
