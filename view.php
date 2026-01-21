<?php
$id = $_GET['id'] ?? '';
$dbFile = 'db.json';

$data = null;
if (file_exists($dbFile)) {
    $json = json_decode(file_get_contents($dbFile), true);
    if (isset($json[$id])) {
        $data = $json[$id];
    }
}

// إذا الرابط غير موجود في قاعدة البيانات، حوله للرئيسية
if (!$data) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta property="og:title" content="<?php echo htmlspecialchars($data['title']); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($data['description']); ?>" />
    <meta property="og:image" content="<?php echo $data['image']; ?>" />
    <meta property="og:url" content="<?php echo $data['original_url']; ?>" />
    <meta property="og:type" content="website" />
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($data['title']); ?>">
    <meta name="twitter:image" content="<?php echo $data['image']; ?>">

    <title><?php echo htmlspecialchars($data['title']); ?></title>

    <style>
        body { background: #111; color: #fff; font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 2s linear infinite; margin-bottom: 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    
    <div class="loader"></div>
    <p>جاري توجيهك إلى الرابط...</p>

    <script>
        // التوجيه عبر جافاسكريبت لضمان قراءة البوتات للميتا تاج قبل التحويل
        setTimeout(function() {
            window.location.href = "<?php echo $data['original_url']; ?>";
        }, 500); // نصف ثانية انتظار
    </script>
    
    <noscript>
        <meta http-equiv="refresh" content="1;url=<?php echo $data['original_url']; ?>">
    </noscript>

</body>
</html>
