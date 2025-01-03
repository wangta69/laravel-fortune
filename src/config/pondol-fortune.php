<?php
return [
  'route_fortune'=>[
    'prefix'=>'fortune',
    'as'=>'fortune.',
    'middleware'=>['web'],
  ],
  'route_fortune_admin'=>[
    'prefix'=>'fortune/admin',
    'as'=>'fortune.admin.',
    'middleware'=>['web', 'admin'],
  ],
  'component' => ['admin'=>['layout'=>'pondol-fortune::admin', 'lnb'=>'pondol-fortune::partials.admin-lnb']],
];
