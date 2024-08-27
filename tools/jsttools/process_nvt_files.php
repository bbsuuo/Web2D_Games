<?php

function processNVTFiles($inputDirectory, $outputFormat) {
    // 确定使用哪个脚本处理文件，基于输出格式
    $scriptFileName = $outputFormat === 'bmp' ? 'img_clut2bmp.php' : 'img_clut2png.php';
    $scriptPath = __DIR__ . '/../psxtools/' . $scriptFileName;

    echo "使用脚本路径: {$scriptPath}\n";

    if (!file_exists($scriptPath)) {
        echo "错误：找不到处理脚本 {$scriptPath}\n";
        exit(1);
    }

    // 确保目录路径统一格式（移除末尾的斜杠并确保正确的路径分隔符）
    $normalizedInputDirectory = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $inputDirectory), DIRECTORY_SEPARATOR);

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($inputDirectory));
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        $filePath = $file->getPathname();
        $fileName = $file->getFilename();
        
        // 检查文件是否为.nvt后缀
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'nvt') {
            // 构造命令以处理.nvt文件
            $command = "php \"{$scriptPath}\" \"{$filePath}\"";
            echo "执行命令: $command\n";
            exec($command, $output, $returnVar);
            echo "执行结果：\n";
            echo "返回状态: $returnVar\n";
        }
    }
}

// 从命令行参数获取要处理的目录和输出格式
$inputDirectory = $argv[1] ?? null;
$outputFormat = $argv[2] ?? null;

if (!$inputDirectory || !$outputFormat || !in_array($outputFormat, ['bmp', 'png'])) {
    echo "使用方法: php process_nvt_files.php <inputDirectory> <outputFormat: bmp/png>\n";
    exit(1);
}

// 处理目录
processNVTFiles($inputDirectory, $outputFormat);

 
//使用方式 : 文件路径  输出格式