<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'My Backend User Management',
    'description' => 'A module that makes it possible for editors to maintain backend users.',
    'category' => 'module',
    'version' => '4.0.4',
    'state' => 'stable',
    'uploadFolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'author' => 'Benjamin Serfhos',
    'author_email' => 'benjamin@serfhos.com',
    'author_company' => 'Rotterdam School of Management, Erasmus University',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
            'beuser' => '9.5.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'KoninklijkeCollective\\MyUserManagement\\' => 'Classes',
        ],
    ],
];
