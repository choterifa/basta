<?php

function getRoundSyncFilePath($id_partida)
{
    return sys_get_temp_dir() . "/basta_round_" . (int) $id_partida . ".json";
}

function readRoundDeadlineMs($id_partida)
{
    $path = getRoundSyncFilePath($id_partida);
    if (!file_exists($path)) {
        return null;
    }

    $contents = @file_get_contents($path);
    if ($contents === false) {
        return null;
    }

    $data = json_decode($contents, true);
    if (!is_array($data) || !isset($data['deadline_ms'])) {
        return null;
    }

    return (int) $data['deadline_ms'];
}

function writeRoundDeadlineMs($id_partida, $deadline_ms)
{
    $path = getRoundSyncFilePath($id_partida);
    @file_put_contents($path, json_encode(['deadline_ms' => (int) $deadline_ms]), LOCK_EX);
}

function ensureRoundDeadlineMs($conn, $id_partida, $lead_time_ms = 1800)
{
    $current_ms = (int) round(microtime(true) * 1000);
    $existing_deadline = readRoundDeadlineMs($id_partida);

    if ($existing_deadline !== null && $existing_deadline > ($current_ms - 15000)) {
        mysqli_query($conn, "UPDATE partidas SET estado='finalizada' WHERE id_partida=" . (int) $id_partida);
        return $existing_deadline;
    }

    $deadline_ms = $current_ms + (int) $lead_time_ms;
    writeRoundDeadlineMs($id_partida, $deadline_ms);
    mysqli_query($conn, "UPDATE partidas SET estado='finalizada' WHERE id_partida=" . (int) $id_partida);

    return $deadline_ms;
}
