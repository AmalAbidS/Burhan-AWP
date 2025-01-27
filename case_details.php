<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "Ra-mehdar18";
$dbname = "BurhanSystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve case ID from URL
$case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch case details along with full location information
$sql = "SELECT 
            c.caseID, 
            c.title, 
            c.additionalInfo, 
            c.status, 
            c.caseType, 
            c.criminal,
            c.victim,
            l.city, 
            l.neighborhood, 
            l.street, 
            l.postalCode 
        FROM `case` c 
        LEFT JOIN `location` l ON c.locationID = l.locationID 
        WHERE c.caseID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $case_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the case exists
if ($result->num_rows > 0) {
    $case = $result->fetch_assoc();
} else {
    echo "<h2>Case not found.</h2>";
    exit;
}

// Retrieve user role from session
$roleID = isset($_SESSION['roleID']) ? intval($_SESSION['roleID']) : 0; // 1 = Admin, 2 = Detective

// Close database connection
$stmt->close();
$conn->close();
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
    <title>تفاصيل القضية</title> 
    <style>
        body {
            font-family: 'Almarai', sans-serif;
            background-color: #1e2a38;
            color: #fff;
            margin: 0;
            padding: 20px;
        }

        .case-details-container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #2d3e50;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #ffcc00;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }

        table th {
            background-color: #ad8a15;
            color: #1e2a38;
        }

        table td {
            background-color: #354a5f;
            color: #d3d3d3;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #ad8a15;
            color: #1e2a38;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .btn:hover {
            background-color: #e6b800;
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
        <li><a href="{{ route('index') }}"class="active"  ><i class="fa fa-home"></i> الرئيسية</a></li> 
            <li><a href="{{ route('currentCase') }}" ><i class="fa fa-search"></i>إضافة محقق</a></li>
            <li><a href="{{ route(name: 'archive') }}" ><i class="fa fa-archive"></i> قسم الإدارة</a></li>
            <li><a href="{{ route(name: 'logout') }}"><i class="fa fa-sign-out"></i> تسجيل الخروج</a></li>
        </ul>
    </nav>
    <div class="case-details-container">
        <h1>تفاصيل القضية</h1>
        <table>
            <tr>
                <th>عنوان القضية</th>
                <td><?= htmlspecialchars($case['title']); ?></td>
            </tr>
            <tr>
                <th>الموقع</th>
                <td>
                    <?= "المدينة: " . htmlspecialchars($case['city']) . "<br>" ?>
                    <?= "الحي: " . htmlspecialchars($case['neighborhood']) . "<br>" ?>
                    <?= "الشارع: " . htmlspecialchars($case['street']) . "<br>" ?>
                    <?= "الرمز البريدي: " . htmlspecialchars($case['postalCode']); ?>
                </td>
            </tr>
            <tr>
                <th>حالة القضية</th>
                <td><?= htmlspecialchars($case['status']); ?></td>
            </tr>
            <tr>
                <th>نوع القضية</th>
                <td><?= htmlspecialchars($case['caseType']); ?></td>
            </tr>
            <tr>
                <th>معلومات إضافية</th>
                <td><?= htmlspecialchars($case['additionalInfo']); ?></td>
            </tr>
            <tr>
                <th>المجرم</th>
                <td><?= htmlspecialchars($case['criminal']); ?></td>
            </tr>
            <tr>
                <th>الضحية</th>
                <td><?= htmlspecialchars($case['victim']); ?></td>
            </tr>
        </table>
        <a href="index.php" class="btn">العودة إلى القضايا</a>

        <!-- Edit button for detectives only -->
        <?php if ($roleID == 2): ?>
            <a href="editCase.php?id=<?= $case_id ?>" class="btn">تعديل القضية</a>
        <?php endif; ?>
    </div>
</body>
</html>
