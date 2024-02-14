<span id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <h2>Consumo del mes de <?= $this->data['result']['month'] ?></h2>
  <div class="bars">
    <div class="limit limit-start">

    </div>
    <div class="barContainer">
    <div class="barConsumed barConsumed<?= ($this->data['result']['limited_exceded'])?'Negative':'Positive' ?><? if($this->data['result']['limited_exceded']) echo ' limited_exceded'; ?>" style="width:<?= $this->data['result']['consumed_percent'] ?>%;">
      <div class="barValue">
        <?= $this->data['result']['consumed_percent_print'] ?>
      </div>
    </div>
    </div>
    <div class="limit limit-end">

    </div>
  </div>
  <span class="axis">
    <span class="icon icon<?= ($this->data['result']['limited_exceded'])?'Negative':'Positive' ?><? if($this->data['result']['limited_exceded']) echo ' limited_exceded'; ?>">

    </span>
    <span class="description">
       <strong>$<?= $this->data['result']['consumed_amount'] ?></strong> consumido de <strong>$<?= $this->data['result']['budget_month'] ?></strong>
    </span>

    <span class="description">
       <strong>$<?= $this->data['result']['total_refund']  ?></strong> de las devoluciones
    </span>
  </span>
</span>
