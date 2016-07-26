<style>
.nostyles {
  width:100%;
  margin:0 !important;
  border:none;
  table-layout:fixed;
}

.nostyles td {
  padding:0;
  border-left:none !important;
  border-bottom:none !important;
  background: none !important;
}

table.nostyles td:last-child { border-right:none !important; }
table.nostyles tbody tr td {vertical-align:top !important;}
.nostyles div {position:relative;}
.nostyles div label {font-weight:normal;margin-bottom:.5em;display:block;}
.nostyles label input[type=radio] {margin-right:5px;position:relative;top:1px;}
.nostyles .help { margin: 15px 0 0; font-style: italic; }

.apply-box {
  border:1px solid #eee;
  padding:10px;
  margin-bottom:20px;
}
.apply-box input[type=text] {display:inline;width:10em;margin:0 5px;}
.apply-box input[type=checkbox] {margin-right:5px;}
</style>
<script>
$(function() {});
</script>

<?php if($prefs['mage'] == "") : ?>

    <p style="margin-bottom:1.5em"><?=lang('undefined')?></p>
    <?=  form_open('C=addons_extensions&M=extension_settings&file=mage_integratee', array(), array('file' => 'mage_integratee')) ?>
    <table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr><th style="width:20%;" class="header">Preference</th><th>Setting</th></tr>
        </thead>
        <tbody>
            <tr class="odd">
                <td class="tableCellOne"><b><?=lang('label_mage')?></b></td>
                <td class="tableCellOne nostyles">
                    <div style="margin-bottom:1em">
                        <input dir="ltr" style="width:100%;" type="text" name="mage" id="mage" value="" size="" maxlength="" class="" tabindex="1" /> 
                        <p class="help"><?=lang('help_mage')?></p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <input type="submit" value="Save settings" class="submit" />
    <?= form_close(); ?>

<?php else : ?>

    <?=  form_open('C=addons_extensions&M=extension_settings&file=mage_integratee', array(), array("exclude[]" => "","blocks[head]" => "0","blocks[after_body_start]" => "0","blocks[global_notices]" => "0","blocks[header]" => "0","blocks[global_messages]" => "0","blocks[left]" => "0","blocks[right]" => "0","blocks[footer]" => "0","blocks[before_body_end]" => "0")) ?>
    <table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tr><th style="width:20%;" class="header">Preference</th><th>Setting</th></tr>
        </thead>
        <tbody>

            <?php /* Magento Path */ ?>
            <tr class="">
                <td class="tableCellOne"><b><?=lang('label_mage')?></b></td>
                <td class="tableCellOne nostyles">
                    <div style="margin-bottom:1em">
                        <input dir="ltr" style="width:100%;" type="text" name="mage" id="mage" value="<?=$prefs['mage']?>" size="" maxlength="" class="" tabindex="1" /> 
                        <p class="help"><?=lang('help_mage')?></p>
                    </div>
                </td>
            </tr>

            <?php /* Run Code */ ?>
            <tr class="">
                <td class="tableCellOne"><b><?=lang('label_store')?></b></td>
                <td class="tableCellOne">
                    <table class="nostyles">
                        <tr>
                            <td style="width:60%;border-right:1px dotted #D0D7DF;">
                                <div style="margin-bottom:1em">
                                    <label><?=lang('help_run_code')?></label>
                                    <input dir="ltr" style="width:100%;" type="text" name="run_code" id="run_code" value="<?=$prefs['run_code']?>" size="" maxlength="" class="" /> 
                                </div>
                            </td>
                            <td style="width:40%;">
                                <div style="margin-bottom:1em">
                                    <label><?=lang('help_run_type')?></label>
                                    <select name="run_type" class="select">
                                        <option value="website" <?php if($prefs['run_type'] == "website") echo "selected"; ?>><?=lang('run_type_website')?></option>
                                        <option value="store" <?php if($prefs['run_type'] == "store") echo "selected"; ?>><?=lang('run_type_store')?></option>
                                    </select> 
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <?php /* Exclude Templates */ ?>
            <tr class="">
                <td class="tableCellOne"><b><?=lang('label_exclude')?></b></td>
                <td class="tableCellOne nostyles">
                    <div style="margin-bottom:1em">
                        <select name="exclude[]" class="select" size="8" multiple style="min-width: 250px;">
                            <?php foreach ($templates as $key => $group) : ?>       
                                <optgroup label="<?=$key?>">
                                    <?php foreach ($group as $template): ?>
                                        <option value="<?=$template['id']?>" <?php if(in_array($template['id'], $prefs['exclude'])) : ?> selected="selected"<?php endif; ?>><?=$template['name']?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select> 
                        <p class="help"><?=lang('help_exclude')?></p>
                    </div>
                </td>
            </tr>

            <?php /* Toggle Blocks */ ?>
            <tr class="">
                <td class="tableCellOne"><b><?=lang('label_blocks')?></b></td>
                <td class="tableCellOne nostyles">
                    <div style="margin-bottom:1em">
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[head]" 
                                value="1" 
                                <?php if($prefs['blocks']['head'] == 1) : ?> checked="checked"<?php endif; ?> 
                            /> 
                            <code>head</code>
                        </label>
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[after_body_start]" 
                                value="1" 
                                <?php if($prefs['blocks']['after_body_start'] == 1) : ?> checked="checked"<?php endif; ?> 
                            /> 
                            <code>after_body_start</code>
                        </label>
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[global_notices]" 
                                value="1" 
                                <?php if($prefs['blocks']['global_notices'] == 1) : ?> checked="checked"<?php endif; ?>
                            /> 
                            <code>global_notices</code>
                        </label>
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[header]" 
                                value="1" 
                                <?php if($prefs['blocks']['header'] == 1) : ?> checked="checked"<?php endif; ?> 
                            /> 
                            <code>header</code>
                        </label>
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[global_messages]" 
                                value="1" 
                                <?php if($prefs['blocks']['global_messages'] == 1) : ?> checked="checked"<?php endif; ?> 
                            /> 
                            <code>global_messages</code>
                        </label>
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[left]" 
                                value="1" 
                                <?php if($prefs['blocks']['left'] == 1) : ?> checked="checked"<?php endif; ?> 
                            /> 
                            <code>left</code>
                        </label>
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[right]" 
                                value="1" 
                                <?php if($prefs['blocks']['right'] == 1) : ?> checked="checked"<?php endif; ?> 
                            /> 
                            <code>right</code>
                        </label>
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[footer]" 
                                value="1" 
                                <?php if($prefs['blocks']['footer'] == 1) : ?> checked="checked"<?php endif; ?> 
                            /> 
                            <code>footer</code>
                        </label>
                        <label class="check">
                            <input 
                                type="checkbox" 
                                name="blocks[before_body_end]" 
                                value="1" 
                                <?php if($prefs['blocks']['before_body_end'] == 1) : ?> checked="checked"<?php endif; ?> 
                            /> 
                            <code>before_body_end</code>
                        </label>
                        <p class="help"><?=lang('help_blocks')?></p>
                    </div>
                </td>
            </tr>

            <?php /* Custom Blocks */ ?>
            <tr class="">
                <td class="tableCellOne"><b><?=lang('label_custom')?></b></td>
                <td class="tableCellOne nostyles">
                    <div style="margin-bottom:1em">
                        <input dir="ltr" style="width:100%;" type="text" name="custom" id="custom" value="<?=$prefs['custom']?>" size="" maxlength="" class="" tabindex="1" /> 
                        <p class="help"><?=lang('help_custom')?></p>
                    </div>
                </td>
            </tr>

        </tbody>
    </table>
    <input type="submit" value="Save settings" class="submit" />
    <?=  form_close(); ?>

<?php endif; ?>