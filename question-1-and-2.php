<?php

$data =
    [
        [
            'guest_id' => 177,
            'guest_type' => 'crew',
            'first_name' => 'Marco',
            'middle_name' => null,
            'last_name' => 'Burns',
            'gender' => 'M',
            'guest_booking' => [
                [
                    'booking_number' => 20008683,
                    'ship_code' => 'OST',
                    'room_no' => 'A0073',
                    'start_time' => 1438214400,
                    'end_time' => 1483142400,
                    'is_checked_in' => true,
                ],
            ],
            'guest_account' => [
                [
                    'account_id' => 20009503,
                    'status_id' => 2,
                    'account_limit' => 0,
                    'allow_charges' => true,
                ],
            ],
        ],
        [
            'guest_id' => 10000113,
            'guest_type' => 'crew',
            'first_name' => 'Bob Jr ',
            'middle_name' => 'Charles',
            'last_name' => 'Hemingway',
            'gender' => 'M',
            'guest_booking' => [
                [
                    'booking_number' => 10000013,
                    'room_no' => 'B0092',
                    'is_checked_in' => true,
                ],
            ],
            'guest_account' => [
                [
                    'account_id' => 10000522,
                    'account_limit' => 300,
                    'allow_charges' => true,
                ],
            ],
        ],
        [
            'guest_id' => 10000114,
            'guest_type' => 'crew',
            'first_name' => 'Al ',
            'middle_name' => 'Bert',
            'last_name' => 'Santiago',
            'gender' => 'M',
            'guest_booking' => [
                [
                    'booking_number' => 10000014,
                    'room_no' => 'A0018',
                    'is_checked_in' => true,
                ],
            ],
            'guest_account' => [
                [
                    'account_id' => 10000013,
                    'account_limit' => 300,
                    'allow_charges' => false,
                ],
            ],
        ],
        [
            'guest_id' => 10000115,
            'guest_type' => 'crew',
            'first_name' => 'Red ',
            'middle_name' => 'Ruby',
            'last_name' => 'Flowers ',
            'gender' => 'F',
            'guest_booking' => [
                [
                    'booking_number' => 10000015,
                    'room_no' => 'A0051',
                    'is_checked_in' => true,
                ],
            ],
            'guest_account' => [
                [
                    'account_id' => 10000519,
                    'account_limit' => 300,
                    'allow_charges' => true,
                ],
            ],
        ],
        [
            'guest_id' => 10000116,
            'guest_type' => 'crew',
            'first_name' => 'Ismael ',
            'middle_name' => 'Jean-Vital',
            'last_name' => 'Jammes',
            'gender' => 'M',
            'guest_booking' => [
                [
                    'booking_number' => 10000016,
                    'room_no' => 'A0023',
                    'is_checked_in' => true,
                ],
            ],
            'guest_account' => [
                [
                    'account_id' => 10000015,
                    'account_limit' => 300,
                    'allow_charges' => true,
                ],
            ],
        ],
    ];


// Question 1
function render($data, $level = 0)
{
    if (is_array($data) && !empty($data)) {
        foreach ($data as $key => $item) {
            if (is_array($item) && !empty($item)) {
                $level++;
                render($item, $level);
            } else {
                echo "<div>" . str_repeat("&nbsp;", $level*3) . "<i>{$key}:</i> {$item}" . "</div>";
            }
        }
    }
}

echo '<h3>Question 1</h3><br/>';
render($data);
echo '<br/><br/><br/>';

echo '<h3>Question 2</h3><br/>';
// Question 2
function sortData($data, &$resultArr, $sortBy = [], $rootItem = '', $rootKey = 0, $newKey = '', $level = 0)
{
    $level++;
    if (is_array($data) && !empty($data)) {
        foreach ($data as $key => $item) {
            if ($level === 1){
                $rootItem = $item;
                $rootKey = $key;
                $newKey = '';
            }
            if (is_array($item) && !empty($item)) {
                sortData($item, $resultArr, $sortBy, $rootItem, $rootKey, $newKey, $level);
            } else {
                if (in_array($key, $sortBy)){
                    $newKeyTmp = $newKey ? $newKey . $item : $item . "_";
                    $numKey = $newKey ? count(explode('_', $newKey)) : 1;

                    if (isset($resultArr[$newKeyTmp]) && $resultArr[$newKeyTmp] != $rootItem){
                        if ($numKey == count($sortBy)){
                            $newKeyTmp .= $rootKey;
                        }
                    }
                    $resultArr[$newKeyTmp] = $rootItem;
                    if ($newKey){
                        unset($resultArr[$newKey]);
                    }
                    $newKey = $newKeyTmp;
                }
            }
        }
        if ($level === 1){
            ksort($resultArr);
        }
    }
}
sortData($data, $result, ['first_name', 'account_id']);
render($result);
