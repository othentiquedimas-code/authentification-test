<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/helpers/security.php';
require_once BASE_PATH . '/helpers/validation.php';

$passed = 0;
$failed = 0;

startSecureSession();

function checkTest(bool $condition, string $label): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "PASS: {$label}\n";
        return;
    }

    $failed++;
    echo "FAIL: {$label}\n";
}

checkTest(validateRegistration('Demo', 'demo@example.com', 'Password1!', 'Password1!', 'etudiant') === [], 'registration accepts student role');
checkTest(validateRegistration('Demo', 'demo@example.com', 'Password1!', 'Password1!', 'enseignant') === [], 'registration accepts teacher role');
checkTest(isset(validateRegistration('Demo', 'demo@example.com', 'Password1!', 'Password1!', 'admin')['role']), 'registration rejects admin role');
checkTest(isset(validateStudentInput('', '', '', 'bad-email', null, 0, '', '')['nom']), 'student validation rejects incomplete input');
checkTest(isset(validateRoomInput('', '', 0)['capacite']), 'room validation rejects invalid capacity');
checkTest(isset(validateDefenseJuryInput(1, 1, 'invalid')['role_in_jury']), 'jury validation rejects invalid role');
checkTest(e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;', 'HTML output is escaped');

$token = csrfToken();
checkTest($token !== '' && verifyCsrfToken($token), 'CSRF accepts the current token');
checkTest(!verifyCsrfToken('invalid-token'), 'CSRF rejects an invalid token');

try {
    $connection = database();
    $tables = $connection->query("SHOW TABLES LIKE 'users'")->fetchColumn();
    checkTest($tables === 'users', 'users table exists');

    foreach (['filiers', 'students', 'teachers', 'rooms', 'defenses', 'defense_jury'] as $table) {
        $exists = $connection->query("SHOW TABLES LIKE '{$table}'")->fetchColumn();
        checkTest($exists === $table, "{$table} table exists");
    }

    $demoCounts = $connection->query(
        "SELECT
            (SELECT COUNT(*) FROM filiers) AS filieres,
            (SELECT COUNT(*) FROM students) AS students,
            (SELECT COUNT(*) FROM teachers) AS teachers,
            (SELECT COUNT(*) FROM rooms) AS rooms,
            (SELECT COUNT(*) FROM defenses) AS defenses,
            (SELECT COUNT(*) FROM defense_jury) AS jury_members"
    )->fetch();
    checkTest((int) $demoCounts['filieres'] >= 2, 'demo filieres are available');
    checkTest((int) $demoCounts['students'] >= 2, 'demo students are available');
    checkTest((int) $demoCounts['teachers'] >= 2, 'demo teachers are available');
    checkTest((int) $demoCounts['rooms'] >= 2, 'demo rooms are available');
    checkTest((int) $demoCounts['defenses'] >= 2, 'demo defenses are available');
    checkTest((int) $demoCounts['jury_members'] >= 4, 'demo jury members are available');

    $statusColumn = $connection->query("SHOW COLUMNS FROM users LIKE 'account_status'")->fetch();
    checkTest($statusColumn !== false && str_contains((string) $statusColumn['Type'], 'en_attente'), 'account approval status is available');
} catch (Throwable $exception) {
    $failed++;
    echo 'FAIL: database smoke test: ' . $exception->getMessage() . "\n";
}

printf("\n%d passed, %d failed\n", $passed, $failed);
exit($failed === 0 ? 0 : 1);
