<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منشئ الروابط الاحترافي</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* نفس التصميم الخاص بك مع تعديلات بسيطة */
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            margin: 0; padding: 20px;
            min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 30px; border-radius: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            width: 100%; max-width: 500px;
        }
        input, textarea, select {
            width: 95%; padding: 12px; margin: 8px 0;
            border: none; border-radius: 12px;
            font-size: 1rem; background-color: #eee; color: #333;
            font-family: 'Cairo', sans-serif;
        }
        button {
            width: 100%; padding: 12px; margin-top: 15px;
            background: linear-gradient(135deg, #00c9ff, #92fe9d);
            border: none; border-radius: 12px;
            font-size: 1.1rem; font-weight: bold; cursor: pointer;
        }
        .file-upload { background: rgba(255,255,255,0.2); color: #fff; }
        #result { margin-top: 20px; word-break: break-all; text-align: center; }
        a { color: #00ffcc; text-decoration: none; font-size: 1.2rem; }
        .group-title { margin-top: 10px; font-size: 0.9rem; color: #ccc; }
    </style>
</head>
<body>

    <h1>صانع الروابط السحري ✨</h1>
    <div class="container">
        <form id="linkForm" enctype="multipart/form-data">
            
            <div class="group-title">الرابط الأصلي (الوجهة):</div>
            <input type="url" name="original_url" placeholder="https://youtube.com/..." required>

            <div class="group-title">بيانات المعاينة (ما يظهر في فيسبوك):</div>
            <input type="text" name="title" placeholder="العنوان الكبير (Title)" required>
            <input type="text" name="description" placeholder="الوصف المختصر (Description)">
            
            <div class="group-title">صورة المعاينة:</div>
            <select id="imgType" onchange="toggleImageInput()">
                <option value="link">رابط صورة مباشر</option>
                <option value="upload">رفع صورة من الجهاز</option>
            </select>
            <input type="url" name="image_url" id="imgUrlInput" placeholder="رابط الصورة (.jpg/.png)">
            <input type="file" name="image_file" id="imgFileInput" class="file-upload" style="display:none;" accept="image/*">

            <div class="group-title">أيقونة الموقع (Favicon):</div>
            <input type="url" name="icon_url" placeholder="رابط الأيقونة (اختياري)">

            <div class="group-title">الدومين المزيف (للخدعة):</div>
            <input type="text" name="fake_domain" placeholder="مثال: google.com" required>

            <button type="submit" id="submitBtn">🚀 إنشاء الرابط المختصر</button>
        </form>

        <div id="result"></div>
    </div>

    <script>
        function toggleImageInput() {
            const type = document.getElementById('imgType').value;
            if(type === 'upload') {
                document.getElementById('imgUrlInput').style.display = 'none';
                document.getElementById('imgFileInput').style.display = 'block';
            } else {
                document.getElementById('imgUrlInput').style.display = 'block';
                document.getElementById('imgFileInput').style.display = 'none';
            }
        }

        document.getElementById('linkForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const res = document.getElementById('result');
            
            btn.innerHTML = "جاري المعالجة...";
            btn.disabled = true;
            res.innerHTML = "";

            const formData = new FormData(this);

            try {
                const response = await fetch('save.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if(data.status === 'success') {
                    res.innerHTML = `✅ تم بنجاح!<br><br><a href="${data.short_link}" target="_blank">${data.short_link}</a>`;
                } else {
                    res.innerHTML = "❌ خطأ: " + data.message;
                }
            } catch (error) {
                res.innerHTML = "❌ حدث خطأ في الاتصال";
            }
            
            btn.innerHTML = "🚀 إنشاء الرابط المختصر";
            btn.disabled = false;
        });
    </script>
</body>
</html>