<? if ($this->data['attrs']['read_only']): ?>
  <rn:widget path="custom/input/OutputField" />
<? else: ?>
    <? $disabled  = ($this->data['attrs']['disabled'])?' disabled="disabled"':''; ?>
    <? $maxlength = ($this->data['attrs']['maxlength'])?' maxlength="' . $this->data['attrs']['maxlength'] . '"':''; ?>
    <? $max       = ($this->data['attrs']['maxlength'])?' max="' . $this->data['attrs']['maxlength'] . '"':''; ?>
    <? $style    = (!$this->data['attrs']['visible'])?'display:none':''; ?>

    <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?> <?= ($this->data['attrs']['wide'])?'form-element-wide':'form-element' ?> inputField-<?= $this->data['attrs']['display_type'] ?>" style="<?= $style ?>">
      <? if($this->data['attrs']['display_type'] != "hidden"): ?>
        <div id="rn_<?= $this->instanceID ?>_LabelContainer">

            <label for="rn_<?= $this->instanceID ?>_<?= $this->data['attrs']['name'] ?>" id="rn_<?= $this->instanceID ?>_Label" class="rn_Label">
            <?= $this->data['attrs']['label_input'] ?>

            <?php if ($this->data['attrs']['label_input'] && $this->data['attrs']['required']): ?>
                <span class="rn_Required"> <?= \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) ?></span><span class="rn_ScreenReaderOnly"> <?= \RightNow\Utils\Config::getMessage(REQUIRED_LBL) ?></span>
            <? else: ?>
                <span class="rn_Required" hidden="hidden" style="display: none"> <?= \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) ?></span><span class="rn_ScreenReaderOnly"> <?= \RightNow\Utils\Config::getMessage(REQUIRED_LBL) ?></span>
            <?php endif; ?>

            <?php if ($this->data['attrs']['hint']): ?>
                <span class="rn_ScreenReaderOnly"><?= $this->data['attrs']['hint'] ?></span>
            <?php endif; ?>

            </label>

        </div>
      <? endif; ?>

        <div class="content">

        <?php if ($this->data['attrs']['display_type'] === 'textarea'): ?>

            <textarea id="rn_<?= $this->instanceID ?>_<?= $this->data['attrs']['name'] ?>" placeholder="<?=$this->data['attrs']['placeholder']?>" class="rn_TextArea" <?= $disabled ?> rows="7" cols="60" name="<?= $this->data['attrs']['name'] ?>" <?= $maxlength; ?>><?= $this->data['attrs']['value'] ?></textarea>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <rn:block id="preHint"/>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
                <rn:block id="postHint"/>
            <?php endif; ?>

        <? elseif($this->data['attrs']['display_type'] === 'checkbox'): ?>

            <?php $checked = ($this->data["attrs"]["value"] == 1)?'checked':'' ?>
            <input type="checkbox" value="1" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" <?= $disabled ?> <?if($this->data['attrs']['value'] !== null && $this->data['attrs']['value'] !== '') echo "value='{$this->data['attrs']['value']}'";?> <?= $checked ?> <?= $maxlength; ?>/>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <?php endif; ?>

        <? elseif($this->data['attrs']['display_type'] === 'boolean'): ?>

            <? if($this->data['attrs']['options']): ?>
            <?php $checked = ($this->data["attrs"]["value"] == true)?'checked':'' ?>
            <input type="radio" value="1" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" <?= $disabled ?> <?= $checked ?>/>
            <span><?if($this->data['attrs']['options'] !== null && $this->data['attrs']['options'] !== '') echo explode(',',$this->data['attrs']['options'])[0]; ?></span>

            <?php $checked = ($this->data["attrs"]["value"] == false)?'checked':'' ?>
            <input type="radio" value="0" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" <?= $disabled ?> <?= $checked ?>/>
            <span><?if($this->data['attrs']['options'] !== null && $this->data['attrs']['options'] !== '') echo explode(',',$this->data['attrs']['options'])[1]; ?></span>
            <?php endif; ?>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <?php endif; ?>
        <? elseif($this->data['attrs']['display_type'] === 'scoring'): ?>

            <? if($this->data['attrs']['options']): ?>
                <? foreach ($this->data['listMenu'] as $option): ?>

                    <input type="radio" value="<?= $option->id ?>" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" value="<?= $option ?>"/>
                    <span><?= $option->name ?></span>

                <? endforeach; ?>

            <?php endif; ?>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <?php endif; ?>

        <? elseif($this->data['attrs']['display_type'] === 'scoring'): ?>

            <? if($this->data['attrs']['options']): ?>
                <? foreach ($this->data['listMenu'] as $option): ?>

                    <input type="radio" value="<?= $option->id ?>" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" value="<?= $option ?>"/>
                    <span><?= $option->name ?></span>

                <? endforeach; ?>

            <?php endif; ?>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <?php endif; ?>

        <? elseif($this->data['attrs']['display_type'] === 'multiple'): ?>

            <? if($this->data['attrs']['options']): ?>
                <? foreach ($this->data['listMenu'] as $option): ?>

                    <input type="checkbox" value="<?= $option->id ?>" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" value="<?= $option ?>"/>
                    <span><?= $option->name ?></span>

                <? endforeach; ?>

            <?php endif; ?>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <?php endif; ?>

        <? elseif($this->data['attrs']['display_type'] === 'number'): ?>

            <input type="number" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" placeholder="<?=$this->data['attrs']['placeholder']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" <?= $disabled ?> value="<?= (isset($this->data['attrs']['value']))?$this->data['attrs']['value']:'' ?>"  <?= $max; ?>/>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <?php endif; ?>
        <? elseif($this->data['attrs']['display_type'] === 'date'): ?>

            <input type="date" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" placeholder="<?=$this->data['attrs']['placeholder']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" <?= $disabled ?> value="<?= (isset($this->data['attrs']['value']))?$this->data['attrs']['value']:'' ?>"  <?= $max; ?>/>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
              <?php endif; ?>

        <? elseif($this->data['attrs']['display_type'] === 'currency'): ?>

          <input type="number" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" placeholder="<?=$this->data['attrs']['placeholder']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" <?= $disabled ?> value="<?= (isset($this->data['attrs']['value']))?$this->data['attrs']['value']:'' ?>"  <?= $max; ?>/>

          <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
              <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
          <?php endif; ?>

        <? elseif($this->data['attrs']['display_type'] === 'hidden'): ?>

          <input type="hidden" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" placeholder="<?=$this->data['attrs']['placeholder']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" <?= $disabled ?> value="<?= (isset($this->data['attrs']['value']))?$this->data['attrs']['value']:'' ?>"  <?= $max; ?>/>

        <? else: ?>

            <input type="text" id="rn_<?=$this->instanceID?>_<?=$this->data['attrs']['name']?>" placeholder="<?=$this->data['attrs']['placeholder']?>" name="<?= $this->data['attrs']['name'] ?>" class="rn_<?=$this->data['attrs']['display_type']?>" <?= $disabled ?> <?if($this->data['attrs']['value'] !== null && $this->data['attrs']['value'] !== '') echo "value='{$this->data['attrs']['value']}'";?>  <?= $maxlength; ?>/>

            <?php if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
                <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($this->data['attrs']['show_count']): ?>
        <div class="rn_CountChar">
            <span class="counts">
                <span class="countLeft">0</span>
                <span> de </span>
                <span class="countMax"><?=$this->data['attrs']['maxlength'] ?></span>
            </span>
            <span class="message">caracteres utilizados.</span>
        </div>
      <?php endif; ?>

    </div>
    </div>
<?php endif; ?>
