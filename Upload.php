<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include config.php;
    // $servername = "127.0.0.1";
    // $username = "root";
    // $password = "farah123";
    // $dbname = "burhansystem";

    // $conn = new mysqli($servername, $username, $password, $dbname);

    // if ($conn->connect_error) {
    //     die("Connection failed: " . $conn->connect_error);
    // }

    $evidenceInfo = isset($_POST['evidence']) ? htmlspecialchars(trim($_POST['evidence'])) : '';
    $caseID = isset($_GET['caseID']) ? intval($_GET['caseID']) : 0;

    if ($caseID === 0) {
        die("Invalid case ID.");
    }

    if (isset($_FILES['images']['tmp_name'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $error = $_FILES['images']['error'][$key];

            if ($error === UPLOAD_ERR_OK) {
                $imageData = file_get_contents($tmpName); // Read the file as binary data
                $imageName = $_FILES['images']['name'][$key]; // Get original file name

                // Prepare SQL to insert data into the evidence table
                $sql = "INSERT INTO evidence (caseID, evidenceInfo, image) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("iss", $caseID, $evidenceInfo, $imageData);

                if ($stmt->execute()) {
                    echo "Evidence \"$imageName\" added successfully.<br>";
                } else {
                    echo "Error inserting evidence: " . $stmt->error . "<br>";
                }

                $stmt->close();
            } else {
                echo "File upload error for file " . $_FILES['images']['name'][$key] . ". Error code: $error<br>";
            }
        }
    } else {
        echo "No files uploaded.<br>";
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>تحميل صور مسرح الجريمة</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <!-- ربط ملفات الـ CSS -->
    <link rel="stylesheet" href="public/css/styles.css">

    <!-- ربط الخطوط (Google Fonts) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;600&display=swap" rel="stylesheet">

    <!-- ربط الـ JS (المكتبات الخارجية) -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
    <script src="https://cdn.rawgit.com/exif-js/exif-js/master/exif.js"></script>

    <style>
        a {
            color: #369;
        }

        .box {
            width: 180px;

        }

        .box,
        #details_img {
            max-width: 300px;
            /* تقليل العرض */
            margin: 20px auto 0;
            /* رفع العناصر للأعلى بتقليل المسافة السفلية */
            display: block;
        }

        .note {
            width: 500px;
            margin: 50px auto;
            font-size: 1.1em;
            color: #333;
            text-align: justify;
        }

        .areapaste input {
            width: 80%;
            margin: 5px auto;
            display: block;
            font-size: 14px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #drop-area {
            border: 2px dashed #aaa;
            width: 120%;
            /* تحديد العرض */
            height: 100px;
            /* تحديد الطول */
            overflow: hidden;
            margin: 10px auto;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            /* توسيط المحتوى داخل العنصر */
            position: relative;
            top: 0px;
            /* رفع المنطقة للأعلى */
        }

        #drop-area .controll-panel {
            position: absolute;
            bottom: 10px;
            /* رفع لوحة التحكم للأعلى */
            right: 10px;
            display: flex;
            gap: 5px;
            /* إضافة مسافة بين العناصر */
        }

        #drop-area .controll-panel .item {
            display: inline-block;
            padding: 10px;
            cursor: pointer;
            background-color: #23232360;
        }

        #drop-area .controll-panel .item i {
            color: #fff;
        }

        #drop-area label {
            margin-bottom: 0;
            height: 100%;
            width: 100%;
            display: block;
        }

        /* التأكد من أن المحتوى داخل المربع لا يؤثر على الحجم */
        #drop-area .uploadIcon {
            display: flex;
            /* Enable flexbox for centering */
            justify-content: center;
            /* Center horizontally */
            align-items: center;
            height: 100px;
            /* Set a fixed height for the container */
            width: 100%;
            /* Take the full width of the parent */
            color: #ffffff;
            /* Make the icon color white */
        }

        /* منع العناصر من تغيير الحجم عند إضافة محتوى */
        #drop-area #gallery {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            /* التأكد من أن المحتوى الزائد لا يظهر */
        }

        .uploadIcon i {
            display: flex;
            /* Enable flexbox for centering */
            justify-content: center;
            /* Center horizontally */
            align-items: center;
            color: #ffffff;
            /* Set the icon color to white */
        }

        #drop-area.highlight {
            border-color: purple;
        }

        p {
            margin-top: 0;
        }

        .my-form {
            margin-bottom: 10px;
        }

        #gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
            height: 100px;
            /* الحد الأقصى لارتفاع المربع */
            overflow: hidden;
            /* إخفاء الصور التي تتجاوز الحدود */
        }

        #gallery img {
            object-fit: cover;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            /* الحفاظ على نسبة العرض والارتفاع دون التأثير على الحجم */
        }

        .button {
            display: inline-block;
            padding: 10px;
            background: #ccc;
            cursor: pointer;
            border-radius: 5px;
            border: 1px solid #ccc;
            justify-content: center;
        }

        .button:hover {
            background: #ddd;
        }

        #fileElem {
            display: none;
        }

        #sendForm {
            width: 100%;
        }

        #sendForm .areapaste input {
            padding-left: 10px;
            width: 320px;
            height: 35px;
            font-size: 16px;
        }

        /* رفع التفاصيل للأعلى */
        #details_img {
            text-align: center;
            margin-top: 0px;
            color: #fff;
            justify-content: center;
        }

        #details_img .list .item {
            margin-bottom: 10px;
        }

        #details_img .list .item .attr {
            width: 120px;
        }

        #details_img .list .item .attr,
        #details_img .list .item .data {
            display: inline-block;
        }

        #container-image .container {
            width: 200px;
            margin: 10px auto;
            /* توسيط الصور */
        }

        /* عرض عدد الصور في #gallery */
        #gallery-count {
            text-align: center;
            font-size: 18px;
            margin-top: 10px;
        }

        .progress-tracker {
            display: flex;
            margin: 25px auto;
            padding: 0;
            list-style: none;


        }
    </style>
</head>

<body>

    <nav id="navbar">
        <div class="logo" id="logo">
            <img src="public\img\burhan.png" alt="Burhan Logo">

        </div>
        <ul>
            <li>
            <li><a href="{{ route('index') }}"><i class="fa fa-home"></i> الرئيسية</a></li>
            <li><a href="{{ route('newCase') }}" class="active"><i class="fa fa-file"></i> قضية جديدة</a></li>
            <li><a href="{{ route('currentCase') }}"><i class="fa fa-search"></i>القضية الحالية</a></li>
            <li><a href="{{ route(name: 'archive') }}"><i class="fa fa-archive"></i> الأرشيف</a></li>
            <li><a href="{{ route(name: 'logout') }}"><i class="fa fa-sign-out"></i> تسجيل الخروج</a></li>
            <li><a href="#"><i class="fa fa-question-circle"></i> مساعدة</a></li>
        </ul>

    </nav>


    <!-- الحاوية الرئيسية -->
    <div class="profile-container">
        <!-- الخطوط الخلفية -->
        <div class="background-lines"></div>

        <!-- المحتوى الأمامي -->
        <div class="profile-card">
            <img src="public/img/AhmedImage.png" alt="أحمد الحربي" class="profile-img">
            <div class="profile-text">
                <h2>أحمد الحربي</h2>
                <p>محقق</p>
            </div>
        </div>
    </div>

    <div class="fullwidth">
        <div class="container separator">


            <ul class="progress-tracker progress-tracker--text progress-tracker--center">
                <li class="progress-step is-complete">
                    <div class="progress-marker">1</div>
                    <div class="progress-text">
                        <h4 class="progress-title">معلومات</h4>

                    </div>
                </li>

                <li class="progress-step is-active">
                    <div class="progress-marker">2</div>
                    <div class="progress-text">
                        <h4 class="progress-title">تحميل</h4>

                    </div>
                </li>

        </div>
    </div>


    <form action="" id="sendForm" method="POST" enctype="multipart/form-data">
        <div class="page-wrapper">
            <div class="form-container">
                <div class="field">
                    <label for="additional-info" class="field-label">الأدلة</label>
                    <input type="text" id="evidence" name="evidence" class="styled-input" placeholder=" " required />
                </div>
                <div class="field">

                    <label for="fileElem">
                        <input type="file" name="images[]" id="fileElem" multiple accept="image/*">
                        <div class="uploadIcon">
                            <i class="fa fa-upload fa-8x" aria-hidden="true"></i>
                            <label for="additional-info" class="field-label"
                                style=" display: flex;   justify-content: center; align-items: center; font-size: 190%; padding:10px;">تحميل
                                صور الأدلة</label>

                        </div>
                    </label>
                    <div id="gallery"></div> <!-- المعرض لعرض الصور -->
                    <div class="controll-panel">
                        <div class="item">
                            <div id="delete-image" style="display:none;">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>


                </div>

            </div>

        </div>
        <div class="Analyis-btn-container">
            <button type="submit" class="Analyis-btn">إضافة</button>
        </div>
    </form>


    <!-- زر في اليمين -->
    <div class="Analyis-btn-container-right">
        <a href="newCase.php?caseID=<?php echo isset($_GET['caseID']) ? intval($_GET['caseID']) : 0; ?>"
            class="Analyis-btn">
            السابق
        </a>
    </div>



    <input type="file" id="uploadInput" multiple webkitdirectory style="display: none;">





</body>

</html>