<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منشئ الروابط المتطور</title>
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
        .btn-delete {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: white; padding: 5px 10px; font-size: 0.9rem; width: auto; margin: 0;
        }
        #result { margin-top: 20px; word-break: break-all; text-align: center; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 10px; display: none; }
        a { color: #00ffcc; text-decoration: none; }
        
        .history-container { width: 100%; max-width: 800px; margin-top: 20px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: rgba(0,0,0,0.3); border-radius: 10px; min-width: 600px; }
        th, td { padding: 10px; text-align: right; border-bottom: 1px solid rgba(255,255,255,0.1); vertical-align: middle; }
        th { background: rgba(0,0,0,0.5); color: #00c9ff; }
        .thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>

    <h1>صانع الروابط (مع الأيقونة والحذف) 🗑️</h1>
    
    <div class="container">
        <form id="linkForm" enctype="multipart/form-data">
            <input type="url" name="original_url" placeholder="الرابط الأصلي (https://...)" required>
            <input type="text" name="title" placeholder="عنوان المعاينة (Title)" required>
            <input type="text" name="description" placeholder="وصف المعاينة (Description)">
            
            <label style="font-size: 0.9rem; color: #ccc;">صورة المعاينة (الكبيرة):</label>
            <select id="imgType" onchange="toggleImageInput()">
                <option value="link">رابط صورة جاهز</option>
                <option value="upload">رفع صورة من الجهاز</option>
            </select>
            <input type="url" name="image_url" id="imgUrlInput" placeholder="رابط الصورة">
            <input type="file" name="image_file" id="imgFileInput" style="display:none;" accept="image/*">
            
            <label style="font-size: 0.9rem; color: #ccc;">أيقونة الموقع (Logo الصغير):</label>
            <input type="url" name="icon_url" placeholder="رابط الأيقونة (مثال: https://example.com/logo.png)">

            <input type="text" name="fake_domain" placeholder="الدومين الوهمي (مثال: google.com)" required>

            <button type="submit" id="submitBtn">🚀 إنشاء وحفظ</button>
        </form>
        <div id="result"></div>
    </div>

    <div class="history-container">
        <h3>سجل الروابط:</h3>
        <table>
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>العنوان</th>
                    <th>الرابط المختصر</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody id="historyBody">
                <?php
                $dbFile = 'db.json';
                if (file_exists($dbFile)) {
                    $data = json_decode(file_get_contents($dbFile), true);
                    if ($data && is_array($data)) {
                        $data = array_reverse($data);
                        foreach ($data as $id => $item) { // نستخدم الـ ID كمفتاح للحذف
                            // تأمين في حالة تغيير هيكل البيانات
                            $currentId = isset($item['id']) ? $item['id'] : $id;
                            
                            echo "<tr id='row-$currentId'>";
                            echo "<td><img src='{$item['image']}' class='thumb'></td>";
                            echo "<td>" . htmlspecialchars($item['title']) . "</td>";
                            echo "<td><a href='{$item['short_link']}' target='_blank'>فتح</a></td>";
                            echo "<td><button class='btn-delete' onclick=\"deleteLink('$currentId')\">حذف</button></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center'>لا يوجد سجل</td></tr>";
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

        // دالة الحذف الجديدة
        async function deleteLink(id) {
            if(!confirm("هل أنت متأكد من حذف هذا الرابط؟ سيتم حذف الصورة أيضاً.")) return;

            try {
                const response = await fetch('delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                });
                const data = await response.json();
                
                if(data.status === 'success') {
                    // إزالة السطر من الجدول دون تحديث الصفحة
                    document.getElementById('row-' + id).remove();
                } else {
                    alert('خطأ: ' + data.message);
                }
            } catch (e) {
                alert('حدث خطأ في الاتصال');
            }
        }

        document.getElementById('linkForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const res = document.getElementById('result');
            
            btn.innerHTML = "جاري العمل...";
            btn.disabled = true;

            const formData = new FormData(this);

            try {
                const response = await fetch('save.php', { method: 'POST', body: formData });
                const data = await response.json();
                
                if(data.status === 'success') {
                    res.style.display = 'block';
                    res.innerHTML = `✅ تم!<br><a href="${data.short_link}" target="_blank">${data.short_link}</a>`;
                    setTimeout(() => location.reload(), 2000); // تحديث الصفحة لرؤية العنصر الجديد في الجدول
                } else {
                    res.style.display = 'block';
                    res.innerHTML = "❌ " + data.message;
                }
            } catch (error) {
                res.innerHTML = "❌ خطأ سيرفر";
            }
            
            btn.innerHTML = "🚀 إنشاء وحفظ";
            btn.disabled = false;
        });
    </script>
</body>
</html>
