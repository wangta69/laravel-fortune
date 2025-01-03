<?php
return [
  'route_saju'=>[
    'prefix'=>'fortune',
    'as'=>'fortune.',
    'middleware'=>['web'],
  ],
  'route_saju_admin'=>[
    'prefix'=>'fortune/admin',
    'as'=>'fortune.admin.',
    'middleware'=>['web', 'admin'],
  ],
  'component' => ['admin'=>['layout'=>'pondol-fortune::admin', 'lnb'=>'pondol-fortune::partials.admin-lnb']],
];
