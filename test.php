<?php
require_once 'app/models/Category.php';
require_once 'app/models/Product.php';

$cat = new Category();
print_r($cat->getAll());

$prod = new Product();
print_r($prod->getAll());
