<?php
// التحقق من نجاح عملية إضافة المحقق
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // التحقق من البيانات المدخلة
    $fName = isset($_POST['fName']) ? $_POST['fName'] : '';
    $lName = isset($_POST['lName']) ? $_POST['lName'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // التحقق من أن الحقول ليست فارغة
    if (empty($fName) || empty($lName) || empty($email) || empty($password)) {
        $message = "يرجى ملء جميع الحقول.";
    } else {
        // التحقق من صحة البريد الإلكتروني
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "البريد الإلكتروني غير صحيح.";
        } elseif (strlen($password) < 6) {
            $message = "كلمة المرور يجب أن تكون على الأقل 6 أحرف.";
        } else {
            // هنا يمكنك إضافة الكود الخاص بإدخال البيانات في قاعدة البيانات أو معالجتها
            // في هذا المثال نقوم فقط بعرض رسالة نجاح
            $message = "تم إضافة المحقق بنجاح!";
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
        font-size: 16px; /* أصغر حجم للخط */
        padding: 8px 15px; /* تقليل المسافة الداخلية */
        border-radius: 5px;
        text-align: center;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
        font-family: 'Almarai', sans-serif; /* الخط الجديد */
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

    /* تأثير الظل */
    .message {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    </style>

</head>
<body>

  
  <!-- الحاوية الرئيسية -->
  <div class="profile-container">
    <!-- الخطوط الخلفية -->
    <div class="background-lines"></div>
    <!-- المحتوى الأمامي -->
    <div class="profile-card">
    <div class="profile-icon">
        <i class="fas fa-user-circle"></i>  <!-- أيقونة مستخدم من Font Awesome -->
    </div>      
    <div class="profile-text">
          <!-- عرض اسم المستخدم من قاعدة البيانات -->
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
            <li><a href="{{ route('currentCase') }}" class="active" ><i class="fa fa-search"></i>إضافة محقق</a></li>
            <li><a href="{{ route(name: 'archive') }}" ><i class="fa fa-archive"></i> قسم الإدارة</a></li>
            <li><a href="{{ route(name: 'logout') }}"><i class="fa fa-sign-out"></i> تسجيل الخروج</a></li>
        </ul>
    </nav>

<!-- عرض الرسالة بعد المعالجة -->
<?php if ($message): ?>
    <div class="message <?php echo strpos($message, 'نجاح') !== false ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- نموذج إضافة المحقق -->
<form action="" method="POST">
    <div class="page-wrapper">
        <div class="form-container">
            <div class="field">
                <label for="fName" class="field-label">الإسم الأول:</label>
                <input 
                    type="text" 
                    id="fName" 
                    name="fName" 
                    class="styled-select" 
                    placeholder="الإسم الأول:"
                    value="<?php echo htmlspecialchars($fName); ?>"
                >
            </div>
            <div class="field">
                <label for="lName" class="field-label">الإسم الأخير:</label>
                <input 
                    type="text" 
                    id="lName" 
                    name="lName" 
                    class="styled-select" 
                    placeholder="الإسم الأخير"
                    value="<?php echo htmlspecialchars($lName); ?>"
                >
            </div>
            <div class="field">
                <label for="email" class="field-label">الإيميل:</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="styled-select" 
                    placeholder="ادخل الإيميل الخاص بالمحقق"
                    value="<?php echo htmlspecialchars($email); ?>"
                >
            </div>
            <div class="field">
                <label for="password" class="field-label">كلمة المرور:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="styled-select" 
                    placeholder="ادخل كلمة المرور الخاصة بالمحقق"
                >
            </div>
        </div>

        <!-- زر الإرسال -->
        <div class="start-button-container">
            <button type="submit" class="start-button">إضافة</button>
        </div>
    </div>
</form>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
  <script src="js/vendor/bootstrap.min.js"></script>
  <script src="js/datepicker.js"></script>
  <script src="js/plugins.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
