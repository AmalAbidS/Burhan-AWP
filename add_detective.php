<?php

// التحقق من أن المستخدم لديه دور مسؤول
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'مسؤول') {
    header("Location: login.php"); // إذا لم يكن مسؤولًا، إعادة توجيهه إلى تسجيل الدخول
    exit();
}

// التحقق من نجاح عملية إضافة المحقق
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // التحقق من البيانات المدخلة
    $fName = isset($_POST['fName']) ? $_POST['fName'] : '';
    $mName = isset($_POST['mName']) ? $_POST['mName'] : '';
    $lName = isset($_POST['lName']) ? $_POST['lName'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $roleID = isset($_POST['roleID']) ? $_POST['roleID'] : 1;  // الحصول على الدور من النموذج

    // التحقق من أن الحقول ليست فارغة
    if (empty($fName) || empty($mName) || empty($lName) || empty($email) || empty($password)) {
        $message = "يرجى ملء جميع الحقول.";
    } else {
        // التحقق من صحة البريد الإلكتروني
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "البريد الإلكتروني غير صحيح.";
        } elseif (strlen($password) < 8) {
            $message = "كلمة المرور يجب أن تكون على الأقل 8 أحرف.";
        } else {
            // الاتصال بقاعدة البيانات
            include('config.php');

            // التحقق من وجود roleID في جدول role
            $result = $conn->query("SELECT roleID FROM role WHERE roleID = '$roleID'");
            if ($result->num_rows == 0) {
                // إذا لم يوجد الدور في جدول role، نضيفه
                $roleName = $roleID == 1 ? 'محقق' : 'مسؤول'; // تحديد اسم الدور بناءً على roleID
                $conn->query("INSERT INTO role (roleID, roleName) VALUES ('$roleID', '$roleName')");
            }

            // إدخال البيانات في جدول user مع roleID
            $sql = "INSERT INTO user (firstName, middleName, lastName, email, password, roleID) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT); // تشفير كلمة المرور
                $stmt->bind_param("sssssi", $fName, $mName, $lName, $email, $hashed_password, $roleID);

                if ($stmt->execute()) {
                    $message = "تم إضافة المحقق بنجاح!";
                } else {
                    $message = "حدث خطأ أثناء إدخال البيانات.";
                }

                $stmt->close();
            } else {
                $message = "فشل إعداد الطلب.";
            }

            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/fontAwesome.css">
    <link rel="stylesheet" href="css/light-box.css">
    <link rel="stylesheet" href="public/css/styles.css">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;600&display=swap" rel="stylesheet">
    <title>إضافة المحقق</title> 

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
            font-family: 'Almarai', sans-serif;
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
  
  <div class="profile-container">
    <div class="background-lines"></div>
    <div class="profile-card">
        <div class="profile-icon">
            <i class="fas fa-user-circle"></i>
        </div>      
        <div class="profile-text">
            <h2>{{ Auth::user()->name }}</h2>
            <p>{{ Auth::user()->job_title ?? 'محقق' }}</p>
        </div>
    </div>
  </div>

  <nav id="navbar">
        <div class="logo" id="logo">
            <img src="public\img\burhan.png" alt="Burhan Logo">
        </div>
        <ul>
        <li><a href="{{ route('index') }}"  ><i class="fa fa-home"></i> الرئيسية</a></li> 
<li><a href="{{ route('currentCase') }}" class="active" ><i class="fa fa-user-plus"></i> إضافة محقق</a></li>
<li><a href="{{ route(name: 'archive') }}" ><i class="fa fa-folder-open"></i> قسم الإدارة</a></li>
<li><a href="{{ route(name: 'logout') }}"><i class="fa fa-door-open"></i> تسجيل الخروج</a></li>
        </ul>
    </nav>

<?php if ($message): ?>
    <div class="message <?php echo strpos($message, 'نجاح') !== false ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<form action="" method="POST">
    <div class="page-wrapper">
        <div class="form-container">
            <div class="field">
                <label for="fName" class="field-label">الإسم الأول:</label>
                <input type="text" id="fName" name="fName" class="styled-select" placeholder="ادخل الإسم الأول للمحقق">
            </div>
            <div class="field">
                <label for="mName" class="field-label">الإسم الأوسط :</label>
                <input type="text" id="mName" name="mName" class="styled-select" placeholder="ادخل الإسم الأوسط للمحقق">
            </div>
            <div class="field">
                <label for="lName" class="field-label">الإسم الأخير:</label>
                <input type="text" id="lName" name="lName" class="styled-select" placeholder="ادخل الإسم الأخير">
            </div>
            <div class="field">
                <label for="email" class="field-label">الإيميل:</label>
                <input type="email" id="email" name="email" class="styled-select" placeholder="example@email.com">
            </div>
            <div class="field">
                <label for="password" class="field-label">كلمة المرور:</label>
                <input type="password" id="password" name="password" class="styled-select" placeholder="ادخل كلمة المرور">
            </div>
            <div class="field">
                <label for="roleID" class="field-label">الدور:</label>
                <select id="roleID" name="roleID" class="styled-select">
                    <option value="1">محقق</option>
                    <option value="2">مسؤول</option>
                </select>
            </div>
        </div>

        <div class="start-button-container">
            <button type="submit" class="start-button">إضافة</button>
        </div>
    </div>
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="js/vendor/bootstrap.min.js"></script>
<script src="js/datepicker.js"></script>
<script src="js/plugins.js"></script>
<script src="js/main.js"></script>
</body>
</html>
