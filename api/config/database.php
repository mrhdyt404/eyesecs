<?php
$pdo = new PDO(
  "mysql:host=localhost;dbname=site_chacker;charset=utf8",
  "root",
  "",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
