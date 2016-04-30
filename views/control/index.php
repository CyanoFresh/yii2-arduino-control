<?php
/* @var $this yii\web\View */

use app\assets\ControlAsset;

ControlAsset::register($this);

$this->title = 'Control';
?>
<div class="control-panel-index">
    <h1 class="page-header"><?= $this->title ?></h1>

    <div id="loader">
        <div class="loader-title text-center">
            Loading...
        </div>
    </div>

    <div id="content">
        <div class="row">
            <div class="col-sm-4">
                <div class="panel panel-default panel-led">
                    <div class="panel-body text-center">
                        <h1>LED</h1>
                    </div>

                    <div class="panel-footer">
                        <a class="btn btn-block btn-lg" data-type="led"><span class="ledStatus text-uppercase">off</span></a>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="panel panel-default panel-relay">
                    <div class="panel-body">
                        <h1>Relay</h1>

                    </div>

                    <div class="panel-footer">
                        <a class="btn btn-block" data-type="relay" data-state="off">Relay is <span class="relayStatus">off</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>