<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Post Navigation
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'labels' => 'yes',
    'prev_label' =>'',
    'next_label' =>'',
    'arrows' => 'yes',
    'prev_arrow' => '',
    'next_arrow' => '',
    'same_cat' => 'no',
    'css' => '',
    'animation_effect' => ''
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$mod_name=$args['mod_name'];
$element_id =!empty($args['element_id'])?'tb_' . $args['element_id']:$args['module_ID'];
$builder_id=$args['builder_id'];
$container_class = apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id,
    $fields_args['css'],
    self::parse_animation_effect($fields_args['animation_effect'], $fields_args)
    ), $mod_name, $element_id, $fields_args );
   
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
$container_props = apply_filters('themify_builder_module_container_props', array(
    'class' =>  implode(' ', $container_class),
    ), $fields_args, $mod_name, $element_id);
$args=null;
?>
<!-- Post Navigation module -->
<div <?php echo self::get_element_attributes( self::sticky_element_props( $container_props, $fields_args ) ); ?>>
	<?php
	    $container_props=$container_class=null;
	    $found=false;
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	    $the_query = Tbp_Utils::get_actual_query();
	    if ($the_query===null || $the_query->have_posts() ){
		if($the_query!==null){
		    $the_query->the_post();
		}
		$isPrev = get_previous_post_link()?true:false;
		if($isPrev===true || get_next_post_link()){
		    $same_cat = 'yes' === $fields_args['same_cat'];
		    $arrows = array('prev','next');
		    $found=true;
		    foreach($arrows as $ar){
				$text = '';
		        if('yes' === $fields_args['arrows']){
					$arrow = 'yes' === $fields_args['arrows'] ? '' !== $fields_args[$ar.'_arrow'] ? '<span class="ti ' . $fields_args[$ar.'_arrow'] . '"></span>' : 'prev' === $ar ? '&laquo;' : '&raquo;' : '' ;
					$text = '<span class="tbp_post_navigation_arrow">'.$arrow.'</span>';
				}
			$label ='yes' === $fields_args['labels']  ? $fields_args[$ar.'_label']: '';
			$p='';
			if($ar==='prev'){
			    if($isPrev===true){
				$p = get_adjacent_post( $same_cat, '', true );
			    }
			}
			elseif(get_next_post_link()){
			    $p = get_adjacent_post( $same_cat, '', false );
			}
			if($p!==''){
			    if($ar==='prev'){
				previous_post_link( '%link', $text . '<span class="tbp_post_navigation_content_wrapper">' . '<span class="tbp_post_navigation_label">' . $label . '</span>' . '<br/>' . '<span class="tbp_post_navigation_title">' . $p->post_title, $same_cat . '</span>' . '</span>');
			    }
			    else{
				next_post_link( '%link', $text . '<span class="tbp_post_navigation_content_wrapper">' . '<span class="tbp_post_navigation_label">' . $label . '</span>' . '<br/>' . '<span class="tbp_post_navigation_title">' . $p->post_title, $same_cat . '</span>' . '</span>');
			    }
			}
		    }
		}
		if($the_query!==null){
		    wp_reset_postdata();
		}
	} ?>
    <?php if($found===false && (Tbp_Utils::$isActive===true || Themify_Builder::$frontedit_active===true)):?>
	<div class="tbp_empty_module">
	    <?php echo Themify_Builder_Model::get_module_name($mod_name);?>
	</div>
    <?php endif; ?>
</div>
<!-- /Post Navigation module -->
