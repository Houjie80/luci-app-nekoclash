<?php
function logMessage($message) {
    $logFile = '/var/log/mihomo_update.log';
    $timestamp = date('Ymd H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

$latest_version = 'neko_v1.18.1';
$current_version = '';
$install_path = '/etc/neko/core/mihomo';
$temp_file = '/tmp/mihomo.gz';

if (file_exists($install_path)) {
    $current_version = shell_exec("{$install_path} --version");
    logMessage("Current version: $current_version");
} else {
    logMessage("The current version file does not exist and will be considered as not installed.");
}

$current_arch = shell_exec("uname -m");
$current_arch = trim($current_arch);

$download_url = '';
switch ($current_arch) {
    case 'aarch64':
        $download_url = 'https://github.com/Thaolga/neko/releases/download/core_neko/mihomo-linux-arm64-neko.gz';
        break;
    case 'armv7l':
        $download_url = 'https://github.com/Thaolga/neko/releases/download/core_neko/mihomo-linux-armv7l-neko.gz';
        break;
    case 'x86_64':
        $download_url = 'https://github.com/Thaolga/neko/releases/download/core_neko/mihomo-linux-amd64-neko.gz';
        break;
    default:
        logMessage("No download link found for the appropriate architecture: $current_arch");
        echo "No download link found for the appropriate architecture: $current_arch";
        exit;
}

logMessage("Latest version: $latest_version");
logMessage("Current architecture: $current_arch");
logMessage("Download link: $download_url");

if (trim($current_version) === trim($latest_version)) {
    logMessage("The current version is the latest version, no need to update.");
    echo "The current version is the latest version.";
    exit;
}

logMessage("Start downloading core update...");
exec("wget ​​-O '$temp_file' '$download_url' 2>&1", $output, $return_var);
logMessage("wget ​​output: " . implode("\n", $output));
logMessage("wget ​​return value: $return_var");

if ($return_var === 0) {
    $temp_unzip_file = '/tmp/mihomo-linux-arm64-neko';

    logMessage("decompression command: gzip -d -c '$temp_file' > '$temp_unzip_file'");
    exec("gzip -d -c '$temp_file' > '$temp_unzip_file' 2>&1", $output, $return_var);
    logMessage("Decompression output: " .implode("\n", $output));
    logMessage("decompression return value: $return_var");

    if ($return_var === 0) {
        logMessage("Rename file: mv '$temp_unzip_file' '$install_path'");
        exec("mv '$temp_unzip_file' '$install_path' 2>&1", $output, $return_var);
        logMessage("Rename output: " .implode("\n", $output));
        logMessage("Rename return value: $return_var");

        if ($return_var === 0) {
            exec("chmod 0755 '$install_path'", $output, $return_var);
            logMessage("Set permission command: chmod 0755 '$install_path'");
            logMessage("Set permission return value: $return_var");

            if ($return_var === 0) {
                logMessage("Core update completed! Current version: $latest_version");
                echo "Update completed! Current version: $latest_version";
            } else {
                logMessage("Failed to set permissions!");
                echo "Failed to set permissions!";
            }
        } else {
            logMessage("Failed to rename the file, return value: $return_var");
            echo "Failed to rename file!";
        }
    } else {
        logMessage("Decompression failed, return value: $return_var");
        echo "Decompression failed!";
    }
} else {
    logMessage("Download failed, return value: $return_var");
    echo "Download failed!";
}

if (file_exists($temp_file)) {
    unlink($temp_file);
    logMessage("Cleaning temporary files: $temp_file");
}
?>
