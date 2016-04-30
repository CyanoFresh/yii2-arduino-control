<?php

/* @var $this yii\web\View */

use app\assets\ControlPanelAsset;

ControlPanelAsset::register($this);

$this->title = 'Control Panel';
?>
<div class="control-panel-index">
    <h1 class="page-header"><?= $this->title ?></h1>

    <div class="controls">
        <div class="checkbox">
            <label>
                <input type="checkbox" id="ledCheckbox" name="ledCheckbox"> LED
            </label>
        </div>
    </div>
</div>