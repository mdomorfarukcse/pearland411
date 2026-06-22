<?php
/**
 * post_controller hook that fixes CodeIgniter 3's broken flash-data
 * cleanup under PHP 8.
 *
 * CI3 marks freshly-set flash items as 'new' and, on the next request,
 * transitions them to 'old' in $_SESSION['__ci_vars']. The cleanup then
 * runs at the start of the request AFTER that, deleting any 'old'
 * markers (and the matching $_SESSION key).
 *
 * The deletion check in system/libraries/Session/Session.php is:
 *
 *     elseif ($value < $current_time)
 *
 * In PHP 7 this worked: $value === 'old' coerced to (int)0 and
 *   0 < time()   was TRUE   → unset fires.
 *
 * PHP 8 changed string/int comparison semantics: the integer is cast
 * to its string form, then a lexicographic compare runs.
 *   'old' < '1718956800'   evaluates 'o' (0x6F) vs '1' (0x31)
 *   'o' is greater than '1', so the test is FALSE   → unset never fires.
 *
 * Result: every flash error/message ever set sticks around forever
 * and leaks onto subsequent admin pages (e.g. the "Listing not
 * found." banner showing up on /admin/campaigns).
 *
 * This hook runs at post_controller (after the controller method
 * finishes, before output is sent) and explicitly removes any
 * __ci_vars entries whose value is the literal string 'old'. PHP
 * has finished using them for the current request by this point —
 * the views have already rendered any flash they care about — so
 * deleting them now leaves the next request with a clean slate.
 *
 * Numeric temp-data entries (mark_as_temp) are untouched; their
 * comparison uses real integers and works correctly in PHP 8.
 */
if (!function_exists('clear_stale_flash_markers'))
{
    function clear_stale_flash_markers()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) return;
        if (empty($_SESSION['__ci_vars']) || !is_array($_SESSION['__ci_vars'])) return;

        foreach ($_SESSION['__ci_vars'] as $key => $value) {
            if ($value === 'old') {
                unset($_SESSION[$key], $_SESSION['__ci_vars'][$key]);
            }
        }
        if (empty($_SESSION['__ci_vars'])) {
            unset($_SESSION['__ci_vars']);
        }
    }
}
