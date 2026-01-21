<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منشئ الروابط + السجل</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff; margin: 0; padding: 20px;
            min-height: 100vh; display: flex; flex-direction: column; align-items: center;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 30px; border-radius: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px); width: 100%; max-width: 600px; margin-bottom: 30px;
        }
        input, textarea, select {
            width: 95%; padding: 12px; margin: 8px 0; border: none; border-radius: 12px;
            font-size: 1rem; background-color: #eee; color: #333; font-family: 'Cairo', sans-serif;
        }
        button {
            width: 100%; padding: 12px; margin-top: 15px;
            background: linear-gradient(135deg, #00c9ff, #92fe9d);
            border: none; border-radius: 12px; font-size: 1.1rem; font-weight: bold; cursor: pointer;
        }
        #result { margin-top: 20px; word-break: break-all; text-align: center; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 10px; display: none; }
        a { color: #00ffcc; text-decoration: none; }
        
        /* تصميم جدول السجل */
        .history-container { width: 100%; max-width: 800px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; background: rgba(0,0,0,0.3); border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px; text-align: right; border-bottom: 1px solid rgba(255,255,255,0.1); }
        th { background: rgba(0,0,0,0.5); color: #00c9ff; }
        .thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>

    <h1>صانع الروابط مع السجل 📂</h1>
    
    <div class="container">
        <form id="linkForm" enctype="multipart/form-data">
            <input type="url" name="original_url" placeholder="الرابط الأصلي (https://...)" required>
            <input type="text" name="title" placeholder="عنوان المعاينة" required>
            <input type="text" name="description" placeholder="وصف المعاينة">
            
            <select id="imgType" onchange="toggleImageInput()">
                <option value="link">رابط صورة جاهز</option>
                <option value="upload">رفع صورة من الجهاز</option>
            </select>
            <input type="url" name="image_url" id="imgUrlInput" placeholder="رابط الصورة">
            <input type="file" name="image_file" id="imgFileInput" style="display:none;" accept="image/*">
            
            <input type="text" name="fake_domain" placeholder="الدومين الوهمي (مثال: google.com)" required>

            <button type="submit" id="submitBtn">🚀 إنشاء وحفظ</button>
        </form>
        <div id="result"></div>
    </div>

    <div class="history-container">
        <h3>سجل الروابط المحفوظة:</h3>
        <table>
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>العنوان</th>
                    <th>الرابط المختصر</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dbFile = 'db.json';
                if (file_exists($dbFile)) {
                    $data = json_decode(file_get_contents($dbFile), true);
                    if ($data && is_array($data)) {
                        // عرض الأحدث أولاً
                        $data = array_reverse($data);
                        foreach ($data as $item) {
                            echo "<tr>";
                            echo "<td><img src='{$item['image']}' class='thumb'></td>";
                            echo "<td>" . htmlspecialchars($item['title']) . "</td>";
                            echo "<td><a href='{$item['short_link']}' target='_blank'>فتح الرابط</a></td>";
                            echo "<td>{$item['created_at']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center'>لا يوجد سجل حتى الآن</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleImageInput() {
            const type = document.getElementById('imgType').value;
            document.getElementById('imgUrlInput').style.display = (type === 'link') ? 'block' : 'none';
            document.getElementById('imgFileInput').style.display = (type === 'upload') ? 'block' : 'none';
        }

        document.getElementById('linkForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const res = document.getElementById('result');
            
            btn.innerHTML = "جاري الحفظ والاختصار...";
            btn.disabled = true;

            const formData = new FormData(this);

            try {
                const response = await fetch('save.php', { method: 'POST', body: formData });
                const text = await response.text(); // قراءة النص أولاً لتجنب أخطاء JSON
                console.log(text); // للمساعدة في اكتشاف الأخطاء
                
                try {
                    const data = JSON.parse(text);
                    if(data.status === 'success') {
                        res.style.display = 'block';
                        res.innerHTML = `✅ تم الحفظ!<br>رابطك: <a href="${data.short_link}" target="_blank">${data.short_link}</a><br><small>قم بتحديث الصفحة لرؤيته في السجل</small>`;
                    } else {
                        res.style.display = 'block';
                        res.innerHTML = "❌ خطأ: " + data.message;
                    }
                } catch (e) {
                    res.innerHTML = "❌ خطأ في السيرفر: " + text;
                }
            } catch (error) {
                res.innerHTML = "❌ تعذر الاتصال بالسيرفر";
            }
            
            btn.innerHTML = "🚀 إنشاء وحفظ";
            btn.disabled = false;
        });
    </script>
</body>
</html>
