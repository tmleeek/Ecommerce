<div id="low-find-replace">

	<form method="post" action="<?=$base_url?>&amp;method=find&amp;preview=yes">
		<div>
			<input type="hidden" name="<?=$csrf_token_name?>" value="<?=$csrf_token_value?>" />
		</div>

		<div class="low-sidebar low-tabs" data-names="legend">

			<div class="low-tabs-pages">

				<fieldset class="low-tab active">
					<legend><?=lang('channels')?></legend>

					<div class="low-boxes">
						<label><input type="checkbox" class="low-select-all" /> <?=lang('select_all')?></label>
					</div>

					<?php foreach ($channels AS $channel_id => $row): ?>

					<div class="low-boxes">
						<h4><span><?=htmlspecialchars($row['channel_title'])?></span></h4>
						<?php foreach ($row['fields'] AS $field_id => $field_name): ?>
							<label>
								<input type="checkbox" name="fields[<?=$channel_id?>][]" value="<?=$field_id?>" />
								<?=htmlspecialchars($field_name)?>
							</label>
						<?php endforeach; ?>
					</div>
					<?php endforeach; ?>

				</fieldset>

				<?php if ($categories): ?>
					<fieldset class="low-tab">
						<legend><?=lang('categories')?></legend>

						<div class="low-boxes">
							<label><input type="checkbox" class="low-select-all" /> <?=lang('select_all')?></label>
						</div>

						<?php foreach ($categories AS $group_id => $row): ?>
						<div class="low-boxes">
							<h4><span><?=htmlspecialchars($row['group_name'])?></span></h4>
							<?php foreach ($row['cats'] AS $cat_id => $cat): ?>
								<label>
									<?=$cat['indent']?>
									<input type="checkbox" name="cats[]" value="<?=$cat_id?>" />
									<?=$cat['name']?>
								</label>
							<?php endforeach; ?>
						</div>
						<?php endforeach; ?>

					</fieldset>
				<?php endif; ?>

			</div> <!-- .low-tabs-pages -->
		</div> <!-- .low-sidebar -->

		<div class="low-inline-form">
			<label for="low-keywords"><?=lang('find')?>:</label>
			<input type="text" id="low-keywords" name="keywords" />
			<button class="submit" type="submit"><?=lang('show_preview')?></button>
		</div>

	</form>

	<div class="low-content">
		<div class="low-dynamic-content">
			<?php if (isset($feedback)) include(PATH_THIRD.'/low_search/views/ajax_replace_feedback.php'); ?>
			<?php if (isset($preview))  include(PATH_THIRD.'/low_search/views/ajax_preview.php'); ?>
		</div>
	</div>

</div>