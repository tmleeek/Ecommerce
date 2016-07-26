<?=form_open($postUrl, '', $hiddenValues)?>
	<p class="notice"><?php echo lang('blocks_confirmdelete_content'); ?></p>

	<p><?= $blockDefinition->name ?></p>

	<input type="submit" name="submit" value="<?php echo lang('blocks_confirmdelete_submit'); ?>" class="submit">
</form>
