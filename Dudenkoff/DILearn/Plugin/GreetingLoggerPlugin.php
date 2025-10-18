<?php
/**
 * Greeting Logger Plugin
 * 
 * DEMONSTRATES: Plugin Pattern (Interceptors)
 * 
 * Plugins allow you to modify the behavior of ANY public method
 * without changing the original class.
 * 
 * THREE TYPES OF PLUGINS:
 * 
 * 1. BEFORE Plugin:
 *    - Runs BEFORE the original method
 *    - Can modify method arguments
 *    - Method name: before{MethodName}
 * 
 * 2. AFTER Plugin:
 *    - Runs AFTER the original method
 *    - Can modify the result
 *    - Method name: after{MethodName}
 * 
 * 3. AROUND Plugin:
 *    - Wraps the original method
 *    - Full control (can prevent execution)
 *    - Method name: around{MethodName}
 *    - Most powerful but use sparingly
 * 
 * CONFIGURATION:
 * Plugins are configured in di.xml under <type> element
 */

namespace Dudenkoff\DILearn\Plugin;

use Dudenkoff\DILearn\Service\GreetingService;

class GreetingLoggerPlugin
{
    /**
     * BEFORE Plugin Example
     * 
     * Runs before GreetingService::greet()
     * Can modify the arguments passed to the original method
     * 
     * PARAMETERS:
     * @param GreetingService $subject The object being intercepted
     * @param string $name The original method's parameter(s)
     * 
     * RETURN:
     * Array of arguments to pass to the original method
     * - Return null to keep arguments unchanged
     * - Return array to modify arguments
     *
     * @param GreetingService $subject
     * @param string $name
     * @return array|null
     */
    public function beforeGreet(
        GreetingService $subject,
        string $name
    ): ?array {
        // Log before the method runs
        error_log('[PLUGIN BEFORE] About to greet: ' . $name);

        // Example: Capitalize the name
        $modifiedName = ucfirst(strtolower($name));

        // Return modified arguments
        // Return [$modifiedName] to change the argument
        // Return null to keep original arguments
        return [$modifiedName];
    }

    /**
     * AFTER Plugin Example
     * 
     * Runs after GreetingService::greet()
     * Can modify the result before it's returned to the caller
     * 
     * PARAMETERS:
     * @param GreetingService $subject The object being intercepted
     * @param string $result The result from the original method
     * @param string $name The original method's parameter(s)
     * 
     * RETURN:
     * The modified result (or original if no modification needed)
     *
     * @param GreetingService $subject
     * @param string $result
     * @param string $name
     * @return string
     */
    public function afterGreet(
        GreetingService $subject,
        string $result,
        string $name
    ): string {
        // Log after the method runs
        error_log('[PLUGIN AFTER] Greeted: ' . $name);

        // Modify the result by adding a suffix
        return $result . ' [Via Plugin]';
    }

    /**
     * AROUND Plugin Example (Commented out - use ONLY when necessary)
     * 
     * Wraps the original method completely
     * You have full control and MUST call $proceed to execute original method
     * 
     * WARNING: Use sparingly! Can impact performance and cause conflicts
     * 
     * PARAMETERS:
     * @param GreetingService $subject The object being intercepted
     * @param callable $proceed Callable to execute the original method
     * @param string $name The original method's parameter(s)
     * 
     * RETURN:
     * The result (either from $proceed or your custom logic)
     */
    /*
    public function aroundGreet(
        GreetingService $subject,
        callable $proceed,
        string $name
    ): string {
        // Code BEFORE original method
        error_log('[PLUGIN AROUND - BEFORE] Name: ' . $name);
        
        // Optionally modify arguments before passing to original method
        $modifiedName = strtoupper($name);
        
        // Call the original method (or skip it entirely)
        $result = $proceed($modifiedName);
        
        // Code AFTER original method
        error_log('[PLUGIN AROUND - AFTER] Result: ' . $result);
        
        // Modify and return the result
        return '[AROUND] ' . $result;
    }
    */
}

