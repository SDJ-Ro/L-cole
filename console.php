<?php
/**
 * =========================================================================
 * L'ÉCOLE — CLI BREAK-GLASS SYSTEM CONSOLE
 * =========================================================================
 * Operational CLI tool for emergency administrator unlocks, manual
 * password resets, lock monitoring, and audit log inspection.
 *
 * Usage:
 *   php MVC/console.php unlock <identifier>
 *   php MVC/console.php list-locked
 *   php MVC/console.php reset-password <identifier> <new-password>
 *   php MVC/console.php logs [limit] [action]
 *   php MVC/console.php test-mail [target-address]
 * =========================================================================
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: The L'École console can only be executed via the command line.\n");
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/MailService.php';
require_once __DIR__ . '/app/Models/AuditModel.php';
require_once __DIR__ . '/app/Models/UserModel.php';

// ANSI color helpers
function color(string $text, string $code): string {
    return "\033[{$code}m{$text}\033[0m";
}
function success(string $msg): void { echo color("✔ [SUCCESS] ", "32;1") . $msg . "\n"; }
function warn(string $msg): void    { echo color("⚠ [WARNING] ", "33;1") . $msg . "\n"; }
function error(string $msg): void   { echo color("✖ [ERROR]   ", "31;1") . $msg . "\n"; }
function info(string $msg): void    { echo color("ℹ [INFO]    ", "36;1") . $msg . "\n"; }

$banner = "
" . color("======================================================================", "34;1") . "
" . color("               L'ÉCOLE — BREAK-GLASS SYSTEM CONSOLE                  ", "36;1") . "
" . color("======================================================================", "34;1") . "
";

echo $banner;

$command = $argv[1] ?? 'help';

switch ($command) {
    case 'unlock':
    case 'unlock:user':
        $identifier = $argv[2] ?? null;
        if (!$identifier) {
            error("Missing identifier. Usage: php console.php unlock <email-or-index-number>");
            exit(1);
        }
        $userModel = new UserModel();
        $res = $userModel->unlockAccount($identifier);
        if ($res['success']) {
            success($res['message']);
            info("Account ID: " . $res['account']['id'] . " | Identifier: " . $res['account']['identifier'] . " | Role: " . $res['account']['role']);
        } else {
            error($res['error']);
            exit(1);
        }
        break;

    case 'list-locked':
    case 'locked':
        $userModel = new UserModel();
        $lockedList = $userModel->listLockedAccounts();
        if (empty($lockedList)) {
            success("Great news! There are currently no locked or suspended accounts in the system.");
        } else {
            warn("Found " . count($lockedList) . " locked/suspended account(s):");
            printf("%-6s | %-24s | %-12s | %-15s | %-20s\n", "ID", "Identifier", "Role", "Lock Status", "Lock Expiry");
            echo str_repeat("-", 85) . "\n";
            foreach ($lockedList as $row) {
                $status = !empty($row['locked_at']) ? color("HARD LOCKED", "31;1") : color("SOFT LOCKED", "33;1");
                $expiry = $row['lock_expires_at'] ?? 'Permanent (Admin)';
                printf("%-6d | %-24s | %-12s | %-24s | %-20s\n", $row['id'], $row['identifier'], $row['role'], $status, $expiry);
            }
        }
        break;

    case 'reset-password':
        $identifier = $argv[2] ?? null;
        $newPass = $argv[3] ?? null;
        if (!$identifier || !$newPass) {
            error("Missing parameters. Usage: php console.php reset-password <identifier> <new-password>");
            exit(1);
        }
        $userModel = new UserModel();
        $res = $userModel->resetPasswordDirect($identifier, $newPass);
        if ($res['success']) {
            success($res['message']);
        } else {
            error($res['error']);
            exit(1);
        }
        break;

    case 'logs':
    case 'audit':
        $limit = isset($argv[2]) && is_numeric($argv[2]) ? (int)$argv[2] : 20;
        $filterAction = isset($argv[3]) ? $argv[3] : (isset($argv[2]) && !is_numeric($argv[2]) ? $argv[2] : null);
        $auditModel = new AuditModel();
        $logs = $auditModel->getRecentLogs($limit, $filterAction);
        if (empty($logs)) {
            info("No audit logs found matching criteria.");
        } else {
            info("Showing latest " . count($logs) . " audit event(s):");
            printf("%-6s | %-19s | %-22s | %-22s | %-15s | %-30s\n", "ID", "Timestamp", "Action", "Identifier", "IP Address", "Details");
            echo str_repeat("-", 125) . "\n";
            foreach ($logs as $l) {
                $actionColor = match($l['action']) {
                    'LOGIN_SUCCESS', 'ACCOUNT_ACTIVATED', 'ACCOUNT_REGISTERED', 'PASSWORD_RESET_COMPLETED', 'ADMIN_CLI_UNLOCK' => "32;1",
                    'LOGIN_FAILED', 'LOGIN_BLOCKED_PENDING', 'SESSION_EXPIRED' => "33;1",
                    'ACCOUNT_LOCKED', 'LOGIN_BLOCKED_HARD_LOCK', 'LOGIN_BLOCKED_SOFT_LOCK', 'CSRF_INVALID' => "31;1",
                    default => "37"
                };
                $actColored = color(str_pad(substr($l['action'], 0, 22), 22), $actionColor);
                $ident = substr($l['identifier'] ?? '-', 0, 22);
                $details = substr($l['details'] ?? '', 0, 45);
                printf("%-6d | %-19s | %s | %-22s | %-15s | %-30s\n", $l['id'], $l['created_at'], $actColored, $ident, $l['ip_address'], $details);
            }
        }
        break;

    case 'test-mail':
        $target = $argv[2] ?? 'sarah_vance@lecole.edu';
        info("Dispatching test diagnostic mail for '{$target}'...");
        $res = MailService::sendPasswordResetOtp($target, '999888', 'management', $target);
        if ($res['success']) {
            success("Test mail sent successfully!");
            info("Routed destination: " . ($res['routed_to'] ?? 'lecoletesting@gmail.com'));
            info("Logged to: MVC/storage/logs/mail.log");
        } else {
            error("Test mail dispatch failed: " . ($res['error'] ?? 'Unknown error'));
        }
        break;

    case 'invite':
    case 'send-invite':
        $identifier = $argv[2] ?? null;
        if (!$identifier) {
            error("Missing identifier. Usage: php console.php invite <identifier-or-index>");
            exit(1);
        }
        $userModel = new UserModel();
        info("Dispatching private email activation invite for '{$identifier}'...");
        $res = $userModel->sendActivationInviteForAccount($identifier);
        if ($res['success']) {
            success($res['message']);
            info("Recipient: " . $res['recipient'] . " (Delivered to: " . $res['routed_to'] . ")");
        } else {
            error($res['error']);
            exit(1);
        }
        break;

    case 'help':
    default:
        echo color("Available commands:\n", "33;1");
        echo "  " . color("unlock <identifier>", "32;1") . "                  Unlock a locked user by email or student index\n";
        echo "  " . color("list-locked", "32;1") . "                        Display all currently locked/suspended accounts\n";
        echo "  " . color("invite <identifier>", "32;1") . "                     Send private email activation invite to pending user\n";
        echo "  " . color("reset-password <id> <pass>", "32;1") . "          Directly reset an account password from CLI\n";
        echo "  " . color("logs [limit] [action]", "32;1") . "               View the latest immutable activity & security logs\n";
        echo "  " . color("test-mail [recipient]", "32;1") . "               Test the Book Cover Mail dispatch engine\n";
        echo "  " . color("help", "32;1") . "                               Display this help guide\n\n";
        break;
}
