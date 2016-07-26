<div id="lamplighter" >
<?php if( ! empty($updates->error)) : ?>
	<p><?php echo $updates->error ?></p>
<?php else : ?>
	<p class="last-check">Last Add-on Check: <?php echo date('l, M. j, Y @ g:ia.', $last_check) ?>&nbsp;&nbsp;<a href="<?php echo $update_url; ?>" class="available refresh">Check Now</a>&nbsp;<img class="monitor-loading" src="<?php echo defined("URL_THIRD_THEMES") ? URL_THIRD_THEMES : '/themes'; ?>/lamplighter/images/loader.gif" alt="Loading..." /></p>
	<table>
		<thead>
			<tr class="first">
				<th class="addon-notes">&nbsp;</th>
				<th class="addon-name">Add-On Name</th>
				<th class="addon-installed">Installed</th>
				<th class="addon-current">Latest</th>
				<th class="addon-status"><span>Status</span></th>
				<th class="addon-link">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($updates as $package => $addon) : ?>
				<?php
					if(in_array($package, $hidden_addons) && $show_hidden == false) {
						continue;
					}
					// convert to object if need be on some php versions
					if( ! is_object($addon) ) {
						$addon = (object) $addon;
					}
				?>
				<?php if($addon->update_available) : ?>
					<tr class="update<?php if($addon->notes) : ?> notes<?php endif ?><?php if (in_array($package, $hidden_addons)): ?> hidden_addon<?php endif ?>">
				<?php else : ?>
					<tr class="<?php if (in_array($package, $hidden_addons)) echo 'hidden_addon'; ?>" >
				<?php endif ?>
					<!-- notes -->
					<?php if($addon->update_available && $addon->notes) : ?>
						<td class="addon-notes"><a href="#" class="toggle">+</a></td>
					<?php else : ?>
						<td class="addon-notes">&nbsp;</td>
					<?php endif ?>

					<?php $commercial_designation = isset($addon->license) && $addon->license == 'Commercial' ? "&nbsp;<span class='commercial_designation' >$</span> "  : '' ?>

					<!-- name -->
					<td class="addon-name">
						<?php if (! in_array($package, $hidden_addons) ): ?>
							<?php echo $addon->name ?><span>&nbsp;<a class="hide_addon" href="<?php echo $hide_addon_url.AMP.'package='.$package; ?>">Hide this</a></span>
						<?php else: ?>
							<?php echo $addon->name ?><span>&nbsp;<a class="unhide_addon" href="<?php echo $unhide_addon_url.AMP.'package='.$package; ?>">Unhide this</a></span>
						<?php endif; ?>
					</td>

					<!-- local version -->
					<td class="addon-installed"><?php echo $addon->version ?></td>

					<!-- devot:ee version -->
					<td class="addon-current">
						<?php if($addon->current_version != '') : ?>
							<?php if($addon->update_available) : ?>
								<span class="available"><?php echo $addon->current_version ?></span>
							<?php else : ?>
								<?php echo $addon->current_version ?>
							<?php endif ?>
						<?php else : ?>
							&ndash;
						<?php endif ?>
					</td>

					<!-- status and link -->
					<?php if($addon->devotee_link != '') : ?>
						<?php if($addon->update_available) : ?>
							<td class="addon-status">
								<a href="<?php echo $cp->masked_url($addon->devotee_link); ?>" class="available update" target="_blank">Update Available</a>
							</td>
						<?php else : ?>
							<?php if($addon->current_version == '') : ?>
								<td class="addon-status">
									<span class="warning">Version Info Unavailable - <a href="<?php echo $cp->masked_url($addon->devotee_link); ?>" target="_blank">Details</a></span>
								</td>
							<?php elseif($addon->version != $addon->current_version) : ?>
								<td class="addon-status">
									<span class="warning">Installed version higher than devot:ee's</span>
								</td>
							<?php else : ?>
								<td class="addon-status">
									<span class="check">Up-to-date</span>
								</td>
							<?php endif ?>
						<?php endif ?>
						<td class="addon-link<?php if($addon->update_available) : ?> link-present<?php endif ?>">
							<a href="<?php echo $cp->masked_url($addon->devotee_link); ?>" class="available" target="_blank">
								<span></span>
							</a>
						</td>
					<?php else : ?>
						<td class="addon-status">
							<span class="warning">Not Found - <a href="<?php echo $cp->masked_url('http://devot-ee.com/search/results?keywords='.$addon->name.'&collection=addons&addon_version_support=ee2'); ?>" target="_blank">Search</a></span>
						</td>
						<td class="addon-link">
							<a href="<?php echo $cp->masked_url('http://devot-ee.com/search/results?keywords='.$addon->name.'&collection=addons&addon_version_support=ee2'); ?>" class="available" target="_blank">
								<span></span>
							</a>
						</td>
					<?php endif ?>
				</tr>
				<?php if($addon->update_available && $addon->notes) : ?>
					<tr class="notes">
						<td class="notes" colspan="7">
							<h6>Release Notes</h6>
							<ul>
							<?php
								foreach (explode("\n", $addon->notes) as $note)
								{
									if ($note != '')
										echo "<li>".stripslashes($note)."</li>\n";
								}
							?>
							</ul>
						</td>
					</tr>
				<?php endif ?>
			<?php endforeach ?>
		</tbody>
	</table>
<?php endif ?>

<div id="lamplighter-footer">
	<?php if( ! empty($hidden_addons) && $show_hidden == false) : ?>
		<p class="hidden_addons"><a href="<?php echo $base_url.AMP.'show_hidden=y' ?>" class="show-hidden-addons">Show Hidden Add-ons (<?php echo count($hidden_addons) ?>)</a></p>
	<?php elseif ( ! empty($hidden_addons) && $show_hidden == true) : ?>
		<p class="hidden_addons"><a href="<?php echo $base_url; ?>" class="show-hidden-addons">Hide Previously Hidden Add-ons (<?php echo count($hidden_addons) ?>)</a></p>
	<?php endif ?>
	<p class="Check">Last Add-on Check: <?php echo date('l, M. j, Y @ g:ia.', $last_check) ?>&nbsp;&nbsp;<a href="<?php echo $update_url; ?>" class="available refresh">Check Now</a>&nbsp;<img class="monitor-loading" src="<?php echo defined("URL_THIRD_THEMES") ? URL_THIRD_THEMES : '/themes'; ?>/lamplighter/images/loader.gif" alt="Loading..." /></p>
	<p class="logos">
		<a href="<?php echo $cp->masked_url('https://lamplighter.io/'); ?>" target="_blank" class="first">Lamplighter.io</a>
	</p>
</div>

</div> <!-- #lamplighter -->
