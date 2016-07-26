<?php 
 $base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=gmaps'.AMP;
?>

<div class="clear_left">&nbsp;</div>

<script>

</script>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP.'method=bulk_add_cache')?>
	<input type="hidden" name="confirm" value="ok"/>

	<textarea name="address" rows="16"></textarea>

	<input type="submit" class="submit" value="<?=lang('add')?>" name="submit">
	</p>
</form>
