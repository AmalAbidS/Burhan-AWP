<?php
// بدء الجلسة للتحقق من أن المستخدم هو المسؤول
session_start();

// التأكد من أن المستخدم هو المسؤول (roleID == 2)
if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: login.php");  // توجيه إلى صفحة تسجيل الدخول إذا لم يكن المسؤول
    exit();
}

// التحقق من وجود userID للمحقق الذي سيتم تعديله
if (isset($_GET['userID'])) {
    $userID = $_GET['userID'];

    // الاتصال بقاعدة البيانات
    include('config.php');

    // جلب بيانات المحقق بناءً على userID
    $sql = "SELECT * FROM user WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    // التأكد من وجود المحقق في قاعدة البيانات
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $message = "المحقق غير موجود.";
    }

    // التعامل مع تحديث البيانات إذا تم إرسال النموذج
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fName = $_POST['fName'];
        $mName = $_POST['mName'];
        $lName = $_POST['lName'];
        $email = $_POST['email'];
        $roleID = $_POST['roleID'];

        // التحقق من أن الحقول ليست فارغة
        if (empty($fName) || empty($mName) || empty($lName) || empty($email)) {
            $message = "يرجى ملء جميع الحقول.";
        } else {
            // استعلام لتحديث بيانات المحقق
            $sql = "UPDATE user SET firstName = ?, middleName = ?, lastName = ?, email = ?, roleID = ? WHERE userID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $fName, $mName, $lName, $email, $roleID, $userID);

            if ($stmt->execute()) {
                $message = "تم تعديل بيانات المحقق بنجاح!";
            } else {
                $message = "حدث خطأ أثناء تعديل البيانات.";
            }
        }
    }

    $stmt->close();
    $conn->close();
} else {
    $message = "لم يتم تحديد المحقق.";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تعديل بيانات المحقق</title>
    <style>
        .message {
            margin-top: 20px;
            font-size: 16px;
            padding: 8px 15px;
            border-radius: 5px;
            text-align: center;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            font-family: 'Arial', sans-serif;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<?php if (isset($message)): ?>
    <div class="message <?php echo strpos($message, 'نجاح') !== false ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<form action="edit_detective.php?userID=<?php echo $userID; ?>" method="POST">
    <div class="form-container">
        <div class="field">
            <label for="fName" class="field-label">الإسم الأول:</label>
            <input 
                type="text" 
                id="fName" 
                name="fName" 
                class="styled-select" 
                value="<?php echo isset($user) ? $user['firstName'] : ''; ?>"
                placeholder="ادخل الإسم الأول للمحقق"
            >
        </div>
        <div class="field">
            <label for="mName" class="field-label">الإسم الأوسط:</label>
            <input 
                type="text" 
                id="mName" 
                name="mName" 
                class="styled-select" 
                value="<?php echo isset($user) ? $user['middleName'] : ''; ?>"
                placeholder="ادخل الإسم الأوسط للمحقق"
            >
        </div>
        <div class="field">
            <label for="lName" class="field-label">الإسم الأخير:</label>
            <input 
                type="text" 
                id="lName" 
                name="lName" 
                class="styled-select" 
                value="<?php echo isset($user) ? $user['lastName'] : ''; ?>"
                placeholder="ادخل الإسم الأخير"
            >
        </div>
        <div class="field">
            <label for="email" class="field-label">الإيميل:</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="styled-select" 
                value="<?php echo isset($user) ? $user['email'] : ''; ?>"
                placeholder="example@email.com"
            >
        </div>
        <div class="field">
            <label for="roleID" class="field-label">الدور:</label>
            <select id="roleID" name="roleID" class="styled-select">
                <option value="1" <?php echo isset($user) && $user['roleID'] == 1 ? 'selected' : ''; ?>>محقق</option>
                <option value="2" <?php echo isset($user) && $user['roleID'] == 2 ? 'selected' : ''; ?>>مسؤول</option>
            </select>
        </div>
        <div class="start-button-container">
            <button type="submit" class="start-button">تعديل</button>
        </div>
    </div>
</form>

</body>
</html>
