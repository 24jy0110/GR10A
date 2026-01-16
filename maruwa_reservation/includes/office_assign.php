<?php
require_once __DIR__ . '/area_master.php';

function assignOfficeByCity(string $city): string {
  global $AREA_MASTER;

  foreach ($AREA_MASTER as $pref) {
    foreach ($pref as $officeCode => $cities) {
      if (in_array($city, $cities, true)) {
        return $officeCode;
      }
    }
  }
  return 'OFCE000'; 
}
