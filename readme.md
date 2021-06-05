# Lumen Zapier Package

## Send hook

`ZAPIER_HOOKS="hook-name:ocew56i ..."`

in code:

```php
    
    $zapHook = app(ZapierHook::class);

    // `hookName` from config `hook-name`
    $zapHook->hookName([/* querystring params */], [/* body params */]);
```

## Add global params for all hooks

config/zapier.php

```
'global-data' => [
    'querystring' => [
        /* querystring blobal params */
    ],
    'body' => [
        /* body blobal params */
    ]
]
```
