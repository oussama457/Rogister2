<?php
session_start();

// إذا كان المستخدم مسجلاً الدخول بالفعل، إعادة توجيهه إلى الصفحة الرئيسية (dashboard)
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// تضمين ملف الاتصال بقاعدة البيانات
require 'includes/db.php'; // تأكد من أن هذا الملف موجود في المسار الصحيح

$errors = array();

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // التحقق من الإدخال
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } elseif (strlen($password) < 8) { // Password length check
        $errors[] = "Password must be at least 8 characters.";
    } else {
        // التحقق من وجود اسم المستخدم أو البريد الإلكتروني مسبقاً
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Username or email already exists.";
        } else {
            // تشفير كلمة المرور
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // إضافة المستخدم إلى قاعدة البيانات
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                // إذا تم التسجيل بنجاح
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['username'] = $username;
                header('Location: dashboard.php');
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- تأكد من مسار ملف CSS -->
</head>
<body>

<h2>Register</h2>

<?php
// عرض الأخطاء إذا كانت موجودة
if (!empty($errors)) {
    echo '<div style="color: red;">';
    foreach ($errors as $error) {
        echo '<p>' . htmlspecialchars($error) . '</p>';  // Sanitize the output
    }
    echo '</div>';
}
?>

<form action="register.php" method="post">
    <label for="username">Username: </label>
    <input type="text" name="username" required><br>

    <label for="email">Email:</label>
    <input type="email" name="email" required><br>

    <label for="password">Password:</label>
    <input type="password" name="password" required><br>

    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" required><br>

    <button type="submit" name="register">Register</button>
</form>

<p>Already have an account? <a href="login.php">Login here</a></p>

<!-- إضافة تأثير النجوم المتحركة باستخدام JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const numberOfStars = 300; // عدد النجوم
    const body = document.querySelector("body");

    // وظيفة لإنشاء نجوم جديدة
    function createStars() {
        for (let i = 0; i < numberOfStars; i++) {
            let star = document.createElement("div");
            star.classList.add("star");  // إضافة فئة "star" لكل نجم
            // تحديد موقع وحجم كل نجم عشوائيًا
            star.style.width = `${Math.random() * 3 + 1}px`;  // حجم النجم بين 1px و 4px
            star.style.height = star.style.width;  // جعل العرض والارتفاع متساويين
            star.style.left = `${Math.random() * 100}%`;  // تحديد الموقع الأفقي
            star.style.top = `${Math.random() * 100}%`;  // تحديد الموقع الرأسي
            star.style.animationDuration = `${Math.random() * 5 + 2}s`;  // تحديد مدة الحركة
            body.appendChild(star);  // إضافة النجم إلى الصفحة
        }
    }

    // استدعاء الدالة لإنشاء النجوم
    createStars();
});
</script>

</body>
</html>

