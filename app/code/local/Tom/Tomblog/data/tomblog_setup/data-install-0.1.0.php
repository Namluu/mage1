<?php
$cate = array(
    array(
        'title' => 'Thể thao',
        'description' => 'Tin tức thể thao',
        'date' => date('Y-m-d H:i:s')
    ),
    array(
        'title' => 'Giải trí',
        'description' => 'Tin tức giải trí',
        'date' => date('Y-m-d H:i:s')
    ),
    array(
        'title' => 'Sức khỏe',
        'description' => 'Tin tức sức khỏe',
        'date' => date('Y-m-d H:i:s')
    ),
);

foreach ($cate as $data) {
    Mage::getModel('tomblog/category')
        ->addData($data)
        ->save();
}

$article = array(
    array(
        'title' => 'Tin 1',
        'content' => 'Tin tức 1',
        'date' => date('Y-m-d H:i:s'),
        'category_id' => 1
    ),
);