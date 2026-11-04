<?php
function isAdminEmail(string $email): bool
{
    $adminEmails = array_map(
        static fn (string $configuredEmail): string => strtolower(trim($configuredEmail)),
        ELARION_ADMIN_EMAILS,
    );

    return in_array(strtolower(trim($email)), $adminEmails, true);
}