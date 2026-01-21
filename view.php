<?php
$id = $_GET['id'] ?? '';
$dbFile = 'db.json';

$data = [];
if (file_exists($dbFile)) {
    $json = json_decode(file_get_contents($dbFile), true);
    if (isset($json[$id])) {
        $data = $json[$id];
    }
}

// إذا الرابط غير موجود، نوجهه للصفحة الرئيسية
if (empty($data)) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <meta property="og:title" content="<?php echo htmlspecialchars($data['title']); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($data['description']); ?>" />
    <meta property="og:image" content="<?php echo $data['image']; ?>" />
    <meta property="og:url" content="<?php echo $data['original_url']; ?>" />
    <meta property="og:type" content="website" />
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($data['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($data['description']); ?>">
    <meta name="twitter:image" content="<?php echo $data['image']; ?>">

    <?php if(!empty($data['icon'])): ?>
    <link rel="icon" href="<?php echo $data['icon']; ?>">
    <?php endif; ?>

    <title><?php echo htmlspecialchars($data['title']); ?></title>

    <style>
        body { background-color: #0f2027; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; }
    </style>

    <script>
        // توجيه الزائر فوراً للرابط الأصلي
        setTimeout(function() {
            window.location.href = "<?php echo $data['original_url']; ?>";
        }, 100); // تأخير بسيط جداً
    </script>
    
    <meta http-equiv="refresh" content="0;url=<?php echo $data['original_url']; ?>">

</head>
<body>
    <p>Loading...</p>
</body>
</html>