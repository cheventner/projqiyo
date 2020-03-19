<?php
themify_product_image_start(); // Hook 
$product = wc_get_product( get_the_ID() );
$attachment_id = $product->get_image_id();
$html='';
if ( !empty($args['thumb_image_w']) || !empty($args['thumb_image_h']) ) {
	$GLOBALS['themify']->gallery_thumb_size_w = $args['thumb_image_w'];
	$GLOBALS['themify']->gallery_thumb_size_h = $args['thumb_image_h'];
    function tb_pro_set_image_size_gallery_thumbnail($size){
		return array(
			'width'  => empty($GLOBALS['themify']->gallery_thumb_size_w) ? $size['width'] : $GLOBALS['themify']->gallery_thumb_size_w,
			'height' => empty($GLOBALS['themify']->gallery_thumb_size_h) ? $size['height'] : $GLOBALS['themify']->gallery_thumb_size_h,
			'crop'   => 1,
		);
	}
	add_filter( 'woocommerce_get_image_size_gallery_thumbnail', 'tb_pro_set_image_size_gallery_thumbnail',99 );
}
if(!empty($attachment_id)){
    $html = wc_get_gallery_image_html( $attachment_id, true );
    if ( $args['image_w'] !== '' || $args['image_h'] !== '' ) {
	if(!Themify_Builder_Model::is_img_php_disabled()){
		$src=wp_get_attachment_image_src( $attachment_id, 'full' );
		if(!empty($src[0])){
			preg_match( '/src="([^"]+)"/', $html, $image_src );	
			if(!empty($image_src[1])){
			    $url = themify_get_image(array(
				'src'=>$src[0],
				'w'=>$args['image_w'],
				'h'=>$args['image_h'],
				'urlonly'=>true,
				'ignore'=>true
			    ));

			    $html=str_replace($image_src[1],$url,$html);
			    $image_src=$url=null;
			}
		}
	}
	if($args['image_w']!==''){
	    $html=preg_replace('/width=\"([0-9]{1,})\"/','width="'.$args['image_w'].'"',$html);
	}
	if($args['image_h']!==''){
	    $html=preg_replace('/height=\"([0-9]{1,})\"/','height="'.$args['image_h'].'"',$html);
	}
    }
	
}
elseif($args['fallback_s'] === 'yes' && $args['fallback_i'] !== ''){
	//fallback
	$full_src = esc_url($args['fallback_i']);
	if(!Themify_Builder_Model::is_img_php_disabled()){
		$image = themify_get_image(array(
		'src'=>$full_src,
		'w'=>$args['image_w'],
		'h'=>$args['image_h'],
		'ignore'=>true
		));
	}
	else{
		$image = '<img class="wp-post-image" src="' . $full_src . '" width="' . $args['image_w'] . '" height="' . $args['image_h'] . '"/>';
	}
	$html='<div class="woocommerce-product-gallery__image">'.$image.'</div>';
}
$columns = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$wrapper_classes = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
	'woocommerce-product-gallery',
	'image-wrap',
	'woocommerce-product-gallery--' . ( $attachment_id ? 'with-images' : 'without-images' ),
	'woocommerce-product-gallery--columns-' . absint( $columns ),
	'images'
) );
?>
<div class="product <?php echo $args['sale_b'] === 'yes' ? ' sale-badge-' . $args['badge_pos'] : ''; ?>">
	<?php if ($args['sale_b'] === 'yes'):?>
	    <?php woocommerce_show_product_sale_flash();?>
	<?php endif; ?>
	<div class="image-wrap <?php  esc_attr_e( implode( ' ', $wrapper_classes ) ); ?>" data-columns="<?php esc_attr_e( $columns ); ?>">
		<figure class="woocommerce-product-gallery__wrapper">
            <?php echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $attachment_id ); ?>
            <?php do_action( 'woocommerce_product_thumbnails' );?>
		</figure>
	</div>
</div>
<?php
if ( !empty($args['thumb_image_w']) || !empty($args['thumb_image_h']) ) {
	unset($GLOBALS['themify']->gallery_thumb_size_w);
	unset($GLOBALS['themify']->gallery_thumb_size_h);
	remove_filter( 'woocommerce_get_image_size_gallery_thumbnail', 'tb_pro_set_image_size_gallery_thumbnail',99 );
}
themify_product_image_end(); // Hook
