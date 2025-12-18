<?php
// helpers/insert_all_students.php
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

// Delete existing sample students first
echo "Clearing existing sample students...\n";
$conn->query("DELETE FROM students WHERE reg_no LIKE 'SRM25%'");

// Sample students data
$students = [
    // Grade 5
    ['SRM25001', 'Faadumo Ahmed Yusuf', 1, '2024/2025', 'Grade 5'],
    ['SRM25002', 'Cabdi Maxamed Hassan', 1, '2024/2025', 'Grade 5'],
    ['SRM25003', 'Halima Cali Yuusuf', 1, '2024/2025', 'Grade 5'],
    ['SRM25004', 'Mahad Axmed Maxamed', 1, '2024/2025', 'Grade 5'],
    ['SRM25005', 'Sahra Maxamuud Cabdi', 1, '2024/2025', 'Grade 5'],
    ['SRM25006', 'Xasan Cismaan Axmed', 1, '2024/2025', 'Grade 5'],
    ['SRM25007', 'Nimco Cabdullahi Yuusuf', 1, '2024/2025', 'Grade 5'],
    ['SRM25008', 'Yuusuf Cabdi Maxamed', 1, '2024/2025', 'Grade 5'],
    ['SRM25009', 'Hodan Ismaaciil Ahmed', 1, '2024/2025', 'Grade 5'],
    ['SRM25010', 'Maxamed Xasan Cali', 1, '2024/2025', 'Grade 5'],
    
    // Grade 6
    ['SRM25011', 'Asli Maxamuud Hassan', 2, '2023/2024', 'Grade 6'],
    ['SRM25012', 'Cabdiraxmaan Axmed Cali', 2, '2023/2024', 'Grade 6'],
    ['SRM25013', 'Maryam Yuusuf Maxamed', 2, '2023/2024', 'Grade 6'],
    ['SRM25014', 'Cismaan Cabdi Yuusuf', 2, '2023/2024', 'Grade 6'],
    ['SRM25015', 'Khadija Ahmed Hassan', 2, '2023/2024', 'Grade 6'],
    ['SRM25016', 'Siciid Maxamed Axmed', 2, '2023/2024', 'Grade 6'],
    ['SRM25017', 'Zamzam Cali Cabdi', 2, '2023/2024', 'Grade 6'],
    ['SRM25018', 'Ahmed Xasan Maxamed', 2, '2023/2024', 'Grade 6'],
    ['SRM25019', 'Fowsiya Cabdullahi Axmed', 2, '2023/2024', 'Grade 6'],
    ['SRM25020', 'Bashiir Yuusuf Hassan', 2, '2023/2024', 'Grade 6'],
    
    // Grade 7
    ['SRM25021', 'Ubax Maxamed Cali', 3, '2022/2023', 'Grade 7'],
    ['SRM25022', 'Jamaal Ahmed Maxamuud', 3, '2022/2023', 'Grade 7'],
    ['SRM25023', 'Deqa Cali Yuusuf', 3, '2022/2023', 'Grade 7'],
    ['SRM25024', 'Cabdifataax Xasan Ahmed', 3, '2022/2023', 'Grade 7'],
    ['SRM25025', 'Aamina Axmed Hassan', 3, '2022/2023', 'Grade 7'],
    ['SRM25026', 'Cabdilaahi Maxamed Cabdi', 3, '2022/2023', 'Grade 7'],
    ['SRM25027', 'Ifrah Yuusuf Maxamuud', 3, '2022/2023', 'Grade 7'],
    ['SRM25028', 'Maxamuud Cabdullahi Cali', 3, '2022/2023', 'Grade 7'],
    ['SRM25029', 'Hibo Ahmed Yuusuf', 3, '2022/2023', 'Grade 7'],
    ['SRM25030', 'Cali Xasan Axmed', 3, '2022/2023', 'Grade 7'],
    
    // Grade 8
    ['SRM25031', 'Cawo Cabdi Maxamed', 4, '2021/2022', 'Grade 8'],
    ['SRM25032', 'Faysal Maxamed Hassan', 4, '2021/2022', 'Grade 8'],
    ['SRM25033', 'Sucad Ahmed Cali', 4, '2021/2022', 'Grade 8'],
    ['SRM25034', 'Daahir Yuusuf Cabdi', 4, '2021/2022', 'Grade 8'],
    ['SRM25035', 'Nadifo Xasan Maxamed', 4, '2021/2022', 'Grade 8'],
    ['SRM25036', 'Cabdirashiid Cali Ahmed', 4, '2021/2022', 'Grade 8'],
    ['SRM25037', 'Isnino Maxamuud Yuusuf', 4, '2021/2022', 'Grade 8'],
    ['SRM25038', 'Axmed Cabdullahi Hassan', 4, '2021/2022', 'Grade 8'],
    ['SRM25039', 'Filsan Maxamed Axmed', 4, '2021/2022', 'Grade 8'],
    ['SRM25040', 'Cabdiqaadir Yuusuf Cali', 4, '2021/2022', 'Grade 8'],
    
    // Form 1
    ['SRM25041', 'Shukri Hassan Maxamed', 1, '2024/2025', 'Form 1'],
    ['SRM25042', 'Cabdiaziz Ahmed Cabdi', 1, '2024/2025', 'Form 1'],
    ['SRM25043', 'Warsan Cali Yuusuf', 1, '2024/2025', 'Form 1'],
    ['SRM25044', 'Cabdiwali Maxamed Xasan', 1, '2024/2025', 'Form 1'],
    ['SRM25045', 'Ladan Axmed Hassan', 1, '2024/2025', 'Form 1'],
    ['SRM25046', 'Faarax Yuusuf Maxamuud', 1, '2024/2025', 'Form 1'],
    ['SRM25047', 'Idil Cabdullahi Ahmed', 1, '2024/2025', 'Form 1'],
    ['SRM25048', 'Jaamac Cali Cabdi', 1, '2024/2025', 'Form 1'],
    ['SRM25049', 'Kinsi Maxamed Yuusuf', 1, '2024/2025', 'Form 1'],
    ['SRM25050', 'Cumar Xasan Cali', 1, '2024/2025', 'Form 1'],
    
    // Form 2
    ['SRM25051', 'Saado Ahmed Maxamed', 2, '2023/2024', 'Form 2'],
    ['SRM25052', 'Cabdiraxman Yuusuf Hassan', 2, '2023/2024', 'Form 2'],
    ['SRM25053', 'Xalima Cabdi Axmed', 2, '2023/2024', 'Form 2'],
    ['SRM25054', 'Saalim Maxamed Cali', 2, '2023/2024', 'Form 2'],
    ['SRM25055', 'Luul Xasan Ahmed', 2, '2023/2024', 'Form 2'],
    ['SRM25056', 'Cabdisalaan Cali Yuusuf', 2, '2023/2024', 'Form 2'],
    ['SRM25057', 'Sagal Maxamuud Hassan', 2, '2023/2024', 'Form 2'],
    ['SRM25058', 'Xuseen Ahmed Cabdi', 2, '2023/2024', 'Form 2'],
    ['SRM25059', 'Barni Yuusuf Maxamed', 2, '2023/2024', 'Form 2'],
    ['SRM25060', 'Cabdullahi Cabdullahi Cali', 2, '2023/2024', 'Form 2'],
    
    // Form 3
    ['SRM25061', 'Maryan Hassan Yuusuf', 3, '2022/2023', 'Form 3'],
    ['SRM25062', 'Cabdisamad Maxamed Axmed', 3, '2022/2023', 'Form 3'],
    ['SRM25063', 'Dahabo Cali Hassan', 3, '2022/2023', 'Form 3'],
    ['SRM25064', 'Maxamuud Ahmed Cabdi', 3, '2022/2023', 'Form 3'],
    ['SRM25065', 'Batulo Yuusuf Maxamed', 3, '2022/2023', 'Form 3'],
    ['SRM25066', 'Cabdiraxiim Xasan Cali', 3, '2022/2023', 'Form 3'],
    ['SRM25067', 'Caaliyah Cabdullahi Ahmed', 3, '2022/2023', 'Form 3'],
    ['SRM25068', 'Mursal Maxamuud Yuusuf', 3, '2022/2023', 'Form 3'],
    ['SRM25069', 'Shafici Ahmed Hassan', 3, '2022/2023', 'Form 3'],
    ['SRM25070', 'Nuur Cali Maxamed', 3, '2022/2023', 'Form 3'],
    
    // Form 4
    ['SRM25071', 'Xaawa Yuusuf Cabdi', 4, '2021/2022', 'Form 4'],
    ['SRM25072', 'Cabdikariim Maxamed Hassan', 4, '2021/2022', 'Form 4'],
    ['SRM25073', 'Samira Ahmed Cali', 4, '2021/2022', 'Form 4'],
    ['SRM25074', 'Abshir Xasan Yuusuf', 4, '2021/2022', 'Form 4'],
    ['SRM25075', 'Ardo Cabdullahi Maxamed', 4, '2021/2022', 'Form 4'],
    ['SRM25076', 'Cabdulqaadir Cali Ahmed', 4, '2021/2022', 'Form 4'],
    ['SRM25077', 'Haboon Maxamuud Hassan', 4, '2021/2022', 'Form 4'],
    ['SRM25078', 'Liibaan Ahmed Cabdi', 4, '2021/2022', 'Form 4'],
    ['SRM25079', 'Nasteexo Yuusuf Axmed', 4, '2021/2022', 'Form 4'],
    ['SRM25080', 'Cali Maxamed Cali', 4, '2021/2022', 'Form 4'],
];

$password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password: "password"

$stmt = $conn->prepare("INSERT INTO students (reg_no, name, password_hash, year_of_study, admission_year, class_level) VALUES (?, ?, ?, ?, ?, ?)");

$inserted = 0;
foreach ($students as $student) {
    $stmt->bind_param("sssiss", $student[0], $student[1], $password_hash, $student[2], $student[3], $student[4]);
    if ($stmt->execute()) {
        $inserted++;
        if ($inserted % 10 == 0) {
            echo "Inserted $inserted students...\n";
        }
    } else {
        echo "Error inserting {$student[0]}: " . $conn->error . "\n";
    }
}

$stmt->close();

echo "\n✓ Successfully inserted $inserted students!\n\n";

// Verification
echo "Verification:\n";
echo str_repeat("=", 40) . "\n";
$result = $conn->query("SELECT class_level, COUNT(*) as count FROM students WHERE reg_no LIKE 'SRM25%' GROUP BY class_level ORDER BY class_level");
while ($row = $result->fetch_assoc()) {
    echo $row['class_level'] . ": " . $row['count'] . " students\n";
}
echo str_repeat("=", 40) . "\n";

$conn->close();
?>
