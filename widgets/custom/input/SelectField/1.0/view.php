<? if ($this->data['attrs']['read_only']): ?>
  <rn:widget path="custom/input/OutputField" />
<? else: ?>
<? $disabled = ($this->data['attrs']['disabled'])?' disabled="disabled"':''; ?>
<? $style    = (!$this->data['attrs']['visible'])?'display:none':''; ?>

<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?> <?= ($this->data['attrs']['wide'])?'form-element-wide':'form-element' ?> inputField-<?= $this->data['attrs']['display_type'] ?>" style="<?= $style ?>">
    <div id="rn_<?= $this->instanceID ?>_LabelContainer">
        <label for="rn_<?= $this->instanceID ?>_<?= $this->data['attrs']['name'] ?>" id="rn_<?= $this->instanceID ?>_Label" class="rn_Label">
        <?= $this->data['attrs']['label_input'] ?>

        <?php if ($this->data['attrs']['label_input'] && $this->data['attrs']['required']): ?>
            <span class="rn_Required"> <?= \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) ?></span><span class="rn_ScreenReaderOnly"> <?= \RightNow\Utils\Config::getMessage(REQUIRED_LBL) ?></span>
        <?php endif; ?>

        <?php if ($this->data['attrs']['hint']): ?>
            <span class="rn_ScreenReaderOnly"><?= $this->data['attrs']['hint'] ?></span>
        <?php endif; ?>

        </label>
    </div>

    <?php if(($this->data["attrs"]["filter"] || $this->data["attrs"]["read_only"]) && $this->data["attrs"]["colapsible"]): ?>
      <div <? if(empty($this->data["attrs"]["value"])) echo 'hidden="hidden" style="display:none;" ' ?>class="rn_SelectedValue rn_Value<? if($this->data["attrs"]["read_only"]) echo ' rn_ReadOnly' ?>">
        <? if($this->data["attrs"]["filter"] && !$this->data["attrs"]["read_only"]): ?>
        <a href="#">
        <? endif; ?>
          <?php foreach($this->data['listMenu'] as $list) :?>
            <?php if($this->data["attrs"]["value"] == $list->id): ?>
              <?= $list->name ?>
            <? endif; ?>
          <?php endforeach; ?>
        <? if($this->data["attrs"]["filter"] && !$this->data["attrs"]["read_only"]): ?>
        </a>
        <?php endif; ?>

      </div>
    <?php endif; ?>

    <?php if($this->data["attrs"]["filter"] && !$this->data["attrs"]["read_only"]): ?>
    <input <? if(!empty($this->data["attrs"]["value"]) && $this->data["attrs"]["colapsible"]) echo 'hidden="hidden" style="display:none;" ' ?>type="text" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>_filter" name="<?= $this->data['attrs']['name'] ?>_filter" placeholder="#rn:msg:CUSTOM_MSG_SEARCH#" class="rn_<?=$this->data['attrs']['display_type']?> rn_SelectFilter" />
    <?php endif; ?>

    <?php if(!$this->data["attrs"]["read_only"]): ?>
    <select <? if(!empty($this->data["attrs"]["value"]) && $this->data["attrs"]["filter"] && $this->data["attrs"]["colapsible"]) echo 'hidden="hidden" style="display:none;" ' ?>id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_selectInput" <?= $disabled ?> <? if($this->data["attrs"]["multiple"] === 'true') echo 'multiple="multiple"'; ?>>
      <?php foreach($this->data['listMenu'] as $list) :?>
        <?php $selected = ($this->data["attrs"]["value"] == $list->id)?'selected':'' ?>
        <option value="<?=$list->id?>" <?= $selected?> data-name="<?= $list->name ?>">
          <?=$list->name ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
        <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
    <?php endif; ?>

</div>
<? endif; ?>
