<?php
// إعدادات تصحيح الأخطاء (هام جداً لمعرفة السبب)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$dbFile = 'db.json';
$uploadDir = 'uploads/';

// 1. التأكد من وجود المجلد والملف
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// إذا الملف غير موجود أو فارغ، نضع مصفوفة فارغة
if (!file_exists($dbFile) || filesize($dbFile) == 0) {
    file_put_contents($dbFile, json_encode([]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // الحصول على رابط الموقع الحالي بشكل صحيح (للصور وللمعاينة)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    // في Replit أحياناً يكون المنفذ مهماً، لكن الرابط الافتراضي يعمل عادة
    $host = $_SERVER['HTTP_HOST']; 
    $basePath = $protocol . "://" . $host . dirname($_SERVER['PHP_SELF']);
    // تنظيف الرابط من الشرطة المائلة الزائدة
    $basePath = rtrim($basePath, '/');

    // معالجة الصورة
    $finalImage = '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        if(move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)){
            $finalImage = $basePath . '/' . $uploadDir . $filename;
        }
    } else {
        $finalImage = $_POST['image_url'] ?? '';
    }

    $id = uniqid();
    
    // رابط صفحة view.php
    $viewLink = $basePath . "/view.php?id=" . $id;

    // اختصار الرابط
    $shortService = "https://da.gd/s?url=" . urlencode($viewLink);
    // نستخدم curl بدلاً من file_get_contents لأنه أكثر موثوقية في الاستضافات
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $shortService);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $shortened = curl_exec($ch);
    curl_close($ch);

    if ($shortened && strpos($shortened, 'http') !== false) {
        $shortened = trim($shortened);
        $cleanShort = str_replace(['https://', 'http://'], '', $shortened);
        $fakeDomain = trim($_POST['fake_domain']);
        // حذف المسافات وأي حروف غير مرغوبة
        $fakeDomain = preg_replace('/[^a-zA-Z0-9\-\.]/', '', $fakeDomain);
        
        $finalLink = "https://$fakeDomain@$cleanShort";

        // تجهيز البيانات للحفظ
        $newData = [
            'id' => $id,
            'original_url' => $_POST['original_url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'image' => $finalImage,
            'short_link' => $finalLink, // نحفظ الرابط المختصر أيضاً للعرض في السجل
            'created_at' => date('Y-m-d H:i:s')
        ];

        // القراءة والحفظ في JSON
        $currentData = json_decode(file_get_contents($dbFile), true);
        if (!is_array($currentData)) {
            $currentData = []; // إعادة تعيين لو الملف فاسد
        }
        
        $currentData[$id] = $newData; // استخدام ID كمفتاح لسهولة البحث
        
        if(file_put_contents($dbFile, json_encode($currentData, JSON_PRETTY_PRINT))) {
            echo json_encode(['status' => 'success', 'short_link' => $finalLink]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'فشل الكتابة في ملف قاعدة البيانات']);
        }
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'فشل الاتصال بموقع الاختصار da.gd']);
    }
}
?>
