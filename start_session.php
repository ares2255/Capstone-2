<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

function jsonOut($ok, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok' => $ok], $data));
    exit();
}

if (isset($_GET['id'])) {
    $pc_id = intval($_GET['id']);

    $check = $pdo->prepare("SELECT status FROM pcs WHERE id = :id");
    $check->execute([':id' => $pc_id]);
    $pc = $check->fetch();

    $isOvertime = false;
    if (!$pc) {
        if ($isAjax) jsonOut(false, ['msg' => 'pc_not_found']);
        header("Location: counter.php"); exit();
    }
    if ($pc['status'] === 'active') {
        // Check if overtime — if so, allow adding time by closing current session first
        $otCheck = $pdo->prepare("SELECT s.id, s.start_time, s.time_limit FROM sessions s WHERE s.pc_id = :id AND s.end_time IS NULL ORDER BY s.id DESC LIMIT 1");
        $otCheck->execute([':id' => $pc_id]);
        $curSession = $otCheck->fetch();
        if ($curSession && !empty($curSession['time_limit']) && (int)$curSession['time_limit'] > 0) {
            $start_dt = new DateTime($curSession['start_time'], new DateTimeZone('Asia/Manila'));
            $now_dt   = new DateTime('now', new DateTimeZone('Asia/Manila'));
            $elapsed_mins = ($now_dt->getTimestamp() - $start_dt->getTimestamp()) / 60;
            if ($elapsed_mins > (int)$curSession['time_limit']) {
                $isOvertime = true;
                // Close overtime session — save cost, will be summed at final end
                $end_now  = date("Y-m-d H:i:s");
                $tl       = (int)$curSession['time_limit'];
                $pkgQ     = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");
                $pkgQ->execute([':m' => $tl]);
                $pkgR     = $pkgQ->fetch();
                $ot_cost  = $pkgR ? (float)$pkgR['price'] : 0;
                $pdo->prepare("UPDATE sessions SET end_time=:et, cost=:c WHERE id=:id AND end_time IS NULL")
                    ->execute([':et' => $end_now, ':c' => $ot_cost, ':id' => $curSession['id']]);
            }
        }
        if (!$isOvertime) {
            if ($isAjax) jsonOut(false, ['msg' => 'already_active']);
            header("Location: counter.php"); exit();
        }
    }

    $mins_raw   = (isset($_GET['mins']) && is_numeric($_GET['mins'])) ? abs(intval($_GET['mins'])) : 0;
    $time_limit = ($mins_raw > 0) ? $mins_raw : null;   // 0 = Open Time → store NULL
    $pkg_id     = (isset($_GET['pkg_id']) && is_numeric($_GET['pkg_id'])) ? intval($_GET['pkg_id']) : null;

    // Always use server time to avoid browser clock mismatch
    $start_time = date("Y-m-d H:i:s");

    $pdo->prepare("UPDATE pcs SET status = 'active' WHERE id = :id")
        ->execute([':id' => $pc_id]);

    $stmt = $pdo->prepare("INSERT INTO sessions (pc_id, start_time, time_limit, package_id) VALUES (:pc, :st, :tl, :pkg)");
    $stmt->execute([':pc' => $pc_id, ':st' => $start_time, ':tl' => $time_limit, ':pkg' => $pkg_id]);

    // Get package price for AJAX response
    $price = 0;
    if ($pkg_id) {
        $pr = $pdo->prepare("SELECT price FROM packages WHERE id = :id");
        $pr->execute([':id' => $pkg_id]);
        $row = $pr->fetch();
        if ($row) $price = $row['price'];
    } elseif ($time_limit === null) {
        // Open Time: show minimum charge as the starting display price
        $settingsRow = $pdo->query("SELECT minimum_charge FROM settings WHERE id=1")->fetch();
        $price = $settingsRow ? (float)$settingsRow['minimum_charge'] : 0;
    }

    if ($isAjax) jsonOut(true, ['price' => $price, 'start_time' => $start_time, 'unixt' => (new DateTime($start_time, new DateTimeZone('Asia/Manila')))->getTimestamp()]);

    header("Location: counter.php?status=started");
    exit();
}

if ($isAjax) jsonOut(false, ['msg' => 'no_id']);
header("Location: counter.php");
?>
