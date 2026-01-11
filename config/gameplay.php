<?php

return [
    'paylines' => [
        [1, 1, 1, 1, 1],
        [2, 2, 1, 2, 2],
        [3, 2, 1, 2, 3],
        [2, 1, 2, 3, 2],
        [2, 2, 2, 2, 2],
        [2, 2, 3, 2, 2],
        [3, 3, 2, 1, 1],
        [1, 2, 2, 2, 1],
        [3, 3, 3, 3, 3],
        [1, 1, 3, 1, 1],
        [1, 1, 2, 3, 3],
        [3, 2, 2, 2, 3],
        [2, 1, 1, 1, 2],
        [3, 3, 1, 3, 3],
        [2, 3, 2, 1, 2],
        [1, 2, 1, 2, 1],
        [2, 3, 3, 3, 2],
        [1, 2, 3, 2, 1],
        [1, 3, 3, 3, 1],
        [3, 2, 3, 2, 3]
    ],


    'reels' => [
        [
            ['name' => 'w2', 'type' => 'wild'],
            ['name' => 's7', 'type' => 'normal'],
            ['name' => 's3', 'type' => 'normal'],
            ['name' => 's10', 'type' => 'normal'],
            ['name' => 'w1', 'type' => 'wild'],
            ['name' => 's1', 'type' => 'normal'],
            ['name' => 's9', 'type' => 'normal'],
            ['name' => 's4', 'type' => 'normal'],
            ['name' => 's11', 'type' => 'normal'],
            ['name' => 's6', 'type' => 'normal'],
            ['name' => 's8', 'type' => 'normal'],
            ['name' => 's2', 'type' => 'normal'],
            ['name' => 's5', 'type' => 'normal']
        ],
        [
            ['name' => 's5', 'type' => 'normal'],
            ['name' => 's11', 'type' => 'normal'],
            ['name' => 'w1', 'type' => 'wild'],
            ['name' => 's8', 'type' => 'normal'],
            ['name' => 's2', 'type' => 'normal'],
            ['name' => 's9', 'type' => 'normal'],
            ['name' => 's3', 'type' => 'normal'],
            ['name' => 'w2', 'type' => 'wild'],
            ['name' => 's7', 'type' => 'normal'],
            ['name' => 's10', 'type' => 'normal'],
            ['name' => 's4', 'type' => 'normal'],
            ['name' => 's1', 'type' => 'normal'],
            ['name' => 's6', 'type' => 'normal']
        ],
        [
            ['name' => 's9', 'type' => 'normal'],
            ['name' => 's4', 'type' => 'normal'],
            ['name' => 's1', 'type' => 'normal'],
            ['name' => 's6', 'type' => 'normal'],
            ['name' => 's3', 'type' => 'normal'],
            ['name' => 'w2', 'type' => 'wild'],
            ['name' => 's11', 'type' => 'normal'],
            ['name' => 's5', 'type' => 'normal'],
            ['name' => 'w1', 'type' => 'wild'],
            ['name' => 's10', 'type' => 'normal'],
            ['name' => 's2', 'type' => 'normal'],
            ['name' => 's7', 'type' => 'normal'],
            ['name' => 's8', 'type' => 'normal']
        ],
        [
            ['name' => 's10', 'type' => 'normal'],
            ['name' => 's2', 'type' => 'normal'],
            ['name' => 's7', 'type' => 'normal'],
            ['name' => 'w1', 'type' => 'wild'],
            ['name' => 's5', 'type' => 'normal'],
            ['name' => 's6', 'type' => 'normal'],
            ['name' => 's1', 'type' => 'normal'],
            ['name' => 's8', 'type' => 'normal'],
            ['name' => 's3', 'type' => 'normal'],
            ['name' => 'w2', 'type' => 'wild'],
            ['name' => 's9', 'type' => 'normal'],
            ['name' => 's11', 'type' => 'normal'],
            ['name' => 's4', 'type' => 'normal']
        ],
        [
            ['name' => 's6', 'type' => 'normal'],
            ['name' => 's3', 'type' => 'normal'],
            ['name' => 'w2', 'type' => 'wild'],
            ['name' => 's9', 'type' => 'normal'],
            ['name' => 's11', 'type' => 'normal'],
            ['name' => 's4', 'type' => 'normal'],
            ['name' => 's7', 'type' => 'normal'],
            ['name' => 's1', 'type' => 'normal'],
            ['name' => 's2', 'type' => 'normal'],
            ['name' => 's10', 'type' => 'normal'],
            ['name' => 'w1', 'type' => 'wild'],
            ['name' => 's8', 'type' => 'normal'],
            ['name' => 's5', 'type' => 'normal']
        ]
    ],

    'paytable' => [
        's1' => [3 => 2.5, 4 => 7.5, 5 => 37.5],
        's2' => [3 => 1.75, 4 => 5, 5 => 25],
        's3' => [3 => 1.25, 4 => 3, 5 => 15],
        's4' => [3 => 1, 4 => 2, 5 => 10],
        's5' => [3 => 0.6, 4 => 1.25, 5 => 7.5],
        's6' => [3 => 0.4, 4 => 1, 5 => 5],
        's7' => [3 => 0.25, 4 => 0.5, 5 => 2.5],
        's8' => [3 => 0.25, 4 => 0.5, 5 => 2.5],
        's9' => [3 => 0.1, 4 => 0.25, 5 => 1.25],
        's10' => [3 => 0.1, 4 => 0.25, 5 => 1.25],
        's11' => [3 => 0.1, 4 => 0.25, 5 => 1.25],
    ],

    'wild_paytable' => [
        'w1' => 2,
        'w2' => 3,
    ],
];