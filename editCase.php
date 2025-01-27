<?php
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

// Fetch case details if caseID is provided
$case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$case = null;
$location_details = "";

if ($case_id) {
    // Fetch case details
    $query = "SELECT c.*, l.city, l.neighborhood, l.street, l.postalCode FROM `case` c 
              LEFT JOIN `location` l ON c.locationID = l.locationID WHERE c.caseID = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        die("Failed to execute query: " . $stmt->error);
    }

    $case = $result->fetch_assoc();

    if ($case) {
        // Store location details for display
        $location_details = [
            'city' => $case['city'],
            'neighborhood' => $case['neighborhood'],
            'street' => $case['street'],
            'postalCode' => $case['postalCode']
        ];
    } else {
        echo "<p>No case found for caseID: $case_id</p>";
    }

    $stmt->close();
} else {
    echo "<p>Invalid or missing caseID</p>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $case) {
    $title = $_POST['title'];
    $case_type = $_POST['case_type'];
    $additional_info = $_POST['additional_info'];
    $status = $_POST['status'];
    $criminal = json_encode($_POST['criminal']);
    $victim = json_encode($_POST['victim']);

    // Handle location update if needed
    $location_id = $case['locationID'];  // Default to the current locationID

    // Check which location fields are updated and build the location query accordingly
    $updated_location = [];
    if (!empty($_POST['city'])) {
        $updated_location[] = "city = '" . $conn->real_escape_string($_POST['city']) . "'";
    }
    if (!empty($_POST['neighborhood'])) {
        $updated_location[] = "neighborhood = '" . $conn->real_escape_string($_POST['neighborhood']) . "'";
    }
    if (!empty($_POST['street'])) {
        $updated_location[] = "street = '" . $conn->real_escape_string($_POST['street']) . "'";
    }
    if (!empty($_POST['postalCode'])) {
        $updated_location[] = "postalCode = '" . $conn->real_escape_string($_POST['postalCode']) . "'";
    }

    // If location data was updated, insert into the `location` table and get the locationID
    if (count($updated_location) > 0) {
        $update_location_query = "UPDATE `location` SET " . implode(", ", $updated_location) . " WHERE locationID = ?";
        $stmt = $conn->prepare($update_location_query);
        $stmt->bind_param("i", $case['locationID']);
        $stmt->execute();
        $stmt->close();
    }

    // Update the `case` table with the new values
    $update_query = "UPDATE `case` SET title = ?, caseType = ?, `status` = ?, additionalInfo = ?, criminal = ?, victim = ?, locationID = ? WHERE caseID = ?";
    $stmt = $conn->prepare($update_query);

    if (!$stmt) {
        die("Failed to prepare update statement: " . $conn->error);
    }

    $stmt->bind_param("ssssssii", $title, $case_type, $status, $additional_info, $criminal, $victim, $location_id, $case_id);

    if ($stmt->execute()) {
        echo "<p>Case updated successfully!</p>";
        // Optionally refresh the case details
        header("Location: editCase.php?id=$case_id");
        exit;
    } else {
        echo "<p>Error updating case: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

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
        /* Page-Specific Wrapper */
        .page-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Form Container */
        .form-container {
            direction: rtl;
            display: flex;
            flex-direction: column;
            gap: 5px;
            width: 100%;
            max-width: 35%;
            box-sizing: border-box;
            height: 80vh;
            min-height: 150px;
            overflow: hidden;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #0c203e25 transparent;
        }

        .form-container select option {
            background-color: #ffffff;
            color: #333333;
            padding: 5px;
            border: none;
        }

        /* Labels */
        .field-label {
            padding-bottom: 10px;
            font-family: 'Almarai', sans-serif;
            font-size: 20px;
            color: #a68731;
            font-weight: bold;
            text-align: right;
            width: 100%;
        }

        /* Styled Select */
        .styled-select {
            width: 100%;
            padding: 10px 40px 10px 30px;
            font-size: 16px;
            color: #ffffff;
            background-color: transparent;
            border: 1px solid #ffffff;
            border-radius: 10px;
            text-align-last: right;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .styled-select-wrapper::before {
            content: "\f107";
            font-family: "FontAwesome";
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffffff;
            font-size: 25px;
            pointer-events: none;
        }

        .styled-select:focus {
            border-color: #9c2529;
            outline: none;
        }

        /* Styled Textarea */
        .styled-textarea {
            width: 100%;
            height: 120px;
            padding: 10px 15px;
            font-size: 16px;
            color: #ffffff;
            background-color: transparent;
            border: 1px solid #ffffff;
            border-radius: 10px;
            text-align: right;
            resize: none;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .styled-textarea:focus {
            border-color: #9c2529;
            outline: none;
        }

        /* Button Styles */
        .button {
            font-family: 'Almarai', sans-serif;
            display: inline-block;
            width: auto;
            padding: 10px 20px;
            font-size: 25px;
            color: #0d192b;
            background-color: #a68731;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .button:hover {
            background-color: #916c28;
            transform: scale(1.05);
        }

        .button:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(166, 135, 49, 0.8);
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
<div class="page-wrapper">
    <div class="form-container">
        <?php if ($case): ?>
            <form method="POST">
                <div class="field">
                    <label class="field-label" for="title">عنوان القضية</label>
                    <input class="styled-input" type="text" name="title" value="<?= htmlspecialchars($case['title']) ?>" required>
                </div>

                <div class="field">
                    <label class="field-label" for="case_type">نوع القضية المحتمل</label>
                    <select class="styled-select" name="case_type" required>
                        <option value="جريمة ضد الاشخاص" <?= $case['caseType'] == 'جريمة ضد الاشخاص' ? 'selected' : '' ?>>جريمة ضد الأشخاص</option>
                        <option value="جريمة ضد الممتلكات" <?= $case['caseType'] == 'جريمة ضد الممتلكات' ? 'selected' : '' ?>>جريمة ضد الممتلكات</option>
                        <option value="جريمة ضد الاشخاص والممتلكات" <?= $case['caseType'] == 'جريمة ضد الاشخاص والممتلكات' ? 'selected' : '' ?>>جريمة ضد الممتلكات والأشخاص</option>
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="additional_info">معلومات القضية </label>
                    <textarea class="styled-textarea" name="additional_info"><?= htmlspecialchars($case['additionalInfo']) ?></textarea>
                </div>

                <div class="field">
                    <label class="field-label" for="status">حالة القضية</label>
                    <input class="styled-input" type="text" name="status" value="<?= htmlspecialchars($case['status']) ?>" required>
                </div>
                
                <label for="location" class="field-label">الموقع</label>
                <div class="location-container">
                    <div class="location-fields">
                        <div class="field">
                            <label class="field-label" for="city">المدينة</label>
                            <input class="styled-input" type="text" name="city" value="<?= htmlspecialchars($location_details['city']) ?>">
                        </div>
                        <div class="field">
                            <label class="field-label" for="neighborhood">الحي</label>
                            <input class="styled-input" type="text" name="neighborhood" value="<?= htmlspecialchars($location_details['neighborhood']) ?>">
                        </div>
                        <div class="field">
                            <label class="field-label" for="street">الشارع</label>
                            <input class="styled-input" type="text" name="street" value="<?= htmlspecialchars($location_details['street']) ?>">
                        </div>
                        <div class="field">
                            <label class="field-label" for="postalCode">الرمز البريدي</label>
                            <input class="styled-input" type="text" name="postalCode" value="<?= htmlspecialchars($location_details['postalCode']) ?>">
                        </div>
                    </div>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d29731.71974583291!2d39.983336709305036!3d21.33214745970783!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x15c2063b00000001%3A0xa61844c2dd399c56!2z2KzYp9mF2LnYqSDYo9mFINin2YTZgtix2YkgLyDYtNi32LEg2KfZhNi32KfZhNio2KfYqg!5e0!3m2!1sar!2ssa!4v1733410031314!5m2!1sar!2ssa"
                    class="styled-iframe" allowfullscreen>
                    </iframe>
                </div>

                <div class="field">
                    <label class="field-label" for="criminal">الجاني</label>
                    <textarea class="styled-textarea" name="criminal"><?= htmlspecialchars($case['criminal']) ?></textarea>
                </div>

                <div class="field">
                    <label class="field-label" for="victim">الضحية</label>
                    <textarea class="styled-textarea" name="victim"><?= htmlspecialchars($case['victim']) ?></textarea>
                </div>

                <div class="field">
                    <button onclick="confirmEdit()" class="button" type="submit">تعديل</button>
                    <a href="index.php" class="button">العودة إلى القضايا</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

<script>
        function confirmEdit() {
            // Confirmation dialog
            const userConfirmed = confirm("هل أنت متأكد أنك تريد تعديل هذه القضية؟");
            
            if (userConfirmed) {
                // If user clicks "OK"
                alert("تم تعديل القضية بنجاح ✅");
            } else {
                // If user clicks "Cancel"
                alert("تم إلغاء التعديل ❌");
            }
        }
</script>
