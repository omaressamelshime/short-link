<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $dbFile = 'db.json';

    if (file_exists($dbFile)) {
        $data = json_decode(file_get_contents($dbFile), true);

        if (isset($data[$id])) {
            // 1. التحقق وحذف الصورة من السيرفر
            $item = $data[$id];
            // نتحقق إذا كان المسار محفوظاً وموجوداً بالفعل
            if (!empty($item['local_image_path']) && file_exists($item['local_image_path'])) {
                unlink($item['local_image_path']); // دالة الحذف في PHP
            }

            // 2. حذف البيانات من المصفوفة
            unset($data[$id]);

            // 3. حفظ ملف JSON الجديد
            file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT));

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'الرابط غير موجود']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'قاعدة البيانات غير موجودة']);
    }
}
?>
