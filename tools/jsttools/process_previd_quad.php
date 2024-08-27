<?php

function processPredivFiles($inputDirectory) {
    // 指定处理文件的脚本路径
    $scriptPath = __DIR__ . '/../psxtools/quad_prediv.php';

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

        // 检查文件是否为.png后缀
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'png') {
            // 获取不包含序号和.nvt的基本文件名
            $baseName = preg_replace('/\.\d+\.nvt$/', '', pathinfo($fileName, PATHINFO_FILENAME));
            // 寻找对应的.prediv.quad文件
            $predivFilePathPattern = $file->getPath() . DIRECTORY_SEPARATOR . $baseName . '.mbs.v55.prediv.quad';

            if (glob($predivFilePathPattern)) {
                $predivFilePath = glob($predivFilePathPattern)[0]; // 假设只有一个匹配的文件
                // 构造命令以处理文件
                $command = "php \"{$scriptPath}\" \"{$filePath}\" \"{$predivFilePath}\"";
                echo "执行命令: $command\n";
                exec($command, $output, $returnVar);
                echo "执行结果：\n";
                echo "返回状态: $returnVar\n";
            } else {
                echo "警告：找不到对应的.prediv.quad文件 {$predivFilePathPattern}\n";
            }
        }
    }
}

// 从命令行参数获取要处理的目录
$inputDirectory = $argv[1] ?? null;

if (!$inputDirectory) {
    echo "使用方法: php " . basename(__FILE__) . " <inputDirectory>\n";
    exit(1);
}

// 处理目录
processPredivFiles($inputDirectory);
