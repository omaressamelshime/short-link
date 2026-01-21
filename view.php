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

if (!$data) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    
    <?php if(!empty($data['icon'])): ?>
    <link rel="icon" href="<?php echo $data['icon']; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $data['icon']; ?>" type="image/x-icon">
    <?php endif; ?>

    <meta property="og:title" content="<?php echo htmlspecialchars($data['title']); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($data['description']); ?>" />
    <meta property="og:image" content="<?php echo $data['image']; ?>" />
    <meta property="og:url" content="<?php echo $data['original_url']; ?>" />
    <meta property="og:type" content="website" />
    
    <title><?php echo htmlspecialchars($data['title']); ?></title>

    <script>
        setTimeout(function() {
            window.location.href = "<?php echo $data['original_url']; ?>";
        }, 50);
    </script>
    <noscript>
        <meta http-equiv="refresh" content="1;url=<?php echo $data['original_url']; ?>">
    </noscript>
</head>
<body>
</body>
</html>
