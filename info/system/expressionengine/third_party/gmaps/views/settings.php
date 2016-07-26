<?php

$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP;
?>

<div class="clear_left">&nbsp;</div>
<div id="save_settings">
    <?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP)?>
    
    <?php
    $this->table->set_template($cp_pad_table_template);
    $this->table->set_heading(
        array('data' => lang(GMAPS_MAP.'_preference'), 'style' => 'width:25%;'),
        lang(GMAPS_MAP.'_setting')
    );
    foreach ($settings['default'] as $key => $val)
    {
    	//subtext
    	$subtext = '';
        $extra_html = '';
    	if(is_array($val))
    	{
    	    $subtext = isset($val[1]) ? '<div class="subtext">'.$val[1].'</div>' : '' ;
            $extra_html = isset($val[2]) ? '<div class="extra_html">'.$val[2].'</div>' : '' ;
            $val = $val[0];
    	}
        $this->table->add_row(lang($key, $key).$subtext, $val.$extra_html);
    }
    echo $this->table->generate();
    ?>


    <?php
    $this->table->set_template($cp_pad_table_template);
    $this->table->set_heading(
        array('data' => lang(GMAPS_MAP.'_geocoding_providers'), 'style' => 'width:25%;'),
        lang(GMAPS_MAP.'_setting')
    );
    foreach ($settings['geocoding_providers'] as $key => $val)
    {
        //subtext
        $subtext = '';
        $extra_html = '';
        if(is_array($val))
        {
            $subtext = isset($val[1]) ? '<div class="subtext">'.$val[1].'</div>' : '' ;
            $extra_html = isset($val[2]) ? '<div class="extra_html">'.$val[2].'</div>' : '' ;
            $val = $val[0];
        }
        $this->table->add_row(lang($key, $key).$subtext, $val.$extra_html);
    }
    echo $this->table->generate();
    ?>
    
    
    <p><?=form_submit('submit', lang('submit'), 'class="submit"')?> <a href="<? echo $base_url?>&method=settings&action=reset">Reset Geocoding Providers</a></p>
    <?php $this->table->clear()?>
    <?=form_close()?>
</div>

<script>
	$(function(){
		$('#save_settings form').submit(function(){
			$('#provider_google_maps').attr('disabled', false);
		});
	});
</script>