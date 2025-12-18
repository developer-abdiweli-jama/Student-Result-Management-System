-- sql/sample_students.sql
-- Sample student data: 10 students per class (Grade 5-8, Form 1-4) = 80 total
-- All with authentic Somali names
-- Note: year_of_study is kept 1-4 for compatibility, actual class is in class_level
-- Grade 5 Students (year_of_study = 1 for primary grades)
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM25001',
        'Faadumo Ahmed Yusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25002',
        'Cabdi Maxamed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25003',
        'Halima Cali Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25004',
        'Mahad Axmed Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25005',
        'Sahra Maxamuud Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25006',
        'Xasan Cismaan Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25007',
        'Nimco Cabdullahi Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25008',
        'Yuusuf Cabdi Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25009',
        'Hodan Ismaaciil Ahmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    ),
    (
        'SRM25010',
        'Maxamed Xasan Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Grade 5'
    );
-- Grade 6 Students  
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM25011',
        'Asli Maxamuud Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25012',
        'Cabdiraxmaan Axmed Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25013',
        'Maryam Yuusuf Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25014',
        'Cismaan Cabdi Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25015',
        'Khadija Ahmed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25016',
        'Siciid Maxamed Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25017',
        'Zamzam Cali Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25018',
        'Ahmed Xasan Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25019',
        'Fowsiya Cabdullahi Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    ),
    (
        'SRM25020',
        'Bashiir Yuusuf Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Grade 6'
    );
-- Grade 7 Students  
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM25021',
        'Ubax Maxamed Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25022',
        'Jamaal Ahmed Maxamuud',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25023',
        'Deqa Cali Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25024',
        'Cabdifataax Xasan Ahmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25025',
        'Aamina Axmed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25026',
        'Cabdilaahi Maxamed Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25027',
        'Ifrah Yuusuf Maxamuud',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25028',
        'Maxamuud Cabdullahi Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25029',
        'Hibo Ahmed Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    ),
    (
        'SRM25030',
        'Cali Xasan Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 7'
    );
-- Grade 8 Students
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM25031',
        'Cawo Cabdi Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25032',
        'Faysal Maxamed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25033',
        'Sucad Ahmed Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25034',
        'Daahir Yuusuf Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25035',
        'Nadifo Xasan Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25036',
        'Cabdirashiid Cali Ahmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25037',
        'Isnino Maxamuud Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25038',
        'Axmed Cabdullahi Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25039',
        'Filsan Maxamed Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    ),
    (
        'SRM25040',
        'Cabdiqaadir Yuusuf Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 8'
    );
-- Form 1 Students
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM25041',
        'Shukri Hassan Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25042',
        'Cabdiaziz Ahmed Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25043',
        'Warsan Cali Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25044',
        'Cabdiwali Maxamed Xasan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25045',
        'Ladan Axmed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25046',
        'Faarax Yuusuf Maxamuud',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25047',
        'Idil Cabdullahi Ahmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25048',
        'Jaamac Cali Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25049',
        'Kinsi Maxamed Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    ),
    (
        'SRM25050',
        'Cumar Xasan Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        1,
        '2024/2025',
        'Form 1'
    );
-- Form 2 Students
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM25051',
        'Saado Ahmed Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25052',
        'Cabdiraxman Yuusuf Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25053',
        'Xalima Cabdi Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25054',
        'Saalim Maxamed Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25055',
        'Luul Xasan Ahmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25056',
        'Cabdisalaan Cali Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25057',
        'Sagal Maxamuud Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25058',
        'Xuseen Ahmed Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25059',
        'Barni Yuusuf Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    ),
    (
        'SRM25060',
        'Cabdullahi Cabdullahi Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        2,
        '2023/2024',
        'Form 2'
    );
-- Form 3 Students
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level,
        stream
    )
VALUES (
        'SRM25061',
        'Maryan Hassan Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'Science'
    ),
    (
        'SRM25062',
        'Cabdisamad Maxamed Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'Arts'
    ),
    (
        'SRM25063',
        'Dahabo Cali Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'General'
    ),
    (
        'SRM25064',
        'Maxamuud Ahmed Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'Science'
    ),
    (
        'SRM25065',
        'Batulo Yuusuf Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'Arts'
    ),
    (
        'SRM25066',
        'Cabdiraxiim Xasan Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'General'
    ),
    (
        'SRM25067',
        'Caaliyah Cabdullahi Ahmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'Science'
    ),
    (
        'SRM25068',
        'Mursal Maxamuud Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'Arts'
    ),
    (
        'SRM25069',
        'Shafici Ahmed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'General'
    ),
    (
        'SRM25070',
        'Nuur Cali Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 3',
        'Science'
    );
-- Form 4 Students
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level,
        stream
    )
VALUES (
        'SRM25071',
        'Xaawa Yuusuf Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'Science'
    ),
    (
        'SRM25072',
        'Cabdikariim Maxamed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'Arts'
    ),
    (
        'SRM25073',
        'Samira Ahmed Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'General'
    ),
    (
        'SRM25074',
        'Abshir Xasan Yuusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'Science'
    ),
    (
        'SRM25075',
        'Ardo Cabdullahi Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'Arts'
    ),
    (
        'SRM25076',
        'Cabdulqaadir Cali Ahmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'General'
    ),
    (
        'SRM25077',
        'Haboon Maxamuud Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'Science'
    ),
    (
        'SRM25078',
        'Liibaan Ahmed Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'Arts'
    ),
    (
        'SRM25079',
        'Nasteexo Yuusuf Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'General'
    ),
    (
        'SRM25080',
        'Cali Maxamed Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 4',
        'Science'
    );
-- ================================================================
-- RETAINED STUDENTS (Failed with GPA < 1.5, repeating their grade)
-- These students have been in the same grade for 2 years
-- ================================================================
-- Retained Grade 6 Student (was Grade 6 in 2023/2024, still Grade 6 in 2024/2025)
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM24081',
        'Cabdirashiid Maxamed Yusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Grade 6'
    );
-- Retained Grade 7 Student (was Grade 7 in 2023/2024, still Grade 7 in 2024/2025)
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM24082',
        'Farxiya Cali Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Grade 7'
    );
-- Retained Grade 8 Student (was Grade 8 in 2023/2024, still Grade 8 in 2024/2025)
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM24083',
        'Mustafe Ahmed Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        5,
        '2020/2021',
        'Grade 8'
    );
-- Retained Form 2 Student (was Form 2 in 2023/2024, still Form 2 in 2024/2025)
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM24084',
        'Saciido Xasan Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        3,
        '2022/2023',
        'Form 2'
    );
-- Retained Form 3 Student (was Form 3 in 2023/2024, still Form 3 in 2024/2025)
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM24085',
        'Cabdiqani Yuusuf Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2021/2022',
        'Form 3'
    );
-- Retained Form 4 Student (was Form 4 in 2023/2024, still Form 4 in 2024/2025)
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level
    )
VALUES (
        'SRM24086',
        'Xamdi Cabdullahi Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        5,
        '2020/2021',
        'Form 4'
    );
-- Graduated Students (Completed Form 4)
INSERT IGNORE INTO students (
        reg_no,
        name,
        password_hash,
        year_of_study,
        admission_year,
        class_level,
        stream
    )
VALUES (
        'SRM23091',
        'Axmed Cali Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'Science'
    ),
    (
        'SRM23092',
        'Hibo Xasan Yusuf',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'Arts'
    ),
    (
        'SRM23093',
        'Cabdiaziz Yuusuf Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'General'
    ),
    (
        'SRM23094',
        'Maryama Maxamuud Axmed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'Science'
    ),
    (
        'SRM23095',
        'Maxamed Ahmed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'Arts'
    ),
    (
        'SRM23096',
        'Farxiya Cali Cabdi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'General'
    ),
    (
        'SRM23097',
        'Cabdilaahi Yuusuf Maxamed',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'Science'
    ),
    (
        'SRM23098',
        'Sahra Maxamed Cali',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'Arts'
    ),
    (
        'SRM23099',
        'Ismaaciil Axmed Hassan',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'General'
    ),
    (
        'SRM23100',
        'Zamzam Yusuf Maxamuud',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        4,
        '2019/2020',
        'Graduated',
        'Science'
    );