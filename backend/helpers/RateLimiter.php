<?php

class RateLimiter
{
    private mysqli $db;
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(mysqli $connection, int $maxAttempts = 15, int $decaySeconds = 900)
    {
        if ($maxAttempts < 1 || $decaySeconds < 1) {
            throw new InvalidArgumentException('Rate limit settings must be positive.');
        }

        $this->db = $connection;
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    public function check(string $identifier): bool
    {
        if ($identifier === '' || strlen($identifier) > 255) {
            throw new InvalidArgumentException('Rate limit identifier must contain 1 to 255 bytes.');
        }

        if (random_int(1, 100) === 1) {
            
            $this->purgeExpired();
        }
        $now = time();
        $resetTime = $now + $this->decaySeconds;
        $maxStoredAttempts = $this->maxAttempts + 1;

        $sql = "INSERT INTO rate_limits (identifier, attempts, reset_time)
                VALUES (?, 1, ?)
                ON DUPLICATE KEY UPDATE
                    attempts = IF(reset_time <= ?, 1, LEAST(attempts + 1, ?)),
                    reset_time = IF(reset_time <= ?, ?, reset_time)";
        $query = $this->db->prepare($sql);
        $query->bind_param(
            'siiiii',
            $identifier,
            $resetTime,
            $now,
            $maxStoredAttempts,
            $now,
            $resetTime
        );
        $query->execute();
        $query->close();

        $sql = 'SELECT attempts, reset_time FROM rate_limits WHERE identifier = ? LIMIT 1';
        $query = $this->db->prepare($sql);
        $query->bind_param('s', $identifier);
        $query->execute();
        $record = $query->get_result()->fetch_assoc();
        $query->close();

        return $record !== null
            && (int) $record['reset_time'] > $now
            && (int) $record['attempts'] <= $this->maxAttempts;
    }

    public function purgeExpired(): int
    {
        $now = time();
        $query = $this->db->prepare('DELETE FROM rate_limits WHERE reset_time <= ?');
        $query->bind_param('i', $now);
        $query->execute();
        $affectedRows = $query->affected_rows;
        $query->close();

        return $affectedRows;
    }
}
