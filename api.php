<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$uid    = currentUser()['id'];
$db     = getDB();

try {
    switch ($method) {

        case 'GET':
            if ($id) {
                $s = $db->prepare('SELECT * FROM tasks WHERE id=? AND user_id=?');
                $s->execute([$id, $uid]);
                $t = $s->fetch();
                echo json_encode($t ?: ['error' => 'Task tidak ditemukan']);
            } else {
                $search  = $_GET['q']         ?? '';
                $fStatus = $_GET['status']    ?? '';
                $fPrio   = $_GET['prioritas'] ?? '';
                $sort    = in_array($_GET['sort'] ?? '', ['judul','deadline','prioritas','dibuat_pada'])
                           ? $_GET['sort'] : 'dibuat_pada';
                $dir     = ($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

                $where  = ['user_id = ?'];
                $params = [$uid];

                if ($search)  { $where[] = '(judul LIKE ? OR deskripsi LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
                if ($fStatus) { $where[] = 'status = ?';    $params[] = $fStatus; }
                if ($fPrio)   { $where[] = 'prioritas = ?'; $params[] = $fPrio; }

                $sql  = 'SELECT * FROM tasks WHERE ' . implode(' AND ', $where) . " ORDER BY $sort $dir";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);

                $statS = $db->prepare(
                    "SELECT COUNT(*) AS total,
                            SUM(status='selesai') AS selesai,
                            SUM(status='proses')  AS proses,
                            SUM(status='belum')   AS belum,
                            SUM(prioritas='tinggi' AND status!='selesai') AS urgent
                     FROM tasks WHERE user_id=?"
                );
                $statS->execute([$uid]);

                echo json_encode(['tasks' => $stmt->fetchAll(), 'stats' => $statS->fetch()]);
            }
            break;

        case 'POST':
            if (empty($input['judul'])) { http_response_code(400); echo json_encode(['error'=>'Judul wajib diisi']); break; }
            $s = $db->prepare('INSERT INTO tasks (user_id,judul,deskripsi,prioritas,status,deadline) VALUES (?,?,?,?,?,?)');
            $s->execute([
                $uid,
                trim($input['judul']),
                trim($input['deskripsi'] ?? ''),
                $input['prioritas'] ?? 'sedang',
                $input['status']    ?? 'belum',
                $input['deadline']  ?: null,
            ]);
            $new = $db->prepare('SELECT * FROM tasks WHERE id=?');
            $new->execute([$db->lastInsertId()]);
            http_response_code(201);
            echo json_encode(['success' => true, 'task' => $new->fetch()]);
            break;

        case 'PUT':
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID diperlukan']); break; }
            // Pastikan task milik user ini
            $chk = $db->prepare('SELECT id FROM tasks WHERE id=? AND user_id=?');
            $chk->execute([$id, $uid]);
            if (!$chk->fetch()) { http_response_code(403); echo json_encode(['error'=>'Akses ditolak']); break; }

            $s = $db->prepare(
                'UPDATE tasks SET judul=?,deskripsi=?,prioritas=?,status=?,deadline=? WHERE id=? AND user_id=?'
            );
            $s->execute([
                trim($input['judul']),
                trim($input['deskripsi'] ?? ''),
                $input['prioritas'] ?? 'sedang',
                $input['status']    ?? 'belum',
                $input['deadline']  ?: null,
                $id, $uid,
            ]);
            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID diperlukan']); break; }
            $s = $db->prepare('DELETE FROM tasks WHERE id=? AND user_id=?');
            $s->execute([$id, $uid]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method tidak diizinkan']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
