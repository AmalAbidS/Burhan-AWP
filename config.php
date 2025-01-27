<?php
    // الاتصال بقاعدة البيانات
    $servername = "localhost"; // اسم السيرفر
    $username = "root"; // اسم المستخدم لقاعدة البيانات
    $dbpassword = ""; // كلمة المرور لقاعدة البيانات
    $dbname = "burhansystem"; // اسم قاعدة البيانات

    $conn = new mysqli($servername, $username, $dbpassword, $dbname);

    // التحقق من الاتصال
    if ($conn->connect_error) {
        die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
?>