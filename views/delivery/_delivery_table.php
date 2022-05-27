<?php

use app\models\City;
use app\models\Delivery;
use yii\helpers\HtmlPurifier;

$mapId = "map-" . $delivery['id'] . '-' . rand(1000, 9999);

$this->registerJs('
var latLng = ' . json_encode([$delivery['lat'],  $delivery['lng'],]) . ';
var map = L.map("' . $mapId . '", {
    center: latLng,
    zoom: 14
});
var osmUrl = "http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
var osmLayer = new L.TileLayer(osmUrl, {
    maxZoom: 19
});
map.addLayer(osmLayer);

var marker = L.marker(latLng).addTo(map);
map.on("move", function() {
    marker.setLatLng(latLng);
});
map.on("dragend", function() {
    marker.setLatLng(latLng);
});
');
?>
<div class="table-responsive-sm">
    <table class="table table-bordered table-sm table-hover">
        <tbody>
            <tr>
                <td class="active"><?= getAttributeLabelOfDelivery('name') ?></td>
                <td colspan="3"><?= HtmlPurifier::process($delivery['name']) ?></td>
                <td colspan="4" rowspan="7" style="height: inherit;position: relative;" class="col-sm-6">
                    <div id="<?= $mapId ?>" style="position: absolute;top: 0;bottom: 0;right: 0;left: 0;"></div>
                </td>
            </tr>
            <tr>
                <td class="active"><?= getAttributeLabelOfDelivery('phone') ?></td>
                <td><?= HtmlPurifier::process($delivery['phone']) ?></td>
                <td class="active"><?= getAttributeLabelOfDelivery('mobile') ?></td>
                <td><?= HtmlPurifier::process($delivery['mobile']) ?></td>
            </tr>
            <tr>
                <td class="active"><?= getAttributeLabelOfDelivery('city') ?></td>
                <td><?= City::getLabel($delivery['city']) ?></td>
                <td class="active"><?= getAttributeLabelOfDelivery('postal_code') ?></td>
                <td><?= HtmlPurifier::process($delivery['postal_code']) ?></td>
            </tr>
            <tr>
                <td class="active"><?= getAttributeLabelOfDelivery('address') ?></td>
                <td colspan="3"><?= HtmlPurifier::process($delivery['address']) ?></td>
            </tr>
            <tr>
                <td class="active"><?= getAttributeLabelOfDelivery('des') ?></td>
                <td colspan="3"><?= HtmlPurifier::process($delivery['des']) ?></td>
            </tr>
        </tbody>
    </table>
</div>