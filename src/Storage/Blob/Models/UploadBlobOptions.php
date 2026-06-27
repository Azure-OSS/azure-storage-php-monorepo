<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures upload blob options.
 */
final class UploadBlobOptions
{
    public readonly BlobHttpHeaders $httpHeaders;

    /**
     * @param  int  $initialTransferSize  The size of the first range request in bytes. Blobs smaller than this limit will be transferred in a single request. Blobs larger than this limit will continue being transferred in chunks of size MaximumTransferSize.
     * @param  ?int  $maximumTransferSize  The maximum length of a transfer in bytes.
     * @param  int  $maximumConcurrency  The maximum number of workers that may be used in a parallel transfer.
     */
    public function __construct(
        public int $initialTransferSize = 256_000_000,
        public ?int $maximumTransferSize = null,
        public int $maximumConcurrency = 5,
        ?BlobHttpHeaders $httpHeaders = null,
        public ?BlobRequestConditions $conditions = null,
    ) {
        $this->httpHeaders = $httpHeaders ?? new BlobHttpHeaders;
    }
}
