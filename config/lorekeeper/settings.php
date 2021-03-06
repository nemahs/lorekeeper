<?php

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
|
| These are settings that affect how the site works.
| These are not expected to be changed often or on short schedule and are 
| therefore separate from the settings modifiable in the admin panel.
| It's highly recommended that you do any required modifications to this file
| as well as config/app.php before you start using the site.
|
*/

return [
    
    /*
    |--------------------------------------------------------------------------
    | Character Codes
    |--------------------------------------------------------------------------
    |
    | character_codes:
    |       This is used in the automatic generation of character codes.
    |       {category}: This is replaced by the character category code.
    |       {number}: This is replaced by the character number.
    |
    |       e.g. Under the default setting ({category}-{number}), 
    |       a character in a category called "MYO" (code "MYO") with number 001
    |       will have the character code of MYO-001.
    |
    |       !IMPORTANT!
    |       As this is used to generate the character's URL, sticking to 
    |       alphanumeric, hyphen (-) and underscore (_) characters
    |       is advised.
    |
    | character_number_digits: 
    |       This specifies the default number of digits for {number} when
    |       pulled automatically. 
    |
    |       e.g. If the next number is 2, setting this to 3 would give 002.
    |
    | character_pull_number: 
    |       This determines if the next {number} is pulled from the highest
    |       existing number, or the highest number in the category.
    |       This value can be "all" (default) or "category".
    |       
    |       e.g. if the following characters exist:
    |       Standard (STD) category: STD-001, STD-002, STD-003
    |       MYO (MYO) category:      MYO-001, MYO-002 
    |       If character_pull_number is 'all': 
    |           The next number pulled will be 004 regardless of category.
    |       If character_pull_number is 'category':
    |           The next number pulled for STD will be 004.
    |           The next number pulled for MYO will be 003. 
    |
    */
    'character_codes' => '{category}-{number}',
    'character_number_digits' => 3,
    'character_pull_number' => 'all',

    /*
    |--------------------------------------------------------------------------
    | Masterlist Thumbnail Dimensions
    |--------------------------------------------------------------------------
    |
    | This affects the dimensions used by the character thumbnail cropper.
    | Using a smallish size is recommended to reduce the amount of time
    | needed to load the masterlist pages.
    |
    */
    'masterlist_thumbnails' => [
        'width' => 200,
        'height' => 200
    ],
];
