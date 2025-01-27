<?php
session_start(); // Start the session

include('config.php');

$userID = $_SESSION['userID'];
$roleID = $_SESSION['roleID'];

// Prepare SQL query based on user role
if ($roleID == 1) { // Admin
    $sql = "SELECT c.caseID, c.title, l.city, l.neighborhood, c.caseType, c.date 
            FROM `Case` c 
            JOIN Location l ON c.locationID = l.locationID";
} elseif ($roleID == 2) { // Detective
    $sql = "SELECT c.caseID, c.title, l.city, l.neighborhood, c.caseType, c.date 
            FROM `Case` c 
            JOIN Location l ON c.locationID = l.locationID
            WHERE c.detectiveID = ?";
} else {
    echo "<p>Unauthorized access.</p>";
    exit;
}

// Fetch results
$cases = [];
if ($stmt = $conn->prepare($sql)) {
    if ($roleID == 2) {
        $stmt->bind_param("i", $userID); // Bind the detective ID
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cases[] = $row;
    }

    $stmt->close();
}

// Close connection
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
    <title>الرئيسية</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1e2a38;
            color: #fff;
            margin: 0;
            padding: 0;
            /* Clean layout */
        }

        .case-container {
            display: flex;
            flex-wrap: wrap;
            /* Wrap items to new rows */
            justify-content: center;
            /* Center items in rows */
            gap: 20px;
            /* Spacing between cards */
            width: 100%;
            max-width: 1400px;
            /* Optional: Adjust container width */
            height: 600px;
            /* Fixed height */
            overflow-y: auto;
            /* Enable scrolling */
            overflow-x: hidden;
            /* Prevent horizontal scrolling */
            padding: 20px;
            margin: 0 auto;
            /* Center the container */
            border-radius: 10px;
            position: relative;
            /* Ensure proper stacking */
        }

        .case-container h1 {
            margin: 0;
            /* Remove default margin */
            padding-bottom: 20px;
            /* Space below the title */
            font-size: 2rem;
            /* Adjust font size */
            text-align: center;
            /* Center the text */
            color: #ffcc00;
            /* Match color scheme */
            width: 100%;
            /* Make the title span the full width */
        }


        /* Hide the scrollbar */
        .case-container::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Edge */
        }

        .case-container {
            -ms-overflow-style: none;
            /* Internet Explorer/Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .case-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1 1 calc(33.333% - 20px);
            /* 3 cards per row */
            max-width: 300px;
            min-width: 250px;
            padding: 10px;
            background-color: #2d3e50;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .case-card:hover {
            transform: translateY(-5px);
            /* Slight lift */
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.4);
            /* Shadow on hover */
            background-color: #354a5f;
            /* Lighter background */
        }

        .case-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s ease;
            /* Smooth zoom */
        }

        .case-card:hover img {
            transform: scale(1.03);
            /* Zoom effect */
        }

        .case-card h5 {
            margin: 10px 0;
            font-size: 1.25rem;
            color: #ffcc00;
        }

        .case-card p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #d3d3d3;
        }

        /* Remove link styles */
        .case-card-link {
            text-decoration: none;
            /* No underline */
            color: inherit;
            /* Inherit text color */
            display: block;
            /* Make link wrap around the card */
        }

        .case-card-link:focus,
        .case-card-link:active {
            outline: none;
            /* Remove blue focus outline */
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
                <i class="fas fa-user-circle"></i> <!-- أيقونة مستخدم من Font Awesome -->
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
            <li><a href="index.php"><i class="fa fa-home"></i> الرئيسية</a></li>
            <li><a href="add_detective.php"><i class="fa fa-search"></i>إضافة محقق</a></li>
            <li><a href="archive.php" class="active"><i class="fa fa-archive"></i> قسم الإدارة</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> تسجيل الخروج</a></li>
        </ul>
    </nav>

    <div class="case-container">
        <h1>القضايا</h1>
        <?php if (!empty($cases)): ?>
            <?php foreach ($cases as $case): ?>
                <a href="case_details.php?id=<?php echo urlencode($case['caseID']); ?>" class="case-card-link">
                    <div class="case-card">
                        <?php
                        $caseImages = [
                            "جريمة ضد الاشخاص" => "public/img/جريمة ضد الاشخاص.jpg",
                            "جريمة ضد الممتلكات" => "public/img/جريمة ضد الممتلكات.jpg",
                            "جريمة ضد الاشخاص و الممتلكات" => "public/img/جريمة ضد الاشخاص و الممتلكات.jpg",
                        ];
                        $imagePath = $caseImages[$case['caseType']] ?? "";
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($case['caseType']); ?>">
                        <h5><?php echo htmlspecialchars($case['title']); ?></h5>
                        <p>
                            <i class="fas fa-map-marker-alt location-icon"></i>
                            <?php echo htmlspecialchars($case['city'] . ', ' . $case['neighborhood']); ?>
                        </p>
                        <p><strong>نوع القضية:</strong> <?php echo htmlspecialchars($case['caseType']); ?></p>
                        <p><strong>التاريخ:</strong> <?php echo htmlspecialchars($case['date']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No cases found.</p>
        <?php endif; ?>
    </div>

</body>

</html>
?>