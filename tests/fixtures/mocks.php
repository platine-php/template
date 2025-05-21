<?php

declare(strict_types=1);

namespace Platine\Template\Cache;

$mock_extension_loaded_to_false = false;
$mock_extension_loaded_to_true = false;
$mock_ini_get_to_false = false;
$mock_ini_get_to_true = false;
$mock_apcu_fetch_to_false = false;
$mock_apcu_store_to_false = false;
$mock_apcu_store_to_true = false;
$mock_apcu_delete_to_false = false;
$mock_apcu_delete_to_true = false;
$mock_apcu_clear_cache_to_false = false;
$mock_apcu_clear_cache_to_true = false;
$mock_apcu_exists_to_false = false;
$mock_apcu_exists_to_true = false;
$mock_realpath_to_false = false;
$mock_realpath_to_same = false;
$mock_glob = false;
$mock_filemtime_to_int = false;
$mock_filemtime_to_false = false;
$mock_time = false;
$mock_time_to_zero = false;
$mock_file_exists = false;
$mock_unlink_to_false = false;
$mock_unlink_to_true = false;
$mock_is_writable_to_false = false;
$mock_is_writable_to_true = false;
$mock_file_get_contents_to_false = false;
$mock_file_put_contents_to_false = false;
$mock_file_get_contents_to_data = false;
$mock_file_get_contents_to_data_serialize = false;
$mock_unserialize_to_false = false;
$mock_touch_to_false = false;

function apcu_exists($key): bool
{
    global $mock_apcu_exists_to_false, $mock_apcu_exists_to_true;
    if ($mock_apcu_exists_to_false) {
        return false;
    } elseif ($mock_apcu_exists_to_true) {
        return true;
    }

    return false;
}

function realpath(string $key)
{
    global $mock_realpath_to_false, $mock_realpath_to_same;
    if ($mock_realpath_to_false) {
        return false;
    } elseif ($mock_realpath_to_same) {
        return $key;
    }

    return \realpath($key);
}

function is_writable(string $key)
{
    global $mock_is_writable_to_false, $mock_is_writable_to_true;
    if ($mock_is_writable_to_false) {
        return false;
    } elseif ($mock_is_writable_to_true) {
        return true;
    }

    return \is_writable($key);
}

function apcu_clear_cache(): bool
{
    global $mock_apcu_clear_cache_to_false, $mock_apcu_clear_cache_to_true;
    if ($mock_apcu_clear_cache_to_false) {
        return false;
    } elseif ($mock_apcu_clear_cache_to_true) {
        return true;
    }

    return false;
}

/**
 * @return null|string
 */
function apcu_fetch($key, bool &$success)
{
    global $mock_apcu_fetch_to_false;
    if ($mock_apcu_fetch_to_false) {
        $success = false;
    } else {
        $success = true;
        return md5($key);
    }
}

function apcu_store($key, $var, int $ttl = 0): bool
{
    global $mock_apcu_store_to_false, $mock_apcu_store_to_true;
    if ($mock_apcu_store_to_false) {
        return false;
    } elseif ($mock_apcu_store_to_true) {
        return true;
    }

    return false;
}

function apcu_delete($key): bool
{
    global $mock_apcu_delete_to_false, $mock_apcu_delete_to_true;
    if ($mock_apcu_delete_to_false) {
        return false;
    } elseif ($mock_apcu_delete_to_true) {
        return true;
    }

    return false;
}

function extension_loaded(string $name): bool
{
    global $mock_extension_loaded_to_false, $mock_extension_loaded_to_true;
    if ($mock_extension_loaded_to_false) {
        return false;
    } elseif ($mock_extension_loaded_to_true) {
        return true;
    } else {
        return \extension_loaded($name);
    }
}

/**
 * @return bool|string
 */
function ini_get(string $option)
{
    global $mock_ini_get_to_true, $mock_ini_get_to_false;
    if ($mock_ini_get_to_false) {
        return false;
    } elseif ($mock_ini_get_to_true) {
        return true;
    } else {
        return \ini_get($option);
    }
}

function unserialize(string $data, array $options = [])
{
    global $mock_unserialize_to_false;
    if ($mock_unserialize_to_false) {
        return false;
    } else {
        return \unserialize($data, $options);
    }
}


/**
 * @return false|string
 */
function file_get_contents(string $filename)
{
    global $mock_file_get_contents_to_false,
            $mock_file_get_contents_to_data,
            $mock_file_get_contents_to_data_serialize;
    if ($mock_file_get_contents_to_false) {
        return false;
    } elseif ($mock_file_get_contents_to_data) {
        return 'foobar';
    } elseif ($mock_file_get_contents_to_data_serialize) {
        return serialize('foobar');
    } else {
        return \file_get_contents($filename);
    }
}

/**
 * @return false|int
 */
function file_put_contents(string $filename, $data)
{
    global $mock_file_put_contents_to_false;
    if ($mock_file_put_contents_to_false) {
        return false;
    } else {
        return \file_put_contents($filename, $data);
    }
}

function touch(string $filename, int $time): bool
{
    global $mock_touch_to_false;
    if ($mock_touch_to_false) {
        return false;
    } else {
        return \touch($filename, $time);
    }
}

function unlink(string $filename, $context = null): bool
{
    global $mock_unlink_to_false, $mock_unlink_to_true;
    if ($mock_unlink_to_false) {
        return false;
    } elseif ($mock_unlink_to_true) {
        return true;
    } else {
        return \unlink($filename);
    }
}

function file_exists(string $filename): bool
{
    global $mock_file_exists;
    if ($mock_file_exists) {
        return true;
    } else {
        return \file_exists($filename);
    }
}

function time(): int
{
    global $mock_time, $mock_time_to_zero;
    if ($mock_time) {
        return 10000000;
    } elseif ($mock_time_to_zero) {
        return 0;
    } else {
        return \time();
    }
}

/**
 * @return false|int
 */
function filemtime(string $filename)
{
    global $mock_filemtime_to_int, $mock_filemtime_to_false;
    if ($mock_filemtime_to_int) {
        return 1000;
    } elseif ($mock_filemtime_to_false) {
        return false;
    } else {
        return \filemtime($filename);
    }
}

/**
 * @return false|string[]
 *
 * @psalm-return false|list<string>
 */
function glob(string $pattern, int $flags = 0)
{
    global $mock_glob;
    if ($mock_glob) {
        return array('file1', 'file2');
    } else {
        return \glob($pattern, $flags);
    }
}

namespace Platine\Template\Loader;

$mock_realpath_to_false = false;
$mock_realpath_to_same = false;


function realpath(string $key)
{
    global $mock_realpath_to_false, $mock_realpath_to_same;
    if ($mock_realpath_to_false) {
        return false;
    } elseif ($mock_realpath_to_same) {
        return $key;
    }

    return \realpath($key);
}


namespace Platine\Template\Filter;

$mock_function_exists_to_false = false;
$mock_function_exists_to_true = false;
$mock_mb_strlen = false;
$mock_mb_strtoupper = false;
$mock_mb_strtolower = false;


function function_exists(string $key)
{
    global $mock_function_exists_to_false, $mock_function_exists_to_true;
    if ($mock_function_exists_to_false) {
        return false;
    } elseif ($mock_function_exists_to_true) {
        return true;
    }

    return \function_exists($key);
}

function mb_strlen(string $key)
{
    global $mock_mb_strlen;
    if ($mock_mb_strlen) {
        return $key;
    }

    return \mb_strlen($key);
}

function mb_strtoupper(string $key)
{
    global $mock_mb_strtoupper;
    if ($mock_mb_strtoupper) {
        return $key;
    }

    return \mb_strtoupper($key);
}

function mb_strtolower(string $key)
{
    global $mock_mb_strtolower;
    if ($mock_mb_strtolower) {
        return $key;
    }

    return \mb_strtolower($key);
}

namespace Platine\Template\Parser;

$mock_preg_split_to_false = false;
$mock_explode_false = false;

function preg_split(string $pattern, string $subject, int $limit = -1, int $flags = 0)
{
    global $mock_preg_split_to_false;
    if ($mock_preg_split_to_false) {
        return false;
    }

    return \preg_split($pattern, $subject, $limit, $flags);
}

function explode(string $separator, string $string, int $limit = PHP_INT_MAX)
{
    global $mock_explode_false;
    if ($mock_explode_false) {
        return false;
    }

    return \explode($separator, $string, $limit);
}
