<?php

function processDirectory($directory, $outputBaseDir, $scriptPath, $gameId = 'swi_unic') {
    // 确保脚本路径是绝对路径
    $scriptPath = realpath($scriptPath);

    echo "检查脚本路径: {$scriptPath}\n";

    if (!$scriptPath) {
        echo "错误：找不到脚本路径 {$scriptPath}\n";
        exit(1);
    }

    $normalizedSourceDirectory = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $directory), DIRECTORY_SEPARATOR);

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;

        $filePath = $file->getPathname();
        $fileName = $file->getFilename();

        // 检查文件是否为.MBP或.MBS后缀
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($extension === 'mbp' || $extension === 'mbs') {
            $filePathNormalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file->getPath());
            $relativePath = str_replace($normalizedSourceDirectory . DIRECTORY_SEPARATOR, '', $filePathNormalized);
            $outputDir = $outputBaseDir . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);

            // 确保输出目录存在
            if (!file_exists($outputDir)) mkdir($outputDir, 0755, true);

            // 构造命令以处理.MBP或.MBS文件
            $command = "php \"{$scriptPath}\" \"{$gameId}\" \"{$filePath}\" \"{$outputDir}\"";
            echo "执行命令: $command\n";
            exec($command, $output, $return_var);
            echo "执行结果：\n";
            echo implode("\n", $output);
            echo "返回状态: $return_var\n";
        }
    }
}

$sourceDirectory = $argv[1] ?? null;
$outputBaseDirectory = $argv[2] ?? null;
$gameId = $argv[3] ?? 'swi_unic';

if (!$sourceDirectory || !$outputBaseDirectory) {
    echo "使用方法: php process_mbp_mbs_files.php <sourceDirectory> <outputBaseDirectory> [gameId]\n";
    exit(1);
}

$scriptPath = __DIR__ . '/../psxtools/quad_vanillaware_FMBP_FMBS.php';

processDirectory($sourceDirectory, $outputBaseDirectory, $scriptPath, $gameId);

//这个脚本搜索文件路径的所有然后 然后根据路径写入至输出路径下
//使用方式 : 文件路径 输出路径  