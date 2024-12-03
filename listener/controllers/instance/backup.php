<?php

/**
 * Creates a backup of a specific instance
 *
 * @param array{instance: string} $data
 * @return array
 * @throws Exception
 */
function instance_backup(array $data): array {
    if(!isset($data['instance'])) {
        throw new InvalidArgumentException("missing_instance", 400);
    }

    if(
        in_array($data['instance'], ['..', '.', 'docker', 'ubuntu'])
        || $data['instance'] !== basename($data['instance'])
    ) {
        throw new InvalidArgumentException("invalid_instance", 400);
    }

    if(!file_exists('/home/'.$data['instance']) || !is_dir('/home/'.$data['instance'])) {
        throw new \Exception("instance_not_found", 404);
    }

    // TODO: Put in maintenance mode

    $instance_escaped = escapeshellarg($data['instance']);

    // Remove old export, if any
    exec("rm -rf /home/$instance_escaped/export");
    exec("mkdir /home/$instance_escaped/export");

    // Backup
    $volume_name = str_replace('.', '', $data['instance']).'_db_data';

    $to_export = [
        "/var/lib/docker/volumes/$volume_name/_data",
        "/home/$instance_escaped/.env",
        "/home/$instance_escaped/docker-compose.yml",
        "/home/$instance_escaped/php.ini",
        "/home/$instance_escaped/mysql.cnf",
        "/home/$instance_escaped/www"
        // TODO: Handle SSL/TLS Certificates
    ];

    $timestamp = date('YmdHis');
    $to_export_str = implode(' ', $to_export);

    exec("tar -cvzf /home/$instance_escaped/export/backup-$timestamp.tar.gz $to_export_str");

    return [
        'code' => 201,
        'body' => "instance_backup_created"
    ];
}