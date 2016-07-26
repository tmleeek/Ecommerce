<?php 
 $base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=gmaps'.AMP;
?>

<div class="clear_left">&nbsp;</div>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP.'method=delete_cache')?>
	<input type="hidden" name="confirm" value="ok"/>

	<p><strong>Delete Cache</strong></p>
	<p class="notice">Are you sure you want to delete the Gmaps Cache? This cannot be undone.</p>

	<input type="submit" class="submit" value="<?=lang('delete')?>" name="submit">
	</p>
</form>
