<?php
// إظهار الأخطاء للتأكد من عمل الكود
ini_set('display_errors', 0); // جعلناها 0 كي لا تخرب استجابة JSON
header('Content-Type: application/json');

$dbFile = 'db.json';
$uploadDir = 'uploads/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
if (!file_exists($dbFile)) file_put_contents($dbFile, json_encode([]));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // إعداد الروابط
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST']; 
    $basePath = $protocol . "://" . $host . dirname($_SERVER['PHP_SELF']);
    $basePath = rtrim($basePath, '/');

    // رفع الصورة
    $finalImage = '';
    $isLocalImage = false; // علامة لمعرفة هل الصورة محلية أم رابط خارجي

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        if(move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)){
            $finalImage = $basePath . '/' . $uploadDir . $filename;
            $isLocalImage = true;
        }
    } else {
        $finalImage = $_POST['image_url'] ?? '';
    }

    $id = uniqid();
    $viewLink = $basePath . "/view.php?id=" . $id;

    // خدمة الاختصار
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://da.gd/s?url=" . urlencode($viewLink));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $shortened = curl_exec($ch);
    curl_close($ch);

    if ($shortened && strpos($shortened, 'http') !== false) {
        $shortened = trim($shortened);
        $cleanShort = str_replace(['https://', 'http://'], '', $shortened);
        $fakeDomain = preg_replace('/[^a-zA-Z0-9\-\.]/', '', $_POST['fake_domain']);
        $finalLink = "https://$fakeDomain@$cleanShort";

        $newData = [
            'id' => $id,
            'original_url' => $_POST['original_url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'image' => $finalImage,
            'icon' => $_POST['icon_url'] ?? '', // هنا حفظنا الأيقونة
            'is_local_image' => $isLocalImage, // نحفظ هذه المعلومة لنعرف نحذفها لاحقاً
            'local_image_path' => $isLocalImage ? $uploadDir . $filename : '', // مسار الحذف
            'short_link' => $finalLink,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $currentData = json_decode(file_get_contents($dbFile), true);
        if (!is_array($currentData)) $currentData = [];
        
        $currentData[$id] = $newData;
        
        file_put_contents($dbFile, json_encode($currentData, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success', 'short_link' => $finalLink]);
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'فشل الاختصار']);
    }
}
?>
