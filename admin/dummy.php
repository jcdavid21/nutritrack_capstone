<?php

    include_once "./backend/config.php";
    $data = [
        [
            "username" => "admin_maria",
            "password" => "admin123",
            "status" => "Active",
            "role_id" => 2
        ],
        [
            "username" => "bhw_juan",
            "password" => "admin123",
            "status" => "Active",
            "role_id" => 3
        ],
        [
            "username" => "bhw_ana",
            "password" => "admin123",
            "status" => "Active",
            "role_id" => 3
        ],
        [
            "username" => "user_pedro",
            "password" => "admin123",
            "status" => "Active",
            "role_id" => 1
        ],
        [
            "username" => "bhw_rosa",
            "password" => "admin123",
            "status" => "Active",
            "role_id" => 3
        ],
        [
            "username" => "user_linda",
            "password" => "admin123",
            "status" => "Active",
            "role_id" => 1
        ],
        [
            "username" => "bhw_carlos",
            "password" => "admin123",
            "status" => "Active",
            "role_id" => 3
        ],
        [
            "username" => "user_sofia",
            "password" => "admin123",
            "status" => "Active",
            "role_id" => 1
        ],
    ];

    foreach ($data as $user) {
        $username = $user['username'];
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        $status = $user['status'];
        $role_id = $user['role_id'];

        $query = "INSERT INTO tbl_user (username, password, status, role_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $username, $hashed_password, $status, $role_id);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    

?>