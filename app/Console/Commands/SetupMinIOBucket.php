<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\S3\S3Client;

class SetupMinIOBucket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minio:setup {--public : Make bucket public for read access}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup MinIO bucket with proper policies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $config = config('filesystems.disks.minio');
        $bucket = $config['bucket'];
        $endpoint = $config['endpoint'];

        $this->info("Setting up MinIO bucket: {$bucket}");

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $config['region'],
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
            ]);

            // Check if bucket exists
            if (!$s3Client->doesBucketExist($bucket)) {
                $this->info("Creating bucket: {$bucket}");
                $s3Client->createBucket(['Bucket' => $bucket]);
                $this->info("Bucket created successfully!");
            } else {
                $this->info("Bucket already exists: {$bucket}");
            }

            // Set bucket policy for public read access to poster and thumbnail directories
            if ($this->option('public')) {
                $this->info("Setting public read policy for posters and thumbnails...");

                $policy = json_encode([
                    'Version' => '2012-10-17',
                    'Statement' => [
                        [
                            'Effect' => 'Allow',
                            'Principal' => ['AWS' => ['*']],
                            'Action' => ['s3:GetObject'],
                            'Resource' => [
                                "arn:aws:s3:::{$bucket}/posters/*",
                                "arn:aws:s3:::{$bucket}/thumbnails/*",
                            ]
                        ]
                    ]
                ]);

                $s3Client->putBucketPolicy([
                    'Bucket' => $bucket,
                    'Policy' => $policy
                ]);

                $this->info("Public read policy applied successfully!");
            }

            $this->info("MinIO setup completed!");

        } catch (\Exception $e) {
            $this->error("Error setting up MinIO: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
