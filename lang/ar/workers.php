<?php

// Option labels for worker attributes. Keys must stay unique: a duplicate key
// silently overrides the earlier one.
return [
    'experience' => [
        'none' => 'بدون خبرة',
        '1-3' => 'من 1 إلى 3 سنوات',
        '3-5' => 'من 3 إلى 5 سنوات',
        '5+' => 'أكثر من 5 سنوات',
    ],

    'religion' => [
        'muslim' => 'مسلمة',
        'christian' => 'مسيحية',
        'other' => 'أخرى',
    ],

    'gender' => [
        'female' => 'أنثى',
        'male' => 'ذكر',
    ],

    'status' => [
        'available' => 'متاحة',
        'reserved' => 'محجوزة',
        'assigned' => 'تم التعيين',
        'in_housing' => 'في السكن',
        'for_rent' => 'للإيجار',
        'returned' => 'معادة',
    ],
];
