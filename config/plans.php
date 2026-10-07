<?php

return [
    // Note: `custom_templates` est laissé à `true` sur tous les plans pendant la phase de test.
    // Passer `free` à `false` lors de la mise en production du créateur de modèles (feature premium).
    'free' => ['cards' => 20, 'exports' => 50, 'bulk' => false, 'premium_templates' => false, 'custom_templates' => true, 'price' => ['XOF' => 0, 'EUR' => 0, 'USD' => 0]],
    'pro' => ['cards' => 500, 'exports' => 1000, 'bulk' => false, 'premium_templates' => true, 'custom_templates' => true, 'price' => ['XOF' => 5000, 'EUR' => 8, 'USD' => 9]],
    'business' => ['cards' => null, 'exports' => null, 'bulk' => true, 'premium_templates' => true, 'custom_templates' => true, 'price' => ['XOF' => 15000, 'EUR' => 25, 'USD' => 29]],
];
