<?php
class ReadingAchievements
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function recordLoan(int $userId): array
    {
        return $this->award($userId, ['primera_pagina']);
    }

    public function recordReturns(int $userId, int $count): array
    {
        if ($userId <= 0 || $count <= 0) {
            return ['goals_completed' => [], 'badges_unlocked' => []];
        }

        $this->db->begin_transaction();
        try {
            $openGoals = $this->db->prepare(
                'SELECT id, titulo, cantidad_objetivo, cantidad_actual
                 FROM metas WHERE id_usuario = ? AND completada = 0 FOR UPDATE'
            );
            $openGoals->bind_param('i', $userId);
            $openGoals->execute();
            $goalRows = $openGoals->get_result();
            $completedGoals = [];
            while ($goal = $goalRows->fetch_assoc()) {
                if ((int) $goal['cantidad_actual'] + $count >= (int) $goal['cantidad_objetivo']) {
                    $completedGoals[] = $goal['titulo'];
                }
            }
            $openGoals->close();

            $update = $this->db->prepare(
                "UPDATE metas
                 SET completada = IF(cantidad_actual + ? >= cantidad_objetivo, 1, 0),
                     cantidad_actual = LEAST(cantidad_objetivo, cantidad_actual + ?)
                 WHERE id_usuario = ? AND completada = 0"
            );
            $update->bind_param('iii', $count, $count, $userId);
            $update->execute();
            $update->close();

            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollback();
            error_log('No se pudo actualizar el progreso de lectura: ' . $error->getMessage());
            return [
                'goals_completed' => [],
                'badges_unlocked' => [],
                'error' => 'No se pudo actualizar el progreso de lectura.',
            ];
        }

        $badges = $this->evaluateBadges($userId);
        return [
            'goals_completed' => $completedGoals,
            'badges_unlocked' => $badges,
        ];
    }

    private function evaluateBadges(int $userId): array
    {
        $query = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN fecha_devolucion_real <= fecha_devolucion_prevista THEN 1 ELSE 0 END) AS puntual
             FROM prestamos p
             JOIN reservas r ON r.id_reserva = p.id_reserva
             WHERE r.fk_id_usuario_reserva = ? AND p.estado = 'devuelto'
               AND p.fecha_devolucion_real IS NOT NULL"
        );
        $query->bind_param('i', $userId);
        $query->execute();
        $stats = $query->get_result()->fetch_assoc() ?: [];
        $query->close();

        $total = (int) ($stats['total'] ?? 0);
        $timely = (int) ($stats['puntual'] ?? 0);
        $earned = [];
        foreach ([3 => 'lector_novato', 10 => 'lector_intermedio', 25 => 'lector_avanzado', 50 => 'lector_experto'] as $threshold => $badge) {
            if ($total >= $threshold) $earned[] = $badge;
        }
        foreach ([5 => 'devolutor_puntual', 15 => 'super_puntual'] as $threshold => $badge) {
            if ($timely >= $threshold) $earned[] = $badge;
        }

        $categoryQuery = $this->db->prepare(
            "SELECT m.nombre, COUNT(*) AS total
             FROM prestamos p
             JOIN reservas r ON r.id_reserva = p.id_reserva
             JOIN ejemplares e ON e.id_ejemplar = p.id_ejemplar
             JOIN libros l ON l.id_libro = e.fk_id_libro_ejemplar
             JOIN materias m ON m.id_materia = l.id_materia
             WHERE r.fk_id_usuario_reserva = ? AND p.estado = 'devuelto'
             GROUP BY m.id_materia, m.nombre"
        );
        $categoryQuery->bind_param('i', $userId);
        $categoryQuery->execute();
        $categories = $categoryQuery->get_result();
        $categoryCount = 0;
        $categoryBadges = [
            'ciencias' => 'cientifico',
            'literatura' => 'literato',
            'historia' => 'historiador',
            'filosofia' => 'filosofo',
        ];
        while ($category = $categories->fetch_assoc()) {
            $categoryCount++;
            $name = function_exists('mb_strtolower')
                ? mb_strtolower(trim($category['nombre'] ?? ''), 'UTF-8')
                : strtolower(trim($category['nombre'] ?? ''));
            foreach ($categoryBadges as $needle => $badge) {
                if (str_contains($name, $needle) && (int) $category['total'] >= 5) {
                    $earned[] = $badge;
                }
            }
        }
        $categoryQuery->close();
        if ($categoryCount >= 3) $earned[] = 'explorador';
        if ($categoryCount >= 5) $earned[] = 'polimata';

        $monthQuery = $this->db->prepare(
            "SELECT DATE_FORMAT(fecha_devolucion_real, '%Y-%m') AS mes, COUNT(*) AS total
             FROM prestamos p
             JOIN reservas r ON r.id_reserva = p.id_reserva
             WHERE r.fk_id_usuario_reserva = ? AND p.estado = 'devuelto'
             GROUP BY mes ORDER BY mes DESC"
        );
        $monthQuery->bind_param('i', $userId);
        $monthQuery->execute();
        $months = $monthQuery->get_result();
        $consecutive = 0;
        $previousMonth = null;
        while ($month = $months->fetch_assoc()) {
            [$year, $monthNumber] = array_map('intval', explode('-', $month['mes']));
            $serial = $year * 12 + $monthNumber;
            if ($previousMonth === null || $previousMonth - $serial === 1) {
                $consecutive++;
            } elseif ($consecutive < 3) {
                $consecutive = 1;
            }
            $previousMonth = $serial;
            if ((int) $month['total'] >= 3) $earned[] = 'veloz';
            if ((int) $month['total'] >= 5) $earned[] = 'maratonista';
        }
        $monthQuery->close();
        if ($consecutive >= 3) $earned[] = 'lector_constante';

        $goalCount = $this->db->prepare(
            'SELECT COUNT(*) AS total FROM metas WHERE id_usuario = ? AND completada = 1'
        );
        $goalCount->bind_param('i', $userId);
        $goalCount->execute();
        $goalsCompleted = (int) ($goalCount->get_result()->fetch_assoc()['total'] ?? 0);
        $goalCount->close();
        if ($goalsCompleted > 0) $earned[] = 'meta_cumplida';
        if ($goalsCompleted >= 3) $earned[] = 'cumplidor_de_metas';

        return $this->award($userId, array_unique($earned));
    }

    private function award(int $userId, array $codes): array
    {
        if (!$codes) return [];
        $lookup = $this->db->query('SELECT id, codigo, nombre FROM insignias');
        $catalog = [];
        while ($badge = $lookup->fetch_assoc()) $catalog[$badge['codigo']] = $badge;
        $lookup->close();

        $insert = $this->db->prepare(
            'INSERT IGNORE INTO usuario_insignias (id_usuario, id_insignia) VALUES (?, ?)'
        );
        $unlocked = [];
        foreach ($codes as $code) {
            $badge = $catalog[$code] ?? null;
            $badgeId = (int) ($badge['id'] ?? 0);
            if (!$badgeId) continue;
            $insert->bind_param('ii', $userId, $badgeId);
            $insert->execute();
            if ($insert->affected_rows === 1) {
                $unlocked[] = ['codigo' => $code, 'nombre' => $badge['nombre'] ?? $code];
            }
        }
        $insert->close();
        return $unlocked;
    }

}
