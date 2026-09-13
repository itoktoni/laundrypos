<?php
require 'vendor/autoload.php';
$a = [['id'=>1,'nama'=>'test "quote\'','telp'=>'081']];
echo "Js::from:\n";
echo Illuminate\Support\Js::from($a);
echo "\n\n@js equivalent (json_encode with flags):\n";
echo json_encode($a, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
echo "\n\nBlade @js would output Js::from\n";
