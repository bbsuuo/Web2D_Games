<?php

function processDirectory($directory, $outputBaseDir, $scriptPath) {
    // 使用 realpath() 确保脚本路径是绝对路径
    $scriptPath = realpath($scriptPath);

    echo "检查脚本路径: {$scriptPath}\n";

    if (!$scriptPath) {
        echo "错误：找不到脚本路径 {$scriptPath}\n";
        exit(1); // 退出程序
    }
    
        // 确保目录路径统一格式（移除末尾的斜杠并确保正确的路径分隔符）
    $normalizedSourceDirectory = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $directory), DIRECTORY_SEPARATOR);

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        $filePath = $file->getPathname();
        $fileName = $file->getFilename();
        
        // 检查文件是否为.ftx后缀
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'ftx') {
            // $fileRealtivePath = $file->getPath();
            // echo "1. fileRealtivePath $fileRealtivePath \n";
            // $filePathNormalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file->getPath());
            // echo "2.filePathNormalized $filePathNormalized \n";
 
            // echo "3.normalizedSourceDirectory $normalizedSourceDirectory \n";
            // //$replacePath =
            // continue;
            $filePathNormalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file->getPath());
            // echo "1. filePathNormalized $filePathNormalized \n";
            $relativePath = str_replace($normalizedSourceDirectory . DIRECTORY_SEPARATOR, '', $filePathNormalized);
            // echo "2. relativePath $relativePath \n";
            $outputDir = $outputBaseDir . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);
            echo "3. outputDir $outputDir \n";
 
            // 确保输出目录存在
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // 构造命令以处理.ftx文件
            $command = "php \"{$scriptPath}\" \"{$filePath}\" \"{$outputDir}\"";
            echo "执行命令: $command\n";
            exec($command, $output, $return_var);
            echo "执行结果：\n";
            echo "返回状态: $return_var\n";
            
        }
    }
}

// 从命令行参数获取要处理的目录
$sourceDirectory = $argv[1] ?? null;
$outputBaseDirectory = $argv[2] ?? null;

if (!$sourceDirectory || !$outputBaseDirectory) {
    echo "使用方法: php process_ftx_files.php <sourceDirectory> <outputBaseDirectory>\n";
    exit(1);
}

// nswit_unicorn_FTEX.php脚本的路径
$scriptPath = __DIR__ . '/../psxtools/nswit_unicorn_FTEX.php';

// 处理目录
processDirectory($sourceDirectory, $outputBaseDirectory, $scriptPath);

//使用方式 : 文件路径 输出路径