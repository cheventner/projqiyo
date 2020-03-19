<div<?php if(!empty($args['drop_cap'])):?> class="tb_text_dropcap"<?php endif;?>>
    <div class="tb_text_wrap" itemprop="description">
	<?php
	$isLoop=$ThemifyBuilder->in_the_loop===true;
	$ThemifyBuilder->in_the_loop = true;
	if ( isset($args['content_type'] ) && $args['content_type'] === 'excerpt') {
		if(!empty($args['excerpt_length'])){
		    echo wp_trim_words( strip_tags( get_the_content() ), $args['excerpt_length'] );
		}
		else{
		    the_excerpt();
		}
	} else {
	    $more_text = !empty($args['more_text']) ? $args['more_text'] : null;
		the_content($more_text);
	}
	$ThemifyBuilder->in_the_loop = $isLoop;
	$args=null;?>
    </div>
</div>

