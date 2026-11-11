<?php

declare(strict_types=1);

use Modules\Audit\Resolvers\LogIdResolver;
use Modules\Audit\Resolvers\RootIdResolver;
use Modules\Audit\Resolvers\RootTypeResolver;

return [
    'resolvers' => [
        'log_id' => LogIdResolver::class,
        'root_type' => RootTypeResolver::class,
        'root_id' => RootIdResolver::class,
    ],
];
