<?php

namespace Owl;

if ( ! defined( 'ABSPATH' ) ) {
 	exit;
}

/**
 * Hook
 */
if ( filter_var( get_option( 'owl_carousel_wordpress_gallery', false ), FILTER_VALIDATE_BOOLEAN ) ) {
 	add_filter( 'post_gallery', 'Owl\owl_carousel_post_gallery', 10, 2 );
}


/**
 * Owl Carousel for Wordpress image gallery
 * @param string $output Gallery output
 * @param array $attr Parameters
 * @return string Owl HTML code
 */
function owl_carousel_post_gallery( $output, $attr ) {

    // Get the gallery attachments
    $attachments = get_gallery_attachments( $attr );

    // owl_custom_default_atts filter
	$attr = apply_filters( 'owl_custom_default_atts', $attr, array() );

	$data_attr = "";

	foreach ( $attr as $key => $value ) {
		if ( $key != "category" ) {
			$data_attr .= ' data-' . strtolower( $key ) . '="' . $value . '" ';
		}
	}

	// Start the output
    $output .= '<div id="owl-carousel-' . rand() . '" class="owl-carousel-plugin" ' . $data_attr . '>';

	foreach ( $attachments as $id => $attachment ) {

        // Values
        $size = apply_filters( 'owl_carousel_wp_gallery_thumbnail_size', 'full' );
		$img = wp_get_attachment_image_src( $id, $size );
		$meta_link = get_post_meta( $id, '_owlurl', true );
		$title = $attachment->post_title;

		$output .= '<div class="item">';
		if ( ! empty( $meta_link ) ) {
			$output .= '<a href="' . $meta_link . '">';
		}

        $output .= '<div class="caption">' . $title . '</div>';

		$output .= '<div class="image" style="
			background-image: url(' . $img[0] . ');
			background-size: cover;
			background-position: center;
			background-repeat: no-repeat;
			height:' . $img[2] . 'px;
			padding-top:' . $img[2] / $img[1] * 100 . '%;
		"></div>';

		if ( ! empty( $meta_link ) ) {
			$output .= '</a>';
		}
		$output .= '</div>';
	}

	$output .= '</div>';

	return $output;
}


/**
 * Get the attachments from the gallery
 * @param  array $attr
 * @return array $attachments
 */
function get_gallery_attachments( $attr ) {
    global $post;

    if ( empty( $attr ) ) {
        return;
    }

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( ! $attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract( shortcode_atts(
        array(
    		'order'      => 'ASC',
    		'orderby'    => 'menu_order ID',
    		'id'         => $post->ID,
    		'itemtag'    => 'dl',
    		'icontag'    => 'dt',
    		'captiontag' => 'dd',
    		'columns'    => 3,
    		'size'       => 'thumbnail',
    		'include'    => '',
    		'exclude'    => ''
        ), $attr ) );

	$id = intval( $id );
	if ( 'RAND' == $order ) $orderby = 'none';

	$include = preg_replace( '/[^0-9,]+/', '', $include );
	$attachments_query = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );

	$attachments = array();
	foreach ( $attachments_query as $key => $val ) {
		$attachments[ $val->ID ] = $attachments_query[ $key ];
	}

	return $attachments;
}
