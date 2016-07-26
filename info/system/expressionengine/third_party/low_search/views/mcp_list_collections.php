<p><a class="submit" href="<?=$new_collection_url?>">+ <?=lang('new_collection')?></a></p>

<?php if (empty($collections)): ?>

	<p><?=lang('no_collections_exist')?></p>

<?php else: ?>

	<table cellpadding="0" cellspacing="0" class="mainTable low-list" id="low-search-collections">
		<colgroup>
			<col style="width:5%" />
			<col style="width:15%" />
			<col style="width:15%" />
			<col style="width:15%" />
			<col style="width:45%" />
			<col style="width:5%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col">#</th>
				<th scope="col"><?=lang('collection_label')?></th>
				<th scope="col"><?=lang('collection_name')?></th>
				<th scope="col"><?=lang('channel')?></th>
				<th scope="col"><?=lang('index_options')?></th>
				<th scope="col"><?=lang('delete')?></th>
			</tr>
		</thead>
		<?php if (count($collections) > 1): ?>
			<tfoot>
				<tr>
					<td colspan="4"></td>
					<td class="low-build-all index-options">
						<?=lang('build_index')?> :
						<a href="#" data-build="index"><?=lang('all_indexes')?></a> :
						<a href="#" data-build="lexicon"><?=lang('all_lexicons')?></a> :
						<a href="#" data-build="both"><?=lang('everything')?></a>
					</td>
					<td></td>
				</tr>
			</tfoot>
		<?php endif; ?>
		<tbody>
			<?php foreach ($collections AS $row): ?>
				<tr class="<?=low_zebra()?>">
					<td><?=$row['collection_id']?></td>
					<td><a href="<?=$base_url?>&amp;method=edit_collection&amp;collection_id=<?=$row['collection_id']?>" title="<?=lang('edit_preferences')?>"><?=htmlspecialchars($row['collection_label'])?></a></td>
					<td><?=htmlspecialchars($row['collection_name'])?></td>
					<td><?=htmlspecialchars($row['channel'])?></td>
					<?php if (empty($totals[$row['channel_id']])): ?>
						<td><?=lang('no_entries')?></td>
					<?php else: ?>
						<td
							class="low-index"
							data-total="<?=$totals[$row['channel_id']]?>"
							data-collection="<?=$row['collection_id']?>"
							data-lexicon="<?=($row['language']?'true':'false')?>"
						>
							<div class="index-options">
								<span><?=number_format($totals[$row['channel_id']])?></span>
								<?=lang('build_index')?>
								<?php foreach ($row['index_options'] AS $option): ?>
									 : <a href="#" data-build="<?=$option?>"><?=lang($option)?></a>
								<?php endforeach; ?>
								<?php if ($row['index_status'] != 'ok'): ?>
									: <em><?=lang('index_status_'.$row['index_status'])?></em>
								<?php endif; ?>
							</div>
						</td>
					<?php endif; ?>
					<td>
						<a href="<?=$base_url?>&amp;method=delete_collection_confirm&amp;collection_id=<?=$row['collection_id']?>">
							<img src="<?=$themes_url?>cp_themes/default/images/icon-delete.png" alt="<?=lang('delete')?>" />
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>