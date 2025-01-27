<?php
// إعداد الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "BurhanSystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// بدء الجلسة
session_start();

// إذا كان المستخدم مسجل دخوله بالفعل، إعادة التوجيه إلى الصفحة المناسبة
if (isset($_SESSION['userID'])) {
    $roleID = $_SESSION['roleID'];
    if ($roleID == 1) { // المحقق
        header("Location: edit_detective.php");
        exit();
    } elseif ($roleID == 2) { // المسؤول
        header("Location: add_detective.php");
        exit();

    
}else {
    header("Location: login.php"); // صفحة افتراضية للمستخدمين الآخرين
    exit();
}
}

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // التحقق من صحة المدخلات
    if (empty($email) || empty($password)) {
        $errorMessage = "يرجى إدخال البريد الإلكتروني وكلمة المرور.";
    } else {
        $sql = "SELECT u.userID, u.password, u.roleID, r.roleName 
                FROM User u
                JOIN Role r ON u.roleID = r.roleID
                WHERE u.email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stored_password = $row['password'];
                $roleID = $row['roleID'];
                $roleName = $row['roleName'];

                // التحقق من كلمة المرور
                if (password_verify($password, $stored_password)) {
                    // حفظ بيانات المستخدم في الجلسة
                    $_SESSION['userID'] = $row['userID'];
                    $_SESSION['roleID'] = $roleID;
                    $_SESSION['roleName'] = $roleName;

                    // التوجيه بناءً على roleID
                    if ($roleID == 1) { // المحقق
                        header("Location: edit_detective.php");
                        exit();
                    } elseif ($roleID == 2) { // المسؤول
                        header("Location: add_detective.php");
                        exit();
                    } else {
                        header("Location: login.php"); // صفحة افتراضية للمستخدمين الآخرين
                        exit();
                    }
                } else {
                    $errorMessage = "كلمة المرور غير صحيحة.";
                }
            } else {
                $errorMessage = "البريد الإلكتروني غير مسجل.";
            }
        } else {
            $errorMessage = "حدث خطأ في النظام.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            position: fixed;
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: 'Almarai', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #1b2a38;
            overflow: hidden;
        }

        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 80%;
            background: linear-gradient(30deg, #1a2a3a, #17202a, #101d2e, #1a2a3a);
            background-size: 60% 60%;
            z-index: -1;
        }

        .overlay-image {
            position: absolute;
            top: 25%;
            left: 25%;
            transform: translate(-50%, -50%);
            width: 70%;
            z-index: 0;
            opacity: 0.3;
        }

        .over-image {
            position: absolute;
            top: 80%;
            left: 110%;
            transform: translate(-50%, -50%);
            width: 70%;
            z-index: 0;
            opacity: 0.2;
        }

        .container {
            position: fixed;
            display: flex;
            width: 80%;
            height: 70%;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0 25px 40px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            margin: auto;
            z-index: 1;
        }

        .navbar {
            width: 100%;
            position: fixed;
            top: 0;
            right: 0;
            display: flex;
            justify-content: flex-end;
            padding: 200px 500px;
            box-sizing: border-box;
            z-index: 1000;
            padding-top: 50px;
        }

        .navbar ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .navbar li {
            margin-left: 20px;
        }

        .navbar a {
            text-decoration: none;
            color: #fbf4d8;
            font-size: 20px;
            transition: color 0.3s;
        }

        .navbar a:hover {
            color: #A18842;
        }

        .logo {
            margin-bottom: 590px;
            margin-right: 1270px;
            width: 180px;
            height: auto;
            opacity: 0.8;
        }

        .login-section {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            background-color: rgba(52, 73, 94, 0.5);
        }

        .login-section h2 {
            color: #f5f5dc;
            font-size: 40px;
            font-weight: 600;
            margin: 0 auto;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 70px;
        }

        .login-section form {
            width: 100%;
        }

        .login-section input {
            width: 94%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 2px solid #ecf0f1;
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .login-section input:focus {
            border: 2px solid #A18842;
            background: rgba(255, 255, 255, 0.5);
            outline: none;
        }

        .login-section button {
            margin-top: 14px;
            width: 100%;
            padding: 12px;
            background: #8d6e3a;
            color: #fff;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Almarai', sans-serif;
            color: #fbf4d8;
        }

        .login-section button:hover {
            background: #A18842;
        }

        .login-section .remember-me {
            margin-top: 2px;
            margin-left: 470px;
            color: #fff;
            font-size: 14px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            direction: rtl;
        }

        .login-section .remember-me input {
            margin-left: 7px;
            margin-right: 0;
        }

        .forgot-password {
            color: #A18842;
            font-size: 14px;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .image-section {
            flex: 1;
            background: url('img/CSII.jpg') no-repeat center center;
            background-size: cover;
        }


        .error-message {
    background-color: #ff4d4d;
    color: white;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    text-align: center;
    width: 94%;
}
    </style>
</head>
<body>
    <header>
        <img src="public\img\burhan.png" alt="شعار الموقع" class="logo">
    </header>
    <nav class="navbar">
        <ul>
            <li><a href="#about">عن برهان</a></li>
            <li><a href="#support">الدعم والمساعدة</a></li>
        </ul>
    </nav>
    <div class="visual-container">
        <div class="background"></div>
        <img src="img/line.png" alt="وصف الصورة" class="overlay-image">
        <img src="img/line.png" alt="وصف الصورة" class="over-image">
    </div>
    <div class="container">
        <div class="image-section"></div>
        <div class="login-section">
            <h2> ! أهلاً وسهلاً بك مجددًا</h2>
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <input type="email" name="email" placeholder="البريد الإلكتروني" required>
                <input type="password" name="password" placeholder="كلمة المرور" required>
                <div class="remember-me">
                    <input type="checkbox" id="rememberMe" name="rememberMe">
                    <label for="rememberMe">تذكّرني</label>
                </div>
                <button type="submit">تسجيل دخول</button>
                <a href="#" class="forgot-password">هل نسيت كلمة المرور؟</a>
            </form>
        </div>
    </div>
</body>
</html>