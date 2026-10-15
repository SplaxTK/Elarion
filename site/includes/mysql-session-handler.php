<?php

declare(strict_types=1);

final class MySqlSessionHandler implements SessionHandlerInterface
{
    private ?string $lockName = null;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        if ($this->lockName === null) {
            return true;
        }

        $statement = $this->pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $statement->execute([':lock_name' => $this->lockName]);
        $released = $statement->fetchColumn();
        $this->lockName = null;

        return $released === 1 || $released === '1';
    }

    public function read(string $id): string|false
    {
        $this->lockName = 'elarion_session_' . substr(hash('sha256', $id), 0, 32);
        $lock = $this->pdo->prepare('SELECT GET_LOCK(:lock_name, 10)');
        $lock->execute([':lock_name' => $this->lockName]);

        $lockAcquired = $lock->fetchColumn();
        if ($lockAcquired !== 1 && $lockAcquired !== '1') {
            $this->lockName = null;
            error_log('[elarion][session] bloqueio da sessão indisponível.');
            throw new RuntimeException('Não foi possível obter o bloqueio da sessão.');
        }

        $statement = $this->pdo->prepare('SELECT payload FROM elarion_sessions WHERE session_id = :session_id AND expires_at > :now LIMIT 1');
        $statement->execute([
            ':session_id' => $id,
            ':now' => time(),
        ]);
        $payload = $statement->fetchColumn();

        return $payload === false ? '' : (string) $payload;
    }

    public function write(string $id, string $data): bool
    {
        $maxLifetime = max(1, (int) ini_get('session.gc_maxlifetime'));
        $statement = $this->pdo->prepare(
            'INSERT INTO elarion_sessions (session_id, payload, expires_at) VALUES (:session_id, :payload, :expires_at)
            ON DUPLICATE KEY UPDATE payload = VALUES(payload), expires_at = VALUES(expires_at)'
        );

        return $statement->execute([
            ':session_id' => $id,
            ':payload' => $data,
            ':expires_at' => time() + $maxLifetime,
        ]);
    }

    public function destroy(string $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM elarion_sessions WHERE session_id = :session_id');

        return $statement->execute([':session_id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $statement = $this->pdo->prepare('DELETE FROM elarion_sessions WHERE expires_at <= :expires_at');
        $statement->execute([':expires_at' => time()]);

        return $statement->rowCount();
    }
}
