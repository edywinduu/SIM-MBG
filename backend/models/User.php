<?php
/**
 * SIM-MBG — User Model
 * Menangani interaksi dan kueri database untuk data pengguna, profil siswa, dan sekolah.
 * Digunakan oleh AuthController dan komponen MVC lainnya.
 */

class User {
    /**
     * @var mysqli|PDO
     */
    private $db;

    /**
     * Inisialisasi User Model dengan koneksi database
     *
     * @param mysqli|PDO $db
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Mencari pengguna berdasarkan username, email, atau NIS/NISN (via relasi tabel students)
     * Mengembalikan data lengkap pengguna beserta relasi profil siswa dan sekolah.
     *
     * @param string $identifier
     * @return array|null
     */
    public function findByUsername($identifier) {
        if (!$this->db) {
            return null;
        }

        $query = "SELECT u.id, u.username, u.email, u.password_hash, u.full_name, u.role, u.status,
                         s.id AS student_id, s.nisn, s.class_name, s.recipient_status, s.profile_picture,
                         sc.id AS school_id, sc.name AS school_name
                  FROM users u
                  LEFT JOIN students s ON s.user_id = u.id
                  LEFT JOIN schools sc ON sc.id = s.school_id
                  WHERE u.username = ? OR u.email = ? OR s.nisn = ?
                  LIMIT 1";

        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return null;
            }
            $stmt->bind_param("sss", $identifier, $identifier, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $user;
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$identifier, $identifier, $identifier]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        return null;
    }

    /**
     * Mencari pengguna berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        if (!$this->db) {
            return null;
        }

        $query = "SELECT u.id, u.username, u.email, u.password_hash, u.full_name, u.role, u.status,
                         s.id AS student_id, s.nisn, s.class_name, s.recipient_status, s.profile_picture,
                         sc.id AS school_id, sc.name AS school_name
                  FROM users u
                  LEFT JOIN students s ON s.user_id = u.id
                  LEFT JOIN schools sc ON sc.id = s.school_id
                  WHERE u.id = ?
                  LIMIT 1";

        $idInt = (int)$id;

        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return null;
            }
            $stmt->bind_param("i", $idInt);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $user;
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$idInt]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        return null;
    }

    /**
     * Memeriksa apakah nama pengguna (username) sudah terdaftar
     *
     * @param string $username
     * @return bool
     */
    public function usernameExists($username) {
        if (!$this->db) {
            return false;
        }

        $query = "SELECT id FROM users WHERE username = ? LIMIT 1";

        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();
            return $exists;
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$username]);
            return (bool)$stmt->fetchColumn();
        }

        return false;
    }

    /**
     * Memeriksa apakah alamat email sudah terdaftar
     *
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        if (!$this->db) {
            return false;
        }

        $query = "SELECT id FROM users WHERE email = ? LIMIT 1";

        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();
            return $exists;
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email]);
            return (bool)$stmt->fetchColumn();
        }

        return false;
    }

    /**
     * Memeriksa apakah Nomor Induk Siswa (NIS/NISN) sudah terdaftar
     *
     * @param string $nisn
     * @return bool
     */
    public function nisnExists($nisn) {
        if (!$this->db) {
            return false;
        }

        $query = "SELECT id FROM students WHERE nisn = ? LIMIT 1";

        if ($this->db instanceof mysqli) {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("s", $nisn);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();
            return $exists;
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$nisn]);
            return (bool)$stmt->fetchColumn();
        }

        return false;
    }

    /**
     * Mencocokkan data sekolah pada tabel schools berdasarkan ID atau nama sekolah
     * Jika belum ada di database, memberikan fallback default
     *
     * @param string|int $schoolInput
     * @return array Array berisi 'id' dan 'name'
     */
    public function findSchool($schoolInput) {
        $schoolId = null;
        $schoolName = trim((string)$schoolInput);

        if (!$this->db) {
            return ['id' => 1, 'name' => $schoolName];
        }

        if ($this->db instanceof mysqli) {
            if (is_numeric($schoolInput)) {
                $stmt = $this->db->prepare("SELECT id, name FROM schools WHERE id = ? LIMIT 1");
                if ($stmt) {
                    $idNum = (int)$schoolInput;
                    $stmt->bind_param("i", $idNum);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) {
                        $schoolId = (int)$row["id"];
                        $schoolName = $row["name"];
                    }
                    $stmt->close();
                }
            } else {
                $stmt = $this->db->prepare("SELECT id, name FROM schools WHERE name = ? OR name LIKE ? LIMIT 1");
                if ($stmt) {
                    $likeSchool = "%" . $schoolInput . "%";
                    $stmt->bind_param("ss", $schoolInput, $likeSchool);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) {
                        $schoolId = (int)$row["id"];
                        $schoolName = $row["name"];
                    }
                    $stmt->close();
                }
            }

            // Fallback jika belum ditemukan di database
            if (!$schoolId) {
                $resDefault = $this->db->query("SELECT id, name FROM schools LIMIT 1");
                if ($resDefault && ($defRow = $resDefault->fetch_assoc())) {
                    $schoolId = (int)$defRow["id"];
                    if (empty($schoolName)) {
                        $schoolName = $defRow["name"];
                    }
                } else {
                    // Buat sekolah default jika tabel schools masih kosong
                    @$this->db->query("INSERT INTO schools (npsn, name, education_level, district, city, sppg_id, status) VALUES ('50100000', 'SMA Negeri 1 Denpasar', 'SMA', 'Denpasar Barat', 'Kota Denpasar', 1, 'active')");
                    $schoolId = (int)$this->db->insert_id;
                    if (empty($schoolName)) {
                        $schoolName = "SMA Negeri 1 Denpasar";
                    }
                }
            }
        } elseif ($this->db instanceof PDO) {
            if (is_numeric($schoolInput)) {
                $stmt = $this->db->prepare("SELECT id, name FROM schools WHERE id = ? LIMIT 1");
                $stmt->execute([(int)$schoolInput]);
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $schoolId = (int)$row["id"];
                    $schoolName = $row["name"];
                }
            } else {
                $stmt = $this->db->prepare("SELECT id, name FROM schools WHERE name = ? OR name LIKE ? LIMIT 1");
                $stmt->execute([$schoolInput, "%" . $schoolInput . "%"]);
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $schoolId = (int)$row["id"];
                    $schoolName = $row["name"];
                }
            }

            if (!$schoolId) {
                $stmtDef = $this->db->query("SELECT id, name FROM schools LIMIT 1");
                if ($stmtDef && ($defRow = $stmtDef->fetch(PDO::FETCH_ASSOC))) {
                    $schoolId = (int)$defRow["id"];
                    if (empty($schoolName)) {
                        $schoolName = $defRow["name"];
                    }
                }
            }
        }

        return [
            'id' => $schoolId ?: 1,
            'name' => $schoolName ?: "SMA Negeri 1 Denpasar"
        ];
    }

    /**
     * Menyimpan akun pengguna baru dan profil siswa menggunakan transaksi database
     *
     * @param array $userData
     * @param array $studentData
     * @return array Array berisi 'user_id' dan 'student_id'
     * @throws Exception
     */
    public function registerStudent($userData, $studentData) {
        if (!$this->db) {
            throw new Exception("Koneksi database belum tersedia.");
        }

        if ($this->db instanceof mysqli) {
            $this->db->begin_transaction();

            try {
                // 1. Simpan ke tabel users
                $stmtUser = $this->db->prepare(
                    "INSERT INTO users (username, email, password_hash, full_name, role, institution, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                if (!$stmtUser) {
                    throw new Exception("Gagal mempersiapkan query user: " . $this->db->error);
                }

                $stmtUser->bind_param(
                    "sssssss",
                    $userData['username'],
                    $userData['email'],
                    $userData['password_hash'],
                    $userData['full_name'],
                    $userData['role'],
                    $userData['institution'],
                    $userData['status']
                );

                if (!$stmtUser->execute()) {
                    throw new Exception("Gagal menyimpan data akun pengguna: " . $stmtUser->error);
                }

                $newUserId = (int)$stmtUser->insert_id;
                $stmtUser->close();

                // 2. Simpan ke tabel students
                $stmtStudent = $this->db->prepare(
                    "INSERT INTO students (user_id, nisn, name, school_id, class_name, recipient_status)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                if (!$stmtStudent) {
                    throw new Exception("Gagal mempersiapkan query siswa: " . $this->db->error);
                }

                $stmtStudent->bind_param(
                    "ississ",
                    $newUserId,
                    $studentData['nisn'],
                    $studentData['name'],
                    $studentData['school_id'],
                    $studentData['class_name'],
                    $studentData['recipient_status']
                );

                if (!$stmtStudent->execute()) {
                    throw new Exception("Gagal menyimpan profil siswa: " . $stmtStudent->error);
                }

                $newStudentId = (int)$stmtStudent->insert_id;
                $stmtStudent->close();

                $this->db->commit();

                return [
                    'user_id' => $newUserId,
                    'student_id' => $newStudentId
                ];
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } elseif ($this->db instanceof PDO) {
            $this->db->beginTransaction();

            try {
                $stmtUser = $this->db->prepare(
                    "INSERT INTO users (username, email, password_hash, full_name, role, institution, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmtUser->execute([
                    $userData['username'],
                    $userData['email'],
                    $userData['password_hash'],
                    $userData['full_name'],
                    $userData['role'],
                    $userData['institution'],
                    $userData['status']
                ]);
                $newUserId = (int)$this->db->lastInsertId();

                $stmtStudent = $this->db->prepare(
                    "INSERT INTO students (user_id, nisn, name, school_id, class_name, recipient_status)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmtStudent->execute([
                    $newUserId,
                    $studentData['nisn'],
                    $studentData['name'],
                    $studentData['school_id'],
                    $studentData['class_name'],
                    $studentData['recipient_status']
                ]);
                $newStudentId = (int)$this->db->lastInsertId();

                $this->db->commit();

                return [
                    'user_id' => $newUserId,
                    'student_id' => $newStudentId
                ];
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        }

        throw new Exception("Driver database tidak didukung.");
    }
}
