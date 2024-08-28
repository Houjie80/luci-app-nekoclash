<?php 
function logMessage($message) { 
    $logFile = '/var/log/sing-box_update.log'; 
    $timestamp = date('Y-m-d H:i:s'); 
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND); 
} 

function writeVersionToFile($version) { 
    $versionFile = '/etc/neko/core/version.txt'; 
    $result = file_put_contents($versionFile, $version); 
    if ($result === false) { 
        logMessage("Unable to write version file: $versionFile"); 
        logMessage("Check if the path exists and that the PHP process has write permissions."); 
    } else { 
        logMessage("Successfully wrote version file: $versionFile"); 
    } 
} 

$latest_version = '1.10.0-beta.4'; 
$current_version = ''; 
$install_path = '/usr/bin/sing-box'; 
$temp_file = '/tmp/sing-box.tar.gz'; 
$temp_dir = '/tmp/singbox_temp'; 

if (file_exists($install_path)) { 
    $current_version = trim(shell_exec("{$install_path} --version")); 
    logMessage("Current version: $current_version"); 
} else { 
    logMessage("The current version file does not exist and will be considered not installed."); 
} 

$current_arch = trim(shell_exec("uname -m")); 

$download_url = ''; 
switch ($current_arch) { 
    case 'aarch64': 
        $download_url = 'https://github.com/SagerNet/sing-box/releases/download/v1.10.0-beta.4/sing-box-1.10.0-beta.4-linux-arm64.tar.gz'; 
        break; 
    case 'x86_64': 
        $download_url = 'https://github.com/SagerNet/sing-box/releases/download/v1.10.0-beta.4/sing-box-1.10.0-beta.4-linux-amd64.tar.gz'; 
        break; 
    default: 
        logMessage("Download link not found for architecture: $current_arch"); 
        echo "Download link not found for architecture: $current_arch"; 
        exit; 
} 

logMessage("Latest version: $latest_version"); 
logMessage("Current architecture: $current_arch"); 
logMessage("Download link: $download_url"); 

if (trim($current_version) === trim($latest_version)) { 
    logMessage("The current version is already the latest version, no need to update."); 
    echo "The current version is already the latest version. "; 
    exit; 
} 

logMessage("Starting to download core update..."); 
exec("wget ​​-O '$temp_file' '$download_url'", $output, $return_var); 
logMessage("wget ​​return value: $return_var"); 

if ($return_var === 0) { 
    if (!is_dir($temp_dir)) { 
        logMessage("Creating temporary decompression directory: $temp_dir"); 
        mkdir($temp_dir, 0755,true); 
    } else { 
        logMessage("Temporary decompression directory already exists: $temp_dir"); 
    } 

    logMessage("Decompression command: tar -xzf '$temp_file' -C '$temp_dir'"); 
    exec("tar -xzf '$temp_file' -C '$temp_dir'", $output, $return_var);
    logMessage("Decompression return value: $return_var"); 

        logMessage("File list after decompression:"); 
        exec("ls -lR '$temp_dir'", $output); 
        logMessage(implode("\n", $output)); 

        $extracted_file = glob("$temp_dir/sing-box-*/*sing-box")[0] ?? ''; 
        if ($extracted_file && file_exists($extracted_file)) { 
            logMessage("Move file command: cp -f '$extracted_file' '$install_path'"); 
            exec("cp -f '$extracted_file' '$install_path'", $output, $return_var); 
            logMessage("Replace file return value: $return_var"); 

            if ($return_var === 0) { 
                exec("chmod 0755 '$install_path'", $output, $return_var); 
                logMessage("Set permission command: chmod 0755 '$install_path'"); 
                logMessage("Set permission return value: $return_var"); 

                if ($return_var === 0) { 
                    logMessage("Core update completed! Current version: $latest_version"); 
                    writeVersionToFile($latest_version); 
                    echo "Update completed! Current version: $latest_version"; 
                } else { 
                    logMessage("Set permission failed!"); 
                    echo "Set permission failed!"; 
                } 
            } else { 
                logMessage("Replace file failed, return value: $return_var"); 
                echo "Replace file failed!"; 
            } 
        } else { 
            logMessage("The decompressed file 'sing-box' does not exist."); 
            echo "The decompressed file 'sing-box' does not exist."; 
        } 
    } else { 
        logMessage("Decompression failed, return value: $return_var"); 
        echo "Decompression failed!"; 
    } 
} else { 
    logMessage("Download failed, return value: $return_var"); 
    echo "Download failed! "; 
} 

if (file_exists($temp_file)) { 
    unlink($temp_file); 
    logMessage("Clean up temporary files: $temp_file"); 
} 
if (is_dir($temp_dir)) { 
    exec("rm -r '$temp_dir'"); 
    logMessage("Clean up temporary decompression directory: $temp_dir"); 
} 
?>
