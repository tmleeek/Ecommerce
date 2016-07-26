<h2>Lamplighter Settings</h2>
<br />
<p>
	<a href="<?php echo $cp->masked_url('https://lamplighter.io/?utm_source=lamplighter_addon&utm_content=text_link&utm_campaign=expressionengine&utm_medium=addon'); ?>">Lamplighter</a> is a service that securely monitors your sites.  Once a site token is entered on this page, the site will be able to send add-on and site information to Lamplighter.
</p>
<?php if (!$curl_enabled) { ?>
	<p><a href="http://php.net/curl">cURL</a> must be enabled on your server for Lamplighter to work correctly.</p>
<?php } else if ($api_key) { ?>
	<p>The Lamplighter add-on has been successfully installed on this site.</p>
	<table class="mainTable">
		<thead>
			<tr>
				<th style="width: 30%;">Setting</th>
				<th style="width: 70%;">Value</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="api_key">Site Token</label>
					<p>This is your site token from Lamplighter.</p>
				</td>
				<td>
					<strong><?php echo $api_key; ?></strong>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<a href="<?php echo $url_remove_key; ?>">
						<input type="button" value="Remove site token" class="submit btn mb" />
					</a>
				</td>
			</tr>
		</tbody>
	</table>
<?php } else { ?>
	<p>
		If you're having trouble finding your site token, or if you are having trouble installing, please contact us at: <a href="mailto:support@lamplighter.io">support@lamplighter.io</a>.
	</p>
	<form id="default_form" method="POST" action="<?php echo $url_save_key; ?>">
	<input type="hidden" name="XID" value="<?php echo XID_SECURE_HASH ?>" />
	<table class="mainTable">
		<thead>
			<tr>
				<th style="width: 30%;">Setting</th>
				<th style="width: 70%;">Value</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="api_key">Site Token</label>
					<p>Please paste your site token from Lamplighter here.</p>
				</td>
				<td>
					<input name="api_key" id="api_key" type="text">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" value="Save" class="submit btn mb" />
				</td>
			</tr>
		</tbody>
	</table>
	</form>
<?php } ?>

