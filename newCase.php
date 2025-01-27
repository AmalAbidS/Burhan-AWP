<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('config.php');
// // Database connection
// $servername = "127.0.0.1";
// $username = "root";
// $password = "farah123";
// $dbname = "burhansystem";

// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check connection
// if ($conn->connect_error) {
//   die("Database connection failed: " . $conn->connect_error);
// }

// Function to sanitize input
function sanitize_input($data, $conn)
{
  return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === "POST") {
  // Retrieve and sanitize form data
  $caseName = isset($_POST['caseName']) ? sanitize_input($_POST['caseName'], $conn) : null;
  $caseType = isset($_POST['CaseType']) ? sanitize_input($_POST['CaseType'], $conn) : null;
  $description = isset($_POST['Description']) ? sanitize_input($_POST['Description'], $conn) : null;
  $city = isset($_POST['City']) ? sanitize_input($_POST['City'], $conn) : null;
  $neighborhood = isset($_POST['Neighborhood']) ? sanitize_input($_POST['Neighborhood'], $conn) : null;
  $street = isset($_POST['Street']) ? sanitize_input($_POST['Street'], $conn) : null;
  $postalCode = isset($_POST['PostalCode']) ? sanitize_input($_POST['PostalCode'], $conn) : null;
  // Sanitize and encode the input for 'criminal' and 'victim' if they are expected to be JSON
  $criminal = isset($_POST['criminal']) ? json_encode(sanitize_input($_POST['criminal'], $conn)) : null;
  $victim = isset($_POST['victim']) ? json_encode(sanitize_input($_POST['victim'], $conn)) : null;

  // Validate required fields
  if (!$caseName || !$caseType || !$description || !$city || !$neighborhood || !$street || !$postalCode || !$criminal || !$victim) {
    die("All required fields must be filled.");
  }


  // Insert location data
  $locationQuery = "INSERT INTO location (city, neighborhood, street, postalCode) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($locationQuery);
  $stmt->bind_param("ssss", $city, $neighborhood, $street, $postalCode);

  if ($stmt->execute()) {
    $locationID = $stmt->insert_id; // Get the ID of the inserted location
  } else {
    die("Error inserting location: " . $stmt->error);
  }

  $stmt->close();

  
  // Insert case data
  $adminID = 1; // Example admin ID
  $caseQuery = "INSERT INTO `case` (title, locationID, additionalInfo, caseType, criminal, victim, adminID, date, time) 
  VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), CURTIME())";
  $stmt = $conn->prepare($caseQuery);
  $stmt->bind_param("sissssi", $caseName, $locationID, $description, $caseType, $criminal, $victim, $adminID);

  if ($stmt->execute()) {
    $caseID = $stmt->insert_id; // Get the ID of the inserted case
  } else {
    die("Error inserting case: " . $stmt->error);
  }

  $stmt->close();
  $conn->close();

  // Redirect to the upload evidence page with the case ID
  header("Location: Upload.php?caseID=" . $caseID);
  exit(); // Stop execution after redirection
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>إضافة قضية جديدة</title>

  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS -->
  <link rel="stylesheet" href="public/css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
  <script src="https://cdn.rawgit.com/exif-js/exif-js/master/exif.js"></script>

  <style>
      * {
            font-family: 'Almarai', sans-serif;
        }
    /* Page-Specific Wrapper */
    .page-wrapper {
      display: flex;
      /* Enable flexbox for centering */
      justify-content: center;
      /* Center horizontally */
      align-items: center;
      /* Center vertically */
      position: relative;
    }

    /* Form Container */
    .form-container {
      direction: rtl;
      /* تأكيد اتجاه النص من اليمين لليسار */

      display: flex;
      flex-direction: column;
      gap: 5px;
      /* Space between fields */
      width: 100%;
      /* Adjust container width */
      max-width: 35%;
      /* Limit the width */
      box-sizing: border-box;

      height: 80vh;
      /* تقصير الطول */
      min-height: 150px;
      /* ضمان وجود حد أدنى للطول */
      overflow: hidden;
      /* إخفاء المحتوى الزائد إن وجد */

      overflow-y: auto;
      /* إضافة خصائص لتحسين التمرير */
      scrollbar-width: thin;
      /* لجعل شريط التمرير نحيفًا في Firefox */
      scrollbar-color: #0c203e25 transparent;
      /* لون التمرير وخلفيته في Firefox */

    }

    /* تخصيص الخيارات في القائمة */
    .form-container select option {
      background-color: #ffffff;
      /* خلفية بيضاء للخيارات */
      color: #333333;
      /* لون النص داخل الخيارات */
      padding: 5px;
      /* مساحة داخلية للخيارات */
      border: none;
      /* إزالة الحدود */
    }


    /* Labels */
    .field-label {
      padding-bottom: 20px;
      font-family: 'Almarai', sans-serif;
      font-size: 20px;
      color: #a68731;
      /* Gold-like color */
      font-weight: bold;
      text-align: right;
      width: 100%;
    }

    /* Styled Select Wrapper */
    .styled-select-wrapper {
      position: relative;

    }

    /* Styled Select */
    .styled-select {
      width: 100%;
      padding: 10px 40px 10px 30px;
      /* Extra padding on the left for the arrow */
      font-size: 16px;
      color: #ffffff;
      /* White text color */
      background-color: transparent;
      /* Transparent background */
      border: 1px solid #ffffff;
      /* White border */
      border-radius: 10px;
      /* Rounded corners */
      text-align-last: right;
      /* Align dropdown text to the right */
      cursor: pointer;
      appearance: none;
      /* Remove default dropdown arrow */
      -webkit-appearance: none;
      -moz-appearance: none;
      transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    /* Custom White Arrow for Styled Select */
    .styled-select-wrapper::before {
      content: "\f107";
      /* Downward arrow symbol */
      font-family: "FontAwesome";
      /* Specify the font family */
      position: absolute;
      left: 15px;
      /* Align the arrow to the left */
      top: 50%;
      /* Center vertically */
      transform: translateY(-50%);
      color: #ffffff;
      /* White arrow color */
      font-size: 25px;
      pointer-events: none;
      /* Prevent interaction with the arrow */
    }

    /* Styled Select Focus State */
    .styled-select:focus {
      border-color: #9c2529;
      /* Gold border on focus */
      outline: none;
    }

    /* Styled Textarea */
    .styled-textarea {
      width: 100%;
      /* Full width */
      height: 120px;
      /* Adjust height */
      padding: 10px 15px;
      font-size: 16px;
      color: #ffffff;
      /* White text color */
      background-color: transparent;
      /* Transparent background */
      border: 1px solid #ffffff;
      /* White border */
      border-radius: 10px;
      /* Rounded corners */
      text-align: right;
      /* Align text to the right */
      resize: none;
      /* Disable resizing */
      transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    /* Styled Textarea Focus State */
    .styled-textarea:focus {
      border-color: #9c2529;
      /* Gold border on focus */
      outline: none;
    }

    /* Location Container */
    .location-container {
      display: flex;
      justify-content: space-between;
      /* Align fields and iframe side by side */
      align-items: flex-start;
      /* Align items at the top */
      gap: 20px;
      /* Space between the fields and the iframe */
      border: 1px solid #ffffff;
      /* Enclose iframe and inputs with white border */
      border-radius: 10px;
      /* Rounded corners */
      padding: 30px;
      /* Padding inside the container */
      background-color: transparent;
      /* Transparent background */
      width: 100%;
      /* Full width */
      max-width: 100%;
      /* Limit overall width */
    }

    /* Input Fields Container */
    .location-fields {
      display: flex;
      flex-direction: column;
      /* Stack fields vertically */
      gap: 15px;
      /* Space between fields */
      flex: 1;
      /* Flexible width */
      max-width: 300px;
      /* Limit width of input fields */
    }

    /* Field Wrapper */
    .field {
      position: relative;
      width: 100%;
      margin-bottom: 20px;
    }

    /* Location Labels (Covering the Input) */
    .location-label {
      font-family: 'Almarai', sans-serif;
      position: absolute;
      top: 50%;
      right: 15px;
      /* Adjust spacing as needed */
      transform: translateY(-50%);
      /* Center vertically */
      background-color: transparent;
      /* Matches the background */
      font-size: 16px;
      color: #ffffff;
      /* White color */
      padding: 0 5px;
      /* Add padding for the text */
      pointer-events: none;
      /* Prevent clicking the label */
      transition: all 0.3s ease;
      /* Smooth transition */
    }

    /* Styled Inputs */
    .styled-input {
      width: 100%;
      padding: 15px;
      font-size: 16px;
      color: #ffffff;
      /* White text */
      background-color: transparent;
      /* Transparent background */
      border: 1px solid #ffffff;
      /* White border */
      border-radius: 10px;
      /* Rounded corners */
      text-align: right;
      /* Align text to the right */
      transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    /* Move the label when the input is focused or has text */
    .styled-input:focus+.location-label,
    .styled-input:not(:placeholder-shown)+.location-label {
      top: -10px;
      /* Move above the input */
      font-size: 12px;
      /* Smaller text */
      padding: 0 5px;
      /* Add padding to overlap */
    }

    .styled-input:focus {
      border-color: #9c2529;
      /* Gold border on focus */
      outline: none;
    }


    /* Styled Iframe */
    .styled-iframe {
      flex: 2;
      /* Take up more space than the inputs */
      height: 350px;
      /* Fixed height */
      border-radius: 10px;
      /* Rounded corners for iframe */
      border: 1px solid #ffffff;
      /* White border around iframe */
    }

    /* Responsive Design for Small Screens */
    @media (max-width: 768px) {
      .location-container {
        flex-direction: column;
        /* Stack iframe and inputs vertically */
      }

      .styled-iframe {
        width: 50%;
        /* Full width on smaller screens */
        height: 200px;
        /* Reduce iframe height on smaller screens */
      }
    }

    /* Button Container for Positioning */
    .start-button-container {
      position: fixed;
      bottom: 20px;
      /* Distance from the bottom */
      left: 20px;
      /* Distance from the left */
      z-index: 1000;
      /* Ensure it stays above other elements */
    }

    /* Start Button */
    .start-button {
      font-family: 'Almarai', sans-serif;
      display: inline-block;
      width: 150px;
      padding: 10px 20px;
      /* Adjust padding for the button size */
      font-size: 25px;
      /* Font size for the text */
      color: #0d192b;
      /* White font color */
      background-color: #a68731;
      /* Gold-like background */
      border: none;
      /* Remove default border */
      border-radius: 20px;
      /* Rounded corners */
      cursor: pointer;
      /* Pointer cursor on hover */
      text-align: center;
      /* Center text */
      transition: background-color 0.3s ease, transform 0.2s ease;
      /* Smooth transitions */
    }

    /* Hover Effect */
    .start-button:hover {
      background-color: #916c28;
      /* Slightly darker gold on hover */
      transform: scale(1.05);
      /* Slight scaling effect */
    }

    /* Focus Effect */
    .start-button:focus {
      outline: none;
      /* Remove default focus outline */
      box-shadow: 0 0 10px rgba(166, 135, 49, 0.8);
      /* Add a glowing effect */
    }

    /* PROGRESS TRACKER */
    .progress-wrap {
      display: flex;
      /* Use flexbox */
      align-items: center;
      /* Vertically align */
      justify-content: center;
      /* Horizontally align */
      margin: 20px;

    }

    .progress-tracker {
      display: flex;
      margin: 5px auto;
      padding: 0;
      list-style: none;
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
      <img src="public/img/AhmedImage.png" alt="أحمد الحربي" class="profile-img">
      <div class="profile-text">
        <h2>أحمد الحربي</h2>
        <p>محقق</p>
      </div>
    </div>
  </div>
  </div>

  <nav id="navbar">
    <div class="logo" id="logo">
      <img src="public\img\burhan.png" alt="Burhan Logo">
    </div>
    <ul>
      <li><a href="index.php"><i class="fa fa-home"></i> الرئيسية</a></li>
      <li><a href="newCase.php" class="active"><i class="fa fa-file"></i> قضية جديدة</a></li>
      <li><a href="archive.php"><i class="fa fa-archive"></i> الأرشيف</a></li>
      <li><a href=""><i class="fa fa-sign-out"></i> تسجيل الخروج</a></li>
    </ul>
  </nav>

  <div class="fullwidth">
    <div class="container separator">
      <ul class="progress-tracker progress-tracker--text progress-tracker--center">
        <li class="progress-step is-active">
          <div class="progress-marker">1</div>
          <div class="progress-text">
            <h4 class="progress-title">معلومات</h4>
          </div>
        </li>
        <li class="progress-step">
          <div class="progress-marker">2</div>
          <div class="progress-text">
            <h4 class="progress-title">تحميل</h4>
          </div>
        </li>
      </ul>
    </div>
  </div>

  <form action="" method="POST">
    <div class="page-wrapper">
      <div class="form-container">
        <div class="field">
          <label for="caseName" class="field-label">عنوان القضية</label>
          <input type="text" id="caseName" name="caseName" class="styled-input" placeholder=" " required />
        </div>

        <div class="field">
          <label for="crime-type" class="field-label">نوع القضية المحتمل</label>
          <div class="styled-select-wrapper">
            <select id="crime-type" name="CaseType" class="styled-select" required>
              <option value="" disabled selected hidden></option>
              <option value="جريمة ضد الاشخاص">جريمة ضد الأشخاص</option>
              <option value="جريمة ضد الممتلكات">جريمة ضد الممتلكات</option>
              <option value="جريمة ضد الاشخاص والممتلكات">جريمة ضد الممتلكات والأشخاص</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="description" class="field-label">معلومات القضية</label>
          <textarea id="description" name="Description" class="styled-textarea" required></textarea>
        </div>

        <div class="field">
          <label for="location" class="field-label">الموقع</label>
          <div class="location-container">
            <div class="location-fields">
              <div class="field">
                <input type="text" id="city" name="City" class="styled-input" placeholder=" " required />
                <label for="city" class="location-label">المدينة</label>
              </div>
              <div class="field">
                <input type="text" id="neighborhood" name="Neighborhood" class="styled-input" placeholder=" "
                  required />
                <label for="neighborhood" class="location-label">الحي</label>
              </div>
              <div class="field">
                <input type="text" id="street" name="Street" class="styled-input" placeholder=" " required />
                <label for="street" class="location-label">الشارع</label>
              </div>
              <div class="field">
                <input type="text" id="postal-code" name="PostalCode" class="styled-input" placeholder=" " required />
                <label for="postal-code" class="location-label">الرمز البريدي</label>
              </div>
            </div>
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d29731.71974583291!2d39.983336709305036!3d21.33214745970783!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x15c2063b00000001%3A0xa61844c2dd399c56!2z2KzYp9mF2LnYqSDYo9mFINin2YTZgtix2YkgLyDYtNi32LEg2KfZhNi32KfZhNio2KfYqg!5e0!3m2!1sar!2ssa!4v1733410031314!5m2!1sar!2ssa"
              class="styled-iframe" allowfullscreen>
            </iframe>

          </div>
        </div>

        <div class="field">
          <label for="criminal" class="field-label">الجاني</label>
          <input type="text" id="criminal" name="criminal" class="styled-input" placeholder=" " required />
        </div>

        <div class="field">
          <label for="victim" class="field-label">الضحية</label>
          <input type="text" id="victim" name="victim" class="styled-input" placeholder=" " required />
        </div>

        <div class="start-button-container">
          <button type="submit" class="start-button">التالي</button>
        </div>
      </div>
    </div>
  </form>

</body>

</html>