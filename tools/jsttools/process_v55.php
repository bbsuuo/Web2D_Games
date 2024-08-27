<?php

function processV55Files($inputDirectory) {
    // 指定处理.v55文件的脚本路径
    $scriptPath = __DIR__ . '/../psxtools/quad_vanillaware_v55.php';

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

        // 检查文件是否为.v55后缀
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'v55') {
            // 构造命令以处理.v55文件
            $command = "php \"{$scriptPath}\" \"{$filePath}\"";
            echo "执行命令: $command\n";
            exec($command, $output, $returnVar);
            echo "执行结果：\n";
            echo "返回状态: $returnVar\n";
        }
    }
}

// 从命令行参数获取要处理的目录
$inputDirectory = $argv[1] ?? null;

if (!$inputDirectory) {
    echo "使用方法: php " . basename(__FILE__) . " <inputDirectory>\n";
    exit(1);
}

//注意 这个脚本没有输出路径 他只在原路径下生成
// 处理目录
processV55Files($inputDirectory);
