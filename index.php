<?php

require 'vendor/autoload.php';

use RDAlfaGroup\Test1\App;
use RDAlfaGroup\Test1\MysqlTokenStore;

$url = 'http://google.com?gclid=&placement=&adposition=&campid=&device=&devicemodel=&creative=&adid=&target=&keyword=&matchtype=';

$app = App::instance()->withTokenStore(new MysqlTokenStore(require 'configs/test1.php'));
$encrypted = $app->encrypt($url);
$decrypted = $app->decrypt($encrypted);

print_r([$encrypted, $decrypted]);
var_dump($url === $decrypted);