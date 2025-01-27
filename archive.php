<?php
include('config.php');


$currentUserID = 2;         // $_SESSION['userID']; اخذها من تسجيل الدخول
$currentRole = 'admin';     // $_SESSION['role'];   اخذها من تسجيل الدخول

// ------------------------------- Case Table -------------------------------
if ($currentRole == 'admin') {
    // If the user is 'admin', display all cases.
    $sql_cases = "SELECT `caseID`, `adminID`, `detectiveID`, `title`, `locationID`, `date`, `time`, `additionalInfo`, `status`, `criminal`, `victim`, `caseType` FROM `case`";
} else {
    // If the user is 'detective', only display his cases.
    $sql_cases = "SELECT `caseID`, `adminID`, `detectiveID`, `title`, `locationID`, `date`, `time`, `additionalInfo`, `status`, `criminal`, `victim`, `caseType` 
                  FROM `case` 
                  WHERE `detectiveID` = $currentUserID";
}
$result_cases = $conn->query($sql_cases);

// ----------------------------- Detectives Table -----------------------------
$sql_detectives = "SELECT userID, firstName, middleName, lastName, email FROM user WHERE roleID = 1";
$result_detectives = $conn->query($sql_detectives);


// ----------------------------- Delete Detectives -----------------------------
if (isset($_GET['delete_detective_id'])) {
    $delete_detective_id = intval($_GET['delete_detective_id']);

    $delete_cases_sql = "DELETE FROM `Case` WHERE `detectiveID` = ?";
    $delete_cases_stmt = $conn->prepare($delete_cases_sql);
    $delete_cases_stmt->bind_param("i", $delete_detective_id);
    $delete_cases_stmt->execute();
    $delete_cases_stmt->close();

    $delete_evidence_sql = "DELETE FROM `Evidence` WHERE `caseID` IN (SELECT caseID FROM `Case` WHERE `detectiveID` = ?)";
    $delete_evidence_stmt = $conn->prepare($delete_evidence_sql);
    $delete_evidence_stmt->bind_param("i", $delete_detective_id);
    $delete_evidence_stmt->execute();
    $delete_evidence_stmt->close();

    $delete_user_sql = "DELETE FROM `User` WHERE `userID` = ? AND `roleID` = 1"; // the user is detictive
    $delete_user_stmt = $conn->prepare($delete_user_sql);
    $delete_user_stmt->bind_param("i", $delete_detective_id);
    $delete_user_stmt->execute();

    if ($delete_user_stmt->affected_rows > 0) {
        echo "تم حذف المحقق بنجاح.";
    } else {
        echo "حدث خطأ أثناء حذف المحقق.";
    }

    // close the connection
    $delete_user_stmt->close();
    $conn->close();

    // يرجع لصفحة الارشيف بعد الحذف
    header("Location: archive.php");
    exit();
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/fontAwesome.css">
    <link rel="stylesheet" href="css/light-box.css">
    <link rel="stylesheet" href="public/css/styles.css">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;600&display=swap" rel="stylesheet">
    <title>قسم الإدارة</title>

    <style>
        body {
            overflow-y: scroll;
        }

        #search-Case {
            background-color: whitesmoke;
            /* Add a search icon to input */
            background-position: 10px 12px;
            /* Position the search icon */
            background-repeat: no-repeat;
            /* Do not repeat the icon image */
            /* width: 100%; */
            /* Full-width */
            font-size: 16px;
            /* Increase font-size */
            padding: 12px 20px 12px 40px;
            /* Add some padding */
            border: none;
            /* Add a grey border */
            border-radius: 1rem;
            margin-bottom: 12px;
            /* Add some space below the input */
            font-family: 'Almarai', sans-serif !important;
        }

        .searchBox {
            background-color: whitesmoke;
            /* Add a search icon to input */
            background-position: 10px 12px;
            /* Position the search icon */
            background-repeat: no-repeat;
            /* Do not repeat the icon image */
            /* width: 100%; */
            /* Full-width */
            font-size: 16px;
            /* Increase font-size */
            padding: 12px 20px 12px 40px;
            /* Add some padding */
            border: none;
            /* Add a grey border */
            border-radius: 1rem;
            margin-bottom: 12px;
            /* Add some space below the input */
            font-family: 'Almarai', sans-serif !important;
        }

        #search-Detective {
            background-color: whitesmoke;
            /* Add a search icon to input */
            background-position: 10px 12px;
            /* Position the search icon */
            background-repeat: no-repeat;
            /* Do not repeat the icon image */
            /* width: 100%; */
            /* Full-width */
            font-size: 16px;
            /* Increase font-size */
            padding: 12px 20px 12px 40px;
            /* Add some padding */
            border: none;
            /* Add a grey border */
            border-radius: 1rem;
            margin-bottom: 12px;
            /* Add some space below the input */
            font-family: 'Almarai', sans-serif !important;
        }

        tbody,
        td,
        tfoot,
        th,
        thead,
        tr {
            font-family: 'Almarai';
        }

        th:hover {
            cursor: pointer;
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
            <li><a href="{{ route('index') }}"><i class="fa fa-home"></i> الرئيسية</a></li>
            <li><a href="{{ route('currentCase') }}"><i class="fa fa-search"></i>إضافة محقق</a></li>
            <li><a href="{{ route(name: 'archive') }}" class="active"><i class="fa fa-archive"></i> قسم الإدارة</a></li>
            <li><a href="{{ route(name: 'logout') }}"><i class="fa fa-sign-out"></i> تسجيل الخروج</a></li>
        </ul>
    </nav>

    <!-- ارشيف القضايا -->
    <section style="margin: 5%; padding-right: 15%; padding-left: 5%;">
        <div>
            <h1 class="field-label" style="text-align: center; font-size: 36px;">أرشيف القضايا:</h1>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <!-- <input type="text" id="search-Case" onkeyup="myFunction1()" placeholder="ابحث عن قضية.."> -->
            <input type="text" id="search-Case" onkeyup="searchTable('search-Case', 'case-Table', 1)" placeholder="ابحث عن قضية..">
            <button onclick="" class="start-button" style="background-color: #565f6c; color:#ddd; height: 100%; margin-bottom: 12px; font-size: 16px;"><i class="fa fa-plus-square" aria-hidden="true"></i>&nbsp;إضافة قضية</button>
        </div>
        <table id="case-Table" class="table table-dark table-hover" style="text-align: center;">
            <thead>
                <tr>
                    <th scope="col" onclick="sortTable('case-Table', 0)">#</th>
                    <th scope="col" onclick="sortTable('case-Table', 1)">عنوان القضية</th>
                    <th scope="col" onclick="sortTable('case-Table', 2)">المحقق</th>
                    <th scope="col" onclick="sortTable('case-Table', 3)">الموقع</th>
                    <th scope="col" onclick="sortTable('case-Table', 4)">التاريخ والوقت</th>
                    <th scope="col" style="cursor:text;">الحالة</th>
                    <th scope="col" style="cursor:text;">التعديل/الحذف</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_cases->num_rows > 0) {
                    $index = 1;
                    while ($row = $result_cases->fetch_assoc()) {
                        // استعلام لجلب اسم المحقق من جدول المستخدمين باستخدام detectiveID
                        $detectiveID = $row['detectiveID'];
                        $sql_detective = "SELECT firstName, middleName, lastName FROM user WHERE userID = $detectiveID";
                        $result_detective = $conn->query($sql_detective);
                        $detectiveName = "غير محدد"; // إذا لم يتم العثور على المحقق
                        if ($result_detective->num_rows > 0) {
                            $detectiveData = $result_detective->fetch_assoc();
                            $detectiveName = $detectiveData['firstName'] . " " . $detectiveData['middleName'] . " " . $detectiveData['lastName'];
                        }

                        // استعلام لجلب تفاصيل الموقع من جدول المواقع باستخدام locationID
                        $locationID = $row['locationID'];
                        $sql_location = "SELECT city, neighborhood, street, postalCode FROM location WHERE locationID = $locationID";
                        $result_location = $conn->query($sql_location);
                        $locationDetails = "غير محدد"; // إذا لم يتم العثور على الموقع
                        if ($result_location->num_rows > 0) {
                            $locationData = $result_location->fetch_assoc();
                            $locationDetails = $locationData['city'] . ", " . $locationData['neighborhood'] . ", " . $locationData['street'] . ", " . $locationData['postalCode'];
                        }

                        echo "<tr>";
                        echo "<td>" . $index++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($detectiveName) . "</td>";
                        echo "<td>" . htmlspecialchars($locationDetails) . "</td>";
                        echo "<td>" . htmlspecialchars($row['date']) . " " . htmlspecialchars($row['time']) . "</td>";
                        echo "<td class='btn btn-";
                        if (htmlspecialchars($row['status']) == "مفتوحة") {
                            echo "success";
                        } else {
                            echo "danger";
                        }
                        echo "'>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>
                        <button class='btn btn-outline-warning btn-sm' onclick='editCase(" . $row['caseID'] . ")'><i class='fa fa-pencil'></i></button>
                        <button class='btn btn-outline-danger btn-sm' onclick='deleteCase(" . $row["caseID"] . ")'><i class='fa fa-trash'></i></button>
                      </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>لا توجد بيانات لعرضها.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <!-- أرشيف المحققين -->
    <section style="margin: 5%; padding-right: 15%; padding-left: 5%;">
        <div>
            <h1 class="field-label" style="text-align: center; font-size: 36px;">أرشيف المحققين:</h1>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <!-- <input type="text" id="search-Detective" onkeyup="myFunction2()" placeholder="ابحث عن محقق.."> -->
            <input type="text" id="search-Detective" onkeyup="searchTable('search-Detective', 'detectives-Table', 1)" placeholder="ابحث عن محقق..">
            <button onclick="" class="start-button" style="background-color: #565f6c; color:#ddd; height: 100%; margin-bottom: 12px; font-size: 16px;"><i class="fa fa-plus-square" aria-hidden="true"></i>&nbsp;إضافة محقق</button>
        </div>
        <table id="detectives-Table" class="table table-dark table-hover" style="text-align: center;">
            <thead>
                <tr>
                    <th scope="col" onclick="sortTable('detectives-Table', 0)">#</th>
                    <th scope="col" onclick="sortTable('detectives-Table', 1)">اسم المحقق</th>
                    <th scope="col" onclick="sortTable('detectives-Table', 2)">الإيميل</th>
                    <th scope="col" style="cursor:text;">التعديل/الحذف</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_detectives->num_rows > 0) {
                    $index = 1;
                    while ($row = $result_detectives->fetch_assoc()) {
                        $fullName = $row['firstName'] . " " . $row['middleName'] . " " . $row['lastName'];
                        echo "<tr>";
                        echo "<td>" . $index++ . "</td>";
                        echo "<td>" . htmlspecialchars($fullName) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>
                                <button class='btn btn-outline-warning btn-sm' onclick='editDetective(" . $row['userID'] . ")'><i class='fa fa-pencil'></i></button>
                                <button class='btn btn-outline-danger btn-sm' onclick='deleteDetective(" . $row["userID"] . ", \"" . addslashes($fullName) . "\")'><i class='fa fa-trash'></i></button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>لا توجد بيانات لعرضها.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <script>
        // New (تقارن دخول الحروف بالترتيب من البداية)
        function searchTable(inputId, tableId, columnIndex) {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById(inputId);
            filter = input.value.toUpperCase();
            table = document.getElementById(tableId);
            tr = table.getElementsByTagName("tr");

            // Create a regular expression to match the beginning of each word
            var regex = new RegExp("^" + filter, "i");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[columnIndex];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    // Match the regular expression
                    if (regex.test(txtValue)) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        // Old (تقارن اي حرف موجود حتى لو في النص)
        // function searchTable(inputId, tableId, columnIndex) {
        //     var input, filter, table, tr, td, i, txtValue;
        //     input = document.getElementById(inputId);
        //     filter = input.value.toUpperCase();
        //     table = document.getElementById(tableId);
        //     tr = table.getElementsByTagName("tr");

        //     // Loop through all table rows, and hide those who don't match the search query
        //     for (i = 0; i < tr.length; i++) {
        //         td = tr[i].getElementsByTagName("td")[columnIndex];
        //         if (td) {
        //             txtValue = td.textContent || td.innerText;
        //             if (txtValue.toUpperCase().indexOf(filter) > -1) {
        //                 tr[i].style.display = "";
        //             } else {
        //                 tr[i].style.display = "none";
        //             }
        //         }
        //     }
        // }
    </script>

    <!-- sort -->
    <script>
        function sortTable(tableId, n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById(tableId); // استخدام معرف الجدول المعطى
            switching = true;
            dir = "asc"; // تعيين الاتجاه الافتراضي للترتيب إلى تصاعدي

            while (switching) {
                switching = false;
                rows = table.rows;

                // المرور عبر جميع الصفوف
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];

                    // مقارنة النصوص في الصفوف بناءً على الاتجاه المحدد
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }

                // إذا كان يجب التبديل، نفذ التبديل
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    // إذا لم يكن هناك تبديل، قم بتغيير الاتجاه إلى تنازلي
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>

    <!-- delete & edit -->
    <script>
        function editCase(id) {
            // يروح لصفحة التعديل مع الآي دي
            window.location.href = `editDetective.php?id=${id}`;
        }

        function deleteCase(caseID, caseTitle) {
            if (confirm("هل أنت متأكد من حذف القضية " + caseTitle + "؟")) {
                // اذا قال اوكي، يروح لصفحة الحذف مع الآي دي
                window.location.href = "archive.php?delete_case_id=" + caseID;
            }
        }

        function editDetective(id) {
            // يروح لصفحة التعديل مع الآي دي
            window.location.href = `editDetective.php?id=${id}`;
        }

        function deleteDetective(userID, userName) {
            if (confirm("هل أنت متأكد من حذف المحقق " + userName + "؟")) {
                // اذا قال اوكي، يروح لصفحة الحذف مع الآي دي
                window.location.href = "archive.php?delete_detective_id=" + userID;
            }
        }
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/vendor/bootstrap.min.js"></script>
    <script src="js/datepicker.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
</body>

</html>