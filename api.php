<?php
// ================================================
// api.php — REST-style handler untuk CRUD task
// ================================================

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$db     = getDB();

try {
    switch ($method) {

        // ── GET: ambil semua / satu task ──────────────────────────────
        case 'GET':
            if ($id) {
                $stmt = $db->prepare('SELECT * FROM tasks WHERE id = ?');
                $stmt->execute([$id]);
                $task = $stmt->fetch();
                echo json_encode($task ?: ['error' => 'Task tidak ditemukan']);
            } else {
                $search    = $_GET['q']         ?? '';
                $filterSt  = $_GET['status']    ?? '';
                $filterPr  = $_GET['prioritas'] ?? '';
                $sortBy    = in_array($_GET['sort'] ?? '', ['judul','deadline','prioritas','dibuat_pada'])
                             ? $_GET['sort'] : 'dibuat_pada';
                $sortDir   = ($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

                $where = ['1=1'];
                $params = [];

                if ($search) {
                    $where[]  = '(judul LIKE ? OR deskripsi LIKE ?)';
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }
                if ($filterSt)  { $where[] = 'status = ?';    $params[] = $filterSt; }
                if ($filterPr)  { $where[] = 'prioritas = ?'; $params[] = $filterPr; }

                $sql  = 'SELECT * FROM tasks WHERE ' . implode(' AND ', $where)
                      . " ORDER BY $sortBy $sortDir";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $tasks = $stmt->fetchAll();

                // ringkasan statistik
                $statStmt = $db->query(
                    "SELECT
                        COUNT(*) AS total,
                        SUM(status='selesai') AS selesai,
                        SUM(status='proses')  AS proses,
                        SUM(status='belum')   AS belum,
                        SUM(prioritas='tinggi' AND status!='selesai') AS urgent
                     FROM tasks"
                );
                echo json_encode(['tasks' => $tasks, 'stats' => $statStmt->fetch()]);
            }
            break;

        // ── POST: buat task baru ──────────────────────────────────────
        case 'POST':
            if (empty($input['judul'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Judul wajib diisi']);
                break;
            }
            $stmt = $db->prepare(
                'INSERT INTO tasks (judul, deskripsi, prioritas, status, deadline)
                 VALUES (:judul, :deskripsi, :prioritas, :status, :deadline)'
            );
            $stmt->execute([
                'judul'     => trim($input['judul']),
                'deskripsi' => trim($input['deskripsi'] ?? ''),
                'prioritas' => $input['prioritas'] ?? 'sedang',
                'status'    => $input['status']    ?? 'belum',
                'deadline'  => $input['deadline']  ?: null,
            ]);
            $new = $db->prepare('SELECT * FROM tasks WHERE id = ?');
            $new->execute([$db->lastInsertId()]);
            http_response_code(201);
            echo json_encode(['success' => true, 'task' => $new->fetch()]);
            break;

        // ── PUT: update task ──────────────────────────────────────────
        case 'PUT':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID diperlukan']); break; }
            $stmt = $db->prepare(
                'UPDATE tasks SET judul=:judul, deskripsi=:deskripsi,
                 prioritas=:prioritas, status=:status, deadline=:deadline
                 WHERE id=:id'
            );
            $stmt->execute([
                'judul'     => trim($input['judul']),
                'deskripsi' => trim($input['deskripsi'] ?? ''),
                'prioritas' => $input['prioritas'] ?? 'sedang',
                'status'    => $input['status']    ?? 'belum',
                'deadline'  => $input['deadline']  ?: null,
                'id'        => $id,
            ]);
            echo json_encode(['success' => true]);
            break;

        // ── DELETE: hapus task ────────────────────────────────────────
        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID diperlukan']); break; }
            $stmt = $db->prepare('DELETE FROM tasks WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method tidak diizinkan']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
