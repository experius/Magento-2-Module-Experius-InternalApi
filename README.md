**Magento Internal Api (ALPHA)**

A Helper class to simplify the usage of the Magento 2 Api within Magento code. Don't know the purpose yet :-).


```php

$output = $this->internalRestApi->call('/V1/guest-carts/7ec5be0e5bb3ad335fec459dc8ea57f1/items', 'GET', []);

```

```php

$payLoad = [
    'cartItem' => [
        'sku' => '24-MB02',
        'qty' => 1,
        'quote_id' => '7ec5be0e5bb3ad335fec459dc8ea57f1',
        'product_type' => 'simple'
    ]
];

$output = $this->internalRestApi->call('/V1/guest-carts/7ec5be0e5bb3ad335fec459dc8ea57f1/items', 'POST', $payLoad);

```

!WARNING: Performance not tested yet.