<?php
system('source ./.env');
$based_directory = $_ENV['LILY_CERTIFICATE_BASED_DIRECTORY'] ?? null;
$target = $_ENV['LILY_NGINX_TARGET'] ?? null;

if ($based_directory === null || $target === null) {
    echo "Please set envs LILY_CERTIFICATE_BASED_DIRECTORY and LILY_NGINX_TARGET.";
    exit(1);
}

$maps = [
    'fullchain.pem' => 'private.pem',
    'privkey.pem' => 'private.key',
];

foreach (glob($based_directory . '/{fullchain,privkey}.pem', GLOB_BRACE) as $file) {
    $name = basename($file);
    echo "Copying $name\n";
    file_put_contents(
        $target . '/' . $maps[$name],
        file_get_contents($file)
    );
}

echo "Start to rebuild nginx.\n";
system("cd $target && docker build . --no-cache");
echo "Finish to rebuild.\n";
echo "Please restart server with `sudo reboot`.\n";
