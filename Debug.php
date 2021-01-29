<?php

/**
 * This file is part of the CleverAge/Tools package.
 *
 * Copyright (C) 2020-2021 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/**
 * Usage
 * -----
 * \CleverAge\Tools\Debug::dump($myvar);                    // dumps $myvar on stdout
 * \CleverAge\Tools\Debug::dump($myvar, 5, 'tmp.log');      // dumps $myvar into file tmp.log with 5 levels deep max
 * \CleverAge\Tools\Debug::debugBacktrace();                // prints stacktrace to the current line
 * \CleverAge\Tools\Debug::debugBacktrace('tmp.log');       // writes stacktrace to the current line into file tmp.log
 *
 * Format
 * ------
 * %t: Time as given by date('c')
 * %c: Context (calling method/function)
 * %l: Label (dump method parameters)
 * %m: Memory (in bytes)
 * %M: Memory (in MB)
 * %d: Dump content
 * %i: Remote IP
 * %I: Remote IP, or X-Forwarded-For IP if localhost found
 * %f: X-Forwarded-For IP
 * %u: Request URI
 *
 * Changelog
 * ---------
 *  v1.2.0:  Added new formatters. Refactoring.
 *  v1.1.1:  Removed HTML formatting, definitely.
 *  v1.1.0:  Replaced indentation, fixed CLI output, [...]
 *  v1.0.0:  New version for Magento 2, using namespaces (but some features removed)
 *  v0.1.17: Fixed PHP 5.3 compatibility
 *  v0.1.16: Added collection items count
 *  v0.1.15: Added output format support with XITOOLSDEBUG_FORMAT_DUMP
 *  v0.1.14: Added Exception support
 *  v0.1.13: Added value alignment using tabs
 *  v0.1.12: Improved depth limit detection
 *  v0.1.11: Added Zend_Db_Select support
 *  v0.1.10: Added Mage_Core_Block_Abstract support
 *  v0.1.9:  Added Zend_Controller_Request_Abstract support
 *  v0.1.8:  Fixed toArray()
 *  v0.1.7:  Finally fixed SimpleXmlElement support
 *  v0.1.6:  Removed support for SimpleXmlElement (too buggy) + Do not load collections to dump items + Added
 *           debugBacktrace() v0.1.5: Added partial support for SimpleXmlElement v0.1.4: File context is now
 *           relative to Mage root
 *  v0.1.3:  Fixed HTML output v0.1.2: Added context support ($addContext) v0.1.1: Added
 *           Varien_Data_Collection/Varien_Data_Collection_Db support v0.1.0: First version, code from PHP.net with
 *           $maxDepth, Varien_Object adaptations and direct output or file
 *
 * @version 1.1.0
 * @since   2011-08-19
 * @author  http://www.php.net/manual/en/function.var-dump.php#92594 (original concept and code)
 * @author  aollier@clever-age.com (Varien_Object & Varien_Data_Collection additions, $maxDepth & $addContext arguments,
 *     debugBacktrace method, M2 adaptations)
 */
namespace CleverAge\Tools;

// phpcs:disable
if (!defined('XITOOLSDEBUG_FORMAT_DUMP')) {
    define('XITOOLSDEBUG_FORMAT_DUMP', "%t [%M] %c %l %d");
}
if (!defined('XITOOLSDEBUG_FORMAT_BACKTRACE')) {
    define('XITOOLSDEBUG_FORMAT_BACKTRACE', "%t [%M] %c Backtrace:\n%d");
}
if (!defined('XITOOLSDEBUG_OUTPUT_DIR')) {
    if (defined('BP')) {
        define('XITOOLSDEBUG_OUTPUT_DIR', BP . '/var/log');
    } else {
        define('XITOOLSDEBUG_OUTPUT_DIR', realpath(__DIR__));
    }
}

class Debug
{
    const TAB = '    ';

    protected static $contextVars;

    public static $useGetterDumpClasses = [
        'Magento\Framework\View\File',
        'Magento\Framework\Component\ComponentFile'
    ];

    /**
     * Dumps given $value.
     *
     * @param mixed $value
     * @param int $maxDepth
     * @param bool|string $file
     * @param string $label
     */
    public static function dump($value, $maxDepth = 8, $file = false, $label = '')
    {
        $vars = self::getFormatVars($label) + [
                'c' => self::getContextAsString([__CLASS__, __FUNCTION__]),
            ];
        self::$contextVars =& $vars;

        ob_start();
        self::dumpRecursive($value, 0, $maxDepth + 1, $file ? false : true);
        $vars['d'] = ob_get_clean();

        $callback = function ($matches) use ($vars) {
            return self::replaceVars($matches, $vars);
        };

        $format = XITOOLSDEBUG_FORMAT_DUMP;
        $output = preg_replace_callback('/(%(.))/', $callback, $format);

        if (!$file) {
            echo PHP_EOL . (PHP_SAPI == 'cli' ? $output : "<pre>$output</pre>");
        } else {
            file_put_contents(
                XITOOLSDEBUG_OUTPUT_DIR . '/' . ((string)$file),
                $output . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    public static function vardump($value, $maxDepth = 8, $file = false, $label = '', $addContext = true)
    {
        $vars = array(
            't' => date('c'),
            'c' => $addContext ? self::getContextAsString(array(__CLASS__, __FUNCTION__)) : ' ',
            'l' => "$label",
            'm' => memory_get_usage(true) . ' B',
            'M' => sprintf('%.2f', round(memory_get_usage(true) / 1024 / 1024, 2)) . ' MB'
        );
        self::$contextVars =& $vars;

        // Prepare var_dump() output
        $xdebugVarDisplayMaxDepth = ini_get('xdebug.var_display_max_depth');
        $htmlErrors = ini_get('html_errors');
        ini_set('xdebug.var_display_max_depth', $maxDepth);
        ini_set('html_errors', 0);

        ob_start();
        var_dump($value);
        $vars['d'] = ob_get_clean();

        // Restore configuration
        ini_set('html_errors', $htmlErrors);
        ini_set('xdebug.var_display_max_depth', $xdebugVarDisplayMaxDepth);

        $callback = function ($matches) use ($vars) {
            return self::replaceVars($matches, $vars);
        };

        $format = XITOOLSDEBUG_FORMAT_DUMP;
        $output = preg_replace_callback('/(%(.))/', $callback, $format);

        if (!$file) {
            echo PHP_EOL . (PHP_SAPI == 'cli' ? $output : "<pre>$output</pre>");
        } else {
            file_put_contents(
                XITOOLSDEBUG_OUTPUT_DIR . '/' . ((string)$file),
                $output . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    /**
     *
     * @param mixed $to
     *          String for functions:   "myFunction"
     *          Arrays for methods:     array($object, "myMethod")
     *                                  array("MyClass", "myStaticMethod")
     * @return string
     */
    public static function getContextAsString($to)
    {
        $target = null;
        try {
            $backtrace = debug_backtrace();

            //Reach target
            for ($i = 0; $i < count($backtrace); $i++) {
                $b = $backtrace[$i];
                if (self::isTargetContext($b, $to)) {
                    if (isset($backtrace[$i + 1])) {
                        $target = $backtrace[$i + 1];
                        $target['file'] = $backtrace[$i]['file'];
                        $target['line'] = $backtrace[$i]['line'];
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
        }

        if (null !== $target) {
            return self::formatContext($target);
        } else {
            return '{Unknown Context}';
        }
    }

    protected static function isTargetContext($context, $target)
    {
        if (is_string($target)) {
            $target = array('function' => $target);
        } else {
            if (is_array($target)) {
                if (is_string($target[0]) && is_string($target[1])) {
                    $target['class'] = $target[0];
                    $target['type'] = '::';
                    $target['function'] = $target[1];
                }
                if (is_object($target[0]) && is_string($target[1])) {
                    $target['class'] = get_class($target[0]);
                    $target['type'] = '->';
                    $target['function'] = $target[1];
                }
            }
        }

        $functionsMatch = false;
        $classesMatch = false;
        $typesMatch = false;

        if (is_array($context)) {
            if ($context['function'] == $target['function']) {
                $functionsMatch = true;
            }
            if (isset($target['class']) && isset($context['class'])) {
                if ($context['class'] == $target['class']) {
                    $classesMatch = true;
                }
            } else {
                $classesMatch = true;
            }
            if (isset($target['type']) && isset($context['type'])) {
                if ($context['type'] == $target['type']) {
                    $typesMatch = true;
                }
            } else {
                $typesMatch = true;
            }
        }

        return $functionsMatch && $classesMatch && $typesMatch;
    }

    protected static function formatContext($context)
    {
        $rootDir = defined('BP') ? BP : '';
        $file = $context['file'];
        if ($rootDir && 0 === strpos($file, $rootDir)) {
            $file = substr($file, strlen($rootDir));
        }

        $out = $file . ' [line ' . $context['line'] . '] ';
        if (isset($context['class'])) {
            $out .= $context['class'] . $context['type'];
        }
        $out .= $context['function'] . '(';

        //TODO args

        $out .= ')';

        return $out;
    }

    protected static function dumpRecursive($value, $currentLevel, $maxDepth, $toHtml = false)
    {
        if ($currentLevel == -1) {
            return $toHtml ? htmlspecialchars($value) : $value;
        }

        $type = gettype($value);
        echo $type;

        // String
        if ($type == 'string') {
            echo '(' . strlen($value) . ')';
            $value = '"' . self::padLeft(self::dumpRecursive($value, -1, $maxDepth), $currentLevel + 1) . '"';
        } elseif ($type == 'boolean') {  // Boolean
            $value = ($value ? 'true' : 'false');
        } elseif ($type == 'object') {   // Object
            $props = get_class_vars(get_class($value));
            echo '(' . count($props) . ') <u>' . get_class($value) . '</u>';
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '{';

            if ($currentLevel >= $maxDepth) {
                echo self::padLeft("\n{DUMP_STOPPED}", $currentLevel + 1);
                echo self::padLeft("\n}", $currentLevel);
                return '';
            } else {
                self::dumpArray($props, $currentLevel + 1, $maxDepth);
                self::dumpObject($value, $currentLevel + 1, $maxDepth);
                echo self::padLeft("\n}", $currentLevel);

                $value = '';
            }
        } elseif ($type == 'array') {  // Array
            echo '(' . count($value) . ')';
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '{';

            if ($currentLevel >= $maxDepth) {
                echo self::padLeft("\n{DUMP_STOPPED}", $currentLevel + 1);
                echo self::padLeft("\n}", $currentLevel);
                return '';
            } else {
                self::dumpArray($value, $currentLevel + 1, $maxDepth);
                echo "\n" . str_repeat(self::TAB, $currentLevel) . '}';
                $value = '';
            }
        }

        echo " $value";
    }

    protected static function padLeft($text, $tabs = 0)
    {
        $replace = "\n" . str_repeat(self::TAB, $tabs);
        return str_replace(array("\n", "\r\n"), $replace, $text);
    }

    protected static function dumpArray($array, $currentLevel, $maxDepth)
    {
        // 1st loop: find longest key
        $longestKeyLength = 1;
        foreach ($array as $key => $val) {
            $longestKeyLength = max(strlen((string)$key), $longestKeyLength);
        }
        $baseIndentation = $longestKeyLength + 3;

        //2nd loop: do dump
        foreach ($array as $key => $val) {
            $tabs = str_repeat(' ', $baseIndentation - (strlen((string) $key) + 2));
            echo self::padLeft("\n[$key]$tabs=> ", $currentLevel);
            self::dumpRecursive($val, $currentLevel + 1, $maxDepth);
        }
    }

    protected static function dumpObject($value, $currentLevel, $maxDepth)
    {
        /* Exception */
        if ($value instanceof \Exception) {
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '*** Exception ***';
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '{';
            echo "\n" . str_repeat(self::TAB, $currentLevel + 1) . '[ Code    ]'
                . str_repeat(self::TAB, 1) . ' => ' . self::padLeft($value->getCode(), $currentLevel + 3);
            echo "\n" . str_repeat(self::TAB, $currentLevel + 1) . '[ Message ]'
                . str_repeat(self::TAB, 1) . ' => ' . self::padLeft($value->getMessage(), $currentLevel + 3)
                . '';
            echo "\n" . str_repeat(self::TAB, $currentLevel + 1) . '[ Trace   ]'
                . str_repeat(self::TAB, 1) . ' => ' . self::padLeft($value->getTraceAsString(), $currentLevel + 3);
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '}';
        }

        /* \Magento\Framework\DataObject */
        if ($value instanceof \Magento\Framework\DataObject) {
            echo "\n" . str_repeat(self::TAB, $currentLevel)
                . '*** Magento\Framework\DataObject::_data ***';
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '{';
            self::dumpArray($value->getData(), $currentLevel + 1, $maxDepth);
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '}';
        }

        /* Classes supporting getter dump */
        if (self::shouldUseGetterDump($value)) {
            $class = trim(get_class($value), '\\');
            echo "\n" . str_repeat(self::TAB, $currentLevel)
                . "*** $class::{data via getters} ***";
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '{';
            self::dumpArray(self::getterDump($value), $currentLevel + 1, $maxDepth);
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '}';
        }

        /* \Magento\Framework\Data\Collection */
        if ($value instanceof \Magento\Framework\Data\Collection) {
            $countItems = $value->isLoaded() ? $value->count() : 0;
            echo "\n" . str_repeat(self::TAB, $currentLevel)
                . "*** Magento\\Framework\\Data\\Collection::_items ($countItems) ***";
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '{';

            if (!$value->isLoaded()) {
                echo "\n" . str_repeat(self::TAB, $currentLevel + 1)
                    . '[NOT LOADED] Load collection explicitly if you want to dump items.';
            } else {
                self::dumpArray($value->getItems(), $currentLevel + 1, $maxDepth);
            }
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '}';

            /* Varien_Data_Collection_Db */
            if ($value instanceof \Magento\Framework\Data\Collection\AbstractDb) {
                echo "\n" . str_repeat(self::TAB, $currentLevel)
                    . '*** Magento\\Framework\\Data\\Collection\\AbstractDb::_select ***';
                echo "\n" . str_repeat(self::TAB, $currentLevel) . '{';
                $sql = $value->getSelect() ? $value->getSelect()->assemble() : '{none}';
                echo "\n" . str_repeat(self::TAB, $currentLevel + 1) . '[ SQL ]' . str_repeat(self::TAB, 2)
                    . ' => ' . self::padLeft($sql, $currentLevel + 2);
                echo "\n" . str_repeat(self::TAB, $currentLevel) . '}';
            }
        }

        /* Zend_Db_Select */
        if ($value instanceof \Zend_Db_Select) {
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '*** Zend_Db_Select ***';
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '{';
            $sql = $value->assemble();
            echo "\n" . str_repeat(self::TAB, $currentLevel + 1) . '[ SQL ]' . str_repeat(self::TAB, 2)
                . ' => ' . self::padLeft($sql, $currentLevel + 2);
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '}';
        }

        /* SimpleXMLElement */
        if ($value instanceof \SimpleXMLElement) {
            echo "\n" . str_repeat(self::TAB, $currentLevel) . '*** SimpleXMLElement *** ';
            self::dumpRecursive(self::toArray($value), $currentLevel, $maxDepth);
        }
    }

    protected static function toArray($object)
    {
        $return = array();
        if ($object instanceof \SimpleXMLElement) {
            $tmp = (array)$object;
            foreach ($tmp as $name => $node) {
                $return[$name] = self::toArray($node);
            }
        } elseif (is_array($object)) {
            foreach ($object as $name => $node) {
                $return[$name] = self::toArray($node);
            }
        } else {
            $return = (string)$object;
        }
        return $return;
    }

    protected static function replaceVars($matches, $vars = null)
    {
        if (isset($matches[2])) {
            $v = $matches[2];
            if (isset($vars[$v])) {
                return $vars[$v];
            } else {
                if ($v == '%') {
                    return '%';
                }
            }
        }
        return $matches[0];
    }

    /**
     * Prints call stacktrace to output or file.
     *
     * @param $file boolean|string
     */
    public static function debugBacktrace($file = false)
    {
        $vars = self::getFormatVars() + [
                'c' => self::getContextAsString([__CLASS__, __FUNCTION__]),
            ];
        self::$contextVars =& $vars;

        $e = new \Exception();
        $trace = $e->getTraceAsString();

        // Remove first line (the method we're in)
        $trace = substr($trace, strpos($trace, "\n") + 1);

        ob_start();
        echo $trace;

        $vars['d'] = ob_get_clean();

        $callback = function ($matches) use ($vars) {
            return self::replaceVars($matches, $vars);
        };

        $format = XITOOLSDEBUG_FORMAT_BACKTRACE;
        $output = preg_replace_callback('/(%(.))/', $callback, $format);

        if (!$file) {
            echo PHP_EOL . (PHP_SAPI == 'cli' ? $output : "<pre>$output</pre>");
        } else {
            file_put_contents(
                XITOOLSDEBUG_OUTPUT_DIR . '/' . ((string)$file),
                $output . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    protected static function shouldUseGetterDump($object)
    {
        $should = false;
        if (is_object($object)) {
            foreach (self::$useGetterDumpClasses as $class) {
                if (is_a($object, $class)) {
                    $should = true;
                    break;
                }
            }
        }
        return $should;
    }

    protected static function getterDump($object, $prefix = 'get')
    {
        $result = [];
        foreach (get_class_methods($object) as $method) {
            if (strpos($method, $prefix) === 0) {
                try {
                    $val = $object->$method();
                } catch (\Exception $e) {
                    $val = '{{ ERROR! ' . $e->getMessage() . '}}';
                }
                $result[substr($method, strlen($prefix))] = $val;
            }
        }
        return $result;
    }

    protected static function getFormatVars($label = '') {
        $remoteIp = $_SERVER['REMOTE_ADDR'] ?? null;
        $forwardedForIp = $_SERVER['X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        return [
            't' => date('c'),
            'l' => "$label",
            'm' => memory_get_usage(true) . ' B',
            'M' => sprintf('%.2f', round(memory_get_usage(true) / 1024 / 1024, 2)) . ' MB',
            'i' => $remoteIp ?? '<no-ip>',
            'I' => strpos($remoteIp, '127.') !== 0 ? $remoteIp : $forwardedForIp,
            'f' => $forwardedForIp ?? '<no-ip>',
            'u' => $_SERVER['REQUEST_URI'] ?? ''
        ];
    }
}

// phpcs:enable
